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

define('LIVESTATUSURL','http://localhost/live/live.php');
define('LIVESTATUSUSER','nagiosadmin');
define('LIVESTATUSPWD','password');


class SM_LiveStatusClient implements sm_Module
{
	private $restClient=null;
	private $url;
	private $pwd;
	private $user;
	
	//Query components
	private $filters;
	private $columns;
	private $stats;
	private $limit;
	private $table;
	private $currQuery;
	private $logicalOp;
	
	function __construct()
	{
		$this->restClient=new SM_RestClient();
		$this->url=sm_Config::get('LIVESTATUSURL',LIVESTATUSURL);
		$this->user=sm_Config::get('LIVESTATUSUSER',LIVESTATUSUSER);
		$this->pwd=sm_Config::get('LIVESTATUSPWD',LIVESTATUSPWD); 
		$this->filters=array();
		$this->columns=array();
		$this->stats=array();
		$this->limit=-1;
		$this->table="";

	}
	
	public function setTable($table)
	{
		$this->table=$table;
	}
	
	public function setFilters($filters,$logicalOp=null)
	{
		$this->filters[]=array('query'=>$filters,'logical'=>$logicalOp);
	}
	
	public function setColumns($columns)
	{
		$this->columns=$columns;
	}
	
	public function setStats($stat)
	{
		$this->stats=$stat;
	}
	
	public function setLimit($limit)
	{
		$this->limit=$limit;
	}
	
	protected function validateDataQuery(){
		return true;
	}
	
	protected function createQuery(){
		
		$this->currQuery="";
		$query=array();
		if($this->validateDataQuery())
		{	
			$query[]="GET ".$this->table;
			
			foreach($this->filters as $k=>$filter)
			{
				$count=0;
				foreach($filter['query'] as $i=>$f)
				{
					$op = '=';
					$regx='/(^[~=<>!]+)\s*(.+)/';
					
					if(is_array($f))
					{
						foreach($f as $v)
						{
							if(preg_match($regx, $v,$matches))
							{
								$op=$matches[1];
								$v=$matches[2];
							}
							$query[]="Filter: ".$i." ".$op." ".$v;
							$count++;
						}
					}
					else
					{
						if(preg_match($regx, $f,$matches))
						{
							$op=$matches[1];
							$f=$matches[2];
						}
						$query[]="Filter: ".$i." ".$op." ".$f;
						$count++;
					}
				}
				if(isset($filter['logical']))
				{
					$query[]=$filter['logical'].":".$count;
				}
			}
			if(count($this->columns)>0)
				$query[]="Columns: ".implode(" ",$this->columns);
			foreach($this->stats as $i=>$d)
				$query[]="Stats: ".$d[0]." = ".$d[1];
			if($this->limit>0)
				$query[]="Limit: ".$this->limit;
			$this->currQuery=implode("\\\\n",$query)."\\\\n";
		}
		return $this->currQuery;
	}
	
	
	
	function execute(){
		$this->restClient->setUrl($this->url);
		$this->restClient->setCredentials($this->user,$this->pwd);
		$args['q']=$this->createQuery(); sm_Logger::write($args['q']);
		$this->restClient->setParameters($args);
		$this->restClient->setMethod("GET");
		$this->restClient->execute();
		$s = $this->restClient->getResponse();
		return $s;
	}
	
	static function install($db)
	{
		sm_Config::set('LIVESTATUSURL',array('value'=>LIVESTATUSURL,"description"=>'LIVESTATUS Web Url'));
		sm_Config::set('LIVESTATUSUSER',array('value'=>LIVESTATUSUSER,"description"=>'LIVESTATUS Web Admin User'));
		sm_Config::set('LIVESTATUSPWD',array('value'=>LIVESTATUSPWD,"description"=>'LIVESTATUS Web Admin Password'));
	}
	
	static function uninstall($db)
	{
		sm_Config::delete('LIVESTATUSURL');
		sm_Config::delete('LIVESTATUSUSER');
		sm_Config::delete('LIVESTATUSPWD');
	}
	
	static function isAlive($timeout=5){
		$old=sm_Logger::$debug;
		sm_Logger::$debug=false;
		//$h=parse_url(sm_Config::get('NAGIOSCOREURL',NAGIOSCOREURL));
		$url = sm_Config::get('LIVESTATUSURL',"");
		$ret=true;
		if($url!="")
		{
			$h=parse_url($url);
			$fp=@fsockopen($h['host'],80,$errno,$errstr,$timeout);
			$ret=$fp===false;
			if(is_resource($fp))
				@fclose($fp);
		}
		sm_Logger::$debug=$old;
		return !$ret;
	}
}