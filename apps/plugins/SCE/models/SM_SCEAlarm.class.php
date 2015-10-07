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
* Class Name:       SM_SCEAlarm
* File Name:        SM_SCEAlarm.class.php
* Generated:        Thursday, Jan 29, 2015 - 16:36:34 CET
*  - for Table:     sce_sla_alarms
*  - in Database:   icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "SM_SCEAlarm"
class SM_SCEAlarm extends modObject
{
	// Variable declaration
	protected $id; // Primary Key
	protected $cid;
	protected $sla;
	protected $type;
	protected $details;
	protected $time;
	protected $violations;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->type="alarm";
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getid() {
		return($this->id);
	}
	
	public function getcid() {
		return($this->cid);
	}
	
	public function getsla() {
		return($this->sla);
	}
	
	public function gettype() {
		return($this->type);
	}
	
	public function getdetails() {
		return($this->details);
	}
	
	public function gettime() {
		return($this->time);
	}
	
	public function getviolations() {
		return($this->violations);
	}
	
	// SET Functions
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function setcid($mValue) {
		$this->cid = $mValue;
	}
	
	public function setsla($mValue) {
		$this->sla = $mValue;
	}
	
	public function settype($mValue) {
		$this->type = $mValue;
	}
	
	public function setdetails($mValue) {
		$this->details = $mValue;
	}
	
	public function settime($mValue) {
		$this->time = $mValue;
	}
	
	public function setviolations($mValue) {
		$this->violations = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM sce_sla_alarms WHERE id = $mID;";
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
		$this->id = $oRow->id; // Primary Key
		$this->cid = $oRow->cid;
		$this->sla = $oRow->sla;
		$this->type = $oRow->type;
		$this->details = $oRow->details;
		$this->time = $oRow->time;
		$this->violations =  $oRow->violations;
		return true;
	}
	
	public function insert() {
		$this->id = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO sce_sla_alarms (`cid`, `sla`, `type`, `details`, `time`, `violations`) VALUES ('$this->cid', '$this->sla', '$this->type', '$this->details', '$this->time','$this->violations');";
		$oResult = $this->database->query($sSQL);
		$this->id = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE sce_sla_alarms SET (id = '$this->id', `cid` = '$this->cid', `sla` = '$this->sla', `type` = '$this->type', `details` = '$this->details', `time` = '$this->time', `violations` = '$this->violations') WHERE id = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM sce_sla_alarms WHERE id = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	

}
// End Class "SM_SCEAlarm"
?>