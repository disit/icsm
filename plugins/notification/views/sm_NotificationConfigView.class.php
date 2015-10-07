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

class sm_NotificationConfigView extends sm_SystemConfigView
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
		if(is_object($obj) && is_a($obj,"sm_SystemConfigView"))
		{
			$this->extendSystemConfigView($obj);
		}
	}

	public function extendSystemConfigView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			$menu = $userUIView->getMenu();
			$menu->insert("notification",array("url"=>"config/notification","title"=>'Notification',"icon"=>"sm-icon sm-icon-notification"));
			$userUIView->addCss("notification.css","main",sm_NotificationPlugin::instance()->getFolderUrl("css"));
		}
	}
	
	function notification_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"config/notification"
		));
	
	
		foreach($this->model as $m=>$p)
		{
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.$m.'</legend>'));
			foreach($p as $item=>$i)
			{
	
				if(isset($i['type']) && $i['type']=="YesNo")
					$form->addElement(new Element_YesNo($i['description'],$i['name'],array('value'=>$i['value'])));
				else if(isset($i['options']))
				{
					$form->addElement(new Element_Select($i['description'], $i['name'], $i['options'],array('value'=>$i['value'])));
				}
				else
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			$form->addElement(new Element_Button("Save","",array("name"=>"Save","class"=>"button light-gray btn btn-primary")));
			$form->addElement(new Element_HTML('</div>'));
		}
	}
	
	static public function install($db)
	{
		sm_Config::set("NOTIFICATIONMESSAGES",array('value'=>"10","description"=>"Set the max number of message in the alerts bar"));
		sm_Config::set("NOTIFICATIONEMAIL",array('value'=>"","description"=>"E-mail where send alert"));
		sm_Config::set("NOTIFICATIONSENDMAIL",array('value'=>"0","description"=>"Send mail on alert to specified address"));
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete("NOTIFICATIONMESSAGES");
		sm_Config::delete("NOTIFICATIONEMAIL");
		sm_Config::delete("NOTIFICATIONSENDMAIL");
	
	}

}