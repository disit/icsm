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

/* NAGIOSQL API server configuration */
define('NAGIOSQLURL',"http://localhost/nagiosql320");
define('NAGIOSQLUSER',"admin");
define('NAGIOSQLPWD',"password");

class SM_NagiosQLClient  implements sm_Module
{
	public $nagiosQLUrl;
	public $nagiosQLUser;
	public $nagiosQLPwd;
	protected $lastError;
	protected $client;
	
	public function SM_NagiosQLClient($ip=null,$user=null,$pwd=null)
	{
		
		$NagiosQLurl = sm_Config::get('NAGIOSQLURL',NAGIOSQLURL);
		$NagiosQLusr = sm_Config::get('NAGIOSQLUSER',NAGIOSQLUSER);
		$NagiosQLpwd = sm_Config::get('NAGIOSQLPWD',NAGIOSQLPWD);
		$this->nagiosQLUrl=isset($ip)?$ip:$NagiosQLurl;
		$this->nagiosQLUser=isset($user)?$user:$NagiosQLusr;
		$this->nagiosQLPwd=isset($pwd)?$pwd:$NagiosQLpwd;
		$this->client = new SM_RestClient();
		
	}
	
	public function __destructor()
	{
		$this->close();
	}

	//function create_host($hostname, $ip, $templateId, $alias)
	function create_host($host)
	{
		$msg=sprintf("Saving hostname %s on nagiosql",$host['host_name']);
		sm_Logger::write($msg);
		$params = array(
				"tfValue1"=> $host['host_name'], 											//mandatory
				"tfValue3"=> $host['alias'],												//mandatory
				"tfValue4"=> isset($host['display_name'])?$host['display_name']:"",
				"tfValue5"=> $host['address'],  											//mandatory
				"tfValue7"=>  isset($host['notes'])?$host['notes']:"",
				"radValue1"=> 2, 
				"radValue2"=> 0, //=0 eredita hostgroups definiti nel template, =2 si usano solo i propri hostgroups
				"selValue1"=> 0, 
				"selValue2"=> 0,
				"chbRegister"=> 1, 
				"chbActive"=> 1, 
			//	"selTemplate"=> implode("::",$host['templateId']), //$host['templateId'][0], 
				"modus"=> "insert",
				"hidLimit"=> 0, 
				"radValue5"=> 2, 
				"radValue6"=> 2, 
				"selValue2"=> 0, 
				"radValue7"=> 2, 
				"radValue8"=> 2,
				"selValue3"=> 0, 
				"radValue9"=> 2, 
				"radValue10"=> 2, 
				"radValue11"=> 2, 
				"radValue12"=> 2, 
				"radValue13"=> 2,
				"radValue4"=> 2, 
				"radValue3"=> 2, 
				"selValue4"=> 0, 
				"radValue14"=> 2, 
				"selAccGr"=> 0);
		
		if(isset($host['parents'])) 
		{
			if(is_array($host['parents']) && count($host['parents'])>0)
			{
				foreach($host['parents'] as $i=>$v)
					$params["mselValue1[".$i."]"]=$v; //parents array *(tutti) o ids']
			}
			else 
				$params["mselValue1[0]"]=$host['parents'];
				
		}
		if(isset($host['hostgroups']))
		{
			if(is_array($host['hostgroups']) && count($host['hostgroups'])>0)
			{
				foreach($host['hostgroups'] as $i=>$v)
					$params["mselValue2[".$i."]"]=$v; //hostgroups array *(tutti) o ids']
			}
			else
				$params["mselValue2[0]"]=$host['hostgroups'];
		}
		if(isset($host['contacts']) && count($host['contacts'])>0)
		{
			if(is_array($host['contacts']) && count($host['contacts'])>0)
			{
				foreach($host['contacts'] as $i=>$v)
					$params["mselValue3[".$i."]"]=$v; //contacts array *(tutti) o ids']
			}
			else
				$params["mselValue3[0]"]=$host['contacts'];
		}
		if(isset($host['use_template']) && is_array($host['use_template']))
		{
			$url = $this->nagiosQLUrl."/admin/templatedefinitions.php";
			//sm_Logger::write($params);
			$data= array("dataId"=>"",
					"type"=>"host"
			
			);
			sm_Logger::write("Reset Template Data => GET to templatedefinition.php",__CLASS__,__METHOD__);
			$this->Get($url,$data);//,$user,$pwd);
			sm_Logger::write($this->client->getResponseMessage());
			foreach($host['use_template'] as $i=>$v)
			{
			//sm_Logger::write($params);
				$data= array("dataId"=>"",
						"type"=>"host",
						"mode"=>"add",
						"def"=>$v."::1"
				
				);
				//sm_Logger::write($data);
				$url = $this->nagiosQLUrl."/admin/templatedefinitions.php";
				//rget = self.GET("/nagiosql320/admin/templatedefinitions.php?dataId=&type=host&mode=add&def=%s" % templateId)
				sm_Logger::write("Create Template for Host => GET to templatedefinition.php");
				$this->Get($url,$data);//,$user,$pwd);
				sm_Logger::write($this->client->getResponseMessage());
				//sm_Logger::write($this->client->getResponse());
			}
		}
		if(isset($host['use_variables'] )&& is_array($host['use_variables']))
		{
			$url = $this->nagiosQLUrl."/admin/variabledefinitions.php";
			//sm_Logger::write($params);
			$data= array("dataId"=>"",
					"linktab"=>"tbl_lnkHostToVariabledefinition"
       
			);
			 
			sm_Logger::write("Reset Variables Data => GET to variabledefinitions.php",__CLASS__,__METHOD__);
			$this->Get($url,$data);//,$user,$pwd);
			sm_Logger::write($this->client->getResponseMessage());
			foreach($host['use_variables'] as $i=>$v)
			{
				if($i[0]!="_")
					$i='_'.$i;
				$data= array("dataId"=>"",
						"version"=>"3",
						"mode"=>"add",
						"def"=>$i,
						"range"=>$v
			
				);
				$url = $this->nagiosQLUrl."/admin/variabledefinitions.php";
				//rget = self.GET("/nagiosql320/admin/templatedefinitions.php?dataId=&type=host&mode=add&def=%s" % templateId)
				sm_Logger::write("Create Variables for Host => GET to variabledefinitions.php");
				$this->Get($url,$data);//,$user,$pwd);
				sm_Logger::write($this->client->getResponseMessage());
			}
		}
		
		//rpost = self.POST("/nagiosql320/admin/hosts.php", params)
		$url = $this->nagiosQLUrl."/admin/hosts.php";
		sm_Logger::write("Create Host => Post to hosts.php");
		$this->Post($url,$params); //,$user,$pwd);
		sm_Logger::write($this->client->getResponseMessage());
		
	}
	
