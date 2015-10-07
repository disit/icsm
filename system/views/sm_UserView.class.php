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

class sm_WelcomeUserWidget extends sm_Widget
{
	protected $user;
	function __construct()
	{
		parent::__construct();
		$this->user=new sm_User();
		$this->setTemplateVar('navbar');
	}
	
	function build()
	{
		$this->uiView = $html = new sm_HTML();
		$html->setTemplateId('user_menu', 'users.tpl.html');
		$html->insert('profile_url','user/profile');
		$html->insert('username',$this->user->get_property('username'));
		$html->insert('logout_url','user/logout');
		
		//$this->addView();
	}
}

class sm_UserView extends sm_ViewElement
{
	
	function __construct($data=null)
	{
		parent::__construct($data);	
			
	}

	
	
	function build()
	{
		switch ($this->op)
		{
			default:
			case 'list':
			case "new":
			case "edit":
				$this->uiView = new sm_Page("user::".$this->op);
				$this->uiView->setTitle("Users");
				$menu = new sm_NavBar();
				$menu->setActive($this->op);
				$menu->insert("list", array("url"=>"user/list","title"=>'Accounts',"icon"=>"sm-icon sm-icon-users"));
				if(sm_ACL::checkPermission("User::Edit"))
				{
					$menu->insert("new", array("url"=>"user/new","title"=>'New',"icon"=>"sm-icon sm-icon-new-user"));
				}
				$this->uiView->menu($menu);
				
				//$this->addView();
				if($this->op=="list")
					$this->uiView->insert($this->user_list());
				else if($this->op=="new")
					$this->uiView->insert($this->user_new());
				else if($this->op=="edit")
					$this->uiView->insert($this->user_edit());
				$this->uiView->addJS("users.js");
				break;
			case "profile":
					$this->user_profile();
				break;
			//callback
			case 'form':	
				$this->uiView  = new sm_HTML();
				$this->uiView->setTemplateId('access_form','access.tpl.html');
				//$this->view->setMainView($html);
				break;
		
			case 'register':
				$this->uiView = new sm_HTML();
				$this->uiView->setTemplateId('access_register_form','access.tpl.html');
				//$this->view->setMainView($html);
				break;
					
			case 'access':
				{
				/*	include "system/config.inc.php";
					$this->uiView = new sm_HTML();
					$this->uiView->setTemplateId('access','access.tpl.html');
					$this->uiView->insert('baseUrl',sm_Config::get("BASEURL",$baseUrl));
					$this->uiView->addCSS('jquery-ui-1.10.2.custom.css','access','css/smoothness/');
					$this->uiView->addJS('jquery-1.11.0.min.js','access');
					$this->uiView->addJS('jquery-ui-1.10.2.custom.min.js','access');
					$this->uiView->addCSS('login.css','access');
					$this->uiView->addJS('form.js','access');
					$this->uiView->addJS('login.js','access');
					$this->uiView->insert("logo",sm_Config::get("SITE_TITLE", ""));
					$this->view->setMainView($this->uiView);
					$this->setTemplateVar(null);*/
				}
				break;
			case 'registration':
			case 'login':
			case 'response':
					$this->uiView=new sm_JSON();
					$this->uiView->insert($this->model);
				break;
			
		}
		
	}
	
	function user_new(){
		
		$panel = new sm_Panel();
		$panel->setTitle('New User');
		$panel->icon(sm_formatIcon("user"));
		$panel->insert(sm_Form::buildForm('register_user',$this));
		return $panel;
	}
	
	
	function user_edit_panel(){
	
		$panel = new sm_Panel();
		$panel->setTitle('User Account');
		$panel->icon(sm_formatIcon("user"));
		$panel->insert(sm_Form::buildForm('edit_user',$this));		
		return $panel;
	}
	
	static public function menu(sm_MenuManager $menu)
	{
		if($menu)
		{
			$menu->setMainLink("Users",'',"user");
			$menu->setSubLink("Users",'New','user/new');
			$menu->setSubLink("Users",'Accounts','user/list');
		}
			
	}
	
	function user_edit(){
		$this->uiView = new sm_Page("user::".$this->op);
		$user=$this->model->userData['username'];
		$uid = $this->model->userData['id'];
		$this->uiView->setTitle($user." Profile");
		$menu = new sm_NavBar();
		$menu->insert("profile", array("url"=>"user/edit/".$uid,"title"=>'Account',"icon"=>"sm-icon sm-icon-users"));
		$menu->setActive($this->type);
		$this->uiView->menu($menu);
		$this->uiView->insert($this->user_edit_panel());
	}
	
	function user_profile(){
		$this->uiView = new sm_Page("user::".$this->op);
		$user=$this->model->userData['username'];
		//$uid = $this->model->userData['id'];
		$this->uiView->setTitle($user." Profile");
		$menu = new sm_NavBar();
		$menu->insert("profile", array("url"=>"user/profile","title"=>'Account',"icon"=>"sm-icon sm-icon-users"));
		$menu->setActive($this->type);
		$this->uiView->menu($menu);
		$this->uiView->insert($this->user_edit_panel());
	}
	
