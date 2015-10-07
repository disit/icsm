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

define('MENU',0);
define('MENU_CALLBACK',1);

class sm_Menu //extends sm_Widget //implements sm_Module
{
	protected $id;
	protected $tpl_var;
	protected $access;
	protected $name;
	protected $disabled;
	protected $style;
	protected $type;
	protected $module;
	protected $weight;
	
	protected $prop;
	protected $activeLink;
	protected $menuItems;
	
	
	
	function __construct($name=null)
	{
		//parent::__construct();
		$this->id=-1;
		$this->type="";//side-nav"; //"nav navbar-nav side-nav";
		$this->tpl_var="none";
		$this->module="";
		$this->disabled=0;
		$this->name="";
		$this->style="";
		$this->type="";
		$this->weight=0;
		
		$this->activeLink="";
		$this->menuItems=array();
		if($name)
			$this->load($name);
		//$this->menuItems=array();
		
	}
	
	public function getid()
	{
		return $this->id;
	}
	
	public function setname($s)
	{
		$this->name=$s;
	}
	
	public function settype($s)
	{
		$this->type=$s;
	}
	
	public function setstyle($s)
	{
		$this->style=$s;
	}
	
	public function setweight($s)
	{
		$this->weight=$s;
	}
	
	public function setmodule($s)
	{
		$this->module=$s;
	}
	
	public function settpl_var($s)
	{
		$this->tpl_var=$s;
	}
	public function load($name)
	{
		if($this->select(array("name"=>$name)))
		{
			$this->loadMenuItems();
			return true;
		}
		return false;
	}
	
	public function loadById($id)
	{
		if($this->select(array("id"=>$id)))
		{
			$this->loadMenuItems();
			return true;
		}
		return false;
	}
	
	public function select($where=array()) { // SELECT Function
		$database = sm_Database::getInstance();
		// Execute SQL Query to get record.
		//$sSQL = "SELECT * FROM menu WHERE mid = $mID;";
		$oResult = $database->select("menu",$where);
		$oRow=null;
		if ($oResult) {
			$oRow =(object)$oResult[0];
		}
		else {
			$err=$database->getError();
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
		// Assign results to class.
		$this->id = $oRow->id; // Primary Key
		$this->name = $oRow->name;
		$this->type = $oRow->type;
		$this->style = $oRow->style;
		$this->tpl_var = $oRow->tpl_var;
		$this->module = $oRow->module;
		$this->disabled = $oRow->disabled;
	    $this->access = $oRow->access;
	    $this->weight = $oRow->weight;
		return true;
	}
	
	public function insert() {
		$database = sm_Database::getInstance();
		$prop = array();
		$prop['name'] = $this->name;
		$prop['type'] = $this->type;
		$prop['style'] = $this->style;
		$prop['tpl_var'] = $this->tpl_var;
		$prop['module'] = $this->module;
		$prop['disabled'] = $this->disabled;
		$prop['access'] = $this->access;
		$prop['weight'] = $this->weight;
		
		if($this->id<0)
		{
			$result = $database->save('menu',$prop);
			$this->id = $database->getLastInsertedId();
		}
		else
			$result = $database->save('menu',$prop,array("id"=>$this->id));
		return $result;
	}
	
	
	public function delete($mID) {
		$database = sm_Database::getInstance();
		$oResult = $database->delete("menu",array("id"=>$mID));
		return $oResult;
	}
	
	public function build(){
		
		
			if(count($this->menuItems))
			{
				$currentLink=sm_Controller::instance()->getRoutePath();
				if(isset($this->module) )
				{
					$menuClass=$this->module;
					if(class_exists($menuClass))
						$this->uiView=new $menuClass();
					else
						$this->uiView=new sm_MenuBar();
				}
				else 
					$this->uiView=new sm_MenuBar();
				$this->uiView->setActiveLink($currentLink);
				$this->uiView->setMenuStyleType($this->style);
				
				foreach($this->menuItems as $k=>$mItem)
				{
					$this->uiView->addMenuItem($mItem);
				}
				sm_View::insert($this->tpl_var,$this->uiView);
			}
		
	}
	
	function loadMenuItems(){
		$database = sm_Database::getInstance();
		$this->menuItems=array();
		$where=array("groupId"=>$this->id,"hidden"=>0,"disabled"=>0);
		$r=$database->select("menu_items",$where,array("mid"),'',array('weight'));
			
		foreach($r as $mItem)
		{
			$item = new sm_MenuItem();
			$item->select(array("mid"=>$mItem['mid']));
			$this->menuItems[]=$item;
		}
	}
	
	function getMenuItems()
	{
		return $this->menuItems;
	}
	
	function addMenuItems($items=array())
	{
		$this->menuItems=array_merge($this->menuItems,$items);
	}
}

?>