	function write_host($host_id)
	{
		$params = array(
				"txtSearch"=> "", 
				"modus"=> "checkform", 
				"hidModify"=> "config", 
				"hidListId"=> $host_id, 
				"hidLimit"=> 0, 
				"hidSortBy"=> 1, 
				"hidSortDir"=> "ASC",
				"hidSort"=> 0, 
				"selModify"=> "none", 
				"selTargetDomain"=> 1 
				
		);
		sm_Logger::write("Calling method to write the file in disk");
		$url = $this->nagiosQLUrl."/admin/hosts.php";
		sm_Logger::write("Write Host => Post to hosts.php");
		//r = self.POST("/nagiosql320/admin/hosts.php", params)
		$this->Post($url,$params);//,$user,$pwd);
		sm_Logger::write($this->client->getResponseMessage());
		sm_Logger::write($this->client->getResponseCode());
	}
	
	function delete_host($host_id)
	{
       $params = array(
        		"txtSearch"=> "",
        		"modus"=> "checkform",
        		"hidModify"=> "delete",
        		"hidListId"=> $host_id,
        		"hidLimit"=> 0,
        		"hidSortBy"=> 1,
        		"hidSortDir"=> "ASC",
        		"hidSort"=> 0,
        		"selModify"=> "none",
        		"selTargetDomain"=> 1
        
        );
        sm_Logger::write("Calling delete method");
        $url = $this->nagiosQLUrl."/admin/hosts.php";
        sm_Logger::write("Delete Host => Post to hosts.php");
        //d = self.POST("/nagiosql320/admin/hosts.php", params)
        $code = $this->Post($url,$params);//,$user,$pwd);
        sm_Logger::write($this->client->getResponseMessage());
        return $code;
	}
	