	function user_list(){
		
		$_models = $this->getModel();
		$users=$_models['records'];	
		
		$table = new sm_TableDataView("UserList",$_models);
		$table->setSortable();
		$table->makeResponsive();
		$commands=null;
		if(sm_ACL::checkPermission("User::Edit"))
		{
			$commands=array();
			$commands['DeleteTBWSelectedUser']=array('name'=>'DeleteTBWSelectedUser','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
			$commands['BanTBWSelectedUser']=array('name'=>'BanTBWSelectedUser','title'=>'Disable Selection',"icon"=>"glyphicon glyphicon-off");
			$commands['ActivateTBWSelectedUser']=array('name'=>'ActivateTBWSelectedUser','title'=>'Activate Selection',"icon"=>"glyphicon glyphicon-ok");
		}
		$table->setSeletectedCmd($commands);
		$filterElement['user_search']=array("Search","", "user_search", array('placeholder'=>"Search",'value'=>$_models['keywords'],'class'=>'input-sm form-control'));
		$table->addFilter($filterElement);
		
		$header=array();
		$table->addHRow("",array("data-type"=>"table-header"));
	
		foreach ($users as $k=>$value)
		{
			$table->addRow("",array("id"=>$value['id']));
			$data=array();
			foreach ($value as $l=>$v)
			{
				if($l=='id')
					continue;
				if($k==0)
				{
					//$header[]['header']=ucfirst(str_replace("_"," ",$l));
					
					if($l=='actions')
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
					else 
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
						
				}
				if($l=="active")
					$v=$v>0?'<span class="label label-success">Yes</span>':'<span class="label label-danger">No</span>';
				if($l=='actions' && is_array($v))
				{
					$this->setTemplateId("actions_forms","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
					$v=$this->tpl->getTemplate("actions_forms");
					
				}
				$data[]['data']=$v;
				$table->addCell($v);
		
			}
		}
		
				
		$panel = new sm_Panel();
		$panel->setTitle('Users');
		$panel->icon(sm_formatIcon("user"));
		$panel->insert($table);
		
		return $panel;
	}
	
	function log_in()
	{
		$this->setTemplateId('access_form', 'access.tpl.html');
		echo $this->tpl->display('access_form');
	}
	
	function register_user_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical,
				"labelToPlaceholder" => 0,
				//"action"=>"user/new"
		));
		
		$form->addElement(new Element_HTML('<legend>User Account Data</legend>'));
		$form->addElement(new Element_Textbox("Username","username",array("shortDesc"=>"User account identifier",'value'=>"")));
		$form->addElement(new Element_Password("Password","password",array("shortDesc"=>"User account password min 6 chars",'value'=>"")));
		//$form->addElement(new Element_Textbox("Username","User",array('value'=>"")));
		$form->addElement(new Element_Email("Email","email",array("shortDesc"=>"User account e-mail address",'value'=>"")));
		$form->addElement(new Element_Button("Save","",array('class'=>"button light-gray btn btn-primary")));
	
	}
	
	function register_user_form_submit($data)
	{
		$u=new sm_User();
		if($u->insertUser($data))
			sm_set_message("New User successfully saved!");
	}
	
	function edit_user_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical,
				"labelToPlaceholder" => 0,
				"autocomplete"=>"off"
				//"action"=>"user/new"
		));
		$user = $this->model->userData; 
		$form->addElement(new Element_HTML('<legend>Account Data</legend>'));
		$form->addElement(new Element_Textbox("Username","username",array('value'=>$user['username'],"shortDesc"=>"User account identifier")));
		$form->addElement(new Element_Password("Password","password",array('value'=>"","shortDesc"=>"User account password min 6 chars")));
		$form->addElement(new Element_Hidden("userID",$user['id']));
		$form->addElement(new Element_Email("Email","email",array('value'=>$user['email'],"shortDesc"=>"User account e-mail address")));
		if(sm_ACL::checkPermission("User::Ban"))
			$form->addElement(new Element_Radio("Enabled","active",array(1=>"Yes",0=>"No"),array('value'=>$user['active'],"shortDesc"=>"User account active/blocked")));
		$form->addElement(new Element_Button("Save","",array('class'=>"button light-gray btn btn-primary")));
	
	}
	
	function edit_user_form_submit($data)
	{
		unset($data['form']);
		$u=new sm_User();
		
		if($u->updateUser($data))
			sm_set_message("User profile successfully saved!");
	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="UserList")
		{
			$form->setSubmitMethod("userListFormSubmit",$this);
		}
	
	}
	
	/**
	 *
	 * @param array $data
	 */
	
	public function userListFormSubmit($data)
	{
		$value=array();
		if(isset($data['user_status']))
		{
			$value['status']=$data['user_status'];
	
		}
		if(isset($data['user_search']))
		{
			$value['keywords']=$data['user_search'];
	
		}
		$_SESSION['user/list']=$value;
			
	}
	
	
}
