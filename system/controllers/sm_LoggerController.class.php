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

class sm_LoggerController extends sm_ControllerElement
{
	protected $model;
	protected $view;
	
	function __construct()
	{
		$this->model = sm_Logger::instance();
	}
	
		
	/**
	 * Gets the log output table
	 *
	 * @url GET /log/output
	 */
	function index()
	{
		$timestamp=null;
		$timestamp_to=null;
		
		$where=array();	
		if(isset($_SESSION['log/output']['timestamp']) && $_SESSION['log/output']['timestamp'])
		{
			$timestamp=$_SESSION['log/output']['timestamp'];
			$where[]="timestamp >= ".strtotime($timestamp)*1000;
		}
		if(isset($_SESSION['log/output']['timestamp_to']) && $_SESSION['log/output']['timestamp_to'])
		{
			$timestamp_to=$_SESSION['log/output']['timestamp_to'];
			$where[]="timestamp <= ".strtotime($timestamp_to)*1000;
		}
		if(count($where))
			$where = "WHERE ".implode(" AND ",$where);
		$pager = new sm_Pager("log/output", 'p');
		$_totalRows=$this->model->getAllCount($where);
		$pager->set_total($_totalRows);
		$data['records'] = $this->model->getAll( $pager->get_limit(),$where );
		//create the nav menu
		$data['pageLink'] = "log/output";
		$data['pager']=$pager;
		$data['timestamp']=$timestamp?$timestamp:"";
		$data['timestamp_to']=$timestamp_to?$timestamp_to:"";
		$this->view = new sm_LoggerView($data);
	}	
}
