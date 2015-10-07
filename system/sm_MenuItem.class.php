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
* Class Name:       sm_MenuItem
* File Name:        sm_MenuItem.class.php
* Generated:        Monday, Jun 9, 2014 - 18:52:09 CEST
*  - for Table:     menu
*  - in Database:   icaro
* Created by: 
********************************************************************************/

// Files required by class:
// No files required.

// Begin Class "sm_MenuItem"
class sm_MenuItem 
{
	// Variable declaration
	protected $mid; // Primary Key
	protected $title;
	//protected $access;
	protected $groupId;
	protected $path;
	protected $icon;
	protected $home;
	protected $hidden;
	protected $weight;
	protected $parent;
	protected $disabled;
	
	
	// Class Constructor
	public function __construct() {
		
		
		$this->groupId=-1;
		$this->home=0;
		$this->hidden=0;
		$this->weight=0;
		$this->parent=0;
		$this->disabled=0;
	}
	
	// Class Destructor
	public function __destruct() {
		
	}
	
	// GET Functions
	public function getmid() {
		return($this->mid);
	}
	
	public function gettitle() {
		return($this->title);
	}
	
/*	public function getaccess() {
		return($this->access);
	}*/
	
	public function getgroupId() {
		return($this->groupId);
	}
	
	public function getpath() {
		return($this->path);
	}
	
	public function geticon() {
		return($this->icon);
	}
	
	public function gethome() {
		return($this->home);
	}
	
	public function gethidden() {
		return($this->hidden);
	}
	
	public function getweight() {
		return($this->weight);
	}
	
	public function getparent() {
		return($this->parent);
	}
	
	public function getdisabled() {
		return($this->disabled);
	}
	
	// SET Functions
	public function setmid($mValue) {
		$this->mid = $mValue;
	}
	
	public function settitle($mValue) {
		$this->title = $mValue;
	}
	
	/*public function setaccess($mValue) {
		$this->access = $mValue;
	}*/
	
	public function setgroupId($mValue) {
		$this->groupId = $mValue;
	}
	
	public function setpath($mValue) {
		$this->path = $mValue;
	}
	
	public function seticon($mValue) {
		$this->icon = $mValue;
	}
	
	public function sethome($mValue) {
		$this->home = $mValue;
	}
	
	public function sethidden($mValue) {
		$this->hidden = $mValue;
	}
	
	public function hasChildren()
	{
		$database = sm_Database::getInstance();
		$database->select("menu_items",array("parent"=>$this->mid));
		return $database->getNumRowsFound()>0;
	}
	
	public function setweight($mValue) {
		$this->weight = $mValue;
	}
	
	public function setparent($mValue) {
		$this->parent = $mValue;
	}
	
	public function setdisabled($mValue) {
		$this->disabled = $mValue;
	}
	
	public function load($id)
	{
		$this->select(array("mid"=>$id));
	}
	
	public function loadByPath($path)
	{
		$this->select(array("path"=>$path));
	}
	
	public function breadcrumbs(){
		$parents=array();
		$database = sm_Database::getInstance();
		if($this->parent>0)
		{
			$parentId=$this->parent; 
			while($parentId>0)
			{
				$r=$database->select("menu_items",array("mid"=>$parentId),array("title","parent"));
				if(!isset($r[0]['title']))
				{
					if(sm_ACL::checkRole("System Administrator"));
					 	sm_set_error("Verify parent for the menu item ".$this->title."@".$this->path);
					continue;
				}
				$parents[]=$r[0]['title'];
				$parentId=$r[0]['parent'];
				
			}
		}
		return implode("/", $parents);
	} 
	
	function reparent($parentId)
	{
		$this->parent=$parentId;
		$this->insert();
	}
	
	public function select($where=array(),$fiels=array()) { // SELECT Function
		$database = sm_Database::getInstance();
		// Execute SQL Query to get record.
		//$sSQL = "SELECT * FROM menu WHERE mid = $mID;";
		$oResult = $database->select("menu_items",$where);
		$oRow=null;
		if ($oResult) {
			$oRow =(object)$oResult[0];
		}
		else {
			$err=$database->getError();
			if ($err!=""){
				trigger_error($err);
			}
			return false;
		}
		// Assign results to class.
		$this->mid = $oRow->mid; // Primary Key
		$this->title = $oRow->title;
		//$this->access = $oRow->access;
		$this->groupId = $oRow->groupId;
		$this->path = $oRow->path;
		$this->icon = $oRow->icon;
		$this->home = $oRow->home;
		$this->hidden = $oRow->hidden;
		$this->weight = $oRow->weight;
		$this->parent = $oRow->parent;
		$this->disabled = $oRow->disabled;
		return true;
	}
	
	public function insert() {
		//$this->mid = NULL; // Remove primary key value for insert
		$database = sm_Database::getInstance();
		$prop = sm_obj2array($this);
		unset($prop['mid']);
		if(!isset($this->mid))
		{
			
			
		//	$sSQL = "INSERT INTO menu (`title`, `access`, `type`, `path`, `icon`, `home`, `hidden`, `weight`, `parent`, `disabled`) VALUES ('$this->title', '$this->access', '$this->type', '$this->path', '$this->icon', '$this->home', '$this->hidden', '$this->weight', '$this->parent', '$this->disabled');";
		//	$oResult = $this->database->query($sSQL);
			$result = $database->save('menu_items',$prop);
			$this->mid = $database->getLastInsertedId();
		}
		else
			$result = $database->save('menu_items',$prop,array("mid"=>$this->mid));
		return $result;
	}
	
	
	public function delete($mID) {
		$database = sm_Database::getInstance();
		$oResult = $database->delete("menu_items",array("mid"=>$mID));
		return $oResult;
	}
	
	function setFields($fields)
	{
		
	}

}
// End Class "sm_MenuItem"
?>