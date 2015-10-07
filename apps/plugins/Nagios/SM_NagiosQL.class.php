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

define('NAGIOSQLDBHOST',"localhost");
define('NAGIOSQLDBUSER',"user");
define('NAGIOSQLDBPWD',"password");
define('NAGIOSQLDBNAME',"nagiosql");
define('NAGIOSQLDEFAULTHOST','generic-host');
define('NAGIOSQLDEFAULTSERVICE','generic-service');

class SM_NagiosQL implements sm_Module
{
	protected $message;
	protected $qlClient;
	protected $db;
	
	function __construct()
	{
		$this->message="";
		$this->qlClient = new SM_NagiosQLClient();
		$NagiosQLDBhost = sm_Config::get('NAGIOSQLDBHOST',NAGIOSQLDBHOST);
		$NagiosQLDBusr = sm_Config::get('NAGIOSQLDBUSER',NAGIOSQLDBUSER);
		$NagiosQLDBpwd = sm_Config::get('NAGIOSQLDBPWD',NAGIOSQLDBPWD);
		$NagiosQLDBname = sm_Config::get('NAGIOSQLDBNAME',NAGIOSQLDBNAME);
		$this->db=new sm_Database($NagiosQLDBhost, $NagiosQLDBusr, $NagiosQLDBpwd);
		$this->db->setDB($NagiosQLDBname);
	}
	
	/*
	 *  HOST Management Section
	 */
	
	function check_data($hostname,$ip)
	{
   		$invalid = false;
        //check se IP � valido
	    if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/', $ip))
	    {
	       sm_Logger::write("Invalid IP (".$ip.")",__CLASS__,__METHOD__);
	       $invalid = true;
	    }
	    else 
	    	sm_Logger::write("IP (".$ip.") ==> OK ",__CLASS__,__METHOD__);
	   	//check se host � valido
	   	$regex2='([\w]+)';
	   	//$regex='([a-z0-9][a-z0-9-]{0,62}[\.])+([a-z]{2,4})';
	   	$regex='([A-Za-z0-9][A-Za-z0-9-@_\.]{0,64})+([a-z0-9]{0,8})';
	   	if(!preg_match("/^$regex$/i", $hostname) && !preg_match("/^$regex2$/i", $hostname))
	    {
	        sm_Logger::write("Invalid hostname (".$hostname.")",__CLASS__,__METHOD__);
	        $invalid = true;
	    }
	    else
	    	sm_Logger::write("Hostname (".$hostname.") ==> OK ",__CLASS__,__METHOD__);
    	if($invalid)
    		$message="Invalid Host Parameters";
	    return $invalid;
	}
	
