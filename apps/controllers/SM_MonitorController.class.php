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

class SM_MonitorController extends SM_RestController
{
	protected $monitor;
	protected $db;

	public function __construct()
	{

		$this->monitor = new SM_Monitor();
		$this->db=sm_Database::getInstance();
	}

	/**
	 * @desc Gets the version of monitor
	 *
	 * @url GET monitor/version
	 *
	 */
	public function getInfo()
	{
		$info=$this->monitor->getInfo();
		$response['response']= new SM_Response('message','Monitor Info',$info);
		return $response; //$user; // serializes object into JSON
	}

	/**
	 * @desc Gets structured data of a monitor by id
	 *
	 * @url GET /monitor/data/:monitorid
	 * 
	 */
	public function getData($monitorid = null)
	{
		if(!isset($monitorid)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			/*$args=array(
					"host"=>"test-192.168.0.111",
					"start"=>$from,
					"end"=>$to,
					"srv"=>$metric,
			);*/
			
			$data=$this->monitor->getData(array("mid"=>$monitorid));
			if($data)
			{
				$response['response']= new SM_Response('message','Monitor',$data);
			}
			else
				throw new SM_RestException(400, "Invalid Parameters or Info not available");
		}

		return $response;
	}
	
	/**
	 * @desc Gets structured data for meters by a configuration contract id
	 *
	 * @url GET /monitor/meters/contract/:id
	 * @url GET /monitor/meters/contract/:id/:segment
	 * @url GET /monitor/meters/contract/:id/:segment/:sid
	 * @url GET /monitor/meters/contract/:id/:segment/:sid/:metric
	 *
	 */
	public function monitor_metersByContract($id,$segment="*",$sid=null,$metric='_HOST_')
	{
		$cid=$this->monitor->getInternalIdbyDescription($id);
		if($cid)
			return $this->monitor_meters($cid,$segment,$sid,$metric='_HOST_');
		else
			throw new SM_RestException(400, "Invalid Parameters or Info not available");
	}
	
	/**
	 * @desc Gets structured data for meters by a configuration id or monitor id or SM id
	 *
	 * @url GET /monitor/meters/:id/
	 * @url GET /monitor/meters/:id/:segment/
	 * @url GET /monitor/meters/:id/:segment/:sid
	 * @url GET /monitor/meters/:id/:segment/:sid/:metric
	 *
	 */
	public function monitor_meters($id,$segment="*",$sid=null,$metric='_HOST_')
	{
		
		if(preg_match("/SM:/",$id))
			$cid=$this->monitor->getInternalId($id);
		else 
			$cid=$this->monitor->getInternalIdbyDescription($id);
		if(!$cid)
		{
			if(is_numeric($id))
				$cid=$id;
			else
				throw new SM_RestException(400, "Invalid Parameters or Info not available");
		}
			
			
		$data=array();
		$data['time']=time();
		$data['id']=$id;
		$configuration=new SM_Configuration($cid);
		$segment_obj=$configuration->getSegment($segment,$sid);
		
		
		if($segment!="*")
		{
			$dataSegments=$segment_obj[$segment];
			$d=$this->_monitor_meters($dataSegments);
			$data=array_merge($data,$d);
		}
		else
		{
			$dataSegments=$segment_obj;
			foreach($dataSegments as $s=>$seg)
			{
				$d=$this->_monitor_meters($seg);
				$data=array_merge($data,$d);
			}
		}
		if($data)
		{
			$response['response']= new SM_Response('message','Monitor',$data);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters or Info not available");
		return $response;
	}
	
	function _monitor_meters($segments)
	{
		$data=array();
		foreach($segments as $s=>$seg)
		{		
			$filters=array();
			if(is_a($seg,"Host")) // && ($seg->gettype()=="host" || $seg->gettype()=="vmhost" || $seg->gettype()=="HLMhost"))
			{	
				$type="host";
				$name=$seg->getname();
				$filters=$this->monitor->getFilters($seg);
			}
			else if(is_a($seg,"Application"))
			{
				$type="application";
				$name=$seg->getdescription();
				$filters=$this->monitor->getFilters($seg);
			}
			
			foreach($filters as $f=>$v)
			{
				$selection=$f;
				/*	if($selected!="All" && $selection!=$selected)
				 continue;*/
				$metric = null;
				if(false && $seg->gettype()=="HLMhost")
					$metric = "!~ HLM";
				$meters= $this->monitor->meters($name,$selection,$type,$metric);
				//$data['time'] = $meters['time'];
				if(isset($data['meters']))
					$data['meters']['meter']=array_merge($data['meters']['meter'],$meters['meters']);
				else
					$data['meters']['meter']=$meters['meters'];
			}
			
		}
		return $data;
	}
	
	/**
	 * @desc Gets the picture of a graph
	 *
	 * @url GET /monitor/img/:hostname/:ip/:metric/:from/:to/
	 * @url GET /monitor/img/:hostname/:ip/:metric/:from/
	 * @url GET /monitor/img/:hostname/:ip/:metric/
	 *
	 */
	public function monitor_img($hostname = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		if(!isset($hostname) && !isset($ip)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			$args=array(
					"host"=>$hostname,
					"ip"=>$ip,
					"start"=>$from,
					"end"=>$to,
					"srv"=>urldecode($metric),
					"type"=>"img"
			);
				
			$graph = new SM_GraphManager();
			$image=$graph->getGraph($args);
			if($image)
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: 0");
				header('Content-Type: image/png');
				imagepng($image);
				imagedestroy($image);
	
			}
		}
		exit();
	}
	
