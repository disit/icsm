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

DEFINE("CACHETABLENAME","cache");

class sm_Cache implements sm_Module
{
	protected $db;
	protected $cache;
	public function __contruct()
	{
		$this->$db=sm_Database::getInstance();
		$cache=array();
		$this->load();
	}
	
	static public function install($db)
	{
		$sql="CREATE TABLE IF NOT EXISTS `".CACHETABLENAME."` (
		`entry` varchar(128) NOT NULL,
		`value` text NOT NULL,
		`module` text NOT NULL,
		`time` TIMESTAMP NOT NULL DEFAULT NOW(),		
		PRIMARY KEY  (`entry`)
		) 
		ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".CACHETABLENAME." table");
			return true;
		}
		sm_Logger::write("Error when installing ".CACHETABLENAME." table");
		return false;
		
	}
	
	static public function uninstall($db)
	{
		$sql="DROP TABLE `".CACHETABLENAME."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
	
	static function getInstance()
	{
		if(self::$instance==null)
		{
			$c=__CLASS__;
			self::$instance = new $c();
		}
		return self::$instance ;
	}
	
	public function flush()
	{
		unset($this->cache);
		$this->cache=array();
	}
	
	public function load()
	{
		$data=$this->db->select(CACHETABLENAME);
		$this->flush();
		foreach($data as $d=>$v)
		{	
			$this->cache[$v['entry']]=$v['value'];
		}
	}
	
	public function write($entry, $value, $module=null)
	{
		$d = debug_backtrace();
		$caller="";
		if(!isset($module) && isset($d[1]['class']))
			$module = $d[1]['class'];
		$this->delete($entry);
		$this->db->save(CACHETABLENAME,array('entry'=>$entry,'value'=>$value,'module'=>$module));
		$this->cache[$entry]=$value;
		return $this->cache[$entry];
	}
	
	public function get($entry)
	{
		if(isset($this->cache[$entry]))
			return $this->cache[$entry];
		else
			return null;
	}
	
	public function remove($entry)
	{
		unset($this->cache[$entry]);
	}
	
	public function delete($entry)
	{
		$this->remove($entry);
		$this->db->delete(CACHETABLENAME,array('entry'=>$entry));
	}
	
}