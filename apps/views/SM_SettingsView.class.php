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

class SM_SettingsView extends sm_ViewElement 
{
	
	function __construct()
	{
		parent::__construct(sm_Config::instance());
		
	}

	public function build() {
		$this->uiView=new sm_Page("SM_SettingsView");
		$this->uiView->setTitle("Settings");
		
		$menu = new sm_NavBar("cView_navbar");
		foreach(array_keys($this->data) as $k=>$title)
			$menu->insert($k,array("url"=>"#".$title,"title"=>str_replace("SM_","",$title),"icon"=>"sm-icon SM-icon-".strtolower(str_replace("SM_","",$title))));
		
		
		$panel = new sm_Panel();
		$panel->setTitle("Supervisor & Monitor Modules");
		$panel->icon("<i class='glyphicon glyphicon-list'></i>");
		$panel->insert($menu);
		
		$this->uiView->insert($panel);
				
		$panel = new sm_Panel("AppicationSettingsPanel");
		$panel->setTitle("Supervisor & Monitor Settings");
		$panel->icon("<i class='glyphicon glyphicon-cog'></i>");
		
		$this->uiView->insert($panel);
		$this->uiView->addJS("config.js");
		//$this->uiView->addCss("SM.css","main",SM_IcaroApp::getFolderUrl("css"));	
		//$this->addView();
	}
	
	
	
	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setSubLink("Settings","Supervisor & Monitor","SM/settings");
	}
}
