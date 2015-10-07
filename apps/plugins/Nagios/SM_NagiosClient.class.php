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

define('NAGIOS_SSH_HOST','localhost');
define('NAGIOS_SSH_USER','user');
define('NAGIOS_SSH_PWD','password');

define('NAGIOSCOREURL','http://localhost/nagios');
define('NAGIOSCOREUSER','nagiosadmin');
define('NAGIOSCOREPWD','password');
define('NAGIOSCOREVERSION','4.0.2');


class SM_NagiosClient implements sm_Module
{
	private $restClient=null;
	private $url;
	private $pwd;
	private $user;
	
	
	function __construct()
	{
		$this->restClient=new SM_RestClient();
		$this->url=sm_Config::get('NAGIOSCOREURL',NAGIOSCOREURL);
		$this->user=sm_Config::get('NAGIOSCOREUSER',NAGIOSCOREUSER);
		$this->pwd=sm_Config::get('NAGIOSCOREPWD',NAGIOSCOREPWD);
	}
	
	function restart()
	{
		if($this->_restart())
			return true;
		sm_Logger::write("Nagios Restart Request submission");
		$this->restClient->setUrl($this->url."/cgi-bin/cmd.cgi");
		$this->restClient->setCredentials($this->user,$this->pwd);
		$this->restClient->setParameters(array("cmd_typ"=>13));
		$this->restClient->setMethod("GET");
		$this->restClient->setContentType("text/html");
		$this->restClient->execute();
		$this->restClient->setParameters("cmd_typ=13&cmd_mod=2&btnSubmit=Commit");
		$this->restClient->setMethod("POST");
		$this->restClient->setContentType("application/x-www-form-urlencoded");
		$this->restClient->execute();
		if(preg_match("/Your command request was successfully submitted to Nagios for processing./",$this->restClient->getResponse(),$m))
		{
			sm_Logger::write("Nagios Restart Request: ".$m[0]);
			return $m[0];
		}
		sm_Logger::write("Nagios Restart Request: failed");
		return null;
	}
	
	function reload(){
		$host = sm_Config::get('NAGIOS_SSH_HOST',NAGIOS_SSH_HOST);
		$user = sm_Config::get('NAGIOS_SSH_USER',NAGIOS_SSH_USER);
		$pwd = sm_Config::get('NAGIOS_SSH_PWD',NAGIOS_SSH_PWD);
		sm_Logger::write("Nagios Reload Request submission via ssh");
		if (!function_exists("ssh2_connect")) {
			sm_Logger::error("function ssh2_connect doesn't exist");
			return $this->restart();
		}
		// log in at server1.example.com on port 22
		if(!($con = ssh2_connect($host, 22))){
			sm_Logger::error( "fail: unable to establish connection");
			return null;
		} else {
			// try to authenticate with username root, password secretpassword
			if(!ssh2_auth_password($con, $user, $pwd)) {
				sm_Logger::error("fail: unable to authenticate");
				return null;
			} else {
				// allright, we're in!
				sm_Logger::write( "okay: logged in...");
		
				// execute a command
				if (!($stream = ssh2_exec($con, "/etc/rc.d/init.d/nagios reload" ))) {
					sm_Logger::error("fail: unable to execute command");
					return null;
				} else {
					// collect returning data from command
					stream_set_blocking($stream, true);
					$data = "";
					while ($buf = fread($stream,4096)) {
						$data .= $buf;
					}
					fclose($stream);
				}
			}
		}
		sm_Logger::write($data);
		return true;
	}
	
