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

class HLM_UIController extends sm_ControllerElement
{

	function __construct()
	{
		$this->model=new HLM();
	}
	
	/**
	 * @desc Set redirection after POST
	 *
	 * @url POST HLM/page
	 *
	 */
	function HLM_page_post()
	{
	
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the HLM page
	 *
	 * @url GET HLM/page
	 * 
	 */
	function HLM_page($id=null)
	{	
	 	$data=array();
	 	$keywords="";
		$timestamp=null;
		$timestampTo=time();
		$where=null;
		if(isset($_SESSION['HLM/page']['search']))
			$keywords=$_SESSION['HLM/page']['search'];
		$where=array();
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(hostname like '%".$k."%' OR metric like '%".$k."%' OR dependsOn like '%".$k."%')";
			}
		
		}	
		if(isset($_SESSION['HLM/page']['timestamp']) && !empty($_SESSION['HLM/page']['timestamp']))
		{
			
			$timestamp=$_SESSION['HLM/page']['timestamp']; 
			$where["registration"]="unix_timestamp(registration) >= ".$timestamp;
			//$timestamp=date("d-m-Y H:i",$timestamp);
		}
		if(isset($_SESSION['HLM/page']['timestampTo']) && !empty($_SESSION['HLM/page']['timestampTo']))
		{
			sm_Logger::write($timestampTo);
			$timestampTo=$_SESSION['HLM/page']['timestampTo'];
			$where["registrationTo"]="unix_timestamp(registration) <= ".$timestampTo;
			//$timestampTo=date("d-m-Y H:i",$timestampTo);
		}
		if(count($where)>0)
			$where="where ".implode(" AND ",$where);
		
		$_totalRows=$this->model->getAllCount($where);
	 	$pager = new sm_Pager("HLM/page");
	 	$pager->set_total($_totalRows);
	 	
	 	$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
	 	foreach($data['records'] as $i=>$v)
	 	{
	 		$val=$data['records'][$i]['value'];
	 		$data['records'][$i]['value']=number_format($val,2,",",".");
	 		
	 		/*$data['records'][$i]['actions']['details']='<a title="View details" class=configuration_action href="configuration/view/'.$v['cid'].'"><img src="img/details.gif" width="16px" height="16px" /></a>';
	 		$data['records'][$i]['actions']['delete']='<a title="Delete configuration" class=configuration_action href="configuration/delete/'.$v['cid'].'"><img src="img/delete.png" width="16px" height="16px" /></a>';*/
	 	}
	 	
	 	//create the nav menu
	 	