	function create_hostgroups($hostGroup)
	{
		$params =array(
				"tfValue1"=>$hostGroup['hostgroup_name'],
				"tfValue2"=>$hostGroup['alias'],
				"tfValue3"=>"",
				"tfValue4"=>"",
				"tfValue5"=>"",
				"selAccGr"=>0,
				"chbRegister"=>1,
				"chbActive"=>1,
				"hidActive"=>"",
				"modus"=>"insert",
				"hidId"=>"",
				"hidLimit"=>0
				 
		);
		$url = $this->nagiosQLUrl."/admin/hostgroups.php";
		sm_Logger::write("Create hostgroups => Post to hostgroups.php",__CLASS__,__METHOD__);
		$this->Post($url,$params); //,$user,$pwd);
		sm_Logger::write($this->client->getResponseMessage());
			
	}
	
	function delete_hostgroups($hostGroupsId)
	{
		//chbId_9:on
		$params =array(
				"txtSearch"=>"",
				"modus"=>"checkform",
				"hidModify"=>"delete",
				"hidListId"=>$hostGroupsId,
				"hidLimit"=> 0,
				"hidSortBy"=> 1,
				"hidSortDir"=> "ASC",
				"hidSort"=> 0,
				"selModify"=> "none",
				"selTargetDomain"=> 1);
		$url = $this->nagiosQLUrl."/admin/hostgroups.php";
		sm_Logger::write("Calling delete  of hostgroups => Post to hostgroups.php");
		$code = $this->Post($url,$params);
		sm_Logger::write($this->client->getResponseMessage());
		sm_Logger::write($this->client->getResponseCode());
		return $code;
		
	}
	
	function write_hostgroups()
	{
       $params =array(
       		"modus"=>"make",
       		"hidLimit"=> 0,
       		"hidSortBy"=> 1,
       		"hidSortDir"=> "ASC",
       		"hidSort"=> 0,
       		"selModify"=> "none", 
       		"selTargetDomain"=> 1);
       $url = $this->nagiosQLUrl."/admin/hostgroups.php";
       sm_Logger::write("Calling write method of hostgroups => Post to hostgroups.php");
       $this->Post($url,$params);
       sm_Logger::write($this->client->getResponseMessage());
       sm_Logger::write($this->client->getResponseCode());
	}
	
