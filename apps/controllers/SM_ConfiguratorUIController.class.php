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

class SM_ConfiguratorUIController extends sm_ControllerElement
{
	protected $model;

	function __construct(){
		$this->model=new SM_Configurator();
		$this->view= new SM_ConfiguratorView();
	}

	/**
	 * @desc Gets the configurator queue table
	 *
	 * @url GET /configurator/queue
	 * 
	 */
	function configurator_queue()
	{
		$_totalRows=$this->model->getQueue()->getAllCount();
		$pager = new sm_Pager("configurator/queue");
		$pager->set_total($_totalRows);
		//calling a method to get the records with the limit set
		$data['records'] = $this->model->getQueue()->getAll( $pager->get_limit() );
		foreach($data['records'] as $r=>$v){ //var_dump($v);
			$mid=$v['mid'];
			$cid=$this->model->getMonitor()->getInternalId($mid);
			if(!empty($cid))
				$data['records'][$r]['actions']='<a title="View details" class=configuration_action href="configuration/view/'.$cid.'"><img src="img/details.gif" width="16px" height="16px" /></a>';
			else
				$data['records'][$r]['actions']="";
			unset($data['records'][$r]['data']);
		}		
		$data['pager'] = $pager;
		$this->view->setModel($data);
		$this->view->setOp("queue_list");
	}
	
		
	/**
	 * @desc Gets the configurations summary table
	 *
	 * @url GET /configurator/configuration
	 * 
	 */
	function configurator_configuration()
	{
		
		$type="All";
		$keywords="";
		if(isset($_SESSION['configurator/configuration']['type']))
			$type=$_SESSION['configurator/configuration']['type'];
		if(isset($_SESSION['configurator/configuration']['keywords']))
			$keywords=$_SESSION['configurator/configuration']['keywords'];
		$where=array();
		if($keywords!="")
		{
			$keys = explode(" ",$keywords);
			foreach($keys as $k){
				if($k!="")
					$where[$k]="(name like '%".$k."%' OR description like '%".$k."%' OR bid like '%".$k."%')";
			}
				
		}
		//calling a method to get the records with the limit set
		
		if($type!="All")
			$where['type']="type='".$type."'";
		if(count($where)>0)
			$where="where ".implode(" AND ",$where);
		$_totalRows=$this->model->getConfiguration()->getAllCount($where);
		$pager = new sm_Pager("configurator/configuration");
		$pager->set_total($_totalRows);
		
		$data['records'] = $this->model->getConfiguration()->getAll( $pager->get_limit(),$where);
		foreach($data['records'] as $i=>$v)
		{	
			$id = $v['description'];
			if(sm_ACL::checkPermission("Configuration::View"))
				$data['records'][$i]['actions']['details']=array("title"=>"View details","class"=>"","url"=>"configuration/view/".$id,"data"=>"<img src='img/details.gif' />","method"=>"GET");
			if(sm_ACL::checkPermission("Configuration::Edit"))
				$data['records'][$i]['actions']['delete']=array("title"=>"Delete configuration","class"=>"confirm","message"=>"Are you sure you want to delete this configuration?","url"=>"configuration/delete/".$id,"data"=>"<img src='img/delete.png' />","method"=>"GET");
		}
		
		//create the nav menu
		if(sm_ACL::checkPermission("Configuration::Edit"))
			$data['commands']['DeleteTBWSelectedConfigurations']=array('name'=>'DeleteTBWSelectedConfigurations','data-confirm'=>'Are you sure you want to delete this selection?','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
		$data['type_selector']=$type;
		$data['keywords']=$keywords;
		$data['pager'] = $pager;
		//$data["tpl"]="configuration_entries";
		//$this->view->setTemplateId($data["tpl"]);
		$this->view->setModel($data);
		$this->view->setOp("list");
	}
	
	/**
	 * @desc Set the configuration table limit per page
	 *
	 * @url POST /configurator/configuration
	 * 
	 */
	function configurator_configuration_limit_type()
	{

		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the delete of configuration
	 *
	 * @url POST /configuration/delete
	 * 
	 * @callback
	 *
	 */
	function configurator_configuration_remove($id=null)
	{
		if(isset($id))
		{
			$_id = array_keys($id);
			if(preg_match("/SM:/",$_id[0]))
				$cid=$this->model->getMonitor()->getInternalId($_id[0]);
			else if(is_numeric($_id[0]))
				$cid=$_id[0];
			else
				$cid=$this->model->getMonitor()->getInternalIdbyDescription($_id[0]);
			sm_EventManager::handle(new sm_Event("DeleteConfiguration",new SM_Configuration($cid)));
			$status = $this->model->remove($cid);
			$this->view=new SM_ConfiguratorView($status);
			$this->view->setOp("response");
		}
	}
	/**
	 * @desc Gets the delete of configuration
	 *
	 * @url GET /configuration/delete/:id
	 *
	 */
	function configurator_configuration_delete($id=null)
	{
		if($id)
		{
			if(preg_match("/SM:/",$id))
				$cid=$this->model->getMonitor()->getInternalId($id);
			else if(is_numeric($id))
				$cid=$id;
			else
				$cid=$this->model->getMonitor()->getInternalIdbyDescription($id);
			
			sm_EventManager::handle(new sm_Event("DeleteConfiguration",new SM_Configuration($cid)));
			$status = $this->model->remove($cid);
			
			if($status)
				sm_set_message("Configuration removed successfully!");
			else
				sm_set_error("An error occurred when deleting configuration!");
		}
		else
			sm_set_error("Invalid request");
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the view page of a configuration
	 *
	 * @url GET /configuration/view/:id
	 *  
	 */
	function configurator_configuration_page($id=null)
	{
		
		if(!isset($id))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data=$this->model->getConfigurationData("header", $id);
		if(!isset($data))
		{
			sm_View::instance()->unregister($this->view);
			sm_set_error("ERROR: Configuration ".$id." not found or deleted");
			sm_send_error(400);
			return;
		}
		$data['id']=$data['configuration']["@attributes"]['cid'];	
		//$data["tpl"]="configuration_page";
		$data['menu']=array();
		$data['menu']["History"]=array(
				"url"=>"configuration/history/".$data['configuration']['description'],
				"title"=>"History",
				"icon"=>"sm-icon sm-icon-history"
		);
		$data['xmlUrl']="configuration/xml/".$data['configuration']['description'];
		//$this->view->setTemplateId($data["tpl"]);
		$this->view->setModel($data);
		//$this->view->setData($uidata);
		$this->view->setOp("view");
	}
	
	/**
	 * @desc Gets the history page of a configuration
	 *
	 * @url GET configuration/history/data/:id/:time
	 * 
	 * @callback
	 */
	function configuration_history_data($id=null,$time=null)
	{
		$configuration= new SM_Configuration($id);
		$mid=$this->model->getMonitor()->getMonitorIdbyDescription($configuration->getdescription());
		$where['mid']=$mid;
		if(isset($time))
			$where['arrival_time']=$time;
		$result=$this->model->getQueue()->getInfo("data", $where);
		$content="Content not available";
		if(isset($result['data']))
		{
			$content = $result['data'];
		}
		//$data['tpl']="configuration_data_dlg";
		$data['data']=$content;
		//$this->view->setTemplateId($data["tpl"]);
		$this->view->setModel($data);
		$this->view->setOp("history_dlg");
	}
	
	/**
	 * @desc Gets the history page of a configuration
	 *
	 * @url GET /configuration/history/:id
	 * 
	 * @callback
	 *
	 */
	function configurator_configuration_history($id=null)
	{
	
		if(!isset($id))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$configuration= new SM_Configuration($id);
		$mid=$this->model->getMonitor()->getMonitorIdbyDescription($configuration->getdescription());
		$_totalRows=$this->model->getQueue()->getAllCount(array('mid'=>$mid));
		$pager = new sm_Pager("configuration/history/".$id);
		$pager->set_total($_totalRows);
		$data['records']=$this->model->getQueue()->getAll($pager->get_limit(),array('mid'=>$mid));//getQueueData("*", $id);
		foreach($data['records'] as $d=>$v)
		{
			if($data['records'][$d]['method']=='POST')
				$data['records'][$d]['method']="Insert";
			else if($data['records'][$d]['method']=='PUT')
				$data['records'][$d]['method']="Update";
			$time=strtotime($data['records'][$d]['timestamp']);
			
			//$data['records'][$d]['actions']='<a title="View Data Sent" class=configuration_action href="javascript:cView_dlg_open(\'configuration/history/data/'.$id.'/'.$time.'\')"><img src="img/details.gif" width="16px" height="16px" /></a>';
			
			$data['records'][$d]['actions']='<a title="View Data Sent" data-toggle="modal" data-target="#DataModal" class="button configuration_action" href="configuration/history/data/'.$id.'/'.$time.'"><img src="img/details.gif" width="16px" height="16px" /></a>';
			
			if($data['records'][$d]['data']=="")
				$data['records'][$d]['actions']="";
			unset($data['records'][$d]['data']);
		}
		$data['pager']=$pager;
		$data['id']=$id;
		$data['icon']=SM_IcaroApp::getFolderUrl("img")."history.gif";
		$this->view->setModel($data);
		$this->view->setOp("history_view");
	}
	
	/**
	 * @desc Set the configuration history limit per page
	 *
	 * @url POST /configurator/history
	 * 
	 */
	function configurator_history_limit()
	{
		if(isset($_POST['configurator_history_limit']))
			$_SESSION['configurator/history']['limit']=$_POST['configurator_history_limit'];
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Gets the info page of a configuration
	 *
	 * @url GET /configuration/info/:id
	 *
	 */
	function configurator_configuration_info($id=null)
	{
	
		if(!isset($id))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data=$this->model->getConfigurationData("header", $id);
		$data['id']=$id;
		//$data["tpl"]="configuration_info";
		//$this->view->setTemplateId($data["tpl"]);
		$this->view->setModel($data);
	}
	
	
	/**
	 * @desc Gets the xml for a configuration by $id
	 *
	 * @url GET /configuration/xml/:id
	 * 
	 * @callback
	 */
	function configurator_configuration_xml($id=null)
	{
		
		if(!isset($id))
		{
			//sm_set_message("Invalid request!");
			return;
		}
		$configuration= new SM_Configuration($id);
		if($configuration->gettype()=="System")
			$configuration->addRelations("hosts","Host");
		
		$data=array('configuration'=>$configuration->build("*"));
		/*$data=$this->model->getConfigurationData("*", $id);*/
		$data['configuration']['@attributes']['url']="configuration/view/";
		
		$this->view = new SM_ConfiguratorView($data);
		$this->view->setOp("xml");
	}
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET /configuration/view/:id/:type
	 * 
	 * @callback
	 */
	function configurator_segment_entries($id=null, $type=null)
	{
		if(!isset($id) || !isset($type))
		{
			sm_set_message("Invalid request!");
			return;
		}		
		$data=$this->model->getConfigurationData($type, $id);
		$_data=array();
		foreach($data['configuration'][$type] as $item=>$obj)
		{
				foreach($obj as $i=>$v)
				{					
					if(isset($v['ip_address']) && is_array($v['ip_address']))
						$data['configuration'][$type][$item][$i]['ip_address']=implode(",",$v['ip_address']);
					if($item=="host")
						$_sid="hid:".$v['@attributes']['hid'];
					else if($item=="service")
						$_sid="sid:".$v['@attributes']['sid'];
					else if($item=="device")
						$_sid="did:".$v['@attributes']['did'];
					else if($item=="application")
						$_sid="aid:".$v['@attributes']['aid'];
					else if($item=="tenant")
						$_sid="tid:".$v['@attributes']['tid'];
					$data['configuration'][$type][$item][$i]['actions']='<a title="View details" class=configuration_action href="javascript:cView_open(\'configuration/view/'.$id.'/'.$type.'/'.$_sid.'\')"><img src="img/details.gif" width="16px" height="16px" /></a>';
				}
			
		}
		
		$_data['segments']=$data['configuration'][$type];			
		$_type=array_keys($_data['segments']);
		$_data['segment_type']=$type;
		$_data['type']=$_type[0];
		$_data['cid']=$id;
		$_data['description']=$data['configuration']["description"];
		$this->view->setOp("segment::list");
		$this->view->setModel($_data);		
		
	}
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET /configuration/view/:id/:type/:sid
	 *
	 * @callback
	 */
	function configurator_segment_entry($id=null, $type=null, $sid=null )
	{
		if(!isset($id) || !isset($type))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data=$this->model->getConfigurationData($type, $id, $sid);
		$attr=null;
		$segment=null;
		$_data=array();
		$hasMonitorInfo=false;
		$attr=explode(":",$sid);
		foreach($data['configuration'][$type] as $item=>$obj)
		{
			foreach($obj as $i=>$o)
			{
				if($o['@attributes'][$attr[0]]==$attr[1])
				{
					$_data['id']=$o['id'];
					if(isset($o['ip_address']) && is_array($o['ip_address']))
						$o['ip_address']=implode(",",$o['ip_address']);
					$hasMonitorInfo=isset($o['monitor_info']['metrics']['metric']);
					$segment[$item][]=$o;
					break;
				}
			}
		}
		
		$_data['segments']=$segment;
		$_type=array_keys($_data['segments']);
		$_data['segment_type']=$type;
		$_data['type']=$_type[0];
		$_data['cid']=$id;
		$_data['description']=$data['configuration']["description"];
		$_data['sid']=$sid;
		$_data['menu']["Info"]=array(
						
					'id'=>"Info",
					'title'=>'Info',
					'url'=>"configuration/info/".$_data['description']."/".$type."/".$sid,
					//'link_attr'=>"role=menu"
					//'class'=>'active'
			);
		if($hasMonitorInfo)
		{
			$_data['menu']["Metrics"]=array(
						
					'id'=>"Metrics",
					'title'=>'Custom Metrics',
					'url'=>"configuration/metrics/".$_data['description']."/".$type."/".$sid,
					//'link_attr'=>"role=menu"
					//'class'=>'active'
			);
		}
		
		$this->view->setOp("segment::view");
		$this->view->setModel($_data);
	
	}
	
	/**
	 * @desc Gets the info for a configuration  
	 *
	 * @url GET /configuration/info/:id/:type/:sid
	 * @url GET /configuration/info/:id/:type
	 *
	 * @callback
	 */
	function configurator_segment_info($id=null, $type=null, $sid=null )
	{
		if(!isset($id) || !isset($type))
		{
			sm_set_message("Invalid request!");
			return;
		}	
		$data=$this->model->getConfigurationData($type, $id,$sid);
		$attr=null;
		$segment=null;
		$_data=array();
		if(isset($sid))
		{
			$attr=explode(":",$sid);
			foreach($data['configuration'][$type] as $item=>$obj)
			{
				foreach($obj as $i=>$o)
				{
					if($o['@attributes'][$attr[0]]==$attr[1])
					{
						$_data['id']=$o['id'];
						if(isset($o['ip_address']) && is_array($o['ip_address']))
							$o['ip_address']=implode(",",$o['ip_address']);
						$segment[$item][]=$o;
						break;
					}
				}
			}
		}
		else
		{
			foreach($data['configuration'][$type] as $item)
			{
				$data['configuration'][$type][$item]['actions']="";
				if(is_array($data['configuration'][$type][$item]['ip_address']))
					$data['configuration'][$type][$item]['ip_address']=implode(",",$data['configuration'][$type][$item]['ip_address']);
			}
		}
	
		if($segment)
		{
			$_data['segments']=$segment;
		}
		else
		{
			$_data['segments']=$data['configuration'][$type];
		}
	
		$_type=array_keys($_data['segments']);
		$_data['segment_type']=$type;
		$_data['type']=$_type[0];
		$_data['cid']=$id;
		$_data['sid']=$sid;
		//$_data["tpl"]="segment_info";
		//$this->view->setTemplateId($_data["tpl"]);
		$this->view->setModel($_data);
		$this->view->setOp("segment::info");
	}
	
	/**
	 * @desc Gets the custom metric for a configuration segment 
	 *
	 * @url GET /configuration/metrics/:id/:type/:sid
	 *
	 * @callback
	 */
	function configurator_segment_metrics($id=null, $type=null, $sid=null )
	{
		if(!isset($id) || !isset($type) || !isset($sid))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data=$this->model->getConfigurationData($type, $id,$sid);
		$attr=null;
		$metrics=null;
		$_data=array();
		$attr=explode(":",$sid);
		foreach($data['configuration'][$type] as $item=>$obj)
		{
			foreach($obj as $i=>$o)
			{
				if($o['@attributes'][$attr[0]]==$attr[1])
				{
					$metrics=$o['monitor_info']['metrics']['metric'];
					break;
				}
			}
		}
		
		$_data['metrics']=$metrics;
		$_data['sid']=$sid;
		$this->view->setModel($_data);
		$this->view->setOp("segment::metrics");
	}
	
	/**
	 * @desc Test if the configuration (urn, id) was updated
	 *
	 * @url GET /configuration/isChanged/:urn/:id
	 *
	 * @callback
	 */
	function configuration_is_changed($urn=null,$id=null)
	{
		
		$isChanged = null;
		if($urn && $id)
		{
			$configuration = $this->model->getConfigurationData("header", $urn);
			if($configuration)
			{
				$currentId = $configuration['configuration']["@attributes"]['cid'];
				$isChanged = $currentId==$id?null:$currentId;
			}
			
		}
		$this->view = new SM_ConfiguratorView();
		$this->view->setModel($isChanged);
		$this->view->setOp("response");
	
	}
	
	
	/***************************** STATIC METHODS **************************/
	
	static function install($db)
	{
		if(class_exists("sm_Board") && class_exists("sm_DashboardManager"))
		{
			$board = new sm_Board();
			$dboard= new sm_DashboardManager();
			sm_Logger::write("Removing Existing board from dashboard");
			$dboard->delete(array("module"=>__CLASS__));
			
			sm_Logger::write("Installing board into system dashboard");
			
			$board->setsegment("Configurator");
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Configurations");
			$board->setcallback_args(serialize(array()));
			$board->setview_name("system");
			$board->setmethod("dashboard_system");
			$dboard->add($board);
			
			sm_Logger::write("Installing boards into overall dashboard");
			$board = new sm_Board();
			$board->setweight(10);
			$board->setsegment("Configurator");
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Configurations");
			$board->setcallback_args(serialize(array()));
			$board->setview_name("overall");
			$board->setmethod("dashboard_overall");
			$dboard->add($board);
			
			$board = new sm_Board();
			$board->setsegment("Hosts");
			$board->setweight(20);
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Hosts");
			$board->setcallback_args(serialize(array()));
			$board->setview_name("overall");
			$board->setmethod("dashboard_overall");
			$dboard->add($board);
			
			$board = new sm_Board();
			$board->setsegment("vHosts");
			$board->setweight(30);
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Virtual Machines");
			$board->setcallback_args(serialize(array()));
			$board->setview_name("overall");
			$board->setmethod("dashboard_overall");
			$dboard->add($board);
		}
	}
	
	static function uninstall($db)
	{
		if(class_exists("sm_DashboardManager"))
		{
			$dboard= new sm_DashboardManager();
			sm_Logger::write("Removing Existing boards from dashboard");
			$dboard->delete(array("module"=>__CLASS__));
		}
	}
	
	
	
	/************************ DASHBOARD Callback *************************/
	
	public function dashboard_system(sm_Board $board)
	{
		$_data=array();
		$_totalRows=$this->model->getQueue()->getAllCount();
		$record = $this->model->getQueue()->getAll( "LIMIT 1" );
		
		$tool_data=array();
		
		if(isset($record[0]))
			$tool_data=array_merge($tool_data,$record[0]);
		$tool_data['total']=$_totalRows;
		$tool_data['id']="ConfiguratorQueue";
		$_data['queue'][]=$tool_data;
		
		$record = $this->model->getConfiguration()->getAll( "LIMIT 1" );
		$_totalRows=$this->model->getConfiguration()->getAllCount();
		$tool_data=array();
		
		if(isset($record[0]))
			$tool_data=array_merge($tool_data,$record[0]);
		$tool_data['total']=$_totalRows;
		$tool_data['id']="Configurations";

		$_data['last'][]=$tool_data;

		$data['callback']['args']['data']=$_data;
		$data['callback']['args']["tpl"]['tpl']="configurator_status_dashboard";
		$data['callback']['args']["tpl"]["path"]="apps/templates/configurator.tpl.html";
		$data['callback']['class']="SM_ConfiguratorView";
		$data['callback']['method']="dashboard_system";
		$data['callback']['args']["css"]['file']="configurator.css";
		$data['callback']['args']['css']['path']="apps/css/";
		return $data;
	}
	
	public function dashboard_overall(sm_Board $board)
	{
		$_data=array();
		
		$seg = $board->getsegment();
		$_data['id']=$seg;
		$_data['title']=$board->gettitle();
		if($seg =="Configurator")
		{
			$record = $this->model->getConfiguration()->getAll( "LIMIT 1" );
			$_totalBusiness=$this->model->getConfiguration()->getAllCount(array("type"=>"Business"));
			$_totalSystem=$this->model->getConfiguration()->getAllCount(array("type"=>"System"));
			$_totalRows=$this->model->getConfiguration()->getAllCount();
			
			$_data['values']['Total']=$_totalRows;
			$_data['values']['Business']=$_totalBusiness;
			$_data['values']['System']=$_totalSystem;
			$_data['values']['Latest']=$record;
			$_data['values']['page']="configurator/configuration";
			$data['callback']['args']['data']=$_data;
			$data['callback']['args']["tpl"]['tpl']="configurator_overall_dashboard";
		}
		else if($seg =="Hosts")
		{
			$monitor=$this->model->getMonitor();
			$status = $monitor->hosts_status();
			$_data['values']['Total']=Host::getAllCount(array("type"=>'host'));
			$_data['values']['Up']=isset($status['Up'])?$status['Up']:0;
			$_data['values']['Down']=isset($status['Down'])?$status['Down']:0;
			$_data['values']['page']="monitor/hosts";
			$data['callback']['args']['data']=$_data;
			$data['callback']['args']["tpl"]['tpl']="hosts_overall_dashboard";
		}
		else if($seg =="vHosts")	
		{
			$monitor=$this->model->getMonitor();
			$status = $monitor->hosts_status("vmhost");
			$_data['values']['Total']=Host::getAllCount(array("type"=>'vmhost'));
			$_data['values']['Up']=isset($status['Up'])?$status['Up']:0;
			$_data['values']['Down']=isset($status['Down'])?$status['Down']:0;
			$_data['values']['page']="monitor/vmhosts";
			$data['callback']['args']['data']=$_data;
			$data['callback']['args']["tpl"]['tpl']="vhosts_overall_dashboard";
		}	

		$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."configurator.tpl.html";
		$data['callback']['class']="SM_ConfiguratorView";
		$data['callback']['method']="dashboard_overall";
		/*$data['callback']['args']["css"]['file']="configurator.css";
		$data['callback']['args']['css']['path']=SM_IcaroApp::getFolder("css");*/
		return $data;
	}
}