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

class sm_MenuConfigController extends sm_ControllerElement
{
	protected $model;
	protected $view;

	function __construct()
	{
		$this->model = sm_MenuManager::instance();
	}

	
	/**
	 * Gets the config/menu setting page
	 * 
	 * @category menu
	 * 
	 * @url GET /config/menu
	 * 
	 */
	function menu_index()
	{
		//$data=array();
		$data['menu']=$this->model->getMenus();
		$id = $data['menu'][0]['id'];
		$data['items'] = $this->model->getMenuItems($id);
		$data['id']=$id;
		$data['sect'] = 'menus';
		$this->view = new sm_MenuConfigView();
		$this->view->setModel($data);
		$this->view->setOp("menu::edit");
		$this->view->setType("menu");
	
	}
	
	/**
	 * Gets the config/menu
	 *
	 * @url GET /config/menu/menus
	 * @url GET /config/menu/menus/:op
	 * @url GET /config/menu/menus/:op/:id
	 *
	 */
	function menu_menus($op=null, $id=null)
	{
		$data['sect'] = 'menus';
		if(!isset($op))
			$op="menu::edit";
		else 
			$op="menu::".$op;
		$menus = $this->model->getMenus();
		if(!isset($id))
			$id = $menus[0]['id'];
		$data['items'] = $this->model->getMenuItems($id);
		$data['menu']=$menus;
		$data['id']=$id;
		$this->view = new sm_MenuConfigView();
		$this->view->setModel($data);
		$this->view->setOp($op);
		$this->view->setType("menu");
	}
	