	function create_service($service) //($hostId, $name, $serviceName, $commandId, $args, $templateId="4::1")
	{
            #config_name = name.replace (" ", "-")
            $params = array(
            "tfValue1"=> $service['config_name'], 
            "mselValue1[]"=> $service['host_name'], 
            "radValue1"=> 2, 
            "radValue2"=> 2, 
            "tfValue3"=> $service['service_description'], 
            "tfValue4"=> $service['display_name'],
            "chbRegister"=> 1, 
            "chbActive"=> 1,
            "radValue3"=> 2, 
            "selValue1"=> isset($service['check_command']['commandId'])?$service['check_command']['commandId']:"",
            //"tfArg1"=> $args, 
            //"selTemplate"=> $templateId, 
            "modus"=> "insert", 
            "hidLimit"=> 0, 
            "radValue4"=> 2,
            "radValue5"=> 2,
            "radValue6"=> 2, 
            "selValue2"=> 0, 
            "radValue7"=> 2, 
            "radValue8"=> 2, 
            "selValue3"=> 0, 
            "radValue9"=> 2, 
            "radValue10"=> 2,
            "radValue11"=> 2,
            "radValue12"=> 2, 
            "radValue13"=> 2, 
            "radValue14"=> 2, 
            "radValue16"=> 2, 
            "radValue15"=> 2, 
            "selValue4"=> 0, 
            "radValue17"=> 2,
            "selAccGr"=> 0);
            
            if(isset($service['check_command']['args']))
            {
            	$args=$service['check_command']['args'];
            	if(is_array($args))
	            {
	            		$i=1;
	            		foreach($args as $a=>$v)
	            		{
	            			$k="tf".ucfirst(strtolower($a));
	            			$params[$k]=$v;
	            			//$i++;
	            		}
	            }
	            else if(!empty($args))
	            	$params["tfArg1"]=$args;
            }
            
    /*        if(isset($service['parents']))
            {
            	foreach($service['parents'] as $i=>$v)
            		$params["mselValue1[".$i."]"]=$v; //parents array *(tutti) o ids']
            }*/
            if(isset($service['servicegroups']) && is_array($service['servicegroups']))
            {
            	foreach($service['servicegroups'] as $i=>$v)
            		$params["mselValue3[".$i."]"]=$v; //servicegroups array *(tutti) o ids']
            }
            if(isset($service['contacts']) && is_array($service['contacts']))
            {
            	foreach($service['contacts'] as $i=>$v)
            		$params["mselValue4[".$i."]"]=$v; //contacts array *(tutti) o ids']
            }

            if(isset($service['use_template']) && is_array($service['use_template']))
            {
            	$url = $this->nagiosQLUrl."/admin/templatedefinitions.php";
            	//sm_Logger::write($params);
            	$data= array("dataId"=>"",
            			"type"=>"service"
		
            	);
            	sm_Logger::write("Reset Template Data => GET to templatedefinition.php",__CLASS__,__METHOD__);
            	$this->Get($url,$data);//,$user,$pwd);
            	sm_Logger::write($this->client->getResponseMessage());

	            foreach($service['use_template'] as $i=>$v)
	            {
	            	//sm_Logger::write($params);
	            	$data= array("dataId"=>"",
	            			"type"=>"service",
	            			"mode"=>"add",
	            			"def"=>$v."::1"
			
	            	);
	            	
	            	//rget = self.GET("/nagiosql320/admin/templatedefinitions.php?dataId=&type=host&mode=add&def=%s" % templateId)
	            	sm_Logger::write("Create Template for Service => GET to templatedefinition.php",__CLASS__,__METHOD__);
	            	$this->Get($url,$data);//,$user,$pwd);
	            	sm_Logger::write($this->client->getResponseMessage());
	            	//sm_Logger::write($this->client->getResponse());
	            }
            }
            if(isset($service['use_variables']) && is_array($service['use_variables']))
            {
            	$url = $this->nagiosQLUrl."/admin/variabledefinitions.php";
            	//sm_Logger::write($params);
            	$data= array("dataId"=>"",
            			"linktab"=>"tbl_lnkServiceToVariabledefinition"
            	
            	);
            	
            	sm_Logger::write("Reset Variables Data => GET to variabledefinitions.php",__CLASS__,__METHOD__);
            	$this->Get($url,$data);//,$user,$pwd);
            	sm_Logger::write($this->client->getResponseMessage());
	            foreach($service['use_variables'] as $i=>$v)
	            {
	            		
	            	$data= array("dataId"=>"",
	            			"version"=>"3",
	            			"mode"=>"add",
	            			"def"=>$i,
	            			"range"=>$v
	            
	            	);
	            	$url = $this->nagiosQLUrl."/admin/variabledefinitions.php";
	            	//rget = self.GET("/nagiosql320/admin/templatedefinitions.php?dataId=&type=host&mode=add&def=%s" % templateId)
	            	sm_Logger::write("Create Variables for Service => GET to variabledefinitions.php",__CLASS__,__METHOD__);
	            	$this->Get($url,$data);//,$user,$pwd);
	            	sm_Logger::write($this->client->getResponseMessage());
	            }
            }
	            
           
          //  $url = $this->nagiosQLUrl."/admin/commandline.php";
         //   $this->Get($url,array('cname'=>$commandId));
         //   sm_Logger::write($this->client->getResponseMessage());
          
            $url = $this->nagiosQLUrl."/admin/services.php";
            sm_Logger::write("Create Service => Post to service.php",__CLASS__,__METHOD__);
            $this->Post($url,$params);
            sm_Logger::write($this->client->getResponseMessage());
	}
	