	function _restart(){
		$host = sm_Config::get('NAGIOS_SSH_HOST',NAGIOS_SSH_HOST);
		$user = sm_Config::get('NAGIOS_SSH_USER',NAGIOS_SSH_USER);
		$pwd = sm_Config::get('NAGIOS_SSH_PWD',NAGIOS_SSH_PWD);
		sm_Logger::write("Nagios Restart Request submission via ssh");
		if (!function_exists("ssh2_connect")) {
			sm_Logger::error("function ssh2_connect doesn't exist");
			return null;
		}
		// log in at server1.example.com on port 22
		if(!($con = ssh2_connect($host, 22))){
			sm_Logger::error( "fail: unable to establish connection");
			return null;
		} else {
			// try to authenticate with username root, password secretpassword
			if(!ssh2_auth_password($con, $user, $pwd)) {
				sm_Logger::error("fail: unable to authenticate");
				return null;
			} else {
				// allright, we're in!
				sm_Logger::write( "okay: logged in...");
	
				// execute a command
				if (!($stream = ssh2_exec($con, "service nagios restart" ))) {
					sm_Logger::error("fail: unable to execute command");
					return null;
				} else {
					// collect returning data from command
					stream_set_blocking($stream, true);
					$data = "";
					while ($buf = fread($stream,4096)) {
						$data .= $buf;
					}
					fclose($stream);
				}
			}
		}
		sm_Logger::write($data);
		return true;
	}
	
	static function daemonAlive() //nagios dead but subsys locked
	{
		$host = sm_Config::get('NAGIOS_SSH_HOST',NAGIOS_SSH_HOST);
		$user = sm_Config::get('NAGIOS_SSH_USER',NAGIOS_SSH_USER);
		$pwd = sm_Config::get('NAGIOS_SSH_PWD',NAGIOS_SSH_PWD);
		sm_Logger::write("Nagios Service Status Request submission via ssh");
		if (!function_exists("ssh2_connect")) {
			sm_Logger::error("function ssh2_connect doesn't exist");
			return false;
		}
		// log in at server1.example.com on port 22
		if(!($con = ssh2_connect($host, 22))){
			sm_Logger::error( "fail: unable to establish connection");
			return false;
		} else {
			// try to authenticate with username root, password secretpassword
			if(!ssh2_auth_password($con, $user, $pwd)) {
				sm_Logger::error("fail: unable to authenticate");
				return false;
			} else {
				// allright, we're in!
				sm_Logger::write( "okay: logged in...");
		
				// execute a command
				if (!($stream = ssh2_exec($con, "service nagios status" ))) {
					sm_Logger::error("fail: unable to execute command");
					return false;
				} else {
					// collect returning data from command
					stream_set_blocking($stream, true);
					$data = "";
					while ($buf = fread($stream,4096)) {
						$data .= $buf;
					}
					fclose($stream);
				}
			}
		}
		
		sm_Logger::write($data);
		if(preg_match("/nagios dead/",$data,$m))
		{
			sm_Logger::write("Nagios Daemon is down!!!");
			return false;
		}
		return true;
	}
	function verifyCfg()
	{
		$nql = new SM_NagiosQLClient();
		$nql->Login();
		$res = $nql->verify();
		$nql->Logout();
		if(!$res)
			return "Written configuration files are not valid!";
		return "Written configuration files are valid. Nagios can be restarted!";
	}
	
	function disableCheck($hostname=null,$service=null)
	{
		sm_Logger::write("Nagios Disable Check submission");
		$this->restClient->setUrl($this->url."/cgi-bin/cmd.cgi");
		$this->restClient->setCredentials($this->user,$this->pwd);
		$this->restClient->setParameters(array("cmd_typ"=>6,"host"=>$hostname,"service"=>$service));
		$this->restClient->setMethod("GET");
		$this->restClient->setContentType("text/html");
		$this->restClient->execute();
		$this->restClient->setParameters("cmd_typ=6&cmd_mod=2&btnSubmit=Commit&host=".$hostname."&service=".$service);
		$this->restClient->setMethod("POST");
		$this->restClient->setContentType("application/x-www-form-urlencoded");
		$this->restClient->execute();
		if(preg_match("/Your command request was successfully submitted to Nagios for processing./",$this->restClient->getResponse(),$m))
		{
			sm_Logger::write("Nagios Disable Check Request: ".$m[0]);
			return $m[0];
		}
		sm_Logger::write("Nagios Disable Check Request: failed");
		return null;
	}
	
