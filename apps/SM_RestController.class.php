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

class SM_RestController implements sm_Module //extends sm_ControllerElement
{
	protected $server;
	
	public function setServer($server)
	{
		$this->server=$server;
	}
	
	public function getServer()
	{
		return $this->server;
	}
	

	public function authorize()
	{
		$username = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:null;
		$password =  isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:null;
		// validate input and log the user in
		$user=sm_Config::get('USERCONFIGCTRL',USERCONFIGCTRL);
		$pwd=sm_Config::get('PWDCONFIGCTRL',PWDCONFIGCTRL);
		if($username == $user && $password==$pwd)
		{
			return true;
		}

		return false;
	}
	
	static public function install($db)
	{
		return true;
	}
	
	static public function uninstall($db)
	{
		return true;
	}
}