	public function write_service($serviceId)
	{
		$params = array(
				"selCnfName"=> "All configs", 
				"modus"=> "checkform", 
				"hidModify"=> "config",  
				"hidListId"=> $serviceId, 
				"hidLimit"=> 0, 
				"hidSortBy"=> 1,
				"hidSortDir"=> "ASC", 
				"hidSort"=> 0, 
				"selModify"=> "none", 
				"selTargetDomain"=> 1
				
		);
	
		sm_Logger::write("Calling method to write the file in disk");
		$url = $this->nagiosQLUrl."/admin/services.php";
		sm_Logger::write("Write Service => Post to services.php");
		$this->Post($url,$params);
		sm_Logger::write($this->client->getResponseMessage());
		sm_Logger::write($this->client->getResponseCode());
	}
	
	function alter_service($configName, $hostId, $description, $commandId, $args, $serviceId, $templateId)
	{
        $params = array(
        		"tfValue1"=> $configName, 
        		"tfValue2"=> $configName, 
        		"mselValue1[]"=> $hostId, 
        		"radValue1"=> 2, 
        		"radValue2"=> 2, 
        		"tfValue3"=> $description,
                "chbRegister"=> 1, 
        		"chbActive"=> 1, 
        		"radValue3"=> 2, 
        		"selValue1"=> $commandId, 
        		"tfArg1"=> $args, 
        		"selTemplate"=> $templateId,
                "modus"=> "modify", 
        		"hidId"=> $serviceId, 
        		"hidLimit"=> 0, 
        		"radValue4"=> 2, 
        		"radValue5"=> 2, 
        		"radValue6"=> 2, 
        		"selValue2"=> 0, 
        		"radValue7"=> 2,
                "radValue8"=> 2, 
        		"selValue3"=> 0, 
        		"radValue9"=> 2, 
        		"radValue10"=> 2, 
        		"radValue11"=> 2,  
        		"radValue12"=> 2, 
        		"radValue13"=> 2, 
        		"radValue14"=> 2,
                "radValue16"=> 2, 
        		"radValue15"=> 2, 
        		"selValue4"=> 0, 
        		"radValue17"=> 2, 
        		"selAccGr"=> 0
        		
        );
        if(isset($args) && is_array($args))
        {
        	$i=1;
        	foreach($args as $a=>$v)
        	{
        		$params["tfArg".$i]=$v;
        		$i++;
        	}
        }
        
        $data= array("dataId"=>"",
        		"type"=>"service",
        		"mode"=>"add",
        		"def"=>$templateId
        
        );
        sm_Logger::write("Alter_service ");
        $url = $this->nagiosQLUrl."/admin/templatedefinitions.php";
        $this->Get($url,$data);
        $url = $this->nagiosQLUrl."/admin/services.php";
        $this->Post($url, $params);
	}
	
	public function delete_service($serviceId)
	{
		$params = array
		(
				"selCnfName"=> "All configs",
				"modus"=> "checkform", 
				"hidModify"=> "delete", 
				"hidListId"=> $serviceId, 
				"hidLimit"=> 0, "hidSortBy"=> 1,
                "hidSortDir"=> "ASC", 
				"hidSort"=> 0, 
				"selModify"=> "none", 
				"selTargetDomain"=> 1
				
		);
        sm_Logger::write("Delete_service => Post to services.php");
        $url = $this->nagiosQLUrl."/admin/services.php";
        return $this->Post($url,$params);
	}
	
