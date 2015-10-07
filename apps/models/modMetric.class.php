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
* Class Name:       modMetric
* File Name:        modMetric.class.php
* Generated:        Wednesday, Oct 9, 2013 - 16:00:57 CEST
*  - for Table:     metric
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modMetric"
class modMetric extends modObject
{
	// Variable declaration
	protected $mid; // Primary Key
	protected $name;
	protected $critical_value;
	protected $warning_value;
	protected $args;
	protected $max_check_attempts;
	protected $check_interval;
	protected $status;
	protected $mode;
	protected $minfo_id;
	// Class Constructor
	public function __construct() {
		parent::__construct();	
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getmid() {
		return($this->mid);
	}
	
	public function getname() {
		return($this->name);
	}
	
	public function getcritical_value() {
		return($this->critical_value);
	}
	
	public function getwarning_value() {
		return($this->warning_value);
	}
	
	public function getargs() {
		return($this->args);
	}
	
	public function getmax_check_attempts() {
		return($this->max_check_attempts);
	}
	
	public function getcheck_interval() {
		return($this->check_interval);
	}
	
	public function getstatus() {
		return($this->status);
	}
	
	public function getmode() {
		return($this->mode);
	}
	
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	// SET Functions
	public function setmid($mValue) {
		$this->mid = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function setcritical_value($mValue) {
		$this->critical_value = $mValue;
	}
	
	public function setwarning_value($mValue) {
		$this->warning_value = $mValue;
	}
	
	public function setargs($mValue) {
		$this->args = $mValue;
	}
	
	public function setmax_check_attempts($mValue) {
		$this->max_check_attempts = $mValue;
	}
	
	public function setcheck_interval($mValue) {
		$this->check_interval = $mValue;
	}
	
	public function setstatus($mValue) {
		$this->status = $mValue;
	}
	
	public function setmode($mValue) {
		$this->mode = $mValue;
	}
	
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM metric WHERE mid = $mID;";
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
		$this->mid = $oRow->mid; // Primary Key
		$this->name = $oRow->name;
		$this->critical_value = $oRow->critical_value;
		$this->warning_value = $oRow->warning_value;
		$this->args = $oRow->args;
		$this->max_check_attempts = $oRow->max_check_attempts;
		$this->check_interval = $oRow->check_interval;
		$this->status = $oRow->status;
		$this->mode = $oRow->mode;
		$this->minfo_id = $oRow->minfo_id;
		return true;
	}
	
	public function insert() {
		$this->mid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO metric (`name`, `critical_value`, `warning_value`, `args`, `max_check_attempts`, `check_interval`, `status`, `mode`, `minfo_id`) VALUES ('$this->name', '$this->critical_value', '$this->warning_value', '$this->args', '$this->max_check_attempts', '$this->check_interval', '$this->status', '$this->mode', '$this->minfo_id');";
		$oResult = $this->database->query($sSQL);
		$this->mid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE metric SET mid = '$this->mid', `name` = '$this->name', `critical_value` = '$this->critical_value', `warning_value` = '$this->warning_value', `args` = '$this->args', `max_check_attempts` = '$this->max_check_attempts', `check_interval` = '$this->check_interval', `status` = '$this->status', `mode` = '$this->mode', `minfo_id` = '$this->minfo_id' WHERE mid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM metric WHERE mid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modMetric"
?>