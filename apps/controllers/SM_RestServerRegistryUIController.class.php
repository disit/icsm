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

class SM_RestServerRegistryUIController extends sm_ControllerElement
{
	protected $model;
	
	function __construct(){
		$this->model=new SM_RestServerRegistry();
	}
	
	/**
	 * @desc Gets the log output table
	 *
	 * @url GET /restserver/registry
	 */
	function index()
	{
		$timestamp_from=null;
		$timestamp_to=null;
		$method="";
		$where=array();
		if(isset($_SESSION['restserver/registry']['time']) && $_SESSION['restserver/registry']['time']!=0)
		{
			$timestamp_from=$_SESSION['restserver/registry']['time'];
			$where[]="arrival_time >= ".$timestamp_from;
		}
		if(isset($_SESSION['restserver/registry']['to_time']) && $_SESSION['restserver/registry']['to_time']!=0)
		{
			$timestamp_to=$_SESSION['restserver/registry']['to_time'];
			$where[]="arrival_time <= ".$timestamp_to;
		}
		if(isset($_SESSION['restserver/registry']['method']))
		{
			$method=$_SESSION['restserver/registry']['method'];
			if($method!="")
				$where[]="method = '".$_SESSION['restserver/registry']['method']."'";
		}
	
		if(count($where))
			$where = "WHERE ".implode(" AND ",$where);
		$_totalRows=$this->model->getAllCount($where);
		$pager = new sm_Pager("restserver/registry");
		$pager->set_total($_totalRows);
		//calling a method to get the records with the limit set
		$data['records'] = $this->model->getAll( $pager->get_limit(),$where);
		$data['pager']=$pager;
		$data['time']=$timestamp_from?date("Y-m-d",$timestamp_from):"";
		$data['to_time']=$timestamp_to?date("Y-m-d",$timestamp_to):"";
		$data['method']=$method;
		$data['commands']['DeleteTBWSelectedRestCall']=array('name'=>'DeleteTBWSelectedRestCall','data-confirm'=>'Are you sure you want to delete this selection?','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
		//create the nav menu
		$this->view = new SM_RestServerRegistryView($data);
		$this->view->setOp("list");
	}
	
	/**
	 * @desc Delete notification
	 *
	 * @url POST /restserver/registry/delete
	 * 
	 * @callback
	 */
	function restserver_registry_delete($id=null)
	{
		$value=false;
		if(isset($id))
		{
			$_id = array_keys($id);
	
			$value = $this->model->delete($_id[0])?true:false;
				
		}
		else
			$value = false;
		$this->view=new SM_RestServerRegistryView($value);
		$this->view->setOp("response");
	}
	
	
	public function dashboard_system(sm_Board $board)
	{
		$_data=array();
		$_totalRows=$this->model->getAllCount();
		$record = $this->model->getAll( "LIMIT 1" );
	
		$tool_data=array();
		
		if(isset($record[0]))
		{
			$record[0]['request']=str_replace("api/","",strstr($record[0]['request'],"api"));
			$tool_data=array_merge($tool_data,$record[0]);
		}
			
		$tool_data['total']=$_totalRows;
		$tool_data['id']="RestServerRegistry";
	
		$_data['last'][]=$tool_data;
		
		$data['callback']['args']['data']=$_data;
		$data['callback']['args']["tpl"]['tpl']="rest_server_status_dashboard";
		$data['callback']['args']["tpl"]["path"]=SM_IcaroApp::getFolder("templates")."restserverregistry.tpl.html";
		$data['callback']['class']="SM_RestServerRegistryView";
		$data['callback']['method']="dashboard";
		$data['callback']['args']["css"]['file']="restserverregistry.css";
		$data['callback']['args']['css']['path']=SM_IcaroApp::getFolderUrl("css");
		return $data;
	}
	
	static function install($db)
	{
		if(class_exists("sm_Board"))
		{
			$dboard= new sm_DashboardManager();
			sm_Logger::write("Removing Existing board from dashboard");
			$dboard->delete(array("module"=>__CLASS__));
			sm_Logger::write("Installing board into dashboard");
			$board = new sm_Board();
			$board->setsegment("RestServer");
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Rest Server API");
			$board->setcallback_args(serialize(array()));
			$board->setview_name("system");
			$board->setmethod("dashboard_system");
			$dboard->add($board);
		}
	}
}
