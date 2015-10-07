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

class SM_GraphManager implements sm_Module
{
	protected $monitor;
	function __construct()
	{
		$this->monitor = new SM_Monitor();
	}
	
	static function install($db)
	{
		return true;
	}
	
	static function uninstall($db)
	{
		return true;
	}
	
	public function getGraph($args=null){
		return $this->monitor->graph($args);
	}
	
	public function doGraph($args=null){
		return $this->monitor->graph($args);
	}
}