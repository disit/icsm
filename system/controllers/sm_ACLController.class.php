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

class sm_ACLController extends sm_ControllerElement
{
	protected $model;
	protected $view;
	
	function __construct()
	{
		$this->model = new sm_ACL();
	}
	
		
	/**
	 * Get the acl role & permissions for user
	 *
	 * @url GET /acl/users/:uid
	 * 
	 */
	function acl_users($uid=null)
	{
		
		if($uid)
		{
			$data=array();
			
			$userACL = new sm_ACL($uid);
			$data['userACL']=$userACL;
			$this->view=new sm_ACLView($data);
			$this->view->setOp("user");
		}
		else 
		{
			sm_set_error("ACL: Invalid user data");
			if(isset($_SERVER['HTTP_REFERER']))
				sm_app_redirect($_SERVER['HTTP_REFERER']);
		}
		
	}
	
	/**
	 * Get the acl role & permissions for user
	 * @url GET /acl/user/profile
	 *
	 */
	function acl_user_profile()
	{
		$user = sm_User::current();
		
		if($user)
		{				
			$userACL = new sm_ACL($user);
			$user->userACL=$userACL;
			$this->view=new sm_ACLView();
			$this->view->setModel($user);
			$this->view->setType('acl');
			$this->view->setOp("profile");
		}
		else
		{
			sm_set_error("ACL: Invalid user data");
			if(isset($_SERVER['HTTP_REFERER']))
				sm_app_redirect($_SERVER['HTTP_REFERER']);
		}
	
	}
	
	/**
	 * @desc Get the acl role & permissions for user
	 * 	
	 * @url GET /acl/user/edit/:id
	 *
	 */
	function acl_user_edit($id=null)
	{	
		$user = new sm_User();
		if($id && $user->loadUser($id))
		{
			$userACL = new sm_ACL($user);
			$user->userACL=$userACL;
			$this->view=new sm_ACLView();
			$this->view->setModel($user);
			$this->view->setType('acl');
			$this->view->setOp("edit");
		}
		else
		{
			sm_set_error("ACL: Invalid user data");
			if(isset($_SERVER['HTTP_REFERER']))
				sm_app_redirect($_SERVER['HTTP_REFERER']);
		}
	
	}
	
	/**
	 * Get the acl roles of users
	 *
	 * @url GET /acl/roles/:uid
	 *
	 
	function acl_user_roles($uid=null)
	{
		
		
		if($uid)
		{
			$data=array();
			$userACL = new sm_ACL($uid);
			$data['userACL']=$userACL;
			$this->view=new sm_ACLView($data);
			$this->view->setOp("user_roles");
		}
		else
		{
			sm_set_error("ACL: Invalid user data");
			if(isset($_SERVER['HTTP_REFERER']))
				sm_app_redirect($_SERVER['HTTP_REFERER']);
		}
		
	}*/
	
	/**
	 * Get the acl roles editor and manager
	 *
	 * @url GET /acl/roles
	 *
	 */
	function acl_roles()
	{
		$data=array();
		$userACL = new sm_ACL();
		$pager = new sm_Pager("acl/roles");
		$_totalRows=$userACL->countRoles();
		$pager->set_total($_totalRows);
		$data['pager']=$pager;
		$data['userACL']=$userACL;
		$data['roles']=$userACL->getAllRoles('full',$pager->get_limit());
		
		$this->view=new sm_ACLView($data);
		$this->view->setOp("roles");
	}
	
	/**
	 * Get the acl roles editor and manager
	 *
	 * @url GET /acl/roles/:id
	 * 
	 * @callback
	 */
	function acl_role_edit($id=null)
	{
		$data=array();
		$userACL = new sm_ACL();
		$data['userACL']=$userACL;
		$data['roleID']=$id;
		$this->view=new sm_ACLView($data);
		$this->view->setOp("edit_role");
	}
	
