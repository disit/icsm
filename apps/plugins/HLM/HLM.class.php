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

define('HLMDBNAME','HLM');
define('HLMDBTABLE','hlm_metrics');
define('HLMDBUser','HLMDBUser');
define('HLMDBPassword','HLMDBPassword');
define('HLMDBUrl','HLMDBUrl');

class HLM implements sm_Module
{
	private $dburl;
	private $dbpwd;
	private $dbuser;
	private $dbName;
	private $dbTable;
	protected $db;

	function __construct()
	{
		$this->dbName = sm_Config::get('HLMDBNAME',HLMDBNAME); 
		$this->dburl=sm_Config::get('HLMDBUrl',HLMDBUrl); //"http://192.168.0.103/nagios/cgi-bin/cmd.cgi";
		$this->dbuser=sm_Config::get('HLMDBUser',HLMDBUser); //"nagiosadmin";
		$this->dbpwd=sm_Config::get('HLMDBPassword',HLMDBPassword);
		$this->dbTable = sm_Config::get('HLMDBTABLE',HLMDBTABLE);
		if(sm_Config::get('HLMDBCREATED',false))
		{
			$this->db=new sm_Database($this->dburl,$this->dbuser,$this->dbpwd);
			$this->db->setDB($this->dbName);
		}
		
		
	}
	function insert($data)
	{
		return $this->db->save($this->dbTable, $data);
	}
	
	
	function writeRDFHLMetric($data)
	{
		$rdf='<?xml version="1.0" encoding="UTF-8" standalone="no"?>
				<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
				xmlns:app="http://www.cloudicaro.it/cloud_ontology/applications#"
				xmlns:icr="http://www.cloudicaro.it/cloud_ontology/core#">';
		foreach($data as $metric)
		{
			$rdf.='<icr:ServiceMetric>
			<icr:atTime rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">'.$data['time'].'</icr:atTime>
			<icr:hasMetricName>'.$data['metric'].'</icr:hasMetricName>
			<icr:hasMetricValue rdf:datatype="http://www.w3.org/2001/XMLSchema#decimal">'.$data['value'].'
			</icr:hasMetricValue>
			<icr:dependsOn rdf:resource="'.$data['dependsOn'].'" />
			</icr:ServiceMetric>';
		}	
		$rdf.='</rdf:RDF>';
		return $rdf;
	}
	
	function deleteHLMetric($id)
	{
		$r=$this->db->delete($this->dbTable,array('id'=>$id));
		return $r;
	}
	
	public function getAllCount($where=array())
	{
		$whereCond="";
		if(!empty($where))
			$whereCond=$this->db->buildWhereClause($this->dbTable, $where);
		$r=$this->db->query("SELECT COUNT(*) as count from `".$this->dbTable."` ".$whereCond);
		return $r[0]['count'];
	}
	
	public function getAll($limit=null, $where=array(),$fields=array())
	{
		
		if(isset($limit))
			$limit=str_replace("LIMIT", "", $limit);
		$r=$this->db->select($this->dbTable, $where,$fields,$limit,array("id"),"DESC");
		return $r;
	
	}

		
	static function getStatus(){
	}
	