	function host($request)
	{
    	$host_data = $request;
        // verifica se o host e ip sono corretti
    	if($this->check_data($host_data['host_name'], $host_data['address']))
    		return false;
    	//$hostname = $host_data['name'];//."-".$host_data['ip'];
        $host=$this->getHostByName($host_data['host_name'], $host_data['address']);
    	if($host)
    		return false;
    	if(!isset($host_data['use_template']))
    		$host_data['use_template'][]=NAGIOSQLDEFAULTHOST;
    	if(isset($host_data['use_template']))
    	{
    		$templates=array();
    		//$templates=explode(",",$host_data['use_template']);
    		if(is_array($host_data['use_template']))
    			$templates=$host_data['use_template'];
    		else 
    			$templates[]=$host_data['use_template'];
    		$tids=array();
    		//$tids[]=1;
    		foreach($templates as $t=>$v)
    		{
    			$template=$this->getHostTemplate($v);
    			if(!$template)
    				sm_Logger::write("Template (".$v.") does not exist!");
    			else //if($template['id']!=1)
    			{
    				$tids[] = $template['id'];
    				sm_Logger::write("Template $v (". $template['id'].")!");
    			}
    		}
    		if(count($tids))
    			$host_data['use_template']=($tids);
    		else
    		{
    			$template=$this->getHostTemplate(NAGIOSQLDEFAULTHOST);
    			if(!$template)
    				sm_Logger::write("Template (".$v.") does not exist!");
    			else //if($template['id']!=1)
    			{
    				unset($host_data['use_template']);
    				$host_data['use_template'][] = $template['id'];
    				sm_Logger::write("Template $v (". $template['id'].")!");
    			}
    		}   		
    	}
    	
    	if(isset($host_data['hostgroups']))
    	{
    		//$hostgroups=explode(",",$host_data['hostgroups']);
    		$hostgroups=$host_data['hostgroups'];
    		$hgid=array();
    		foreach($hostgroups as $t=>$v)
    		{
    			$hostgroup=$this->getHostGroup($v);
    			if(!$hostgroup)
    				sm_Logger::write("Hostgroup (".$v.") does not exist!");
    			else
    				$hgid[] = $hostgroup['id'];
    		}
    		if(count($hgid))
    			$host_data['hostgroups']=$hgid;
    	}
    	if(isset($host_data['parents']))
    	{
    		$parents=explode(",",$host_data['parents']);
    		$pids=array();
    		foreach($parents as $t=>$v)
    		{
    			$parent=$this->getHost(array("alias"=>$v));
    			if(!$parent)
    				sm_Logger::write("Parent Host (".$v.") does not exist!");
    			else
    				$pids[] = $parent['id'];
    		}
    		if(count($pids))
    			$host_data['parents']=$pids;
    	}
    	if(isset($host_data['contacts']))
    	{
    		$contacts=explode(",",$host_data['contacts']);
    		$contactsId=array();
    		foreach($contacts as $t=>$v)
    		{
    			$contact=$this->getContact($v);
    			if(!$contact)
    				sm_Logger::write("Contact (".$v.") does not exist!");
    			else
    				$contactsId[] = $contacts['id'];
    		}
    		$host_data['contacts']=$contactsId;
    	}
    	
    	$ip=$host_data['address'];
    	sm_Logger::write("Creating the Hostname (".$host_data['host_name'].") ");
    	
    	$this->qlClient->create_host($host_data);
    	$host=$this->getHostByName($host_data['host_name'],$host_data['address']);
    	if(!$host)
    		return false;
    	$hostId=$host['id'];
    	sm_Logger::write(sprintf("Writing the hostname %s hostID: %s",$host_data['host_name'], $host_data['address'], $hostId));
    	sleep(1);
    	$this->qlClient->write_host(intval($hostId));
    	sm_Logger::write(sprintf("Hostname %s successfully created",$host_data['host_name']));
    	$message=sprintf("Hostname %s successfully created",$host_data['host_name']);
    	return true;
	}
	
	function remove_host($host)
	{
		if(!isset($host['id']))
			return false;
		$hostId=$host['id'];
		$this->qlClient->delete_host(intval($hostId));
	}
	
	function updateHost($request)
	{
		
	}
	
	function getHostByName($name,$ip=null)
	{
		if(isset($ip))
			$where=array("host_name"=>$name,"address"=>$ip);
		else
			$where=array("host_name"=>$name);
		$host=$this->db->selectRow("tbl_host",$where);
		if(isset($host))
		{
			sm_Logger::debug($host,__CLASS__,__METHOD__);
			return $host;
		}
		else
			return null;
	}
	
	function getHost($where=null)
	{
		if(is_array($where))
		{
			$host=$this->db->selectRow("tbl_host",$where);
			if(isset($host))
			{
				sm_Logger::debug($host,__CLASS__,__METHOD__);
				return $host;
			}
		}
			return null;
	}
	
	function getContact($name)
	{
		$where=array("contact_name"=>$name);
		$contact=$this->db->selectRow("tbl_contact",$where);
		if(isset($contact))
		{
			sm_Logger::debug($contact);
			return $contact;
		}
		else
			return null;
	}
	
