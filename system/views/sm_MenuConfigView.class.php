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

class sm_MenuConfigView  extends sm_SystemConfigView
{
	/**
	 * 
	 * @param string $data
	 */
	function __construct($data=null)
	{
		parent::__construct($data);
		$this->type="menu";
	}
	
	/**
	 * 
	 * @param sm_Event $event
	 */
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj) && is_a($obj,"sm_SystemConfigView"))
		{
			$this->extendSystemConfigView($obj);
		}
	}
	
	/**
	 * 
	 * @param sm_Widget $obj
	 */
	public function extendSystemConfigView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			$menu = $userUIView->getMenu();
			$menu->insert("menu",array("url"=>"config/menu","title"=>"Menu","icon"=>"sm-icon sm-icon-menu"));
		}		
	}
	
	function build(){
		//callback page/html first
		if($this->op=="edit::item")
			return $this->menu_edit_item();
		if($this->op=="clone::item")
			return $this->menu_clone_item();
		//Parent Page build
		parent::build();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see sm_SystemConfigView::build_config()
	 */
	function build_config()
	{
		$content="";
		$nav_menu=new sm_NavBar("MenuEditTabMenu");
		$nav_menu->setTemplateId("nav_bar");
		$nav_menu->insert("menus",array("url"=>"config/menu/menus","title"=>"Menus"));
		$nav_menu->insert("items",array("url"=>"config/menu/items","title"=>"Menu Items"));
		$nav_menu->setActive($this->model['sect']);
		//$tab_menu->insert("list",array("url"=>"menu/list","title"=>"Menus"));
		$html = new sm_HTML("MenuConfigPage");
		$html->insert("nav_menu",$nav_menu);
		$html->insert("container","<div id=menu_config_container>");
		
		if($this->op=="menu::edit")
			$content =  $this->menu_editor();
		
		else if($this->op=="items::edit")
			$content =  $this->menu_items();

		$html->insert("content",$content);
		$html->insert("end-container","</div>");
		$this->uiView->addJS("jquery.nestable.js");
		$this->uiView->addJS("menu.js");
		$this->uiView->addCSS("menu.css");
		return $html;
	}
	/**
	 * 
	 * @return sm_HTML
	 */
	
	function menu_editor()
	{
		$items = $this->model['items'];
		$nav_menu=new sm_TabMenu("MenuEditNavBar");
		//$nav_menu->setTemplateId("nav_bar");
		foreach($this->model['menu'] as $i=>$menu)
		{
			if($menu['id']==$this->model['id'])
			{
				$this->model['current_menu']=$menu;
				$title = $menu['name'];
				$nav_menu->setActive($i);
				$nav_menu->insert($i,array("url"=>"config/menu/menus/edit/".$menu['id'],"title"=>$menu['name'],"link_class"=>"button"));
			}
			else 
				$nav_menu->insert($i,array("url"=>"config/menu/menus/edit/".$menu['id'],"title"=>$menu['name'],"link_class"=>"button"));
		}
		$nav_menu->insert("add",array("url"=>"#","title"=>"New Menu","class"=>"tab-right","link_class"=>"button","tab"=>'modal',"link_attr"=>"data-target='#AddMenuDlg'"));
		$title =$this->model['current_menu']['name'];
		$html = new sm_HTML();
		$html->insert("nav",$nav_menu);
		$html->insert("container","<div id=menu_editor_container>");
		$html->insert("column_left","<div class=col-md-6>");
		$panel=new sm_HTML("MenuStructure");
		$panel->insert("title","<h4><i>".$title."</i> Structure</h4>");
		
		$panel->insert(0,"<div id=menu_editor_menu >");
		
		$panel->insert(1,'<button class=button class="btn" data-action="expand-all">Expand All</button>');
		$panel->insert(2,'<button class=button class="btn"  data-action="collapse-all">Collapse All</button>');
		$panel->insert(2,"<button class=button href='menu/menus/add/".$this->model['current_menu']['id']."' title=Edit data-toggle='modal' data-target='#MenuEditDlg'>Add Item</button>");
		
		$panel->insert(4,"</div>");
		
		$panel->insert(5,"<div id=menu_editor class='dd'>");
		$code=$this->build_menu($items);
		$panel->insert(6,$code);
		$panel->insert(7,sm_Form::buildForm("menu_reorder", $this));
		$panel->insert(8,"</div>");
	
		$html->insert("panel1",$panel);
		$html->insert("column_left_end","</div>");
		$html->insert("column_right","<div class=col-md-6>");
		
		$panel=new sm_HTML("MenuData");
		$panel->insert("title","<h4><i>".$title."</i> Data</h4>");
		$panel->insert("form", sm_Form::buildForm("menu_data", $this));
		$html->insert("panel2",$panel);
		$html->insert("column_right_end","</div>");
		
		$deleteDlg=new sm_HTML("MenuDeleteItemDlg");
		$deleteDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteDlg->insert("title", "Delete Confirmation");
		$deleteDlg->insert("body", "Do you want to proceed?");
		$deleteDlg->insert("id", "MenuDeleteItemDlg");
		
		$deleteMenuDlg=new sm_HTML("MenuDeleteDlg");
		$deleteMenuDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteMenuDlg->insert("title", "Delete Confirmation");
		$deleteMenuDlg->insert("body", "All menu items will be deleted. Do you want to proceed?");
		$deleteMenuDlg->insert("id", "MenuDeleteDlg");
		
		$editDlg=new sm_HTML("MenuEditDlg");
		$editDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$editDlg->insert("title", "Edit Menu Item");
		$editDlg->insert("id", "MenuEditDlg");
		$editDlg->insert("btn1", "Close");
		$editDlg->insert("btn2", "Save");
		
		
		$addMenuDlg=new sm_HTML("AddMenuDlg");
		$addMenuDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addMenuDlg->insert("title", "Add New Menu");
		$addMenuDlg->insert("id", "AddMenuDlg");
		$addMenuDlg->insert("body",sm_Form::buildForm("menu_new", $this));
		$addMenuDlg->insert("btn1", "Close");
		$addMenuDlg->insert("btn2", "Save");
		
		$html->insert("form", sm_Form::buildForm("menu_delete_item", $this));
		$html->insert("end-container","</div>");
		$html->insert("delete-dialog", $deleteDlg);
		$html->insert("delete-menu-dialog", $deleteMenuDlg);
		$html->insert("edit-dialog", $editDlg);
		$html->insert("add-dialog", $addMenuDlg);
		
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Menus');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($html);
		
		return $panel;
	}
	
	function menu_items()
	{
		$model = $this->getModel();
		$items=$model['items'];
		$groups=array("-2"=>"Select a menu","-1"=>"None");
		$menus = $model['menu'];
		foreach($menus as $menu)
		{
			$id=$menu['id'];
			$groups[$id]=$menu['name'];
		}
		
		$table = new sm_TableDataView("MenuItemsList");
		$table->setSortable();
		
		isset($model['commands'])?$table->setSeletectedCmd($model['commands']):null;
		$header=array();
		$table->addHRow();
		
		foreach ($items as $k=>$value)
		{
			$value=sm_obj2array($value);
			$value['actions']['edit']=array(
					"id"=>"menu-item-edit-".$value['mid'],
					"title"=>"Edit",
					"toggle"=>"modal",
					"target"=>"#MenuEditDlg",
					"url"=>"menu/edit/item/".$value['mid'],
					"icon"=>"sm-icon-edit");
			$value['actions']['clone']=array(
					"id"=>"menu-item-clone-".$value['mid'],
					"title"=>"Clone",
					"toggle"=>"modal",
					"target"=>"#MenuEditDlg",
					"url"=>"menu/clone/item/".$value['mid'],
					"icon"=>"sm-icon-clone");
			$value['actions']['delete']=array(
					"id"=>"menu-item-delete-".$value['mid'],
					"target"=>"#MenuConfirmCommand",
					"class"=>"confirm",
					"message"=>"Are you sure you want to delete this configuration?",
					"title"=>"Delete Item",
					"url"=>'menu/delete/item/'.$value['mid'],
					"icon"=>"sm-icon-trash",
					"method"=>"POST");
			//$table->addRow();
			$table->addRow("",array("id"=>$value['mid']));
			//$data=array();
			foreach ($value as $l=>$v)
			{
				
				if($k==0)
				{
					//$header[]['header']=ucfirst(str_replace("_"," ",$l));
						
					if($l=='actions')
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
					else
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
		
				}
				if($l=="groupId")
					$v=$groups[$v];
				if($l=='actions' && is_array($v))
				{
					/*$this->setTemplateId("actions_forms","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
					$v=$this->tpl->getTemplate("actions_forms");*/
					$a = new sm_TableDataViewActions();
					$a->insertArray($v);
					$v=$a;
						
				}
				//$data[]['data']=$v;
				$table->addCell($v);
		
			}
		}
		
		$filter['groupId']=array("Select","Menu Group", "groupId",$groups,array('class'=>'input-sm','value'=>$model["groupId"],));
		$filter['keywords']=array("Search","", "keywords", array('placeholder'=>"Search",'value'=>$model['keywords'],'class'=>'input-sm form-control'));
		//$filter['to']=array("Date","to", "to",array('class'=>'input-sm','value'=>$this->model['to'],'placeholder'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")",'title'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")"));
		$table->addFilter($filter);
		
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Menu Items');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($table);
		$dlg = new sm_Dialog("MenuConfirmCommand",CONFIRMATION_DLG);
		$dlg->setConfirmationFormClass("confirm");
		$panel->insert($dlg);
		$editDlg=new sm_HTML("MenuEditDlg");
		$editDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$editDlg->insert("title", "Edit Menu Item");
		$editDlg->insert("id", "MenuEditDlg");
		$editDlg->insert("btn1", "Close");
		$editDlg->insert("btn2", "Save");
		$panel->insert($editDlg);
		
		return $panel;
	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="MenuItemsList")
		{
			$form->setSubmitMethod("menuItemsListFormSubmit",$this);
		}
	
	}
	
	public function menuItemsListFormSubmit($data)
	{
		$_SESSION['config/menu/items']=$data;
	}
	
	/**
	 * 
	 * @param unknown $rows
	 * @param unknown $id
	 * @return boolean
	 */
	function has_children($rows, $id) {
		foreach ( $rows as $row ) {
			if ($row->getparent () == $id)
				return true;
		}
		return false;
	}
	
	/**
	 * 
	 * @param unknown $rows
	 * @param number $parent
	 * @return string
	 */
	function build_menu($rows, $parent = 0) {
		if(count($rows)==0)
		{
			return "<p>There are no items in the menu. Click on <i>Add Item</i> to populate.</p>";
		}
		$result = "<ol class=dd-list>";
		foreach ( $rows as $row ) {
			if ($row->getparent () == $parent) {
				$result .= "<li class='dd-item' data-id='".$row->getmid()."'><div class=dd-actions><a href='menu/edit/item/".$row->getmid ()."' title=Edit data-toggle='modal' data-target='#MenuEditDlg'><i class='glyphicon glyphicon-pencil'> </i></a> | <a href='menu/clone/item/".$row->getmid ()."' title=Clone data-toggle='modal' data-target='#MenuEditDlg'><i class='glyphicon glyphicon-plus-sign'> </i></a> | <a href='#'  class='confirm-delete' data-id=". $row->getmid () ." data-toggle='modal' data-target='#MenuDeleteItemDlg' title=Delete ><i class='glyphicon glyphicon-trash'></i></a></div>
						<div class='dd-handle'><div class=dd-title>" . $row->gettitle ()."</div></div>";
				if ($this->has_children ( $rows, $row->getmid () ))
					$result .= $this->build_menu ( $rows, $row->getmid () );
				$result .= "</li>";
			}
		}
		$result .= "</ol>";
		
		return $result;
	}
	
	/**
	 * 
	 */
	function menu_edit_item()
	{
		$editDlg=new sm_HTML("MenuEditDlg");
		$editDlg->setTemplateId("TwoButtonsModalRemote_Dlg","ui.tpl.html");
		$editDlg->insert("title", "Edit Menu Item");
		$editDlg->insert("id", "MenuEditDlg");
		$editDlg->insert("btn1", "Close");
		$editDlg->insert("btn2", "Save");
		
		$editDlg->insert("body", sm_Form::buildForm("menu_edit_item", $this));
		
		$this->uiView = $editDlg;
	}
	
	/**
	 *
	 */
	function menu_clone_item()
	{
		$editDlg=new sm_HTML("MenuEditDlg");
		$editDlg->setTemplateId("TwoButtonsModalRemote_Dlg","ui.tpl.html");
		$editDlg->insert("title", "Clone Menu Item");
		$editDlg->insert("id", "MenuEditDlg");
		$editDlg->insert("btn1", "Close");
		$editDlg->insert("btn2", "Save");
		
		$editDlg->insert("body", sm_Form::buildForm("menu_clone_item", $this));
	
		$this->uiView = $editDlg;
	}
	
	/****************** FORMS *************************/
	
	/**
	 * 
	 * @param sm_Form $form
	 */
	
	function menu_edit_item_form(sm_Form $form)
	{
		$var = sm_obj2array($this->model);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus", "redirect"),
				"view" => new View_SideBySide(), //_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"menu/edit/item"
		));
		$groups=array("-1"=>"Select a menu");
		$menus = sm_MenuManager::instance()->getMenus();
		foreach($menus as $menu)
		{
			$id=$menu['id'];
			$groups[$id]=$menu['name'];
		}
		foreach($var as $i=>$v){
			if($i=="mid")
				$form->addElement(new Element_Hidden($i,$v));
			else if($i=="home" || $i=="disabled" || $i=="hidden")
				$form->addElement(new Element_Radio(ucfirst($i), $i,array(1=>"Yes",0=>"No"),array("value"=>$v)));
			else if($i=="weight")
				$form->addElement(new Element_Number($i,$i,array("value"=>$v)));
			else if($i=="parent")
			{
				$paths=array("0"=>"Select a parent");
				$choices=array();
				$items = sm_MenuManager::instance()->getMenuItems($var['groupId']);
				foreach($items as $item)
				{
						if($item->getgroupId()>0)
							$choices[$item->getmid()]=$groups[$item->getgroupId()]."::".$item->gettitle();
						else
							$choices[$item->getmid()]=$item->gettitle();
				}
				asort($choices);
				$paths=$paths+$choices;
				$form->addElement(new Element_Select($i,$i,$paths,array("value"=>$v)));

			}
			else if($i=="groupId")
			{
				
				$form->addElement(new Element_Select("Menu Group",$i,$groups,array("value"=>$v)));
			}
			else
				$form->addElement(new Element_Textbox($i, $i,array("value"=>$v)));
		}
		
		
	}
	
	function menu_clone_item_form(sm_Form $form)
	{
		$this->menu_edit_item_form($form);
		$form->configure(array(
				"action"=>"menu/clone/item"
		));
		if($this->model->hasChildren())
		{
			$form->addElement(new Element_Radio("Clone children", "clone_children",array(1=>"Yes",0=>"No"),array("value"=>0)));
		}
	}
	
	/**
	 *
	 * @param sm_Form $form
	 */
	
	
	function menu_data_form(sm_Form $form)
	{
		$var = $this->model['current_menu'];
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"menu/menus/edit"
		));
	
		foreach($var as $i=>$v){
			if($i=="id")
				$form->addElement(new Element_Hidden($i,$v));
			else if($i=="disabled")
				$form->addElement(new Element_Radio(ucfirst($i), $i,array(1=>"Yes",0=>"No"),array("value"=>$v)));
			else if($i=="weight")
				$form->addElement(new Element_Number(ucfirst($i),$i,array("value"=>$v)));
			else if($i=="access")
			{
				if(class_exists("sm_ACL"))
				{
					$options=array(""=>"Select User Role Access");
					$acl = new sm_ACL();
					$roles=$acl->getAllRoles('full');
					foreach ($roles as $p=>$role)
						$options[$role['ID']]=$role['Name'];
					$form->addElement(new Element_Select(ucfirst($i), $i, $options,array("value"=>array($v))));
				}
	
			}
			else if($i=="style")
				$form->addElement(new Element_Select(ucfirst($i), $i, sm_MenuBar::getStyles(),array("value"=>array($v))));
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
		}
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
	function menu_new_form(sm_Form $form)
	{
		$var = $this->model['current_menu'];
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"menu/menus/add"
		));
	
		$form->addElement(new Element_Textbox("Insert Menu Name", "name"));
		
	
	}
	
	
	/**
	 * 
	 * @param sm_Form $form
	 */
	
	function menu_reorder_form(sm_Form $form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus", "redirect"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"menu/reorder"
		));
	
		$form->addElement(new Element_Hidden("json"));
		$form->addElement(new Element_Button("Apply","",array("name"=>"Sort","class"=>"button light-gray btn-md")));
		
		
	
	}
	
	
	/**
	 * 
	 * @param sm_Form $form
	 */
	function menu_delete_item_form(sm_Form $form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus", "redirect"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"menu/delete/item"
		));
	
		$form->addElement(new Element_Hidden("mid"));	
	}
	
	
	
}