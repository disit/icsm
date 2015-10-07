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

class SM_MonitorUIDashboardController extends sm_ControllerElement
{
	/**
	 * @desc View details of data in dashboard
	 * @name Dashboard View Details
	 *
	 * @url GET /monitor/dashboard/details/:type/:meter/:state
	 *
	 * @callback
	 */
	public function monitor_dashboard_host_details($type,$meter,$state)
	{
		$data['type']=$type;
		$data['meter']=$meter;
		$data['state']=$state;
	
		$monitor= new SM_Monitor();
		$pager = new sm_Pager("monitor/dashboard/details/".$type."/".$meter."/".$state);
	
		$howmany=$pager->get_perPage();
		$page=$pager->get_page();
	
		$hosts = $monitor->services($data['type'],array("state"=>$data['state'],"metric"=>$data['meter']),array("host_description","plugin_output","display_name","host_address"),-1);
		$_totalRows = count($hosts);
		$pager->set_total($_totalRows);
		$hosts = $monitor->services($data['type'],array("state"=>$data['state'],"metric"=>$data['meter']),array("host_description","plugin_output","display_name","host_address"),$howmany,$page);
		$_data['records']=array();
		foreach ($hosts as $host)
		{
			if(!isset($_data['title']))
				$_data['title']="Check: ".$host[2];
			$fields=array("cid","hid","name","description","os","monitor_ip_address");
			$where =array("type"=>$data['type'],"description"=>$host[0]);
			$host_data=sm_Database::getInstance()->select("host",$where,$fields,"1");
	
			foreach($host_data as $i=>$v)
			{
				$mConf=$monitor->getData(array("iid"=>$host_data[$i]['cid']));
				$host_data[$i]['Ip Address']=$host_data[$i]['monitor_ip_address'];
				$host_data[$i]['value']= $host[1];
				$url="monitor/configuration/view/".$mConf['description']."/hosts/hid:".$host_data[$i]['hid'];
				$host_data[$i]['actions']['details']=array("title"=>"View details","class"=>"","url"=>$url,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
				unset($host_data[$i]['monitor_ip_address']);
				unset($host_data[$i]['cid']);
				unset($host_data[$i]['hid']);
				$_data['records'][]=$host_data[$i];
			}
				
				
			//$_data['records']=array_merge($_data['records'],$host_data);
		}
	
		$_data['pager']=$pager;
		$this->view=new SM_MonitorDashboardView($_data);
		$this->view->setOp("view::details");
	
	}
	
	/**
	 * @desc View the overall status dashboard
	 * @name General Info
	 * @url GET /monitor/dashboard/overall
	 *
	 *
	 *
	 */
	public function monitor_dashboard_overall()
	{		
		$dashboard = sm_DashboardManager::getInstance()->getDashboard(array("view_name"=>"overall"));
		$dashboard->setRefreshUrl("monitor/dashboard/overall");
		$dashboard->setTitle("General Info");
		
		$this->view=new SM_MonitorDashboardView($dashboard);
		$this->view->setOp("view");
		$this->view->setType("overall");
	}
	
	/**
	 * @desc View the local dashboard
	 *
	 * @url GET /monitor/dashboard/:id/
	 *
	 * @callback
	 *
	 */
	public function monitor_dashboard($id = null)
	{
		$dashboard =sm_DashboardManager::getInstance()->getDashboard(array("view_name"=>"monitor"));
		$dashboard->setId($id);
		$this->view=new SM_MonitorDashboardView($dashboard);
		$this->view->setOp("panel");
	}
	
	/**
	 * @desc Add the graph in the dashboard
	 * @access
	 *
	 * @url POST /monitor/dashboard/:id/:segment/:sid/:ip/:metric/:from/:to/
	 * @url POST /monitor/dashboard/:id/:segment/:sid/:ip/:metric/:from/
	 * @url POST /monitor/dashboard/:id/:segment/:sid/:ip/:metric/
	 * @url POST /monitor/dashboard/:id/:segment/:sid
	 *
	 * @callback
	 *
	 */
	public function monitor_dashboard_graph_add($id = null, $segment = null, $sid = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		$this->monitor_dashboard_add_board($id,$segment,$sid,$ip,"GRAPH",$metric,$from,$to);
		exit();
	}
	
	
	
	/**
	 * Delete the graph from the dashboard
	 *
	 * @url DELETE /monitor/dashboard/:id/:segment/:sid/:ip/:metric/
	 * @url DELETE /monitor/dashboard/:id/:segment/:sid/:ip
	 * @callback
	 */
	public function monitor_dashboard_graph_delete($id = null, $segment = null, $sid = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		$seg=null;
		if(isset($segment))
			$seg=$segment;
	
		if(isset($sid) && isset($ip) && $segment!="applications"  && $segment!="services")
			$seg .= "/".$sid."/".$ip;
		else
		{
			$seg .= "/".$sid;
			$metric=$ip.":".$metric;
		}
	
		$dboard= new sm_DashboardManager();
		$board =$dboard->getBoards(array('module'=>__CLASS__,"field"=>"segment","value"=>$seg));
		if(count($board)>0)
		{
			if(isset($metric))
			{
				$args = unserialize($board[0]->getcallback_args());
					
				$pos = array_search($metric,$args["metrics"]['GRAPH']);
				if($pos!==FALSE)
				{
					unset( $args["metrics"]['GRAPH'][$pos] );
				}
				if(count($args["metrics"])>=1)
				{
					$board[0]->setcallback_args(serialize($args));
					$dboard->update($board[0]);
				}
				else
					$dboard->remove($board[0]);
			}
			else
				$dboard->remove($board[0]);
		}
	
		exit();
	}
	
	
	
	public static function monitor_dashboard_add_board($id = null, $segment = null, $sid = null, $ip=null, $type="",$metric="_HOST_", $from = null, $to = null)
	{
		$seg=null;
		if(isset($segment))
			$seg=$segment;
	
	
		if(isset($sid) && isset($ip) &&  $segment!="applications" &&  $segment!="services")
			$seg .= "/".$sid."/".$ip;
		else
		{
			$seg .= "/".$sid;
			$metric=$ip.":".$metric;
		}
	
	
		$dboard= new sm_DashboardManager();
		$board =$dboard->getBoards(array('module'=>__CLASS__,"field"=>"segment","value"=>$seg,"ref_id"=>$id));
		if(count($board)>0)
		{
			$args = unserialize($board[0]->getcallback_args());
			if(!isset($args["metrics"][$type]) || !in_array($metric,$args["metrics"][$type]))
				$args["metrics"][$type][]=$metric;
	
			$board[0]->setcallback_args(serialize($args));
			$dboard->update($board[0]);
		}
		else
		{
			$segment_data=null;
			if($segment && $sid)
			{
				$configuration=new SM_Configuration($id);
	
				$segment_obj=$configuration->getSegment($segment,$sid);
				$segment_data=$segment_obj[$segment];
			}
			$args=array();
			$args["metrics"][$type][]=$metric;
			$board = new sm_Board();
			if($seg)
				$board->setsegment($seg);
			$board->setmodule(__CLASS__);
			$board->setref_id($id);
			if(isset($segment_data))
				$board->settitle($segment_data->getname());
			else
				$board->settitle($type);
			$board->setcallback_args(serialize($args));
			//$view = $type=="SUMMARY"?"Summary":"Host";
			$board->setview_name($type);
			$dboard->add($board);
		}
	}
	
	static function monitor_dashboard_load_boards($segment,$sid,$ip=null){
		
		if($ip!=null)
			return sm_DashboardManager::getInstance()->getBoards(array('module'=>__CLASS__,"field"=>"segment","value"=>$segment."/".$sid."/".$ip));
		return sm_DashboardManager::getInstance()->getBoards(array('module'=>__CLASS__,"field"=>"segment","value"=>$segment."/".$sid));
	}
	
	public function monitor_hosts_dashboard(sm_Board $board)
	{
		$data=array();
		$args = unserialize($board->getcallback_args());
		if(!isset($args['metrics']['HOST']))
			return $data;
		$data['callback']['args']['data']['id']="host";
		$data['callback']['args']['data']['title']=$board->gettitle();
		$monitor = new SM_Monitor();
		foreach($args['metrics']['HOST'] as $i=>$metric)
		{
			$data['callback']['args']['data']['metric'][$metric]=array();
			$stats = $monitor->metric_stats($metric);
			if(isset($stats))
				$data['callback']['args']['data']['metric'][$metric]=$stats;
		}
	
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_hosts_groups";
		return $data;
	}
	
	public function monitor_virtual_hosts_dashboard(sm_Board $board)
	{
		$data=array();
		$args = unserialize($board->getcallback_args());
		if(!isset($args['metrics']['VIRTUALHOST']))
			return $data;
		$data['callback']['args']['data']['id']="vmhost";
		$data['callback']['args']['data']['title']=$board->gettitle();
		$monitor = new SM_Monitor();
		foreach($args['metrics']['VIRTUALHOST'] as $i=>$metric)
		{
				
			$data['callback']['args']['data']['metric'][$metric]=array();
			$stats = $monitor->metric_stats($metric,"vmhost");
			if(isset($stats))
				$data['callback']['args']['data']['metric'][$metric]=$stats;
		}
	
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_virtual_machines";
		return $data;
	
	}
	
	public function monitor_lastchecks_dashboard(sm_Board $board)
	{
		$data=array();
		$monitor = new SM_Monitor();
		$seg = $board->getsegment();
		$interval=sm_Config::get("MONITORDASHBOARD_EVENTS_INTERVAL",4);
		$data['callback']['args']['data']['id']=$seg;
		$data['callback']['args']['data']['title']=$board->gettitle()." of recent ".$interval." hour(s)";
		$data['callback']['args']['data']['checks']=array();
		$checks = $monitor->all_last_events(10,time()-3600*$interval);
		if(isset($checks['checks']))
			$data['callback']['args']['data']['checks']=$checks['checks'];
	
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_lastchecks";
		return $data;
	}
	
	/*public function monitor_local_lastchecks_dashboard(sm_Board $board)
	{
		$data=array();
		$monitor = new SM_Monitor();
		$seg = $board->getsegment();
		$id=$board->getref_id();
		$conf = SM_Configuration::load($id);
		
		$interval=sm_Config::get("MONITORDASHBOARD_EVENTS_INTERVAL",4);
		$data['callback']['args']['data']['id']=$seg;
		$data['callback']['args']['data']['title']=$board->gettitle()." of recent ".$interval." hour(s)";
		$data['callback']['args']['data']['checks']=array();
		$checks = $monitor->last_events($seg, array("description"=>$conf->getdescription()), 10,time()-3600*$interval);
		if(isset($checks['checks']))
			$data['callback']['args']['data']['checks']=$checks['checks'];
	
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_local_lastchecks";
		return $data;
	}*/
	
	public function monitor_local_host_overall_dashboard(sm_Board $board)
	{
		$data=array();		
		$_data=array();	
		$seg = $board->getsegment();
		$_data['id']=$seg."-overall";
		//$_data['title']=$board->gettitle();
		$id=$board->getref_id();
		$segment=explode("/",$board->getsegment());
		
		$conf = SM_Configuration::load($id);
		if($seg =="Hosts")
		{
			$N = Host::getAllCount(array("type"=>'host','cid'=>$conf->getcid()));
			if($N>0)
			{
				$monitor = new SM_Monitor();
				$hosts = Host::getAll(array("type"=>'host','cid'=>$conf->getcid()));
				$_data['values']['Up']=$_data['values']['Down']=0;
				foreach ($hosts as $h){
					$where=array('description'=>$h->getdescription());
					$status = $monitor->hosts_status("host",$where);
					$_data['values']['Up']=isset($status['Up'])?$_data['values']['Up']+$status['Up']:$_data['values']['Up'];
					$_data['values']['Down']=isset($status['Down'])?$_data['values']['Down']+$status['Down']:$_data['values']['Down'];
				}
				$_data['title']="Hosts";
				$_data['values']['Total']=$N;
				//$_data['values']['page']="monitor/hosts";
				$_data['values']['board_title']=$board->gettitle();
				$data['callback']['args']['data']=$_data;
				$data['callback']['args']['status']=array();
				$data['callback']['args']["tpl"]['tpl']="hosts_overall_dashboard";
				$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."configurator.tpl.html";
				$data['callback']['class']="SM_MonitorDashboardView";
				$data['callback']['method']="dashboard_local_overall";
			}
		}
		else if($seg =="vHosts")	
		{
			$N = Host::getAllCount(array("type"=>'vmhost','cid'=>$conf->getcid()));
			if($N>0){
				$monitor = new SM_Monitor();
				$hosts = Host::getAll(array("type"=>'vmhost','cid'=>$conf->getcid()));
				$_data['values']['Up']=$_data['values']['Down']=0;
				foreach ($hosts as $h){
					$where=array('description'=>$h->getdescription());
					$status = $monitor->hosts_status("vmhost",$where);
					$_data['values']['Up']=isset($status['Up'])?$_data['values']['Up']+$status['Up']:$_data['values']['Up'];
					$_data['values']['Down']=isset($status['Down'])?$_data['values']['Down']+$status['Down']:$_data['values']['Down'];
				}
				$_data['title']="Virtual Machines";				
				$_data['values']['Total']=$N;
				$_data['values']['board_title']=$board->gettitle();
				$data['callback']['args']['data']=$_data;
				$data['callback']['args']['status']=array();
				$data['callback']['args']["tpl"]['tpl']="vhosts_overall_dashboard";
				$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."configurator.tpl.html";
				$data['callback']['class']="SM_MonitorDashboardView";
				$data['callback']['method']="dashboard_local_overall";
			}
		}	
		
		return $data;
	}
	
	
	public function monitor_local_host_performance_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$segment=explode("/",$board->getsegment());
		$monitor = new SM_Monitor();
		$conf = SM_Configuration::load($id);
		$data=array();
		if(!isset($args['metrics']))
			return $data;
	
		$data['callback']['args']['data']=array();
		$data['callback']['args']['status']=array();
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_local_host_performance";
	
		foreach($args['metrics'] as $i=>$metric)
		{
				
			foreach($metric as $val)
			{
				$meter = $monitor->meter($conf->getname(), $conf->getidentifier(), "hostgroup", $val);
				usort($meter['meters'], array($this, 'sort_meter_value_desc'));
	
				$dash_data['status'][$val]= $meter['meters'];
			}
			$data['callback']['args']['data']['board_title']=$board->gettitle();
			if(isset($dash_data['status']))
			{
				$data['callback']['args']['status']=$dash_data['status'];
	
			}
			else
				$data['callback']['args']['status']="Metrics not available or evaluation in progress!";
	
	
		}
		return $data;
	
	}
	
