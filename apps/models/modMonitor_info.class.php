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
* Class Name:       modMonitor_info
* File Name:        modMonitor_info.class.php
* Generated:        Wednesday, Oct 9, 2013 - 16:01:11 CEST
*  - for Table:     monitor_info
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modMonitor_info"
class modMonitor_info extends modObject
{
	// Variable declaration
	protected $minfo_id; // Primary Key
	protected $ref;
	protected $type;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->ref=-1;
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	public function getref() {
		return($this->ref);
	}
	
	public function gettype() {
		return($this->type);
	}
	
	// SET Functions
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function setref($mValue) {
		$this->ref = $mValue;
	}
	
	public function settype($mValue) {
		$this->type = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM monitor_info WHERE minfo_id = $mID;";
		$oResult = $this->database->query($sSQL);
		$oRow=null;
		if ($oResult) {
			$oRow =(object)$oResult[0];
		}
		else {
			$err=$this->database->getError();
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
		// Assign results to class.
		$this->minfo_id = $oRow->minfo_id; // Primary Key
		$this->ref = $oRow->ref;
		$this->type = $oRow->type;
		return true;
	}
	
	public function insert() {
		$this->minfo_id = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO monitor_info (`ref`, `type`) VALUES ('$this->ref', '$this->type');";
		$oResult = $this->database->query($sSQL);
		$this->minfo_id = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE monitor_info SET minfo_id = '$this->minfo_id', `ref` = '$this->ref', `type` = '$this->type' WHERE minfo_id = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM monitor_info WHERE minfo_id = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modMonitor_info"
?>