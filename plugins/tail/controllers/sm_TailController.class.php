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

class sm_TailController extends sm_ControllerElement
{
	protected $model;
	protected $view;
	
	function __construct()
	{
		$this->model = new sm_Tail();
	}
	
	
	/**
	 * Gets the tail page
	 *
	 * @url GET /tail
	 * 
	 * @access 
	 */
	function tail()
	{
		$file=sm_Logger::$logfolder.sm_Logger::$fileLog;
		$data=array();
		$data['file']=sm_Logger::$fileLog;
		$data['refreshUrl']="tail/refresh/?file=".$file;
		
		$this->view=new sm_TailView($data);
		$this->view->setOp("view");
	}
	
	
	/**
	 * Gets the tail refresh
	 *
	 * @url GET tail/refresh
	 * 
	 * @callback
	 *
	 */
	function tail_refresh()
	{
		$file=$_GET['file'];
		$data=$this->model->refresh($file);
		$this->view=new sm_TailView($data);
		$this->view->setOp("refresh");
	}
	
	 
	
}