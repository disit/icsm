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

class SM_NagiosView extends sm_ViewElement
{
	function __construct($data=null)
	{
		
		parent::__construct($data);
	}
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_ConfiguratorView")
		{
			$this->extendConfiguratorView($obj);
		}
	}
	
	public function extendConfiguratorView(sm_Widget $obj)
	{

		$data = $obj->getModel();
		if(isset($data['menu']) && $obj->getOp()=="view")
		{
			$userUIView = $obj->getUIView();
			if(is_a($userUIView,"sm_Page"))
			{
				$menu = $userUIView->getMenu();
				$menu->insert("Nagios",array(
					"url"=>"nagios/view/".$data['id'],
					"title"=>"Nagios Data",
					"icon"=>"sm-icon sm-icon-nagios"));
				$userUIView->addCss("monitortool.css","main",SM_NagiosPlugin::instance()->getFolderUrl("css"));
			}
			return;
		}

	}
	
	/**
	 * Create the HTML code for the module.
	 */
	public function build() {
	
		switch($this->getOp())
		{
			case "view":
				$this->nagios_xml_build();
				break;
		}
	}
	
	public function nagios_xml_build(){		
		$panel = new sm_Panel("Nagios");
		$panel->setTitle($this->data['title']);
		$panel->insert($this->data['html']);
		if(isset($this->data['icon']))
		$panel->icon("<img src='".$this->data['icon']."' />");
		//$this->view->setMainView($panel);
		$this->uiView=$panel;
	}
	
	public static function notificationMailRender(SM_Notification &$notification)
	{
		sm_Logger::write("Sending Mail From Nagios");
	}
}