#!/usr/bin/php
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

define("DEFAULT_SLEEP",5);
define("RUNNING",1);
define("PAUSED",0);
define("SHUTDOWN",1);

$path="../../..";
include $path."/lib/XML2Array.class.php";
include $path."/lib/Array2XML.class.php";
include $path."/lib/XSLT/XSLT_Processor.class.php";
include $path."/system/sm_Database.class.php";
include $path."/system/sm_Module.class.php";
include $path."/system/sm_Logger.class.php";
include $path."/system/sm_Plugin.class.php";
include $path."/system/sm_Config.class.php";
include $path."/system/functions.inc.php";
include $path."/system/config.inc.php";

function load($class)
{
	global $path;
	if(file_exists($path."/system/".$class.".class.php"))
		include $path."/sytem/".$class.".class.php";
	if(file_exists("./models/".$class.".class.php"))
		include "./models/".$class.".class.php";
	else if(file_exists("./".$class.".class.php"))
		include "./".$class.".class.php";
	else if(file_exists("./controllers/".$class.".class.php"))
		include "./controllers/".$class.".class.php";
	else if(file_exists("./includes/".$class.".class.php"))
		include "./includes/".$class.".class.php";
	else if(file_exists($path."/apps/plugins/Nagios/includes/".$class.".class.php"))
		include $path."/apps/plugins/Nagios/includes/".$class.".class.php";
	else if(file_exists($path."/apps/plugins/Nagios/".$class.".class.php"))
		include $path."/apps/plugins/Nagios/".$class.".class.php";
	else if(file_exists($path."/apps/plugins/Nagios/".$class.".php"))
		include $path."/apps/plugins/Nagios/".$class.".php";
	else if(file_exists($path."/apps/".$class.".class.php"))
		include $path."/apps/".$class.".class.php";
	else if(file_exists($path."/apps/models/".$class.".class.php"))
		include $path."/apps/models/".$class.".class.php";
}

error_reporting(E_ALL);
ini_set("display_errors", "on");
spl_autoload_register("load");

set_error_handler(array("sm_Logger","logErrorHandler"));

$log = '/var/log/SM_daemon.log';
$lock = null;

/**
 * Method for displaying the help and default variables.
 */
function displayUsage() {
	global $log;
	
	echo "\n";
	echo "Process for demonstrating a PHP daemon.\n";
	echo "\n";
	echo "Usage:\n";
	echo "\tDaemon.php [options]\n";
	echo "\n";
	echo "\toptions:\n";
	echo "\t\t--help display this help message\n";
	echo "\t\t--log=<filename> The location of the log file (default '$log')\n";
	echo "\n";
} // end displayUsage()

// configure command line arguments
if ($argc > 0) {
	foreach ( $argv as $arg ) {
		$args = explode ( '=', $arg );
		switch ($args [0]) {
			case '--help' :
				return displayUsage ();
			case '--log' :
				$log = $args [1];
				break;
			case '--lock' :
					$lock = $args [1];
					break;
			case '--path' :
					$path = $args [1];
				break;
		} // end switch
	} // end foreach
} // end if





/*function writeDB($table,$name,$value)
{
	$db = new sm_Database();
	$db->save($table,array("value"=>$value),array('name'=>$name));
	unset($db);
}*/

function readDB($table,$name,$default)
{
	$db = new sm_Database();
	$r=$db->selectRow($table,array('name'=>$name),array("value"));
	unset($db);
	if(isset($r['value']))
		return $r['value'];
	return $default;
}