	/**
	 * @desc New/update acl role
	 *
	 * @url POST /acl/roles
	 *
	 */
	function acl_role_save($data)
	{
		$userACL = new sm_ACL();
		if($userACL->saveRole($data))
			sm_set_message("ACL: Role ".$data['roleName']." saved successfully!");
		else
			sm_set_error("ACL: Error when saving Role ".$data['roleName']."!");
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Delete acl role
	 *
	 * @url POST /acl/roles/delete/:id
	 *
	 */
	function acl_role_delete($id=null,$data=null)
	{
		$userACL = new sm_ACL();
	
		if(isset($id))
		{
			$data['roleID']=$id;
			$data['roleName'] = $userACL->getRoleDescription($id);
			if($userACL->deleteRole($data))
				sm_set_message("ACL: Role ".$data['roleName']." deleted successfully!");
			else
				sm_set_error("ACL: Error when deleting Role ".$data['roleName']."!");
		}
				
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * New/update/delete acl role
	 *
	 * @url POST /acl/users/perms
	 * @url POST /acl/users/roles
	 *
	 */
	function acl_users_roles_perms($data)
	{
		$userACL = new sm_ACL();
		switch($data['action'])
		{
			case 'saveRoles':
				//$redir = "?action=user&userID=" . $_POST['userID']
				if($userACL->saveUserRole($data))
					sm_set_message("ACL: Role for ".$userACL->getUsername( $data['userID'])." saved successfully!");
				else
					sm_set_error("ACL: Error when saving Role for ".$userACL->getUsername( $data['userID'])."!");
				
				break;
			case 'savePerms':
				if($userACL->saveUserPerms($data))
					sm_set_message("ACL: Permission for ".$userACL->getUsername( $data['userID'])." saved successfully!");
				else
					sm_set_error("ACL: Error when saving Permission for ".$userACL->getUsername( $data['userID'])."!");
				
				break;
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	

	
	/**
	 * Get the acl permissions editor and manager
	 *
	 * @url GET /acl/permissions
	 *
	 */
	function acl_permissions($op=null)
	{
		$data=array();
		$userACL = new sm_ACL();
		$data['userACL']=$userACL;
		$this->view=new sm_ACLView($data);
		$this->view->setOp("perms");
	}
	
	/**
	 * Get the acl permissions of users
	 *
	 * @url GET /acl/perms/:uid
	 *
	
	function acl_user_perms($uid=null)
	{
		$data=array();
		
		if($uid)
		{
			$userACL = new sm_ACL($uid);
			$data['userACL']=$userACL;
		}
		$this->view=new sm_ACLView($data);
		$this->view->setOp("user_perms");
	}
	 */
	
	/**
	 * Get the acl roles editor and manager
	 *
	 * @url GET /acl/perm/
	 * @url GET /acl/perm/:id
	 *
	 */
	function acl_perm_edit($id=null)
	{
		$data=array();
		$userACL = new sm_ACL();
		$data['userACL']=$userACL;
		$data['permID']=$id;
		$this->view=new sm_ACLView($data);
		$this->view->setOp("edit_perm");
	}
	
	/**
	 * New/update/delete acl role
	 *
	 * @url POST /acl/perm/
	 *
	 */
	function acl_perm_save($data)
	{
		$userACL = new sm_ACL();
	
		switch($data['action'])
		{
			case 'savePerm':
				if($userACL->savePerm($data))
					sm_set_message("ACL: Permission ".$data['permName']." saved successfully!");
				else
					sm_set_error("ACL: Error when saving Permission ".$data['permName']."!");
				
				break;
			case 'delPerm':
				$userACL->deletePerm($data);
				
				break;
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	function onExtendController(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="sm_UserController")
		{
			$this->extendUserController($obj);
		}
	}
	
	function extendUserController(sm_ControllerElement $obj)
	{
		if(!sm_ACL::checkRole("System Administrator"))
			return;
		$curView=$obj->getView();
		if($curView)
		{
			$data=$curView->getModel(); //users
			if($curView->op=='list' && isset($data['records']) )
			{	
				$acl = new sm_ACL();
				foreach($data['records'] as $i=>$d)
				{
					$actions=array();
					if(isset($data['records'][$i]['actions']))
						$actions=$data['records'][$i]['actions'];
					
					unset($data['records'][$i]['actions']);

					$data['records'][$i]['roles']="";
					
					$roles=$acl->getUserRoles($data['records'][$i]['id']);
					$roles_list=array(); 
					foreach($roles as $r)
						$roles_list[]=$acl->getRoleNameFromID($r);
					$data['records'][$i]['roles']=implode(", ",$roles_list);
					$action=array();
					$action[]=array("id"=>"ACL-view-".$d['id'],"method"=>"GET","title"=>"Role & Permissions","url"=>'acl/user/edit/'.$d['id'],"data"=>'<img src="img/acl16.png" />');
					if(!empty($action))
						$data['records'][$i]['actions']=array_merge($action,$actions);
					
				}
				
				$curView->setModel($data);
				return;
			}
		}
	}
	
	
	
}