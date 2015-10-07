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

define('SMMONITORVERSION',"1.0");
define('MONITORINSTANCETABLE','monitors'); 
define('MONITORHOSTTABLE','monitor_host');
define('SMMONITORTOOL',"");

/**** Monitor status values ***/
define('MONITOR_READY',-1);
define('MONITOR_WAITING',0);
define('MONITOR_MONITORING',1);
define('MONITOR_STOPPED',2);
define('MONITOR_FAILED',3);
define('MONITOR_PROCESSING',4);
define('MONITOR_DELETING',5);

class SM_Monitor implements sm_Module
{
	protected $db;
	function __construct()
	{
		$this->db=sm_Database::getInstance();
	}
	
	public function getInfo()
	{
		$info['name']="SM Monitor for Nagios Api";
		$info['version']=SMMONITORVERSION;
		return $info;
	}
	
	public function getData($data)
	{
		//return $this->meters();
		$r=$this->db->select(MONITORINSTANCETABLE,$data);
		if($r && isset($r[0]))
			return $r[0];
		return null;
	}
	
	static public function install($db)
	{
		/********* Configuration vars ***********************/
		if(!sm_Config::get('SMMONITORTOOL',null))
			sm_Config::set('SMMONITORTOOL',array('value'=>SMMONITORTOOL,"description"=>'The Monitor Tool used for monitoring'));
		
		/****** ACL Section *******************/
		sm_Logger::write("Installing Permissions: Monitor::Edit");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Monitor Edit','permKey'=>'Monitor::Edit'));
		sm_Logger::write("Installing Permissions: Monitor::View");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Monitor View','permKey'=>'Monitor::View'));
		sm_Logger::write("Permissions Installed");
		
		/******** Database section **************************/
		$sql="CREATE TABLE IF NOT EXISTS `".MONITORINSTANCETABLE."` (
		`mid` varchar(128) NOT NULL,
		`iid` varchar(128) NOT NULL,
		`description` varchar(1024) NOT NULL,
		`data` longtext NOT NULL,
  		`class` varchar(45) NOT NULL,
		`plugin` varchar(45) NOT NULL,
		`status` int(11) DEFAULT '0',
		`lastupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`errors` longtext NOT NULL,
  		PRIMARY KEY (`mid`),
  		KEY `iid` (`iid`)
		)
		ENGINE=MyISAM CHARSET=utf8;";
		
		$sql2="CREATE TABLE IF NOT EXISTS `".MONITORHOSTTABLE."` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `mid` varchar(128) NOT NULL,
		  `ref` varchar(128) NOT NULL,
		  `status` varchar(45) NOT NULL,
		  `type` varchar(45) NOT NULL,
		  `last_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (`id`),
		  KEY `ref` (`ref`)
		) ENGINE=MyISAM CHARSET=utf8;";
		
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".MONITORINSTANCETABLE." table");
			
		}
		$result=$db->query($sql2);
		if($result)
		{
			sm_Logger::write("Installed ".MONITORHOSTTABLE." table");
				
		}
		
		return false;
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete('SMMONITORTOOL');
		
		$sql="DROP TABLE `".MONITORINSTANCETABLE."`;";
		$sql2="DROP TABLE `".MONITORHOSTTABLE."`;";
		$result=$db->query($sql) || $db->query($sql2);
		if($result)
			return true;
		return false;
	}
	
	static function registerTool($class)
	{
		sm_Config::set('SMMONITORTOOL',array('value'=>$class));
	}
	
	static function unregisterTool($class)
	{
		sm_Config::set('SMMONITORTOOL',array('value'=>""));
	}
	
	public function exists($mid)
	{
		$result=$this->db->select(MONITORINSTANCETABLE,array('mid'=>$mid));
		if($result && isset($result[0]))
			return true;
		return false;
	}
	
	public function getMonitorId($iid)
	{
		$r=$this->db->select(MONITORINSTANCETABLE,array('iid'=>$iid),array("mid"));
		if($r && isset($r[0]))
			return $r[0]['mid'];
		return $this->getInternalIdbyDescription($iid);
	}
	
	public function getInternalId($mid)
	{
		$r=$this->db->select(MONITORINSTANCETABLE,array('mid'=>$mid),array("iid"));
		if($r && isset($r[0]))
			return $r[0]['iid'];
		return null;
	}
	
	public function getMonitorIdbyDescription($desc)
	{
		$r=$this->db->select(MONITORINSTANCETABLE,array('description'=>$desc),array("mid"));
		if($r && isset($r[0]['mid']))
		{
			//sm_Logger::write($r[0]);
			return $r[0]['mid'];
		}
	}
	
	public function getInternalIdbyDescription($desc)
	{
		$r=$this->db->select(MONITORINSTANCETABLE,array('description'=>$desc),array("iid"));
		if($r && isset($r[0]['iid']))
		{
			//sm_Logger::write($r[0]);
			return $r[0]['iid'];
		}
		$r=$this->db->select(MONITORINSTANCETABLE,array('data'=>$desc),array("iid"));
		if($r && isset($r[0]['iid']))
		{
			//sm_Logger::write($r[0]);
			return $r[0]['iid'];
		}
		return null;
	}
	
	public function queue($mid)
	{
		$data=array("status"=>0,"errors"=>"");
		return $this->save($data,array("mid"=>$mid));
	}
	
	public function processing($mid)
	{
		$data=array("status"=>4);
		return $this->save($data,array("mid"=>$mid));
	}
	
	public function save($data=null,$where = null){
		return $this->db->save(MONITORINSTANCETABLE,$data,$where);
	}
	
	public function update($cid, $data=null,$where = null){
		if(isset($cid))
		{
			$r = $this->remove($cid);
			if(isset($r['result']))
			{
				if($r['result'])
				{
					$this->save($data,$where);
					$this->queue($data['mid']);
					return;
				}
				
				$data['status']=3;
				$data["errors"]=isset($r['result']['error'])?implode(" ",$r['result']['error']):"Error when removing monitor data";
				
				
			}
		}	
		else {
			$data['status']=3;
			$data["errors"]=isset($r['result']['error'])?implode(" ",$r['result']['error']):"Error when removing monitor data";
		}
		return $this->save($data,$where);
	}
	
	public function delete($mid){
		$this->db->delete(MONITORHOSTTABLE,array("mid"=>$mid));
		return $this->db->delete(MONITORINSTANCETABLE,array("mid"=>$mid));
	}
	
	
	
	public function getAllCount($where=array())
	{
		$whereCond="";
		if(!empty($where))
			$whereCond=$this->db->buildWhereClause(MONITORINSTANCETABLE, $where);
		$r=$this->db->query("SELECT COUNT(*) as count from `".MONITORINSTANCETABLE."` ".$whereCond);
		return $r[0]['count'];
	}
	
	public function getAll($limit=null, $where=array(),$fields=array())
	{
		
		if(isset($limit))
			$limit=str_replace("LIMIT", "", $limit);
		$r=$this->db->select(MONITORINSTANCETABLE, $where,$fields,$limit,array("mid"),"DESC");
	
		return $r;
	
	}
		
	function invokeOnMonitorTool($args,$obj,$function)
	{
		if(is_array($args))
			return	call_user_func_array(array($obj, $function), $args);
		else
			return	call_user_func(array($obj, $function),$args);
	}
	
	function toolInstance(){
		$tool=sm_Config::get('SMMONITORTOOL',"");
		$toolInstance=null;
		if($tool!="" && class_exists($tool))
		{
			$toolInstance=new $tool();
		}
		return $toolInstance;
	}
	
	/*function meters($host_name,$ip)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host_name,$ip),$monitorTool,__FUNCTION__);
		}
	}*/
	function overallStatus($name,$selection,$type="")
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($name,$selection,$type),$monitorTool,__FUNCTION__);
		}
	}
	
	function meters($name,$selection,$type,$metric=null)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($name,$selection,$type,$metric),$monitorTool,__FUNCTION__);
		}
	}

	function meter($name,$selection,$type,$metric=null)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($name,$selection,$type,$metric),$monitorTool,__FUNCTION__);
		}
	}
	
	function controls($name,$selection,$type)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($name,$selection,$type),$monitorTool,__FUNCTION__);
		}
	}
	
	function graph($args=null)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($args),$monitorTool,__FUNCTION__);
		}
	}
	
	function graph_data($host_name,$ip,$metric)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host_name,$ip,$metric),$monitorTool,__FUNCTION__);
		}
	}
	
	function graph_list_old($host_name,$ip)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host_name,$ip),$monitorTool,__FUNCTION__);
		}
	}
	
	function graph_list($name,$selection,$type)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($name,$selection,$type),$monitorTool,__FUNCTION__);
		}
	}
	/**
	 * 
	 * @param unknown $metric
	 * @return mixed
	 */
	function metric_stats($metric,$type="host")
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($metric,$type),$monitorTool,__FUNCTION__);
		}
	}
	
	/** 
	 * @desc Query Hosts
	 * @param string $type
	 * @param unknown $where
	 * @param unknown $fields
	 * @param number $howmany
	 * @param number $page
	 * @return mixed
	 */
	
	function hosts($type='host',$where=array(),$fields=array(),$howmany=10,$page=1)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($type,$where,$fields,$howmany,$page),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Query Servicess
	 * @param string $type
	 * @param unknown $where
	 * @param unknown $fields
	 * @param number $howmany
	 * @param number $page
	 * @return mixed
	 */
	
	function services($type='host',$where=array(),$fields=array(),$howmany=10,$page=1)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($type,$where,$fields,$howmany,$page),$monitorTool,__FUNCTION__);
		}
	}
	
	/** 
	 * @desc Count Hosts
	 * @param string $type
	 * @param unknown $where
	 * @return mixed
	 */
	function count_hosts($type='host',$where=array())
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($type,$where),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Query Hosts status
	 * @param string $type
	 * @param array $where
	 * @return mixed
	 */
	function hosts_status($type='host',$where=array())
	{
		$monitorTool=$this->toolInstance();
		$result = null;
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			$result = $this->invokeOnMonitorTool(array($type,$where),$monitorTool,__FUNCTION__);
		}
		if(!$result)
			$result = $this->cached_host_status($where);
		return $result;
	}
	
	/**
	 * @desc Query Host ping data
	 * @param string $type
	 * @param array $where
	 * @return mixed
	 */
	function host_ping($type='host',$where=array())
	{
		$monitorTool=$this->toolInstance();
		$result = null;
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			$result = $this->invokeOnMonitorTool(array($type,$where),$monitorTool,__FUNCTION__);
		}
		if(!$result)
			$result = $this->cached_host_status($where);
		return $result;
	}
	
	
	
	/**
	 * @desc Query Check status 
	 * @param array $where
	 * @return mixed
	 */
	function services_status($where=array())
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($where),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Applications Check status 
	 * @param array $where
	 * @return mixed
	 */
	function application_services_status($where=array())
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($where),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Last Events Query  
	 * @param unknown $limit
	 * @param unknown $time
	 * @return mixed
	 */
	function all_last_events($limit,$time)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($limit,$time),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Last Events Query
	 * @param unknown $limit
	 * @param unknown $time
	 * @return mixed
	 */
	function last_events($type,$where=array(),$limit,$time)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($type,$where,$limit,$time),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * 
	 * @param unknown $where
	 * @return mixed
	 */
	
	function checks_list_count($where=array(),$state=null){
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($where,$state),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * 
	 * @return mixed
	 */
	
	function getChecksStateLabel(){
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array(),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * 
	 * @param unknown $where
	 * @param number $state
	 * @param number $howmany
	 * @param number $page
	 * @return mixed
	 */
	
	function checks_list($where=array(),$state=0,$howmany=10,$page=1){
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($where,$state,$howmany,$page),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Get list of availble checks in the monitor tool
	 * @return mixed
	 */
	
	function checksDefintions($where,$fields)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($where,$fields),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 * @desc Get list of availble metrics in the monitor tool
	 * @return mixed
	 */
	
	function checksMetrics($status=null)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($status),$monitorTool,__FUNCTION__);
		}
	}
	
	/**
	 *  Stop/Pause Monitoring 
	 * 
	 * @param unknown $host
	 * @param unknown $ip
	 * @param unknown $check
	 * @return mixed
	 */
	function stop($host,$ip,$check)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host,$ip,$check),$monitorTool,__FUNCTION__);
		}
	}
	
	/* Start/Resume Monitoring */
	function start($host,$ip,$check)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host,$ip,$check),$monitorTool,__FUNCTION__);
		}
	}
	
	/* Reschedule an immediate check */
	function rescheduleCheck($host,$ip,$check)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($host,$ip,$check),$monitorTool,__FUNCTION__);
		}
	}
	
	/* Insert Configuration in Monitoring Tool*/
	function insert($id)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($id),$monitorTool,__FUNCTION__);
		}
	}
	
	/* Delete Configuration in Monitoring Tool*/
	function remove($id)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($id),$monitorTool,__FUNCTION__);
		}
	}
	
	/* Get Filters for searching in Monitoring Tool*/
	function getFilters($SM_obj)
	{
		$monitorTool=$this->toolInstance();
		if(isset($monitorTool) && method_exists($monitorTool,__FUNCTION__))
		{
			return $this->invokeOnMonitorTool(array($SM_obj),$monitorTool,__FUNCTION__);
		}
		return null;
	}
	
	function deleteConfigurationMonitor($id){
		if(!preg_match("/SM:/",$id))
			$mid=$this->getMonitorId($id);
		else
		{
			$mid=$id;
		}
		if(!$this->exists($mid))
		{
			sm_Logger::write($mid." not exists!");
		}
			
		sm_Logger::write("Deleting configuration ".$id." from monitor tool");
		$result=$this->remove($id);
		sm_Logger::write("Deleting monitor instance for ".$mid);
		$result&=$this->delete($mid);
		return $result;
	}

	function handleHostEvent($hostdata)
	{
		$data=array();
		$data['ref'] = $hostdata->ref;
		$data['status'] = $hostdata->status;
		
		$midQuery = sprintf("SELECT mid, type FROM ".MONITORINSTANCETABLE." join host on iid = cid  where host.description='%s';",$data['ref']);
		
		$row=$this->db->selectRow(MONITORHOSTTABLE,array('ref'=>$data['ref']));
		$where=isset($row['id'])?array("id"=>$row['id']):null;
		$res = $this->db->query($midQuery); 
		if($res)
		{
			$data['mid']=$res[0]['mid'];
			$data['type']=$res[0]['type'];
			if(isset($row['status']) && $row['status']!=$data['status'])
			{
				$data['last_changed']=date('Y-m-d H:i:s',time());
			}
			
			$this->db->save(MONITORHOSTTABLE, $data,$where);
		}
		
	}
	
	function refreshHostStatus()
	{
		$start=0;
		$howmany=100;
		$count=0;
		while($row = $this->db->select("host",null,null,$start.",".$howmany))
		{
			foreach ($row as $r)
			{
				$hostdata=array();
				$status = $this->hosts_status($r['type'],array("description"=>$r['description']));
				$hostdata['ref']=$r['description'];
				$hostdata['status']="UNKNOWN";
				if(isset($status))
				{
					if($status['Up'])
						$hostdata['status']="UP";
					else if($status['Down'])
						$hostdata['status']="DOWN";
				}
				
				$this->handleHostEvent((Object) $hostdata);
				$count++;
			}
			$start+=$howmany;
		}	
		return $count;
	}
	
	function update_host($mid,SM_Configuration $conf)
	{
		$this->save_host($mid, $conf);
	}
	
	function save_host($mid,SM_Configuration $conf)
	{
		$host_type=array("host","vmhost");
		$hosts=$conf->getConfiguration()->get('hosts');
		$this->db->delete(MONITORHOSTTABLE, array("mid"=>$mid));
		foreach($hosts as $host)
		{
			if(!in_array($host->gettype(),$host_type))
				continue;
			$data=array();
			$data['ref'] = $host->getdescription();
			$data['status'] = "UP";
			$data['mid']=$mid;
			$data['type']=$host->gettype();
			$this->db->save(MONITORHOSTTABLE, $data);
		}
		
	}
	
	function cached_host_status($where=array())
	{
		if($row = $this->db->select(MONITORHOSTTABLE,$where))
			return $row;
		return null;
		
	}
	
}