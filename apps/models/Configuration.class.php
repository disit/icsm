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
* Class Name:       Configuration
* File Name:        Configuration.class.php
* Generated:        Friday, Aug 2, 2013 - 10:28:01 CEST
*  - for Table:     configuration
*   - in Database:  icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.
DEFINE("CONFIGURATIONDBTABLE","configuration");

// Begin Class "Configuration"
class Configuration extends modConfiguration //implements sm_Module
{
	// Variable declaration
	
	//segments
	protected $segment;
	
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->segment=array();
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	public function saveXML()
	{
		$conf=json_decode(json_encode(array('configuration'=>$this->build("*"))),true);
		$keys=array_keys($conf);
		return Array2XML::createXML($keys[0],$conf[$keys[0]])->saveXML();
	}
	
	public function load($cid)
	{
		if(!$this->select($cid))
			return false;
		
		$this->segment['applications']=Application::getAll(array('cid'=>$this->cid));
		$this->segment['tenants']=Tenant::getAll(array('cid'=>$this->cid));
		$this->segment['devices']=Device::getAll(array('cid'=>$this->cid));
		$this->segment['hosts']=Host::getAll(array('cid'=>$this->cid));
		
		return true;	
	}
	
	public function add($component)
	{
			if(is_a($component, "Application"))
				$this->segment['applications'][]=$component;
		
			else if(is_a($component,"Tenant"))
				$this->segment['tenants'][]=$component;
				
			else if(is_a($component,"Device"))
				$this->segment['devices'][]=$component;
				
			else if(is_a($component,"Host"))
				$this->segment['hosts'][]=$component;
	}
	
	public function save()
	{
		$this->insert();
		foreach($this->segment as $type=>$comp)
			foreach($comp as $i=>$d)
			{
				$d->setcid($this->cid);
				$d->insert();
			}
	}
	
	public function validate($context=null)
	{
		$result=true;
		foreach($this->segment as $type=>$comp)
		{
			foreach($comp as $i=>$d)
			{
				$result &=$d->validate($this);
			}
		}
		return $result;
	}
	
	public function delete($cid=null)
	{
		if(!$cid)
			$cid=$this->getcid();
		SM_Logger::write("Deleting devices");
		if(isset($this->segment['devices']))
		{
			foreach($this->segment['devices'] as $i=>$d)
			{
				SM_Logger::write("Deleting device ".$d->getdid());
				$d->delete($d->getdid());
			}
		}
		SM_Logger::write("Deleting tenants");
		if(isset($this->segment['tenants']))
		{
			foreach($this->segment['tenants'] as $i=>$d)
			{
				SM_Logger::write("Deleting tenant ".$d->gettid());
				$d->delete($d->gettid());
			}
		}
		SM_Logger::write("Deleting hosts");
		if(isset($this->segment['hosts']))
		{
			foreach($this->segment['hosts'] as $i=>$d)
			{
				SM_Logger::write("Deleting host ".$d->gethid());
				$d->delete($d->gethid());
			}
		}
		SM_Logger::write("Deleting applications");
		if(isset($this->segment['applications']))
		{
			foreach($this->segment['applications'] as $i=>$d)
			{
				SM_Logger::write("Deleting application ".$d->getaid());
				$d->delete($d->getaid());
			}
		}
		parent::delete($cid);
	}
	
	public function parse($xml)
	{
		$data = json_decode(json_encode(XML2Array::createArray($xml)));
		$this->write($data->configuration);
	}
	
	public function build($components=null,$filter=null)
	{
		$result=array
		(
				"@attributes"=>array("cid"=>$this->getcid()),
				"identifier"=>$this->getidentifier(),
				"description"=>$this->getdescription(),
				"name"=>$this->getname(),
				"contacts"=>$this->getcontacts(),
				"type"=>$this->gettype(),
				"bid"=>$this->getbid(),
		);
		
		$segment=$this->getSegment($components,$filter);
		//return $this->getSegment($components);
		if(isset($segment))
		{
			
			$segments=array();
			foreach($segment as $s=>$seg)
			{
				if(!$seg)
					continue;
				$data_segment=array();
				if(!is_array($seg))
					$data_segment[]=$seg;
				else
					$data_segment=$seg;
				foreach($data_segment as $item=>$object)
				{
		
					$array=$object->build();
					$segments[$s][strtolower(get_class($object))][]=$array;
		
				}
			}
			$result=array_merge($result,$segments);
		}
		return $result;
	}

	public function get($components)
	{
		if($components!="")
				return $this->segment[$components];
		else
				return null;
		
	}
	
