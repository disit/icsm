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

define("PLUGIN_FOLDER",'../plugins');
define("PLUGINMANAGERTABLE","plugins");
define("PLUGIN_INSTALLED",1);
define("PLUGIN_DISABLED",0);

class sm_PluginManager implements sm_Module
{
	static protected $instance;
	protected $plugins;
	protected $pluginPaths;
	protected $db;
	function __construct()
	{
		$this->pluginPaths=array();
		global $pluginPath;
		if(isset($pluginPath))
			$this->pluginPaths=$pluginPath;
		else 
			$this->pluginPaths['plugins']=PLUGIN_FOLDER;
		$this->plugins=array();
		$this->db=sm_Database::getInstance();
	}
	
	
	static function instance()
	{
		if(self::$instance ==null)
		{
			$c=__CLASS__;
			self::$instance=new $c();
		}
		return self::$instance;
	}
	
	static public function install($db)
	{
		$sql="CREATE TABLE IF NOT EXISTS `".PLUGINMANAGERTABLE."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(128) NOT NULL,
			`path` varchar(1024) NOT NULL,
			`description` TEXT NOT NULL,
	  		`version` varchar(128) NOT NULL,
			`status` int(11) DEFAULT '1',
			`class` varchar(128) NOT NULL DEFAULT '',
	  		PRIMARY KEY (`id`),
	  		KEY `name` (`name`)
			)
			ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	
			$result=$db->query($sql);
			if($result)
			{
				sm_Logger::write("Installed ".PLUGINMANAGERTABLE." table");
				return true;
			}
			return false;
	}
	
	static public function uninstall($db)
	{
		$sql="DROP TABLE `".CONFIGTABLENAME."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
	
	public function bootstrap()
	{
		$this->bootstrapPlugins();
		
	}
	
	
	protected function bootstrapPlugins()
	{
		$results=$this->_loadPlugins();
		//$path="";
		//$results=array();
		
		//foreach($this->pluginPaths as $path=>$p)
		//{
		//	$file_names = glob($p."/*/*Plugin.php",GLOB_BRACE);
			/*if( FALSE === $file_names || count($file_names)==0) //FALSE === file_exists($file_name) )
				continue;
			else
				$results=array_merge($results,$file_names);
		}*/
		
		foreach($results as $r=>$p)
		{
			$filename=$p['path'].$p['class'].".php";
			if(file_exists($filename))
			{
				include_once $filename;
				$p['class']::instance()->bootstrap();
			}
		}
	}
	
	static function getPlugin($name)
	{
		if(isset($this->plugins[$name]))
			return $this->plugins[$name];
		return null;
	}
	
	function register(sm_Plugin $p)
	{
		//global $pluginPath;
		//Add plugin path to Plugin Manager
		$this->pluginPath[$p->getPluginRoot()]=$p->getPluginRoot();
		
		//Register plugin to Plugin Manager
		$this->plugins[$p->getPluginName()]=$p;
		
		//Register plugin controllers classes & path
		$controllers=$p->getControllers();
		if(count($controllers)>0)
		{
			sm_Controller::addControllerPath($p->getPluginPath());
			foreach($controllers as $x=>$controller)
				sm_Controller::register($controller);
		}
		
		//Register plugin views path
		$views=$p->getViews();
		if(count($views)>0)
		{
			sm_View::addViewPath($p->getPluginPath());
		}
		
		//Register plugin observers instances	
		$observers=$p->getObservers();
		foreach($observers as $x=>$observer)
			sm_Observer::register($observer);
	}
	
	function getPlugins()
	{
		return $this->plugins;
	}
	
	function installPlugin($pluginName){
		$res =false;
		if(class_exists($pluginName) || $this->includePlugin($pluginName))
		{
			$plugin = new $pluginName();
			if($plugin)
			{
				$res = $plugin->installPlugin(sm_Database::getInstance());
				if(!$this->existsPlugin($pluginName))
					$this->writePlugin($plugin);
			}
		}
		return $res;
		
	}
	
	function disablePlugin($pluginName){
		if(class_exists($pluginName))
		{
			$results=$this->db->save(PLUGINMANAGERTABLE,array("status"=>PLUGIN_DISABLED),array("class"=>$pluginName));
			return $results;
		}
		return false;
	}
	
	function enablePlugin($pluginName){
			$results=$this->db->save(PLUGINMANAGERTABLE,array("status"=>PLUGIN_INSTALLED),array("class"=>$pluginName));
			return $results;
	}
	
	function uninstallPlugin($pluginName){
		$res =false;
		$plugin = new $pluginName();
		if($plugin)
		{
			$res = $plugin->uninstallPlugin(sm_Database::getInstance());
			$this->deletePlugin($plugin);
		}
		return $res;
	}
	
	protected function writePlugin(sm_Plugin $plugin)
	{
		$plugin_data['name']=$plugin->getPluginName();
		$plugin_data['version']=$plugin->getPluginVersion();
		$plugin_data['description']=$plugin->getPluginDescription();
		$plugin_data['path']=$plugin->getPluginPath();
		$plugin_data['class']=get_class($plugin);
		$this->db->save(PLUGINMANAGERTABLE,$plugin_data);
	}
	
	public function detectPlugins()
	{
		$results=array();
		
		foreach($this->pluginPaths as $path=>$p)
		{
			$file_names = glob($p."/*/*Plugin.php",GLOB_BRACE);
			if( FALSE === $file_names || count($file_names)==0) //FALSE === file_exists($file_name) )
			 	continue;
			else
			{
				foreach($file_names as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);					
					if(!$this->existsPlugin($class))
					{
						include_once $c;
						$results[]=new $class;
					}
				}
			}
		}
		return $results;
	}
	
	public function includePlugin($name)
	{
		$found=false;
		foreach($this->pluginPaths as $path=>$p)
		{
			$file_names = glob($p."/*/*Plugin.php",GLOB_BRACE);
			if( FALSE === $file_names || count($file_names)==0) //FALSE === file_exists($file_name) )
				continue;
			else
			{
				foreach($file_names as $c)
				{
					$class=explode(".",$c);
					//$class=str_replace($v, "", $class[0]);
					$class=substr($class[0],strripos($class[0], "/")+1);
					if($class==$name)
					{
						include_once $c;
						$found=true;
						break;
					}
				}
			}
			if($found)
				break;
		}
		return $found;
	}
	
	public function listPlugins()
	{
		$results=$this->db->select(PLUGINMANAGERTABLE);
		return $results;
	}
	
	protected function _loadPlugins()
	{
		$results=$this->db->select(PLUGINMANAGERTABLE,array("status"=>1));
		return $results;
	}
	
	protected function existsPlugin($class)
	{
		$results=$this->db->select(PLUGINMANAGERTABLE,array("class"=>$class));
		return count($results)>0;
	}
	
	protected function deletePlugin(sm_Plugin $plugin)
	{
		$this->db->delete(PLUGINMANAGERTABLE,array("name"=>$plugin->getPluginName()));
	}
}