	 	//$data['type_selector']=$type;
	 	$data['pager'] = $pager;
	 	$data['timestamp']=$timestamp;
	 	$data['timestampTo']=$timestampTo;
	 	
	 	
	 	$data['keywords']=$keywords;
		$this->view = new HLM_View ( $data );
		$this->view->setOp("list");
	}
	
	/**
	 * @desc Gets the Installatinon HLM page
	 *
	 * @url GET HLM/install
	 *
	 */
	function HLM_install()
	{
		$data=array();
		
		
		$conf=sm_Config::instance()->conf;
		$data=array();
		foreach($conf as $c=>$p)
		{
			if(strpos($p['module'],"HLM")===FALSE)
				continue;
			$p['name']=$c;
			$data[$p['module']][]=$p;
		}
		$this->view = new HLM_View ( $data );
		$this->view->setOp("install");
	}
	
	/**
	 * @desc Do the  HLM DB Installatinon
	 *
	 * @url POST HLM/install
	 *
	 */
	function HLM_install_data($data)
	{
		unset($data['form']);
		if(sm_Config::instance()->save($data))
		{
			
			sm_set_message("HLM Settings successfully saved!");
			$hlm = new HLM();
			$hlm->createDatabase();
		}
		else
			sm_set_error("Error when saving HLM Settings");
	
	}
	
	
	/**
	 * @desc Delete a metric from db
	 *
	 * @url POST HLM/delete
	 *
	 * @callback
	 */
	function HLM_delete($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
			
			$value = $this->model->deleteHLMetric($_id[0])?true:false;
	
		}
		else
			$value = false;
		$this->view=new HLM_View($value);
		$this->view->setOp("response");
	}
	
	/**
	 * @desc Delete a metric from db
	 *
	 * @url POST HLM/remove/:id
	 *
	 */
	function HLM_delete_metric($id=null)
	{
		if(isset($id) && is_numeric($id))
		{
			if ($this->model->deleteHLMetric($id))
				sm_set_message("HLM: Metric #".$id." deleted successfully!");
			else
				sm_set_error(sm_Database::getInstance()->getError());
	
		}
		else
			sm_set_error("Invalid data");
	
		sm_app_redirect($_SERVER['HTTP_REFERER']);
	}

	
	public function extendSettingsController(sm_ControllerElement $obj)
	{
		if(is_object($obj) )
		{
			if(get_class($obj)=="SM_SettingsController")
			{
				$curView=$obj->getView();
				if($curView)
				{
					$conf=$curView->getModel()->conf;
					$data=$curView->getData(); //configuration_entries
					if(is_array($data))
					{
						foreach($conf as $c=>$p)
						{
							if(strpos($p['module'],"HLM")===FALSE)
								continue;
							$p['name']=$c;
							$data[$p['module']][]=$p;
						}
						$curView->setData($data);
					}
				}
			}
		}
	}
	
	public function extendMonitorUIController(sm_ControllerElement $obj)
	{
		if(is_object($obj) )
		{
				$curView=$obj->getView();
				if($curView && $curView->getOp()=="graphs_view")
				{
					$model=$curView->getModel();
					if($model['type']=="hosts" && isset($model['sid']))
					{
						$hid=explode(":",$model['sid']);
						$host = new Host();
						$host->select($hid[1]);
						if($host->gettype()=="HLMhost")
						{
							unset($model['graphs']["_HOST_:0"]);
							unset($model['graphs']["_HOST_:1"]);
							unset($model['graphs_menu']['_HOST_']);
							$curView->setModel($model);
						}
					}
					
				}
			
		}
	}
	
	
	
	function onExtendController(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_SettingsController")
		{
			$this->extendSettingsController($obj);
			return;
		}
		if(get_class($obj)=="SM_MonitorUIController")
		{
			$this->extendMonitorUIController($obj);
			return;
		}
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
	
		//HLM Local Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("Monitor");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("HLM");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("hlm_local_dashboard");
		$dboard->add($board);
			
		//Overall HLM Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("HLM");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("HLM Health");
		$board->setmethod("hlm_overall_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
	
		$dboard->add($board);
	
	
	}
	
	public function hlm_overall_dashboard(sm_Board $board)
	{
		$data=array();
		$hlm = new HLM();
		$seg = $board->getsegment();
		$interval=sm_Config::get("MONITORDASHBOARD_EVENTS_INTERVAL",4);
		$data['callback']['args']['data']['id']=$seg;
		$data['callback']['args']['data']['title']=$board->gettitle(); //." of recent ".$interval." hour(s)";
		$data['callback']['args']['data']['checks']=array();
		$s=strtotime(date('Y-m-d 00:00:00',time()));
		$metrics = $hlm->last_events(10,$s);
		if(isset($metrics))
			$data['callback']['args']['data']['metrics']=$metrics;
	
		$data['callback']['class']="HLM_View";
		$data['callback']['method']="dashboard_overall";
		return $data;
	}
	
	public function hlm_local_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$conf = SM_Configuration::load($id);
		$name=$conf->getname();
		$id=$conf->getidentifier();
		$monitor = new SM_Monitor();
		$data=array();
		$metrics = $monitor->meters($name,$id,"hosts","HLM Metrics Collector Host");
			//	$conf = SM_Configuration::load($id);	
		if(isset($metrics['meters']) && count($metrics['meters'])>0)
			$data['callback']['args']['data']['metrics']['hosts']=$metrics['meters'];
		
		$metricsservices = $monitor->meters($name,$id,"hosts","HLM Metrics Collector Service");
		if(isset($metricsservices['meters']) && count($metricsservices['meters'])>0)
			$data['callback']['args']['data']['metrics']['services']=$metricsservices['meters']; 
		
		// TODO hlm computing
		$data['callback']['args']['data']['id']=$conf->getdescription();
		$data['callback']['args']['hlm']=array();
		$data['callback']['class']="HLM_View";
		$data['callback']['method']="dashboard_local";
	
		return $data;
	}
}