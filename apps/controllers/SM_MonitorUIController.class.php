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

class SM_MonitorUIController extends sm_ControllerElement
{
	
	function __construct(){
		
	}
	
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET monitor/configuration/segment/view/:id/:type
	 *
	 * @callback
	 */
	
	function monitor_segment_entries($id=null, $type=null, $sid=null )
	{
		$this->model= new SM_Monitor();
		$controller = new SM_ConfiguratorUIController();
		$controller->configurator_segment_entries($id,$type,$sid);
		$view = $controller->getView();
		$view_model=$view->getModel();
		$stype = $view_model['type'];
		
		foreach($view_model['segments'][$stype] as $i=>$records)
		{
			if($stype =="host")
			{
				$view_model['segments'][$stype][$i]['state']="<span class='label label-default'>UNKN</span>";
				$state = $this->model->hosts_status($view_model['segments'][$stype][$i]['type'],array("description"=>$view_model['segments'][$stype][$i]['description']));
				if($state)
					$view_model['segments'][$stype][$i]['state']=$state['Up']?"<span class='label label-success'>UP</span>":"<span class='label label-danger'>DOWN</span>";
			}	
		}
		if($type =="hosts")
		{
			$view->setFilters($type,array("name","state","os","type","ip_address"));
		}
		else if($type =="applications")
		{
			$view->setFilters($type,array("name","description","type","contacts"));
		}
		
		$view->setModel($view_model);
		$this->view=$view;
		
	}
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET monitor/configuration/segment/view/:id/:type/:sid
	 *
	 * @callback
	 */
	
	function monitor_segment_entry($id=null, $type=null, $sid=null )
	{
		$this->model= new SM_Monitor();
		$controller = new SM_ConfiguratorUIController();
		$controller->configurator_segment_entry($id,$type,$sid);
		$view = $controller->getView();
		$view_model=$view->getModel();
		$monitor_data=$this->model->getData(array('description'=>$view_model['description']));
		if($monitor_data['status']!=0)
		{
			$params=$view_model['description']."/".$view_model['segment_type']."/".$view_model['sid'];
			$view_model['menu']["Info"]=array(		
					'id'=>"Info",
					'title'=>'Info',
					'url'=>"monitor/configuration/info/".$params,
			);
			$view_model['menu']["Controls"]=array(
					'id'=>"Controls",
					'title'=>'Controls',
					'url'=>"monitor/controls/".$params
			);
			$view_model['menu']["Meters"]=array(
					'id'=>"Meters",
					'title'=>'Meters',
					'url'=>"monitor/meters/".$params
			);
			$view_model['menu']["Graphs"]=array(
							'id'=>"Graphs",
							'title'=>'Graphs',
							'url'=>"monitor/graphs/".$params
			);
			if($type=="hosts")
				$view_model['menu']["Events"]=array(
					'id'=>"Events",
					'title'=>'Events',
					'url'=>"monitor/events/".$params
			);
			
		}
		$view->setModel($view_model);
		$this->view=$view;
	
	}
	
	
	/**
	 * @desc Gets the info for a configuration
	 *
	 * @url GET monitor/configuration/info/:id/:type/:sid
	 *
	 * @callback
	 */
	function monitor_configuration_segment_info($id=null, $type=null, $sid=null )
	{
		$this->model= new SM_Monitor();
		$controller = new SM_ConfiguratorUIController();
		$controller->configurator_segment_info($id,$type,$sid);
		$view = $controller->getView();
		$view_model=$view->getModel();
		if($type=="hosts" && in_array($view_model['segments']['host'][0]['type'],array('host','vmhost')))
		{
			$metrics["HOST"][]="CPU";
			$metrics["HOST"][]="Memory";
			$metrics["HOST"][]="Disk";
			
			$metrics["GRAPH"][]="_HOST_:0";
			//$metrics["GRAPH"][]="_HOST_:1";
			$name=$view_model['segments']['host'][0]['name'];
			$host_ip = $view_model['segments']['host'][0]['monitor_ip_address'];
			foreach($metrics as $i=>$metric)
				foreach($metric as $val)
				{
					if($i=="GRAPH")
					{
						$graph = $this->monitor_doGraph($id, "hosts", $sid, $host_ip, $val); //, $from, $to);
						if(isset($graph['graphs']))
							$view_model['graphs'][$val]=$graph['graphs'];
					}
					if($i=="HOST")
					{
						$meter = $this->model->meter($name, $host_ip, "host", $val);
						if(isset($meter['meters']))
						{
							usort($meter['meters'], array("SM_MonitorUIDashboardController", 'sort_meter_value_desc'));
							$view_model['meters'][$val]=$meter['meters'];
						}
						else 
							$view_model['meters'][$val]= array();
					}
				}
			$state = $this->model->hosts_status($view_model['segments']['host'][0]['type'],array("description"=>$view_model['segments']['host'][0]['description']));
			if($state)
			{
				$view_model['state']=$state['Up']?"UP":"DOWN";
				$view_model['ping']=$this->model->host_ping($view_model['segments']['host'][0]['type'],array("description"=>$view_model['segments']['host'][0]['description']));
			}
		}
	/**/
		
		$view->setModel($view_model);
		$this->view=$view;
			
	}
	
	/**
	 * @desc Gets the xml for a configuration by $id
	 *
	 * @url GET monitor/configuration/xml/:id
	 * 
	 * @callback
	 */
	function monitor_configuration_xml($id=null)
	{
		
		if(!isset($id))
		{
			sm_set_error("Invalid request!");
			return;
		}
		$monitor= new SM_Monitor();
		if(preg_match("/SM:/",$id))
			$cid=$monitor->getInternalId($id);
		else 
			$cid=$id;
				
		$configuration= new SM_Configuration($cid);
		if($configuration->gettype()=="System")
			$configuration->addRelations("hosts","Host");
		
		$data=array('configuration'=>$configuration->build("*"));
		$data['configuration']['@attributes']['url']="monitor/configuration/segment/view/";
		
		$this->view = new SM_ConfiguratorView($data);
		$this->view->setOp("xml");
		
	}
	