	function getCommand($name)
	{
		$where=array("command_name"=>$name);
		$command=$this->db->selectRow("tbl_command",$where);
		if(isset($command))
		{
			sm_Logger::debug($command);
			return $command;
		}
		else
			return null;
	}
	
	function getCommandById($id)
	{
		$where=array("id"=>$id);
		$command=$this->db->selectRow("tbl_command",$where);
		if(isset($command))
		{
			sm_Logger::debug($command);
			return $command;
		}
		else
			return null;
	}
	
	function getHostTemplate($templateName)
	{
		$where=array("template_name"=>$templateName);
		$template=$this->db->selectRow("tbl_hosttemplate",$where);
		if(isset($template))
		{
			sm_Logger::debug($template,__CLASS__,__METHOD__);
		 	return $template;
		}
		else
			return null;
	}

	/*
	 *  SERVICE Management Section
	*/
	
	function service($request)
	{
		$service_data = $request;
		// verifica se o service e ip sono corretti
		/*if($this->check_data($service_data['service_name'], $service_data['address']))
			return false;*/
		//$servicename = $service_data['name'];//."-".$service_data['ip'];
		$service=$this->getService($service_data['config_name']);
		if($service)
			return false;
		if(!isset($service_data['use_template']))
			$service_data['use_template']=NAGIOSQLDEFAULTSERVICE;
		
		$host_data=explode("@",$service_data['host_name']);
		if(isset($host_data[1]))
			$host=$this->getHostByName($service_data['host_name'],$host_data[1]);
		else 
			$host=$this->getHostByName($service_data['host_name']);
		if($host)
			$service_data['host_name']=$host['id'];
		
		if(isset($service_data['use_template']))
		{
			$templates=explode(",",$service_data['use_template']);
			$tids=array();
			//$tids[]=1;
			foreach($templates as $t=>$v)
			{
				$template=$this->getServiceTemplate($v);
				if(!$template)
					sm_Logger::write("Template (".$v.") does not exist!");
				else //if($template['id']!=1)
				{
					$tids[] = $template['id'];
					sm_Logger::write("Template $v (". $template['id'].")!");
				}
			}
			if(count($tids))
				$service_data['use_template']=($tids);
		}
		 
		if(isset($service_data['servicegroups']))
		{
			$servicegroups=explode(",",$service_data['servicegroups']);
			$hgid=array();
			foreach($servicegroups as $t=>$v)
			{
				$servicegroup=$this->getServiceGroup($v);
				if(!$servicegroup)
					sm_Logger::write("servicegroup (".$v.") does not exist!");
				else
					$hgid[] = $servicegroup['id'];
			}
			if(count($hgid))
				$service_data['servicegroups']=$hgid;
		}
		if(isset($service_data['contacts']))
		{
			$contacts=explode(",",$service_data['contacts']);
			$contactsId=array();
			foreach($contacts as $t=>$v)
			{
				$contact=$this->getContact($v);
				if(!$contact)
					sm_Logger::write("Contact (".$v.") does not exist!");
				else
					$contactsId[] = $contacts['id'];
			}
			$service_data['contacts']=$contactsId;
		}
		 
		
		sm_Logger::write("Creating the servicename (".$service_data['config_name'].") ");
		 
		$this->qlClient->create_service($service_data);
		$service=$this->getService($service_data['config_name']);
		if(!$service)
			return false;
		$serviceId=$service['id'];
		sm_Logger::write(sprintf("Writing the servicename %s serviceID: %s",$service_data['config_name'], $serviceId));
		sleep(1);
		$this->qlClient->write_service(intval($serviceId));
		sm_Logger::write(sprintf("Service with config_name %s successfully created",$service_data['config_name']));
		$message=sprintf("Service with config_name %s successfully created",$service_data['config_name']);
		return true;
	}
	
	function remove_service($service)
	{
		if(!isset($service['id']))
			return false;
		$serviceId=$service['id'];
		$this->qlClient->delete_service(intval($serviceId));
		
	}
	
