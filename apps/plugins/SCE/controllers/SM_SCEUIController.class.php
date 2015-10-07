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

class SM_SCEUIController extends sm_ControllerElement
{
	
	protected $model;
	
	function __construct(){
		$this->model =new SM_SCE();
		$this->view = new SM_SCEView();
	}
	
	/**
	 * @desc Gets the info page of a configuration
	 *
	 * @url GET sce/sla/alarms/configuration/:id
	 *
	 * @callback
	 */
	function sla_alarms($id=null)
	{
		if(!isset($id))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data['alarms']=$this->model->getAll("limit 10",array("cid"=>$id));
		$data['id']=$id;
		$this->view->setOp("sla::alarms");
		$this->view->setModel($data);
	}
	
	/**
	 * @desc Gets the delete of configuration
	 *
	 * @url POST /sce/delete/event
	 *
	 * @callback
	 *
	 */
	function sce_event_remove($id=null)
	{
		if(isset($id))
		{
			$_id = array_keys($id);
			$eid=$_id[0];
			$alarm = new SM_SCEAlarm();
			$status = $alarm->delete($eid);
			$this->view=new SM_SCEView($status);
			$this->view->setOp("response");
		}
	}
	
	function onDeleteConfiguration(sm_Event &$event)
	{
		$sce = new SM_SCE();
		$conf = $event->getData();
		$id = $conf->getConfiguration()->getdescription();
		sm_Logger::write("Deleting SCE data for ".$id);
		return $sce->deleteConfigurationData($id);
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
			$this->extendSLAController($obj);
			return;
		}
	
		
	}
	
	
	
	function extendSLAController(sm_ControllerElement $obj)
	{
		$curView=$obj->getView();
		if($curView)
		{
			if($curView->getOp()=="sla::view")
			{
				$model = $curView->getModel();
				$model['menu']["Alarms"]=array(
				
						'id'=>"Alarms",
						'title'=>'Alarms',
						'url'=>"sce/sla/alarms/configuration/".$model['description'],
						//'link_attr'=>"role=menu"
						//'class'=>'active'
				);
				$curView->setModel($model);
			}
			
		}
	}
	
	function extendSettingsController(sm_ControllerElement $obj)
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
					if(strpos($p['module'],"SCE")===FALSE)
						continue;
					$p['name']=$c;
					$data[$p['module']][]=$p;
				}
				$curView->setData($data);
			}
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
		$board->setsegment("SLA");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Summary");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("sce_sla_local_dashboard");
		$dboard->add($board);
			
		//Overall HLM Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("SLA");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("Last SLA Alarms");
		$board->setmethod("sce_sla_overall_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
	
		$dboard->add($board);
	
	
	}
	
	public function sce_sla_overall_dashboard(sm_Board $board)
	{
		$data=array();
		$sce = new SM_SCE();
		$seg = $board->getsegment();
		
		$data['callback']['args']['data']['id']=$seg;
		$data['callback']['args']['data']['title']=$board->gettitle();
		
		$s=strtotime(date('Y-m-d 00:00:00',time()));
		$alarms = $sce->getAll("limit 10");
		if(isset($alarms))
			$data['callback']['args']['data']['alarms']=$alarms;
	
		$data['callback']['class']="SM_SCEView";
		$data['callback']['method']="dashboard_overall";
		return $data;
	}
	
	public function sce_sla_local_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$conf = SM_Configuration::load($id);
				
		$sce = new SM_SCE();
		$data=array();
		$alarms = $sce->getAll("limit 10",array("cid"=>$conf->getdescription()));
		$data['callback']['args']['data']['title']=$board->gettitle();
		if(isset($alarms))
			$data['callback']['args']['data']['alarms']=$alarms;
	
		
		$data['callback']['args']['data']['id']=$conf->getdescription();
		
		$data['callback']['class']="SM_SCEView";
		$data['callback']['method']="dashboard_local";
	
		return $data;
	}
}