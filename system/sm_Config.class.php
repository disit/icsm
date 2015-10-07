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

DEFINE("CONFIGTABLENAME","settings");

class sm_Config implements sm_Module
{
	private $db;
	public $conf;
	static private $instance=null;
	
	function __construct()
	{
		  $this->db = sm_Database::getInstance();
		  $this->conf=null;
		  $this->init();
	}
	
	static function instance(){
		if(!self::$instance)
			self::$instance=new sm_Config();
		return self::$instance;
	}
	
	static function get($name, $default)
	{
		return self::instance()->var_get($name, $default);
	}
	
	static function set($name, $data, $class=null)
	{
		$d = debug_backtrace(); 
		if(!$class && isset($d[1]['class']))
			$data['module']=$d[1]['class'];
		else
			$data['module']=$class;
		return self::instance()->var_set($name, $data);
	}
	
	static function delete($name)
	{
		return self::instance()->var_delete($name);
	}
	
	function var_set($name, $var) {
		$ret=false;
		$result = $this->db->select(CONFIGTABLENAME,array('name' => $name));
		if(!isset($var['description']))
			$var['description']=isset($result[0]['description'])?$result[0]['description']:"n.a";
		if(!isset($var['name']))
			$var['name']=$name;
		if(!isset($var['module']))
			$var['module']=isset($result[0]['module'])?$result[0]['module']:""; 
		
		if(isset($result[0]))
			$result =$this->db->save(CONFIGTABLENAME,$var,array('name' => $name));
		else 
			$result =$this->db->save(CONFIGTABLENAME,$var);
		
		if($result)
		{
			$ret=true;
			$this->conf[$name] = $var;
		}
		return $ret;
	}
	
	function var_get($name,$default)
	{
		return isset($this->conf[$name]['value']) ? $this->conf[$name]['value'] : $default;
	}
	
	function var_description($name)
	{
		return isset($this->conf[$name]['description']) ? $this->conf[$name]['description'] : "";
	}
	
	function save($vars) {
	
		$ret = true;
		foreach($vars as $k=>$var)
		{
		 	$ret|=$this->var_set($k,array("value"=>$var));
		}
		return $ret;
	}
	
	function var_delete($name)
	{
		$result = $this->db->delete(CONFIGTABLENAME,array('name' => $name));
		if($result)
		{
			unset($this->conf[$name]);
		}
	}
	
	function init() {
	
		$this->conf=array();
		$result = $this->db->select(CONFIGTABLENAME);
		$N=count($result);
		for($i=0;$i<$N;$i++) 
		{
			$variable = $result[$i];		
			//$val=unserialize($variable['value']);
			$val = $variable['value'];
			if(is_array($val))
				$this->conf[$variable['name']]['value']=$val;
			else
				$this->conf[$variable['name']]['value'] = stripslashes($val);
			$this->conf[$variable['name']]['description']=$variable['description'];
			$this->conf[$variable['name']]['module']=$variable['module'];
			
		}
		
	}
	
	static public function install($db)
	{
		
		
		$sql="CREATE TABLE IF NOT EXISTS `".CONFIGTABLENAME."` (
		`name` varchar(128) NOT NULL,
		`value` text NOT NULL,
		`description` text NOT NULL,
		`module` text NOT NULL,		
		PRIMARY KEY  (`name`)
		) 
		ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		$result=$db->query($sql);
		if($result)
		{
			include("system/config.inc.php");
			sm_Config::set("HOMEPAGE",array('value'=>"config/system","description"=>"Set the path for Homepage"));
			sm_Config::set("SITE_TITLE",array('value'=>"Site Main Title","description"=>"Site Main Title"));
			sm_Config::set("BASEURL",array('value'=>$baseUrl,"description"=>"The Site Base URL"));
			sm_Config::set("BASEDIR",array('value'=>$baseDir,"description"=>"The Site Base DIR/Folder"));
			sm_Logger::write("Installed ".CONFIGTABLENAME." table");
			return true;
		}
		sm_Logger::write("Error when installing ".CONFIGTABLENAME." table");
		return false;
		
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete("HOMEPAGE");
		sm_Config::delete("SITE_TITLE");
		sm_Config::delete("BASEURL");
		sm_Config::delete("BASEDIR");
		
		$sql="DROP TABLE `".CONFIGTABLENAME."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
	
	function loadSystemConf(){
		include "config.inc.php";
		$data['DBHOST']=sm_Config::get("DBHOST",$dbHost);$dbHost;
		$data['DBUSER']=sm_Config::get("DBUSER",$dbUser);$dbUser;
		$data['DBPWD']=sm_Config::get("DBPWD",$dbPwd);$dbPwd;
		
		//$data['Config']=file_get_contents("system/config.inc.php");
		return $data;
	}
	
	function saveSystemConf($data){
	/*	$f=fopen("system/config.inc.php","wt");
		fwrite($f,preg_replace('/\s*\n+/',"\n",$data['Config']));
		fclose($f);*/
	}
	
}
