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

class SM_NagiosMonitorToolsUIController extends sm_ControllerElement
{
		
	/**
	 * Gets the monitor tool page
	 *
	 * @url GET monitor/tools/:tool
	 *
	 */
	function monitor_tools_page($tool=null)
	{
		//$this->install(null);
		if(!isset($tool))
		{
			sm_set_message("Invalid request!");
			return;
		}
		$url="";
		switch($tool)
		{
			case "nagios":
				$url=sm_Config::get('NAGIOSCOREURL',"");
			break;
			case "nagiosql":
				$url=sm_Config::get('NAGIOSQLURL',"");
			break;
			case "pnp4nagios":
				$url=sm_Config::get('PNP4NAGIOSURL',"");
			break;
		}
		if($url!="")
		{
			$data['tool_url']=$url;	
			$this->view = new SM_NagiosMonitorToolsView($data);
		}
		
	}
	
	public function dashboard_system(sm_Board $board)
	{
		$status=array();
		$tools=unserialize($board->getcallback_args());
		foreach($tools['tools'] as $i=>$tool)
		{		
			if($tool=='NagiosQL')
			{
				/*$ql = new SM_NagiosQL();
				$r=$ql->login();
				if($r)
					$r&=$ql->logout();*/
				$uptime="";
				$r=false;
				$ip=sm_Config::get('NAGIOSQLURL',"N.A");
				if($ip!="N.A" && $ip!=="")
				{
					$url=parse_url($ip);
					$ip=$url['host'];
					$t=microtime();
					$r=SM_NagiosQL::isAlive();
					$uptime=microtime()-$t;
				}	
				$tool_data=array();
				$tool_data['img']=SM_NagiosPlugin::instance()->getFolderUrl("img")."nagiosql.png";
				$tool_data['status']=$r?"success":"warning";
				$tool_data['ip']=$ip;
				$tool_data['version']="3.2.0";
				$tool_data['id']="NagiosQL";
				$tool_data['response']=is_numeric($uptime)?number_format($uptime,3):$uptime;
				if($r)
					$tool_data['status_msg'] = "Running";
				else
					$tool_data['status_msg'] = "Down";
				$status[]=$tool_data;
				continue;
			}
			
			if($tool=='NagiosCore')
			{
				/*$ql = new SM_NagiosQL();
				$r=$ql->login();
				if($r)
				{
					$r&=$ql->verify();
					$r&=$ql->apply();
					$r&=$ql->logout();
				}*/
				$uptime="";
				$r=false;
				$ip=sm_Config::get('NAGIOSCOREURL',"N.A");
				if($ip!="N.A" && $ip!=="")
				{
					$url=parse_url($ip);
					$ip=$url['host'];
					$t=microtime();
					$r=SM_NagiosClient::isAlive();
					$uptime=microtime()-$t;
				}
				
				$tool_data=array();
				$tool_data['img']=SM_NagiosPlugin::instance()->getFolderUrl("img")."nagios.png";
				$tool_data['status']=$r?"success":"warning";
				$tool_data['version']=SM_NagiosClient::version();
				$tool_data['id']="NagiosCore";
				$tool_data['ip']=$ip;
				$tool_data['response']=is_numeric($uptime)?number_format($uptime,3):$uptime;
				
				if($r)
					$tool_data['status_msg'] = "Running";
				else
					$tool_data['status_msg'] = "Down";
				
				$status[]=$tool_data;
				continue;
			}
			
			if(false && $tool=="NagiosConfigDaemon")
			{
				
				$r=false;
				$r=SM_NagiosConfiguratorDaemon::isAlive();
				
				$tool_data=array();
				$tool_data['img']=SM_NagiosPlugin::instance()->getFolderUrl("img")."nagios.png";
				$tool_data['status']=$r?"success":"warning";
				$tool_data['version']=SM_NagiosConfiguratorDaemon::version();
				$tool_data['id']="NagiosConfigDaemon";
				
				
				if($r)
					$tool_data['status_msg'] = "Running";
				else
					$tool_data['status_msg'] = "Down";
				
				$status[]=$tool_data;
				continue;
			}
		}
		$data=array();
		if(!empty($status))
		{
			
			$data['callback']['args']['content']=$status;
			$data['callback']['args']["tpl"]['tpl']="monitor_tool_status_dashboard";
			$data['callback']['args']["tpl"]["path"]=SM_NagiosPlugin::instance()->getFolderUrl("templates")."nagios.tpl.html";
			$data['callback']['class']="SM_NagiosMonitorToolsView";
			$data['callback']['method']="dashboard_system";
			$data['callback']['args']["css"]['file']="monitortool.css";
			$data['callback']['args']['css']['path']=SM_NagiosPlugin::instance()->getFolderUrl("css");
		}
		
		return $data;
	}
	
	static function install($db)
	{
		if(!class_exists("sm_Board") || !class_exists("sm_DashboardManager") )
			return;
		
			$board = new sm_Board();
			$res=$board->exists(array("segment"=>"NagiosMonitorTools","view_name"=>"system"));
			if(isset($res[0]['id']))
			{
				sm_Logger::write("Removing Existing board from dashboard");
				$board->delete($res[0]["id"]);
			}
			sm_Logger::write("Installing board into dashboard");
			$dboard= new sm_DashboardManager();
			$board->setsegment("NagiosMonitorTools");
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Nagios Monitor Tools");
			$board->setcallback_args(serialize(array("tools"=>array("NagiosQL","NagiosCore","NagiosConfigDaemon"))));
			$board->setview_name("system");
			$board->setmethod("dashboard_system");
			$dboard->add($board);
		
	}
	

} 