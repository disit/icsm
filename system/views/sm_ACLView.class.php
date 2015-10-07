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

class sm_ACLView extends sm_UserView
{
	
	function __construct($data=null)
	{
		parent::__construct($data);
		if(!isset($this->model['type']))
			$this->model['type']="users";
		if(!isset($this->model['title']))
			$this->model['title']="Access Command List";
	}
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj) && is_a($obj,"sm_UserView"))
		{
			$this->extendUserView($obj);
		}
	}
	
	
	public function extendUserView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page") )
		{
			switch($obj->getOp())
			{
				default:
				
				case 'list':
				case 'new':
					$menu = $userUIView->getMenu();
					if($menu && sm_ACL::checkRole("System Administrator"))
					{
						$menu->insert("roles",array("url"=>"acl/roles","title"=>'Roles',"icon"=>"sm-icon sm-icon-roles"));
						$menu->insert("perms",array("url"=>"acl/permissions","title"=>'Permissions',"icon"=>"sm-icon sm-icon-perms"));
						$userUIView->addCss("acl.css");
					}
				break;
				
				case 'profile':
					$menu = $userUIView->getMenu();
					if($menu)
					{
						//$user=$obj->getModel();
						//$uid = $user->userData['id'];
						$menu->insert("acl",array("url"=>"acl/user/profile","title"=>'Roles',"icon"=>"sm-icon sm-icon-acl"));
			
						$userUIView->addCss("acl.css");
					}
					break;
				case 'edit':
					$menu = $userUIView->getMenu();
					if($menu)
					{
						$user=$obj->getModel();
						$uid = $user->userData['id'];
						$menu->insert("acl",array("url"=>"acl/user/edit/".$uid,"title"=>'Roles',"icon"=>"sm-icon sm-icon-acl"));
							
						$userUIView->addCss("acl.css");
					}
					break;
			}
			
			
		}
	}
	
	function build()
	{
		parent::build();
		$panel=null;
		switch($this->op)
		{
			case 'user':
				$panel = $this->acl_user();
				break;
			case 'user_roles':
				$panel = $this->acl_user_roles();
				break;
			case 'roles':
				$panel = new sm_Panel("ACL-Roles");
				$panel->setTitle("Roles");
				$panel->insert($this->acl_roles());
				$this->uiView->addJS("acl.js");
				break;
			case 'edit_role': 
					$this->acl_edit_role();
				break;
			case 'perms':
				$panel = $this->acl_permissions();
				break;
			case 'edit_perm':
				$panel = $this->acl_edit_perm();
				break;
			case 'user_perms':
				$panel = $this->acl_user_perms();
				break;
			
		}	
		if($panel)
			$this->uiView->insert($panel);
	}
	
	
	public function acl_edit_role(){	
			$editDlg=new sm_HTML("EditRoleDlg");
			$editDlg->setTemplateId("TwoButtonsModalRemote_Dlg","ui.tpl.html");
			$editDlg->insert("title", "Edit Role");
			$editDlg->insert("id", "EditRoleDlg");
			$editDlg->insert("btn1", "Close");
			$editDlg->insert("btn2", "Save");
			$editDlg->insert("body", sm_Form::buildForm("acl_edit_role", $this));
		
			$this->uiView = $editDlg;
		
	}
	
	public function acl_edit_perm(){	
		$panel = new sm_Panel();
		$panel->setTitle($this->model['permID']=="new"?"New Permission": 'Edit Permission');
		$panel->insert(sm_Form::buildForm("acl_perm_edit", $this));
		return $panel;
	}
	
	public function acl_roles()
	{
		$roles = $this->model['roles'];
		if (count($roles) > 0)
		{
			$table = new sm_TableDataView("acl_roles_table",$this->model);
			$table->setSortable();
			$table->makeResponsive();
			$table->addHRow("",array("data-type"=>"table-header"));
			$table->addHeaderCell("Name");
			$table->addHeaderCell("Description");
			$table->addHeaderCell("Users");
			$table->addHeaderCell("Capabilities");
			$table->addHeaderCell("Actions","sorter-false");
			foreach ($roles as $k => $v)
			{
				$count=0;
				$users= $this->model['userACL']->getUserWithRoles($v['ID']);
				$rPerms = $this->model['userACL']->getRolePerms($v['ID']);
				
				foreach ($rPerms as $k=>$perm)
				{	
					if($perm['value'])
						$count++;
				}
				$table->addRow();
				$table->addCell($v['Name']);
				$table->addCell($v['Description']);
				$table->addCell(count($users));
				$table->addCell($count);
				$actions=array();
				$actions[]=array(
						"id"=>"ACL-edit-".$v['ID'],
						"class"=>"ACLEdit",
						"title"=>"Edit Role",
						"url"=>'acl/roles/'.$v['ID'],
						"data"=>"<i class='sm-icon-16 sm-icon-edit'></i>",
						"target"=>'#EditRoleDlg',
						"method"=>"GET");
				$actions[]=array(
						"id"=>"ACL-delete-".$v['ID'],
						"class"=>"ACLDelete",
						"title"=>"Delete Role",
						"url"=>'acl/roles/delete/'.$v['ID'],
						"data"=>"<i class='sm-icon-16 sm-icon-delete'></i>",
						"target"=>'#ACLDeleteItemDlg',
						"method"=>"POST");
				
			/*	$actions="<button href='acl/roles/".$v['ID']."' class='button action_form_cmd' title='Edit Role' data-toggle='modal' data-target='#EditRoleDlg'><i class='sm-icon-16 sm-icon-edit'></i>";
				$actions.="<button href='acl/roles/delete/".$v['ID']."' class='button action_form_cmd' title='Delete Role' data-toggle='modal' data-target='#ACLDeleteItemDlg'><i class='sm-icon-16 sm-icon-delete'></i></button>";
			*/	
				
					$this->setTemplateId("actions_forms","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $actions);
					$actions=$this->tpl->getTemplate("actions_forms");
				
								
				$table->addCell($actions);
			}
			$command['new']=array('class'=>'btn','label'=>'Add Role','title'=>"Add new role","data-toggle"=>"modal", "data-target"=>"#AddRoleDlg");
			$table->setSeletectedCmd($command);
			$table->setPageLink("acl/roles");
		}
		if (count($roles) < 1)
		{
			
			$table="No roles yet.";
		}
		$this->model['roleID']="new";
		$addRoleDlg=new sm_HTML("AddRoleDlg");
		$addRoleDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addRoleDlg->insert("title", "Add New Role");
		$addRoleDlg->insert("id", "AddRoleDlg");
		$addRoleDlg->insert("body",sm_Form::buildForm("acl_edit_role", $this));
		$addRoleDlg->insert("btn1", "Close");
		$addRoleDlg->insert("btn2", "Save");
		
		$editRoleDlg=new sm_HTML("EditRoleDlg");
		$editRoleDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$editRoleDlg->insert("title", "Edit Role");
		$editRoleDlg->insert("id", "EditRoleDlg");
		
		$editRoleDlg->insert("btn1", "Close");
		$editRoleDlg->insert("btn2", "Save");
		
		$deleteDlg=new sm_HTML("ACLDeleteItemDlg");
		$deleteDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteDlg->insert("title", "Delete Confirmation");
		$deleteDlg->insert("body", "Do you want to proceed?");
		$deleteDlg->insert("id", "ACLDeleteItemDlg");
		
		$html = new sm_HTML();
		$html->setTemplateId("acl_roles","acl.tpl.html");
		$html->insert('content',$table);
		$html->insert('content',$addRoleDlg);
		$html->insert('content',$editRoleDlg);
		$html->insert('content',$deleteDlg);
		
		return $html;
		
		
		
	}
	public function acl_roles_()
	{
		$roles = $this->model['userACL']->getAllRoles('full');
		if (count($roles) > 0)
		{
			$tabs = new sm_Tabs();
			
			foreach ($roles as $k => $v)
			{
				//$roleName[]['role']= "<a href=\"acl/role/" . $v['ID'] . "\">" . $v['Name'] . "</a>";
				$this->model['roleID']=$v['ID'];
				$tabs->insert(str_replace(" ","-",$v['Name']), array("title"=>$v['Name'],"paneldata"=>sm_Form::buildForm("acl_edit_role", $this)));
			}
		}
		if (count($roles) < 1)
		{
			//$roleName[]['role'] = "No roles yet.";
			$tabs="No roles yet.";
		} 
		
		
	/*	$this->setTemplateId("acl_roles_available","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_roles_available","acl_roles",$roleName);
		$this->tpl->addTemplatedata("acl_roles_available",array("url"=>"acl/role/new"));
		$content=$this->tpl->getTemplate("acl_roles_available");*/
		
		
		
		$this->model['roleID']="new";
		$addRoleDlg=new sm_HTML("AddRoleDlg");
		$addRoleDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addRoleDlg->insert("title", "Add New Role");
		$addRoleDlg->insert("id", "AddRoleDlg");
		$addRoleDlg->insert("body",sm_Form::buildForm("acl_edit_role", $this));
		$addRoleDlg->insert("btn1", "Close");
		$addRoleDlg->insert("btn2", "Save");
		
		$html = new sm_HTML();
		$html->setTemplateId("acl_roles","acl.tpl.html");
		$html->insert('content',$tabs);
		$html->insert('content',$addRoleDlg);
		
		
		
		$panel = new sm_Panel();
		$panel->setTitle("Roles");
		$panel->insert($html);
		return $panel;
		
	}
	
	public function acl_permissions()
	{
		$roles = $this->model['userACL']->getAllPerms('full');
		foreach ($roles as $k => $v)
		{
			$roleName[]['perm']= "<a href=\"acl/perm/" . $v['ID'] . "\">" . $v['Name'] . "</a>";
		}
		if (count($roles) < 1)
		{
			$roleName[]['perm'] = "No permissions yet.";
		}
		$this->setTemplateId("acl_perms_available","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_perms_available","acl_perms",$roleName);
		$this->tpl->addTemplatedata("acl_perms_available",array("url"=>"acl/perm/new"));
		$content=$this->tpl->getTemplate("acl_perms_available");
	
			
		$html = new sm_HTML();
		$html->setTemplateId("acl_perms","acl.tpl.html");
		$html->insert("content",$content);
		
		$panel = new sm_Panel();
		$panel->setTitle("Permissions");
		$panel->insert($html);
		return $panel;
	
	}
	
	public function acl_user() {
		$content="";
		$roles=$this->model['userACL']->getUserRoles();
		$roleName=array();
		if (count($roles) < 1)
		{
			$roleName[]['role'] = "No roles yet.";
		}
		else
			foreach ($roles as $k => $v)
			{
				$roleName[]['role']= $this->model['userACL']->getRoleNameFromID($v);
			}
		
		$perms = $this->model['userACL']->perms;
		$permsData=array();
		if (count($perms) < 1)
		{
			$permsData[]['permissions'] = "No permissions yet.";
		}
		else
			foreach ($perms as $k => $v)
			{
				if ($v['value'] === false) { continue; }
					$s= $v['Name'];
				if ($v['inheritted']) { $s.="  (inherited)"; }
				
				$permsData[]['permissions']=$s;
			}
		$username=$this->model['userACL']->getUsername();
		
		
		$this->setTemplateId("acl_roles_list","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_roles_list","acl_roles",$roleName);
		$this->tpl->addTemplatedata("acl_roles_list",array("userID"=>$this->model['userACL']->userID));
		$content.=$this->tpl->getTemplate("acl_roles_list");
		
		$this->setTemplateId("acl_permissions_list","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_permissions_list","acl_permissions",$permsData);
		$this->tpl->addTemplatedata("acl_permissions_list",array("userID"=>$this->model['userACL']->userID));
		$content.=$this->tpl->getTemplate("acl_permissions_list");
		
		$this->setTemplateId("acl_roles_permissions","acl.tpl.html");
		$this->tpl->addTemplatedata("acl_roles_permissions",array("username"=>$username,"content"=>$content,"userID"=>$this->model['userACL']->userID));		
		
		$html = new sm_HTML();
		$html->insert("acl_permissions_list",$this->tpl->getTemplate("acl_roles_permissions"));
		$panel = new sm_Panel();
		$panel->setTitle("Roles & Permissions");
		$panel->insert($html);
		return $panel;
	
	}
	
	public function user_edit_panel(){
		$content="";
		$roles=$this->model->userACL->getUserRoles();
		$roleName=array();
		if (count($roles) < 1)
		{
			$roleName[]['role'] = "No roles yet.";
		}
		else
			foreach ($roles as $k => $v)
			{
				$roleName[]['role']= $this->model->userACL->getRoleNameFromID($v);
			}
		
		$perms = $this->model->userACL->perms;
		$permsData=array();
		if (count($perms) < 1)
		{
			$permsData[]['permissions'] = "No permissions yet.";
		}
		else
			foreach ($perms as $k => $v)
			{
				if ($v['value'] === false) { continue; }
					$s= $v['Name'];
				if ($v['inheritted']) { $s.="  (inherited)"; }
				
				$permsData[]['permissions']=$s;
			}
		$username=$this->model->userData['username'];
		$grid = new sm_Grid("ACLUserProfileGrid");
		$this->setTemplateId("acl_roles_list","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_roles_list","acl_roles",$roleName);
		$this->tpl->addTemplatedata("acl_roles_list",array("userID"=>$this->model->userData['id']));
		$content[]=$this->tpl->getTemplate("acl_roles_list");
		
		$this->setTemplateId("acl_permissions_list","acl.tpl.html");
		$this->tpl->addTemplatedataRepeat("acl_permissions_list","acl_permissions",$permsData);
		$this->tpl->addTemplatedata("acl_permissions_list",array("userID"=>$this->model->userData['id']));
		$content[]=$this->tpl->getTemplate("acl_permissions_list");
		if(sm_ACL::checkRole("System Administrator"))
		{	
			/*$this->setTemplateId("acl_roles_permissions","acl.tpl.html");
			$this->tpl->addTemplatedata("acl_roles_permissions",array("username"=>$username,"content"=>implode("",$content),"userID"=>$this->model->userData['id']));
			$left = $this->tpl->getTemplate("acl_roles_permissions");
			*/
			$left = new sm_HTML();
			$left->insert("1", $content[0]);
			$left->insert("2", $content[1]);
			$right = new sm_Tabs();
			$right->insert("Roles", array("title"=>"Roles","paneldata"=>$this->acl_user_roles()));
			$right->insert("Permissions",array("title"=>"Permissions","paneldata"=>$this->acl_user_perms()));
			
			$grid->addRow(
					array($left,$right),
					array(2,10)
			);
		}
		else 
		{
			$grid->addRow(
					array($content[0],$content[1]),
					array(6,6)
			);
		}
		
		$panel = new sm_Panel("ACLUserProfile");
		$panel->setTitle("Roles & Permissions");
		$panel->insert($grid);
		return $panel;
	}
	
	public function acl_user_roles() {		
		$username=$this->model->userACL->getUsername();
		$html = new sm_HTML('ACL_User_Roles');
		$html->setTemplateId("acl_roles_edit","acl.tpl.html");
		$html->insert("username",$username);
		$html->insert("content",sm_Form::buildForm("acl_user_roles", $this));
		
		return $html;
	
	}
	
	public function acl_user_perms() {
		$username=$this->model->userACL->getUsername();	
		$html = new sm_HTML('ACL_User_Perms');
		$html->setTemplateId("acl_perms_edit","acl.tpl.html");
		$html->insert("username",$username);
		$html->insert("content",sm_Form::buildForm("acl_user_perms", $this));
		return $html;
	}
	
	
	/*
	 * 		ACL - Forms Section
	 */
	
	function acl_user_roles_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"acl/users/roles"
		));
		$table = new sm_Table();		
		$table->addHRow();
		$table->addHeaderCell("");
		$table->addHeaderCell("Member");
		$table->addHeaderCell("Not Member");
		$table->addFooterCell("","",array("colspan"=>3));
		
		$rows=array();
		$roles=$this->model->userACL->getAllRoles('full');
		foreach ($roles as $k => $v)
		{
			$table->addRow();
			//$cell=array();
			$cell="<label>" . $v['Name'] . "</label>";
			$table->addCell($cell);

			$cell="<input type=\"radio\" name=\"role_" . $v['ID'] . "\" id=\"role_" . $v['ID'] . "_1\" value=\"1\"";
			if ($this->model->userACL->userHasRole($v['ID'])) 
				{ $cell.= " checked=\"checked\""; }
			$cell.=" /></td>";
			$table->addCell($cell);
			
			$cell= "<input type=\"radio\" name=\"role_" . $v['ID'] . "\" id=\"role_" . $v['ID'] . "_0\" value=\"0\"";
			if (!$this->model->userACL->userHasRole($v['ID'])) { $cell.= " checked=\"checked\""; }
			$cell.=" /></td>";
			
			
			$table->addCell($cell);
		}
		
	
		
		$form->addElement(new Element_HTML("<div class=row>"));
		$form->addElement(new Element_HTML("<div class=col-xs-6>"));
		
		
		$form->addElement(new Element_HTML($table->render()));
		$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_Hidden("action","saveRoles"));
		$form->addElement(new Element_Hidden("userID",$this->model->userACL->userID));
		$form->addElement(new Element_HTML("<div class='btn-toolbar' role='toolbar'>"));
		$form->addElement(new Element_HTML("<div class=btn-group>"));
		$form->addElement(new Element_HTML("<button type=submit class='button light-gray btn btn-primary'>Save</button>"));
		$form->addElement(new Element_HTML("</div>"));
		
		
		$form->addElement(new Element_HTML("</div>"));
	}
	
	function acl_user_perms_form($form){
		
		$view = new View_Grid();
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => $view,
				//"labelToPlaceholder" => 1,
				"action"=>"acl/users/perms"
		));
		$rPerms =$this->model->userACL->perms; 
		$aPerms = $this->model->userACL->getAllPerms('full'); 
		//$view = $form->getView();
		
	//	$form->addElement(new Element_HTML("<div class=row>"));
	//	$form->addElement(new Element_HTML("<div class='col-xs-3 col-md-6'>"));
		$i=0;
		foreach ($aPerms as $k => $v)
		{
			$view->map['widths'][$i]=6;
			$value="1";
			$label=$v['Name'];
			$name="perm_" . $v['ID'];
			$options["0"]="Deny";
			$options["1"]="Allow";
			$options["3"]="Inherit";
				
				$key=strtolower($v['Key']);
				if ($this->model->userACL->hasPermission($key) && $rPerms[$key]['inheritted'] != true)
				{
					$value="1";
				}
				
				//if ($rPerms[$v['Key']]['value'] === false && $rPerms[$v['Key']]['inheritted'] != true)
				else if (array_key_exists($key,$rPerms) && $rPerms[$key]['value'] === false && $rPerms[$key]['inheritted'] != true)
				{
					$value="0";
				}
				
				else if (!array_key_exists($key,$rPerms) || $rPerms[$key]['inheritted'] == true)
				{
					$value="3";
					if (array_key_exists($key,$rPerms) && $rPerms[$key]['value'] === true )
					{
						$options["3"].= ' (Allow)';
					} 
					else {
						$options["3"].= ' (Deny)';
					}
				}
			
			$form->addElement(new Element_Select($label, $name, $options,array("value"=>$value)));
			$view->map['layout'][$i/2]=$i%2+1;
			$i++;
		}
		
	//	$form->addElement(new Element_HTML("</div>"));
	//	$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_Hidden("action","savePerms"));
		$form->addElement(new Element_Hidden("userID",$this->model->userACL->userID));
	//	$form->addElement(new Element_HTML("<div class='btn-toolbar' role='toolbar'>"));
	//	$form->addElement(new Element_HTML("<div class=btn-group>"));
	//	$form->addElement(new Element_Button()("<button type=submit class='button light-gray btn btn-primary'>Save</button>"));
	//	$form->addElement(new Element_HTML("</div>"));
	//	$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_Button("Save","",array('class'=>"button light-gray btn btn-primary")));
	
	}
	
	function acl_edit_role_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"acl/roles"
		));
		$roleID=$this->model['roleID']!="new"?$this->model['roleID']:"";
		
		
		$table=null;
		$msg="<p>Permissions to assign not available yet.</p>";
		
		$rPerms = $this->model['userACL']->getRolePerms($roleID);
		$aPerms = $this->model['userACL']->getAllPerms('full');
		if(count($aPerms)>0)
		{
			$table = new sm_Table();
			$table->addHRow();
			$table->addHeaderCell("");
			$table->addHeaderCell("Allow");
			$table->addHeaderCell("Deny");
			$table->addHeaderCell("Ignore");
			$table->addFooterCell("","",array("colspan"=>4));
			foreach ($aPerms as $k => $v)
			{
				$table->addRow();
				$key=strtolower($v['Key']);
				$cell="<label>" . $v['Name'] . "</label>";
				$table->addCell($cell);
				
				$cell="<input type=\"radio\" name=\"perm_" . $v['ID'] . "\" id=\"perm_" . $v['ID'] . "_1\" value=\"1\"";
				if (array_key_exists($key,$rPerms) && $rPerms[$key]['value'] === true && $roleID!= '')
				{ $cell.= " checked=\"checked\""; }
				$cell.=" /></td>";
				$table->addCell($cell);
				
				$cell= "<input type=\"radio\" name=\"perm_" . $v['ID'] . "\" id=\"perm_" . $v['ID'] . "_0\" value=\"0\"";
				if (array_key_exists($key,$rPerms) && $rPerms[$key]['value'] != true && $roleID!= '') 
				{ $cell.= " checked=\"checked\""; }
				$cell.=" /></td>";
				$table->addCell($cell);
				
				$cell= "<input type=\"radio\" name=\"perm_" . $v['ID'] . "\" id=\"perm_" . $v['ID'] . "_X\" value=\"X\"";
				if ($roleID == '' || !array_key_exists($key,$rPerms)) { 
					$cell.= " checked=\"checked\""; }
				$cell.=" /></td>";
				$table->addCell($cell);
			}			
		}
		$form->addElement(new Element_HTML("<fieldset style='margin:5px'>"));
		$form->addElement(new Element_Hidden("action","saveRole"));
		$form->addElement(new Element_Hidden("roleID",$roleID));
		
		$form->addElement(new Element_HTML("<div class='col-xs-12 col-md-12'>"));
		$form->addElement(new Element_Textbox("Name:","roleName",array("shortDesc"=>"<small>Role identifier</small>","value"=>$this->model['userACL']->getRoleNameFromID($roleID))));
		$form->addElement(new Element_Textbox("Description:","description",array("shortDesc"=>"<small>Textual description of role</small>","value"=>$this->model['userACL']->getRoleDescription($roleID))));
		$form->addElement(new Element_HTML("</div>"));
		
		
		
		$form->addElement(new Element_HTML("<div class='col-xs-12 col-md-12'>"));
		$form->addElement(new Element_HTML("<label>Edit Permissions:</label>"));
		$form->addElement(new Element_HTML($table?$table->render():$msg));
		$form->addElement(new Element_HTML("</div>"));
	
		//$form->addElement(new Element_Button("Save","",array("class"=>'button light-gray btn btn-primary')));
		
		
		
		$form->addElement(new Element_HTML("</fieldset>"));
	}
	
	function acl_perm_edit_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"acl/perm"
		));
		$permID=$this->model['permID']?$this->model['permID']:null;	
		$permName= $permID && $permID!="new"? $this->model['userACL']->getPermNameFromID($permID):"";
		$permKey= $permID && $permID!="new"?$this->model['userACL']->getPermKeyFromID($permID):"";
		$description = $permID && $permID!="new"?$this->model['userACL']->getPermDescriptionFromID($permID):"";
		$form->addElement(new Element_HTML("<div class=row>"));
		$form->addElement(new Element_HTML("<div class=col-xs-3>"));
		$form->addElement(new Element_Textbox("Permission Name:","permName",array("shortDesc"=>"<small>Permission identifier</small>","value"=>$permName)));
		$form->addElement(new Element_Textbox("Key:","permKey",array("shortDesc"=>"<small>Short key for permission identifier</small>","value"=>$permKey)));
		$form->addElement(new Element_Textarea("Description:","description",array("shortDesc"=>"<small>Short description for permission</small>","value"=>$description)));
		$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_Hidden("action","savePerm"));
		$form->addElement(new Element_Hidden("permID",$permID));
		$form->addElement(new Element_HTML("<div class='btn-toolbar' role='toolbar'>"));
		$form->addElement(new Element_HTML("<div class=btn-group>"));
		$form->addElement(new Element_HTML("<button type=submit class='button light-gray btn btn-primary'>Save</button>"));
		$form->addElement(new Element_HTML("</div>"));
		
		$form->addElement(new Element_HTML("<div class=btn-group>"));
		$form->addElement(new Element_HTML("<a href='acl/permissions' class='button light-gray btn btn-primary'>Back</a>"));
		$form->addElement(new Element_HTML("</div>"));
		$form->addElement(new Element_HTML("</div>"));
	
	}
	

	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setSubLink("Users","Roles", "acl/roles");
		$menu->setSubLink("Users","Permissions", "acl/permissions");
	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function _onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getId()=="register_user")
		{
			//$form->setSubmitMethod("acl_user_roles_form_submit");
			$table = new sm_Table();
			$table->addHRow();
			$table->addHeaderCell("");
			$table->addHeaderCell("Member");
			$table->addHeaderCell("Not Member");
			$table->addFooterCell("","",array("colspan"=>3));
			$acl = new sm_ACL();
			$rows=array();
			$roles=$acl->getAllRoles('full');
			foreach ($roles as $k => $v)
			{
				$table->addRow();
				//$cell=array();
				$cell="<label>" . $v['Name'] . "</label>";
				$table->addCell($cell);
			
				$cell="<input type=\"radio\" name=\"role_" . $v['ID'] . "\" id=\"role_" . $v['ID'] . "_1\" value=\"1\"";
				$cell.=" /></td>";
				$table->addCell($cell);
					
				$cell= "<input type=\"radio\" name=\"role_" . $v['ID'] . "\" id=\"role_" . $v['ID'] . "_0\" value=\"0\"";
				$cell.= " checked=\"checked\""; 
				$cell.=" /></td>";
					
					
				$table->addCell($cell);
			}
			$ele[]=new Element_HTML('<legend>User Roles</legend>');
			$ele[]=new Element_HTML("<div class=row>");
			$ele[]=new Element_HTML("<div class=col-xs-6>");
			$ele[]=new Element_HTML($table->render());
			$ele[]=new Element_HTML("</div>");
			$ele[]=new Element_HTML("</div>");
			$ele[]=new Element_Hidden("action","saveRoles");
			$form->addElementsBefore($ele,"Save");
		}
	}
}
