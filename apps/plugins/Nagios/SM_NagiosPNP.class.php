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

define('PNP4NAGIOSURL','http://localhost/pnp4nagios/');
define('PNP4NAGIOSUSER','nagiosadmin');
define('PNP4NAGIOSPWD','password');

class SM_NagiosPNP implements sm_Module
{
	private $restClient=null;
	private $url;
	private $pwd;
	private $user;
	function __construct()
	{
		$this->restClient=new SM_RestClient();
		$this->url=sm_Config::get('PNP4NAGIOSURL',PNP4NAGIOSURL);
		$this->user=sm_Config::get('PNP4NAGIOSUSER',PNP4NAGIOSUSER);
		$this->pwd=sm_Config::get('PNP4NAGIOSPWD',PNP4NAGIOSPWD);
	}

	static function install($db)
	{
		sm_Config::set('PNP4NAGIOSURL',array('value'=>PNP4NAGIOSURL,"description"=>'PNP4Nagios Web Url'));
		sm_Config::set('PNP4NAGIOSUSER',array('value'=>PNP4NAGIOSUSER,"description"=>'PNP4Nagios Web Admin User'));
		sm_Config::set('PNP4NAGIOSPWD',array('value'=>PNP4NAGIOSPWD,"description"=>'PNP4Nagios Web Admin Password'));
	}

	static function uninstall($db)
	{
		sm_Config::delete('PNP4NAGIOSURL');
		sm_Config::delete('PNP4NAGIOSUSER');
		sm_Config::delete('PNP4NAGIOSPWD');
	}

	public function getGraph($args=null){
		if(isset($args["type"]) && $args['type']=="xml")
		{
			$this->restClient->setUrl($this->url."xport/xml/");
		}
		else if(isset($args["type"]) && $args['type']=="popup")
		{
			$this->restClient->setUrl($this->url."popup/");
		}
		else if(isset($args["type"]) && $args['type']=="info")
		{
			$this->restClient->setUrl($this->url."xml/");
		}
		else 
			$this->restClient->setUrl($this->url."image/");
		$this->restClient->setCredentials($this->user,$this->pwd);
		if($args)
		{
			if(!isset($args["srv"]))
				$args["srv"]="_HOST_";
			if(!isset($args['type']) || (isset($args['type']) && $args['type']!="info"))
			{
				$t=time();
				if(!isset($args["start"]))
					$args["start"]=$t-(24*3600);
				if(!isset($args["end"]))
					$args["end"]=$t;
			}
			
			if(isset($args["srv"]) && strstr($args["srv"],":")!==false)
			{
				$srvs=explode(":",$args["srv"]);
				$args["srv"]=$srvs[0];
				if(!isset($args['type']) || (isset($args['type']) && $args['type']!="info"))
					$args["source"]=$srvs[1];
			}
			$this->restClient->setParameters($args);
		}
		$this->restClient->setMethod("GET");
		$this->restClient->execute();
		$s = $this->restClient->getResponse();
		$type = $this->restClient->getResponseContentType();
		$s=substr($s, 1);
		if(isset($args["type"]) && ($args['type']=="xml" || $args['type']=="info"))
			return $s;	
		if($type=="image/png")
		{
			$image = @imagecreatefromstring($s);
			return $image;
		}
		return imagecreatefrompng(__DIR__."/img/notavailable.png" );
	}
}