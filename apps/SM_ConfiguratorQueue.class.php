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

define('CONFIGURATIONQUEUEDBTABLE',"configurator_queue");

class SM_ConfiguratorQueue implements sm_Module
{
	protected $db;
	function __construct()
	{
		$this->db=sm_Database::getInstance();
	}
	
	function push($mid,$data)
	{
		$request=array(
			'mid'=>$mid,
			'sender_ip'=>$_SERVER['REMOTE_ADDR'],
			'sender_user'=>isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:"",
			'method'=>$_SERVER['REQUEST_METHOD'],
			'request'=>$_SERVER['REQUEST_URI'],
			'arrival_time'=>$_SERVER['REQUEST_TIME'],
			'agent'=>$_SERVER['HTTP_USER_AGENT'],
			'end_point'=>"",
			'status'=>'Pending',
			'data'=>json_encode($data),
			'content_type'=>"application/json",
			'close_time'=>0
		);
		$result= $this->db->save(CONFIGURATIONQUEUEDBTABLE,$request);
		if($result)
			return $this->db->getLastInsertedId();
		return null;
		
	}
	
	function update($where=array(),$data)
	{
		if(count($data)>0)
		{
			return $this->db->save(CONFIGURATIONQUEUEDBTABLE,$data,$where);
		}	
	}
	
	function delete($mid)
	{
		return $this->db->delete(CONFIGURATIONQUEUEDBTABLE,array("mid"=>$mid));
	}
	
	function getInfo($field,$where=array())
	{
		$fields = null;
		if($field!='all' && $field!='*')
			$fields = array($field);
		$r=$this->db->select(CONFIGURATIONQUEUEDBTABLE,$where,$fields);
		if($r && isset($r[0]))
		{
			if($r[0]['data']!="")
			{
				$r[0]['data']=json_decode($r[0]['data']);
				
			}
			return $r[0];
		}
		return null;
	}
	
	function setInfo($fields,$id)
	{
		
		if(isset($fields) && isset($id))
		{
			
			$r=$this->db->save(CONFIGURATIONQUEUEDBTABLE,$fields,array('id'=>$id));
			if($r && isset($r[0]))
				return $r[0];
		}
		return null;
	}
	
	
	static public function install($db)
	{
		$sql="CREATE TABLE IF NOT EXISTS `".CONFIGURATIONQUEUEDBTABLE."` (
		`mid` varchar(128) NOT NULL,
		`sender_ip` varchar(128) NOT NULL,
		`sender_user` varchar(128) NOT NULL,
		`method` varchar(10) NOT NULL,
		`request` varchar(128) NOT NULL,
		`content_type` varchar(255) NOT NULL,
		`agent` text NOT NULL,		
		`data` LONGTEXT NOT NULL,
		`status` varchar(128) NOT NULL,
		`end_point` varchar(255) NOT NULL,
		`arrival_time` int NOT NULL,
		`close_time` int NOT NULL,
		`id` int(11) NOT NULL AUTO_INCREMENT,
  		PRIMARY KEY (`id`),
  		KEY `mid` (`mid`)
		)
		ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".CONFIGURATIONQUEUEDBTABLE." table");
			return true;
		}
		sm_Logger::write("Not Installed ".CONFIGURATIONQUEUEDBTABLE." table");
		return false;	
	}
	
	static public function uninstall($db)
	{
		$sql="DROP TABLE `".CONFIGURATIONQUEUEDBTABLE."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
	
	public function getAllCount($whereCond=NULL)
	{
		$where="";
		if(isset($whereCond))
			$where=$this->db->buildWhereClause(CONFIGURATIONQUEUEDBTABLE,$whereCond);
		$r=$this->db->query("SELECT COUNT(*) as count from `".CONFIGURATIONQUEUEDBTABLE."` ".$where);
		return $r[0]['count'];
	}
	
	public function getAll($limit="",$whereCond=NULL)
	{
		$where="";
		if(isset($whereCond))
		{
			$where=$this->db->buildWhereClause(CONFIGURATIONQUEUEDBTABLE,$whereCond);
			//sm_Logger::write("SELECT from_unixtime(`arrival_time`) as timestamp, sender_ip, sender_user, mid, method, status, data, from_unixtime(`close_time`) as end from `".CONFIGURATIONQUEUEDBTABLE."` ".$where."  order by timestamp DESC ".$limit);
		}
		$r=$this->db->query("SELECT from_unixtime(`arrival_time`) as timestamp, sender_ip, sender_user, mid, method, status, data, from_unixtime(`close_time`) as end from `".CONFIGURATIONQUEUEDBTABLE."` ".$where."  order by timestamp DESC ".$limit);
		return $r;
		
	}
	
	
}
