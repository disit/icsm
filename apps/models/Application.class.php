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

/*******************************************************************************
 * Class Name:       Application
* File Name:        Application.class.php
* Generated:        Friday, Aug 2, 2013 - 11:46:09 CEST
*  - for Table:     application
*   - in Database:  icaro
* Created by:
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "Application"
class Application extends modApplication{
	// Variable declaration
	protected $services;
	// Class Constructor
	public function __construct($aid=null) {
		parent::__construct();
		$this->services=array();
		if($aid)
		{
			$this->load($aid);	
		}
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
		if($this->services)
		{
			foreach($this->services as $i=>$service)
			{
				$service->__destruct();
			}
		}
	}
	
	public function load($aid)
	{
		if($aid)
		{
			$this->select($aid);
			$this->services=Service::getAll(array("aid"=>$aid));
		}
	}
	
	static public function getAll($where) //($data)
	{		
		if( !is_array($where) )
			return null;
		$db=sm_Database::getInstance();
		$whereCond="";
		if(!empty($where))
			$whereCond=$db->buildWhereClause("application", $where);
		$sql="SELECT aid FROM application ".$whereCond;
		$result=$db->query($sql);
		if($result)
		{
			foreach($result as $r=>$s)
			{
				$obj=new Application($s['aid']);
				$all[]=$obj;
			}
			return $all;
		}
		return null;
	}
	
	function write($a)
	{
		foreach(get_object_vars($a) as $prop => $value)
		{
			$name = $prop;
			if($name[0]=="@")
			{
				//$name=substr($name,1);
				$this->write($value);
			}
			elseif($name=="services")
			{
				$this->services=array();
				if(isset($value->service)){
					$services=array();
					if(!is_array($value->service))
						$services[]=$value->service;
					else 
						$services=$value->service;
					foreach($services as $i=>$s)
					{
						$service = new Service();
						$service->write($s);
						$this->services[]= $service;
					}	
				}
			}
			else if(property_exists($this,$name))
						$this->$name =$value;
		}
	}
	
	public function insert(){
		parent::insert();
		foreach($this->services as $i=>$service)
		{
			$service->setaid($this->aid);
			$service->insert();
		}
	}
	public function delete($ref){
	if(is_array($this->services))
		foreach($this->services as $i=>$service)
		{
			$service->delete($service->getsid());
		}
		
		//parent::delete($ref);
	}
	
	public function build()
	{
		$result=sm_obj2array($this);
		unset($result['database']);
		unset($result['services']);
		foreach($this->services as $i=>$service)
		{
			$result['services']['service'][]=$service->build();
		}
		$result['@attributes']=array("cid"=>$result['cid'],"aid"=>$result['aid']);
		unset($result['cid']);
		unset($result['aid']);
		return $result;
	}
	
	function &getService($id)
	{
		$res = null;
		foreach($this->services as $i=>$service)
		{
			if($service->getsid()==$id)
				return $service;
		}
		return $res;
	}
	
	function addService(Service $service)
	{
		$this->services[]= $service;
	}
	
	function getServices()
	{
		return $this->services;
	}
	
	function getMyHost($host_ip)
	{
		foreach ($this->services as $s=>$service)
		{
			$host=$service->getHost();
			$ip=$service->getip_address();
			if(!empty($ip) && $host_ip==$ip)
				return $host;
			else
			{
				foreach(explode(";", $host->getip_address()) as $i=>$p)
					if($host_ip==$p)
						return $host;
			}
		}
		return null;
	}

}
// End Class "Application"
?>