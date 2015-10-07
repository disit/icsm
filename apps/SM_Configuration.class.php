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

class SM_Configuration implements sm_Module
{
	//protected $status;
	protected $cid;
	static public $instance;
	protected $configuration;
	public function __construct($id=null)
	{
		//$this->status="Pending";
		$this->configuration = new Configuration();
		$this->cid=null;
		if($id)
		{
			if($this->configuration->load($id))
				$this->cid=$this->configuration->getcid();
		}
	}
	static public function load($id)
	{
		if(self::$instance==null)
			self::$instance = new SM_Configuration();
		if(self::$instance->configuration->load($id))
		{
			self::$instance->cid=$id;
			return self::$instance;
		}
		else
			return null;		
	}
	
	function getConfiguration()
	{
		return $this->configuration;
	}
		
	function apply()
	{
		
	}
	
	public function setcid($mValue)
	{
		$this->cid=$mValue;
		$this->configuration->setcid($mValue);
	}
	
	
	public function __call($method,$args)
	{
		return call_user_func_array(array($this->configuration,$method),$args);
	}
	
	static public function install($db)
	{
	 	return Configuration::install($db);
	 	//return true;
	}
	
	static public function uninstall($db)
	{
	 	//return true;
		return Configuration::uninstall($db);
	}
	
	

}