	function enableCheck($hostname=null,$service=null)
	{
		sm_Logger::write("Nagios Enable Check submission");
		$this->restClient->setUrl($this->url."/cgi-bin/cmd.cgi");
		$this->restClient->setCredentials($this->user,$this->pwd);
		$this->restClient->setParameters(array("cmd_typ"=>5,"host"=>$hostname,"service"=>$service));
		$this->restClient->setMethod("GET");
		$this->restClient->setContentType("text/html");
		$this->restClient->execute();
		$this->restClient->setParameters("cmd_typ=5&cmd_mod=2&btnSubmit=Commit&host=".$hostname."&service=".$service);
		$this->restClient->setMethod("POST");
		$this->restClient->setContentType("application/x-www-form-urlencoded");
		$this->restClient->execute();
		if(preg_match("/Your command request was successfully submitted to Nagios for processing./",$this->restClient->getResponse(),$m))
		{
			sm_Logger::write("Nagios Enable Check Request: ".$m[0]);
			return $m[0];
		}
		sm_Logger::write("Nagios Enable Check Request: failed");
		return null;
	}
	
	function rescheduleCheck($hostname=null,$service=null)
	{
		sm_Logger::write("Nagios Reschedule Check submission");
		$this->restClient->setUrl($this->url."/cgi-bin/cmd.cgi");
		$this->restClient->setCredentials($this->user,$this->pwd);
		$this->restClient->setParameters(array("cmd_typ"=>7,"host"=>$hostname,"service"=>$service,"force_check"=>null));
		$this->restClient->setMethod("GET");
		$this->restClient->setContentType("text/html");
		$this->restClient->execute();
		//time format 01-09-2015 12:08:22
		$t=time();
		$time = date('m-d-Y H:i:s',$t);
		$this->restClient->setParameters("cmd_typ=7&cmd_mod=2&btnSubmit=Commit&force_check=on&host=".$hostname."&service=".$service."&start_time=".$time);
		$this->restClient->setMethod("POST");
		$this->restClient->setContentType("application/x-www-form-urlencoded");
		$this->restClient->execute();
		$time = date('d-m-Y H:i:s',$t);
		if(preg_match("/Your command request was successfully submitted to Nagios for processing./",$this->restClient->getResponse(),$m))
		{
			sm_Logger::write("Nagios Reschedule Check Request: ".$m[0]." (Scheduled @ ".$time.")");
			return true;
		}
		sm_Logger::write("Nagios Reschedule Check Request: failed(".$time.")");
		return false;//$this->parseErrors($this->restClient->getResponse());
	}
	
	static function isAlive($timeout=5){
		$old=sm_Logger::$debug;
		sm_Logger::$debug=false;
		//$h=parse_url(sm_Config::get('NAGIOSCOREURL',NAGIOSCOREURL));
		$url = sm_Config::get('NAGIOSCOREURL',"");
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
		sm_Config::set('NAGIOSCOREURL',array('value'=>NAGIOSCOREURL,"description"=>'NagiosCore Web Url'));
		sm_Config::set('NAGIOSCOREUSER',array('value'=>NAGIOSCOREUSER,"description"=>'NagiosCore Web Admin User'));
		sm_Config::set('NAGIOSCOREPWD',array('value'=>NAGIOSCOREPWD,"description"=>'NagiosCore Web Admin Password'));
		
		sm_Config::set('NAGIOS_SSH_HOST',array('value'=>NAGIOS_SSH_HOST,"description"=>'NagiosCore Host Ip for SSH Connection'));
		sm_Config::set('NAGIOS_SSH_USER',array('value'=>NAGIOS_SSH_USER,"description"=>'NagiosCore User for SSH Connection'));
		sm_Config::set('NAGIOS_SSH_PWD',array('value'=>NAGIOS_SSH_PWD,"description"=>'NagiosCore Password for SSH Connection'));
	}
	
	static function uninstall($db)
	{
		sm_Config::delete('NAGIOSCOREURL');
		sm_Config::delete('NAGIOSCOREUSER');
		sm_Config::delete('NAGIOSCOREPWD');
		
		sm_Config::delete('NAGIOS_SSH_HOST');
		sm_Config::delete('NAGIOS_SSH_USER');
		sm_Config::delete('NAGIOS_SSH_PWD');
	}
	
	static function version(){
		return NAGIOSCOREVERSION;
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
}
