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
* Class Name:       modConfiguration
* File Name:        modConfiguration.class.php
* Generated:        Wednesday, Oct 9, 2013 - 15:57:49 CEST
*  - for Table:     configuration
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modConfiguration"
class modConfiguration extends modObject
{
	// Variable declaration
	protected $cid; // Primary Key
	protected $identifier;
	protected $description;
	protected $name;
	protected $contacts;
	protected $type;
	protected $bid;
	// Class Constructor
	public function __construct() {
		$this->cid = ""; // Primary Key
		$this->identifier ="";
		$this->description = "";
		$this->name = "";
		$this->contacts ="";
		$this->type = "";
		$this->bid = "";
		parent::__construct();
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getcid() {
		return($this->cid);
	}
	
	public function getbid() {
		return($this->bid);
	}
	
	public function getidentifier() {
		return($this->identifier);
	}
	
	public function getdescription() {
		return($this->description);
	}
	
	public function getname() {
		return($this->name);
	}
	
	public function getcontacts() {
		return($this->contacts);
	}
	
	public function gettype() {
		return($this->type);
	}
	
	// SET Functions
	public function setcid($mValue) {
		$this->cid = $mValue;
	}
	
	public function setbid($mValue) {
		$this->bid = $mValue;
	}
	
	public function setidentifier($mValue) {
		$this->identifier = $mValue;
	}
	
	public function setdescription($mValue) {
		$this->description = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function setcontacts($mValue) {
		$this->contacts = $mValue;
	}
	
	public function settype($mValue) {
		$this->type = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		if(is_numeric($mID))
			$sSQL = "SELECT * FROM configuration WHERE cid = '$mID';";
		else
			$sSQL = "SELECT * FROM configuration WHERE cid = '$mID' OR description = '$mID';";
		$oResult = $this->database->query($sSQL);
		$oRow=null;
		if ($oResult) {
			$oRow =(object)$oResult[0];
		}
		else {
			$err=mysqli_error($this->database->getLink());
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
		// Assign results to class.
		$this->cid = $oRow->cid; // Primary Key
		$this->identifier = $oRow->identifier;
		$this->description = $oRow->description;
		$this->name = $oRow->name;
		$this->contacts = $oRow->contacts;
		$this->type = $oRow->type;
		$this->bid = $oRow->bid;
		return true;
	}
	
	public function insert() {
		$this->cid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO configuration (`identifier`, `description`, `name`, `contacts`,`type`,`bid`) VALUES ('$this->identifier', '$this->description', '$this->name', '$this->contacts', '$this->type', '$this->bid');";
		$oResult = $this->database->query($sSQL);
		$this->cid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE configuration SET cid = '$this->cid', `identifier` = '$this->identifier', `description` = '$this->description', `name` = '$this->name', `contacts` = '$this->contacts' , `type` = '$this->type', `bid` = '$this->bid' WHERE cid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM configuration WHERE cid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modConfiguration"
?>