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


//include "test.php";

if(isset($_GET['group'])  || isset($_GET['name']) )
{
	$nagiosDir="/usr/local/nagios/etc/objects";
	//$nagiosDir='E:\\Program Files (x86)\\ICW\\etc\\nagios\\nagwin';
	
	$ip=isset($_GET['ip'])?$_GET['ip']:null;
	$winos=isset($_GET['winos'])?$_GET['winos']:"Win-XP";
	$name=isset($_GET['name'])?$_GET['name']:null;
	
	$confFile=null;
	$template="";
	if(isset($_GET['group']) && isset($_GET['alias']))
	{
		$group=$_GET['group']!=""?$_GET['group']:null;
		$alias=$_GET['alias']!=""?$_GET['alias']:"";
		if($group)
		{
			$t=fopen("grouptemplate.cfg","rt");
		
			$groupData=array(
		
				'$GROUPNAME'=>$group,
				'$ALIAS'=>$alias,
		
			);
		
			$cfg=fread($t,filesize("grouptemplate.cfg"));
			fclose($t);
			$cfg=str_replace(array_keys($groupData),$groupData,$cfg);	
			$template=$cfg;
			$confFile=$group;
			$f=fopen($nagiosDir.'/'.$confFile.'.cfg','wt');
			fwrite($f,$template);
			fclose($f);
		}
		
	}
	$template="";
	if(isset($name) && isset($ip))
	{
		$t=fopen("wintemplate.cfg","rt");
		$groupHost[]="windows-servers";
		
		if(isset($_GET['hostgroup']))
			$groupHost[]=$_GET['hostgroup'];
		$winData=array(
		
			'$HOSTNAME'=>$name,
			'$WINOS'=>$winos!=""?$winos:"Win-XP",
			'$IPADDRESS'=>$ip,
			'$HOSTGROUP'=>implode(",",$groupHost)	
		);
		$cfg=fread($t,filesize("wintemplate.cfg"));
		fclose($t);
		$cfg=str_replace(array_keys($winData),$winData,$cfg);
		$template=$cfg;
		
			$confFile=$name;
			$f=fopen($nagiosDir.'/'.$confFile.'.cfg','wt');
			fwrite($f,$template);
			fclose($f);
	}
	
	//exec("E:\\Program Files (x86)\\ICW\\bin\\CheckNagiosConfig.cmd > tmp.txt");
	echo "done";
}
else
	echo "Error";


?>
