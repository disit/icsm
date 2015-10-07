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

class sm_NotificationController extends sm_ControllerElement
{
	protected $model;
	protected $value;
	
	function __construct()
	{
		
		
	}
	
	/**
	 * Gets the notification messages
	 *
	 * @url GET /notification/page
	 * @url GET /notification/page/:type
	 *
	 */
	function notification($type="alert")
	{
		$from = null;
		$to=null;
		$user = sm_User::current();
		$where=array();
		$where[]="to_user = $user->userID" ;
		$where[]="type = '$type'";
		if(!sm_ACL::checkPermission("Notification::View"))
			$where[]="to_user = $user->userID";
		if(isset($_SESSION['notification/page']['from']) && $_SESSION['notification/page']['from']!=0)
		{
			$from=$_SESSION['notification/page']['from'];
			$where[]="unix_timestamp(timestamp) >= ".$from;
		}
		if(isset($_SESSION['notification/page']['to']) && $_SESSION['notification/page']['to']!=0)
		{
			$to=$_SESSION['notification/page']['to'];
			$where[]="unix_timestamp(timestamp) <= ".$to;
		}
		if(count($where)){
			$where = implode(" AND ",$where);
		}
		$notification = new sm_Notification();
		$pager = new sm_Pager("notification/page");
		$_totalRows=$notification->getAllCount($where);
		$pager->set_total($_totalRows);
		$notifications = $notification->getAll( $pager->get_limit(),$where);
		$data=array();
		$data['from']=$from?date("Y-m-d",$from):"";
		$data['to']=$to?date("Y-m-d",$to):"";
		$data['pager']=$pager;
		$data['notifications']=$notifications;
		$data['type']=$type;
		$this->view=new sm_NotificationView($data);
		$this->view->setOp("page");
	}
	
