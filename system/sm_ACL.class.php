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

	class sm_ACL implements sm_Module
	{
		var $perms = array();		//Array : Stores the permissions for the user
		var $userID = 0;			//Integer : Stores the ID of the current user
		var $userRoles = array();	//Array : Stores the roles of the current user
		protected $db;
		
		function __construct($user=null)
		{
			$this->db = sm_Database::getInstance();
			
			if (is_numeric($user))
			{
				$this->userID = floatval($user);
			} 
			else if($user instanceof sm_User){
				$this->userID = $user->userID;
			}
			else 
				$this->userID=sm_User::current()->userID;
			//$this->userID = $user->userID;
			$this->userRoles = $this->getUserRoles($this->userID);
			$this->buildACL();
		}
		
	/*	function ACL($userID = '')
		{
			$this->__constructor($userID);
			//crutch for PHP4 setups
		}*/
		
		function buildACL()
		{
			//first, get the rules for the user's role
			if (count($this->userRoles) > 0)
			{
				$this->perms = array_merge($this->perms,$this->getRolePerms($this->userRoles));
			}
			//then, get the individual user permissions
			$this->perms = array_merge($this->perms,$this->getUserPerms($this->userID));
		}
		
		function getAllUserPerms($format='ids')
		{
				$resp=array();
				foreach($this->perms as $row)
				{
					if($row['value'] == '1')
					{
						if ($format == 'full')
						{
							$resp[] = $row;
						} else {
							$resp[] = $row['ID'];
						}
					}
				}
			
			return $resp;
		}
		
		function getPermKeyFromID($permID)
		{
			$strSQL = "SELECT `permKey` FROM `acl_permissions` WHERE `ID` = " . floatval($permID) . " LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['permKey'];
			return null;
		}
		
		function getPermDescriptionFromID($permID)
		{
			$strSQL = "SELECT `description` FROM `acl_permissions` WHERE `ID` = " . floatval($permID) . " LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['description'];
			return null;
		}
		
		function getPermIDFromKey($permKey)
		{
			$strSQL = "SELECT `ID` FROM `acl_permissions` WHERE `permKey` = '" . $permKey . "' LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['ID'];
			return null;
		}
		
		function getPermNameFromID($permID)
		{
			$strSQL = "SELECT `permName` FROM `acl_permissions` WHERE `ID` = " . floatval($permID) . " LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['permName'];
			return null;
		}
		
		function getRoleIDFromName($roleName)
		{
			$strSQL = "SELECT `ID` FROM `acl_roles` WHERE `roleName` = '" . $roleName . "' LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['ID'];
			return null;
		}
		
		function getRoleNameFromID($roleID)
		{
			$strSQL = "SELECT `roleName` FROM `acl_roles` WHERE `ID` = " . floatval($roleID) . " LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['roleName'];
			return null;
		}
		
		function getRoleDescription($roleID)
		{
			$strSQL = "SELECT `description` FROM `acl_roles` WHERE `ID` = " . floatval($roleID) . " LIMIT 1";
			$result = $this->db->query($strSQL);
			if($result)
				return $result[0]['description'];
			return null;
		}
		
		function getUserRoles($userID=null)
		{
			if(!$userID)
				$userID=$this->userID;
			$strSQL = "SELECT * FROM `acl_user_roles` WHERE `userID` = " . floatval($userID) . " ORDER BY `addDate` ASC";
			$result = $this->db->query($strSQL);
			$resp = array();
			if($result)
				foreach($result as $row)
				{
					$resp[] = $row['roleID'];
				}
			return $resp;
		}
		
		function getUserWithRoles($roleID=null)
		{
			$users=array();
			if($roleID){
				$strSQL = "SELECT * FROM `acl_user_roles` WHERE `roleID` = " . floatval($roleID) . " ORDER BY `addDate` ASC";
				$result = $this->db->query($strSQL);
				if($result)
					foreach($result as $row)
					{
						$users[] = $row['userID'];
					}
			}
			return $users;
		}
		
		/**
		 * 
		 * @param string $format ('ids' only ids,'full' ids and role names)
		 * @return multitype:unknown multitype:unknown
		 */
		
		function getAllRoles($format='ids',$limit=null)
		{
			$format = strtolower($format);
			$strSQL = "SELECT * FROM `acl_roles` ORDER BY `roleName` ASC ".$limit;
			$result = $this->db->query($strSQL);
			$resp = array();
			if($result)
				foreach($result as $row)
				{
					if ($format == 'full')
					{
						$resp[] = array("ID" => $row['ID'],"Name" => $row['roleName'],"Description"=>$row['description']);
					} else {
						$resp[] = $row['ID'];
					}
				}
			return $resp;
		}
		
		function countRoles(){
			$strSQL = "SELECT COUNT(*) as count FROM `acl_roles`";
			$result = $this->db->query($strSQL);
			return $result[0]['count'];
		}
		
		/**
		 * 
		 * @param string $format ('ids' only ids,'full' ids and role names)
		 * @return multitype:unknown multitype:unknown
		 */
		
		function getAllPerms($format='ids')
		{
			$format = strtolower($format);
			$strSQL = "SELECT * FROM `acl_permissions` ORDER BY `permName` ASC";
			$result = $this->db->query($strSQL);
			$resp = array();
			if($result)
				foreach($result as $row)
				{
					if ($format == 'full')
					{
						$resp[$row['permKey']] = array('ID' => $row['ID'], 'Name' => $row['permName'], 'Key' => $row['permKey']);
					} else {
						$resp[] = $row['ID'];
					}
				}
			return $resp;
		}

		function getRolePerms($role)
		{
			if (is_array($role))
			{
				$roleSQL = "SELECT * FROM `acl_role_perms` WHERE `roleID` IN (" . implode(",",$role) . ") ORDER BY `ID` ASC";
			} else {
				$roleSQL = "SELECT * FROM `acl_role_perms` WHERE `roleID` = " . floatval($role) . " ORDER BY `ID` ASC";
			}
			$result = $this->db->query($roleSQL);
			$perms = array();
			if($result)
				foreach($result as $row)
				{
					$pK = strtolower($this->getPermKeyFromID($row['permID']));
					if ($pK == '') { continue; }
					if ($row['value'] === '1') {
						$hP = true;
					} else {
						$hP = false;
					}
					$perms[$pK] = array('perm' => $pK,'inheritted' => true,'value' => $hP,'Name' => $this->getPermNameFromID($row['permID']),'ID' => $row['permID']);
				}
			return $perms;
		}
		
		function getUserPerms($userID=null)
		{
			if(!$userID)
				$userID=$this->userID;
			$strSQL = "SELECT * FROM `acl_user_perms` WHERE `userID` = " . floatval($this->userID) . " ORDER BY `addDate` ASC";
			$result = $this->db->query($strSQL);
			$perms = array();
			if($result)
				foreach($result as $row)
				{
					$pK = strtolower($this->getPermKeyFromID($row['permID']));
					if ($pK == '') { continue; }
					if ($row['value'] == '1') {
						$hP = true;
					} else {
						$hP = false;
					}
					$perms[$pK] = array('perm' => $pK,'inheritted' => false,'value' => $hP,'Name' => $this->getPermNameFromID($row['permID']),'ID' => $row['permID']);
				} 
			return $perms;
		}
		
		function userHasRole($roleID)
		{
			foreach($this->userRoles as $k => $v)
			{
				if (floatval($v) === floatval($roleID))
				{
					return true;
				}
			}
			return false;
		}
		
		function hasPermission($permKey)
		{
			$permKey = strtolower($permKey);
			if (array_key_exists($permKey,$this->perms))
			{
				if ($this->perms[$permKey]['value'] === '1' || $this->perms[$permKey]['value'] === true)
				{
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		function getUsername($userID=null)
		{
			/*$strSQL = "SELECT `username` FROM `users` WHERE `ID` = " . floatval($userID) . " LIMIT 1";
			$data = mysql_query($strSQL);
			$row = mysql_fetch_array($data);
			return $row[0];*/
			if(!$userID)
			{
				$userID=$this->userID;
			}
			$u = new sm_User();
			$u->loadUser($userID);
			if($u->is_loaded())
				return $u->userData['username'];
			return null;
		}
		
		/**
		 * 
		 * @param unknown $permKey
		 * @return boolean
		 */
		
		static public function checkPermission($permKey){
			$acl = new sm_ACL(sm_User::current());
			return $acl->hasPermission($permKey);
		}
		
		/**
		 * 
		 * @param unknown $roleName
		 * @return boolean
		 */
		static public function checkRole($roleName){
			$acl = new sm_ACL(sm_User::current());
			$roleID=$acl->getRoleIDFromName($roleName);
			return $acl->userHasRole($roleID);
		}
		
		
		static public function installPerm($perm)
		{
			if(!isset($perm['permID']))
				$perm['permID']=null;
			$acl = new sm_ACL();
			if(!$acl->getPermIDFromKey($perm['permKey']))
			{
				$acl->savePerm($perm);
			
				//Assign the permission to System Administrator as default
				$perms["perm_".$acl->getPermIDFromKey($perm['permKey'])]=1;
				$perms['roleID']='00000000000000000001';
				
				$acl->saveRolePerms($perms);
			}
			
		}
		
		static public function install($db)
		{
			include(__DIR__."/sql/acl_install.sql.php");
			foreach($sql as $i=>$q)
			{
				$result=$db->query($q);
		
				if($result)
				{
					sm_Logger::write("Installed table #".$i);
					sm_set_message("Installed table #".$i);
				}
				else
				{
					$e = $db->getError();
					sm_Logger::write($e);
					sm_set_error($e);
				}
			}
		
			return true;
		
		}
		
		static public function uninstall($db)
		{
			include(__DIR__."/sql/acl_uninstall.sql.php");
			foreach($sql as $i=>$q)
			{
				$result=$db->query($q);
		
				if($result)
				{
					sm_Logger::write("Uninstalled table #".$i);
					sm_set_message("Uninstalled table #".$i);
				}
				else
				{
					$e = $db->getError();
					sm_Logger::write("Error when uninstalling table #".$i." (".$e.")");
					sm_set_error("Error when uninstalling table #".$i." (".$e.")");
					
				}
			}
		
			return true;
		}
	
		function saveRole($role){
			$strSQL = sprintf("REPLACE INTO `acl_roles` SET `ID` = %u, `roleName` = '%s', `description` = '%s'",$role['roleID'],$role['roleName'],$role['description']);
			$result=$this->db->query($strSQL);
			$roleID = $this->getRoleIDFromName($role['roleName']);
			/*if ($this->db->getNumRowsFound() > 0)
			{
				$roleID = $role['roleID'];
			} else {
				$roleID = $this->db->getLastInsertedId();
			}*/
			foreach ($role as $k => $v)
			{
				if (substr($k,0,5) == "perm_")
				{
					$permID = str_replace("perm_","",$k);
					if ($v == 'X' || $v == 'x' || $v=="3")
					{
						$strSQL = sprintf("DELETE FROM `acl_role_perms` WHERE `roleID` = %u AND `permID` = %u",$roleID,$permID);
						$this->db->query($strSQL);
						continue;
					}
					$strSQL = sprintf("REPLACE INTO `acl_role_perms` SET `roleID` = %u, `permID` = %u, `value` = %u, `addDate` = '%s'",$roleID,$permID,$v,date ("Y-m-d H:i:s"));
					$this->db->query($strSQL);
				}
			}
			if($result)
				return true;
			return false;
		}
		
		function deleteRole($role){
			
			$strSQL = sprintf("DELETE FROM `acl_roles` WHERE `ID` = %u LIMIT 1",$role['roleID']);
			$this->db->query($strSQL);
			$strSQL = sprintf("DELETE FROM `acl_user_roles` WHERE `roleID` = %u",$role['roleID']);
			$this->db->query($strSQL);
			$strSQL = sprintf("DELETE FROM `acl_role_perms` WHERE `roleID` = %u",$role['roleID']);
			$this->db->query($strSQL);
			return true;
		}
		
		function saveUserRole($role){
			$result=true;
			foreach ($role as $k => $v)
			{
				if (substr($k,0,5) == "role_")
				{
					$roleID = str_replace("role_","",$k);
					if ($v == '0' || $v == 'X' || $v == 'x') {
						$strSQL = sprintf("DELETE FROM `acl_user_roles` WHERE `userID` = %u AND `roleID` = %u",$role['userID'],$roleID);
					} else {
						$strSQL = sprintf("REPLACE INTO `acl_user_roles` SET `userID` = %u, `roleID` = %u, `addDate` = '%s'",$role['userID'],$roleID,date ("Y-m-d H:i:s"));
					}
					$result&=$this->db->query($strSQL);
				}
			}
			return $result;
		}
		
		function saveUserPerms($perms){
			$result=true;
			foreach ($perms as $k => $v)
			{
				if (substr($k,0,5) == "perm_")
				{
					$permID = str_replace("perm_","",$k);
					if ($v == 'x' || $v == 'X' || $v=="3")
					{
						$strSQL = sprintf("DELETE FROM `acl_user_perms` WHERE `userID` = %u AND `permID` = %u",$perms['userID'],$permID);
					} else {
						$strSQL = sprintf("REPLACE INTO `acl_user_perms` SET `userID` = %u, `permID` = %u, `value` = %u, `addDate` = '%s'",$perms['userID'],$permID,$v,date ("Y-m-d H:i:s"));
					}
					$result&=$this->db->query($strSQL);
				}
			}
			return $result;
		}
		
		function saveRolePerms($perms){
			$result=true;
			foreach ($perms as $k => $v)
			{
				if (substr($k,0,5) == "perm_")
				{
					$permID = str_replace("perm_","",$k);
					if ($v == 'x' || $v == 'X' || $v=="3")
					{
						$strSQL = sprintf("DELETE FROM `acl_role_perms` WHERE `roleID` = %u AND `permID` = %u",$perms['roleID'],$permID);
					} else {
						//$strSQL = sprintf("REPLACE INTO `acl_user_perms` SET `userID` = %u, `permID` = %u, `value` = %u, `addDate` = '%s'",$perms['userID'],$permID,$v,date ("Y-m-d H:i:s"));
						$strSQL = sprintf("REPLACE INTO `acl_role_perms` SET `roleID` = %u, `permID` = %u, `value` = %u, `addDate` = '%s'",$perms['roleID'],$permID,$v,date ("Y-m-d H:i:s"));
					}
					$result&=$this->db->query($strSQL);
				}
			}
			return $result;
		}
		
		
		function savePerm($perm){
			if(!isset($perm['description']))
				$perm['description']="";
			
			$strSQL = sprintf("REPLACE INTO `acl_permissions` SET `ID` = %u, `permName` = '%s', `permKey` = '%s', `description` = '%s'",$perm['permID'],$perm['permName'],$perm['permKey'],$perm['description']);
			$result=$this->db->query($strSQL);
			return $result;
		}
		
		function deletePerm($perm){
			$strSQL = sprintf("DELETE FROM `acl_permissions` WHERE `ID` = %u LIMIT 1",$perm['permID']);
			$result=$this->db->query($strSQL);
			return $result;
		}
	}

?>