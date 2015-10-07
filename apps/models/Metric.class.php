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
* Class Name:       Metric
* File Name:        Metric.class.php
* Generated:        Friday, Aug 2, 2013 - 10:33:37 CEST
*  - for Table:     metric
*   - in Database:  icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "Metric"
class Metric extends modMetric{
	// Variable declaration
	// Class Constructor
	public function __construct($mid=null) {
		parent::__construct();
		$this->max_check_attempts=5;
		$this->check_interval=5;
		if($mid){
			$this->select($mid);
		}
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	static public function getAll($minfo_id)
	{
		$db=sm_Database::getInstance();
		$sql="SELECT mid FROM metric WHERE minfo_id=$minfo_id;";
		$result=$db->query($sql);
		if($result)
		{
			foreach($result as $r=>$s)
			{
				$obj=new Metric($s['mid']);
				$all[]=$obj;
			}
			return $all;
		}
		return null;
	}
	
	function write($a)
	{
		foreach(get_object_vars($a) as $prop => $value)
		{
			$name = $prop;
			if($name[0]=="@")
			{
				//$name=substr($name,1);
				$this->write($value);
			}
			else
				$this->$name =$value;
		}
	}
	
	public function build()
	{
		$result=sm_obj2array($this);
		
		$result['@attributes']=array(
				"minfo_id"=>$result['minfo_id'],
				"mid"=>$result['mid']
				
		);
		unset($result['mid']);
		unset($result['minfo_id']);
		unset($result['database']);
		return $result;
	}

}
// End Class "Metric"
?>