	/**
	 * @desc Update the notification messages on bar
	 *
	 * @url GET /notification/update
	 * 
	 * @callback
	 * 
	 */
	function notification_update()
	{
		$user = sm_User::current();
		$notification = new sm_Notification();
		$notification->to_user = $user->userID;
		$notifications = $notification->getAllUserNotifications($user);
	
		$data=array();
		$data['notifications']=$notifications;
		$data['newcount']=$notification->newcount;
		$this->view=new sm_NotificationView($data);
		$this->view->setOp("update");
	}
	
	
	/**
	 * @desc Write a notification
	 *
	 * @url POST /notification/:type/:from/:to
	 * 
	 * @callback
	 * 
	 */
	function notification_write($type=null,$from=null,$to=null)
	{
		$notification = new sm_Notification();
		$notification->to_user = $to;
		$notification->from_user = $from;
		$notification->type = $type;
		$notification->message = $_REQUEST['message'];
		$value = $notification->handle();
		$this->view=new sm_NotificationView($value);
		$this->view->setOp("response");
		
	}
	
	
	/**
	 * @desc Delete notification callback
	 *
	 * @url POST /notification/delete
	 * 
	 * @callback 
	 */
	function notification_delete($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
				
			$value = sm_Notification::deleteNotification($_id[0])?true:false;
			
		}
		else
			$value = false;
		$this->view=new sm_NotificationView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Delete notification
	 *
	 * @url POST /notification/remove/:id
	 * 
	 */
	function notification_delete_message($id=null)
	{
		if(isset($id) && is_numeric($id))
		{
			if (sm_Notification::deleteNotification($id))
				sm_set_message("Notification: Message #".$id." deleted successfully!");
			else
				sm_set_error(sm_Database::getInstance()->getError());
			
		}
		else
			sm_set_error("Invalid data");
		
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Seen notification
	 *
	 * @url POST /notification/seen
	 * 
	 * @callback
	 */
	function notification_seen($notifications=null)
	{
		if(isset($notifications))
		{
				$user = sm_User::current();
				$_notifications = json_decode($notifications);
				foreach ($_notifications as $notification) {
					if (is_numeric($notification)) sm_Notification::Seen($notification,$user);
				}
				$value = true;
		}
		else
			$value = false;
		$this->view=new sm_NotificationView($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc View Alert/notification
	 *
	 * @url POST /notification/view
	 *
	 * @callback
	 */
	function notification_view_alert($id)
	{
		$data=null;
		if(isset($id))
		{
			$_id = array_keys($id);
				
			$data = sm_Notification::loadNotification($_id[0]);
		}
		$this->view=new sm_NotificationView($data[0]);
		$this->view->setOp("view");
	}
	
	/**
	 * @desc Gets the config/notification
	 *
	 * @url GET /config/notification
	 * 
	 */
	function index()
	{
		$data=array();
		$options=array("5","10");
	
		$data['Notification'][]=array('name'=>'NOTIFICATIONMESSAGES','value'=>sm_Config::get("NOTIFICATIONMESSAGES",""),'options'=>$options,"description"=>"Set the max number of message in the alerts bar");
		$data['Notification'][]=array('name'=>'NOTIFICATIONEMAIL','value'=>sm_Config::get("NOTIFICATIONEMAIL",""),"description"=>"E-mail where send alert");
		$data['Notification'][]=array('name'=>'NOTIFICATIONSENDMAIL','value'=>sm_Config::get("NOTIFICATIONSENDMAIL",0),"description"=>"Send mail on alert to specified address","type"=>"YesNo");
		$this->view = new sm_NotificationConfigView();
		$this->view->setModel($data);
		$this->view->setType("notification");
	
	}
	
	/**
	 * @desc Post the config/system
	 *
	 * @url POST /config/notification
	 */
	function post_config($data)
	{
		if(isset($_POST['NOTIFICATIONMESSAGES']))
		{
			sm_Config::set("NOTIFICATIONMESSAGES",array('value'=>$_POST['NOTIFICATIONMESSAGES'],"description"=>"Set the max number of message in the alerts bar"));
			sm_set_message("Max number of message in the alerts bar successfully saved!");
		}
	
		if(isset($_POST['NOTIFICATIONSENDMAIL']))
		{
			sm_Config::set("NOTIFICATIONSENDMAIL",array('value'=>$_POST['NOTIFICATIONSENDMAIL'],"description"=>"Send mail on alert to specified address"));
			sm_set_message("Send mail on alert to specified address successfully saved!");
		}
		
		if(isset($_POST['NOTIFICATIONEMAIL']))
		{
			sm_Config::set("NOTIFICATIONEMAIL",array('value'=>$_POST['NOTIFICATIONEMAIL'],"description"=>"E-mail where send alert"));
			sm_set_message("E-mail where send alert successfully saved!");
		}
	}
	
	//User Profile 
	/**
	 * @desc Gets the config/notification
	 *
	 * @url GET /notification/user/profile
	 *
	 */
	function user_profile($id=null)
	{	
		$user = sm_User::current();
		$this->view = new sm_NotificationUserView();
		$this->view->setOp("profile");
		$this->view->setType('notification');
		$this->view->setModel($user);
	}
	
	/**
	 * @desc Gets the config/notification
	 *
	 * @url GET /notification/user/edit/:id
	 *
	 */
	function user_edit($id=null)
	{
		$user = new sm_User();	
		if($id && $user->loadUser($id)){
			$this->view = new sm_NotificationUserView();
			$this->view->setOp("edit");
			$this->view->setType('notification');
			$this->view->setModel($user);
		}
		else
		{
			sm_set_error("Notification: Invalid user data");
			if(isset($_SERVER['HTTP_REFERER']))
				sm_app_redirect($_SERVER['HTTP_REFERER']);
		}
	}
	
	
			
	//Event methods
	public function onRemoveUser(sm_Event &$event=null)
	{
		$userID = $event->getData();
		if(sm_Notification::deleteUserNotification($userID))
			sm_set_message("Notification: Removed all notifications for user: ".$userID);
		if(sm_Notification::deleteUserNotificationPreferences($userID))
			sm_set_message("Notification: Removed all notification preferences for user: ".$userID);
			
	}
	
	public function onInsertUser(sm_Event &$event=null)
	{
		$userID = $event->getData();
		$notification = new sm_Notification();
		$notification->saveUserPreferences($userID);

	}
	
	public function onLoadUser(sm_Event &$event=null)
	{
		$user = $event->getData();
		$pref=sm_Notification::loadUserNotificationPreferences($user->userID);
		if(isset($pref[0]))
			$user->userData['notification']['preferences']=$pref[0];
	}
	
	
}
	