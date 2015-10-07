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

class SM_SLAController extends sm_ControllerElement
{
	protected $model;
	
	function __construct(){
		$this->model =new SM_Configurator();
		$this->view = new SM_SLAView();
	}
	
	
	/**
	 * @desc Gets the view of the configuration by id
	 *
	 * @url GET sla/configuration/view/:id
	 *
	 * @callback
	 */
	function sla_configuration($id=null)
	{
		if(!isset($id))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$data=$this->model->getConfigurationData("header", $id);
		
		$_data=array();
		
		$_data['id']=$id;
		$_data['description']=$data['configuration']["description"];
		$_data['menu']["Info"]=array(
		
				'id'=>"Info",
				'title'=>'Info',
				'url'=>"sla/configuration/info/".$_data['description'],
				//'link_attr'=>"role=menu"
				//'class'=>'active'
		);
		
		
		$this->view->setOp("sla::view");
		$this->view->setModel($_data);
	
	}
	
	/**
	 * @desc Gets the info page of a configuration
	 *
	 * @url GET sla/configuration/info/:id
	 * 
	 * @callback
	 */
	function sla_configuration_info($id=null)
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
		$this->view->setOp("sla::info");
		$this->view->setModel($data);
	}
	
	static public function install($db)
	{
		//self::installDashboard();
		return true;
	}
	
	static public function installDashboard()
	{
		if(!class_exists("sm_Board") || !class_exists("sm_DashboardManager") )
			return;
		$dboard= new sm_DashboardManager();
		$dboard->delete(array("module"=>__CLASS__));
	
		//SLA Local Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("Monitor");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("SLA");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("sla_local_dashboard");
		$dboard->add($board);
			
		//Overall SLA Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("sla");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("SLA Health");
		$board->setmethod("sla_overall_dashboard");
		$board->setcallback_args(serialize($args));
		$board->setview_name("overall");
		
		$dboard->add($board);
	
		
	}
	
	public function sla_overall_dashboard(sm_Board $board)
	{
		$data=array();
		$monitor = new SM_Monitor();
		$seg = $board->getsegment();
		$interval=sm_Config::get("MONITORDASHBOARD_EVENTS_INTERVAL",4);
		$data['callback']['args']['data']['id']=$seg;
		$data['callback']['args']['data']['title']=$board->gettitle(); //." of recent ".$interval." hour(s)";
		$data['callback']['args']['data']['checks']=array();
		$checks = $monitor->last_events(10,time()-3600*$interval);
		if(isset($checks['checks']))
			$data['callback']['args']['data']['checks']=$checks['checks'];
	
		$data['callback']['class']="SM_SLAView";
		$data['callback']['method']="dashboard_overall";
		return $data;
	}
	
	public function sla_local_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		//$segment=explode("/",$board->getsegment());
		$monitor = new SM_Monitor();
	//	$conf = SM_Configuration::load($id);
		$data=array();
		
		// TODO sla computing
		$data['callback']['args']['sla']=array();
		$data['callback']['class']="SM_SLAView";
		$data['callback']['method']="dashboard_local";
	
		return $data;
	}
}