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

class sm_SystemConfigController extends sm_ControllerElement 
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
	 * @url GET /config/system
	 */
	function index()
	{
		include 'system/config.inc.php';
		$sys['BASEDIR']=sm_Config::get("BASEDIR",$baseDir);
		$sys['BASEURL']=sm_Config::get("BASEURL",$baseUrl);
	//	$sys['SITE_TITLE']=sm_Config::get("SITE_TITLE","");
		$data['Server'][]=array('name'=>'BASEDIR','value'=>$sys['BASEDIR'],"description"=>"Server Base Folder");
		$data['Server'][]=array('name'=>'BASEURL','value'=>$sys['BASEURL'],"description"=>"Server Base Url");
	//	$data['Server'][]=array('name'=>'SITE_TITLE','value'=>$sys['SITE_TITLE'],"description"=>"Site Main Title");
		
		
		$this->view = new sm_SystemConfigView();
		$this->view->setModel($data);
		$this->view->setType("system");
		
	}
	
	/**
	 * Post the config/system
	 *
	 * @url POST /config/system
	 */
	function post_index($data)
	{
		if(isset($_POST['BASEDIR']))
		{
			sm_Config::set("BASEDIR",array('value'=>$_POST['BASEDIR'],"description"=>"Server Base Folder"));
			sm_set_message("Server Base Folder successfully saved!");
		}
		if(isset($_POST['BASEURL']))
		{
			sm_Config::set("BASEURL",array('value'=>$_POST['BASEURL'],"description"=>"Server Base Url"));
			sm_set_message("Server Base Url successfully saved!");
		}
	/*	if(isset($_POST['SITE_TITLE']))
		{
			sm_Config::set("SITE_TITLE",array('value'=>$_POST['SITE_TITLE'],"description"=>"Site Main Title"));
			sm_set_message("Site Main Title successfully saved!");
		}*/
	}
	
	
	
}