	function getService($name)
	{
		$where=array("config_name"=>$name);
		$service=$this->db->selectRow("tbl_service",$where);
		if(isset($service))
		{
			sm_Logger::debug($service,__CLASS__,__METHOD__);
			return $service;
		}
		else
			return null;
	}
	
	function getServiceById($id)
	{
		$where=array("id"=>$id);
		$service=$this->db->selectRow("tbl_service",$where);
		if(isset($service))
		{
			sm_Logger::debug($service,__CLASS__,__METHOD__);
			return $service;
		}
		else
			return null;
	}
	
	function getServiceTemplate($templateName)
	{
		$where=array("template_name"=>$templateName);
		$template=$this->db->selectRow("tbl_servicetemplate",$where);
		if(isset($template))
		{
			sm_Logger::debug($template,__CLASS__,__METHOD__);
		 	return $template;
		}
		else
			return null;
	}
	
	
	/*
	 *  HOSTGroup Management Section
	*/
	
	function getHostGroup($name)
	{
		$where=array("hostgroup_name"=>$name,);
		$hostgroup=$this->db->selectRow("tbl_hostgroup",$where);
		if(isset($hostgroup))
		{
			sm_Logger::debug($hostgroup);
			return $hostgroup;
		}
		else
			return null;
	}
	
	/*
	 *  HOSTGroup Management Section
	*/
	
	function getHostGroupServices($name)
	{
		$where=array("hostgroup_name"=>$name);
		$hostgroup=$this->db->selectRow("tbl_hostgroup",$where);
		if(isset($hostgroup))
		{
			sm_Logger::debug($hostgroup);
			$where=array("idSlave"=>$hostgroup['id']);
			$services=$this->db->select("tbl_lnkServiceToHostgroup",$where);
			sm_Logger::debug($services);
			return $services;
		}
		else
			return null;
	}
	
	function hostGroup($request)
	{
		$hostsGroup=$request['hostgroup_name'];
		sm_Logger::write("Creating the Hostname (".$hostsGroup.") ");
		$this->qlClient->create_hostgroups($request);
		$this->qlClient->write_hostgroups();
		sm_Logger::write(sprintf("Hostsgroup %s successfully created",$hostsGroup));
	}
	
	function remove_hostGroup($request)
	{
		$hostsGroupId=$request['id'];
		sm_Logger::write("Deleting the HostGroup (".$request['hostgroup_name'].") ");
		$this->qlClient->delete_hostgroups($hostsGroupId);
		$this->qlClient->write_hostgroups();
		sm_Logger::write(sprintf("Hostsgroup %s deleted successfully",$request['hostgroup_name']));
	}
	
	/*
	 *  SERVICEGroup Management Section
	*/
	
	function getServiceGroup($name)
	{
		$where=array("servicegroup_name"=>$name);
		$servicegroup=$this->db->selectRow("tbl_servicegroup",$where);
		if(isset($servicegroup['id']))
		{
			$where=array("idMaster"=>$servicegroup['id']);
			$fields=array("idSlave");
			$members=$this->db->selectRow("tbl_lnkServicegroupToServicegroup",$where,$fields);
			if(isset($members) && is_array($members));
			{
				$servicegroup['servicegroups_members']=array();			
				foreach($members as $member)
				{
					if(isset($member['idSlave']))
						$servicegroup['servicegroups_members'][]=$member['idSlave'];
				}
			}
			$fields=array("idSlaveH","idSlaveHG","idSlaveS");
			$members=$this->db->select("tbl_lnkServicegroupToService",$where,$fields);
			if(isset($members) && is_array($members))
			{
				$servicegroup['host_members']=array();
				foreach($members as $member)
				{
					$servicegroup['host_members'][]=implode("::",$member);
				}
			}
			sm_Logger::debug($servicegroup);
			return $servicegroup;
		}
		else
			return null;
	}
	
