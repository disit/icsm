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

class SM_MonitorDashboardView extends sm_DashboardView
{
	
	//protected $uiView;
	function __construct($data=null)
	{
		
		parent::__construct($data);
	
	}
	
	public function build(){
		switch($this->op)
		{
			case 'view::details':
				$this->dashboard_details();
				break;
			
			default:
				parent::build();
				break;
		}
	}
	
	public function dashboard_page() {
		parent::dashboard_page();		
		$this->uiView->addJS("monitor_dashboard.js","panel",SM_IcaroApp::getFolderUrl("js"));
		$viewDlg=new sm_HTML("DashboardDlg");
		$viewDlg->setTemplateId("Modal_dlg","ui.tpl.html");
		$viewDlg->insert("title", "View Details");
		$viewDlg->insert("id", "DashboardDlg");
		$viewDlg->insert("btn1", "Close");
		$this->uiView->insert($viewDlg);
	}
	
	function dashboard_details()
	{
		$id="dashboard_table";
		$html=new sm_HTML();
		$html->insert("title","<h5>".$this->model['title']."</h5>");
		$table = new sm_TableDataView($id,$this->model);
		$table->setAjax(true);
		$header=0;
		$table->makeResponsive();
		$table->addHRow("",array("data-type"=>"table-header"));
		if(count($this->model['records']>0))
		{
			$headers=array_keys($this->model['records'][0]);
			foreach($headers as $l)
			{
				$header++;
				$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
			}
		}
			
		foreach ($this->model['records'] as $k=>$value)
		{
			$table->addRow();
			foreach ($value as $l=>$v)
			{
				if($l=="os")
				{
					$v="<i class='sm-icon icon-".$v."' title='".$v."'></i><span class=os-type>".$v."</span>";
				}
				if($l=="state")
				{
					if($v==1)
						$v="<span class='label label-success'>UP</span>";
					else if ($v==0)
						$v="<span class='label label-danger'>DWN</span>";
					else if($v==-1)
						$v="<span class='label label-default'>UNK</span>";
				}		
				if($l=='actions' && is_array($v))
				{
					$this->setTemplateId("actions_forms","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
					$v=$this->tpl->getTemplate("actions_forms");
						
				}
				$table->addCell($v);
			}
		}
		$html->insert("content",$table);
		$this->uiView = $html;
	
	}
	

	public function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj))
		{
			if(get_class($obj)=="sm_DashboardView" && $obj->getType()=="overall")
			{
				$panel=$obj->getUIView();
				$panel->addJS("monitor_dashboard.js","panel",SM_IcaroApp::getFolderUrl("js"));
				$viewDlg=new sm_HTML("DashboardDlg");
				$viewDlg->setTemplateId("Modal_dlg","ui.tpl.html");
				$viewDlg->insert("title", "View Details");
				$viewDlg->insert("id", "DashboardDlg");
				$viewDlg->insert("btn1", "Close");
				$panel->insert($viewDlg);
			}
		}
	}
	
	public function dashboard_panel() {
	
		if(!empty($this->model['title']))
			$title=$this->model['title'];
		else 
			$title="Dashboard";
		
		$content=$this->dashboard_create();
		$boards="";
		$this->uiView = $panel = new sm_Panel("MonitorDashboard");
		$panel->setTitle($title);
		$tab = new sm_TabMenu("MonitorDashboard");
	
		$panel->insert($tab);
		$tabContainer = new sm_HTML();
		$panel->insert($tabContainer);
		$tabContainer->insert("start", '<div id="tabs_container" class="tab-content">');
		$id=null;
		if(count($content)>0)
		{
			foreach($content as $c=>$val)
			{
				//if($boards.=$val['html'];
				$tabPanel = new sm_HTML();
				if($id==null)
				{
					$id=$c;
					$tabPanel->insert("pre","<div id='".str_replace(" ","-",$c)."' class='tab-pane active'>");
				}
				else 
				{
					$tabPanel->insert("pre","<div id='".str_replace(" ","-",$c)."' class='tab-pane'>");
				}
				$tab->insert($c,array("url"=>"#".str_replace(" ","-",$c),"tab"=>"tab","title"=>$c,"link_class"=>"button light-gray btn-sm"));
				
				
				foreach($val as $p)
				{
					if(is_array($p))
						$tabPanel->insert("html",$p['html']);
					else
						$tabPanel->insert("html",$p);
				}
				$tabPanel->insert("end","</div>");
				$tabContainer->insert($c,$tabPanel);
				
				
			}
		}
		if($id)
			$tab->setActive($id);
			
		$tabContainer->insert("end", '</div>');
		$panel->icon("<img src='img/dashboard.png' />");
		$panel->addJS("monitor_local_dashboard_init();","panel");
		
	} 
	
	public function dashboard_host($host=null,$meters=null,$graphs=null,$tpl=null){
		$html = new sm_HTML();
		foreach($host as $k=>$data){
			$host = $data['host'];
			if($host->gettype()=="HLMhost")
				continue;
			$host_dashboard=new sm_HTML();
			$host_dashboard->setTemplateId("monitor_host_dashboard",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
			
			$data['icon']="sm-icon-".$host->gettype();
			$data['os']=$host->getos();//"sm-icon-".$host->gettype();
			unset($data['host']);
			$host_dashboard->insertArray($data);
			if($graphs[$k]){
				foreach ($graphs[$k] as $graph)
				{
					$grahArea = new sm_HTML();
					$grahArea->setTemplateId("graph_element_dashboard",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
					$grahArea->insertArray($graph);
					$host_dashboard->insert("graph_element_dashboard", $grahArea);
				}
			}
	
			if($meters[$k])// && count($meters[$k])>0)
			{
				$table = new sm_TableDataView($k);
				$table->makeResponsive();
				$table->addHRow("",array("data-type"=>"table-header"));
				$table->addHeaderCell("Name");
				$table->addHeaderCell("Usage");
				$table->addHeaderCell("Value");
				$table->addHeaderCell("State");
				$table->addHeaderCell("Last Check");
				foreach ($meters[$k] as $name=>$_meter)
				{
						
					foreach($_meter as $meter)
					{
						if(!isset($meter['value']))
								continue;
						$usage = new sm_HTML();
						$usage->setTemplateId("progress_bar","ui.tpl.html");
						if($meter['state']==0)
						{
							$usage->insert("class","progress-bar-success");
							$meter['state']='<span class="label label-success">OK</span>';
						}
						if($meter['state']==1)
						{
							$usage->insert("class","progress-bar-warning");
							$meter['state']='<span class="label label-warning">WARN</span>';
						}
						if($meter['state']==2)
						{
							$usage->insert("class","progress-bar-danger");
							$meter['state']='<span class="label label-danger">CRIT</span>';
						}
						if($meter['state']==3)
						{
							$usage->insert("class","progress-bar-danger");
							$meter['state']='<span class="label label-danger">UNKW</span>';
		
						}
						if($meter['value']==0 && $meter['max']==0)
							$usage->insert("value",0);
						else
							$usage->insert("value",100*$meter['value']/$meter['max']);
							
						$usage->insert("min",0);
						$usage->insert("max",100);
						$usage->insert("title",sprintf("value: %d%s - max: %d%s",$meter['value'],$meter['unit'],$meter['max'],$meter['unit']));
						$table->addRow(); 
						if($name == "Disk")
							$table->addCell($meter['display_name']."<br><small>".$meter['metric']."</small>");
						else 
							$table->addCell($meter['display_name']);
						$table->addCell($usage);
						if($meter['value']==0 && $meter['max']==0)
							$table->addCell(sprintf("%d%%",0));
						else
							$table->addCell(sprintf("%d%%",100*$meter['value']/$meter['max']));
						
						$table->addCell($meter['state']);
						
						$table->addCell(date("d/m/y H:i:s",$meter['last_check']));
					}
				}
			}
			else
				$table ="<h3>No data available</h3>";
			$host_dashboard->insert("meters_element_dashboard", $table);
			$html->insert($k, $host_dashboard);
			//$host_dashboard->addCSS("monitor.css",SM_IcaroApp::getFolderUrl("css"));
				
				
		}
		return $html;
	}
	
	public function dashboard_local_overall($data=null,$status=null,$tpl=null){
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='monitor_dashboard col-xg-6 col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_HTML();
			$content->setTemplateId($tpl['tpl'],$tpl['path']); 
			$content->insertArray($data['values']);
			$panel->insert($content);
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
			$html->addCSS("configurator.css","panel",SM_IcaroApp::getFolderUrl("css"));
		}
		return $html;
	}
	
	public function dashboard_local_host_performance($data=null,$status=null,$tpl=null){
		$html=null;
	
		if($status){
			$data['element']=array();
			foreach($status as $k=>$s){
				$container=new sm_HTML();
				$container->setTemplateId("summary_element_dashboard",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
				
				if(!empty($s))
				{	
					$table = new sm_TableDataView($k);
					$table->makeResponsive();	
					$table->addHRow("",array("data-type"=>"table-header"));
					$table->addHeaderCell("Host");
					$table->addHeaderCell($k." Usage");
					$table->addHeaderCell("Value");
					$table->addHeaderCell("Last Check");
					$table->addHeaderCell("State");
					if($k=="Disk")
					{
						$table->addHeaderCell("Volume");
					}
					else
						$container->insert("class", "col-xg-6");
						
					foreach($s as $r)
					{
						if(count($r)==0 )
							continue;	
						$usage= new sm_HTML();
						$usage->setTemplateId("progress_bar","ui.tpl.html");
						if(!isset($r['value']) || ($r['value']==0 && $r['max']==0))
						{
							$r['value']=0;
							$usage->insert("title",sprintf("value: %01.2f%s - max: %01.2f%s",$r['value'],$r['unit'],$r['max'],$r['unit']));
						}
						else
						{
							$usage->insert("title",sprintf("value: %01.2f%s - max: %01.2f%s",$r['value'],$r['unit'],$r['max'],$r['unit']));
							$r['value']=100*$r['value']/$r['max'];
						}
						
						if(!isset($r['state']))
						{
							$usage->insert("class","progress-bar-danger");
							$r['state']='<span class="label label-danger">UNKW</span>';
						}
						else
						{
							if($r['state']==0)
							{
								$usage->insert("class","progress-bar-success");
								$r['state']='<span class="label label-success">OK</span>';
							}
							if($r['state']==1) 
							{
								if($r['value']>=$r['warning'])
								{
									$usage->insert("class","progress-bar-warning");
									$r['state']='<span class="label label-warning">WARN</span>';
								}
								else 
								{
									$usage->insert("class","progress-bar-success");
									$r['state']='<span class="label label-success">OK</span>';
								}
							}
							if($r['state']==2)
							{
								if($r['value']>=$r['critical'])
								{
									$usage->insert("class","progress-bar-danger");
									$r['state']='<span class="label label-danger">CRIT</span>';
								}
								else if($r['value']>=$r['warning'])
								{
									$usage->insert("class","progress-bar-warning");
									$r['state']='<span class="label label-warning">WARN</span>';
								}
								else 
								{
									$usage->insert("class","progress-bar-success");
									$r['state']='<span class="label label-success">OK</span>';
								}
							}
							if($r['state']==3)
							{
								$usage->insert("class","progress-bar-danger");
								$r['state']='<span class="label label-danger">UNKW</span>';
							}
						}
						
						$usage->insert("value",$r['value']);
				/*		if(isset($r['value']))
								$usage->insert("title",sprintf("value: %01.2f%s - max: %01.2f%s",$r['value'],$r['unit'],$r['max'],$r['unit']));*/
						//$value->insert("text",sprintf("%d%%",100*$r['value']/$r['max']));
						$usage->insert("min",0);
						$usage->insert("max",100);
						$host = explode("@",$r['host']);
						$table->addRow();
						$table->addCell($host[0]."<br><small>".$host[1]."</small>");
						$table->addCell($usage);
						$table->addCell(sprintf("%d%%",$r['value']));
						
						if(isset($r['last_check']))
							$table->addCell(date("d/m/y H:i:s",$r['last_check']));
						else 
							$table->addCell("N.A.");
						$table->addCell($r['state']);
						if($k=="Disk")
						{
							if(isset($r['metric']))
								$table->addCell($r['metric']);
							else
								$table->addCell("N.A.");
						}
					}
				}
				else
				{
					if($k!="Disk")
						$container->insert("class", "col-xg-6");
					$table="<h4 class=well>Metrics not available or evaluation in progress!</h4>";
				}
				
				$container->insert("element",$table);
				$container->insert("title",$k);
					
					
					
				$data['element'][]=$container;
					
			}
	
			$html=new sm_HTML("monitor_summary_dashboard");
			$html->setTemplateId("monitor_dashboard",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
			$html->insertArray($data);
			$html->insert("class","monitor_dashboard_group col-xg-12 col-lg-12 col-md-12");
			//	$html->addCSS("monitor.css",SM_IcaroApp::getFolderUrl("css"));
		}
		return $html;
	}
	
	public function dashboard_services_overall($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='dashboard-element col-xg-3 col-lg-6 col-md-6 col-sm-6'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_HTML();
			$content->setTemplateId($tpl['tpl'],$tpl['path']);
			$content->insertArray($data['values']);
			$panel->insert($content);
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
			$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
			
		}
		return $html;
	}
	
	public function dashboard_application_services_overall($data=null,$tpl=null,$css=null){
		$panels=null;
		if($data){
			$panels = new sm_HTML();
			foreach ($data as $k=>$v)
			{
				$html=new sm_HTML();
				$html->insert("pre","<div class='dashboard-element col-xg-3 col-lg-6 col-md-6 col-sm-6'>");
				$panel = new sm_Panel($v['id']);
				$panel->setTitle($v['title']);
				$panel->setType("default");
				$content=new sm_HTML();
				$content->setTemplateId($tpl['tpl'],$tpl['path']);
				$content->insertArray($v['values']);
				$panel->insert($content);
				$html->insert("panel",$panel);
				$html->insert("end","</div>");
				$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
				$panels->insert($k,$html);
			}
				
		}
		return $panels;
	}
	
	public function dashboard_local_application_services($data=null,$tpl=null,$css=null){
		$panels=null;
		if($data){
			$panels = new sm_HTML();
			foreach ($data as $k=>$v)
			{
				$html=new sm_HTML();
				$html->insert("pre","<div class='monitor_dashboard col-xg-6 col-lg-6 col-md-12 col-sm-12'>");
				$panel = new sm_Panel($v['id']);
				$panel->setTitle($v['title']." <small>Services Checks Status</small>");
				$panel->setType("default");
				$table = new sm_Table();
				$table->makeResponsive();
				$headers = array_keys(reset($v['service']));
				$table->addHRow("",array("data-type"=>"table-header"));
				foreach ($headers as $h)
					$table->addHeaderCell($h);
				foreach($v['service'] as $name=>$s)
				{
					$table->addRow();
					foreach ($s as $label=>$val)
					{
						if($label=="OK")
							$val = '<p class="label label-success">'.$val.'</p>';
						else if($label=="Crit")
							$val = '<p class="label label-danger">'.$val.'</p>';
						else if($label=="Unkn")
							$val = '<p class="label label-default">'.$val.'</p>';
						else if($label=="Warn")
							$val = '<p class="label label-warning">'.$val.'</p>';							
						$table->addCell($val);
					}
					/*$content=new sm_HTML();
					$content->setTemplateId($tpl['tpl'],$tpl['path']);
					$content->insertArray($v['service'][$name]);*/
					
				}
				$panel->insert($table);
				$html->insert("panel",$panel);
				$html->insert("end","</div>");
				$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
				$panels->insert($k,$html);
			}
	
		}
		return $panels;
	}
	
	public function dashboard_lastchecks($data=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_Table("monitor_events_table");
			$content->makeResponsive();
			$content->addHRow("",array("data-type"=>"table-header"));
			$content->addHeaderCell("Host");
			$content->addHeaderCell("Name");
			$content->addHeaderCell("Output");
			$content->addHeaderCell("Time");
			$content->addHeaderCell("Event");
			$content->addHeaderCell("State");
			$content->addHeaderCell("Level");
			foreach ($data['checks'] as $check)
			{
				$content->addRow();
				$content->addCell($check['host']."<br><small>".$check['address']."</small>");
				$content->addCell($check['name']);
				$content->addCell($check['output']);
				$content->addCell($check['time']);
				$content->addCell($check['event']);
				$content->addCell($check['state']);
				$content->addCell($check['level']);
			}
			$panel->insert($content);
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
			$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
		}
		return $html;
	}
	
	function dashboard_hosts_groups($data=null,$tpl=null,$css=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			foreach($data['metric'] as $k=>$metric)
			{
				$content = new SM_PieGraph($data['id']);
				$content->title($k);
				$content->insertArray($metric);
				$panel->insert($content);
			}
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
			$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
		}
			return $html;
	}
	
	function dashboard_virtual_machines($data=null,$tpl=null,$css=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			foreach($data['metric'] as $k=>$metric)
			{
				$content = new SM_PieGraph($data['id']);
				$content->title($k);
				$content->insertArray($metric);
				$panel->insert($content);
			}
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
			$html->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
		}
		return $html;
	}
	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("General Info","monitor/dashboard/overall","stats");
	}
	
/*	public function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj))
		{
			if(is_a($obj,"sm_DashboardView"))
			{
				$dashview=$obj->getUIView();
				$dashview->addCSS("monitor.css","panel",SM_IcaroApp::getFolderUrl("css"));
				$dashview->addCSS("jquery.jqplot.min.css","panel",SM_IcaroApp::getFolderUrl("css"));
				$dashview->addJS("jquery.jqplot.js","panel",SM_IcaroApp::getFolderUrl("js"));
				$dashview->addJS("jqplot.pieRenderer.js","panel",SM_IcaroApp::getFolderUrl("js/plugins"));
				$dashview->addJS("monitor_dashboard.js","panel",SM_IcaroApp::getFolderUrl("js"));
				$dashview->addJS("excanvas.js","panel",SM_IcaroApp::getFolderUrl("js"));
				$viewDlg=new sm_HTML("DashboardDlg");
				$viewDlg->setTemplateId("Modal_dlg","ui.tpl.html");
				$viewDlg->insert("title", "View Details");
				$viewDlg->insert("id", "DashboardDlg");
				$viewDlg->insert("btn1", "Close");
				$dashview->insert($viewDlg);
	
			}
		}
	}
	*/
}