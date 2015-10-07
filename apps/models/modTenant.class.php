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
* Class Name:       modTenant
* File Name:        modTenant.class.php
* Generated:        Wednesday, Oct 9, 2013 - 16:01:39 CEST
*  - for Table:     tenant
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modTenant"
class modTenant extends modObject
{
	// Variable declaration
	protected $tid; // Primary Key
	protected $name;
	protected $id;
	protected $description;
	protected $contacts;
	protected $runOn;
	protected $minfo_id;
	protected $cid;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->minfo_id=-1;
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function gettid() {
		return($this->tid);
	}
	
	public function getname() {
		return($this->name);
	}
	
	public function getid() {
		return($this->id);
	}
	
	public function getdescription() {
		return($this->description);
	}
	
	public function getcontacts() {
		return($this->contacts);
	}
	
	public function getrunOn() {
		return($this->runOn);
	}
	
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	public function getcid() {
		return($this->cid);
	}
	
	// SET Functions
	public function settid($mValue) {
		$this->tid = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function setdescription($mValue) {
		$this->description = $mValue;
	}
	
	public function setcontacts($mValue) {
		$this->contacts = $mValue;
	}
	
	public function setrunOn($mValue) {
		$this->runOn = $mValue;
	}
	
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function setcid($mValue) {
		$this->cid = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM tenant WHERE tid = $mID;";
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
		$this->tid = $oRow->tid; // Primary Key
		$this->name = $oRow->name;
		$this->id = $oRow->id;
		$this->description = $oRow->description;
		$this->contacts = $oRow->contacts;
		$this->runOn = $oRow->runOn;
		$this->minfo_id = $oRow->minfo_id;
		$this->cid = $oRow->cid;
		return true;
	}
	
	public function insert() {
		$this->tid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO tenant (`name`, `id`, `description`, `contacts`, `runOn`, `minfo_id`, `cid`) VALUES ('$this->name', '$this->id', '$this->description', '$this->contacts', '$this->runOn', '$this->minfo_id', '$this->cid');";
		$oResult = $this->database->query($sSQL);
		$this->tid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE tenant SET tid = '$this->tid', `name` = '$this->name', `id` = '$this->id', `description` = '$this->description', `contacts` = '$this->contacts', `runOn` = '$this->runOn', `minfo_id` = '$this->minfo_id', `cid` = '$this->cid' WHERE tid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM tenant WHERE tid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modTenant"
?>