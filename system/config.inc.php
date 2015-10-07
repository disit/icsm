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

//The plugin folder
global $pluginPath;
//MySql Host
$dbHost="localhost";
//MySql User
$dbUser="dbuser";
//MySql Pwd
$dbPwd="dbpassword";
//MySql Db Name
$dbName="icaro";
//Base Url
$baseUrl="/SM/";
//Base Dir
$baseDir="/SM/";
//Class folder path for system modules
$classPath['system']="system/";
//Class folder path for apps/addons modules
$classPath['apps']="apps/";
//Class folder path for addons plugins
$pluginPath['system']="plugins/";
$pluginPath['apps']="apps/plugins/";
//Class folder path for libs
$libPath[]="lib/";
//Class folder path for libs/PFCB
$libPath[]="lib/PFBC/";
//Class folder path for libs/XSLT
$libPath[]="lib/XSLT/";
//Class folders structures for classPath
//Class folder path for views
$classPathStructure["views"]="views/";
//Class folder path for controllers
$classPathStructure["controllers"]="controllers/";
//Class folder path for models
$classPathStructure["models"]="models/";
//Class folder path for includes
$classPathStructure["includes"]="includes/";
//Class folder path for observers
$classPathStructure["observers"]="observers/";
//Class folder path for ui element
$classPathStructure["ui"]="ui/";
//Class folder path for root
$classPathStructure["root"]="/";