	function write($o)
	{
		$this->segment=array();
		
 		if(isset($o->description))
			$this->setdescription($o->description);
		if(isset($o->contacts))
			$this->setcontacts($o->contacts);
		if(isset($o->name))
			$this->setname($o->name);
		if(isset($o->identifier))
			$this->setidentifier($o->identifier);
		if(isset($o->type))
			$this->settype($o->type);
		if(isset($o->bid))
			$this->setbid($o->bid);
		if(isset($o->{'@attributes'}) && isset($o->{'@attributes'}->cid))
			$this->setcid($o->{'@attributes'}->cid);
		
		if(isset($o->applications->application))
		{
			$apps = array();
			if(!is_array($o->applications->application))
				$apps[]=$o->applications->application;
			else
				$apps=$o->applications->application;
				
			foreach($apps as $i=>$v)
			{
				$d = new Application();
				$d->write($v);
				$this->segment['applications'][]=$d;
			}
		}
		if(isset($o->tenants->tenant))
		{
			$tenants = array();
			if(!is_array($o->tenants->tenant))
				$tenants[]=$o->tenants->tenant;
			else
				$tenants=$o->tenants->tenant;
			foreach($tenants as $i=>$v)
			{
				$d = new Tenant();
				$d->write($v);
				$this->segment['tenants'][]=$d;
			}
		}
		if(isset($o->hosts->host))
		{
			$hosts = array();
			if(!is_array($o->hosts->host))
				$hosts[]=$o->hosts->host;
			else
				$hosts=$o->hosts->host;
			foreach($hosts as $i=>$v)
			{
				$d = new Host();
				$d->write($v);
				$this->segment['hosts'][]=$d;
			}
		}
		if(isset($o->devices->device))
		{
			$device = array();
			if(!is_array($o->devices->device))
				$device[]=$o->devices->device;
			else
				$device=$o->devices->device;
			foreach($device as $i=>$v)
			{
				$d = new Device();
				$d->write($v);
				$this->segment['devices'][]=$d;
			}
		}
		
	}
	
	
	
	public function getSegment($components=NULL,$filter=null)
	{
		if(isset($components) && $components!="*")
		{
			if(isset($this->segment[$components]))
			{
				if(isset($filter))
				{	
					$filter_element=explode(":", $filter);
					foreach($this->segment[$components] as $i=>$element)
					{	
						if(method_exists($element, "get".$filter_element[0]) && call_user_func_array(array($element,"get".$filter_element[0]),array())==$filter_element[1])
						{	 
							return array($components=>$element);
						}
					}
				}
				else
					return array($components=>$this->segment[$components]);
			}
			else if($components == "services" && isset($this->segment["applications"]))
			{
				 foreach($this->segment["applications"] as $i=>$element)
				 {
					 $filter_element=explode(":", $filter);
					 $seg = $element->getService($filter_element[1]);
					 if($seg)
					 {
						 return array($components=>$seg);
						
					 }
				 }
			 }
			else
				return null;
		}    
		//else
			return $this->segment;
	}
	
	public function &getSegmentObj($components=NULL,$filter=null)
	{
		if(isset($components) && $components!="*" && isset($filter))
		{
			if(isset($this->segment[$components]))
			{
				if(isset($filter))
				{
					$filter_element=explode(":", $filter);
					foreach($this->segment[$components] as $i=>$element)
					{
						if(call_user_func_array(array($element,"get".$filter_element[0]),array())==$filter_element[1])
						{
							return $element;
						}
					}
				}
			}
		}
		return null;
	}
	
	static public function install($db)
	{
		include(__DIR__."/sql/db_install.sql.php");
		foreach($sql as $i=>$q)
		{
			$result=$db->query($q);
		
			if($result)
			{
				sm_Logger::write("Installed table #".$i);
				
			}
			else
				sm_Logger::write($db->getError());
		}
		
		return true;
		
	}
	
	static public function uninstall($db)
	{
		include(__DIR__."/sql/db_uninstall.sql.php");
		foreach($sql as $i=>$q)
		{
			$result=$db->query($q);
		
			if($result)
			{
				sm_Logger::write("Uninstalled table #".$i);
				
			}
			else
				sm_Logger::write("Error when uninstalling table #".$i);
		}
		
		return true;
	}
	
	public function getAllCount($where=array())
	{
		$whereCond="";
		if(!empty($where))
			$whereCond=$this->database->buildWhereClause(CONFIGURATIONDBTABLE, $where);
		$r=$this->database->query("SELECT COUNT(*) as count from `".CONFIGURATIONDBTABLE."` ".$whereCond);
		return $r[0]['count'];
	}
	
	public function getAll($limit=null, $where=array(),$fields=array())
	{
		
		if(isset($limit))
			$limit=str_replace("LIMIT", "", $limit);
		$r=$this->database->select(CONFIGURATIONDBTABLE, $where,$fields,$limit,array("cid"),"DESC");
		
		return $r;
		
	}
	
	public function getId($where=array())
	{
		$whereCond="";
		if(!empty($where))
			$whereCond=$this->database->buildWhereClause(CONFIGURATIONDBTABLE, $where);
		
		$r=$this->database->query("SELECT cid from `".CONFIGURATIONDBTABLE."` ".$whereCond);
		return $r[0]['cid'];
	}
	
	public function addRelations($to=null,$with=null){
		if($to && $with)
		{
			if(isset($this->segment[$to]) && in_array($with,array("Host","Service")) && class_exists($with))
			{
				$obj = new $with;
				$this->segment['relations']=array();
				foreach ($this->segment[$to] as $s)
				{
					$relations=array();
					$description = $s->getdescription();
					if(is_a($obj,"Host"))
						$relations=$obj->getAll(array('parent_host'=>$description));
					if(is_a($obj,"Service"))
						$relations==$obj->getAll(array('run_on'=>$description));		
					if(count($relations)>0)
						$this->segment['relations']=array_merge($this->segment['relations'],$relations);
				}
			}
		}
	}
}
// End Class "Configuration"
?>