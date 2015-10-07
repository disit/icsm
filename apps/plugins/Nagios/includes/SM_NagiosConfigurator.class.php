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

define('SMNAGIOSCONFIGURATORXSLTFILE', "SM2Nagios.xslt");
define('SMNAGIOSCONFIGURATORTEMPLATESFILE', "templates.xml");
define('SMNAGIOSCONFIGURATORCMDSFILE', "commands.xml");
define('SMNAGIOSCONFIGURATORTABLE','nagios_configuration');



class SM_NagiosConfigurator extends SM_Configurator
{
	protected $ql;
	protected $req;
	protected $conf;
	protected $templates;
	protected $commands;
	protected $report;
	protected $logger;
	protected $enableRollback;
	
	public function __construct()
	{
		parent::__construct();
		$this->ql = new SM_NagiosQL();
		$this->req=NULL;
		$this->conf=NULL;
		$this->loadData();
		$this->report=array();
		$this->enableRollback=sm_Config::get("SMNAGIOSCONFIGURATORROLLBACK",0);
		//$this->save();
		
	}
	
	public function __destruct()
	{
		unset($this->ql);
		unset($this->report);
		
	}
	
	public function setRollback($bool)
	{
		$this->enableRollback=$bool;
	}
	
	
	
	protected function loadData(){
		$tpls=file_get_contents(__DIR__.DIRECTORY_SEPARATOR.SMNAGIOSCONFIGURATORTEMPLATESFILE);
		$data=XML2Array::createArray($tpls);
		$this->templates=$data['templates'];
		$tpls=file_get_contents(__DIR__.DIRECTORY_SEPARATOR.SMNAGIOSCONFIGURATORCMDSFILE);
		$data=XML2Array::createArray($tpls);
		$this->commands=$data['commands'];
	}
	
	protected function _loadConf()
	{
		$this->templates['host']=array(
				'windowsXP'=>"windows-server",
				'windowsServer2012'=>"windows-server",
				'windows8'=>"windows-server",
				'linux'=>"linux-server",
				'ubuntu'=>"ubuntu-linux-server",
				'as400'=>"IBM-As400",
				'nexus'=>"Cisco-Nexus",
				'cisco-nexus'=>"Cisco-Nexus"
		);
		$this->templates['vmhost']=array(
				'windowsXP'=>"windows-server",
				'windowsServer2012'=>"windows-server",
				'windows8'=>"windows-server",
				'linux'=>"linux-server",
				'ubuntu'=>"ubuntu-linux-server",
				'as400'=>"IBM-As400",
				'nexus'=>"Cisco-Nexus",
				'cisco-nexus'=>"Cisco-Nexus"
		);
		$this->templates['device']=array(
				'ExternalStorage'=>"external-storage",
				'Firewall'=>"firewall",
				'Router'=>"router",
		);
		$this->templates['wmdevice']=array(
				'ExternalStorage'=>"WM-external-storage",
				'Firewall'=>"WM-firewall",
				'Router'=>"WM-router",
		);
		
		$this->commands['command'][]=array("name"=>'avgCPU%1m',"plugin"=>"check_php");
		$this->commands['command'][]=array("name"=>'avgMem1m%1m',"plugin"=>"check_php");
	}
	
	public function save(){
		
		$mapfile=__DIR__.DIRECTORY_SEPARATOR.SMNAGIOSCONFIGURATORTEMPLATESFILE;
		$xml = Array2XML::createXML("templates",$this->templates);
		$xml->save($mapfile);
		
		$mapfile=__DIR__.DIRECTORY_SEPARATOR.SMNAGIOSCONFIGURATORCMDSFILE;
		$xml2 = Array2XML::createXML("commands",$this->commands);
		$xml2->save($mapfile);
	}
	
	public function execute(){
		$this->req=NULL;
		$this->conf=NULL;
		if(!$this->loadRequest())
		{
			echo "Error Load Request";
		}
		$id=$this->req['id'];
		/*$this->configure($this->req);
		switch ($this->req['method'])
		{
			case "POST":
				$this->configure();
			break;
			
			case "UPDATE":
				$this->update();
			break;
		}*/
		
		
		//$this->updateStatus($this->req);
	}
	
	
	public function loadRequest($status=0)
	{
		//selectRow($table, $where = null, $fields = null, $limit = '', $orderby = null, $direction = 'ASC')
		
		$query = "SELECT cid, mid, status FROM configuration JOIN monitors on cid=iid where status in (".$status.")  order by lastupdate ASC LIMIT 0,1";
		
		$req = sm_Database::getInstance()->query($query);
	  //  $req=$this->configuratorQueue->getAll("LIMIT 0,1","status='Active' AND method in ('PUT','POST')");
		if(!is_array($req) || count($req)==0)
		{
			return null;
		}
		
		return $this->req=$req[0];
		
	}
	
	public function checkData()
	{
		if(!is_array($this->req) || !isset($this->req['data']))
			return false;
		$this->conf = trim(stripslashes(str_replace("\\n","",$this->req['data'])),"\"");
		return !empty($this->conf);	
	}
	
	public function updateStatus($where=null,$status="Closed")
	{
		if($where)
			$this->configuratorQueue->update($where,array("status"=>$status));
	}
	