// fork the process to work in a daemonized environment
$pid = pcntl_fork ();
if ($pid == - 1) {
	echo('\nError: could not daemonize process.');
	return 1; // error
} else if ($pid) {
	//return 0; // success
	exit(0);
} else {
	// the main process
		
		$sleep = sm_Config::get("SMNAGIOSCONFIGSLEEP",DEFAULT_SLEEP);
		sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>RUNNING));
		sm_Config::set("SMNAGIOSCONFIGDAEMONSHUTDOWN",array("value"=>0));
		sm_Logger::$usedb=false;
		sm_Logger::$logfolder=substr($log, 0,-strlen(basename($log)));
		sm_Logger::$fileLog= basename($log);
		sm_Logger::removeLog();
		sm_Logger::write('Status: starting up');
		
		$msg="Daemon Started at ".date("d/m/y H:i:s",time());
		sm_Logger::write($msg);
		echo "\n".$msg; 
		$msg="Daemon Configuration: sleep = ".$sleep." sec\n";
		echo "\n".$msg;
		sm_Logger::write($msg);
		$rollback=sm_Config::get("SMNAGIOSCONFIGURATORROLLBACK",0);
		$rollStatus=$rollback?"On":"Off";
		$msg="Daemon Configuration: rollback = ".$rollStatus."\n";
		echo "\n".$msg;
		sm_Logger::write($msg);
		/*$msg="Nagios Configurator Ver. ".$nagios->version();
		echo "\n".$msg;
		sm_Logger::write($msg);*/
		$pause=false;
		$nextRun=time()+$sleep;
	while (true) {
		if(readDB("settings","SMNAGIOSCONFIGDAEMONWATCHDOG",0)==0)
		{
			sm_Logger::write('WatchDog: I\'m alive!');
			sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array("value"=>1));
		}
		$tmp = readDB("settings","SMNAGIOSCONFIGURATORROLLBACK",0);
		if($rollback !=$tmp)
		{
			$rollback =$tmp;
			$rollback==0?sm_Logger::write('Status rollback: On'):sm_Logger::write('Status rollback: Off');
		}			
		
		if(readDB("settings","SMNAGIOSCONFIGRUN",RUNNING))
		{
			if($pause)
			{
				$pause=false;
				sm_Logger::write('Status: restarted at '.date("d/m/y H:i:s",time()));
			}
			$ret=true;
			$nagiosRestart = false;
			if(!SM_NagiosClient::isAlive())
			{
				sm_Logger::write('Nagios: server does not respond or timeout exceeded!');
				$ret=false;
			}
			else 
				sm_Logger::write('Nagios: server is on!');
			if(!SM_NagiosClient::daemonAlive())
			{
				sm_Logger::write('Nagios: daemon does not respond or dead!');
				$ret=false;
				$nagiosRestart =true;
			}
			else
				sm_Logger::write('Nagios: daemon is on!');
			if(!SM_NagiosQL::isAlive())
			{
				sm_Logger::write('Nagios QL: server does not respond or timeout exceeded!');
				$ret=false;
			}
			else
				sm_Logger::write('Nagios QL: server is on!');
			if($ret)
			{
					if($nextRun<=time())
					{
						$nagios = new SM_NagiosConfiguratorDaemon();
						$nagios->setRollback($rollback);
						$nagios->execute();
						$nextRun=time()+$sleep;
						sm_Logger::write("Status: next run = ".date("d/m/y H:i:s",$nextRun));
					}
					
			}
			else
			{
				sm_Logger::write('Skipping installation inside Nagios!');
				if($nagiosRestart)
				{
					$nagios  = new SM_NagiosClient();
					$nagiosRestart=!$nagios->_restart();
				}	
			}
			
		}
		else {
			if(!$pause)
			{
				$pause=true;
				sm_Logger::write('Status: suspended at '.date("d/m/y H:i:s",time()));
			}
		}
		$newSleep=readDB("settings","SMNAGIOSCONFIGSLEEP",DEFAULT_SLEEP);
		if($sleep != $newSleep)
		{
			$nextRun=$nextRun-$sleep + $newSleep;
			$sleep = $newSleep;
			sm_Logger::write("Status: sleep = ".$sleep." sec\n");
			sm_Logger::write("Status: new next run = ".date("d/m/y H:i:s",$nextRun));
		}
		sleep ( 5 );
		if(readDB("settings","SMNAGIOSCONFIGDAEMONSHUTDOWN",0))
			 break;
	} // end while
	sm_Logger::write('Status: shutdown');
	if($lock)
	{
		sm_Logger::write('Remove lock: '.$lock);
		if(unlink($lock))
			sm_Logger::write('Lock file removed successfully!');
		else 
			sm_Logger::write('ERROR: Lock file removal is failed!');

	}
	sm_Config::set("SMNAGIOSCONFIGDAEMONWATCHDOG",array("value"=>0));
	sm_Config::set("SMNAGIOSCONFIGRUN",array("value"=>0));
	$msg="Daemon Stopped at ".date("d/m/y H:i:s",time());
	echo "\n".$msg;
	sm_Logger::write($msg);
	sleep (5);
	sm_Logger::removeLog();
} // end if
?>
