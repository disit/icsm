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

class sm_Dashboard implements sm_Module
{
	protected $title;
	protected $boards;
	protected $refreshUrl;
	protected $id;
	public function __construct($id=null)
	{
		$this->title="";
		$this->refreshUrl="";
		$this->boards=array();
		$this->id=$id;
	}
	
	
	
	public function load($data)
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
			$this->boards=$all;
		}
	}
	
	public function add(sm_Board $board)
	{
		
		//if(isset($data['segment']) && !$board->exists(array("field"=>"segment","value"=>$data['segment'])))
		if(is_object($board))
		{
			$this->boards[]=$board;
		}
	}
	
	public function remove(sm_Board $board)
	{
		if(is_object($board))
			$board->delete($board->getid());
	}
	
	public function getBoards()
	{
		return $this->boards;
	} 
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getRefreshUrl()
	{
		return $this->refreshUrl;
	}
	
	public function setRefreshUrl($refreshUrl)
	{
		$this->refreshUrl = $refreshUrl;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	static public function install($db){}
	
	
	static public function uninstall($db){}
}