	function createServiceGroupMembers($hostgroupName, $hostName)
	{
		$members=array();
		$h=$this->getHostByName($hostName);
		$sg=$this->getHostGroupServices($hostgroupName);		
		if($sg && $h)
		{
			foreach ($sg as $service)
			{
				$members[]=$h['id']."::0::".$service['idMaster'];
			}
			
			sm_Logger::write("ServiceGroupMembers Created successfully",null);
		}
		else 
			sm_Logger::write("Members not found!",null);
		//return !empty($members)?$members:array();
		return $members;
	}
	
	function serviceGroup($request)
	{
		$serviceGroup=$request['servicegroup_name'];
		sm_Logger::write("Creating the ServiceGroup (".$serviceGroup.") ");
		$this->qlClient->create_servicegroups($request);
		$this->qlClient->write_servicegroups();
		sm_Logger::write(sprintf("Servicegroup %s successfully created",$serviceGroup));
	}
	
	function updateServiceGroup($request)
	{
		$serviceGroup=$request['servicegroup_name'];
		sm_Logger::write("Updating the ServiceGroup (".$serviceGroup.") ");
		$this->qlClient->update_servicegroups($request);
		$this->qlClient->write_servicegroups();
		sm_Logger::write(sprintf("Servicegroup %s successfully updated",$serviceGroup));
	}
	
	function remove_serviceGroup($request)
	{
		$serviceGroupId=$request['id'];
		sm_Logger::write("Deleting the ServiceGroup (".$request['servicegroup_name'].") ");
		$this->qlClient->delete_servicegroups($serviceGroupId);
		$this->qlClient->write_servicegroups();
		sm_Logger::write(sprintf("ServiceGroup %s deleted successfully",$request['servicegroup_name']));
	}
	
	
	function getAllServices($where=array(),$fields=array()){
		if(is_array($where))
		{
			$services=$this->db->select("tbl_service",$where,$fields);
			if($services)
			{
				sm_Logger::debug($services,__CLASS__,__METHOD__);
				return $services;
			}
		}
		return null;
	}
	
	/*
	 *  Utiliy Section
	*/
	
	function verify()
	{
		return $this->qlClient->verify();
	}
	
	function apply()
	{
		return $this->qlClient->apply();
	}
	
	function logout()
	{
		return $this->qlClient->Logout();
	}
	
	function login()
	{
		return $this->qlClient->Login();
	}
	
	function getErrors(){
		return $this->qlClient->getLastErrors();
	}
	
	static function isAlive($timeout=5){
		$t=microtime();
		$old=sm_Logger::$debug;
		sm_Logger::$debug=false;
		//$tool=new SM_NagiosQL();
		$url = sm_Config::get('NAGIOSQLURL',"");
		$ret=true;
		if($url!="")
		{
			$h=parse_url($url);
			$fp=@fsockopen($h['host'],80,$errno,$errstr,$timeout);
			$ret=$fp===false;
			if(is_resource($fp))
				@fclose($fp);
		}
		sm_Logger::$debug=$old;
		return !$ret;
	}
	
	static function install($db)
	{
		sm_Config::set('NAGIOSQLDBHOST',array('value'=>NAGIOSQLDBHOST,"description"=>'NagiosQL Database Host'));
		sm_Config::set('NAGIOSQLDBUSER',array('value'=>NAGIOSQLDBUSER,"description"=>'NagiosQL Database User'));
		sm_Config::set('NAGIOSQLDBPWD',array('value'=>NAGIOSQLDBPWD,"description"=>'NagiosQL Database Password'));
		sm_Config::set('NAGIOSQLDBNAME',array('value'=>NAGIOSQLDBNAME,"description"=>'NagiosQL Database Name'));
	} 
	
	static function uninstall($db)
	{
		sm_Config::delete('NAGIOSQLDBHOST');
		sm_Config::delete('NAGIOSQLDBUSER');
		sm_Config::delete('NAGIOSQLDBPWD');
		sm_Config::delete('NAGIOSQLDBNAME');
	}
	
}