	public function _configure()
	{
		if(!$this->checkData())
		{
			sm_Logger::error("Error Load Data");
			echo "Error Load Data";
		}
		//else 
		// echo print_r ($this->conf,true);
		// exit();
		// else
		else{
			//$this->conf=file_get_contents(realpath("./").DIRECTORY_SEPARATOR."schema/smSysConf.xml");
			//$this->conf=trim(stripslashes(str_replace("\\n","",$s)),"\"");
			ini_set('display_errors', 'On');
			error_reporting(E_ALL);
			//if($this->xml_validate($this->conf))
			
				
				$nagiosXML = $this->mapNagiosData($this->conf);
				if(!$nagiosXML)
					sm_Logger::error("Error when mapping Nagios XML!");
				else
				{
					$arr = XML2array::createArray($nagiosXML);
					$this->preNagiosData($arr);
					//print_r ($nagios);
					return $nagios;
				}
		}
		return null;
		
		
	}
	
	public function configure($id){
		
		
		$t=time();
		$conf=$this->getConfigurationData("*",$id);
		$dom=Array2XML::createXML("configuration",$conf['configuration']);
		$nagiosXML = $this->mapNagiosData($dom->saveXML(),true);
		$mid=$this->getMonitor()->getMonitorId($id);
		if($nagiosXML)
		{
			$data=array("status"=>4,"errors"=>"");
			if($this->getMonitor()->save($data,array("mid"=>$mid)))
				$this->log('message',"Monitor Table: Status for ".$mid." set to Processing");
			$nagios=XML2Array::createArray($nagiosXML);
			$this->prepareNagiosData($nagios);
			if($this->writeNagiosConfiguration($nagios))
			{
				$data=array("status"=>1,"plugin"=>SM_NagiosPlugin::instance()->getPluginName(),"errors"=>""); //,"time"=>time()-$t);
				$status="Monitoring";
				$this->log('result',true);
				$this->updateStatus(array("mid"=>$mid,"close_time"=>time()),"Closed");
			}
			else
			{
				$errors=$this->getErrors();
				if($this->enableRollback)
					$this->rollback($id);
				else 
				{
					$this->log('error',"Nagios Configuration Rollback: <a href='nagios/rollback/".$id."'>Click to rollback</a>");
					$errors[]="Nagios Configuration Rollback: <a href='nagios/rollback/".$id."'>Click to rollback</a>";
				}
				
				$data=array("status"=>3,"plugin"=>SM_NagiosPlugin::instance()->getPluginName(),"errors"=>implode("<br>",$errors)); //,"time"=>time()-$t);
				$status="Failed";
				$this->log('result',false);
			}
				
			if($this->getMonitor()->save($data,array("mid"=>$mid)))
				$this->log('message',"Monitor Table: Status for ".$mid." set to ".$status);
		}
		else {
			$this->log('error',"Invalid XML Nagios Data");
			$status="Failed";
			$data=array("status"=>3,"errors"=>"Invalid XML Nagios Data");
			if($this->getMonitor()->save($data,array("mid"=>$mid)))
				$this->log('message',"Monitor Table: Status for ".$mid." set to ".$status);
			$this->log('result',false);
		}
		return $this->report;
	}
	
	function synchronize($id)
	{
		$conf=$this->getConfigurationData("*",$id);
		$dom=Array2XML::createXML("configuration",$conf['configuration']);
		$nagiosXML = $this->mapNagiosData($dom->saveXML(),true);
		if($nagiosXML)
		{
			$nagios=XML2Array::createArray($nagiosXML);
			$this->prepareNagiosData($nagios);
			if($this->synchNagiosConfiguration($nagios))
			{
				
				$this->log('result',true);
				
			}
		}
		return $this->report;
	}
	
