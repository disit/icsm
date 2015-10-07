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

$_GET['group']="TEST GROUP";
$_GET['ip']="192.168.0.37";
$_GET['os']="windows";
$_GET['host_name']="Ivan@192.168.0.37";
$_GET['alias']="Alias IvanBruno";
$_GET['templates']="generic-host,windows-server";
$_GET['hostgroups']="EbosX,windows-servers";

include '../system/functions.inc.php';
include '../apps/SM_InitApiServer.php';

function test_autoloader($class) {
	include "../system/config.inc.php";
	$paths = array_merge($classPath,$libPath);
	foreach($paths as $path=>$p){
		if(file_exists("../".$p . $class . '.class.php'))
		{
			include "../".$p . $class . '.class.php';
			break;
		}
	}

	if(file_exists("controllers/". $class . '.class.php'))
	{
		include "controllers/". $class . '.class.php';
		//break;
	}

}

spl_autoload_register('test_autoloader');

sm_Logger::removeLog();
sm_Logger::removeErrLog();
set_error_handler(array("sm_Logger","logErrorHandler"));
$request=$_GET;
if($_GET['test']=="insert")
{
	$filename="schema/sharepoint1.xml";
	$confData = file_get_contents($filename);

	$conf=new SM_Configurator();
	$conf->insert($confData,"Business");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	return;
}


