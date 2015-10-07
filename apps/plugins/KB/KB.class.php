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

define('KBURL','http://localhost:8080/IcaroKB');
define('KBAPICHECK','/api/status');
define('KBUSERWRITE','kb-write');
define('KBPWDWRITE','icaro');
define('KBUSERREAD','kb-read');
define('KBPWDREAD','icaro');
define('KBCONFEDITORTOOLURL','');
define('KBAPIGETHLM',"/api/businessConfiguration/%ID/hlmetrics");
define('KBHYPEROSQUERYURL','');
define('KBVMOSQUERYURL','');
define('KBAPPQUERYURL','');
define('KBSERVICEQUERYURL','');


class KB implements sm_Module
{
	private $url;
	private $pwd_w;
	private $user_w;
	private $pwd_r;
	private $user_r;
	
	function __construct()
	{
		
		$this->url=sm_Config::get('KBURL',KBURL);
		$this->user_w=sm_Config::get('KBUSERWRITE',KBUSERWRITE);
		$this->pwd_w=sm_Config::get('KBPWDWRITE',KBPWDWRITE);
		$this->user_r=sm_Config::get('KBUSERREAD',KBUSERREAD);
		$this->pwd_r=sm_Config::get('KBPWDREAD',KBPWDREAD);
		
	}
	
	
	static function isAlive($timeout=5){
		$old=sm_Logger::$debug;
		sm_Logger::$debug=false;
		$h=parse_url(sm_Config::get('KBURL',KBURL));
		$port=isset($h['port'])?$h['port']:80;
		$fp=@fsockopen($h['host'],$port,$errno,$errstr,$timeout);
		$ret=$fp===false;
		if(is_resource($fp))
			@fclose($fp);
		sm_Logger::$debug=$old;
		return !$ret;
	}
	
	static function getStatus(){
		$url=sm_Config::get('KBURL',KBURL)."/".sm_Config::get('KBAPICHECK',KBAPICHECK);
		$user_r=sm_Config::get('KBUSERREAD',KBUSERREAD); 
		$pwd_r=sm_Config::get('KBPWDREAD',KBPWDREAD);
		$status=SM_RestClient::get($url,null,$user_r,$pwd_r);
		return $status;
	}
	
	static function getMetrics($id){
		$url=sm_Config::get('KBURL',KBURL)."/".sm_Config::get('KBAPIGETHLM',KBAPIGETHLM);
		$url=str_replace("%ID", $id, $url);
		$user_r=sm_Config::get('KBUSERREAD',KBUSERREAD);
		$pwd_r=sm_Config::get('KBPWDREAD',KBPWDREAD);
		$client=SM_RestClient::get($url,null,$user_r,$pwd_r);
		return $client;
	}
	
	static function postMetrics($data){
		$url=sm_Config::get('KBURL',KBURL)."/api/serviceMetric"; 
		$user_r=sm_Config::get('KBUSERWRITE',KBUSERWRITE);
		$pwd_r=sm_Config::get('KBPWDWRITE',KBPWDWRITE);
		$client=SM_RestClient::post($url,$data,$user_r,$pwd_r,"application/xml");
		return $client;
	}
	
	static function install($db)
	{
		sm_Config::set('KBURL',array('value'=>KBURL,"description"=>'Knowledge Base Web Url'));
		sm_Config::set('KBAPICHECK',array('value'=>KBAPICHECK,"description"=>'Knowledge Base API Check Status Url'));
		sm_Config::set('KBAPIGETHLM',array('value'=>KBAPIGETHLM,"description"=>'Knowledge Base API GET HLM'));
		sm_Config::set('KBUSERWRITE',array('value'=>KBUSERWRITE,"description"=>'Knowledge Base User (write)'));
		sm_Config::set('KBPWDWRITE',array('value'=>KBPWDWRITE,"description"=>'Knowledge Base Pwd (write)'));
		sm_Config::set('KBUSERREAD',array('value'=>KBUSERREAD,"description"=>'Knowledge Base User (read)'));
		sm_Config::set('KBPWDREAD',array('value'=>KBPWDREAD,"description"=>'Knowledge Base Pwd (read)'));
		sm_Config::set('KBCONFEDITORTOOLURL',array('value'=>KBCONFEDITORTOOLURL,"description"=>'Knowledge Base Configuration Editor Url'));
		sm_Config::set('KBVMOSQUERYURL',array('value'=>KBVMOSQUERYURL,"description"=>'Knowledge Base Query Url for gettings VMs OS list'));
		sm_Config::set('KBHYPEROSQUERYURL',array('value'=>KBHYPEROSQUERYURL,"description"=>'Knowledge Base Query Url for gettings HyperVisors OS list'));
		sm_Config::set('KBSERVICEQUERYURL',array('value'=>KBSERVICEQUERYURL,"description"=>'Knowledge Base Query Url for gettings Services list'));
		sm_Config::set('KBAPPQUERYURL',array('value'=>KBAPPQUERYURL,"description"=>'Knowledge Base Query Url for gettings Application list'));
		sm_Logger::write("KB Variables Installed Successfully");
		sm_set_message("KB Variables Installed Successfully");		
		
	}
	

	
	static function uninstall($db)
	{
		sm_Config::delete('KBURL');
		sm_Config::delete('KBAPICHECK');
		sm_Config::delete('KBUSERWRITE');
		sm_Config::delete('KBPWDWRITE');
		sm_Config::delete('KBUSERREAD');
		sm_Config::delete('KBPWDREAD');
		sm_Config::delete('KBCONFEDITORTOOLURL');
		sm_Config::delete('KBVMOSQUERYURL');
		sm_Config::delete('KBHYPEROSQUERYURL');
		sm_Config::delete('KBAPPQUERYURL');
		sm_Config::delete('KBSERVICEQUERYURL');
		sm_Config::delete('KBAPIGETHLM');
		sm_Logger::write("KB Variables Uninstalled Successfully");
		sm_set_message("KB Variables Uninstalled Successfully");
	}
	
	function dowloadXml($url)
	{
	
		$opts = array(
				'http'=>array(
						'method'=>"GET",
						'header'=>"Accept: application/sparql-results+xml\r\n"
	    
				)
		);
		$context = stream_context_create($opts);
		$str = file_get_contents($url,null,$context);
		return $str;
	}
}
	