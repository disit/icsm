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

define("RESTCALLDBTABLE","restCalls");

class SM_RestServerRegistry implements sm_Module
{
	protected $db;
	
	function __construct()
	{
		$this->db=sm_Database::getInstance();
	}
	
	function save($request)
	{
		$this->db->save(RESTCALLDBTABLE,$request);
	}
	
	public function getAllCount($where=null)
	{
		$where=$this->db->buildWhereClause(RESTCALLDBTABLE,$where);
		$r=$this->db->query("SELECT COUNT(*) as count from `".RESTCALLDBTABLE."`".$where);
		return $r[0]['count'];
	}
	
	public function getAll($limit,$where=null)
	{
		$where=$this->db->buildWhereClause(RESTCALLDBTABLE,$where);
		$query="SELECT *, from_unixtime(`arrival_time`) as time from `".RESTCALLDBTABLE."` ".$where." order by time DESC ".$limit;
		$r=$this->db->query($query);
		return $r;
	}
	
	function delete($id) {
		$sql = "DELETE FROM ".RESTCALLDBTABLE." WHERE id = {$id} ";
		return $this->db->query($sql);
	}
	
	static public function install($db)
	{
		$sql="CREATE TABLE IF NOT EXISTS `".RESTCALLDBTABLE."` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`sender_ip` varchar(128) NOT NULL,
		`sender_user` varchar(128) NOT NULL,
		`method` varchar(10) NOT NULL,
		`request` varchar(128) NOT NULL,
		`content_type` varchar(255) NOT NULL,
		`agent` text NOT NULL,
		`data` text NOT NULL,
		`arrival_time` int NOT NULL,
		PRIMARY KEY  (`id`)
		)
		ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".RESTCALLDBTABLE." table");
			return true;
		}
		sm_Logger::write("Not Installed ".RESTCALLDBTABLE." table");
		return false;
	}
	
	static public function uninstall($db)
	{
		$sql="DROP TABLE `".RESTCALLDBTABLE."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
}
