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

class sm_Installer extends sm_App
{
	function __construct() {
	//	session_start();
		parent::__construct();
	//	$_SERVER['REQUEST_URI']="config/system";
		$this->observer->unregister('sm_UserObserver');
		$this->view->unregister('sm_Menu');
		self::$instance=$this;
		$this->module="";
		$this->plugins->bootstrap();
	}
	
	function setMode($mode)
	{
		$this->op=$mode;
	}
	
	function setModule($module)
	{
		$this->module=$module;
	}
	
	function handle(){
		parent::handle();
	}
	
	function execute()
	{
		$type=!empty($this->op)?$this->op:"";
		if($type=="install")
			$this->do_install();
		if($type=="install_app")
			$this->do_install("apps");
		else if($type=="install_plugins")
			$this->do_install("plugin");
		else if($type=="uninstall")
			$this->do_uninstall();
		else if($type=="update_plugin")
			$this->do_update("plugin");
		else if($type=="update")
			$this->do_update();
		else if($type=="update_app")
			$this->do_update("apps");
		else if($type=="menu")
			$this->do_install("menu");
		
	}
	
	function do_install($what="system")
	{
		
		include("system/config.inc.php");
		sm_Logger::$usedb=false;
		$db = sm_Database::getInstance();
		$db->initialize($dbHost, $dbUser, $dbPwd);
		$db->setDB($dbName);
		if($what=="system" || $what=="apps")
		{
			$paths=$classPath;
			foreach($paths as $p=>$v)
			{
				if($p!=$what)
					continue;
				sm_Logger::write($v);
				$sys=glob($v."{,controllers/,views/}{*class.php}", GLOB_BRACE); 
				$args=array($db);
				foreach($sys as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					if($class=="sm_Module")
						continue;
					
					if(method_exists($class, "install"))
					{
						sm_Logger::write($class."::install");
						call_user_func_array(array($class, "install"), $args);
					}
							
				}	
			}	
		}
		if($what=="menu" || $what=="system")
		{
			$paths=$classPath;
			foreach($paths as $p=>$v)
			{
				sm_Logger::write($v);
				$sys=glob($v."{,controllers/,views/}{*class.php}", GLOB_BRACE);
				$manager=sm_MenuManager::instance();
				$args=array($manager);
				foreach($sys as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					if($class=="sm_Module")
						continue;
						
					if(method_exists($class, "menu"))
					{
						sm_Logger::write($class."::menu");
						call_user_func_array(array($class, "menu"), $args);
					}
						
				}
			}
		}
		if($what=="plugin")
		{
			$paths=$pluginPath;
			foreach($paths as $p=>$v)
			{
				sm_Logger::write($v);
				$sys=glob($v."*/{*Plugin.php}", GLOB_BRACE);
				$args=array($db);
				foreach($sys as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					if(!class_exists($class))
						include $c;
					$this->plugins->installPlugin($class);
					/*if(method_exists($class, "install"))
					{
						sm_Logger::write($class."::install");
						call_user_func_array(array($class, "install"), $args);
					}*/
			
				}
			}
		}
	}
	
	
	function do_update($what="system")
	{
	
		include("system/config.inc.php");
		sm_Logger::$usedb=false;
		$db = sm_Database::getInstance();
		$db->initialize($dbHost, $dbUser, $dbPwd);
		$db->setDB($dbName);
		if($what=="system" || $what=="apps")
		{
			$paths=$classPath; 
			foreach($paths as $p=>$v)
			{
				if($p!=$what)
					continue;
				sm_Logger::write($v);
				$sys=glob($v."{,controllers/,views/}{".$this->module."*class.php}", GLOB_BRACE);
				$args=array($db);
				foreach($sys as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					if($class=="sm_Module")
						continue;
						
					if(method_exists($class, "install"))
					{
						sm_Logger::write($class."::install");
						call_user_func_array(array($class, "install"), $args);
					}
						
				}
			}
		}
		if($what=="plugin")
		{
			$paths=$pluginPath;
			foreach($paths as $p=>$v)
			{
				sm_Logger::write($v);
				$sys=glob($v."*/{".$this->module."Plugin.php}", GLOB_BRACE);
				$args=array($db);
				foreach($sys as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					
					if(!class_exists($class))
						include $c;
					$this->plugins->installPlugin($class);
					/*if(method_exists($class, "install"))
					 {
					sm_Logger::write($class."::install");
					call_user_func_array(array($class, "install"), $args);
					}*/
						
				}
			}
		}
	}
	
	function do_uninstall()
	{
		
		include("system/config.inc.php");
		sm_Logger::$usedb=false;
		$db = sm_Database::getInstance();
		$db->initialize($dbHost, $dbUser, $dbPwd);
		$db->setDB($dbName);
		
		$paths=$pluginPath;
		foreach($paths as $p=>$v)
		{
			sm_Logger::write($v);
			$sys=glob($v."*/{*Plugin.php}", GLOB_BRACE);
			$args=array($db);
			foreach($sys as $c)
			{
				$class=explode(".",$c);
				//$class=str_replace($v, "", $class[0]);
				$class=substr($class[0],strripos($class[0], "/")+1);
				include $c;
				$this->plugins->uninstallPlugin($class);
				/*if(method_exists($class, "install"))
				 {
				sm_Logger::write($class."::install");
				call_user_func_array(array($class, "install"), $args);
				}*/
					
			}
		}
		
		foreach($classPath as $p=>$v)
		{
			$sys=glob($v."{*class.php}", GLOB_BRACE); 
			$args=array($db);
			foreach($sys as $c)
			{
				$class=explode(".",$c);
				$class=str_replace($v, "", $class[0]);
				if($class=="sm_Module")
					continue;
				sm_Logger::write($class."::uninstall");
				if(method_exists($class, "uninstall"))
					call_user_func_array(array($class, "uninstall"), $args);
						
			}	
		}	
	}
	
/*	static function form_extend(sm_Form $f)
	{
		if(self::$instance instanceof sm_Installer)
		{
			if($f->getId()=="system_config")
			{
				$form_element=$f->getElement("Save");
				if($form_element instanceof Element_Button)
					$form_element->setAttribute("value", "Install");
				//$f->setSubmitMethod("installer_form_submit");
				
			}
		}
	}*/
	
}