	public function synchNagiosConfiguration($nagios){
	
		if(!$this->ql->login())
		{
			$this->log('error',"Login/Connection Failed with NagiosQL");
			return false;
		}
		$this->log('message',"Connection Success with NagiosQL");
		$continue=true;
		if(isset($nagios['nagios']['hostgroup']))
		{
			$hostgroup_data=$nagios['nagios']['hostgroup'];
				
			$hostgroups=array();
			if (!isset($hostgroup_data[0]))
				$hostgroups[]=$hostgroup_data;
			else
				$hostgroups=$hostgroup_data;
			foreach($hostgroups as $h=>$hostgroup)
			{
	
				$this->log('message',"Starting HostGroup Check: ".$hostgroup['hostgroup_name']);
				$hg=$this->ql->getHostGroup($hostgroup['hostgroup_name']);
				if($hg)
				{
						
					$this->log('message',"HostGroup Already Exists (Updating Table)");
					$mapdata=$hostgroup['@attributes'];
					$mapdata['qltable']='tbl_hostgroup';
					$mapdata['qlid']=$hg['id'];
					$this->writeNagiosData($mapdata);
				}
				else
				{
						
					$this->log('message',"Missing HostGroup: ".$hostgroup['hostgroup_name']);
				}
			}
		}
	
		if(isset($nagios['nagios']['host']))
		{
				
			$this->log('message',"Starting Host(s) Checks");
			$host_data=$nagios['nagios']['host'];
			$hosts=array();
			if (!isset($host_data[0]))
				$hosts[]=$host_data;
			else
				$hosts=$host_data;
			foreach($hosts as $h=>$host)
			{
	
				$this->log('message',"Starting Host Check: ".$host['host_name']);
				$hst=$this->ql->getHostByName($host['host_name'], $host['address']);
				if($hst)
				{
	
					$this->log('message',"Host ".$host['host_name']." => Already Exists (Updating Table)");
					$mapdata=$host['@attributes'];
					$mapdata['qltable']='tbl_host';
					$mapdata['qlid']=$hst['id'];
					$this->writeNagiosData($mapdata);
				}
				else
				{
	
					$this->log('message',"Missing Host: ".$host['host_name']);						
				}
			}
		}
	
		if(isset($nagios['nagios']['servicegroup']))
		{
			$servicegroup_data=$nagios['nagios']['servicegroup'];
			$servicegroups=array();
			if (!isset($servicegroup_data[0]))
				$servicegroups[]=$servicegroup_data;
			else
				$servicegroups=$servicegroup_data;
			foreach($servicegroups as $sgroup=>$servicegroup)
			{
					
	
				$this->log('message',"Starting ServiceGroup Checks: ".$servicegroup['servicegroup_name']);
	
	
				$sg=$this->ql->getServiceGroup($servicegroup['servicegroup_name']);
				if($sg)
				{
	
					$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Already Exists (Updating Table)");
					
					
						$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Updated Successfully");
						$mapdata=$servicegroup['@attributes'];
						$mapdata['qltable']='tbl_servicegroup';
						$mapdata['qlid']=$sg['id'];
						$this->writeNagiosData($mapdata);
							
					
						
				}
				else
				{
						
					$this->log('message',"Missing  ServiceGroup: ".$servicegroup['servicegroup_name']);
					
				}
			}
		}
	
		if(isset($nagios['nagios']['service']))
		{
				
			$this->log('message',"Starting Service(s) Insertion");
			$service_data=$nagios['nagios']['service'];
			$services=array();
			if (!isset($service_data[0]))
				$services[]=$service_data;
			else
				$services=$service_data;
			foreach($services as $s=>$service)
			{
					
				$this->log('message',"Starting Service Insertion: ".$service['config_name']);
				$srv=$this->ql->getService($service['config_name']);
				if($srv)
				{
						
					$this->log('message',"Service ".$service['config_name']." => Already Exists");
					$mapdata=$service['@attributes'];
					$mapdata['qltable']='tbl_service';
					$mapdata['qlid']=$srv['id'];
					$this->writeNagiosData($mapdata);
						
				}
				else
				{
						
					$this->log('message',"Missing Service: ".$service['config_name']);
					
	
				}
			}
		}
		
		$this->ql->logout();
		return $continue;
	}
	
