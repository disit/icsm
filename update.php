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

include 'system/functions.inc.php';
include 'system/config.inc.php';
error_reporting(E_ALL);
ini_set('display_errors', 'On');

spl_autoload_register('sm_autoloader');
set_error_handler(array("sm_Logger","logErrorHandler"));
sm_Logger::$debug=true;
sm_Logger::removeLog();



	$install_app=new sm_Installer();
	if(isset($_GET['q']) && $_GET['q']=="plugin")
		$install_app->setMode('update_plugin');
	else
		$install_app->setMode('update');
	if(isset($_GET['module']))
		$install_app->setModule($_GET['module']);
//	$install_app->handle();
	$install_app->execute();
	echo "<pre>".file_get_contents("logs/SM_output.log")."</pre>";