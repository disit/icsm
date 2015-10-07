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

class sm_ViewElement extends sm_Widget
{
	protected $model;
	protected $data;
	protected $type;
	
	
	/**
	 * Current op. Can be used by modules to determine
	 * what api call to do
	 	* @var string
	 */
	public $op;
	
	
	function __construct($model=null)
	{
		parent::__construct();
		$this->model=$model;
		
		
	}
	
	/**
	 * 
	 * @param string $op
	 */
	
	function setOp($op=null) //,$args=null)
	{
		$this->op=$op;
	
	}
	
	/**
	 * 
	 * @return string
	 */
	
	function getOp() //,$args=null)
	{
		return $this->op;
	
	}
	
	function setModel($m)
	{
		$this->model = $m;
	}
		
	function getModel()
	{
		return $this->model;
	}
	
	function setData($data){
		$this->data=$data;
	}
	
	function getData(){
		return $this->data;
	}
	
	function setType($data){
		$this->type=$data;
	}
	
	function getType(){
		return $this->type;
	}
	
}