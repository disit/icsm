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

class sm_UserObserver  
{
	/*function __construct()
	{
		parent::__construct();
	}*/
	
	public static function bootstrap()
	{	//var_dump(preg_match("/\s+login/\s+",$_SERVER['REQUEST_URI']));
		$dir = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
		$root = ($dir == '.' ? '' : $dir . '/');
		$path = substr(preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']), 0);
		$path = str_replace($root, '', $path);
		$route=explode("/",$path);
		$user = new sm_User();
		if(!$user->is_loaded()) 
		{
			//header("Location:".$root."login");
			if($route[0]!="login")
				sm_App::getInstance()->setRedirection($root."login");
			else 
			{
				include "system/config.inc.php";
				$View = new sm_HTML();
				//$View = new sm_Site();
				$View->setTemplateId('access','access.tpl.html');
				$View->insert('baseUrl',sm_Config::get("BASEURL",$baseUrl));
				$View->addCSS('jquery-ui-1.10.2.custom.css','access','css/smoothness/');
				$View->addJS('jquery-1.11.0.min.js','access');
				$View->addJS('jquery-ui-1.10.2.custom.min.js','access');
				$View->addCSS('login.css','access');
				$View->addJS('form.js','access');
				$View->addJS('login.js','access');
				$View->insert("logo",sm_Config::get("SITE_TITLE", ""));
				sm_View::setMainView($View);
			}
			//exit();
		}	
		else
		{
			if($route[0]=="login")
				sm_App::getInstance()->setRedirection($root);
			else 
				$view=new sm_WelcomeUserWidget();
		}
		//
		
	}
}
