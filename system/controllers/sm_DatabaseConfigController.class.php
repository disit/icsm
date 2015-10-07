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

class sm_DatabaseConfigController extends sm_ControllerElement
{
	protected $model;
	protected $view;

	function __construct()
	{
		$this->model = sm_Config::instance();
	}


	/**
	 * Gets the config/system
	 *
	 * @url GET /config/database
	 */
	function index()
	{		
		include 'system/config.inc.php';
		$sys['DBHOST']=sm_Config::get("DBHOST",$dbHost);$dbHost;
		$sys['DBUSER']=sm_Config::get("DBUSER",$dbUser);$dbUser;
		$sys['DBPWD']=sm_Config::get("DBPWD",$dbPwd);$dbPwd;
		$data['MySql'][]=array('name'=>'DBHOST','value'=>$sys['DBHOST'],"description"=>"Database host address");
		$data['MySql'][]=array('name'=>'DBUSER','value'=>$sys['DBUSER'],"description"=>"Database User");
		$data['MySql'][]=array('name'=>'DBPWD','value'=>$sys['DBPWD'],"description"=>"Database password");
		$this->view = new sm_DatabaseConfigView();
		$this->view->setModel($data);
		$this->view->setType("database");

	}



}