	function create_servicegroups($serviceGroup)
	{
		$params =array(
				"tfValue1"=>$serviceGroup['servicegroup_name'],
				"tfValue2"=>$serviceGroup['alias'],
				"tfValue3"=>"",
				"tfValue4"=>"",
				"tfValue5"=>"",
				"selAccGr"=>0,
				"chbRegister"=>1,
				"chbActive"=>1,
				"hidActive"=>"",
				"modus"=>"insert",
				"hidId"=>null,
				"hidLimit"=>0
					
		);
		if(isset($serviceGroup['servicegroups_members']) && is_array($serviceGroup['servicegroups_members']))
		{
			foreach($serviceGroup['servicegroups_members'] as $i=>$v)
				$params["mselValue2[".$i."]"]=$v; //servicegroups array *(tutti) o ids']
		}
		if(isset($serviceGroup['members']) && is_array($serviceGroup['members']))
		{
			foreach($serviceGroup['members'] as $i=>$v)
				$params["mselValue1[".$i."]"]=$v; //servicegroups array *(tutti) o ids']
		}
	
		$url = $this->nagiosQLUrl."/admin/servicegroups.php";
		sm_Logger::write("Create servicegroups => Post to servicegroups.php",__CLASS__,__METHOD__);
		$this->Post($url,$params); //,$user,$pwd);
		sm_Logger::write($this->client->getResponseMessage());
			
	}
	
	function update_servicegroups($serviceGroup)
	{
		$params =array(
				"tfValue1"=>$serviceGroup['servicegroup_name'],
				"tfValue2"=>$serviceGroup['alias'],
				"tfValue3"=>"",
				"tfValue4"=>"",
				"tfValue5"=>"",
				"selAccGr"=>0,
				"chbRegister"=>1,
				"chbActive"=>1,
				"hidActive"=>"",
				"modus"=>"modify",
				"hidId"=>$serviceGroup["id"],
				"hidLimit"=>0
					
		);
		if(isset($serviceGroup['servicegroups_members']) && is_array($serviceGroup['servicegroups_members']))
		{
			foreach($serviceGroup['servicegroups_members'] as $i=>$v)
				$params["mselValue2[".$i."]"]=$v; //servicegroups array *(tutti) o ids']
		}
		if(isset($serviceGroup['members']) && is_array($serviceGroup['members']))
		{
			foreach($serviceGroup['members'] as $i=>$v)
				$params["mselValue1[".$i."]"]=$v; //servicegroups array *(tutti) o ids']
		}
		$url = $this->nagiosQLUrl."/admin/servicegroups.php";
		sm_Logger::write("Create servicegroups => Post to servicegroups.php",__CLASS__,__METHOD__);
		$this->Post($url,$params); //,$user,$pwd);
		sm_Logger::write($this->client->getResponseMessage());
			
	}
	
	function delete_servicegroups($serviceGroupsId)
	{
		//chbId_9:on
		$params =array(
				"txtSearch"=>"",
				"modus"=>"checkform",
				"hidModify"=>"delete",
				"hidListId"=>$serviceGroupsId,
				"hidLimit"=> 0,
				"hidSortBy"=> 1,
				"hidSortDir"=> "ASC",
				"hidSort"=> 0,
				"selModify"=> "none",
				"selTargetDomain"=> 1);
		$url = $this->nagiosQLUrl."/admin/servicegroups.php";
		sm_Logger::write("Calling delete  of servicegroups => Post to servicegroups.php");
		$code = $this->Post($url,$params);
		sm_Logger::write($this->client->getResponseMessage());
		sm_Logger::write($this->client->getResponseCode());
		return $code;
	}
	
	function write_servicegroups()
	{
		$params =array(
				"modus"=>"make",
				"hidLimit"=> 0,
				"hidSortBy"=> 1,
				"hidSortDir"=> "ASC",
				"hidSort"=> 0,
				"selModify"=> "none",
				"selTargetDomain"=> 1,
				"hidListId"=>null,
				"txtSearch"=>"");
		$url = $this->nagiosQLUrl."/admin/servicegroups.php";
		sm_Logger::write("Calling write method of servicegroups => Post to servicegroups.php");
		$this->Post($url,$params);
		sm_Logger::write($this->client->getResponseMessage());
		sm_Logger::write($this->client->getResponseCode());
	}
	
	
	/* Client call */
	function Post($url,$body,$user=null,$pwd=null,$contentType=null)
	{
		$this->client->setUrl($url);
		$this->client->setParameters($body);
		$this->client->setMethod("POST");
		$this->client->setCredentials($user,$pwd);
		$this->client->setContentType($contentType);
		$this->client->execute();
		return $this->client->getResponseCode();
	}
	
