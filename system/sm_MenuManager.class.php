<?php 
/* Icaro Supervisor & Monitor (ICSM).
   Copyright (C) 2015 DISIT Lab http://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA. */

define("MAINMENU","main_menu");
define("ADMINMENU","Admin Menu");
define("USERMENU","User Menu");

class sm_MenuManager implements sm_Module
{
	static $instance;
	protected $menus;
	protected $db;
	function __construct($type=null)
	{
		$this->menus=array();
		$this->db=sm_Database::getInstance();
	}
	
	function bootstrap(){
		$main_type= sm_Config::get("MAINMENU",MAINMENU);
		$where="type='".$main_type."' AND disabled=0";
		if(class_exists("sm_ACL"))
		{
			$acl = new sm_ACL(sm_User::current());
			$roles = $acl->getUserRoles();
			if(!empty($roles))
				$where="type='".$main_type."' AND disabled=0 AND access in (".implode(",",$roles).")";
			else 
				$where="type='".$main_type."' AND disabled=0";
		}
		$r=$this->db->select("menu",$where,null,"",array("weight"));
		if($r && count($r)>0)
		{
			$this->menus=new sm_Menu($r[0]['name']);
			if(count($r)>1)
			{
				unset($r[0]);
				foreach($r as $i=>$m)
				{
					$submenu=new sm_Menu($m['name']);
					$items = $submenu->getMenuItems();
					$this->menus->addMenuItems($items);
				}
			}
			$view = new sm_MenuView($this->menus);
		}
	}
	
	public function create()
	{
		sm_View::instance()->invoke("menu",array(&$this));
		//sm_call_method("menu",array(&self::$instance),null,"sm_ViewElement");
	}
	
	public function addMenuItems($items)
	{
		//$this->$menu_items=array_merge($this->$menu_items,$items);
		foreach($items as $k=>$v)
		{
			$v->insert();
		}
	}
	
	public function setMainLink($title,$url='#',$icon="",$weight=0,$menu=null)
	{
		if(!isset($menu))
			$menu =  sm_Config::get("ADMINMENU",ADMINMENU);
		$group = $this->getMenuId($menu);
		$item=new sm_MenuItem();
		$item->select(array("title"=>$title));
		$item->setpath($url);
		$item->seticon($icon);
		$item->setweight($weight);
		$item->settitle($title);
		$item->setgroupId($group);
		$item->insert();
	}
	
	public function setSubLink($parent,$title,$url='#',$icon="",$weight=0,$menu=null){
	
		if(!isset($menu))
			$menu =  sm_Config::get("ADMINMENU",ADMINMENU);
		$group = $this->getMenuId($menu);
		$parentItem=new sm_MenuItem();
		$parentItem->select(array("title"=>$parent));
		$parentId=$parentItem->getmid();
		if(!isset($parentId))
			$this->setMainLink($parent);
		$parentItem->select(array("title"=>$parent));
		$parentId=$parentItem->getmid();
		$item=new sm_MenuItem();
		$item->select(array("title"=>$title));
		if($item->getpath()!=$url)
		{
			$item->setmid(null);
			$item->setpath($url);
			$item->seticon($icon);
			$item->setweight($weight);
			$item->settitle($title);
			$item->setparent($parentId);
			$item->setgroupId($group);
			$item->insert();
		}
	}
	
	public function getMenuId($name){
		$database = sm_Database::getInstance();
		$oResult = $database->select("menu",array("name"=>$name),array("id"));
		if ($oResult) {
			$oRow =(object)$oResult[0];
			return $oRow->id;
		}
		return null;
	}
	
