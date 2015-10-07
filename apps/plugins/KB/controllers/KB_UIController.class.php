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

class KB_UIController extends sm_ControllerElement
{

	/**
	 * Gets the kb tool page
	 *
	 * @url GET KB/configuration/:id
	 * @url GET KB/configuration/
	 */
	function KB_edit_configuration_page($id=null)
	 {
		
		$url = sm_Config::get ( 'KBCONFEDITORTOOLURL', null );
		if (! isset ( $url )) {
			sm_set_error ( "Invalid url!" );
			return;
		}
		if ($url != "") {
			$data ['tool_url'] = $url;
			$data ["tool_title"] = "New Configuration";
			$this->view = new KB_View ( $data );
			$this->setOp("edit");
		}
	
	}
	
	/**
	 * @desc Check the KB url query 
	 *
	 * @url GET KB/check
	 *
	 * @callback
	 */
	
	function KB_checkUrl(){
		$kb = new KB();
		$url=$_GET['url'];
		$str = $kb->dowloadXml($url);
		
		$a=XML2Array::createArray($str);
			
		$this->view=new KB_View($a);
		$this->view->setOp("view::tax");
	}
	
	
	
	
	/**
	 * @desc Gets the Installatinon page of KB plugin
	 *
	 * @url GET KB/install
	 *
	 */
	function KB_install()
	{
		$data=array();
	
	
		$conf=sm_Config::instance()->conf;
		$data=array();
		foreach($conf as $c=>$p)
		{
			if(strpos($p['module'],"KB")===FALSE)
				continue;
			$p['name']=$c;
			$data[$p['module']][]=$p;
		}
		$this->view = new KB_View ( $data );
		$this->view->setOp("install");
	}
	
	

	public function dashboard_system(sm_Board $board)
	{
		$status=array();
		$tools=unserialize($board->getcallback_args());
		foreach($tools['tools'] as $i=>$tool)
		{
			if($tool=='KB')
			{
				$uptime="";
				$r=false;
				$ip=sm_Config::get('KBURL',"N.A");
				if($ip!="N.A" && $ip!="")
				{
					$url=parse_url($ip);
					$ip=$url['host'];
					$t=microtime();
					$r=KB::isAlive();
					$uptime=microtime()-$t;
				}
				
				$tool_data=array();
				$tool_data['img']=SM_KBPlugin::instance()->getFolderUrl("img")."KB.png";
				$tool_data['status']=$r?"success":"warning";
				$tool_data['ip']=$ip;
				$tool_data['version']="1.0";
				$tool_data['id']="KB";
				$tool_data['response']=is_numeric($uptime)?number_format($uptime,3):$uptime;
				if($r)
					$tool_data['status_msg'] = "Running";
				else
					$tool_data['status_msg'] = "Down";
				$tool_data['internal_status']="N.A";
				if($r)
				{
					$KBstatus=KB::getStatus();
					$intstatus="No Data Available";
					if($KBstatus->getResponseCode()==200)
					{
						$xml=$KBstatus->getResponse();
						$doc = new DOMDocument();
						$doc->loadXML($xml);
						$values=$doc->getElementsByTagName("value");
						$intstatus=array();
						foreach ($values as $k=>$v){
							$s=$v->getAttribute("type");
							$s=str_replace("count", "", $s);
							$intstatus[]=array("meter"=>$s,"value"=>$v->textContent);
						}
					}
					$tool_data['internal_status']=$intstatus;
				}
				$status=$tool_data;
				continue;
			}
		}
		$data=array();
		if(!empty($status))
		{
			$data['callback']['args']['content']=$status;
			$data['callback']['args']["tpl"]['tpl']="KB_status_dashboard";
			$data['callback']['args']["tpl"]["path"]=SM_KBPlugin::instance()->getFolderUrl("templates")."KB.tpl.html";
			$data['callback']['class']="KB_View";
			$data['callback']['method']="dashboard_system";
			$data['callback']['args']["css"]['file']="KB.css";
			$data['callback']['args']['css']['path']=SM_KBPlugin::instance()->getFolderUrl("css");
		}

		return $data;
	}

	static function install($db)
	{
		if(!class_exists("sm_Board") || !class_exists("sm_DashboardManager") )
			return;
		
			$dboard= new sm_DashboardManager();
			sm_Logger::write("Removing Existing board from dashboard");
			$dboard->delete(array("module"=>__CLASS__));
			$board = new sm_Board();
			sm_Logger::write("Installing board into dashboard");
			
			$board->setsegment("KB");
			$board->setmodule(__CLASS__);
			$board->setref_id(-1);
			$board->settitle("Knowledge Base");
			$board->setcallback_args(serialize(array("tools"=>array("KB"))));
			$board->setview_name("system");
			$board->setmethod("dashboard_system");
			$dboard->add($board);
		
	}
	
	function onExtendController(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_SettingsController")
		{
			$this->extendSettingsController($obj);
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
					if(strpos($p['module'],"KB")===FALSE)
						continue;
					$p['name']=$c;
					$data[$p['module']][]=$p;
				}
				$curView->setData($data);
			}
		}

	}
	
	
}