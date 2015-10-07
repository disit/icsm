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

class sm_UserController extends sm_ControllerElement
{
	protected $model;
	protected $view;

	function __construct()
	{
		$this->model = new sm_User();
		$this->view = new sm_UserView();		
	}


	/**
	 * Gets the user method :op
	 *
	 * @url GET /login
	 * 
	 * 
	 */
	function login()
	{
		$this->view->setOp('access');
	}
	/**
	 * Gets the user method :op
	 *
	 * @url GET /login/form
	 * 
	 * @callback
	 */
	function form()
	{
		$this->view->setOp('form');
	}
	
	/**
	 * @desc Gets the user method :op
	 *
	 * @url GET login/register
	 * 
	 * @callback
	 */
	function register()
	{
		$this->view->setOp('register');
	}
	
	/**
	 * @desc Gets the user method :op
	 *
	 * @url GET /user/new
	 */
	function insert()
	{
		$this->view->setOp('new');
	}
	
	/**
	 * @desc Gets the user method :op
	 *
	 * @url POST /user/remove/:id
	 */
	function remove($id=null)
	{
		if($id){
			$this->model->loadUser($id);
			$username = $this->model->get_property("username");
			if($this->model->removeUser($id))
			{
				sm_set_message(sprintf("User %s (%d) removed successfully!",$username,$id));
			}
			else
				sm_set_error(sprintf("An error occurs when removing User %s (%d)!",$username,$id));
		}
		else
			sm_set_error("Invalid User Id");
		$this->setOp('delete');
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Delete user callback
	 *
	 * @url POST /user/delete
	 *
	 * @callback
	 */
	function user_delete($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
			$value = $this->model->removeUser($_id[0])?true:false;	
		}
		else
			$value = false;
		$this->view=new sm_UserView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Activate/Enable user callback
	 *
	 * @url POST /user/activate
	 *
	 * @callback
	 */
	function user_activate($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
			$value = $this->model->enableUser($_id[0])?true:false;
		}
		else
			$value = false;
		$this->view=new sm_UserView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Ban/Disable user callback
	 *
	 * @url POST /user/ban
	 *
	 * @callback
	 */
	function user_ban($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
			$value = $this->model->disableUser($_id[0])?true:false;
		}
		else
			$value = false;
		$this->view=new sm_UserView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Gets the user method :op
	 *
	 * @url POST /user/enable/:id
	 */
	function enable($id=null)
	{
		if($id){
			$this->model->loadUser($id);
			$username = $this->model->get_property("username");
			if($this->model->enableUser($id))
			{
				sm_set_message(sprintf("User %s (%d) enabled successfully!",$username,$id));
			}
			else
				sm_set_error(sprintf("An error occurs when enabling User %s (%d)!",$username,$id));
		}
		else
			sm_set_error("Invalid User Id");
		$this->setOp('enable');
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the user method :op
	 *
	 * @url POST /user/disable/:id
	 */
	function disable($id=null)
	{
		if($id){
			$this->model->loadUser($id);
			$username = $this->model->get_property("username");
			if($this->model->disableUser($id))
			{
				sm_set_message(sprintf("User %s (%d) disabled successfully!",$username,$id));
			}
			else
				sm_set_error(sprintf("An error occurs when disabling User %s (%d)!",$username,$id));
		}
		else
			sm_set_error("Invalid User Id");
		$this->setOp('disable');
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
		
	/**
	 * @desc Gets the user method :op
	 *
	 * @url GET /user/list
	 * 
	 * @perms User::View
	 */
	
	function show()
	{
		//$limit=10;
		$keywords="";
		if(isset($_SESSION['user/list']['keywords']))
			$keywords=$_SESSION['user/list']['keywords'];
		$where=array();
		/*	if($status>=0)
		 $where['status']="status='".$status."'";*/
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(users.username like '%".$k."%' OR users.email like '%".$k."%')";
			}
		
		}
		if(!empty($where))
			$where = implode("AND",$where);
		$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("user/list");
		$pager->set_total($_totalRows);
		//calling a method to get the records with the limit set
		$data['records'] = $this->model->getAll( "*",$where,$pager->get_limit() );
		if(sm_ACL::checkPermission("User::Edit"))
		{
			foreach($data['records'] as $i=>$d)
			{
				//$data['records'][$i]['created']=date("d-M-Y H:i:s",$d['created']);
				if($d['active'])
					$data['records'][$i]['actions'][]=array("id"=>"user-disable-".$d['id'],"title"=>"Disable User","url"=>'user/disable/'.$d['id'],"data"=>"<img src='img/deny.png' />","method"=>"POST");
				else
					$data['records'][$i]['actions'][]=array("id"=>"user-enable-".$d['id'],"title"=>"Enable User","url"=>'user/enable/'.$d['id'],"data"=>"<img src='img/allow.png' />","method"=>"POST");
				$data['records'][$i]['actions'][]=array("id"=>"user-delete-".$d['id'],"title"=>"Edit User","url"=>'user/edit/'.$d['id'],"data"=>"<img src='img/details.gif' />","method"=>"GET");
				$data['records'][$i]['actions'][]=array("id"=>"user-delete-".$d['id'],"title"=>"Delete User","url"=>'user/remove/'.$d['id'],"data"=>"<img src='img/delete.png' />","method"=>"POST");
				unset($data['records'][$i]['password']);
			}
		}
		$data['pager'] = $pager;
		$data['keywords'] = $keywords;
		$this->view->setOp('list');
		$this->view->setModel($data);
	}
	
	
	/**
	 * @desc Gets the user edit profile 
	 *
	 * @url GET /user/edit/:id
	 * 
	 * @perms User::Edit
	 * 
	 */
	function edit($id=null)
	{
		$user = new sm_User();
		if(isset($id) && $user->loadUser($id))
		{
			$this->view->setModel($user);
			$this->view->setOp('edit');
			$this->view->setType('profile');
			return;
		}	
		sm_set_error("User: Invalid user data");
		sm_app_redirect("user/list");
	}
	
	/**
	 * @desc Gets the user profile 
	 *
	 * @url GET /user/profile
	 * 
	 */
	function profile()
	{
		
		$user = sm_User::current();
		if($user){
			$this->view->setModel($user);
			$this->view->setOp('profile');
			$this->view->setType('profile');
		}
		
	}
		
	/**
	 * @desc Gets the user logging out
	 *
	 * @url GET /user/logout
	 * 
	 */
	public function logout()
	{
		$this->model->logout("./");
		//exit();
	}
	
	/**
	 * @desc Do Login
	 *
	 * @url POST /login
	 * @callback
	 */
	function doLogin($data)
	{
		//$data = $_POST;
		
		if(isset($data['nameuser']))
		{
	
			$remember=isset($data['remember']) && $data['remember'];
		  
			if ($this->model->login( $data['nameuser'], $data['passuser'], $remember ))
			{
				$successURL = './';
				$ret=array(
						'success'=> true,
						'title'=>'<strong>Login Success</strong>',
						'content'=>'You have authenticated successfuly<br />'.
						'click <a href="'.$successURL.'">here</a> to continue',
						'redirect'=>$successURL
	
				);
			}
			else
			{
				$ret=array(
						'success'=>false,
						'title'=>'<strong>Login Failed : User and Password combination is not valid</strong>',
				);
	
			}
			$this->view->setModel($ret);
			$this->view->setOp('login');
			
		}
		
		//exit();
	}
	
	/**
	 * Gets the user method :op
	 *
	 * @url POST user/register
	 * @url POST login/register
	 * @callback
	 */
	function doRegister($data)
	{
		
		//$data = $_POST;
		if(isset($data['username']))
		{
		
			//$remember=isset($data['remember']) && $data['remember'];
		
			if ($this->model->insertUser($data))
			{
				$successURL = './';
				$ret=array(
						'succes'=> true,
						'title'=>'<strong>Registration Success</strong>',
						'content'=>'You have been registrated successfuly<br />'.
						'click <a href="'.$successURL.'">here</a> to continue',
						'redirect'=>$successURL
		
				);
				$this->setOp("registration");
			}
			else
			{
				$error=sm_get_error();
				$ret=array(
						'succes'=>false,
						'title'=>$error,
				);
		
			}
			$this->view->setModel($ret);
			$this->view->setOp('login');
		}
		
		//exit();
	}
	
	
	
	

}
