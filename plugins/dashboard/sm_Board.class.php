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
* Class Name:       sm_Board
* File Name:        sm_Board.class.php
* Generated:        Wednesday, Nov 13, 2013 - 9:33:43 CET
*  - for Table:     dashboard
*  - in Database:   icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "sm_Board"
class sm_Board
{
	// Variable declaration
	protected $id; // Primary Key
	protected $ref_id;
	protected $module;
	protected $callback_args;
	protected $title;
	protected $segment;
	protected $weight;
	protected $enable;
	protected $view_name;
	protected $method;
	protected $group;
	//Database link
	protected $database;
	// Class Constructor
	public function __construct($id=null) {
		$this->weight=-1;
		$this->enable=1;
		$this->title="";
		$this->segment="";
		$this->method="";
		$this->group="";
		$this->database=sm_Database::getInstance();
		if($id)
			$this->select($id);
	}
	
	// Class Destructor
	public function __destruct() {
		//parent::__destruct();
	}
	
	// GET Functions
	public function getid() {
		return($this->id);
	}
	
	public function getref_id() {
		return($this->ref_id);
	}
	
	public function getmodule() {
		return($this->module);
	}
	
	public function getcallback_args() {
		return($this->callback_args);
	}
	
	public function getmethod() {
		return($this->method);
	}
	
	public function gettitle() {
		return($this->title);
	}
	
	public function getsegment() {
		return($this->segment);
	}
	
	public function getweight() {
		return($this->weight);
	}
	
	public function getenable() {
		return($this->enable);
	}
	
	public function getview_name() {
		return($this->view_name);
	}
	
	public function getgroup() {
		return($this->group);
	}
	
	// SET Functions
	public function setid($mValue) {
		$this->id = $mValue;
	}
	
	public function setref_id($mValue) {
		$this->ref_id = $mValue;
	}
	
	public function setmodule($mValue) {
		$this->module = $mValue;
	}
	
	public function setcallback_args($mValue) {
		$this->callback_args = $mValue;
	}
	
	public function setmethod($mValue)
	{
		$this->method=$mValue;
	}
	
	public function settitle($mValue) {
		$this->title = $mValue;
	}
	
	public function setsegment($mValue) {
		$this->segment = $mValue;
	}
	
	public function setweight($mValue) {
		$this->weight = $mValue;
	}
	
	public function setenable($mValue) {
		$this->enable = $mValue;
	}
	
	public function setview_name($mValue) {
		$this->view_name = $mValue;
	}
	
	public function setgroup($mValue) {
		$this->group = $mValue;
	}
	
	public function select($mID) { // SELECT Function
		// Execute SQL Query to get record.
		$sSQL = "SELECT * FROM dashboard WHERE id = $mID;";
		$oResult = $this->database->query($sSQL);
		$oRow=null;
		if ($oResult) {
			$oRow =(object)$oResult[0];
		}
		else {
			$err=mysqli_error( $this->database->getLink());
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
		// Assign results to class.
		$this->id = $oRow->id; // Primary Key
		$this->ref_id = $oRow->ref_id;
		$this->module = $oRow->module;
		$this->callback_args = $oRow->callback_args;
		$this->title = $oRow->title;
		$this->segment = $oRow->segment;
		$this->weight = $oRow->weight;
		$this->enable = $oRow->enable;
		$this->view_name = $oRow->view_name;
		$this->method = $oRow->method;
		$this->group = $oRow->group;
		return true;
	}
	
	public function exists($where=null) { // SELECT Function
		// Execute SQL Query to check exist record.
		$whereCond=$this->database->buildWhereClause('dashboard',$where);
		$sSQL = "SELECT * FROM dashboard ".$whereCond.";";
		$oResult = $this->database->query($sSQL);
		if ($oResult) {
			return $oResult;
		}
		else {
			$err=mysqli_error($this->database->getLink());
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
	}
	
	public function insert() {
		$this->id = NULL; // Remove primary key value for insert
		$sSQL = "INSERT INTO dashboard (`ref_id`, `module`, `callback_args`, `title`, `segment`, `weight`, `enable`, `view_name`,`method`,`group`) VALUES ('$this->ref_id', '$this->module', '$this->callback_args', '$this->title', '$this->segment', '$this->weight', '$this->enable', '$this->view_name', '$this->method','$this->group');";
		$oResult = $this->database->query($sSQL);
		$this->id = $this->database->getLastInsertedId();
	}
	
	function update($mID) {
		$sSQL = "UPDATE dashboard SET id = '$this->id', `ref_id` = '$this->ref_id', `module` = '$this->module', `callback_args` = '$this->callback_args', `title` = '$this->title', `segment` = '$this->segment', `weight` = '$this->weight', `enable` = '$this->enable', `view_name` = '$this->view_name', `method` = '$this->method' , `group`= '$this->group' WHERE id = $mID;";
		sm_Logger::write($sSQL);
		$oResult = $this->database->query($sSQL);
	}
	
	public function delete($mID) {
		$sSQL = "DELETE FROM dashboard WHERE id = $mID;";
		$oResult = $this->database->query($sSQL);
	}

}
// End Class "sm_Board"
?>