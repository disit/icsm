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
* Class Name:       modService
* File Name:        modService.class.php
* Generated:        Friday, Oct 25, 2013 - 12:32:08 CEST
*  - for Table:     service
*  - in Database:   icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modService"
class modService extends modObject
{
	// Variable declaration
	protected $sid; // Primary Key
	protected $minfo_id;
	protected $aid;
	
	protected $id;
	protected $name;
	protected $type;
	protected $description;
	protected $ip_address;
	protected $service_group;
	protected $run_on;
	protected $monitor_info;
	protected $port;
	protected $process_name;
	protected $alias;
	protected $parent;
	protected $auth_user;
	protected $auth_pwd;
	
	
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->port=0;
		$this->alias="";
		$this->auth_user="";
		$this->auth_pwd="";
		$this->process_name="";
		$this->ip_address="";
		$this->parent="";
		$this->service_group="";
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getsid() {
		return($this->sid);
	}
	
	public function getid() {
		return($this->id);
	}
	
	public function gettype() {
		return($this->type);
	}
	
	public function getdescription() {
		return($this->description);
	}
	
	public function getip_address() {
		return($this->ip_address);
	}
	
	public function getservice_group() {
		return($this->service_group);
	}
	
	public function getrun_on() {
		return($this->run_on);
	}
	
	public function getport() {
		return($this->port);
	}
	
	public function getprocess_name() {
		return($this->process_name);
	}
	
	public function getalias() {
		return($this->alias);
	}
	
	public function getparent() {
		return($this->parent);
	}
	
	public function getauth_user() {
		return($this->auth_user);
	}
	
	public function getauth_pwd() {
		return($this->auth_pwd);
	}
	
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	public function getaid() {
		return($this->aid);
	}
	
	public function getname() {
		return($this->name);
	}
	
	// SET Functions
	public function setsid($mValue) {
		$this->sid = $mValue;
	}
	
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function settype($mValue) {
		$this->type = $mValue;
	}
	
	public function setdescription($mValue) {
		$this->description = $mValue;
	}
	
	public function setip_address($mValue) {
		$this->ip_address = $mValue;
	}
	
	public function setservice_group($mValue) {
		$this->service_group = $mValue;
	}
	
	public function setrun_on($mValue) {
		$this->run_on = $mValue;
	}
	
	public function setport($mValue) {
		$this->port = $mValue;
	}
	
	public function setprocess_name($mValue) {
		$this->process_name = $mValue;
	}
	
	public function setalias($mValue) {
		$this->alias = $mValue;
	}
	
	public function setparent($mValue) {
		$this->parent = $mValue;
	}
	
	public function setauth_user($mValue) {
		$this->auth_user = $mValue;
	}
	
	public function setauth_pwd($mValue) {
		$this->auth_pwd = $mValue;
	}
	
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function setaid($mValue) {
		$this->aid = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM service WHERE sid = $mID;";
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
		$this->sid = $oRow->sid; // Primary Key
		$this->id = $oRow->id;
		$this->type = $oRow->type;
		$this->description = $oRow->description;
		$this->ip_address = $oRow->ip_address;
		$this->service_group = $oRow->service_group;
		$this->run_on = $oRow->run_on;
		$this->port = $oRow->port;
		$this->process_name = $oRow->process_name;
		$this->alias = $oRow->alias;
		$this->parent = $oRow->parent;
		$this->auth_user = $oRow->auth_user;
		$this->auth_pwd = $oRow->auth_pwd;
		$this->minfo_id = $oRow->minfo_id;
		$this->aid = $oRow->aid;
		$this->name = $oRow->name;
		return true;
	}
	
	public function insert() {
		$this->sid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO service (`id`, `type`, `description`, `ip_address`, `service_group`, `run_on`, `port`, `process_name`, `alias`, `parent`, `auth_user`, `auth_pwd`, `minfo_id`, `aid`, `name`) VALUES ('$this->id', '$this->type', '$this->description', '$this->ip_address', '$this->service_group', '$this->run_on', '$this->port', '$this->process_name', '$this->alias', '$this->parent', '$this->auth_user', '$this->auth_pwd', '$this->minfo_id', '$this->aid', '$this->name');";
		$oResult = $this->database->query($sSQL);
		$this->sid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE service SET (sid = '$this->sid', `id` = '$this->id', `type` = '$this->type', `description` = '$this->description', `ip_address` = '$this->ip_address', `service_group` = '$this->service_group', `run_on` = '$this->run_on', `port` = '$this->port', `process_name` = '$this->process_name', `alias` = '$this->alias', `parent` = '$this->parent', `auth_user` = '$this->auth_user', `auth_pwd` = '$this->auth_pwd', `minfo_id` = '$this->minfo_id', `aid` = '$this->aid', `name` = '$this->name') WHERE sid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM service WHERE sid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modService"
?>