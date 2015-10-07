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

class sm_NotificationUserView extends sm_UserView
{
	function __construct($data=null)
	{
		parent::__construct($data);
		/*if(!isset($this->model['widget_type']))
		 $this->model['widget_type']="bar";*/
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
		if(is_a($userUIView,"sm_Page") && ($obj->getOp()=="profile" || $obj->getOp()=="edit"))
		{
			$user=$obj->getModel();
			$uid = $user->userData['id'];
			$menu = $userUIView->getMenu();
			if($obj->getOp()=="profile" )
				$menu->insert("notification",array("url"=>"notification/user/profile","title"=>'Notification',"icon"=>"sm-icon sm-icon-notification"));
			else 
				$menu->insert("notification",array("url"=>"notification/user/edit/".$uid,"title"=>'Notification',"icon"=>"sm-icon sm-icon-notification"));
			$userUIView->addCss("notification.css","main",sm_NotificationPlugin::instance()->getFolderUrl("css"));
	
		}
	}
	
	public function user_edit_panel(){
			$panel = new sm_Panel();
			$panel->setTitle('Notification');
			$panel->icon(sm_formatIcon("bell"));
			$panel->insert(sm_Form::buildForm('notification_user_preferences',$this));
			return $panel;
		
	}
	
	
	function notification_user_preferences_form($form){
		$view = new View_Grid();
		
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => $view,
				"labelToPlaceholder" => 0,
				//"action"=>"user/new"
		));
		$view->map['layout']=array(2,1);
		$view->map['widths']=array(6,6,6);
		$user = $this->model->userData;
		$form->addElement(new Element_HTML('<legend>Preferences</legend>'));
		$form->addElement(new Element_Hidden("uid",$user['id']));
		
		$form->addElement(new Element_Textbox("Messages","NOTIFICATIONMESSAGES",
				array('value'=>isset($user['notification']['preferences']['NOTIFICATIONMESSAGES'])?$user['notification']['preferences']['NOTIFICATIONMESSAGES']:sm_Config::get('NOTIFICATIONMESSAGES', 0),
						"shortDesc"=>sm_Config::instance()->var_description("NOTIFICATIONMESSAGES"))));
		$form->addElement(new Element_YesNo("Send Mail","NOTIFICATIONSENDMAIL",
				array('value'=>isset($user['notification']['preferences']['NOTIFICATIONSENDMAIL'])?$user['notification']['preferences']['NOTIFICATIONSENDMAIL']:"No",
						"shortDesc"=>sm_Config::instance()->var_description("NOTIFICATIONSENDMAIL"))));
		$form->addElement(new Element_Select("Clean Messages","NOTIFICATIONCLEAN",
				array('daily','weekly','monthly','always'),
				array('value'=>isset($user['notification']['preferences']['NOTIFICATIONCLEAN'])?$user['notification']['preferences']['NOTIFICATIONCLEAN']:"always",
						"shortDesc"=>sm_Config::instance()->var_description("NOTIFICATIONCLEAN"))));
	
		$form->addElement(new Element_Button("Save","",array('class'=>"button light-gray btn btn-primary")));
	
	}
	
	function notification_user_preferences_form_submit($data)
	{
		$preferences=$data;
		unset($preferences['form']);
		unset($preferences['uid']);
		$notification = new sm_Notification();
		if($notification->saveUserPreferences($data['uid'],$preferences))
			sm_set_message("Notification Preferences successfully saved!");
	}
}