	/**
	 * @desc Gets the configuration view page 
	 * 
	 * @url GET /monitor/configuration/view/:id
	 * @url GET /monitor/configuration/view/:id/:segment/:sid
	 *
	 */
	function monitor_configuration_view($id=null,$segment=null,$sid=null)
	{
		$this->model= new SM_Monitor();
		$controller = new SM_ConfiguratorUIController();
		$controller->configurator_configuration_page($id);
		$view = $controller->getView();
		$view_model=$view->getModel();
		if(!isset($view_model['id']))
		{
			return;
		}
		//unset($view_model['menu']['History']);
		$view_model['monitor']=$this->model->getData(array('iid'=>$view_model['id']));
		
		if($view_model['monitor']['status']==-1)
		{
			$view_model['monitor']['status_text']="Ready";
			$view_model['monitor']['label_status']="info";
		}
		else if($view_model['monitor']['status']==0)
		{
			$view_model['monitor']['status_text']="Waiting";
			$view_model['monitor']['label_status']="warning";
		}
		else if($view_model['monitor']['status']==1)
		{
			$view_model['monitor']['status_text']="Monitoring";
			$view_model['monitor']['label_status']="success";
		}
		else if($view_model['monitor']['status']==2)
		{
			$view_model['monitor']['status_text']="Stopped";
			$view_model['monitor']['label_status']="danger";
		}
		else if($view_model['monitor']['status']==3)
		{
			$view_model['monitor']['status_text']="Failed";
			$view_model['monitor']['label_status']="danger";
		}
		else if($view_model['monitor']['status']==4)
		{
			$view_model['monitor']['status_text']="Processing";
			$view_model['monitor']['label_status']="default";
		}
		
		$view_model['xmlUrl']="monitor/configuration/xml/".$view_model['monitor']['description'];
		$view->setModel($view_model);
		$data["view"]=$view;
		if(isset($segment) && isset($sid))
		{
			$data["link"]="monitor/configuration/segment/view/".$view_model['monitor']['description']."/".$segment."/".$sid;
		}
		sm_View::instance()->unregister($view);
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("configuration_view");
	}
	