	static public function install($db)
	{
		$query = "CREATE TABLE IF NOT EXISTS `menu` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `module` varchar(255) NOT NULL,
			  `type` varchar(255) NOT NULL,
			  `tpl_var` varchar(255) NOT NULL,
			  `style` varchar(255) NOT NULL,
			  `disabled` int(1) DEFAULT '0',
			  `access` varchar(255) NOT NULL,
			  `weight` int(11) DEFAULT '0',
			  PRIMARY KEY (`id`) USING BTREE
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		$db->query($query);
		
		$query ="INSERT INTO `menu`
		(`id`,`name`,`module`,`type`,`tpl_var`,`style`,`disabled`,`access`,`weight`)VALUES(1,'Admin Menu','sm_MenuBar','main_menu','menu','side-nav',0,'00000000000000000001',0),
		(2,'User Menu','sm_MenuBar','main_menu','menu','side-nav',0,'00000000000000000002',0);";
		
		$db->query($query);
		sm_Config::set("MAINMENU",array("value"=>MAINMENU,"description"=>"The identifier/type of the main menu"));
		$query="CREATE TABLE IF NOT EXISTS `menu_items` (
			  `mid` int(11) NOT NULL AUTO_INCREMENT,
			  `path` varchar(255) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `groupId` int(255) DEFAULT '-1',
			  `icon` text,
			  `home` int(1) DEFAULT '0',
			  `hidden` int(1) DEFAULT '0',
			  `weight` int(11) DEFAULT '0',
			  `parent` int(10) unsigned NOT NULL DEFAULT '0',
			  `disabled` int(1) DEFAULT '0',
			  PRIMARY KEY (`mid`,`path`) USING BTREE,
			  KEY `path` (`path`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
			
		$db->query($query);
		return true;
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete("MAINMENUNAME");
		$query="DROP TABLE IF EXISTS `menu_items`";
		$db->query($query);
		//return true;
		
		$query="DROP TABLE IF EXISTS `menu`";
		$db->query($query);
		return true;
	}
	
	/**
	 * Get the current instance of the MenuManager
	 * @return sm_MenuManager
	 */
	static public function instance()
	{
		if(self::$instance ==null)
		{
			$c=__CLASS__;
			self::$instance=new $c();
		}
		return self::$instance;
	}
	
	function getMenuItems($group=null,$where=array()){
		$items=array();
		
		if(isset($group))
			$where["groupId"]=$group;
		$r=$this->db->select("menu_items",$where,array("mid"),'',array('parent','weight'));
			
		foreach($r as $mItem)
		{
			$item = new sm_MenuItem();
			$item->select(array("mid"=>$mItem['mid']));
			$items[]=$item;
		}
		return $items;
	}
	
	function getMenus($id=null,$where=array()){
		
		
		if(isset($id))
			$where["id"]=$id;
		$r=$this->db->select("menu",$where);
			
		if($r)
			return $r;
		return null;
	}
	
	function menu_reorder($orderData,$parent=0)
	{
		foreach($orderData as $i=>$v)
		{
			$item = new sm_MenuItem();
			$item->load($v['id']);
			$item->setweight($i);
			$item->setparent($parent);
			$item->insert();
			if(isset($v['children']))
				$this->menu_reorder($v['children'],$v['id']);
		}
		return true;
	}
	
	/**
	 * 
	 * @param number $id
	 */
	
	function  delete_menu_item($id)
	{
		$item = new sm_MenuItem();
		return $item->delete($id);
	}
	
	
	function save_menu_item($data)
	{
		$item = new sm_MenuItem();
			
		foreach ($data as $prop=>$v)
		{
			if($prop=="mid" && empty($v))
				continue;
			if(in_array($prop,array("home","disabled","hidden")))
				call_user_func_array(array($item,"set".$prop), array($v[0]));
			else
				call_user_func_array(array($item,"set".$prop), array($v));
			
		}
		$result = $item->insert();
			
		$this->propagate("groupId",$item->getgroupId(),$item->getmid());
		return $result;
	}
	
	/**
	 *
	 * @param number $id
	 */
	
	function delete_menu($id)
	{
		$items=$this->getMenuItems($id);
		foreach($items as $item)
			$item->delete();
		return $this->db->delete("menu",array("id"=>$id));
	}
	
	
	function save_menu($data)
	{
					
		$where=null;
		if(isset($data['id']))
		{
			$where=array("id"=>$data['id']);
			
		}
		$r=$this->db->save("menu",$data,$where);
		if($r)
			return $r;
		return null;
	}
	
	function propagate($prop,$val,$parent)
	{
		$where=array("parent"=>$parent);
		$r=$this->db->select("menu_items",$where,array("mid"));
			
		foreach($r as $mItem)
		{
			$item = new sm_MenuItem();
			$item->load($mItem['mid']);
			call_user_func_array(array($item,"set".$prop), array($val));
			$item->insert();
			$this->propagate($prop,$val,$item->getmid());
		}
	}
}