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
* Class Name:       modHost
* File Name:        modHost.class.php
* Generated:        Wednesday, Oct 9, 2013 - 16:00:41 CEST
*  - for Table:     host
*  - in Database:   test
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "modHost"
class modHost extends modObject
{
	// Variable declaration
	protected $hid; // Primary Key
	protected $id;
	protected $name;
	protected $os;
	protected $type;
	protected $ip_address;
	protected $monitor_ip_address;
	protected $alias;
	protected $parent_host;
	protected $host_group;
	protected $domain_name;
	protected $auth_pwd;
	protected $auth_user;
	protected $description;
	protected $minfo_id;
	protected $cid;
	// Class Constructor
	public function __construct() {
		parent::__construct();
		$this->minfo_id=-1;
		$this->monitor_ip_address="";
	}
	
	// Class Destructor
	public function __destruct() {
		parent::__destruct();
	}
	
	// GET Functions
	public function gethid() {
		return($this->hid);
	}
	
	public function getid() {
		return($this->id);
	}
	
	public function getname() {
		return($this->name);
	}
	
	public function getos() {
		return($this->os);
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
	
	public function getalias() {
		return($this->alias);
	}
	
	public function getparent_host() {
		return($this->parent_host);
	}
	
	public function gethost_group() {
		return($this->host_group);
	}
	
	public function getdomain_name() {
		return($this->domain_name);
	}
	
	public function getauth_pwd() {
		return($this->auth_pwd);
	}
	
	public function getauth_user() {
		return($this->auth_user);
	}
	
	public function getdescription() {
		return($this->description);
	}
	
	public function getminfo_id() {
		return($this->minfo_id);
	}
	
	public function getcid() {
		return($this->cid);
	}
	
	// SET Functions
	public function sethid($mValue) {
		$this->hid = $mValue;
	}
	
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function setname($mValue) {
		$this->name = $mValue;
	}
	
	public function setos($mValue) {
		$this->os = $mValue;
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
	
	public function setalias($mValue) {
		$this->alias = $mValue;
	}
	
	public function setparent_host($mValue) {
		$this->parent_host = $mValue;
	}
	
	public function sethost_group($mValue) {
		$this->host_group = $mValue;
	}
	
	public function setdomain_name($mValue) {
		$this->domain_name = $mValue;
	}
	
	public function setauth_pwd($mValue) {
		$this->auth_pwd = $mValue;
	}
	
	public function setauth_user($mValue) {
		$this->auth_user = $mValue;
	}
	
	public function setdescription($mValue) {
		$this->description = $mValue;
	}
	
	public function setminfo_id($mValue) {
		$this->minfo_id = $mValue;
	}
	
	public function setcid($mValue) {
		$this->cid = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM host WHERE hid = $mID;";
		if(is_numeric($mID))
			$sSQL = "SELECT * FROM host WHERE hid = $mID;";
		else
			$sSQL = "SELECT * FROM host WHERE hid = '$mID' OR description = '$mID';";
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
		$this->hid = $oRow->hid; // Primary Key
		$this->id = $oRow->id;
		$this->name = $oRow->name;
		$this->os = $oRow->os;
		$this->type = $oRow->type;
		$this->ip_address = $oRow->ip_address;
		$this->monitor_ip_address = $oRow->monitor_ip_address;
		$this->alias = $oRow->alias;
		$this->parent_host = $oRow->parent_host;
		$this->host_group = $oRow->host_group;
		$this->domain_name = $oRow->domain_name;
		$this->auth_pwd = $oRow->auth_pwd;
		$this->auth_user = $oRow->auth_user;
		$this->description = $oRow->description;
		$this->minfo_id = $oRow->minfo_id;
		$this->cid = $oRow->cid;
		return true;
	}
	
	public function insert() {
		$this->hid = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO host (`id`, `name`, `os`, `type`, `ip_address`, `alias`, `parent_host`, `host_group`, `domain_name`, `auth_pwd`, `auth_user`, `description`, `minfo_id`, `cid`, `monitor_ip_address`) VALUES ('$this->id', '$this->name', '$this->os', '$this->type', '$this->ip_address', '$this->alias', '$this->parent_host', '$this->host_group', '$this->domain_name', '$this->auth_pwd', '$this->auth_user', '$this->description', '$this->minfo_id', '$this->cid', '$this->monitor_ip_address');";
		$oResult = $this->database->query($sSQL);
		$this->hid = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE host SET hid = '$this->hid', `id` = '$this->id', `name` = '$this->name', `os` = '$this->os', `type` = '$this->type', `ip_address` = '$this->ip_address', `alias` = '$this->alias', `parent_host` = '$this->parent_host', `host_group` = '$this->host_group', `domain_name` = '$this->domain_name', `auth_pwd` = '$this->auth_pwd', `auth_user` = '$this->auth_user', `description` = '$this->description', `minfo_id` = '$this->minfo_id', `cid` = '$this->cid', `monitor_ip_address`='$this->monitor_ip_address' WHERE hid = $mID;";
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM host WHERE hid = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "modHost"
?>