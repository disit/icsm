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

DEFINE("DASHBOARDTABLENAME","dashboard");

class sm_DashboardManager implements sm_Module
{
	public static $instance;


	public function __construct()
	{

	}

	static public function install($db)
	{
		$sql="CREATE TABLE  IF NOT EXISTS `".DASHBOARDTABLENAME."` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`ref_id` varchar(255) NOT NULL,
		`module` varchar(128) NOT NULL,
		`method` varchar(128) NOT NULL,
		`callback_args` text DEFAULT NOT NULL,
		`title` text NOT NULL,
		`group` text NOT NULL,
		`segment` text NOT NULL,
		`weight` int(10) NOT NULL DEFAULT '-1',
		`enable` int(1) NOT NULL DEFAULT '1',
		`view_name` varchar(128) NOT NULL DEFAULT '\"\"',
		PRIMARY KEY (`id`),
		KEY `ref_id` (`ref_id`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".DASHBOARDTABLENAME." table");
			return true;
		}
		sm_Logger::write("Error when installing ".DASHBOARDTABLENAME." table");
		return false;

	}

	static public function uninstall($db)
	{
		$sql="DROP TABLE IF EXISTS `".DASHBOARDTABLENAME."` where ref_id=-1;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}

	static public function getInstance()
	{
		if(!self::$instance)
			self::$instance=new sm_DashboardManager();
		return self::$instance;
	}

	public function getBoards($data)
	{
		if( !is_array($data) )
			return null;
		$db=sm_Database::getInstance();
		$where=array();
		$where['enable']=1;
		if(isset($data['enable']))
		{
			$where['enable']=$data['enable'];
		}
		if(isset($data['view_name']))
			$where['view_name']=$data['view_name'];
		if(isset($data['ref_id']))
			$where['ref_id']=$data['ref_id'];
		if(isset($data['module']))
		{
			$where['module']=$data['module'];
				
			if(isset($data['field']) && isset($data['value']))
			{
				$where[$data['field']]=$data['value'];
				//	$sql="SELECT id FROM ".DASHBOARDTABLENAME." WHERE module='".$data['module']."' and ".$data['field']."='".$data['value']."' order by weight asc";
			}
			/*	else
				$sql="SELECT id FROM ".DASHBOARDTABLENAME." WHERE module='".$data['module']."' order by weight asc";*/
				
			//$sql.="SELECT id FROM ".DASHBOARDTABLENAME."  WHERE view_name='".$data['view_name']."' order by weight asc";
		}

		/*	else
			$sql="SELECT id FROM ".DASHBOARDTABLENAME." order by weightasc";*/
		//$table, $where = null, $fields = null, $limit = '', $orderby = null, $direction = 'ASC'
		$result=$db->select(DASHBOARDTABLENAME,$where,array("id"),'',array("weight"));
		$all=array();
		if($result)
		{
			foreach($result as $r=>$s)
			{
				$obj=new sm_Board($s['id']);
				$all[]=$obj;
			}
			return $all;
		}
		return null;
	}
	
	public function getDashboard($data)
	{
		$d = new sm_Dashboard();
		$d->load($data);
		return $d;
	}

	public function add(sm_Board $board)
	{

		//if(isset($data['segment']) && !$board->exists(array("field"=>"segment","value"=>$data['segment'])))
		if(is_object($board))
		{
			$board->insert();
		}
	}

	public function update(sm_Board $board)
	{

		if(is_object($board))
			$board->update($board->getid());

	}

	public function remove(sm_Board $board)
	{
		if(is_object($board))
			$board->delete($board->getid());
	}

	public function delete($where=array())
	{
		if(!empty($where))
		{
			$db=sm_Database::getInstance();
			$db->delete(DASHBOARDTABLENAME,$where);
		}
		
	}

	public function enable($val)
	{


	}
	
	public function getAllCount($where=null)
	{
		$where=$this->db->buildWhereClause(DASHBOARDTABLENAME,$where);
		$query = "SELECT COUNT(*) as count from `".DASHBOARDTABLENAME."` ".$where;
	
		$r=$this->db->query($query);
		return $r[0]['count'];
	}
	
	public function getAll($limit,$where=null)
	{
		$where=$this->db->buildWhereClause(DASHBOARDTABLENAME,$where);
		$query="SELECT * from `".DASHBOARDTABLENAME."` ".$where." ".$limit;
	
		$r=$this->db->query($query);
		return $r;
	}
}
