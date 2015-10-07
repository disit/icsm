#!/usr/bin/php
<?php
$path="../../..";


include $path."/lib/xml2Array.class.php";
include $path."/lib/array2xml.class.php";
include $path."/lib/XSLT/XSLT_Processor.class.php";
include $path."/system/sm_Database.class.php";
include $path."/system/sm_Module.class.php";
include $path."/system/sm_Logger.class.php";
include $path."/system/sm_Config.class.php";
include $path."/system/functions.inc.php";




function load($class)
{
	if(file_exists("../models/".$class.".class.php"))
		include "../models/".$class.".class.php";
	else if(file_exists("../".$class.".class.php"))
		include "../".$class.".class.php";
	else if(file_exists("../controllers/".$class.".class.php"))
		include "../controllers/".$class.".class.php";
	else if(file_exists("../includes/".$class.".class.php"))
		include "../includes/".$class.".class.php";
	else if(file_exists("../plugins/Nagios/includes/".$class.".class.php"))
		include "../plugins/Nagios/includes/".$class.".class.php";
}

//error_reporting(E_ALL);
//ini_set("display_errors", "on");
spl_autoload_register("load");

$log = '/var/log/Daemon.log';

/**
 * Method for displaying the help and default variables.
 */
function displayUsage() {
	global $log;
	
	echo "n";
	echo "Process for demonstrating a PHP daemon.n";
	echo "n";
	echo "Usage:n";
	echo "tDaemon.php [options]n";
	echo "n";
	echo "toptions:n";
	echo "tt--help display this help messagen";
	echo "tt--log=<filename> The location of the log file (default '$log')n";
	echo "n";
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
		} // end switch
	} // end foreach
} // end if
  
// fork the process to work in a daemonized environment
file_put_contents ( $log, "Status: starting up.n", FILE_APPEND );
$pid = pcntl_fork ();
if ($pid == - 1) {
	file_put_contents ( $log, "Error: could not daemonize process.n", FILE_APPEND );
	return 1; // error
} else if ($pid) {
	return 0; // success
} else {
	// the main process
	sm_Logger::$usedb=false;
	sm_Logger::$logfolder=substr($log, 0,strlen(basename($log)));
	sm_Logger::$fileLog= basename($log);
	sm_Logger::removeLog();
	while ( true ) {
		
		
		//file_put_contents ( $log, 'Running...', FILE_APPEND );
		sm_Logger::write('Running...');
		sleep ( 5 );
	} // end while
} // end if

?>
