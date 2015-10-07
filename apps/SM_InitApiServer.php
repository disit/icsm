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

function icaro_autoloader($class)
{
	$appFolder = dirname(__FILE__);
	if(file_exists($appFolder."/". $class . '.class.php'))
	{
		include $appFolder."/" . $class . '.class.php';
		return;
	}
	else if(file_exists($appFolder."/". $class . '.php'))
	{
		include $appFolder."/" . $class . '.php';
		return;
	}
	$dirs=glob($appFolder."/{*/,plugins/*/*/,plugins/*/}", GLOB_BRACE | GLOB_ONLYDIR | GLOB_MARK );
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
}
spl_autoload_register('icaro_autoloader'); // don't load our classes unless we use them