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


$sql['permissions']='CREATE TABLE IF NOT EXISTS `acl_permissions` (
	`ID` bigint(20) unsigned zerofill NOT NULL auto_increment,
	`permKey` varchar(30) NOT NULL,
	`permName` varchar(30) NOT NULL,
	`description` text, 
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `permKey` (`permKey`)
	) ENGINE=MyIsam DEFAULT CHARSET=utf8;';
	
$sql['roles']='CREATE TABLE IF NOT EXISTS `acl_roles` (
  `ID` bigint(20) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `roleName` varchar(20) NOT NULL,
  `description` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `roleName` (`roleName`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;';
	
$sql['role_perms']="CREATE TABLE IF NOT EXISTS `acl_role_perms` (
	`ID` bigint(20) unsigned zerofill NOT NULL auto_increment,
	`roleID` bigint(20) NOT NULL,
	`permID` bigint(20) NOT NULL,
	`value` tinyint(1) NOT NULL default '0',
	`addDate` datetime NOT NULL, 
	PRIMARY KEY  (`ID`),
	UNIQUE KEY `roleID_2` (`roleID`,`permID`)
	) ENGINE=MyIsam DEFAULT CHARSET=utf8;";

$sql['user_perms']="CREATE TABLE IF NOT EXISTS `acl_user_perms` (
		`ID` bigint(20) unsigned zerofill NOT NULL auto_increment,
		`userID` bigint(20) NOT NULL, 
		`permID` bigint(20) unsigned zerofill NOT NULL,
		`value` tinyint(1) NOT NULL default '0', 
		`addDate` datetime NOT NULL, 
		PRIMARY KEY  (`ID`), 
		UNIQUE KEY `userID` (`userID`,`permID`)
	) ENGINE=MyIsam DEFAULT CHARSET=utf8;";

$sql['user_roles']="CREATE TABLE IF NOT EXISTS `acl_user_roles` (
		`userID` bigint(20) NOT NULL,
		`roleID` bigint(20) unsigned zerofill NOT NULL,
		`addDate` datetime NOT NULL,
		UNIQUE KEY `userID` (`userID`,`roleID`)
	) ENGINE=MyIsam DEFAULT CHARSET=utf8;";


$sql['DefaultRoles']="INSERT INTO `acl_roles` VALUES (00000000000000000001,'System Administrator','The administrator of system '),(00000000000000000002,'Authenticated Users','User logged'),(00000000000000000003,'Anonymous User','Common User');";
$sql['AssignAdminRole']="INSERT INTO `acl_user_roles` VALUES (1,1,'".date ("Y-m-d H:i:s")."');";

/*$sql['app']="CREATE TABLE IF NOT EXISTS `app` (
		`ID` int(11) unsigned zerofill NOT NULL auto_increment,
		`restore` datetime NOT NULL,PRIMARY KEY  (`ID`)
	) ENGINE=MyIsam DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;";*/
