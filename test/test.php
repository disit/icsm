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

include '../system/functions.inc.php';
include '../system/config.inc.php';
include '../apps/IcaroSM/init.php';
echo "<pre>",print_r (sm_get_all_module("",array("../system","../apps")),true)."</pre>";
exit();

	
	
function load_($paths="", $class="", $folders="/")
{
	$dir=glob("{".$paths."}{".$folders."}{".$class."}.class.php", GLOB_BRACE);
	if(is_array($dir))
	{
		foreach($dir as $d=>$path)
		{
			echo $path."<br>";
			/*if (is_dir($path)) $result=glob($path.'/{*'.$class."}.class.php",GLOB_BRACE))
			{
				echo "<pre>",print_r($result,true),"</pre>";
				//return true;
			}
			else
			load_($path,$class);*/
			//	return true;
		}
	}
//	return false;
}
//echo dirname(__FILE__);
echo SM_IcaroApp::getFolder("templates");

exit();
$where="../apps/*,../apps,../system,../lib,../include";
/*load_($where,"*Controller","/controllers/");
load_($where,"*View","/views/");
load_($where,"*Observer","/observers/");*/

load_($where,"modHost");
	
	
	/*	if ($result=glob($path.'{*'.$class."}.class.php",GLOB_BRACE))
	 {
	echo "<pre>",print_r($result,true),"</pre>";
	}
		
	if(file_exists($path.$class.".class.php"))
	{
	echo $path.$class.".class.php";
	break;
	}	*/