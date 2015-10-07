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

define("SCESLAALARMSTABLE","sce_sla_alarms");
define('SCEWRITEINDB',1);

class SM_SCE implements sm_Module
{
protected $db;
	
	function __construct()
	{
		$this->db=sm_Database::getInstance();
	}
	
	function save($request)
	{
		if(sm_Config::get('SCEWRITEINDB',SCEWRITEINDB)==0)
			return;
		if(is_a($request,"SM_SCEAlarm"))
			$alarm=$request;
		else
		{ 
			$alarm = new SM_SCEAlarm();
			$alarm->setcid($request['business_configuration']);
			$alarm->setsla($request['sla']);
			$alarm->settime($request['timestamp']);
			$xml = Array2XML::createXML("metrics",array("metrics"=>$request['metric']));
			$xpath = new DOMXpath($xml);
			$alarm->setviolations($xpath->evaluate("/metrics/metrics")->length);
			$alarm->setdetails($xml->saveXML());
		}
		
		$alarm->insert();
	}
	
	function deleteConfigurationData($cid)
	{
		$this->db->delete(SCESLAALARMSTABLE,array("cid"=>$cid));
	}
	
	public function getAllCount($where=null)
	{
		$where=$this->db->buildWhereClause(SCESLAALARMSTABLE,$where);
		$r=$this->db->query("SELECT COUNT(*) as count from `".SCESLAALARMSTABLE."`".$where);
		return $r[0]['count'];
	}
	
	public function getAll($limit,$where=null)
	{
		$where=$this->db->buildWhereClause(SCESLAALARMSTABLE,$where);
		$query="SELECT id from `".SCESLAALARMSTABLE."` ".$where." order by time DESC ".$limit;
		$r=$this->db->query($query);
		$alarms=array();
		foreach($r as $id)
		{
			$alarm = new SM_SCEAlarm();
			$alarm->select($id['id']);
			$alarms[]=$alarm;
		}
		return $alarms;
	}
	
	
	static public function install($db)
	{
		sm_Config::set('SCEWRITEINDB',array('value'=>SCEWRITEINDB,"description"=>'Enable/Disable SCE Plugin to register alarms in the database'));
		$sql="CREATE TABLE IF NOT EXISTS `".SCESLAALARMSTABLE."` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `cid` varchar(128) NOT NULL,
		  `sla` varchar(128) NOT NULL,
		  `type` varchar(45) NOT NULL,
		  `details` LONGTEXT NOT NULL,
		  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `violations` int(10) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  KEY `cid` (`cid`)
		) ENGINE=MyISAM CHARSET=utf8;";
		
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".SCESLAALARMSTABLE." table");
				
		}
		return true;
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete('SCEWRITEINDB');
		$sql="DROP TABLE  IF EXISTS `".SCESLAALARMSTABLE."`;";
		
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
}