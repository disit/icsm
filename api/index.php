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

include "../system/functions.inc.php";
include '../apps/SM_InitApiServer.php';

function sm_rest_server_autoloader($class) {
	include "../system/config.inc.php";
	$paths = array_merge($classPath,$libPath);
	foreach($paths as $path=>$p){
		if(file_exists("../".$p . $class . '.class.php'))
		{
			include "../".$p . $class . '.class.php';
			break;
		}
	}
	$dirs=glob("../".$pluginPath['system']."/*/", GLOB_BRACE | GLOB_ONLYDIR | GLOB_MARK );
	if(is_array($dirs))
	{
		foreach($dirs as $d=>$path)
		{
			if(file_exists($path.$class.".class.php"))
			{
				include $path.$class.".class.php";
				return;
			}
			else if(file_exists($path . $class . '.php'))
			{
				include $path . $class . '.php';
				return;
			}
	
		}
	}
	
	if(file_exists("controllers/". $class . '.class.php'))
	{
		include "controllers/". $class . '.class.php';
		//break;
	}

}

$mode = 'debug'; // 'debug' or 'production'
if($mode=='debug')
{
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
}

spl_autoload_register('sm_rest_server_autoloader'); // don't load our classes unless we use them
set_error_handler(array("sm_Logger","logErrorHandler"));
$server = new SM_RestServer($mode);
$server->refreshCache(); // uncomment momentarily to clear the cache if classes change in production mode
$server->addControllerClass('SM_RestServerController');
$server->addControllerClass('SM_NagiosController');
$server->addControllerClass('SM_ConfiguratorController');
$server->addControllerClass('SM_GraphController');
$server->addControllerClass('SM_MonitorController');
$server->addControllerClass('SM_NotificationChController');
$server->addControllerClass('SM_HLMController');
$server->addControllerClass('SM_SCEController');
$server->handle();
//$server->exportMap();


