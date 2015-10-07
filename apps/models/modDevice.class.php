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
* Class Name:       modDevice
* File Name:        modDevice.class.php
* Generated:        Wednesday, Oct 9, 2013 - 16:00:29 CEST
*  - for Table:     device
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modDevice"
class modDevice extends modObject
{
	// Variable declaration
	protected $did; // Primary Key
	protected $id;
	protected $device_type;
	protected $type;
	protected $ip_address;
	protected $monitor_ip_address;
	protected $name;
	protected $model;
	protected $description;
	protected $port;
	protected $alias;
	protected $parent_device;
	protected $device_group;
	protected $auth_user;
	protected $auth_pwd;
	protected $domain_name;
	protected $minfo_id;
	protected $cid;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->minfo_id=-1;
		$this->port=0;
		$this->alias="";
		$this->auth_user="";
		$this->auth_pwd="";
		$this->process_name="";
		$this->ip_address="";
		$this->parent_device="";
		$this->device_group="";
		$this->device_type="";
		$this->monitor_ip_address="";
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function getdid() {
		return($this->did);
	}
	
	public function getid() {
		return($this->id);
	}
	
	public function getdevice_type() {
		return($this->device_type);
	}
	
	public function gettype() {
		return($this->type);
	}
	
	public function getip_address() {
		return($this->ip_address);
	}
	
	public function getmonitor_ip_address() {
		return($this->monitor_ip_address);
	}
	
	public function getname() {
		return($this->name);
	}
	
	public function getmodel() {
		return($this->model);
	}
	
	public function getdescription() {
		return($this->description);
	}
	
	public function getport() {
		return($this->port);
	}
	
	public function getalias() {
		return($this->alias);
	}
	
	public function getparent_device() {
		return($this->parent_device);
	}
	
	public function getdevice_group() {
		return($this->device_group);
	}
	
	public function getauth_user() {
		return($this->auth_user);
	}
	
	public function getauth_pwd() {
		return($this->auth_pwd);
	}
	
	public function getdomain_name() {
		return($this->domain_name);
	}
	
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	public function getcid() {
		return($this->cid);
	}
	
	// SET Functions
	public function setdid($mValue) {
		$this->did = $mValue;
	}
	
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function setdevice_type($mValue) {
		$this->device_type = $mValue;
	}
	
	public function settype($mValue) {
		$this->type = $mValue;
	}
	
	public function setip_address($mValue) {
		$this->ip_address = $mValue;
	}
	
	public function setmonitor_ip_address($mValue) {
		$this->monitor_ip_addres = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function setmodel($mValue) {
		$this->model = $mValue;
	}
	
	public function setdescription($mValue) {
		$this->description = $mValue;
	}
	
	public function setport($mValue) {
		$this->port = $mValue;
	}
	
	public function setalias($mValue) {
		$this->alias = $mValue;
	}
	
	public function setparent_device($mValue) {
		$this->parent_device = $mValue;
	}
	
	public function setdevice_group($mValue) {
		$this->device_group = $mValue;
	}
	
	public function setauth_user($mValue) {
		$this->auth_user = $mValue;
	}
	
	public function setauth_pwd($mValue) {
		$this->auth_pwd = $mValue;
	}
	
	public function setdomain_name($mValue) {
		$this->domain_name = $mValue;
	}
	
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function setcid($mValue) {
		$this->cid = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM device WHERE did = $mID;";
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
		$this->did = $oRow->did; // Primary Key
		$this->id = $oRow->id;
		$this->device_type = $oRow->device_type;
		$this->type = $oRow->type;
		$this->ip_address = $oRow->ip_address;
		$this->monitor_ip_address = $oRow->monitor_ip_address;
		$this->name = $oRow->name;
		$this->model = $oRow->model;
		$this->description = $oRow->description;
		$this->port = $oRow->port;
		$this->alias = $oRow->alias;
		$this->parent_device = $oRow->parent_device;
		$this->device_group = $oRow->device_group;
		$this->auth_user = $oRow->auth_user;
		$this->auth_pwd = $oRow->auth_pwd;
		$this->domain_name = $oRow->domain_name;
		$this->minfo_id = $oRow->minfo_id;
		$this->cid = $oRow->cid;
		return true;
	}
	
	public function insert() {
		$this->did = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO device (`id`, `device_type`, `type`, `ip_address`, `name`, `model`, `description`, `port`, `alias`, `parent_device`, `device_group`, `auth_user`, `auth_pwd`, `domain_name`, `minfo_id`, `cid`, `monitor_ip_address`) VALUES ('$this->id', '$this->device_type', '$this->type', '$this->ip_address', '$this->name', '$this->model', '$this->description', '$this->port', '$this->alias', '$this->parent_device', '$this->device_group', '$this->auth_user', '$this->auth_pwd', '$this->domain_name', '$this->minfo_id', '$this->cid', '$this->monitor_ip_address');";
		$oResult = $this->database->query($sSQL);
		$this->did = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE device SET did = '$this->did', `id` = '$this->id', `device_type` = '$this->device_type', `type` = '$this->type', `ip_address` = '$this->ip_address', `name` = '$this->name', `model` = '$this->model', `description` = '$this->description', `port` = '$this->port', `alias` = '$this->alias', `parent_device` = '$this->parent_device', `device_group` = '$this->device_group', `auth_user` = '$this->auth_user', `auth_pwd` = '$this->auth_pwd', `domain_name` = '$this->domain_name', `minfo_id` = '$this->minfo_id', `cid` = '$this->cid', `monitor_ip_address`='$this->monitor_ip_address' WHERE did = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM device WHERE did = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modDevice"
?>