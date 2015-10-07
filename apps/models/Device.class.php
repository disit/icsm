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
* Class Name:       Device
* File Name:        Device.class.php
* Generated:        Friday, Aug 2, 2013 - 10:33:03 CEST
*  - for Table:     device
*   - in Database:  icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "Device"
class Device extends modDevice{
	// Variable declaration
	protected $monitor_info;
	// Class Constructor
	public function __construct($did=null) {
		parent::__construct();
		$this->monitor_info=null;
		if($did)
		{
			$this->select($did);
			//$this->monitor_info=Monitor_Info::getAll(array("field"=>'ref',"value"=>$did));
			$this->monitor_info=Monitor_Info::getAll(array('minfo_id'=>$this->getminfo_id()));
		}
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
		if($this->monitor_info)
		{
			$this->monitor_info->__destruct();
		}
	}
	
	static public function getAll($where) //($data)
	{		
		if( !is_array($where) )
			return null;
		$db=sm_Database::getInstance();
		$whereCond="";
		if(!empty($where))
			$whereCond=$db->buildWhereClause("device", $where);
		$sql="SELECT did FROM device ".$whereCond;
		$result=$db->query($sql);
		if($result)
		{
			foreach($result as $r=>$s)
			{
				$obj=new Device($s['did']);
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
			elseif($name=="monitor_info") // && isset($value->metrics))
			{
				$this->monitor_info=new Monitor_Info();
				$this->monitor_info->write($value);
			}
			else
				$this->$name=is_array($value)?implode(";",$value):$value;
		}
	}
	
	public function insert(){
		if($this->monitor_info)
		{
			$this->monitor_info->insert();
			$this->setminfo_id($this->monitor_info->getminfo_id());
			parent::insert();
			$this->monitor_info->setref($this->getdid());
			$this->monitor_info->update($this->monitor_info->getminfo_id());
		}
		else 
		{
			parent::insert();
		}
	}
	public function delete($ref){

	//	SM_Logger::write("Deleting Device");
	//	parent::delete($ref);
		if($this->monitor_info)
			$this->monitor_info->delete($this->minfo_id);
		
		
	}
	
	public function build()
	{
		$result=sm_obj2array($this);
		$result['@attributes']=array(
				"cid"=>$result['cid'],
				"did"=>$result['did'],
				"minfo_id"=>$result['minfo_id']
				
		);
		unset($result['cid']);
		unset($result['did']);
		unset($result['minfo_id']);
		unset($result['database']);
		$result['ip_address']=explode(";",$result['ip_address']);
		if($this->monitor_info)
		{
			unset($result['monitor_info']);
			$result['monitor_info'] = $this->monitor_info->build();
		}
		
		return $result;
	}
}
// End Class "Device"
?>