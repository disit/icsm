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

define('SMCONFIGURATORVERSION',"1.0");
define('ASYNCCONFIGCTRL',true);
define('SMCONFIGURATORINSTANCEID','00000');
define('SMCONFIGURATORXSDFILE', "sm.xsd");
define('SMCONFIGURATORXSLTFILE_BUSINESS', "RDFToSM_Business.xslt");
define('SMCONFIGURATORXSLTFILE_SYSTEM', "RDFToSM_System.xslt");
define('SMCONFIGURATORUSEXSLTFILE', true);

class SM_Configurator implements sm_Module
{
	protected $configuratorQueue=null;
	protected $configuration=null;
	protected $monitor=null;
	protected $async;
	protected $db;
	protected $prefix;
	protected $lastMid;
	function __construct()
	{
		$this->prefix="SM:";
		$this->async=sm_Config::get('ASYNCCONFIGCTRL', ASYNCCONFIGCTRL);
		$this->db=sm_Database::getInstance();
		$this->configuratorQueue = new SM_ConfiguratorQueue();
		$this->configuration = new SM_Configuration();
		$this->monitor = new SM_Monitor();
		$this->lastMid="";
	}
	
	function getQueueData($data,$confId)
	{
		if(!preg_match("/SM:/",$confId))
			$mid=$this->monitor->getMonitorId($confId);
		else
			$mid=$confId;
		if($mid)
			return $this->configuratorQueue->getInfo($data,array("mid"=>$mid));
		return null;
		
	}
	
	function loadConfiguration($cid)
	{
		$this->configuration = SM_Configuration::load($cid);
		return $this->configuration;
	}
	
	function getConfigurationData($data,$confId,$filter=null)
	{
		if(preg_match("/SM:/",$confId))
			$cid=$this->monitor->getInternalId($confId);
		else if(is_numeric($confId))
			$cid=$confId;
	    else
	    	$cid=$this->monitor->getInternalIdbyDescription($confId);
		if($cid)
		{
			
			$configuration = $this->loadConfiguration($cid); //SM_Configuration::load($cid);
			if($configuration)
			{
				$segment=null;
				if($data!="*")
				{
					$segment=$data;
				}
					
				$result=$configuration->build($segment, $filter);
				if(isset($result))
				{
					
					return array('configuration'=>$result);
				}
			}	
		}
		return null;
		
	}
	
	
	
	
	
	function xslt_transform($str,$type="Business")
	{
		if($type=="System" && sm_Config::get("SMCONFIGURATORUSEXSLTFILE",SMCONFIGURATORUSEXSLTFILE))
		{
			$xsltFile = realpath("./").DIRECTORY_SEPARATOR."schema".DIRECTORY_SEPARATOR.sm_Config::get("SMCONFIGURATORXSLTFILE_SYSTEM",SMCONFIGURATORXSLTFILE_SYSTEM);
			$xslt = new XSLT_Processor();
			$xml=$xslt->mapString($str, $xsltFile);
		}
		else if($type=="Business" && sm_Config::get("SMCONFIGURATORUSEXSLTFILE",SMCONFIGURATORUSEXSLTFILE))
		{
			$xsltFile = realpath("./").DIRECTORY_SEPARATOR."schema".DIRECTORY_SEPARATOR.sm_Config::get("SMCONFIGURATORXSLTFILE_BUSINESS",SMCONFIGURATORXSLTFILE_BUSINESS);
			$xslt = new XSLT_Processor();
			$xml=$xslt->mapString($str, $xsltFile);
		}
		else
			$xml=$str;
		
		return $xml;
	}
	
	function xml_validate($xml)
	{
		$xsdfilename=realpath("./").DIRECTORY_SEPARATOR."schema".DIRECTORY_SEPARATOR.sm_Config::get('SMCONFIGURATORXSDFILE', SMCONFIGURATORXSDFILE);
		$dom = new DOMDocument();
		$dom->loadXML($xml);
		// Enable user error handling 
		error_reporting(E_ALL);
		libxml_use_internal_errors(true);
		$a=$dom->schemaValidate($xsdfilename);
		
		return $a;
	}
	
