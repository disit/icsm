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

define('SMTAXONOMYTABALE','SM_Tax');

class SM_Taxonomy implements sm_Module
{

	static function dbTableName()
	{
		return SMTAXONOMYTABALE;
	}
	
	
	static function install($db)
	{
		
		$db = sm_Database::getInstance();
		$sql="CREATE TABLE IF NOT EXISTS `".SMTAXONOMYTABALE."` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`alias` varchar(512) NOT NULL DEFAULT '',
			`uri` varchar(512) NOT NULL DEFAULT '',
			`parent` int(10) NOT NULL DEFAULT '0',
			`added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `name` (`name`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".SMTAXONOMYTABALE." table");
			sm_set_message("'".SMTAXONOMYTABALE."' table installed successfully");
				
		}
		else
		{
			sm_Logger::write("Not Installed ".SMTAXONOMYTABALE." table");
			sm_set_error("'".SMTAXONOMYTABALE."' table installation error");
		}
	
	
	
	}
	
	static function insert($data=array()){
		if(count($data)>0)
		{
			$db = sm_Database::getInstance();
			$result = $db->selectRow(SMTAXONOMYTABALE,array("name"=>$data['name']));
			if($result)
			{
				$db->save(SMTAXONOMYTABALE,$data,array("name"=>$data['name']));
				return $result['id'];
			}
			else
			{
				$db->save(SMTAXONOMYTABALE,$data);
				return $db->getLastInsertedId();
			}
		}
		return null;
	}
	
	static function uninstall($db)
	{
		
		$sql="DROP TABLE ".SMTAXONOMYTABALE;
		$result=$db->query($sql);
		if($result)
			sm_set_message(SMTAXONOMYTABALE." DB Table removed successfully!");
		else
			sm_set_error($db->getError());
	}
	
}