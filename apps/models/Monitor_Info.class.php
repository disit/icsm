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

/*******************************************************************************
* Class Name:       Monitor_Info
* File Name:        Monitor_Info.class.php
* Generated:        Friday, Aug 2, 2013 - 11:46:09 CEST
*  - for Table:     Monitor_Info
*  - in Database:  icaro
* Created by:
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "Monitor_Info"
class Monitor_Info extends modMonitor_info{
	// Variable declaration
	protected $metrics;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->metrics=array();
	}

	// Class Destructor
	public function __destruct() {
		parent::__destruct();
		if($this->metrics)
		{
			foreach($this->metrics as $i=>$metric)
			{
				$metric->__destruct();
			}
		}
		
	}

	static public function getAll($where=array())
	{
		
		if( !is_array($where) )
			return null;
		$db=sm_Database::getInstance();
		$whereCond="";
		if(!empty($where))
			$whereCond=$db->buildWhereClause("monitor_info", $where);
		

		$sql="SELECT minfo_id FROM monitor_info ".$whereCond;
		$result=$db->query($sql);
		if($result)
		{
			$s=$result[0];
			$obj=new Monitor_Info();
			$obj->select($s['minfo_id']);
			$metrics=Metric::getAll($s['minfo_id']);
			if($metrics)
				$obj->metrics=$metrics;
			return $obj;
		}
		return null;
	}

	function write($a)
	{
		if(empty($a))
			return;
		foreach(get_object_vars($a) as $prop => $value)
		{
			$name = $prop;
			if($name[0]=="@")
			{
				$name=substr($name,1);
				if($name=="attributes" && is_object($value) && isset($value->type))
					$this->settype($value->type);
			}
			else if($name=="metrics")
			{
				$this->metrics=array();
				if(isset($value->metric))
				{
					$metrics=array();
					if(!is_array($value->metric))
						$metrics[]=$value->metric;
					else
						$metrics=$value->metric;
					foreach($metrics as $i=>$s)
					{
						$metric = new Metric();
						$metric->write($s);
						$this->metrics[]= $metric;
					}	
				}
			}
			else
				$this->$name =$value;
		}
	}
	
	public function insert(){
		parent::insert();
		foreach($this->metrics as $i=>$d)
		{
			$d->setminfo_id($this->minfo_id);
			$d->insert();
		}
	}
	public function delete($ref){
	
		
	/*	foreach($this->metrics as $i=>$d)
		{
			SM_Logger::write("Deleting Metric");
			$d->delete($d->getmid());
		}*/
		SM_Logger::write("Deleting Monitor Info");
		parent::delete($ref);
	}
	
	public function build()
	{
		$result=sm_obj2array($this);
		unset($result['metrics']);
		if(count($this->metrics)>0)
		{
				foreach($this->metrics as $i=>$metric)
				{
					$result['metrics']['metric'][]=$metric->build();
				}
		}
		else 
			$result['metrics']="";
		$result['@attributes']=array(
		"minfo_id"=>$result['minfo_id'],
		"ref"=>$result['ref'],
		"type"=>$result['type']);
		unset($result['ref']);
		unset($result['type']);
		unset($result['minfo_id']);
		unset($result['database']);
		return $result;
	}
	

}
// End Class "Monitor_Info"
?>