	static function install($db)
	{		
		sm_Config::set('HLMDBNAME',array('value'=>HLMDBNAME,"description"=>'HLM Database Schema'));
		sm_Config::set('HLMDBUrl',array('value'=>HLMDBUrl,"description"=>'HLM Database Url'));
		sm_Config::set('HLMDBUser',array('value'=>HLMDBUser,"description"=>'HLM Database User (write)'));
		sm_Config::set('HLMDWRITEINTOKB',array('value'=>false,"description"=>'Write HLM data into KB'));
		sm_Config::set('HLMDBPassword',array('value'=>HLMDBPassword,"description"=>'HLM Database Pwd (write)'));
		sm_Config::set('HLMDBTABLE',array('value'=>HLMDBTABLE,"description"=>'HLM Database Table Name'));
		sm_Config::set('HLMDBCREATED',array('value'=>false,"description"=>'HLM Database Creation Status'));
		
		sm_set_message("HLM Variables inserted successfully!");
		
		/****** ACL Section *******************/
		sm_Logger::write("Installing Permissions: HLM::Edit");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'HLM Edit','permKey'=>'HLM::Edit'));
		sm_Logger::write("Installing Permissions: HLM::View");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'HLM View','permKey'=>'HLM::View'));
		sm_Logger::write("Permissions Installed");
		
	}
	
	static function uninstall($db)
	{
		$result=true;
		if(sm_Config::get('HLMDBCREATED',true) && strcasecmp($db->get_current_db(), sm_Config::get('HLMDBNAME',HLMDBNAME)!=0))
		{
			$dbName = sm_Config::get('HLMDBNAME',HLMDBNAME);
			$dbTable = sm_Config::get('HLMDBTABLE',HLMDBTABLE);
			$dburl=sm_Config::get('HLMDBUrl',HLMDBUrl); //"http://192.168.0.103/nagios/cgi-bin/cmd.cgi";
			$dbuser=sm_Config::get('HLMDBUser',HLMDBUser); //"nagiosadmin";
			$dbpwd=sm_Config::get('HLMDBPassword',HLMDBPassword);
			$hlmdb=new sm_Database($dburl,$dbuser,$dbpwd);
			$sql="DROP TABLE ".$dbName.".".$dbTable;
			$result=$hlmdb->query($sql);
			if($result)
				sm_set_message("HLM DB Table removed successfully!");
			else
				sm_set_error($hlmdb->getError());
			$sql="DROP SCHEMA ".$dbName;
			$result=$hlmdb->query($sql);
			if($result)
				sm_set_message("HLM DB Schema removed successfully!");
			else
				sm_set_error($hlmdb->getError());
		}
		if($result)
		{
			sm_Config::delete('HLMDBNAME');
			sm_Config::delete('HLMDBUrl');
			sm_Config::delete('HLMDBUser');
			sm_Config::delete('HLMDBPassword');
			sm_Config::delete('HLMDBTABLE');
			sm_Config::delete('HLMDBCREATED');
			sm_Config::delete('HLMDWRITEINTOKB');
			sm_set_message("HLM Unistalled successfully!");
		}
		else
			sm_set_error("HLM Unistall Failed");
	}
	
	function createDatabase()
	{
		if(!sm_Config::get('HLMDBCREATED',true))
		{
			$db=new sm_Database($this->dburl,$this->dbuser,$this->dbpwd);
			//create schema
			if(!$db->exist_db($this->dbName))
			{
				sm_Logger::write("HLM: Creating Schema #".$this->dbName);
				sm_set_message("HLM: Creating Schema #".$this->dbName);
				$sql= 'CREATE DATABASE IF NOT EXISTS `'.$this->dbName.'`';
			
				$result=$db->query($sql);	
				if(!$result)
				{
					$error=$db->getError();
					sm_Logger::write($error);
					sm_set_error($error);
					return;
				}
				else
				{
					sm_Logger::write("HLM: Created Schema #".$this->dbName);
					sm_set_message("HLM: Created Schema #".$this->dbName);
					$db->setDB($this->dbName);
				}
			}
			sm_Logger::write("HLM: Creating Table #".$this->dbTable);
			sm_set_message("HLM: Creating Table #".$this->dbTable);
				//create table
			$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->dbTable.'` (
				  `id` int(32) NOT NULL AUTO_INCREMENT,
				  `metric` varchar(256) DEFAULT NULL,
				  `value` double DEFAULT NULL,
				  `unit` varchar(32) DEFAULT NULL,
				  `warning` varchar(32) DEFAULT NULL,
				  `critical` varchar(32) DEFAULT NULL,
				  `max` double DEFAULT NULL,
				  `dependsOn` varchar(128) DEFAULT NULL,
				  `hostname` varchar(255) DEFAULT NULL,
				  `time` datetime DEFAULT NULL,
				  `registration` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
			$result=$db->query($sql);
			if($result)
			{
				sm_Logger::write("HLM: Installed Table #".$this->dbTable);
				sm_set_message("HLM: Installed Table #".$this->dbTable);
				sm_Config::set('HLMDBCREATED',array('value'=>true));
			}
			else
			{
					$error=$db->getError();
					sm_Logger::write($error);
					sm_set_error($error);
			}
		}
	}
	
	function last_events($howmany,$time,$id=null)
	{
		$where="where time >=".$time;
		if($id)
			$where.=" AND dependsOn ='".$id."'"; 
		
		return $this->getAll($howmany,$where,null);
	}
}