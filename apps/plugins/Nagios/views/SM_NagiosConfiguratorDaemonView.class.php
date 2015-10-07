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

class SM_NagiosConfiguratorDaemonView extends sm_TailView
{
	function __construct($data=NULL)
	{
		parent::__construct($data);
	}
	
	/**
	 * Create the HTML code for the module.
	 */
	public function build() {
	
		$op = $this->getOp();
		if($op=="command")
		{
			$this->model['menu']=$this->commandMenu()->render();
			$this->uiView = new sm_JSON();
			$this->uiView->insert($this->model);
			return;
		}
		if($op=="refresh::plot" || $op=="settings")
		{
			$this->uiView = new sm_JSON();
			$this->uiView->insert($this->model);
			return;
		}
		if($op=="refresh::queue")
		{
			$data['html']=$this->queue()->render();
			$this->uiView = new sm_JSON();
			$this->uiView->insert($data);
			return;
		}
		if($op=="view")
		{
			$title=$this->model['title'];
			$this->uiView=new sm_Page("NagiosConfiguratorDaemonView");
			$this->uiView->setTitle($title);
			$this->uiView->addCss("tail.css","main",sm_TailPlugin::instance()->getFolderUrl("css"));
			$this->uiView->addJs("jqtail.js","main",sm_TailPlugin::instance()->getFolderUrl("js"));
			$this->uiView->addJs("var tailRefreshUrl='".$this->model['refreshUrl']."';");
			//$this->addView();
			$this->uiView->addCss("NagiosDaemon.css","main",SM_NagiosPlugin::instance()->getFolderUrl("css"));
			$this->uiView->addCss("jquery.jqplot.min.css","main",SM_IcaroApp::instance()->getFolderUrl("css"));
			$this->uiView->addJs("jquery.jqplot.js","main",SM_IcaroApp::instance()->getFolderUrl("js"));
			$this->uiView->addJs("jqplot.dateAxisRenderer.js","main",SM_IcaroApp::instance()->getFolderUrl("js/plugins"));
			$this->uiView->addJs("jqplot.highlighter.js","main",SM_IcaroApp::instance()->getFolderUrl("js/plugins"));
			$this->uiView->addJs("jqplot.cursor.js","main",SM_IcaroApp::instance()->getFolderUrl("js/plugins"));
			$this->uiView->addJS("SM_NagiosDaemonPlot.js","main",SM_NagiosPlugin::instance()->getFolderUrl("js"));
			$this->uiView->addJS("SM_NagiosDaemonQueue.js","main",SM_NagiosPlugin::instance()->getFolderUrl("js"));
			$this->uiView->addJS("SM_NagiosDaemonSetting.js","main",SM_NagiosPlugin::instance()->getFolderUrl("js"));
			$this->addViewMenu();
			
			
			$panel = new sm_Panel("NagiosDaemonQueue");		
			$panel->setTitle("Queue");
			$panel->icon("<i class='sm-icon sm-icon-daemon-queue-small'> </i>");
			$panel->insert($this->queue());
			$panel2 = new sm_Panel("NagiosDaemonPerformance");
			$panel2->setTitle("Status");
			$panel2->insert($this->performance());
			$panel2->icon("<i class='sm-icon sm-icon-daemon-graph-small'> </i>");
			$panel3 = new sm_Panel("NagiosDaemonSettings");
			$panel3->setTitle("Settings");
			$panel3->insert($this->settings());
			$panel3->icon("<i class='sm-icon sm-icon-daemon-graph-small'> </i>");
			$status=new sm_Grid("NagiosDaemonStatus");
			$status->addRow(array($this->panel(),$panel2),array(8,4));
			$status->addRow(array($panel,$panel3),array(8,4));
			$this->uiView->insert($status);
			
			return;
		}
		parent::build();
		
	}
	
	protected function queue(){
		$queue=$this->model['queue'];
		if(isset($queue))
		{
			$content=new sm_Table("nagios_daemon_queue");
			$content->makeResponsive();
			$content->addHRow("",array("data-type"=>"table-header"));
			foreach(array_keys($queue[0]) as $k)
				$content->addHeaderCell($k);
			
			
			foreach ($queue as $q)
			{
				$content->addRow();
				
				foreach(array_keys($q) as $k)
				{
					if($k=="status")
					{
						if($q[$k]==-1) //Ready
						{
							$v="<span class='label label-info'>Ready</span>";
						}
						else if($q[$k]==0) //Waiting
						{
							$v="<span class='label label-warning'>Waiting</span>";
						}
						else if($q[$k]==1)//Monitoring
						{
							$v="<span class='label label-success'>Monitoring</span>";
						}
						else if($q[$k]==2)//Stopped
						{
							$v="<span class='label label-danger'>Stopped</span>";
						}
						else if($q[$k]==3)//Failed
						{
							$v="<span class='label label-danger'>Failed</span>";
						}
						else if($q[$k]==4)//Processing
						{
							$v="<span class='label label-default'>Processing</span>";
						}
					}
					else 
						$v=$q[$k];
					$content->addCell($v);	
				}		
			}
			
			return $content;
		}
		$html = new sm_HTML("nagios_daemon_queue");
		$html->insert("Empty", "<div id=nagios_daemon_queue class='alert alert-info' role='alert'>Queue is empty!</div>");
		return $html;
	}
	