	function exists($id)
	{
		if($this->monitor->getInternalIdbyDescription($id))
			return true;
		return false;
	}
	
	
	function insert($confData,$type)
	{
		sm_Logger::write("Converting Data into Configuration data model");
		$configuration = $this->xslt_transform($confData,$type);
		
		sm_Logger::write("Validating XML Configuration vs Schema");
		if(!$this->xml_validate($configuration))
		{
			sm_Logger::error("Error when validating xml!");
			return array("error"=>"Error when validating xml: Malformed XML");
		}
		sm_Logger::write("XML Configuration vs Schema Validated");
		sm_Logger::write("Building Configuration data model");
		
		$conf = new SM_Configuration();
		$conf->parse($configuration);
			
		sm_Logger::write("Validating Configuration data model");
		if($conf->validate())
		{
			sm_Logger::write("Configuration data model validated");
		}
		else
		{
			sm_Logger::error("Invalid or Malformed Configuration data model");
			return array("error"=>"Invalid or Malformed Configuration data model");
		}
		sm_Logger::write("Check for duplicated (".$conf->getdescription().")");
		if($this->exists($conf->getdescription()))
		{
			sm_Logger::error("A configuration with same identifier (".$conf->getdescription().") already exists!");
			return array("error"=>"A configuration with same identifier (".$conf->getdescription().") already exists!");
		}
		
		$this->generateId($conf->getidentifier());
		sm_Logger::write("Generating Monitor ID");
		//sm_Logger::error($conf);
		if($this->lastMid!="")
		{
			$mid=$this->lastMid;
			sm_Logger::write("Monitor id generated successfully (".$mid.")!");
		}
		else
		{
			sm_Logger::error("Error when generating monitor id!");
			return array("error"=>"Error when generating monitor id!");
			
		}
		sm_Logger::write("Writing/Saving Configuration data");
		$conf->save();

		$data=array(
				'mid'=>$mid,
				'iid'=>$conf->getcid(),
				'description'=>$conf->getdescription(),
				'data'=>$conf->getbid(),
				'class'=>''
									
		);
		
		sm_Logger::write("Storing Monitor Data");
		$this->monitor->save($data);
		$this->monitor->save_host($mid,$conf);
		sm_Logger::write("Stored Monitor Data (".$mid.") in DB");
		sm_Logger::write("Queuing Configuration Data ");
		$id=$this->configuratorQueue->push($mid,$configuration);
		sm_Logger::write("Stored Configuration Data (".$mid.") in Configuration Queue DB");
		if(!$this->async)
		{
			//1. configura Nagios
			//2. verifica la cfg su Nagios
			
			//3. mette lo stato a "Closed"
			
		}
		$data=array("status"=>"Active","close_time"=>time());
		$this->configuratorQueue->setInfo($data,$id);
		return array("mid"=>$mid);
		//return true;
	}
	
	function update($cid,$confData,$type="Business")
	{
		sm_Logger::write("Converting Data into Configuration data model");
		$configuration = $this->xslt_transform($confData,$type);
		
		sm_Logger::write("Validating XML Configuration vs Schema");
		if(!$this->xml_validate($configuration))
		{
			sm_Logger::error("Error when validating xml!");
			return array("error"=>"Error when validating xml: Malformed XML");
		}
		sm_Logger::write("XML Configuration vs Schema Validated");
		sm_Logger::write("Building Configuration data model");
		
		$conf = new SM_Configuration();
		$conf->parse($configuration);
			
		sm_Logger::write("Validating Configuration data model");
		if($conf->validate())
		{
			sm_Logger::write("Configuration data model validated");
		}
		else
		{
			sm_Logger::error("Invalid or Malformed Configuration data model");
			return array("error"=>"Invalid or Malformed Configuration data model");
		}

		
		sm_Logger::write("Queuing Configuration Data ");
		
		sm_Logger::write("Writing/Saving Configuration data");
		$oldConf = new SM_Configuration($cid);
		$mid = $this->monitor->getMonitorIdbyDescription($oldConf->getdescription());
		$oldConf->delete();
		$conf->save();
		
		$data=array(
				'mid'=>$mid,
				'iid'=>$conf->getcid(),
				'description'=>$conf->getdescription(),
				'data'=>$conf->getbid(),
				'class'=>'',
				
		);
		
		sm_Logger::write("Updating Monitor Data");
		$this->monitor->update($cid,$data,array('mid'=>$mid));
		$this->monitor->update_host($mid,$conf);
		sm_Logger::write("Update Monitor Data (".$mid.") in DB");

		
		
		$data=array("status"=>"Closed");
		$this->configuratorQueue->update(array("mid"=>$mid), $data);
		$id = $this->configuratorQueue->push($mid,$configuration);
		
		
		sm_Logger::write("Stored Configuration Data (".$mid.") in Configuration Queue DB");
		
		$data=array("status"=>"Active","close_time"=>time());
		$this->configuratorQueue->setInfo($data,$id);
		return  array("mid"=>$mid);
	}
	
