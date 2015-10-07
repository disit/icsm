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

class sm_NotificationWidget extends sm_Widget
{
	function __construct()
	{
		parent::__construct();
		$this->setTemplateVar('navbar');
	}
	
	function build()
	{
		if(sm_User::current()->is_loaded())
		{
			$this->uiView = $notification = new sm_HTML();
			$notification->setTemplateId("notification_bar",sm_NotificationPlugin::instance()->getFolder("templates")."notification.tpl.html");
			$notification->addJs("bootbox.js","main","js/bootbox/");
			$notification->addJs("notification.js","main",sm_NotificationPlugin::instance()->getFolderUrl("js"));
			$notification->addCss("notification.css","main",sm_NotificationPlugin::instance()->getFolderUrl("css"));
		}
		//$this->addView();
		
	}
}