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
* Class Name:       modApplication
* File Name:        modApplication.class.php
* Generated:        Wednesday, Oct 9, 2013 - 15:59:55 CEST
*  - for Table:     application
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modApplication"
class modApplication extends modObject
{
	// Variable declaration
	protected $aid; // Primary Key
	protected $cid;
	protected $name;
	protected $id;
	protected $description;
	protected $contacts;
	protected $type;
	// Class Constructor
	public function __construct() {
		parent::__construct();
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getaid() {
		return($this->aid);
	}
	
	public function getcid() {
		return($this->cid);
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
	
	public function gettype() {
		return $this->type;
	}
	
	// SET Functions
	public function setaid($mValue) {
		$this->aid = $mValue;
	}
	
	public function setcid($mValue) {
		$this->cid = $mValue;
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
	
	public function settype($mType) {
		$this->type = $mType;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM application WHERE aid = $mID;";
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
		$this->aid = $oRow->aid; // Primary Key
		$this->cid = $oRow->cid;
		$this->name = $oRow->name;
		$this->id = $oRow->id;
		$this->description = $oRow->description;
		$this->contacts = $oRow->contacts;
		$this->type = $oRow->type;
		return true;
	}
	
	public function insert() {
		$this->aid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO application (`cid`, `name`, `id`, `description`, `contacts`,`type`) VALUES ('$this->cid', '$this->name', '$this->id', '$this->description', '$this->contacts','$this->type');";
		$oResult = $this->database->query($sSQL);
		$this->aid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE application SET aid = '$this->aid', `cid` = '$this->cid', `name` = '$this->name', `id` = '$this->id', `description` = '$this->description', `contacts` = '$this->contacts', `type`= '$this->type' WHERE aid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM application WHERE aid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modApplication"
?>