	/**
	 * @desc Gets the xml representation for a picture of a graph
	 *
	 * @url GET /monitor/xml/:hostname/:ip/:metric/:from/:to/
	 * @url GET /monitor/xml/:hostname/:ip/:metric/:from/
	 * @url GET /monitor/xml/:hostname/:ip/:metric/
	 *
	 */
	public function monitor_img_xml($hostname = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		if(!isset($hostname) && !isset($ip)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			$args=array(
					"host"=>$hostname,
					"ip"=>$ip,
					"start"=>$from,
					"end"=>$to,
					"srv"=>urldecode($metric),
					"type"=>"xml"
			);
	
			$graph = new SM_GraphManager();
			$image=$graph->getGraph($args);
			if($image)
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: 0");
				header('Content-Type: application/xml');
				echo $image;
	
			}
		}
		exit();
	}
	

	/**
	 * @desc Gets the xml info a graph
	 *
	 * @url GET /monitor/info/:hostname/:ip/:metric/
	 *
	 */
	public function monitor_img_info($hostname = null, $ip=null, $metric="_HOST_")
	{
		if(!isset($hostname) && !isset($ip)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			$args=array(
					"host"=>$hostname,
					"ip"=>$ip,
					"srv"=>urldecode($metric),
					"type"=>"info"
			);
	
			$graph = new SM_GraphManager();
			$data=$graph->getGraph($args);
			if($data)
			{
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: 0");
				header('Content-Type: application/xml');
				echo $data;
	
			}
		}
		exit();
	}
	
	/**
	 * @desc Gets the list of check defined in the Monitor Tool (es: monitor/checks/definitions/config_name:service_description:display_name)
	 *
	 * @url GET /monitor/checks/definitions
	 * @url GET /monitor/checks/definitions/:fields
	 * @url GET /monitor/checks/definitions/:fields/:where
	 *
	 */
	public function monitor_available_checks($fields=null,$where=null)
	{
		$filter=array();
		if($fields)
		{
			$filter=explode(":",$fields);
		}
		$whereCond = array('hostgroup_name'=>1);
		if($where)
		{
			
			$whereCond=array_merge(array("hostgroup_name=1"),explode(":",$where));
			$whereCond=implode(" AND ",$whereCond);
			var_dump($whereCond);
		}
		$services =$this->monitor->checksDefintions($whereCond,$filter);
		if($services)
		{
			$data['total']=count($services);
			$data['services']['service']=$services;
			$response['response']= new SM_Response('message','Monitor',$data);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters or Info not available");
		return $response;
	}
	
	/**
	 * @desc Gets the list of metrics defined in the Monitor Tool
	 *
	 * @url GET /monitor/checks/metrics
	 * @url GET /monitor/checks/metrics/:status
	 *
	 */
	public function monitor_available_metrics($status=null)
	{
		if($status && $status!="active" && $status!="disabled")
			throw new SM_RestException(400, "Invalid Parameters or Status ".$status." not available");
				
		$metrics =$this->monitor->checksMetrics($status);
		if($metrics)
		{
			$data['total']=count($metrics);
			$data['metrics']['metric']=$metrics;
			$response['response']= new SM_Response('message','Monitor',$data);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters or Info not available");
		return $response;
	}
	
	/**
	 *@desc Gets an immediate reschedule check
	 *
	 * @url GET /monitor/reschedule/check/:id/:segment/:sid/:metric
	 *
	 *
	 */
	public function monitor_reschedule_check($id,$segment,$sid,$metric='_HOST_')
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$ip_address=$segment_obj[$segment]->getmonitor_ip_address();

		$host_name=$segment_obj[$segment]->getname();//.'@'.$ip_address;
		$monitor = new SM_Monitor();
		$data = $monitor->rescheduleCheck($host_name,$ip_address,$metric);
		$response['response']= new SM_Response('message','Monitor',$data);
		return $response;
	}
	
	/**
	 *@desc Calculate Host status check
	 *
	 * @url GET /monitor/refresh/host/status
	 *
	 *
	 */
	public function monitor_refresh_host_status()
	{
		$data = $this->monitor->refreshHostStatus();
		$response['response']= new SM_Response('message','Monitor',$data);
		return $response;
	}
	
	
	function onMonitorEvent(sm_Event &$event)
	{
		$eventData=$event->getData();
		if($eventData->type=="status" && ($eventData->segment->type=="host"))
		{
			//$this->monitor->handleHostEvent($eventData->segment);
			$this->monitor->refreshHostStatus();
		}
		else if($eventData->type=="status" && ($eventData->segment->type=="service"))
		{
			//$this->monitor->handleServiceEvent($eventData->segment);
		}
	}
	
	function onDeleteConfiguration(sm_Event &$event)
	{
		$monitor = new SM_Monitor();
		$conf = $event->getData();
		$id = $conf->getConfiguration()->getdescription();
		
		if(class_exists("sm_DashboardManager") && isset($id))
		{
			sm_Logger::write("Deleting Dashboard monitor instance for ".$id);
			$dashboard = new sm_DashboardManager();
			$dashboard->delete(array('module'=>'SM_MonitorUIDashboardController',"ref_id"=>$id));
		}
		return true;
	
	}
}