	/**
	 * @desc Gets the configurations list table
	 *
	 * @url GET /monitor/configurations
	 * @url GET /monitor/business
	 *
	 */
	function monitor_business_configurations()
	{
	
		$keywords="";
		$status=-2;
		$this->model= new SM_Monitor();
		if(isset($_SESSION['monitor/configurations']['status']))
			$status=$_SESSION['monitor/configurations']['status'];
		if(isset($_SESSION['monitor/configurations']['keywords']))
			$keywords=$_SESSION['monitor/configurations']['keywords'];
	
		$query = "SELECT cid, status, configuration.description, errors FROM configuration JOIN monitors on cid=iid where type='business'";
		$query_count = "SELECT COUNT(*) as count FROM configuration JOIN monitors on cid=iid where type='business'";
		//calling a method to get the records with the limit set
		$where=array();
		if($status>=-1)
			$where['status']="status='".$status."'";
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(configuration.name like '%".$k."%' OR configuration.description like '%".$k."%' OR configuration.bid like '%".$k."%')";
			}
			
		}
		$cond="";
		if(count($where)>0)
			$cond=" AND ".implode("AND",$where);
		
		$query.=$cond;
		$query_count.=$cond;
		
		
		$r=sm_Database::getInstance()->query($query_count);
		$_totalRows=$r[0]['count'];
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/configurations");
		$pager->set_total($_totalRows);
		//$monitor = $this->model; //new SM_Monitor();
		$query.=" ".$pager->get_limit();
		//$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$records=sm_Database::getInstance()->query($query);
		
		$data['records']=array();
		if($records)
			$data['records']=$this->build_data($records);
		//create the nav menu
		$data['title']="Business";
		$data['type_selector']=$status;
		$data['keywords']=$keywords;
		$data['pager'] = $pager;
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("business_list");
	}
	
	/**
	 * @desc Gets the configurations list table
	 *
	 * @url GET /monitor/infrastructure
	 * @url GET /monitor/system
	 *
	 */
	function monitor_system_configurations()
	{
	
		$this->model= new SM_Monitor();
		$keywords="";
		$status=-2;
		//$this->model= new SM_Monitor();
		if(isset($_SESSION['monitor/infrastructure']['status']))
			$status=$_SESSION['monitor/infrastructure']['status'];
		if(isset($_SESSION['monitor/infrastructure']['keywords']))
			$keywords=$_SESSION['monitor/infrastructure']['keywords'];
	
		$query = "SELECT cid,status, configuration.description, errors FROM configuration JOIN monitors on iid=cid where type='system'";
		$query_count = "SELECT COUNT(*) as count FROM configuration JOIN monitors on iid=cid where type='system'";
		//calling a method to get the records with the limit set
		$where=array();
		if($status>=-1)
			$where['status']="status='".$status."'";
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(configuration.name like '%".$k."%' OR configuration.description like '%".$k."%' OR configuration.bid like '%".$k."%')";
			}
				
		}
		$cond="";
		if(count($where)>0)
			$cond=" AND ".implode("AND",$where);
	
		$query.=$cond;
		$query_count.=$cond;
		$r=sm_Database::getInstance()->query($query_count);
	
		$_totalRows=$r[0]['count'];
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/infrastructure");
		$pager->set_total($_totalRows);
		//$monitor = $this->model; //new SM_Monitor();
		$query.=" ".$pager->get_limit();
		//$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$records=sm_Database::getInstance()->query($query);
		$data['records']=array();
		if($records)
			$data['records']=$this->build_data($records);
		
		//create the nav menu
		$data['title']="Host Groups";
		$data['type_selector']=$status;
		$data['keywords']=$keywords;
		$data['pager'] = $pager;
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("system_list");
	}
	
	/**
	 * @desc Set the configuration table limit per page
	 *
	 * @url POST /monitor/infrastructure
	 * @url POST /monitor/system
	 * @url POST /monitor/configurations
	 * @url POST /monitor/business
	 * 
	 */
	function monitor_configuration_post()
	{
	
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the configurations list table
	 *
	 * @url GET /monitor/hosts
	 *
	 */
	function monitor_hosts()
	{
		$keywords="";
		$this->model= new SM_Monitor();
		$state=-1;
	
		if(isset($_SESSION['monitor/hosts']['state']))
			$state=$_SESSION['monitor/hosts']['state'];
		if(isset($_SESSION['monitor/hosts']['keywords']))
			$keywords=$_SESSION['monitor/hosts']['keywords'];
		
		$query = "SELECT cid,hid,name,description,os,ip_address,status as state FROM host JOIN monitor_host on ref=description where host.type='host'";
		$query_count = "SELECT COUNT(*) as count FROM host JOIN monitor_host on ref=description where host.type='host'";
		//calling a method to get the records with the limit set
		$where=array();
		if($state>=0)
		{
			$status=$state==0?"DOWN":"UP";
			$where['status']="status='".$status."'";
		}
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(host.name like '%".$k."%' OR host.description like '%".$k."%')";
			}
		
		}
		$cond="";
		if(count($where)>0)
			$cond=" AND ".implode("AND",$where);
		
		$query.=$cond;
		$query_count.=$cond;
		$r=sm_Database::getInstance()->query($query_count);
		
		$_totalRows=$r[0]['count'];
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/hosts");
		$pager->set_total($_totalRows);
		//$monitor = $this->model; //new SM_Monitor();
		$query.=" ".$pager->get_limit();
		//$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$records=sm_Database::getInstance()->query($query);
		$data['state_selector']=$state;
		$data['keywords']=$keywords;
		$data['records']=$this->host_data($records);
		$data['pager'] = $pager;
		$data['title']="Hosts";
		$data['type']="hosts";
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("hosts_list");
	}
	
	/**
	 * @desc Gets the configurations list table
	 *
	 * @url GET /monitor/vmhosts
	 *
	 */
	function monitor_vmhosts()
	{
		$this->model= new SM_Monitor();
		$keywords="";
		$state=-1;
	
		if(isset($_SESSION['monitor/vmhosts']['state']))
			$state=$_SESSION['monitor/vmhosts']['state'];
		if(isset($_SESSION['monitor/vmhosts']['keywords']))
			$keywords=$_SESSION['monitor/vmhosts']['keywords'];
		
		$query = "SELECT cid,hid,name,description,os,ip_address,status as state FROM host JOIN monitor_host on ref=description where host.type='vmhost'";
		$query_count = "SELECT COUNT(*) as count FROM host JOIN monitor_host on ref=description where host.type='vmhost'";
		//calling a method to get the records with the limit set
		$where=array();
		if($state>=0)
		{
			$status=$state==0?"DOWN":"UP";
			$where['status']="status='".$status."'";
		}
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(host.name like '%".$k."%' OR host.description like '%".$k."%')";
			}
		
		}
		$cond="";
		if(count($where)>0)
			$cond=" AND ".implode("AND",$where);
		
		$query.=$cond;
		$query_count.=$cond;
		$r=sm_Database::getInstance()->query($query_count);
		
		$_totalRows=$r[0]['count'];
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/vmhosts");
		$pager->set_total($_totalRows);
		//$monitor = $this->model; //new SM_Monitor();
		$query.=" ".$pager->get_limit();
		//$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$records=sm_Database::getInstance()->query($query);
		$data['records']=$this->host_data($records,"vmhost");
		$data['state_selector']=$state;
		$data['keywords']=$keywords;
		$data['pager'] = $pager;
		$data['title']="Virtual Machines";
		$data['type']="vmhosts";
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("vmhosts_list");
	}
	
	/**
	 * @desc Gets the configurations list table
	 *
	 * @url GET /monitor/devices
	 *
	 */
	function monitor_devices()
	{
		$keywords="";
		$this->model= new SM_Monitor();
		$state=-1;
	
		if(isset($_SESSION['monitor/device']['state']))
			$state=$_SESSION['monitor/device']['state'];
		if(isset($_SESSION['monitor/device']['keywords']))
			$keywords=$_SESSION['monitor/device']['keywords'];
	
		$query = "SELECT cid,did,name,description,type,ip_address FROM device where";
		$query_count = "SELECT COUNT(*) as count FROM device where";
		//calling a method to get the records with the limit set
		$where=array();
		/*	if($status>=0)
		 $where['status']="status='".$status."'";*/
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(device.name like '%".$k."%' OR device.description like '%".$k."%')";
			}
	
		}
		$cond=" true";
		if(count($where)>0)
			$cond=" ".implode("AND",$where);
	
		$query.=$cond;
		$query_count.=$cond;
		$r=sm_Database::getInstance()->query($query_count);
	
		$_totalRows=$r[0]['count'];
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/devices");
		$pager->set_total($_totalRows);
		//$monitor = $this->model; //new SM_Monitor();
		$query.=" ".$pager->get_limit();
		//$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$records=sm_Database::getInstance()->query($query);
		$data['state_selector']=$state;
		$data['keywords']=$keywords;
		$data['records']=$this->device_data($records);
		$data['pager'] = $pager;
		$data['title']="Devices";
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("devices_list");
	}
	
	/**
	 * @desc Gets the checks list table
	 *
	 * @url GET /monitor/checks
	 *
	 */
	function monitor_checks()
	{
		$keywords="";
		$this->model= new SM_Monitor();
		$state="All";
	
		if(isset($_SESSION['monitor/checks']['state']))
			$state=$_SESSION['monitor/checks']['state'];
		if(isset($_SESSION['monitor/checks']['keywords']))
			$keywords=$_SESSION['monitor/checks']['keywords'];
	
		$where = array();
		if($keywords!="")
		{
			$where[]=str_replace("/\s+/", " ", $keywords);
			$where = $where + explode(" ",str_replace("/\s+/", " ", $keywords));
			
		}
		$stateArray["All"]="All";
		$stateArray=array_merge($stateArray,$this->model->getChecksStateLabel());
		
		if($state=="All")
			$state=null;
		$_totalRows=$this->model->checks_list_count($where,$state);
		//$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("monitor/checks");
		$pager->set_total($_totalRows);
		$records=$this->model->checks_list($where,$state,$pager->get_perPage(),$pager->get_page());
		$data['state_selector']=$state;
		$data['keywords']=$keywords;
		$data['records']=$this->checks_data($records);
		$data['state']=$stateArray;
		$data['pager'] = $pager;
		$data['title']="Checks";
		$this->view = new SM_MonitorView();
		$this->view->setModel($data);
		$this->view->setOp("checks_list");
	}
	
	protected function host_data($records,$type="host"){
		foreach($records as $i=>$monitor_data)
		{						
			$id = $monitor_data['cid'];
			//$state = $this->model->hosts_status($type,array("description"=>$records[$i]['description']));
			//$records[$i]['state']=-1;
			//if(isset($state["Up"]))
			
			if($records[$i]['state']=="UP")
				$records[$i]['state']=1;//$state["Up"];
			else if($records[$i]['state']=="DOWN")
				$records[$i]['state']=0;
			else 
				$records[$i]['state']=-1;
			$action=array();
			if(sm_ACL::checkPermission("Monitor::View"))
			{
				$link = "monitor/configuration/view/".$id."/hosts/"."hid:".$records[$i]['hid'];
				$action['details']=array("title"=>"View details","class"=>"","url"=>$link,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
				//$action['details']=$data['records'][$i]['actions'];
			}
			unset($records[$i]['actions']);
			unset($records[$i]['cid']);
			unset($records[$i]['hid']);
	
			if(!empty($action))
				$records[$i]['actions']=$action; //array_merge($action,$actions);	
		}
		return $records;
	}
	
	protected function device_data($records){
		foreach($records as $i=>$monitor_data)
		{
			$cid = $monitor_data['cid'];
			$state = $this->model->hosts_status("device",array("description"=>$records[$i]['description']));
			$records[$i]['state']=-1;
			if(isset($state["Up"]))
				$records[$i]['state']=$state["Up"];
			$action=array();
			if(sm_ACL::checkPermission("Monitor::View"))
			{
				$link = "monitor/configuration/view/".$cid."/devices/"."did:".$records[$i]['did'];
				$action['details']=array("title"=>"View details","class"=>"","url"=>$link,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
				//$action['details']=$data['records'][$i]['actions'];
			}
			unset($records[$i]['actions']);
			unset($records[$i]['cid']);
			unset($records[$i]['did']);
	
			if(!empty($action))
				$records[$i]['actions']=$action; //array_merge($action,$actions);
		}
		return $records;
	}
	
	protected function build_data($records){
		foreach($records as $i=>$monitor_data)
		{
			
			$conf = SM_Configuration::load($monitor_data['cid']);
			
			$id = $monitor_data['description'];
			$overallStatus= $this->model->overallStatus($conf->getname(),$conf->getidentifier());
		
			$records[$i]=array('name'=>$conf->getname()."<br><small>Contract: ".$conf->getbid()."</small>")+$overallStatus+$records[$i];
		
			//$data['records'][$i]['actions']['details']='<a title="View details" class=configuration_action href="monitor/configuration/view/'.$monitor_data['iid'].'"><img src="img/details.gif" width="16px" height="16px" /></a>';
			$action=array();
			if(sm_ACL::checkPermission("Monitor::View"))
			{
				$action['details']=array("title"=>"View details","url"=>"monitor/configuration/view/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
				//$action['details']=$data['records'][$i]['actions'];
			}
			$d['Monitor Status']="N.A";
				
				
			unset($records[$i]['actions']);
				
				if($monitor_data['status']==-1) //Ready
				{
					$records[$i]['Monitor Status']="<span class='label label-info'>Ready</span>";
					if(sm_ACL::checkPermission("Monitor::Edit"))
					{
						$action['monitor_queue']=array("title"=>"Insert in the start monitor queue","class"=>"","url"=>"monitor/queue/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."queue.gif' />","method"=>"GET");
						$action['monitor_start']=array("title"=>"Start monitor manually","class"=>"","url"=>"monitor/insert/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."on.png' />","method"=>"GET");
					}
				}
	 			else if($monitor_data['status']==0) //Waiting
				{
					$records[$i]['Monitor Status']="<span class='label label-warning'>Waiting</span>";
					/*if(sm_ACL::checkPermission("Monitor::Edit"))
						$action['monitor_start']=array("title"=>"Start monitor manually","class"=>"","url"=>"monitor/insert/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."on.png' />","method"=>"GET");*/
				}
				else if($monitor_data['status']==1)//Monitoring
				{
					$records[$i]['Monitor Status']="<span class='label label-success'>Monitoring</span>";
					if(sm_ACL::checkPermission("Monitor::Edit"))
						$action['monitor_stop']=array("title"=>"Delete monitor","class"=>"confirm","message"=>"Are you sure you want to delete this monitor?","url"=>"monitor/delete/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."off.png' />","method"=>"GET");
				}
				else if($monitor_data['status']==2)//Stopped
				{
					$records[$i]['Monitor Status']="<span class='label label-danger'>Stopped</span>";
					if(sm_ACL::checkPermission("Monitor::Edit"))
						$action['monitor_start']=array("title"=>"Start monitor","class"=>"","url"=>"monitor/start/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."on.png' />","method"=>"GET");
				}
				else if($monitor_data['status']==3)//Failed
				{
					$records[$i]['Monitor Status']="<span class='label label-danger'>Failed</span>";
					if(!empty($monitor_data['errors']))
						$records[$i]['Monitor Status'].=" <i class='sm-icon sm-icon-medium sm-icon-monitor-errors-info' data-toggle='tooltip' data-placement='right' title='".htmlspecialchars($monitor_data['errors'],ENT_QUOTES)."'></i>";
					 
						
					if(sm_ACL::checkPermission("Monitor::Edit"))
					{
						$action['monitor_queue']=array("title"=>"Insert in the start monitor queue","class"=>"","url"=>"monitor/queue/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."queue.gif' />","method"=>"GET");
						$action['monitor_start']=array("title"=>"Start monitor","class"=>"","url"=>"monitor/insert/".$id,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."on.png' />","method"=>"GET");
					}
				}
				else if($monitor_data['status']==4)//Processing
				{
					$records[$i]['Monitor Status']="<span class='label label-default'>Processing</span>";
				/*	if(sm_ACL::checkPermission("Monitor::Edit"))
						$action['monitor_start']=array("title"=>"Start monitor","class"=>"","url"=>"monitor/insert/".$monitor_data['cid'],"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."on.png' />","method"=>"GET");*/
				}
			
			unset($records[$i]['cid']);
			unset($records[$i]['identifier']);
			unset($records[$i]['status']);
			unset($records[$i]['errors']);
		
			if(!empty($action))
				$records[$i]['actions']=$action; 
		
		
		}
		return $records;
	}
	
	protected function checks_data($records){
		foreach($records as $i=>$check)
		{
				$host= new Host();
				$host->select($check['host_alias']);
				$hid=$host->gethid();
				$conf = new Configuration();
				$conf->load($host->getcid());
				$cid = $conf->getdescription();
				if(sm_ACL::checkPermission("Monitor::View"))
				{
					$records[$i]['actions']['details']=array("title"=>"View details","url"=>"monitor/configuration/view/".$cid."/hosts/hid:".$hid,"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
				}
				unset($records[$i]['host_alias']);
		}
		return $records;
	}
	
	/**
	 * @desc Gets the xml representation for a picture of a graph
	 *
	 * @url GET /monitor/xml/:hostname/:ip/:metric/:from/:to/
	 * @url GET /monitor/xml/:hostname/:ip/:metric/:from/
	 * @url GET /monitor/xml/:hostname/:ip/:metric/
	 *
	 * callback
	 */
	public function monitor_img_xml($hostname = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		if(!isset($hostname) && !isset($ip)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			sm_send_error(400);
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
			$xml=$graph->getGraph($args);
			if($xml)
			{
				header('Content-Type: application/xml');
				header('Content-Disposition: attachment; filename="'.$hostname.'_'.$metric.'.xml"');
				echo $xml;
	
			}
			exit();
		}
		
	}
	
	/**
	 * Gets the picture of a graph
	 *
	 * @url GET /monitor/img/:id/:segment/:sid/:ip/:metric/:from/:to/
	 * @url GET /monitor/img/:id/:segment/:sid/:ip/:metric/:from/
	 * @url GET /monitor/img/:id/:segment/:sid/:ip/:metric/
	 * @url GET /monitor/img/:id/:segment/:sid/:ip/
	 * @url GET /monitor/img/:id/:segment/:sid/
	 *
	 */
	public function monitor_img($id = null, $segment = null, $sid = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		if(!isset($id) && !isset($segment)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			sm_send_error(400);
		else
		{
			$configuration=new SM_Configuration($id);
			$segment_obj=$configuration->getSegment($segment,$sid);
			$host_name=$segment_obj[$segment]->getname();
			$ipAddr="";
			if($ip && $ip!='*')
				$ipAddr=$ip;
			else
				$ipAddr=$segment_obj[$segment]->getip_address();
					
			$args=array(
					"host"=>$host_name,
					"ip"=>$ipAddr,
					"start"=>$from,
					"end"=>$to,
					"srv"=>urldecode($metric)
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
			exit();
		}
		
	}
	
	
	public static function monitor_doGraph($id = null, $segment = null, $sid = null, $host_ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		if($segment=="applications" || $segment=="tenants" )
		{
			$host=$segment_obj[$segment]->getMyHost($host_ip);
			if($host)
			{
				$host_name=$host->getname();
				$hid="hid:".$host->gethid();
			}
		}
		else if($segment=="services")
		{
			
			$host=$segment_obj[$segment]->getHost();
			if($host)
			{
				$host_name=$host->getname();
				$hid="hid:".$host->gethid();
			}
		}
		else 
		{
			$host_name=$segment_obj[$segment]->getname(); //.'@'.$host_ip;
		}
		
		$graph = $monitor->graph_data($host_name, $host_ip,urldecode($metric));
		if(!empty($graph))
		{
			$t=time();
		
			$start=$from?$from:$t-(24*3600);
			$end=$to?$to:$t;
			$data=array();
			$data['start_time']=$start;
			$data['end_time']=$end;
			$data["selection"]=$graph['metric'];
			$data['graphs']=array(
					"title"=>$graph['title'],
					"subtitle"=>$graph['subtitle'],
					"start"=>$start,
					"end"=>$end,
					"xmlurl"=>"monitor/xml/".$host_name."/".$host_ip."/".$graph['metric'].":".$graph['submetric'],
					"url"=>"monitor/graph/".$id."/".$segment."/".$sid."/".$host_ip."/".$graph['metric'].":".$graph['submetric'],
					"dashurl"=>"monitor/dashboard/".$id."/".$segment."/".$sid."/".$host_ip."/".$graph['metric'].":".$graph['submetric'],
			);	
			if($segment=="hosts")
				$data['graphs']['graphurl']="monitor/img/".$id."/".$segment."/".$sid."/".$host_ip."/".$graph['metric'].":".$graph['submetric'];
			else 
				$data['graphs']['graphurl']="monitor/img/".$id."/hosts/".$hid."/".$host_ip."/".$graph['metric'].":".$graph['submetric'];
				
			return $data;
		}
		return null;
	}
	
	
	/**
	 * Gets the graph panel
	 *
	 * @url GET /monitor/graph/:id/:segment/:sid/:ip/:metric/:from/:to/
	 * @url GET /monitor/graph/:id/:segment/:sid/:ip/:metric/:from/
	 * @url GET /monitor/graph/:id/:segment/:sid/:ip/:metric/
	 * @url GET /monitor/graph/:id/:segment/:sid/:ip/
	 * @url GET /monitor/graph/:id/:segment/:sid/
	 * 
	 * @callback
	 */
	public function monitor_graph($id = null, $segment = null, $sid = null, $ip=null,$metric="_HOST_", $from = null, $to = null)
	{
		
		$data=$this->monitor_doGraph($id, $segment, $sid, $ip, $metric, $from, $to);
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("graph_view");
		
	}
	
	
	
	/**
	 * Gets structured data for viewing picture of a graph
	 *
	 * @url GET /monitor/graphs/:id/:segment/:sid/:from/:to
	 * @url GET /monitor/graphs/:id/:segment/:sid/:from
	 * @url GET /monitor/graphs/:id/:segment/:sid
	 *
	 * @callback
	 */
	public function monitor_graphs($id,$segment,$sid,$from=null,$to=null)
	{
		if($segment=="hosts" || $segment=="devices")
			return $this->monitor_host_graphs($id,$segment,$sid,$from,$to);
		else
			return $this->monitor_application_graphs($id,$segment,$sid,$from,$to);
	}

	public function monitor_host_graphs($id,$segment,$sid,$from=null,$to=null)
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		$filters=$monitor->getFilters($segment_obj[$segment]);
		$selected=current(array_keys($filters));
		
		if(isset($_GET['ip']) && in_array($_GET['ip'], array_keys($filters)))
			$selected=$_GET['ip'];
		$host_ip=$selected;
		$hid="";
		if(is_a($segment_obj[$segment],"Host"))
		{
			$type="host";
			$name=$segment_obj[$segment]->getname();
			$hid=$sid;
		}
		else if(is_a($segment_obj[$segment],"Device"))
		{
			$type="device";
			$name=$segment_obj[$segment]->getname();
			$hid=$sid;
		}
	
		$monitor = new SM_Monitor();
		$graph_data = $monitor->graph_list($name,$selected,$type);
		//sm_Logger::write($from);
		$data=array();
		$data['type']=$segment;
		$data['sid']=$sid;
		$t=time();
		$start=$from?strtotime(gmdate("M d Y H:i:s", $from)):$t-(24*3600);
		$end=$to?strtotime(gmdate("M d Y H:i:s", $to)):$t;
		$data['start_time']=intval($start);
		$data['end_time']=intval($end);
		
		$data['graphs']=array();
		$boards = SM_MonitorUIDashboardController::monitor_dashboard_load_boards("hosts", $hid, $host_ip);
		if(isset($boards) && count($boards)>0)
		{
			$board=$boards[0];
			$args = unserialize($board->getcallback_args());
			//$data=array();
			if(isset($args["metrics"]["GRAPH"]))
			{
				foreach($args['metrics']["GRAPH"] as $i=>$metric)
				{
						$graph = $this->monitor_doGraph($id, "hosts", $hid, $host_ip, $metric,$start,$end);
						if($graph)
							$data['graphs'][$metric]=$graph['graphs'];
				}
			}
		}
		if(count($data['graphs'])==0)
		{
				$data0 = $this->monitor_doGraph($id,"hosts",$hid,$host_ip,"_HOST_:0",$start,$end);
				$data1 = $this->monitor_doGraph($id,"hosts",$hid,$host_ip,"_HOST_:1",$start,$end);
				if(isset($data0['graphs']))
				{
					SM_MonitorUIDashboardController::monitor_dashboard_add_board($id,"hosts",$hid,$host_ip,"GRAPH","_HOST_:0");
					$data['graphs']["_HOST_:0"]=$data0['graphs'];
				}
				if(isset($data1['graphs']))
				{
					SM_MonitorUIDashboardController::monitor_dashboard_add_board($id,"hosts",$hid,$host_ip,"GRAPH","_HOST_:1");
					$data['graphs']["_HOST_:1"]=$data1['graphs'];
				}
		}
		$data['graphs_menu']=array();
		foreach($graph_data as $k=>$g)
		{
				
			foreach ($g as $i)
			{
				$metricParam=$i['metric'];
				$data['graphs_menu'][$k][]=array(
						"title"=>$i['title'], //str_replace("_"," ",$description),
						"subtitle"=>$i['subtitle'], //str_replace("_"," ",$perf),
						"start"=>$start,
						"end"=>$end,
						"url"=>"monitor/graph/".$id."/hosts/".$hid."/".$host_ip."/".$metricParam,
						"graphurl"=>"monitor/img/".$id."/hosts/".$hid."/".$host_ip."/".$metricParam,
						"dashurl"=>"monitor/dashboard/".$id."/hosts/".$hid."/".$host_ip."/".$metricParam,	
				);			
			}
		}
	
		$data['addresses']=$filters;
		$data['address_selected']=$selected;
		$data['title']=$filters[$selected]==$selected?$filters[$selected]:$filters[$selected]."@".$selected;
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("graphs_view");
	}
	
	public function monitor_application_graphs($id,$segment,$sid,$from=null,$to=null)
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		$filters=$monitor->getFilters($segment_obj[$segment]);
		$selected=current(array_keys($filters));
		if(isset($_GET['ip']) && in_array($_GET['ip'], array_keys($filters)))
			$selected=$_GET['ip'];
		$aid="";
		$graph_data=array();
		if(is_a($segment_obj[$segment],"Application"))
		{
			$type="application";
			$name=$segment_obj[$segment]->getdescription();
			$host=$segment_obj[$segment]->getMyHost($selected);
						
		}
		else if(is_a($segment_obj[$segment],"Tenant"))
		{
			$type="tenant";
			$name=$segment_obj[$segment]->getdescription();
			$host=$segment_obj[$segment]->getMyHost($selected);
		}
		else if(is_a($segment_obj[$segment],"Service"))
		{
			$type="service";
			$name=$segment_obj[$segment]->getdescription();
			$host=$segment_obj[$segment]->getHost();
						
		}
		$monitor = new SM_Monitor();
		$graph_data = $monitor->graph_list($name,$selected,$type);
		$data=array();
		$t=time();
		$start=$from?$from:$t-(24*3600);
		$end=$to?$to:$t;
		$data['start_time']=intval($start);
		$data['end_time']=intval($end);
		$data['type']=$type;
		$data['graphs']=array();
		$boards = SM_MonitorUIDashboardController::monitor_dashboard_load_boards($segment, $sid);
		if(isset($boards) && count($boards)>0)
		{
			$board=$boards[0];
			$args = unserialize($board->getcallback_args());
			//$data=array();
			if(isset($args["metrics"]["GRAPH"]))
			{
				foreach($args['metrics']["GRAPH"] as $i=>$metric)
				{
					$s = explode(":",$metric);
					$host_ip = $s[0];
					unset($s[0]);
					$metric = implode(":",$s);
					if($type=="service")
						$host=$segment_obj[$segment]->getHost();
					else 
						$host=$segment_obj[$segment]->getMyHost($host_ip);
					if($host)
					{
						//$hid="hid:".$host->gethid();
						$graph = $this->monitor_doGraph($id, $segment, $sid, $host_ip, $metric,$start,$end);
					}
					
				
					if($graph)
						$data['graphs'][]=$graph['graphs'];
				}
			}
		}
		
		if($host)
		{
			$hid="hid:".$host->gethid();
			$host_ip=$selected;
		}
		$data['graphs_menu']=array();
		foreach($graph_data as $k=>$g)
		{
			foreach ($g as $i)
			{
				$metricParam=$i['metric'];
				 
				$data['graphs_menu'][$k][]=array(
						"title"=>$i['title'], //str_replace("_"," ",$description),
						"subtitle"=>$i['subtitle'], //str_replace("_"," ",$perf),
						"start"=>$start,
						"end"=>$end,
						"url"=>"monitor/graph/".$id."/".$segment."/".$sid."/".$host_ip."/".$metricParam,
						"graphurl"=>"monitor/img/".$id."/hosts/".$hid."/".$host_ip."/".$metricParam,
						"dashurl"=>"monitor/dashboard/".$id."/".$segment."/".$sid."/".$host_ip."/".$metricParam
				);
	
			}
		}
		$data['title']="Services";
		$data['addresses']=$filters;
		$data['address_selected']=$selected;
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("graphs_view");
	}
	
	
	

	
	/**
	 * Gets structured data for viewing picture of meters
	 *
	 * @url GET /monitor/meters/:id/:segment/:sid
	 * @url GET /monitor/meters/:id/:segment/:sid/:metric
	 *
	 * @callback
	 */
	public function monitor_meters($id,$segment,$sid,$metric='_HOST_')
	{
		$selected="All";
		if(isset($_GET['filter']) && $_GET['filter']!="All")
			$selected=$_GET['filter'];	
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		$filters=$monitor->getFilters($segment_obj[$segment]);
		if(is_a($segment_obj[$segment],"Host"))
		{
			$type="host";
			$name=$segment_obj[$segment]->getname();
		}
		if(is_a($segment_obj[$segment],"Application"))
		{
			$type="application";
			$name=$segment_obj[$segment]->getdescription();
		}
		if(is_a($segment_obj[$segment],"Service"))
		{
			$type="service";
			$name=$segment_obj[$segment]->getdescription();
		}
		foreach($filters as $f=>$v)
		{
			$selection=$f; 
			if($selected!="All" && $selection!=$selected)
				continue;
			$ms=$monitor->meters($name,$selection,$type);
			if(count($ms['meters'])>0)
				$data['meters'][$selection] = $ms;
		}
		$t=time();
		$data['time']=$t;
		$data['filter']=array_merge(array("All"=>"All"), $filters);
		$data['selected']=$selected;
		$data["id"]=$id;
		$data["segment"]=$segment;
		//$data["tpl"]="meters_page";
		
		$data["refreshUrl"]="monitor/refresh/meters/".$id."/".$segment."/".$sid;
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("meters_view");
	}
	
	/**
	 * Gets structured data for refreshing picture of meters
	 *
	 * @url GET /monitor/refresh/meters/:id/:segment/:sid
	 * @url GET /monitor/refresh/meters/:id/:segment/:sid/:metric
	 *
	 * @callback
	 */
	public function monitor_meters_refresh($id,$segment,$sid,$metric='_HOST_')
	{
		$selected="All";
		if(isset($_GET['filter']) && $_GET['filter']!="All")
			$selected=$_GET['filter'];	
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		$filters=$monitor->getFilters($segment_obj[$segment]);
		if(is_a($segment_obj[$segment],"Host"))
		{
			$type="host";
			$name=$segment_obj[$segment]->getname();
		}
		if(is_a($segment_obj[$segment],"Application"))
		{
			$type="application";
			$name=$segment_obj[$segment]->getdescription();
		}
		foreach($filters as $f=>$v)
		{
			$selection=$f;
			if($selected!="All" && $selection!=$selected)
				continue;
			$meters= $monitor->meters($name,$selection,$type);
			if(isset($data['meters']))
				$data['meters']=array_merge($data['meters'],$meters['meters']);
			else
				$data['meters']=$meters['meters'];
		}
		$t=time();
		$data['time']=$t;
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("monitor_cmd");
	}
	
	
	
	/**
	 * Gets monitoring controls panel 
	 *
	 * @url GET /monitor/controls/:id/:segment/:sid
	 * @url GET /monitor/controls/:id/:segment/:sid/:metric
	 *
	 * @callback
	 */
	public function monitor_controls($id,$segment,$sid,$metric='_HOST_')
	{
		$selected="All";
		if(isset($_GET['filter']) && $_GET['filter']!="All")
			$selected=$_GET['filter'];	
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$monitor = new SM_Monitor();
		$filters=$monitor->getFilters($segment_obj[$segment]);
		if(is_a($segment_obj[$segment],"Host"))
		{
			$type="host";
			$name=$segment_obj[$segment]->getname();
			$hid=$sid;
		}
		if(is_a($segment_obj[$segment],"Application"))
		{
			$type="application";
			$name=$segment_obj[$segment]->getdescription();
		}
		if(is_a($segment_obj[$segment],"Service"))
		{
			$type="service";
			$name=$segment_obj[$segment]->getdescription();
		}
		foreach($filters as $f=>$v)
		{
			$host_name=null;
			$selection=$f;
			if($selected!="All" && $selection!=$selected)
				continue;
			$controldata = $monitor->controls($name,$selection,$type);
			if($type=="application")
			{
				$host=$segment_obj[$segment]->getMyHost($selection);
				if($host)
				{
					$hid="hid:".$host->gethid();
					$host_name=$host->getname();
				}
			}
			else if($type=="service")
			{
				$host=$segment_obj[$segment]->getHost();
				if($host)
				{
					$hid="hid:".$host->gethid();
					$host_name=$host->getname();
				}
			}
			if(!isset($controldata['controls'][$selection]))
				continue;
			foreach($controldata['controls'][$selection] as $i=>$v)
			{
				if($type=="application" && isset($host_name))
				{
					$controldata['controls'][$selection][$i]=array('address'=>$host_name)+$controldata['controls'][$selection][$i];
					unset($controldata['controls'][$selection][$i]['host']);
				}
				else 
				{
					$controldata['controls'][$selection][$i]=array('address'=>$v['host'])+$controldata['controls'][$selection][$i];
					unset($controldata['controls'][$selection][$i]['host']);
				}
				$m=$v['check'];
				if(sm_ACL::checkPermission("Monitor::Edit"))
				{
					if($v['active']==1)
					{
						$controldata['controls'][$selection][$i]['status']="<select class='configuration_action monitor_control_cmd'>
								<option class='on' selected value='1' text=\"monitor/start/".$id."/hosts/".$hid."/".$m."?filter=".$selection."\">On</option>
		  						<option class='off' value='0' text=\"monitor/stop/".$id."/hosts/".$hid."/".$m."?filter=".$selection."\">Off</option>
								</select>";
					}
					else
					{
						$controldata['controls'][$selection][$i]['status']="<select class='configuration_action monitor_control_cmd'>
								<option class='on' value='1' text=\"monitor/start/".$id."/hosts/".$hid."/".$m."?filter=".$selection."\">On</option>
		  						<option class='off' selected  value='0' text=\"monitor/stop/".$id."/hosts/".$hid."/".$m."?filter=".$selection."\">Off</option>
								</select>";
					}
				}
				else
				{
					if($v['active'])
						$controldata['controls'][$selection][$i]['status']='<span class="label label-success">On</span>';
					else 
						$controldata['controls'][$selection][$i]['status']='<span class="label label-danger">Off</span>';;
						
				}
				unset($controldata['controls'][$selection][$i]['active']);
			}
			$data['controls'][$selection]=$controldata['controls'][$selection];
		}
		$data['filter']=array_merge(array("All"=>"All"),$filters);
		$data['selected']=$selected;
		$data["id"]=$id;
		$data["sid"]=$sid;
		$data["segment"]=$segment;
		//$data["tpl"]="controls_page";
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("controls_view");
	}
	
	/**
	 * @desc Gets monitoring events panel
	 *
	 * @url GET /monitor/events/:id/:segment/:sid/
	 * @url GET /monitor/events/:id/:segment/:sid/:time
	 *
	 * @callback
	 */
	public function monitor_events($id,$segment,$sid,$time=null)
	{
		if(!isset($id) || !isset($segment) || !isset($sid))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$conf = new SM_Configurator();
		$data_seg=$conf->getConfigurationData($segment, $id, $sid);
		$attr=explode(":",$sid);
		$description=null;
		foreach($data_seg['configuration'][$segment] as $item=>$obj)
		{
			foreach($obj as $i=>$o)
			{
				if($o['@attributes'][$attr[0]]==$attr[1])
				{
					$description=$o['description'];				
					break;
				}
			}
		}
		$data=array();
		if($description){
			$monitor = new SM_Monitor();
			if(!isset($time))
				$time = time()-3600*24;
			$data = $monitor->last_events($segment, array("description"=>$description), 100, $time);
		}
		$data['type']=$segment;
		$data['title']="Last Events";
		$data['id']="Monitor_Events";
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("events_view");
	}
	
	
	
	/**
	 * Gets monitoring stop 
	 *
	 * @url GET /monitor/stop/:id/:segment/:sid
	 * @url GET /monitor/stop/:id/:segment/:sid/:metric
	 *
	 * @callback
	 */
	function monitor_stop($id,$segment,$sid,$metric='_HOST_')
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$ips=explode(";",$segment_obj[$segment]->getip_address());
		if(isset($_GET['filter']))
		{
			$ip_address=$_GET['filter'];
		}
		else
			$ip_address=$ips[0];
		$host_name=$segment_obj[$segment]->getname();//.'@'.$ip_address;
		$monitor = new SM_Monitor();
		$data = $monitor->stop($host_name,$ip_address,$metric);
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("monitor_cmd");
	}
	
	/**
	 * @desc Gets monitoring start
	 *
	 * @url GET /monitor/start/:id/:segment/:sid
	 * @url GET /monitor/start/:id/:segment/:sid/:metric
	 *
	 * @callback
	 */
	function monitor_start($id,$segment,$sid,$metric='_HOST_')
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$ips=explode(";",$segment_obj[$segment]->getip_address());
		if(isset($_GET['filter']))
		{
			$ip_address=$_GET['filter'];
		}
		else
			$ip_address=$ips[0];
		$host_name=$segment_obj[$segment]->getname();//.'@'.$ip_address;
		$monitor = new SM_Monitor();
		$data = $monitor->start($host_name,$ip_address,$metric);
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("monitor_cmd");
	}
	
	/**
	 *@desc Gets an immediate reschedule check
	 *
	 * @url GET /monitor/reschedule/check/:id/:segment/:sid:/metric
	 *
	 * @callback
	 */
	function monitor_reschedule_check($id,$segment,$sid,$metric='_HOST_')
	{
		$configuration=new SM_Configuration($id);
		$segment_obj=$configuration->getSegment($segment,$sid);
		$ips=explode(";",$segment_obj[$segment]->getip_address());
		if(isset($_GET['filter']))
		{
			$ip_address=$_GET['filter'];
		}
		else
			$ip_address=$ips[0];
		$host_name=$segment_obj[$segment]->getname();//.'@'.$ip_address;
		$monitor = new SM_Monitor();
		$data = $monitor->rescheduleCheck($host_name,$ip_address,$metric);
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("monitor_cmd");
	}
	
	/**
	 * @desc Gets data in the queue monitor configurator tool
	 *
	 * @url GET /monitor/queue/:id
	 *
	 */
	function monitor_queue($id=null)
	{
		$result=array();
		if($id==null)
		{
			sm_set_error("Invalid Data");
		}
		else
		{
			$monitor = new SM_Monitor();
			if(preg_match("/SM:/",$id))
				$cid=$monitor->getInternalId($id);
			else if(is_numeric($id))
				$cid=$id;
			else
				$cid=$monitor->getInternalIdbyDescription($id);
			$monitor_data=$monitor->getData(array('iid'=>$cid));
			if($monitor_data['status']==MONITOR_READY || $monitor_data['status']==MONITOR_FAILED)
			{	
				if($monitor->queue($monitor_data['mid']))
				{
					$result['result'][]="Monitor configuration queue insertion success for ".$monitor_data['mid'];
				}
				else 
					$result["error"][]="Error when inserting ".$monitor_data['mid']." in monitor configuration queue!";
			}
			else 
				$result["error"][]="Invalid Monitor Status: configuration cannot be push in the queue!";
				
		
			foreach($result as $k=>$msg)
			{
				if(is_array($msg))
				{
					foreach($msg as $i=>$s)
					{
						if($k=="error")
							sm_set_error($s);
						else
							sm_set_message($s);
					}
				}
			}
			if(isset($result['result']) && isset($monitor_data))
			{
				sm_EventManager::handle(new sm_Event("MonitorQueue",$monitor_data));
			}
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets insert data in the monitoring tool
	 *
	 * @url GET /monitor/insert/:id
	 * @url GET /monitor/insert/
	 *
	 */
	function monitor_insert($id=null)
	{
		$result=array();
		if($id==null)
		{
			sm_set_error("Invalid Data");
		}
		else
		{
			$monitor = new SM_Monitor();
			if(preg_match("/SM:/",$id))
				$cid=$monitor->getInternalId($id);
			else if(is_numeric($id))
				$cid=$id;
			else
				$cid=$monitor->getInternalIdbyDescription($id);
			$monitor_data=$monitor->getData(array('iid'=>$cid));
			if($monitor_data['status']==MONITOR_READY || $monitor_data['status']==MONITOR_FAILED)
				$result = $monitor->insert($cid);
			foreach($result as $k=>$msg)
			{
				if(is_array($msg))
				{
					foreach($msg as $i=>$s)
					{
						if($k=="error")
							sm_set_error($s);
						else
							sm_set_message($s);
					}
				}
			}
			if(isset($result['result']) && isset($monitor_data))
			{
				sm_EventManager::handle(new sm_Event("MonitorInsert",$monitor_data));
			}
		
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	
	/**
	 * Gets insert data in the monitoring tool
	 *
	 * @url GET /monitor/console/:cmd/:id
	 *
	 * @callback
	 */
	function monitor_console($cmd=null,$id=null)
	{
		$data['cmd']=$cmd;
		$data['id']=$id;
		$file="/var/log/SM_NagiosConfigurator.log";
		$data['refreshUrl']="nagios/configurator/daemon/refresh/log/?file=".$file;
		$this->view = new SM_MonitorView($data);
		$this->view->setOp("monitor_console");
	}
	
	/**
	 * Delete configuration from monitoring tool
	 *
	 * @url GET /monitor/delete/:id
	 *
	 */
	function monitor_delete($id=null)
	{
		$result=array();
		if($id==null)
		{
			sm_set_error("Invalid Data");
		}
		else
		{
			$monitor = new SM_Monitor();
			if(preg_match("/SM:/",$id))
				$cid=$monitor->getInternalId($id);
			else if(is_numeric($id))
				$cid=$id;
			else
				$cid=$monitor->getInternalIdbyDescription($id);
			$monitor_data=$monitor->getData(array('iid'=>$cid));
			$result = $monitor->remove($cid);
			foreach($result as $k=>$msg)
			{
				if(is_array($msg))
				{
					foreach($msg as $i=>$s)
					{
						if($k=="error")
							sm_set_error($s);
						else
							sm_set_message($s);
					}
				}
			}
			if(isset($result['result']) && isset($monitor_data))
			{
				sm_EventManager::handle(new sm_Event("DeleteMonitor",$monitor_data));
			}
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	
	
	function onExtendController(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_ConfiguratorUIController")
		{
			$this->extendConfigurator($obj);
		}
	}
	
	function onMonitorEvent(sm_Event &$event)
	{
				
	}
	
	function onDeleteConfiguration(sm_Event &$event)
	{
		$monitor = new SM_Monitor();
		$conf =  $event->getData();
		$id = $conf->getConfiguration()->getcid();
		sm_Logger::write("Deleting monitor instance for ".$id);
		return $monitor->deleteConfigurationMonitor($id);
	}

	
	function extendConfigurator(sm_ControllerElement $obj)
	{
		
		$curView=$obj->getView();
		if(isset($curView))
		{			
			$data=$curView->getModel();
			if($curView->getOp()=="view")
			{
												
					$monitor = new SM_Monitor();
					$data['monitor']=$monitor->getData(array('iid'=>$data['id']));
					if($data['monitor']['status']==0)
					{
						$data['monitor']['status']="Waiting";
						$data['monitor']['label_status']="warning";
					}
					else if($data['monitor']['status']==1)
					{
						$data['monitor']['status']="Monitoring";
						$data['monitor']['label_status']="success";
					}	
					else if($data['monitor']['status']==-1)
					{
						$data['monitor']['status']="Ready";
						$data['monitor']['label_status']="info";
					}
					else if($data['monitor']['status']==2)
					{
						$data['monitor']['status']="Stopped";
						$data['monitor']['label_status']="danger";
					}
					else if($data['monitor']['status']==3)
					{
						$data['monitor']['status']="Failed";
						$data['monitor']['label_status']="danger";
					}
					else if($data['monitor']['status']==4)
					{
						$data['monitor']['status']="Processing";
						$data['monitor']['label_status']="default";
					}
					
					
					$curView->setModel($data);
					return;
				
			}
			
		if($curView->getOp()=='list')
			{
				
				if(!isset($data['records']))
					return;
				$monitor = new SM_Monitor();
			
				foreach($data['records'] as $i=>$d)
				{
					$monitor_data=$monitor->getData(array('iid'=>$d['cid']));
					$d['Monitor Status']="N.A";
					$actions=array();
					if(isset($data['records'][$i]['actions']))
					{
						$actions=$data['records'][$i]['actions'];
						unset($data['records'][$i]['actions']);
					}
					
					
					if($monitor_data['status']==-1) //Waiting
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-info'>Ready</span>";
						
					}
					else if($monitor_data['status']==0) //Waiting
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-warning'>Waiting</span>";
						
					}
					else if($monitor_data['status']==1)//Monitoring
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-success'>Monitoring</span>";
						
					}
					else if($monitor_data['status']==2)//Stopped
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-danger'>Stopped</span>";
						
					}
					else if($monitor_data['status']==3)//Failed
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-danger'>Failed</span>";
						
					}
					else if($monitor_data['status']==4)//Failed
					{
						$data['records'][$i]['Monitor Status']="<span class='label label-default'>Processing</span>";
					
					}
			
					
					if(sm_ACL::checkPermission("Monitor::View"))
						$action['monitor']=array("title"=>"View monitor data","class"=>"","url"=>"monitor/configuration/view/".$d['description'],"data"=>"<img src='".SM_IcaroApp::getFolderUrl("img")."monitor.png' />","method"=>"GET");
					if(!empty($action))
						$data['records'][$i]['actions']=array_merge($action,$actions);
				}
				$curView->setModel($data);
				return;
			}
		
		}
		
	}
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET sla/monitor/configuration/segment/view/:id
	 *
	 * @callback
	 */
	function monitor_sla_configuration($id=null)
	{
		$sla = new SM_SLAController();
		$sla->sla_configuration($id);
		$this->view=$sla->getView();
	}
	
}