	function Get($url,$body,$user=null,$pwd=null,$contentType=null)
	{
		$this->client->setUrl($url);
		$this->client->setParameters($body);
		$this->client->setMethod("GET");
		$this->client->setCredentials($user,$pwd);
		$this->client->setContentType($contentType);
		$this->client->execute();
		return $this->client->getResponseCode();
	}
	
	function Login()
	{
		$url =  $this->nagiosQLUrl."/index.php";
        sm_Logger::write(sprintf("Authenticating on Nagiosql at %s with login %s",$url, $this->nagiosQLUser));
        $data=array("tfUsername"=> $this->nagiosQLUser, "tfPassword"=> $this->nagiosQLPwd);
        $this->Post($url,$data);
        sm_Logger::write(sprintf("Authentication status code: %s", $this->client->getResponseCode()));
        if(preg_match("/Login failed/",$this->client->getResponse()))
        {
            //##### FIXME Assert to success. Anything else is fail!
            sm_Logger::write("Authentication failed!!!");
             return false;
        }
        sm_Logger::write('Authentication successful');
        return true;
        
	}
	
	function Logout()
	{
			$url =  $this->nagiosQLUrl."/index.php";
			sm_Logger::write(sprintf("Logging off from Nagiosql at %s with login %s",$url, $this->nagiosQLUser));
			$data=array("logout"=> "yes");
			if($this->Get($url,$data)==200)
			{
				sm_Logger::write('Logout successful');
				return true;
			}
			return false;
				
	}
	
	function verify()
	{
		$this->lastError=array();
		$url =  $this->nagiosQLUrl."/admin/verify.php";
		sm_Logger::write("Verifying Nagios Configuration");
		$data=array("butValue3"=>"Do it");
		$this->Post($url,$data);
		if(!preg_match("/Written configuration files are valid. Nagios can be restarted!/",$this->client->getResponse()))
		{		
			$this->lastError = $this->parseErrors($this->client->getResponse());
			foreach($this->lastError as $msg)
				sm_Logger::write($msg);
			sm_Logger::write("Verification failed!!!");
			return false;
		}
		sm_Logger::write('Verification success');
		return true;
	}
	
	function apply()
	{
		$url =  $this->nagiosQLUrl."/admin/verify.php";
		sm_Logger::write("Apply Configuration to Nagios ");
		$data=array("butValue4"=>"Do it");
		$this->Post($url,$data);
		if(!preg_match("/Restart command successfully sent to Nagios/",$this->client->getResponse()))
		{
			//##### FIXME Assert to success. Anything else is fail!
			sm_Logger::write("Apply Configuration to Nagios failed!!!");
			return false;
		}
		sm_Logger::write('Apply Configuration to Nagios success');
		return true;
	}
	
	
	static function install($db)
	{
		sm_Config::set('NAGIOSQLURL',array('value'=>NAGIOSQLURL,"description"=>'NagiosQL Web Url'));
		sm_Config::set('NAGIOSQLUSER',array('value'=>NAGIOSQLUSER,"description"=>'NagiosQL Web Admin User'));
		sm_Config::set('NAGIOSQLPWD',array('value'=>NAGIOSQLPWD,"description"=>'NagiosQL Web Admin Password'));
	}
	
	static function uninstall($db)
	{
		sm_Config::delete('NAGIOSQLURL');
		sm_Config::delete('NAGIOSQLUSER');
		sm_Config::delete('NAGIOSQLPWD');
	}

	protected function parseErrors($response){
		$list=array();
		$doc = new DOMDocument();
		$doc->loadHTML($response);
		$xpath = new DOMXPath($doc);
		$errors = $xpath->query("//*[@class='errormessage']");
		for ($i = 0; $i < $errors->length; ++$i) {
			
			$list[] = $errors->item($i)->nodeValue;
			//echo($nodeName." :".$nodeValue."<br><br>");
		}
		return $list;
	}
	
	function getLastErrors()
	{
		return $this->lastError;
	}
}