	public function remove_configuration($cid)
	{
		if($cid)
		{
			$this->log('message',"***** Nagios Data Removal Activated *****");
			if(!$this->ql->login())
			{
				$this->log('error',"Nagios Data Removal: Login/Connection Failed with NagiosQL");
				$this->report['result']=false;
				return $this->report;
			}
			$results=$this->db->select(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid,"qlTable"=>"tbl_hostgroup"));
			if($results)
			{
				$this->removeNagiosConfiguration($results);
				$this->log('message',"Nagios Data Removal: HostGroups deleted");
			}
			$results=$this->db->select(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid,"qlTable"=>"tbl_service"));
			if($results)
			{
				$this->removeNagiosConfiguration($results);
				$this->log('message',"Nagios Data Removal: Services deleted");
			}
			$results=$this->db->select(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid,"qlTable"=>"tbl_servicegroup"));
			if($results)
			{
				$this->removeNagiosConfiguration($results);
				$this->log('message',"Nagios Data Removal: ServiceGroups deleted");
			}
			$results=$this->db->select(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid,"qlTable"=>"tbl_host"));
			if($results)
			{
				$this->removeNagiosConfiguration($results);
				$this->log('message',"Nagios Data Removal: Hosts deleted");
			}
			$this->deleteNagiosData($cid);
			$this->log('message',"***** Nagios Data Removal Success *****");
			if($this->ql->verify())
			{
				$this->log('message',"Nagios Data Removal: Nagios Configuration Check Passed");
				$nagiosClient = new SM_NagiosClient();
				//if($nagiosClient->restart())
				if($nagiosClient->reload())
					$this->log('message',"Nagios Data Removal: Nagios Restarted");
			}
			else
				$this->log('error',"Nagios Data Removal: Nagios Configuration Check Failed");
				
			$this->ql->logout();
			$this->log('message',"***** Nagios Data Removal Terminated *****");
			$data=array("status"=>-1,"plugin"=>str_replace("SM_","",__CLASS__),"errors"=>""); //,"time"=>time()-$t);
			$status="Ready";
			
			$mid=$this->getMonitor()->getMonitorId($cid);
			if($this->getMonitor()->save($data,array("mid"=>$mid)))
				$this->log('message',"Monitor Table: Status for ".$mid." set to ".$status);
			$this->report['result']=true;
			
		}
		else
			$this->log('error',"Nagios Data Removal: Invalid Configuration Id (".$cid.")");
		return $this->report;
	}
	
	public function writeNagiosConfiguration($nagios){
		
		if(!$this->ql->login())
		{
			$this->log('error',"Login/Connection Failed with NagiosQL");
			return false;
		}
		$this->log('message',"Connection Success with NagiosQL");
		$continue=true;
		if(isset($nagios['nagios']['hostgroup']))
		{
			$hostgroup_data=$nagios['nagios']['hostgroup'];
			
			$hostgroups=array();
			if (!isset($hostgroup_data[0]))
				$hostgroups[]=$hostgroup_data;
			else
				$hostgroups=$hostgroup_data;
			foreach($hostgroups as $h=>$hostgroup)
			{
				
				$this->log('message',"Starting HostGroup Insertion: ".$hostgroup['hostgroup_name']);
				$hg=$this->ql->getHostGroup($hostgroup['hostgroup_name']);
				if($hg)
				{
					
					$this->log('message',"HostGroup Already Exists (Skipping Creation)");
					$mapdata=$hostgroup['@attributes'];
					$mapdata['qltable']='tbl_hostgroup';
					$mapdata['qlid']=$hg['id'];
					$this->writeNagiosData($mapdata);
				}
				else
				{
					
					$this->log('message',"Inserting New HostGroup: ".$hostgroup['hostgroup_name']);
					$this->ql->hostGroup($hostgroup);
					$hg=$this->ql->getHostGroup($hostgroup['hostgroup_name']);
					if($hg)
					{
						
						$this->log('message',"HostGroup Inserted Successfully: ".$hostgroup['hostgroup_name']);
						$mapdata=$hostgroup['@attributes'];
						$mapdata['qltable']='tbl_hostgroup';
						$mapdata['qlid']=$hg['id'];
						$this->writeNagiosData($mapdata);
					}
					else
					{
						
						$this->log('error',"HostGroup Insertion FAILED: ".$hostgroup['hostgroup_name']);
						$continue=false;
					}
				}
			}
		}
		
		if($continue && isset($nagios['nagios']['host']))
		{
			
			$this->log('message',"Starting Host(s) Insertion");
			$host_data=$nagios['nagios']['host'];
			$hosts=array();
			if (!isset($host_data[0]))
				$hosts[]=$host_data;
			else
				$hosts=$host_data;
			foreach($hosts as $h=>$host)
			{
				
				$this->log('message',"Starting Host Insertion: ".$host['host_name']);
				$hst=$this->ql->getHostByName($host['host_name'], $host['address']);
				if($hst)
				{
				
					$this->log('message',"Host ".$host['host_name']." => Already Exists");
					$mapdata=$host['@attributes'];
					$mapdata['qltable']='tbl_host';
					$mapdata['qlid']=$hst['id'];
					$this->writeNagiosData($mapdata);
				}
				else
				{
				
					$this->log('message',"Inserting New Host: ".$host['host_name']);
					$this->ql->host($host);
					$hst=$this->ql->getHostByName($host['host_name'], $host['address']);
					if($hst)
					{
						
						$this->log('message',"Host ".$host['host_name']." => Inserted Successfully");
						$mapdata=$host['@attributes'];
						$mapdata['qltable']='tbl_host';
						$mapdata['qlid']=$hst['id'];
						$this->writeNagiosData($mapdata);
					}
					else
					{
						
						$this->log('error',"Host ".$host['host_name']." => Insertion FAILED");
						$continue=false;
						break;
					}
					
				}
			}
		}
		
		if($continue && isset($nagios['nagios']['servicegroup']))
		{
			$servicegroup_data=$nagios['nagios']['servicegroup'];
			$servicegroups=array();
			if (!isset($servicegroup_data[0]))
				$servicegroups[]=$servicegroup_data;
			else
				$servicegroups=$servicegroup_data;
			foreach($servicegroups as $sgroup=>$servicegroup)
			{
			
				
				$this->log('message',"Starting ServiceGroup Insertion: ".$servicegroup['servicegroup_name']);			
				$this->log('message',"Checking if ServiceGroup Already Exists: ".$servicegroup['servicegroup_name']);
				$sg=$this->ql->getServiceGroup($servicegroup['servicegroup_name']);
				if($sg)
				{
				
					$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Already Exists (Skipping Creation)");
					$this->log('message',"Deleting ServiceGroup: ".$servicegroup['servicegroup_name']);
					$mapdata['qltable']='tbl_servicegroup';
					$servicegroup['id']=$mapdata['qlid']=$sg['id'];
					$this->ql->remove_serviceGroup($servicegroup);
					$this->removeNagiosData($mapdata);
					if(!$this->ql->getServiceGroup($servicegroup['servicegroup_name']))
						$this->log('message',"Delete Success for: ".$servicegroup['servicegroup_name']);
				/*	if(isset($servicegroup['servicegroups_members']))
					{
						$srvgroups=array();
						if (!is_array($servicegroup['servicegroups_members']))
							$srvgroups[]=$servicegroup['servicegroups_members'];
						else
							$srvgroups=$servicegroup['servicegroups_members'];
						foreach($srvgroups as $srg=>$srvgroup)
						{
							$sm_member=$this->ql->getServiceGroup($srvgroup);
							if(is_array($sm_member) && !in_array($sm_member['id'], $sg["servicegroups_members"]))
							{
								$sg["servicegroups_members"][]=$sm_member['id'];
		
					
								$this->log('message',"Updating ServiceGroup memberships: ".$servicegroup['servicegroup_name']);
								$servicegroup['id']=$sg['id'];
								$this->ql->updateServiceGroup($servicegroup);
								if($sg)
								{
					
									$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Updated Successfully");
									$mapdata=$servicegroup['@attributes'];
									$mapdata['qltable']='tbl_servicegroup';
									$mapdata['qlid']=$sg['id'];
									$this->writeNagiosData($mapdata);
								}
								else
								{
					
									$this->log('error',"Service Group ".$servicegroup['servicegroup_name']." => Update FAILED");
									$continue=false;
								}
							}
							
						}
							
					}*/
					
				}
				else
					$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Not Found (Start Creation)");
				
				if(true)
				{
					
					$this->log('message',"Inserting ServiceGroup: ".$servicegroup['servicegroup_name']);
					if(isset($servicegroup['servicegroups_members']))
					{
						$this->log('message',"Check for Service Group Member: ".$servicegroup['servicegroup_name']);
						$srvgroups=array();
						if (!is_array($servicegroup['servicegroups_members']))
							$srvgroups[]=$servicegroup['servicegroups_members'];
						else
							$srvgroups=$servicegroup['servicegroups_members'];
						$ids=array();
						foreach($srvgroups as $srg=>$srvgroup)
						{
							$this->log('message',"Inserting New ServiceGroup Member: ".$srvgroup);
							$sg_member=$this->ql->getServiceGroup($srvgroup);
							if(isset($sg_member))
							{
								$ids[]=$sg_member['id'];
								$this->log('message',"ServiceGroup Member ".$srvgroup." has id: ".$sg_member['id']);
							}
							else 
							{
								$this->log('error',"ServiceGroup Member: ".$srvgroup." does not exists");
								$continue=false;
							}
					
						}
						$servicegroup["servicegroups_members"]=$ids;
					}
					if(isset($servicegroup['members']))
					{
						$mData=explode("::", $servicegroup['members']);
						$request['hostgroup']=$mData[0];
						$request['hostname']=$this->nagiosEscapeStr($mData[1]);
						$this->log('message',"Create ServiceGroup Members: ".$servicegroup['members']);
						$servicegroupmembers=$this->ql->createServiceGroupMembers($request['hostgroup'],$request['hostname']);
						if(!$servicegroupmembers)
						{
							$this->log('error',"ServiceGroup Membership failed: ".$request['hostname']." does not exists");
							//$continue=false;
						}
						else 
							$servicegroup['members']=$servicegroupmembers;
					}
					
					
					$this->ql->serviceGroup($servicegroup);
					$sg=$this->ql->getServiceGroup($servicegroup['servicegroup_name']);
					if($sg)
					{
					
						$this->log('message',"Service Group ".$servicegroup['servicegroup_name']." => Inserted Successfully");
						if(isset($servicegroup['@attributes'])){
							$mapdata=$servicegroup['@attributes'];
							$mapdata['qltable']='tbl_servicegroup';
							$mapdata['qlid']=$sg['id'];
							$this->writeNagiosData($mapdata);
						}
						
					}
					else
					{
					
						$this->log('error',"Service Group ".$servicegroup['servicegroup_name']." => Insertion FAILED");
						$continue=false;
					}
				}
			}
		}
		
		if($continue && isset($nagios['nagios']['service']))
		{
			
			$this->log('message',"Starting Service(s) Insertion");
			$service_data=$nagios['nagios']['service'];
			$services=array();
			if (!isset($service_data[0]))
				$services[]=$service_data;
			else
				$services=$service_data;
			foreach($services as $s=>$service)
			{
			
				$this->log('message',"Starting Service Insertion: ".$service['config_name']);
				$srv=$this->ql->getService($service['config_name']);
				if($srv)
				{
			
					$this->log('message',"Service ".$service['config_name']." => Already Exists");
					$this->log('message',"Deleting Service: ".$service['config_name']);
					$this->ql->remove_service($srv); 
					$mapdata=$service['@attributes'];
					$mapdata['qltable']='tbl_service';
					$mapdata['qlid']=$srv['id'];
					$this->removeNagiosData($mapdata);
					if(!$this->ql->getService($service['config_name']))
						$this->log('message',"Delete Success for service: ".$service['config_name']);
					
				}
				else
					$this->log('message',"Service ".$service['config_name']." => Not Found (Start Creation)");

				$this->log('message',"Inserting New Service: ".$service['config_name']);
				$this->ql->service($service);
				$srv=$this->ql->getService($service['config_name']);
				if($srv)
				{
		
					$this->log('message',"Service ".$service['config_name']." => Inserted Successfully");
					$mapdata=$service['@attributes'];
					$mapdata['qltable']='tbl_service';
					$mapdata['qlid']=$srv['id'];
					$this->writeNagiosData($mapdata);
				}
				else
				{
		
					$this->log('error',"Service ".$service['config_name']." => Insertion FAILED");
					$continue=false;
					break;
				}
						
				
			}
		}
		if($continue)
		{
			if($this->ql->verify())
			{
				$this->log('message',"Nagios Configuration Check Passed");
				$nagiosClient = new SM_NagiosClient();
				//if($nagiosClient->restart())
				if($nagiosClient->reload())
					$this->log('message',"Nagios Restarted");
			}
			else
			{
				$errors=$this->ql->getErrors();
				foreach($errors as $msg)
					$this->log('error',$msg);
				$this->log('error',"Nagios Configuration Check Failed");
				$continue=!$continue;
			}
		}
		else
		{
			$this->log('error',"***** ATTENTION: Nagios Configuration Aborted *****");
			
		}
		$this->ql->logout();
		return $continue;
	}
	
	function removeNagiosConfiguration($results=null)
	{
		foreach($results as $i=>$data)
		{
			if(!empty($data['type']))
			{
				if($data['type']=='host')
				{
					$host['id']=$data['qlid'];
					$this->ql->remove_host($host);
				}
				else if($data['type']=='service' || $data['type']=='metric')
				{
					$service['id']=$data['qlid'];
					$this->ql->remove_service($service);
				}
				else if($data['type']=='hostgroup')
				{
					$hostgroup['id']=$data['qlid'];
					$hostgroup['hostgroup_name']="Hostgroup ".$data['qlid'];
					$this->ql->remove_hostGroup($hostgroup);
				}
				else if($data['type']=='servicegroup')
				{
					$servicegroup['id']=$data['qlid'];
					$servicegroup['servicegroup_name']="ServiceGroup ".$data['qlid'];
					$this->ql->remove_serviceGroup($servicegroup);
				}
			}
		}
	}
	
	public function prepareNagiosData(&$data)
	{
		
		foreach($data['nagios'] as $k=>$v)
		{
			if($k=="host")
			{
				
				if((bool)count(array_filter(array_keys($v), 'is_string')))
				{
					$host_type=$v['notes'];
					/*if(isset($v['use_template']) && isset($this->templates[$host_type][$v['use_template']]))
						$data['nagios'][$k]['use_template']=$this->templates[$host_type][$v['use_template']];*/
					if(isset($v['use_template']))
					{
						if(is_array($v['use_template']))
						{
							foreach($v['use_template'] as $i=>$t)
							{
								if( isset($this->templates[$host_type][$t]))
									$data['nagios'][$k]['use_template'][$i]=$this->templates[$host_type][$t];
							}
						}
						else
						{
							if( isset($this->templates[$host_type][$v['use_template']]))
								$data['nagios'][$k]['use_template']=array($this->templates[$host_type][$v['use_template']]);
						}
					
					}
					$data['nagios'][$k]['host_name']=$this->nagiosEscapeStr($data['nagios'][$k]['host_name']);
					$hostgroup_data=array();			
					if(isset($v['hostgroups'])  )
					{
						if(is_array($v['hostgroups']))
							$hostgroup_data=array_merge($hostgroup_data,$v['hostgroups']);
						else 
							$hostgroup_data[]=$v['hostgroups'];
					}
					if(!empty($hostgroup_data))
						$data['nagios'][$k]['hostgroups']=$hostgroup_data;
					
				}
				else
				{
					foreach($v as $j=>$h)
					{
						$host_type=$v[$j]['notes'];
						if(isset($v[$j]['use_template']))
						{
							if(is_array($v[$j]['use_template']))
							{
								foreach($v[$j]['use_template'] as $i=>$t)
								{
									if( isset($this->templates[$host_type][$t]))
										$data['nagios'][$k][$j]['use_template'][$i]=$this->templates[$host_type][$t];
								}
							}
							else
							{
								if( isset($this->templates[$host_type][$v[$j]['use_template']]))
									$data['nagios'][$k][$j]['use_template']=array($this->templates[$host_type][$v[$j]['use_template']]);
							}
								
						}
							
						$data['nagios'][$k][$j]['host_name']=$this->nagiosEscapeStr($data['nagios'][$k][$j]['host_name']);
						$hostgroup_data=array();
						if(isset($v[$j]['hostgroups']) )
						{
							if(is_array($v[$j]['hostgroups']))
								$hostgroup_data=array_merge($hostgroup_data,$v[$j]['hostgroups']);
							else
								$hostgroup_data[]=$v[$j]['hostgroups'];
						}
							
						if(!empty($hostgroup_data))
							$data['nagios'][$k][$j]['hostgroups']=$hostgroup_data;
					}
				}
				continue;
			}
			
			if($k=="service")
			{
				if((bool)count(array_filter(array_keys($v), 'is_string')))
				{
					if(is_array($v['host_name']) && count($v['host_name'])>1)
					{
						$N = count($v['host_name']);
						for($i=0; $i<$N; $i++)
						{
							if(isset($v['use_template']) && isset($this->templates[$k][$v['use_template']]))
								$v['use_template']=$this->templates[$k][$v['use_template']];
							$copy[$i]=$v;
							unset($copy[$i]['host_name']);
							unset($copy[$i]['config_name']);
							unset($copy[$i]['service_description']);
							$copy[$i]['host_name']=$this->nagiosEscapeStr($v['host_name'][$i]);
							$copy[$i]['config_name']=$this->nagiosEscapeStr($v['config_name']."-".$i);
							$copy[$i]['service_description']=$this->nagiosEscapeStr($s['service_description'][$i]);
							if(isset($v['check_command']))
								$copy[$i]['check_command']=$this->createNagiosCmdData($v);
								
						}
						if(count($copy)==$N)
						{
							$data['nagios'][$k]=$copy;
						}
						
					}
				}
				else
				{
					$services = array();
					foreach($v as $j=>$s)
					{
						if(is_array($s['host_name']) && count($s['host_name'])>1)
						{
							$N = count($s['host_name']);
							$copy=array();
							for($i=0; $i<$N; $i++)
							{
								if(isset($s['use_template']) && isset($this->templates[$k][$s['use_template']]))
									$s['use_template']=$this->templates[$k][$s['use_template']];
								$copy[$i]=$s;
								unset($copy[$i]['host_name']);
								unset($copy[$i]['config_name']);
								unset($copy[$i]['service_description']);
								$copy[$i]['host_name']=$this->nagiosEscapeStr($s['host_name'][$i]);						
								$copy[$i]['config_name']=$this->nagiosEscapeStr($s['config_name']."-".$i);
								$copy[$i]['service_description']=$this->nagiosEscapeStr($s['service_description'][$i]);
								if(isset($s['check_command']))
									$copy[$i]['check_command']=$this->createNagiosCmdData($s);
							}
							if(count($copy)==$N)
							{
								$services=array_merge($services,$copy);
								
							}
						}
						else 
						{
							if(isset($s['use_template']) && isset($this->templates[$k][$s['use_template']]))
								$s['use_template']=$this->templates[$k][$s['use_template']];
							$copy=array();
							$copy[0]=$s;
							unset($copy[0]['host_name']);
							unset($copy[0]['config_name']);
							unset($copy[0]['service_description']);
							$copy[0]['host_name']=$this->nagiosEscapeStr($s['host_name']);
							$copy[0]['config_name']=$this->nagiosEscapeStr($s['config_name']);
							$copy[0]['service_description']=$this->nagiosEscapeStr($s['service_description']);
							
							if(isset($s['check_command']))
									$copy[0]['check_command']=$this->createNagiosCmdData($s);
							$services=array_merge($services,$copy);
						}
					}
					//unset($data['nagios'][$k]);
					$data['nagios'][$k]=$services;
					continue;
				}
			}
			
		}
	}
	
	public function createNagiosCmdData($s)
	{
		$command=array();
		$name="";
		$cmd=null;
		$NQl=new SM_NagiosQL();
		$tplArgs=null;
		if(isset($s['check_command']['name']))
		{
			$name=$this->getNagiosCommand($s['check_command']['name']);
			$cmd=$NQl->getCommand($name);
		}
		else if(isset($s['use_template']))
		{
			$sTpl=$NQl->getServiceTemplate($s['use_template']);
			$id=explode("!", $sTpl['check_command']);
			$cmd=$NQl->getCommandById($id[0]);
			$tplArgs=$id;
			unset($tplArgs[0]);
		}

		if(isset($cmd) && !empty($cmd) && is_array($cmd))
		{
			$command['commandId']=$cmd['id'];
			if(preg_match_all('/-{0,2}?([A-Za-z0-9]+)?\s+"{0,1}\$(ARG[0-9]+)\$"{0,1}/', $cmd['command_line'], $matches))
			{
				foreach($matches[1] as $i=>$v)
					$args[$v]=$matches[2][$i];
				$command['commandId']=$cmd['id'];
				$arguments=array();
				$params=array();
				if(isset($s['check_command']['args']))
					parse_str($s['check_command']['args'],$params);
			/*	if($name!=$s['service_description'])
					$params['m']=$s['service_description'];*/
				foreach($args as $n=>$v){
					if(isset($params[$n]))
					{
						$arguments[$v]=$params[$n];
						unset($params[$n]);
					}
					else if(isset($tplArgs))
						$arguments[$v]=$tplArgs[str_replace("ARG","", $v)];
					else
						$arguments[$v]="''";
				}
				if(count($params)>0 && isset($args['']))
				{
					$p=array();
					foreach($params as $k=>$v)
					{
						if(!empty($v))
							$p[]="-".$k." ".$v;
					}
					if(count($p)>0)
						$arguments[$args['']]=implode(" ",$p);//http_build_query($params);
				}
				
				$command['args']=$arguments;
			}
		}
		return $command;		
	}
	
	protected function getNagiosCommand($name)
	{
		foreach($this->commands['command'] as $k=>$command)
		{
			if($command['name']==$name)
				return $command['plugin'];
		} 
		return "check_php";
	}
	
	static function nagiosEscapeStr($str)
	{
		
		$s=str_replace(",", "", $str);
		$s=str_replace("(", "", $s);
		$s=str_replace(")", "", $s);
		$s=str_replace(":", ".", $s);
		$s=str_replace(" ", "_", $s);
		$s=str_replace("%", "", $s);
		return $s;
	}
	
	public function mapNagiosData($str)
	{
		$xsltFile = realpath(__DIR__).DIRECTORY_SEPARATOR."schema".DIRECTORY_SEPARATOR.sm_Config::get("SMNAGIOSCONFIGURATORXSLTFILE",SMNAGIOSCONFIGURATORXSLTFILE);
		$xslt = new XSLT_Processor();
		$xslt->setSchemaDir( realpath(__DIR__).DIRECTORY_SEPARATOR."schema".DIRECTORY_SEPARATOR);
		if(is_file($str))
			$xml=$xslt->mapFile($str, $xsltFile);
		elseif(is_string($str))
			$xml=$xslt->mapString($str, $xsltFile);
		$dom = new DOMDocument();
		$a=$dom->loadXML($xml);
		//Check if xml is well-formed 
		if($a && !empty($xml))
		{
			$this->log('message',"XML Nagios Model Generated Successfully");
			return $xml;
		}	
		$this->log('error',"Error when generating XML Nagios Model (Cause: malformed xml)");
		return NULL;
	}
	
	function existsNagiosData($qlid,$qltable)
	{
		$data=array(
				"qlid"=>$qlid,
				"qltable"=>$qltable);
		$res = $this->db->select(SMNAGIOSCONFIGURATORTABLE,$data);
		return $this->db->getNumRowsReturned()=='1';
	}
	
	function writeNagiosData($data=null){
		if(isset($data) && isset($data['qlid']) && !$this->existsNagiosData($data['qlid'],$data['qltable']))
			$this->db->save(SMNAGIOSCONFIGURATORTABLE,$data);
	}
	
	function removeNagiosData($data=null){
		if(isset($data) && isset($data['qlid']) && !$this->existsNagiosData($data['qlid'],$data['qltable']))
			$this->db->delete(SMNAGIOSCONFIGURATORTABLE,$data);
	}
	
	function deleteNagiosData($cid=null)
	{
		if($cid)
			$this->db->delete(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid));
	}
	
	function rollback($cid=null)
	{
		if($cid)
		{
			$this->log('message',"***** ROLLBACK Activated *****");		
			if(!$this->ql->login())
			{
				$this->log('error',"ROLLBACK: Login/Connection Failed with NagiosQL");
				return false;
			}
			$results=$this->db->select(SMNAGIOSCONFIGURATORTABLE,array("cid"=>$cid));
			if($results)
			{
				$this->removeNagiosConfiguration($results);
			}
			$this->deleteNagiosData($cid);
			$this->log('message',"***** ROLLBACK Nagios Data Success *****");
			if($this->ql->verify())
			{
				$this->log('message',"ROLLBACK: Nagios Configuration Check Passed");
				$nagiosClient = new SM_NagiosClient();
			//	if($nagiosClient->restart())
				if($nagiosClient->reload())
					$this->log('message',"ROLLBACK: Nagios Restarted");
			}
			else
			{
				$this->log('error',"ROLLBACK: Nagios Configuration Check Failed");
			}
			
			$this->ql->logout();
			$this->log('message',"***** ROLLBACK Terminated *****");
			return true;
		}
		return false;
	}
	
	
	static function install($db)
	{
		if(class_exists("SM_Monitor"))
		{
			sm_Config::set('SMNAGIOSCONFIGURATORXSLTFILE',array('value'=>SMNAGIOSCONFIGURATORXSLTFILE,"description"=>'Nagios XSLT Map File'),"SM_Monitor");
			sm_Config::set('SMNAGIOSCONFIGURATORTEMPLATESFILE',array('value'=>SMNAGIOSCONFIGURATORTEMPLATESFILE,"description"=>'Nagios Templates Map'),"SM_Monitor");
			sm_Config::set('SMNAGIOSCONFIGURATORCMDSFILE',array('value'=>SMNAGIOSCONFIGURATORCMDSFILE,"description"=>'Nagios Commands & Service Map'),"SM_Monitor");
			sm_Config::set("SMNAGIOSCONFIGURATORROLLBACK",array('value'=>0,"description"=>'Enable/Disable Nagios Rollback Commands (Enabled=1, Disabled=0)'),"SM_Monitor");
			$sql="CREATE TABLE IF NOT EXISTS `".SMNAGIOSCONFIGURATORTABLE."` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`qlid` int(11) NOT NULL,
			`qltable` varchar(128) NOT NULL,
			`type` varchar(1024) NOT NULL,
			`cid` int(11) NOT NULL,
	  		`sid` int(11) NOT NULL,
			`status` int(11) DEFAULT '0',
	  		PRIMARY KEY (`id`),
	  		KEY `qlid` (`qlid`),
			KEY `cid` (`cid`)
			)
			ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	
			$result=$db->query($sql);
			if($result)
			{
				sm_Logger::write("Installed ".SMNAGIOSCONFIGURATORTABLE." table");
				return true;
			}
			return false;
		}
		
	}
	
	static function uninstall($db)
	{
		sm_Config::delete('SMNAGIOSCONFIGURATORCMDSFILE');
		sm_Config::delete('SMNAGIOSCONFIGURATORTEMPLATESFILE');
		sm_Config::delete('SMNAGIOSCONFIGURATORXSLTFILE');
		sm_Config::delete('SMNAGIOSCONFIGURATORROLLBACK');
		
		$sql="DROP TABLE IF EXISTS `".SMNAGIOSCONFIGURATORTABLE."`;";
		$result=$db->query($sql);
		sm_Logger::write("Uninstalled ".__CLASS__);
		return true;
	}
	
	function log($type,$msg)
	{
		$this->report[$type][]=$msg;
		sm_Logger::write($msg);
	}
	
	function setLogFilename($file)
	{
		$this->logger->fileLog=$file;
	}
	
	static public function createNagiosCfgFile($type,$data)
	{
		$tpl=new sm_HTML();
		$tpl->setTemplateId("nagios_cfg", SM_NagiosPlugin::instance()->getFolderUrl("templates")."nagios.tpl.cfg");
		$tpl->insert("object",str_replace("_"," ",$type));
		foreach($data as $v)
		{
			$obj = new sm_HTML();
			$obj->setTemplateId($type, SM_NagiosPlugin::instance()->getFolderUrl("templates")."nagios.tpl.cfg");
			$obj->insertArray($v);
			$tpl->insert("definitions",$obj);
		}
		return $tpl->render();
	}
	
	public function getReport(){
		return $this->report;
	}
	
	public function getErrors(){
		if(isset($this->report['error']))
			return $this->report['error'];
		return array();
	}
	
}	