	/**
	 * @desc Gets the config menu items page
	 *
	 * @url GET /config/menu/items
	 *
	 * @access System Administrator
	 */
	function menu_items()
	{
		$data['sect'] = 'items';
		$op="items::edit";
		
		$data['keywords']="";
		$data['groupId']=null;
		$where=array();
		if(isset($_SESSION['config/menu/items']['keywords']) && $_SESSION['config/menu/items']['keywords']!="")
		{
			$data['keywords']=$_SESSION['config/menu/items']['keywords'];
			$where['title']=array("like","'%".$data['keywords']."%'");
		}
		if(isset($_SESSION['config/menu/items']['groupId']) && $_SESSION['config/menu/items']['groupId']>-2)
		{
			$data['groupId']=$_SESSION['config/menu/items']['groupId'];
		}
		
		
		$data['items'] = $this->model->getMenuItems($data['groupId'],$where);
		$data['menu']=$this->model->getMenus();
		
		
		$commands=array();
		$commands['DeleteTBWSelectedMenuItems']=array('name'=>'DeleteTBWSelectedMenuItems','data-confirm'=>'Are you sure you want to delete this selection?','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
		
		$data['commands']=$commands;
		$this->view = new sm_MenuConfigView();
		$this->view->setModel($data);
		$this->view->setOp($op);
		$this->view->setType("menu");
	}
	
	/**
	 * Gets the config/menu
	 *
	 * @url GET /config/menu/items/:op
	 * @url GET /config/menu/items/:op/:id
	 *
	 * @access System Administrator
	 */
	function menu_items_op($op=null, $id=null)
	{
		$data['sect'] = 'items';
		$op="items::".$op;
		
		$data['items'] = $this->model->getMenuItems($id);
		$data['menu']=$this->model->getMenus();
		$data['id']=$id;
		
		$this->view = new sm_MenuConfigView();
		$this->view->setModel($data);
		$this->view->setOp($op);
		$this->view->setType("menu");
	}
	
	/**
	 * @desc Register the menu configuration
	 *
	 * @url POST /config/menu
	 * 
	 */
	function menu_save_config($data)
	{
		//$data=array();
		if(isset($data['form']))
		{
			if($data['form']=="menu_reorder")
			{
				$this->model->menu_reorder(json_decode($data['json']));
			}
		}
	
	}

	/**
	 * @desc Post the menu/reorder data
	 *
	 * @url POST menu/reorder
	 * 
	 */
	function menu_reorder_index($data)
	{
		if($this->model->menu_reorder(json_decode($data['json'],true)))
			sm_set_message("Menu order saved!");
		
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
		else 
			sm_app_redirect("config/menu");
	}
		
	/**
	 * @desc Delete a menu item from menu and menu items table
	 *
	 * @url POST menu/delete/item
	 * 
	 */
	function menu_delete_item($data)
	{
		
		$result=$this->model->delete_menu_item($data['mid']);
		if($result)
			sm_set_message("Menu item '#".$data['mid']."' deleted successfully!");
		else
			sm_set_error("An Error occurred when deleting item '#".$data['mid']."'!");
	}
	
	/**
	 * @desc Delete a menu item from menu and menu items table
	 *
	 * @url POST menu/delete/item/:mid
	 *
	 */
	function menu_remove_item($mid,$data)
	{
	
		if($mid && is_numeric($mid))
		{
			$result=$this->model->delete_menu_item($mid);
		
			if($result)
				sm_set_message("Menu item '#".$mid."' deleted successfully!");
			else
				sm_set_error("An Error occurred when deleting item '#".$mid."'!");
		}
		else
			sm_set_error("Invalid data");
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Delete a menu item from menu menu items table (ajax call)
	 *
	 * @url POST menu/item/delete
	 *
	 * @callback
	 */
	function menu_ajax_delete_item($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
	
			$value = $this->model->delete_menu_item($_id[0])?true:false;
				
		}
		else
			$value = false;
		$this->view=new sm_MenuConfigView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Delete a menu from menu table
	 *
	 * @url POST menu/delete/menu
	 *
	 */
	function menu_delete_menu($data)
	{
	
		$result=$this->model->delete_menu($data['id']);
		if($result)
			sm_set_message("Menu '#".$data['id']."' deleted successfully!");
		else
			sm_set_error("An Error occurred when deleting menu '#".$data['id']."'!");
	}
	
	/**
	 * @desc GET the menu item edit form 
	 *
	 * @url GET menu/edit/item
	 * @url GET menu/edit/item/:id
	 * 
	 * @callback
	 */
	
	function menu_edit_item($id=null){
		$data = new sm_MenuItem();
		if(isset($id))
			$data->load($id);
		$this->view = new sm_MenuConfigView($data);
		$this->view->setOp("edit::item");
			
	}
	
	/**
	 * @desc GET the menu item edit form
	 *
	 * @url GET menu/clone/item/:id
	 *
	 * @callback
	 */
	
	function menu_clone_item($id=null){
		$data = new sm_MenuItem();
		if(isset($id))
			$data->load($id);
		$data->setmid(null);
		$data->setgroupId(null);
		$this->view = new sm_MenuConfigView($data);
		$this->view->setOp("clone::item");
			
	}
	
	/**
	 * @desc GET the menu item edit form
	 *
	 * @url GET menu/menus/add/:id
	 *
	 * @callback
	 */
	function menu_add_item($id){
		$data = new sm_MenuItem();
		if(isset($id))
			$data->setgroupId($id);
		$this->view = new sm_MenuConfigView($data);
		$this->view->setOp("edit::item");
	}
	
	/**
	 * @desc Save/Update a menu item in the menu table
	 *
	 * @url POST menu/edit/item
	 *
	 */
	function menu_save_item($data)
	{
		$result=true;
		if(isset($data['mid']))
		{
			unset($data['form']);
			$result = $this->model->save_menu_item($data);
			if($result)
				sm_set_message("Menu item '".$data['title']."' saved successfully!");
			else
				sm_set_error("An Error occurred when saving item '".$data['title']."'!");
		}
		sm_app_redirect($_SERVER["HTTP_REFERER"]);
	}
	
	/**
	 * @desc Clone a menu item in the menu table
	 *
	 * @url POST menu/clone/item
	 * 
	 * @callback
	 */
	function menu_save_clone_item($data)
	{
		$result=true;
		
			unset($data['form']);
			unset($data['clone_children']);
			$result = $this->model->save_menu_item($data);
			if($result)
				sm_set_message("Menu item '".$data['title']."' saved successfully!");
			else
				sm_set_error("An Error occurred when saving item '".$data['title']."'!");
		
		sm_app_redirect($_SERVER["HTTP_REFERER"]);
	}
	
	/**
	 * @desc Save/Update a menu in the menu table
	 *
	 * @url POST menu/menus/add
	 *
	 */
	function menu_add($data)
	{
		$result=true;
		if(isset($data['name']))
		{
			unset($data['form']);
			$menu = new sm_Menu();
			$menu->setname($data['name']);
			$result = $menu->insert();
			if($result)
			{
				sm_set_message("Menu '".$data['name']."' saved successfully!");
				sm_app_redirect("config/menu/menus/edit/".$menu->getid());
				return;
			}
			else
				sm_set_error("An Error occurred when saving item '".$data['name']."'!");
			
		}
		sm_app_redirect("config/menu/menus/edit/");
	}
	
	/**
	 * @desc Save/Update/Delete a menu in the menu table
	 *
	 * @url POST menu/menus/edit
	 *
	 */
	function menu_save($data)
	{
		$result=true;
		if(isset($data['id']) && $data['id']>0)
		{
			unset($data['form']);
			$cmd=$data['cmd'];
			unset($data['cmd']);
			if($cmd=="")
			{
				$result = $this->model->save_menu($data);
				if($result)
				{
					sm_set_message("Menu '".$data['name']."' saved successfully!");
					sm_app_redirect("config/menu/menus/edit/".$data['id']);
					return;
				}
				else
					sm_set_error("An Error occurred when saving item '".$data['name']."'!");
			}
			else if($cmd=="delete")
			{
				$result = $this->model->delete_menu($data['id']);
				if($result)
				{
					sm_set_message("Menu '".$data['name']."' delete successfully!");
				}
				else
					sm_set_error("An Error occurred when deleting item '".$data['name']."'!");
			}
			
		}
		sm_app_redirect("config/menu/menus/edit/");
	}
	
	
}