	function remove($confId)
	{
		sm_Logger::write("Starting deleting configuration ".$confId);
		if(!preg_match("/SM:/",$confId))
			$mid=$this->monitor->getMonitorId($confId);
		else
			$mid=$confId;
		$data=null;
		$cid=$confId;
		if(!$this->monitor->exists($mid))
		{
			sm_Logger::write($mid." not exists!");
			//return null;
		}
		else
			$cid=$this->monitor->getInternalId($mid);
		sm_Logger::write("Deleting all instances in configuration queue for ".$confId);
		$result=$this->configuratorQueue->delete($mid);
		if($result)
		{
			sm_Logger::write("Configuration queue: instances deleted for ".$confId);
			
			/*sm_Logger::write("Deleting monitor instance for ".$confId);
			$result=$this->monitor->delete($mid);*/
		}
		//if(!$this->async)
		
		if($result)
		{
			//sm_Logger::write("Monitor instances deleted for ".$mid);
			sm_Logger::write("Deleting configuration for ".$cid);
			SM_Configuration::load($cid)->delete();
			//1. elimina configurazione da Nagios
			//2. verifica la cfg su Nagios
			$data=array("mid"=>$confId,"status"=>"DELETED","close_time"=>time());
		}
		return $data;
	}
	
	
	
	protected function generateId($str=null)
	{
		if(!$str)
				return;
		do {
			$s=$this->prefix.sm_Config::get('SMCONFIGURATORINSTANCEID', SMCONFIGURATORINSTANCEID).":";
			$mid=uniqid($s);
			
				$mid.=":".$str;
		}
		while($this->monitor->exists($mid));
		$this->lastMid=$mid;
		//return $mid;
	}
	
	static function install($db)
	{
		if(!sm_Config::get('ASYNCCONFIGCTRL',null))
			sm_Config::set('ASYNCCONFIGCTRL',array('value'=>ASYNCCONFIGCTRL,"description"=>'Configuration Asyncronous Mode'));
		if(!sm_Config::get('SMCONFIGURATORINSTANCEID',null))
			sm_Config::set('SMCONFIGURATORINSTANCEID',array('value'=>SMCONFIGURATORINSTANCEID,"description"=>'Configuration Instance Identifier'));
		if(!sm_Config::get('SMCONFIGURATORXSDFILE',null))
			sm_Config::set('SMCONFIGURATORXSDFILE',array('value'=>SMCONFIGURATORXSDFILE,"description"=>'Configuration Data Model XSD File Path'));
		if(!sm_Config::get('SMCONFIGURATORXSLTFILE_SYSTEM',null))
			sm_Config::set('SMCONFIGURATORXSLTFILE_SYSTEM',array('value'=>SMCONFIGURATORXSLTFILE_SYSTEM,"description"=>'Configuration Data Model XSLT Map File Path (System)'));
		if(!sm_Config::get('SMCONFIGURATORXSLTFILE_BUSINESS',null))
			sm_Config::set('SMCONFIGURATORXSLTFILE_BUSINESS',array('value'=>SMCONFIGURATORXSLTFILE_BUSINESS,"description"=>'Configuration Data Model XSLT Map File Path (Business)'));
		if(!sm_Config::get('SMCONFIGURATORUSEXSLTFILE',null))
			sm_Config::set('SMCONFIGURATORUSEXSLTFILE',array('value'=>SMCONFIGURATORUSEXSLTFILE,"description"=>'Use Configuration Data Model XSLT Map'));
		
		/****** ACL Section *******************/
		sm_Logger::write("Installing Permissions: Configuration::Edit");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Configuration Edit','permKey'=>'Configuration::Edit'));
		sm_Logger::write("Installing Permissions: Configuration::View");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Configuration View','permKey'=>'Configuration::View'));
		sm_Logger::write("Permissions Installed");
	}
	
	static function uninstall($db)
	{
		sm_Config::delete('ASYNCCONFIGCTRL');
		sm_Config::delete('SMCONFIGURATORINSTANCEID');
		sm_Config::delete('SMCONFIGURATORXSDFILE');
		sm_Config::delete('SMCONFIGURATORXSLTFILE_SYSTEM');
		sm_Config::delete('SMCONFIGURATORXSLTFILE_BUSINESS');
		sm_Config::delete('SMCONFIGURATORUSEXSLTFILE');
		return true;
	}
	
	public function getQueue(){
		return $this->configuratorQueue;
	}
	
	public function getConfiguration()
	{
		return $this->configuration;
	}
	
	public function getMonitor()
	{
		return $this->monitor;
	}
	
	public function getId($data)
	{
		return $this->configuration->getId($data);
	}
	
	
	
	
	
	
	
}