	public function monitor_local_performance_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$segment=explode("/",$board->getsegment());
		$monitor = new SM_Monitor();
		$conf = SM_Configuration::load($id);
		$data=array();
		if(!isset($args['metrics']))
			return $data;
	
		$data['callback']['args']['data']=array();
		$data['callback']['args']['status']=array();
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_local_performance";
	
		foreach($args['metrics'] as $i=>$metric)
		{
				
			foreach($metric as $val)
			{
				$meter = $monitor->meter($conf->getname(), $conf->getidentifier(), "hostgroup", $val);
				usort($meter['meters'], array($this, 'sort_meter_value_desc'));
	
				//$dash_data['status'][$val]= !empty($meter['meters'])?$meter['meters']:"Metrics not available or evaluation in progress!";
				$dash_data['status'][$val]= !empty($meter['meters'])?$meter['meters']:null;
			}
			$data['callback']['args']['data']['board_title']=$board->gettitle();
			if(isset($dash_data['status']))
			{
				$data['callback']['args']['status']=$dash_data['status'];
	
			}
			else
				$data['callback']['args']['status']="Metrics not available or evaluation in progress!";
	
	
		}
		return $data;
	
	}
	
	public function monitor_local_host_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$segment=explode("/",$board->getsegment());
		$monitor = new SM_Monitor();
		$conf = SM_Configuration::load($id);
		$data=array();
		if(!isset($args['metrics']) || $segment[0]!="hosts")
			return $data;
	
		$data['callback']['args']['host']=array();
		$data['callback']['args']['meters']=array();
		$data['callback']['args']['graphs']=array();
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_host";
	
	
		foreach($args['metrics'] as $i=>$metric)
		{
	
			$dhosts=$conf->getSegment("hosts");
			if(is_a($dhosts,"Host"))
				$hosts[]=$dhosts;
			else
				$hosts=$dhosts['hosts'];
	
			foreach($hosts as $host)
			{
				$name=$host->getname();
				$host_ip = $host->getmonitor_ip_address();
				$sid="hid:".$host->gethid();
	
				foreach($metric as $val)
				{
					if($i=="GRAPH")
					{
						$graph = SM_MonitorUIController::monitor_doGraph($id, "hosts", $sid, $host_ip, $val); //, $from, $to);
						if(isset($graph['graphs']))
							$data['callback']['args']['graphs'][$host->gethid()][$val]=$graph['graphs'];
					}
					if($i=="HOST")
					{
						//$m = explode(":",$val);
						$meter = $monitor->meter($name, $host_ip, "host", $val);
						$data['callback']['args']['meters'][$host->gethid()][$val]= isset($meter['meters'])?$meter['meters']:array();
					}
				}
					
				if(!isset($data['callback']['args']['host'][$host->gethid()]))
				{
					$data['callback']['args']['host'][$host->gethid()]['board_title']=$name." (".$host_ip.")";
					$data['callback']['args']['host'][$host->gethid()]['host']=$host;
				}
	
			}
	
				
		}
		return $data;
	
	}
	
	public function monitor_services_dashboard(sm_Board $board)
	{
		$_data=array();
	
		$seg = $board->getsegment();
		$_data['id']=$seg;
		$_data['title']=$board->gettitle();
	
	
		$monitor=new SM_Monitor();
		$status = $monitor->services_status();
		$_data['values']['Total']=isset($status['Total'])?$status['Total']:0;
		$_data['values']['OK']=isset($status['OK'])?$status['OK']:0;
		$_data['values']['Crit']=isset($status['Critical'])?$status['Critical']:0;
		$_data['values']['Warn']=isset($status['Warning'])?$status['Warning']:0;
		$_data['values']['Unkn']=isset($status['Unknown'])?$status['Unknown']:0;
		$_data['values']['page']="monitor/checks";
		$data['callback']['args']['data']=$_data;
		$data['callback']['args']["tpl"]['tpl']="services_overall_dashboard_table";
	
	
		$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."monitor.tpl.html";
		$data['callback']['class']="SM_MonitorDashboardView";
		$data['callback']['method']="dashboard_services_overall";
	
		return $data;
	}
	
	public function monitor_local_service_overall_dashboard(sm_Board $board)
	{
		$data=array();
		$_data=array();
		$id=$board->getref_id();	
		$conf = SM_Configuration::load($id);
		$apps = $conf->getSegment("applications");
		
		if(count($apps['applications'])>0)
		{
			$monitor=new SM_Monitor();
			$seg = $board->getsegment();
			foreach($apps['applications'] as $app){
				
				$id = $app->getdescription();
				$_data[$id]['id']=$seg;
				$_data[$id]['title']=$app->getname();
				$services = $app->getServices();
				foreach ($services as $service)
				{
					$sid= $service->getdescription();
					$name = $service->getname();
					$status = $monitor->application_services_status(array("description"=>$sid));
					$_data[$id]['service'][$name]['Name']=$name;
					$_data[$id]['service'][$name]['Total']=isset($status['Total'])?$status['Total']:0;
					$_data[$id]['service'][$name]['OK']=isset($status['OK'])?$status['OK']:0;
					$_data[$id]['service'][$name]['Crit']=isset($status['Critical'])?$status['Critical']:0;
					$_data[$id]['service'][$name]['Warn']=isset($status['Warning'])?$status['Warning']:0;
					$_data[$id]['service'][$name]['Unkn']=isset($status['Unknown'])?$status['Unknown']:0;
					//$_data['values']['page']="monitor/checks";
					
				}
			}
			$data['callback']['args']['data']=$_data;
			$data['callback']['args']["tpl"]['tpl']="services_overall_dashboard_table";
			$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."monitor.tpl.html";
			$data['callback']['class']="SM_MonitorDashboardView";
			$data['callback']['method']="dashboard_local_application_services";
		}
	
		return $data;
	}
	
	function onDeleteMonitor(sm_Event &$event)
	{
		$monitor = new SM_Monitor();
		$data = $event->getData();
		$id=$data['iid'];
		//sm_Logger::write("Deleting monitor instance for ".$id);
		//$result=$this->monitor->delete($mid);
	
		if(class_exists("sm_DashboardManager") && isset($id) && $id>0)
		{
			sm_Logger::write("Starting deleting Dashboards monitor instance for ".$id);
			sm_Logger::write("Deleting Dashboard monitor instance for ".$id);
			$dashboard = new sm_DashboardManager();
			$boards=$dashboard->getBoards(array('module'=>__CLASS__,"field"=>"ref_id","value"=>$id));
			if(isset($boards))
			{
				foreach($boards as $i=>$board)
					$board->delete($board->getid());
			}
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
			$dashboard->delete(array('module'=>__CLASS__,"ref_id"=>$id));
		}
		return true;
	
	}
	
	static public function install($db)
	{
		self::installDashboard();
		return true;
	
	}
	
	static public function installDashboard()
	{
		if(!class_exists("sm_Board") || !class_exists("sm_DashboardManager") )
			return;
		$dboard= new sm_DashboardManager();
		$dboard->delete(array("module"=>__CLASS__));
	
		/*
		 * Configuration Dashboard
		*/
		//Summary/Overall panel
				
	
		/* $board = new sm_Board();
	
		 $board->setweight(0);
		 $board->setsegment("Monitor");
		 $board->setmodule(__CLASS__);
		 $board->setref_id(-1);
		 $board->settitle("Summary");
		 $board->setcallback_args(serialize($args));
		 $board->setview_name("monitor");
		 $board->setmethod("monitor_local_overall_dashboard");
		 $dboard->add($board);*/
	
		 $board = new sm_Board();
		 $board->setsegment("Hosts");
		 $board->setweight(0);
		 $board->setmodule(__CLASS__);
		 $board->setref_id(-1);
		 $board->settitle("Summary");
		 $board->setcallback_args(serialize(array()));
		 $board->setview_name("monitor");
		 $board->setmethod("monitor_local_host_overall_dashboard");
		 $dboard->add($board);
		 	
		 $board = new sm_Board();
		 $board->setsegment("vHosts");
		 $board->setweight(1);
		 $board->setmodule(__CLASS__);
		 $board->setref_id(-1);
		 $board->settitle("Summary");
		 $board->setcallback_args(serialize(array()));
		 $board->setview_name("monitor");
		 $board->setmethod("monitor_local_host_overall_dashboard");
		 $dboard->add($board);
		 
		 $board = new sm_Board();
		 $board->setsegment("Services");
		 $board->setweight(2);
		 $board->setmodule(__CLASS__);
		 $board->setref_id(-1);
		 $board->settitle("Summary");
		 $board->setcallback_args(serialize(array()));
		 $board->setview_name("monitor");
		 $board->setmethod("monitor_local_service_overall_dashboard");
		 $dboard->add($board);
		 
	/*	 $board = new sm_Board();
		 $board->setsegment("Services");
		 $board->setweight(2);
		 $board->setmodule(__CLASS__);
		 $board->setref_id(-1);
		 $board->settitle("Summary");
		 $board->setcallback_args(serialize(array()));
		 $board->setview_name("monitor");
		 $board->setmethod("monitor_local_lastchecks_dashboard");
		 $dboard->add($board);*/
		
		
		
		
		//Performance panel
		$args=array();
		$args["metrics"]["Performance"][]="CPU";
		$args["metrics"]["Performance"][]="Memory";
		$args["metrics"]["Performance"][]="Disk";
	
		$board = new sm_Board();
	
		$board->setweight(0);
		$board->setsegment("hosts");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Summary");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("monitor_local_host_performance_dashboard");
		$dboard->add($board);
	
		//Hosts
	/*	$args=array();
		$args["metrics"]["HOST"][]="CPU";
		$args["metrics"]["HOST"][]="Memory";
		$args["metrics"]["HOST"][]="Disk";
		$args["metrics"]["GRAPH"][]="_HOST_:0";
		$args["metrics"]["GRAPH"][]="_HOST_:1";
		$board = new sm_Board();
	
		$board->setweight(1);
		$board->setsegment("hosts");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Hosts");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("monitor_local_host_dashboard");
		$dboard->add($board);
	*/
	
		//Overall HOSTS Dashboard
		$args=array();
	
		$args["metrics"]['HOST'][]="CPU";
		$args["metrics"]['HOST'][]="Memory";
		$args["metrics"]['HOST'][]="Disk";
		$args["metrics"]['HOST'][]="Network";
	
		$board = new sm_Board();
	
		$board->setweight(51);
		$board->setsegment("hosts");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Hosts Groups Health");
		$board->setmethod("monitor_hosts_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
		$dboard->add($board);
	
		//Overall Virtual Hosts Dashboard
		$args=array();
	
		$args["metrics"]['VIRTUALHOST'][]="CPU";
		$args["metrics"]['VIRTUALHOST'][]="Memory";
		$args["metrics"]['VIRTUALHOST'][]="Disk";
		$args["metrics"]['VIRTUALHOST'][]="Network";
	
	
	
		$board = new sm_Board();
	
		$board->setweight(52);
		$board->setsegment("vhosts");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Virtual Machines Health");
		$board->setmethod("monitor_virtual_hosts_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
		$dboard->add($board);
	
		$board = new sm_Board();
		$args=array();
		$board->setweight(50);
		$board->setsegment("services");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Checks");
		$board->setmethod("monitor_services_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
		$dboard->add($board);
	
		//Overall Last Checks Dashboard
		$args=array();
		$args["metrics"][]="Last Checks";
	
		$board = new sm_Board();
	
		$board->setweight(60);
		$board->setsegment("Checks");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Last Checks");
		$board->setmethod("monitor_lastchecks_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
		$dboard->add($board);
	}
	
	/**
	 *
	 * @param number $x
	 * @param number $y
	 * @return number
	 */
	
	static function sort_meter_value_desc($x, $y)
	{
	
		if($x['unit']!="%" && $x['max']!=0 && $y['max'])
		{
			$a = 100*$x['value']/$x['max'];
			$b = 100*$y['value']/$y['max'];
			if($a==$b)
				return 0;
			if($a<$b)
				return 1;
			return -1;
		}
		else
			return strnatcmp($y["value"], $x["value"]);
	}
	
	/**
	 *
	 * @param number $x
	 * @param number $y
	 * @return number
	 */
	
	static function sort_meter_value_asc($x, $y)
	{
		if($x['unit']!="%" && $x['max']!=0 && $y['max'])
		{
			$a = 100*$x['value']/$x['max'];
			$b = 100*$y['value']/$y['max'];
			if($a==$b)
				return 0;
			if($a>$b)
				return 1;
			return -1;
		}
		return strnatcmp($x["value"], $y["value"]);
			
	}
	
}