if($_GET['test']=="XSLT")
{
	$filename="schema/sharepoint1.xml";
	$confData = file_get_contents($filename);
	
	$conf=new SM_Configurator();
	sm_Logger::write("Converting Data into Configuration data model");
	$configuration = $conf->xslt_transform($confData,"Business");
	echo($configuration);
	sm_Logger::write("Validating XML Configuration vs Schema");
	if(!$conf->xml_validate($configuration))
	{
		sm_Logger::error("Error when validating xml!");
		echo ("Error when validating xml: Malformed XML"); exit();
	}
	sm_Logger::write("XML Configuration vs Schema Validated");
	sm_Logger::write("Building Configuration data model");
	
	$_conf = new SM_Configuration();
	$_conf->parse($configuration);
		
	sm_Logger::write("Validating Configuration data model");
	if($_conf->validate())
	{
		sm_Logger::write("Configuration data model validated");
	}
	else
	{
		sm_Logger::error("Invalid or Malformed Configuration data model");
		echo("Invalid or Malformed Configuration data model"); exit();
	}
	
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='Service')
{	$ql = new SM_NagiosQL();
	$ql->login();
	for($i=0;$i<1;$i++)
	{
		$service=array();
		$service['host_name']="ebos6-192.168.0.55";
		$service['config_name']="Test_Service-".$i;
		$service['display_name']="Test_Service-".$i;
		$service['service_description']="Test_Service-".$i;
		if($i%2==0)
		{
			$service['use_template']="Check-Process";
			//$service['check_command']['commandId']=72;
			$service['check_command']['args']="n=httpd";
			$conf = new SM_NagiosConfigurator();
			$service['check_command']=$conf->createNagiosCmdData($service);
		}
		else 
		{
			$service['use_template']="generic-service";
			$service['check_command']['commandId']=19;
		}
		
		sm_Logger::write("Start insert service with ". $service['config_name'],null,"TEST Service");
		if($ql->service($service))
			sm_Logger::write("Service insert request CHECK PASSED",null,"TEST Service");
	}
	if($ql->verify())
		sm_Logger::write("NagiosCheck Test CHECK PASSED",null,"TEST Service");
	else
		sm_Logger::write("NagiosCheck Test CHECK FAILED",null,"TEST Service");
	$ql->logout();
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='NagiosRestart')
{
	$nagios=new SM_NagiosClient();

	$r= $nagios->restart();
	if($r)
		sm_Logger::write("NagiosRestart  Test CHECK PASSED",null,"TEST");
	else
		sm_Logger::write("NagiosRestart  Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}


if($_GET['test']=='NagiosQL')
{
	$ql = new SM_NagiosQL();
	$request=isset($_GET)?$_GET:null;
	$r=$ql->login();
	
	$r&=$ql->logout();
	if($r)
		sm_Logger::write("NagiosQL LoginTest CHECK PASSED",null,"TEST");
	else
		sm_Logger::write("NagiosQL Login Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='NagiosCheck')
{
	$ql = new SM_NagiosQL();
	$request=isset($_GET)?$_GET:null;
	$r=$ql->login();
	$r&=$ql->verify();
	$r&=$ql->apply();
	$r&=$ql->logout();
	if($r)
		sm_Logger::write("NagiosCheck Test CHECK PASSED",null,"TEST");
	else 
		sm_Logger::write("NagiosCheck Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='HostGroup')
{
	$ql = new SM_NagiosQL();
	$ql->login();
	$hg=$ql->getHostGroup("Prova");
	if($hg)
	{
		$ql->remove_hostGroup($hg);
	}
	else 
		$ql->hostGroup(array("hostgroup_name"=>"Prova","alias"=>"Prova"));
	$ql->logout();
	$hg=$ql->getHostGroup("Prova");
	if($hg)
		sm_Logger::write("HostGroup Test CHECK PASSED",null,"TEST");
	else
		sm_Logger::write("HostGroup Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='ServiceGroup')
{
	$ql = new SM_NagiosQL();
	$ql->login();
	$sg=$ql->getServiceGroup($request['name']);
	$ql->logout();
	var_dump($sg);
	if($sg)
		sm_Logger::write("ServiceGroup Test CHECK PASSED",null,"TEST");
	else
		sm_Logger::write("ServiceGroup Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='HostGroupServices')
{
	$ql = new SM_NagiosQL();
	$ql->login();
	$sg=$ql->getHostGroupServices($request['hostgroup_name']);
	$ql->logout();
	
	if($sg)
	{
		echo "<pre>";
		foreach ($sg as $service)
		{
			$s=$ql->getServiceById($service['idMaster']);
			
			echo($s['service_description']);
			echo($s['active']?" (Active) ":"");
			echo "<br>";
		}
		echo "</pre>";
		sm_Logger::write("HostGroupServices Test CHECK PASSED",null,"TEST");
	}
	else
		sm_Logger::write("HostGroupServices Test CHECK FAILED",null,"TEST");
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='CreateHostGroupServices')
{
	$ql = new SM_NagiosQL();
	$ql->login();
	$sg['members']=$ql->createServiceGroupMembers($request['hostgroup'],$request['hostname']);
	

	if($sg)
	{
		echo "<pre>";
		foreach ($sg['members'] as $member)
		{		
			echo($member), "<br>";
		}
		echo "</pre>";
		$sg['servicegroup_name']="testSG";
		$sg['alias']="testSG";
		$ql->serviceGroup($sg);
		sm_Logger::write("CreateHostGroupServices Test CHECK PASSED",null,"TEST");
	}
	else
		sm_Logger::write("CreateHostGroupServices Test CHECK FAILED",null,"TEST");
	$ql->logout();
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
	exit();
}

if($_GET['test']=='Host')
{
	$request=$_GET;
	$ql = new SM_NagiosQL();
	$ql->login();
	
	$host=$ql->getHostByName($request['host_name'], $request['ip']);
	var_dump($host);
	exit();
/*
sm_Logger::write("Check Host Data with ". $_GET['name']." ". $_GET['ip']);
$ql->check_data($_GET['name'], $_GET['ip']);
sm_Logger::write("Get Host with ". $_GET['name']." ". $_GET['ip']);
$ql->getHostByName($_GET['name'], $_GET['ip']);
sm_Logger::write("getHostTemplateId with ". $_GET['name']." ". $_GET['ip']);
$ql->getHostTemplateId($_GET['template']);
sm_Logger::write("Check Host Data with ". $_GET['name']." ". $_GET['ip']);
$ql->check_data($_GET['name'], $_GET['ip']);
sm_Logger::write("Get Host with ". $_GET['name']." ". $_GET['ip']);
$ql->getHostByName($_GET['name'], $_GET['ip']);
sm_Logger::write("getHostTemplateId with ". $_GET['name']." ". $_GET['ip']);
$ql->getHostTemplateId($_GET['template']);
*/


	if($host)
	{
		$ql->remove_host($host);
		exit();
	}
	
	sm_Logger::write("Start insert host with ". $request['host_name']." ". $request['address'],null,"TEST");
	if(!$host && $request && $ql->host($request))
		sm_Logger::write("hostname request CHECK PASSED",null,"TEST");
	$ql->logout();
	echo "<pre>";
	echo file_get_contents("./logs/SM_output.log");
	echo "<br>**************************************************************************************</br>";
	echo file_get_contents("./logs/SM_error.log");
	echo "</pre>";
}
//echo nl2br(file_get_contents("log.txt"));
?>