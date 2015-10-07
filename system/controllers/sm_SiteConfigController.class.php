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

class sm_SiteConfigController extends sm_ControllerElement
{
	protected $model;
	protected $view;

	function __construct()
	{
		$this->model = sm_Config::instance();
	}


	/**
	 * Gets the config/site
	 *
	 * @url GET /config/site
	 */
	function index()
	{
		$data=array();
		$options=array();
		$menuItems=sm_MenuManager::instance()->getMenuItems();
		
		foreach ($menuItems as $k=>$v)
		{
			if($v->getpath()!="#" && $v->getpath()!="")
			{
				$name = $v->gettitle();
				$paths = $v->breadcrumbs();
				if($paths!="")
					$name=$paths."/".$name;
				$options[$v->getpath()]=$name;
			}
		}
		
		$data['Site'][]=array('name'=>'HOMEPAGE','value'=>sm_Config::get("HOMEPAGE",""),'options'=>$options,"description"=>"Set the path for Homepage");
		$data['Site'][]=array('name'=>'SITE_TITLE','value'=>sm_Config::get("SITE_TITLE",""),"description"=>"Site Main Title");


		$this->view = new sm_SiteConfigView();
		$this->view->setModel($data);
		$this->view->setType("site");

	}

	/**
	 * Post the config/system
	 *
	 * @url POST /config/site
	 * 
	 */
	function post_index($data)
	{
		if(isset($_POST['HOMEPAGE']))
		{
			sm_Config::set("HOMEPAGE",array('value'=>$_POST['HOMEPAGE']));
			sm_set_message("Homepage Settings successfully saved!");
		}
		
		if(isset($_POST['SITE_TITLE']))
		{
			sm_Config::set("SITE_TITLE",array('value'=>$_POST['SITE_TITLE'],"description"=>"Site Main Title"));
			sm_set_message("Site Main Title successfully saved!");
		}
	}



}