	protected function performance(){
		$performance=$this->model['performance'];
		
		$chart=new sm_HTML("chart_mem");
		$chart->setTemplateId("monitor_graph",SM_NagiosPlugin::instance()->getFolderUrl("templates")."nagios.tpl.html");
		$chart->insert("id","mem");
		$chart->insert("title","Memory Usage (%)");
		$chart->insert("data",$performance['mem']);
		
		$chart2=new sm_HTML("chart_cpu");
		$chart2->setTemplateId("monitor_graph",SM_NagiosPlugin::instance()->getFolderUrl("templates")."nagios.tpl.html");
		$chart2->insert("id","cpu");
		$chart2->insert("title","Cpu Usage (%)");
		$chart2->insert("data",$performance['cpu']);
		$html = new sm_HTML();
		$html->insert("1",$chart);
		$html->insert("2",$chart2);
		
		return $html;
	}
	
	protected function settings(){
		$html = new sm_HTML();
		$html->insert("form",sm_Form::buildForm("settings", $this));
		
		return $html;
	}
	
	public function settings_form(sm_Form $form){
		$form->configure(array(
				"prevent" => array("bootstrap","jQuery","focus","redirect"),
				"action"=>"nagios/configurator/daemon/settings"
		));
		$form->addElement(new Element_YesNo("Enable Rollback", "rollback",array('value'=>$this->model['settings']['rollback']['value'])));
		$form->addElement(new Element_Number("Set Sleep (secs)", "sleep",array('value'=>$this->model['settings']['sleep']['value'],"min"=>1,"max"=>3600,"step"=>1)));
		$form->addElement(new Element_Button("Apply","",array("class"=>"button light-gray btn-xs")));
	}
	
	protected function commandMenu()
	{
		
		$menu = new sm_NavBar("NagiosDaemonMenu");
		$menu->insert("brand","NagiosDaemon");
		
		if(sm_Config::get("SMNAGIOSCONFIGRUN",0) && sm_Config::get("SMNAGIOSCONFIGDAEMONSHUTDOWN",0)==0)
			$menu->insert("isAlive", array("id"=>"isAliveCmd","url"=>"nagios/configurator/daemon/command/alive","title"=>'Check Alive',"icon"=>"sm-icon sm-icon-daemon-alive","link_attr"=>"data-target='.message'"));
		if(sm_Config::get("SMNAGIOSCONFIGDAEMONSHUTDOWN",0)==0)
		{
			if(sm_Config::get("SMNAGIOSCONFIGRUN",0)==0)
			{
					
				//paused or stopped
				$menu->insert("run", array("id"=>"runCmd","url"=>"nagios/configurator/daemon/command/run","title"=>'Resume',"icon"=>"sm-icon sm-icon-daemon-play","link_attr"=>"data-toggle='#pauseCmd' data-target='.message'"));
				//$menu->insert("pause", array("id"=>"pauseCmd","url"=>"nagios/configurator/daemon/command/pause","title"=>'Pause',"icon"=>"sm-icon sm-icon-daemon-pause","link_attr"=>"style='display:none;' data-toggle='#runCmd' data-target='.message'"));
			}
			else
			{
				//running
				//$menu->insert("run", array("id"=>"runCmd","url"=>"nagios/configurator/daemon/command/run","title"=>'Resume',"icon"=>"sm-icon sm-icon-daemon-play","link_attr"=>"style='display:none;' data-toggle='#pauseCmd' data-target='.message'"));
				$menu->insert("pause", array("id"=>"pauseCmd","url"=>"nagios/configurator/daemon/command/pause","title"=>'Pause',"icon"=>"sm-icon sm-icon-daemon-pause","link_attr"=>"data-toggle='#runCmd' data-target='.message'"));
			}
			//on
			$menu->insert("shutdown", array("id"=>"shutdownCmd","url"=>"nagios/configurator/daemon/command/shutdown","title"=>'Service Off',"icon"=>"sm-icon sm-icon-daemon-off","link_attr"=>"data-toggle='#startCmd' data-target='.message'"));
			//$menu->insert("start", array("id"=>"startCmd","url"=>"nagios/configurator/daemon/command/start","title"=>'Service On',"icon"=>"sm-icon sm-icon-daemon-on","link_attr"=>"style='display:none;' data-toggle='#shutdownCmd' data-target='.message'"));
		}
		else
		{
			//off
			//$menu->insert("shutdown", array("id"=>"shutdownCmd","url"=>"nagios/configurator/daemon/command/shutdown","title"=>'Service Off',"icon"=>"sm-icon sm-icon-daemon-off","link_attr"=>"style='display:none;' data-toggle='#startCmd' data-target='.message'"));
			$menu->insert("start", array("id"=>"startCmd","url"=>"nagios/configurator/daemon/command/start","title"=>'Service On',"icon"=>"sm-icon sm-icon-daemon-on","link_attr"=>"data-toggle='#shutdownCmd' data-target='.message'"));
		}	
		return $menu;
	}
	protected function addViewMenu()
	{
		$this->uiView->menu($this->commandMenu());
		$this->uiView->addJS("SM_NagiosDaemonMenu.js","main",SM_NagiosPlugin::instance()->getFolderUrl("js"));
	}
	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setSubLink("Monitor Tools","Nagios Configurator","nagios/daemon");
	}
}