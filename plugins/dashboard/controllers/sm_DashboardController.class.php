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

class sm_DashboardController extends sm_ControllerElement
{
	protected $model;
	protected $view;
	
	function __construct()
	{
		$this->model = new sm_DashboardManager();
	}
	
	
	/**
	 * @desc Gets the dashboard page
	 *
	 * @url GET /dashboard
	 * @url GET /dashboard/:type
	 * 
	 * @access 
	 */
	function dashboard($type="system")
	{
		$data=array();
		$data['boards']=$this->model->getBoards(array("view_name"=>$type));
		$data['refreshurl']="dashboard/".$type;		
		$data['title']=ucfirst($type);
		$this->view=new sm_DashboardView($data);
		$this->view->setOp("view");
		$this->view->setType($type);
	}
	
	
	/**
	 * @desc Gets a dashboard panel
	 *
	 * @url GET /dashboard/panel/:type
	 * 
	 * @callback
	 *
	 */
	function dashboard_panel($type="system")
	{
		$data=array();
		$data['boards']=$this->model->getBoards(array("view_name"=>$type));
		$this->view=new sm_DashboardView($data);
		$this->view->setOp("panel");
		$this->view->setType($type);
	}
	
	/**
	 * @desc Gets the dashboard configuration page
	 *
	 * @url GET /dashboard/config
	 *
	 * @access
	 */
	function dashboard_config()
	{
		$data=array();
		
		$this->view=new sm_DashboardView($data);
		$this->view->setOp("config");
		$this->view->setType($type);
	}
	
	
	function dashboard_add()
	{
		$this->model->add($data);
	}
	
	function dashboard_remove()
	{
		$this->model->remove($data);
		
	}
	
	function dashboard_enable()
	{
		$this->model->enable($data);
	}
}