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

define("SMNagiosConfigDaemonVersion","1.0");

class SM_NagiosConfiguratorDaemon extends SM_NagiosConfigurator
{
	function execute()
	{
		$options=array(-1=>"Ready",0=>"Waiting",1=>"Monitoring",2=>"Stopped",3=>"Failed");
		$req=$this->loadRequest();
		if(isset($req['mid']))
		{
			$mid = $req['mid'];
			$cid= $req['cid']; //$this->getMonitor()->getInternalId($mid);
			//$monitor_data=$this->getMonitor()->getData(array('iid'=>$cid));
			sm_Logger::write('Running...'.$mid." (".$cid.")");
			$status=$req['status'];
			sm_Logger::write('Current status...'.$options[$status]);
			if($status==0 || $status==3)
			{
				sm_Logger::write('Configuring...'.$cid);
				$result=$this->configure($cid);
				foreach($result as $k=>$msg)
				{
					if(is_array($msg))
					{
						foreach($msg as $i=>$s)
						{
							if($k=="error")
								sm_Logger::error($s);
							else
								sm_Logger::write($s);
						}
					}
				}
			}
		
		}
		else
			sm_Logger::write('Running...Nothing to do');		
	}
	
	static function version(){
		return SMNagiosConfigDaemonVersion;
	}
	
	static function isAlive()
	{
		$status = sm_Config::get("SMNAGIOSCONFIGDAEMONWATCHDOG",0);
		if($status)
			sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array('value'=>0));
		return $status;
	}
	
	static function install($db)
	{
		sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array("value"=>0,"description"=>'Watch Dog Variable for Nagios Configurator Daemon Check Alive'),"SM_Monitor");
		sm_Config::set('SMNAGIOSCONFIGSLEEP',array('value'=>5,"description"=>'Nagios Configurator Daemon Sleep Interval (secs)'),"SM_Monitor");
		sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>0,"description"=>'Nagios Configurator Daemon Run Command (1: Run, 0:Pause)'),"SM_Monitor");
		sm_Config::set("SMNAGIOSCONFIGDAEMONSHUTDOWN",array("value"=>0,"description"=>'Nagios Configurator Daemon Shutdown Command (1: Exit, 0:Run)'),"SM_Monitor");
	}
	
	static function uninstall($db)
	{
		sm_Config::delete("SMNAGIOSCONFIGDAEMONWATCHDOG");
		sm_Config::delete("SMNAGIOSCONFIGSLEEP");
		sm_Config::delete("SMNAGIOSCONFIGRUN");
		sm_Config::delete("SMNAGIOSCONFIGDAEMONSHUTDOWN");
	}
	
	static function command($command,$value)
	{
		switch($command){
			case "alive":
				sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array("value"=>0),"SM_Monitor");
				return sm_Config::get("SMNAGIOSCONFIGDAEMONWATCHDOG",0)==0?"Command check alive sent successfully":"Error on check alive command";
				break;
			case "run":
				sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>1),"SM_Monitor");
				return sm_Config::get("SMNAGIOSCONFIGRUN",0)?"Command run sent successfully":"Error on run command";
				break;
			case "pause":
				sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>0),"SM_Monitor");
				return sm_Config::get("SMNAGIOSCONFIGRUN",1)==0?"Command pause sent successfully":"Error on pause command";
				break;
			case "sleep":
					if($value)
						sm_Config::set("SMNAGIOSCONFIGSLEEP",array("value"=>$value),"SM_Monitor");
					return sm_Config::get("SMNAGIOSCONFIGSLEEP",5);
				break;
			case "shutdown":
				sm_Config::set("SMNAGIOSCONFIGDAEMONSHUTDOWN",array("value"=>1),"SM_Monitor");
				return sm_Config::get("SMNAGIOSCONFIGDAEMONSHUTDOWN",0)?"Command shutdown sent successfully":"Error on shutdown command";
				break;
			case "start":
				sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array("value"=>0),"SM_Monitor");
				sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>1),"SM_Monitor");
				sm_Config::set("SMNAGIOSCONFIGDAEMONSHUTDOWN",array("value"=>0),"SM_Monitor");
				$lock="/run/lock/subsys/SM_NagiosConfigurator";
				$output=array();
				if(file_exists($lock))
				{
					$cmd="sudo rm ".$lock;
					exec($cmd,$output);
				}
				$cmd="sudo /etc/init.d/sm_dmn start";
				//$output=array();
				exec($cmd,$output);
				return implode(" ",$output);
				break;
			case "queue":
				return;
			
			default:
				return false;
				break;
		}
	}

	static function queue()
	{
		$query = "SELECT identifier, mid, status, lastupdate  as time, errors FROM configuration JOIN monitors on cid=iid where status in (-1,0,3,4) and lastupdate >0 order by lastupdate ASC";
		
		$req = sm_Database::getInstance()->query($query);
		//  $req=$this->configuratorQueue->getAll("LIMIT 0,1","status='Active' AND method in ('PUT','POST')");
		if(!is_array($req) || count($req)==0)
		{
			return null;
		}
		
		return $req;
	}
	
	static function getSettings()
	{
		$query = "SELECT name, value, description FROM settings where name in ('SMNAGIOSCONFIGSLEEP','SMNAGIOSCONFIGURATORROLLBACK')  ";
	
		$req = sm_Database::getInstance()->query($query);
		//  $req=$this->configuratorQueue->getAll("LIMIT 0,1","status='Active' AND method in ('PUT','POST')");
		if(!is_array($req) || count($req)==0)
		{
			return null;
		}
		
		return array("sleep"=>$req[0],"rollback"=>$req[1]);
	}
	
	static function saveSettings($data)
	{
		$r=true;
		if(isset($data['sleep']))
				$r&=sm_Database::getInstance()->save("settings",array("value"=>$data['sleep']),array('name'=>'SMNAGIOSCONFIGSLEEP'));
		if(isset($data['rollback']))
				$r&=sm_Database::getInstance()->save("settings",array("value"=>$data['rollback']),array('name'=>'SMNAGIOSCONFIGURATORROLLBACK'));
		return $r;
	}
	
	static function getPerformance(){
		$data['cpu']=0.0;
		$data['mem']=0.0;
		$cmd=" ps axo stat,comm,pid,%cpu,%mem | grep 'S' | grep daemon.php";
		$output=array();
		exec($cmd,$output);
		if(isset($output[0]))
		{
			$s=preg_replace('/\s+/', ';',$output[0]);
			$s=explode(";",$s);
			$data['cpu']=$s[3];
			$data['mem']=$s[4];
		}
		return $data;
		
	}
}