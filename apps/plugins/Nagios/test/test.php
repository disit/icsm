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

include '../../../../system/functions.inc.php';
include '../../../../system/sm_Module.class.php';
include '../../../../system/sm_Database.class.php';
include '../../../../system/sm_Template.class.php';
include '../../../../system/sm_Plugin.class.php';
include '../../../SM_Configurator.class.php';
include '../includes/SM_NagiosConfigurator.class.php';
include '../SM_NagiosPlugin.php';
include '../../../../system/ui/sm_UIElement.class.php';
include '../../../../system/ui/sm_HTML.class.php';


chdir('../../../..');
$db = new sm_Database();
$services=$db->selectRow("SM_Tax",array("name"=>"service"),array("id"));
$parent=$services['id'];
$services=$db->select("SM_Tax",array("parent"=>$parent),array("name"));
foreach($services as $s)
	$data[]=array("name"=>$s['name'],"hostgroups"=>$s['name'],"alias"=>$s['name'],"check_command"=>"check-host-alive");
//echo SM_NagiosConfigurator::createNagiosCfgFile("host_template", $data);

echo SM_NagiosConfigurator::createNagiosCfgFile("hostgroup", $data);
