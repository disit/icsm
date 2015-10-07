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

class SM_MonitorView extends sm_ViewElement
{
	
	
	function __construct($data=null)
	{
		parent::__construct($data);
	}

	/**
	 * Create the HTML code for the module.
	 */
	public function build() {

		switch($this->op)
		{
			case "graphs_view":
				$this->graphs_page_build();
				break;
			case "graph_view":
				$this->graph_element_build();
				break;
			case "controls_view":
				$this->controls_page_build();
				break;
			case "events_view":
				$this->events_page_build();
				break;
			case "meters_view":
				$this->meters_page_build();
				break;
			case "meters_refresh":
				$this->meters_refresh();
				break;
			case "monitor_cmd":
				$this->monitor_cmd();
				break;
			case "monitor_console":
				$this->monitor_console();
				break;
			case "hosts_list":
			case "vmhosts_list":
			case "business_list":
			case "system_list":
			case "devices_list":
			case "checks_list":
				$this->monitor_list_build();
				//$this->monitor_configurations_list_build();
				break;
			case "configuration_view":
				$this->monitor_configuration_view_build();
				break; 
		}
	}
	
	function monitor_list_build()
	{
		$this->uiView = new sm_Page("Monitor_".$this->op);
		$this->uiView->setTitle("Monitor");
		$menu = $this->monitor_navbar($this->op);
		$this->uiView->menu($menu);
		
		$this->uiView->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addCss("monitor.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addJS("monitor.js","main",SM_IcaroApp::getFolderUrl("js"));
		//$this->addView();
		switch($this->op)
		{
			case "hosts_list":
				
				$panel = $this->monitor_host_list_build();
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-hosts'></i>");
				$this->uiView->insert($panel);
				break;
			case "vmhosts_list":
				
				$panel = $this->monitor_host_list_build();
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-vms'></i>");
				$this->uiView->insert($panel);
				break;
			case "devices_list":
				$panel = $this->monitor_device_list_build();
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-devices'></i>");
				$this->uiView->insert($panel);
				break;
			case "business_list":
				
				$panel = $this->monitor_configurations_list_build();
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-business'></i>");
				$this->uiView->insert($panel);
				break;
			case "system_list":
				
				$panel = $this->monitor_configurations_list_build("system");
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-cluster'></i>");
				$this->uiView->insert($panel);
				break;
			case "checks_list":
			
				$panel = $this->monitor_checks_list_build();
				$panel->icon("<i class='sm-icon sm-icon-small sm-icon-checks'></i>");
				$this->uiView->insert($panel);
				break;
		}
		$activeMenu = $menu->getActive();
		if(isset($activeMenu['title']))
			$panel->setTitle($activeMenu['title']);
	}
	
	/************ CONFIGURATIONS *****************/
	function monitor_configuration_view_build()
	{
		
		
		$this->model['view']->build();
		$view_model=$this->model['view']->getModel();
		$this->uiView = $userUIView = $this->model['view']->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			//$userUIView->setTitle("Monitor - ");
			$title =$userUIView->getTitle();
			$userUIView->setTitle($title);
			$submenu=$this->monitor_navbar("","secondary");
			$userUIView->insert($submenu,"menubar");
			$menu = $userUIView->getMenu();
			$monitor_data= new sm_HTML();
			$monitor_data->setTemplateId("monitor_data",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
			$monitor_data->insertArray($view_model['monitor']);
			$panel = new sm_Panel("MonitorDescriptionData");
			$panel->setClass("metadataBox");
			$panel->setTitle("Monitor Data");
			$panel->insert($monitor_data);
			$conf = $userUIView->getUIElement("configuration_page");
			$conf->insert("description_data",$panel);
			$monitor_data=$view_model['monitor'];
			/*
			 * Appending Menu Items
			*/
			$menu->prepend("Dashboard",array(
					"url"=>"monitor/dashboard/".$monitor_data['description'],
					"title"=>"Dashboard",
					"icon"=>"sm-icon sm-icon-dashboard"));
				
			//$monitor=new SM_Monitor();
			//$monitor_data=$monitor->getData(array('iid'=>$view_model['id']));
			
			if(sm_ACL::checkPermission("Monitor::Edit"))
			{
				if($monitor_data['status']==0  || $monitor_data['status']==-1 || $monitor_data['status']==3)
			
				{
			
					$menu->insert("Monitor",array(
							"url"=>"monitor/queue/".$view_model['id'],
							"title"=>"Monitor On",
							"icon"=>"sm-icon sm-icon-monitor-on",
							"class"=>"extern"
					));
				}
				else if($monitor_data['status']==2 )//Stopped
				{
			
					$menu->insert("Monitor",array(
							"url"=>"monitor/start/".$view_model['id'],
							"title"=>"Monitor On",
							"icon"=>"sm-icon sm-icon-monitor-on",
							"class"=>"extern"
					));
				}
				else if($monitor_data['status']==1)//Monitoring
				{
					$menu->insert("Monitor",array(
							"url"=>"monitor/stop/".$view_model['id'],
							"title"=>"Monitor Pause",
							"icon"=>"sm-icon sm-icon-monitor-pause",
							"class"=>"extern"
					));
				}
				
				if($monitor_data['status']>0)//Monitoring
				{
			
					$menu->insert("Monitor-Delete",array(
							"url"=>"monitor/delete/".$view_model['id'],
							"title"=>"Monitor Delete",
							"icon"=>"sm-icon sm-icon-monitor-off",
							"class"=>"extern"
					));
				}
			}
		
			$userUIView->addCss("monitor.css","main",SM_IcaroApp::getFolderUrl("css"));
				
		
		}
		
		$userUIView->addJs("monitor.js","main",SM_IcaroApp::getFolderUrl("js"));
		$userUIView->addJs("meters.js","main",SM_IcaroApp::getFolderUrl("js"));
		$userUIView->addJS("jquery.switch.min.js","main",SM_IcaroApp::getFolderUrl("js")."jquery.switch/");
		$userUIView->addCss("jquery.switch.css","main",SM_IcaroApp::getFolderUrl("js")."jquery.switch/");
		if(isset($this->model["link"]))
			$userUIView->addJs("configurationLink='".$this->model["link"]."';");
		$userUIView->addJs('monitor_check_configuration("'.$monitor_data['description'].'","'.$view_model['id'].'",true);',"main");
	}
	
	function monitor_host_list_build()
	{
		$id="monitor_hosts_table";
		if($this->model['type']=="vmhosts")
			$id="monitor_vmhosts_table";
		$table = new sm_TableDataView($id,$this->model);
		$header=0;
		$table->addHRow();
		if(count($this->model['records'])>0)
		{
			$headers=array_keys($this->model['records'][0]);
			foreach($headers as $l)
			{
				$header++;
				if($l=='actions')
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
				else
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
			}
	
		}
			
		foreach ($this->model['records'] as $k=>$value)
		{
	
			$table->addRow();
			foreach ($value as $l=>$v)
			{
					
	
				//if($l=='actions' && is_array($v))
				//	$v=implode("",$v);
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
		$table->setSortable();
		$options=array("-1"=>"All","0"=>"Down","1"=>"UP",);
		$filterElement['monitor_search']=array("Search","", "monitor_search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
		$filterElement['monitor_status']=array("Select","Filter for", "monitor_status", $options,array('value'=>$this->model['state_selector'],'class'=>'input-sm'));
			
		$table->addFilter($filterElement);
	
	
		$panel = new sm_Panel();
	
		$panel->setTitle($this->model['title']);
		$panel->insert($table);
		return $panel;
	
	}
	
	function monitor_device_list_build()
	{
		$id="monitor_devices_table";
		
		$table = new sm_TableDataView($id,$this->model);
		$header=0;
		$table->addHRow();
		if(count($this->model['records'])>0)
		{
			$headers=array_keys($this->model['records'][0]);
			foreach($headers as $l)
			{
				$header++;
				if($l=='actions')
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
				else
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
			}
	
		}
			
		foreach ($this->model['records'] as $k=>$value)
		{
	
			$table->addRow();
			foreach ($value as $l=>$v)
			{
					
	
				//if($l=='actions' && is_array($v))
				//	$v=implode("",$v);
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
		$table->setSortable();
		$options=array("-1"=>"All","0"=>"Down","1"=>"UP",);
		$filterElement['monitor_search']=array("Search","", "monitor_search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
		$filterElement['monitor_status']=array("Select","Filter for", "monitor_status", $options,array('value'=>$this->model['state_selector'],'class'=>'input-sm'));
			
		$table->addFilter($filterElement);
	
	
		$panel = new sm_Panel();
	
		$panel->setTitle($this->model['title']);
		$panel->insert($table);
		return $panel;
	
	}
	
	function monitor_checks_list_build()
	{
		$id="monitor_checks_table";
	
		$table = new sm_TableDataView($id,$this->model);
		$header=0;
		$table->addHRow();
		if(count($this->model['records'])>0)
		{
			$headers=array_keys($this->model['records'][0]);
			foreach($headers as $l)
			{
				$header++;
				if($l=='actions')
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
				else
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
			}
	
		}
			
		foreach ($this->model['records'] as $k=>$value)
		{
	
			$table->addRow();
			foreach ($value as $l=>$v)
			{				
				if($l=='actions' && is_array($v))
				{
					$actions = new sm_TableDataViewActions();
					$actions->insertArray($v);
					$v=$actions;
	
				}
	
				$table->addCell($v);
			}
		}
		$table->setSortable();
		$options=$this->model['state'];
		$filterElement['monitor_search']=array("Search","", "monitor_search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
		$filterElement['monitor_status']=array("Select","Filter for", "monitor_status", $options,array('value'=>$this->model['state_selector'],'class'=>'input-sm'));
			
		$table->addFilter($filterElement);
	
	
		$panel = new sm_Panel();
	
		$panel->setTitle($this->model['title']);
		$panel->insert($table);
		return $panel;
	
	}
	
	function monitor_configurations_list_build($type="business")
	{
			$confirmDlg=false;
			
			$table = new sm_TableDataView("monitor_table_".$type,$this->model);
			$header=0;
			$table->makeResponsive();
			//$table->addHRow();
			$table->addHRow("",array("data-type"=>"table-header"));
			if(isset($this->model['records'][0]))
			{
				
				$headers=array_keys($this->model['records'][0]);
				foreach($headers as $l)
				{
					$header++;
					if($l=='actions')
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
					else
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
				}
			}	
			
			foreach ($this->model['records'] as $k=>$value)
			{
	
				$table->addRow();
				foreach ($value as $l=>$v)
				{
					
						
					//if($l=='actions' && is_array($v))
					//	$v=implode("",$v);
					if($l=='actions' && is_array($v))
					{
						foreach($v as $i=>$action)
						{
							
							if(isset($action['class']) && preg_match('/confirm/', $action['class']))
							{
								$v[$i]['target']="#confirmDeleteMonitor";
								$confirmDlg=true;
							}
						}
						$this->setTemplateId("actions_forms","ui.tpl.html");
						$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
						$v=$this->tpl->getTemplate("actions_forms");
							
					}
						
					$table->addCell($v);
				}
			}
			$table->setSortable();
			
			$options=array("-2"=>"All","-1"=>"Ready","0"=>"Waiting","1"=>"Monitoring","2"=>"Stopped","3"=>"Failed","4"=>"Processing");
			$filterElement['monitor_search']=array("Search","", "monitor_search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
			$filterElement['monitor_status']=array("Select","Filter for", "monitor_status", $options,array('value'=>$this->model['type_selector'],'class'=>'input-sm'));
			
			$table->addFilter($filterElement);
			
		
	
		$panel = new sm_Panel();
		if($confirmDlg)
		{
			$dlg = new sm_Dialog("confirmDeleteMonitor",CONFIRMATION_DLG);
			$dlg->setConfirmationFormClass("confirm");
			$panel->insert($dlg);
		}
		$panel->setTitle($this->model['title']);
		if($table)
			$panel->insert($table);
		return $panel;
	
	}
	
	/*********** GRAPHS *************/
	public function graphs_page_build(){
		$selection="";
		$graphs=array();
		foreach( $this->model['graphs'] as $i=>$g)
		{
			$graph = new sm_HTML();
			$graph->setTemplateId('graph_element',SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
			$graph->insertArray($g);
			$graphs[$i]=$graph;
		}
		
		
		$this->setTemplateId("tree_menu","ui.tpl.html");
		
		$tree_menu_items="";
		foreach($this->model['graphs_menu'] as $key=>$value)
		{
			$this->setTemplateId("tree_menu_item","ui.tpl.html");
			$item=($key=="_HOST_"?"Host Data":$key);
			$header=array(
					'header'=>$item,
					'url'=>'#');
			$header['class']="closed";
			if($item==$selection)
				$header['class']="active";
			if(count($value)>1)
				$this->tpl->addTemplateDataRepeat('tree_menu_item','tree_menu_subitem',  $value);
			else
			{
				$header['url']=$value[0]['url'];
				$header['class']="hidden";
			}
			$this->tpl->addTemplatedata(
					"tree_menu_item",
					$header
					);
		
			$tree_menu_items.=$this->tpl->getTemplate("tree_menu_item");
			
		}
		$this->tpl->addTemplatedata(
				"tree_menu",
				array(
						'id'=>"Graph_Menu",
						'tree_menu_items'=>$tree_menu_items,
				)
		);
		$selector="";
		if(isset($this->model['addresses']) && count($this->model['addresses'])>1)
		{		
			$selector = new sm_Panel();
			$selector->setType("default");
			$selector->setId("Graphs_Address_Selector");
			$selector->icon("<img src='".SM_IcaroApp::getFolderUrl("img")."network.png' />");
			$selector->setTitle("Address Selector");
			$selector->insert(sm_Form::buildForm("graphs_address_selector",$this));
		}
		
		$calendar = new sm_Panel();
		$calendar->setType("default");
		$calendar->setId("Graphs_Calendar");
		$calendar->icon("<img src='img/calendar.gif' />");
		$calendar->setTitle("Date Selection");
		$calendar->insert(sm_Form::buildForm("graphs_calendar",$this));
		
		$html = new sm_HTML();
		$html->setTemplateId("graphs_page",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		$html->insertArray(array(
						'address_selector'=>$selector,
						'graph_calendar'=>$calendar, 
						'graphs_menu'=>$this->tpl->getTemplate("tree_menu"),
						'graphs_menu_icon'=>"<img src='".SM_IcaroApp::getFolderUrl("img")."metrics.png"."' />",
						'graphs_title'=>"Graphs Menu",
						'graphs_main_title'=>"Graphs (".$this->model['title'].")",
						'graphs_icon'=>"<img src='".SM_IcaroApp::getFolderUrl("img")."graph.gif"."' />",
						"graphs_start_time"=>date("d/m/Y H:i:s",$this->model['start_time']),
						"graphs_end_time"=>date("d/m/Y H:i:s",$this->model['end_time']),
						"graphs"=>$graphs
				));
		
		//$html->addJS("graphs_init();","graphs_page");
		//$this->view->setMainView($html);
		$this->uiView=$html;

	}
	
	function graphs_calendar_form($form)
	{
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical,
				//"labelToPlaceholder" => 1,
				"action"=>"",
				"class"=>""
		));
		$form->addElement(new Element_HTML("<p>Choose time interval</p>"));
		$form->addElement(new Element_DateTimeLocal("From", "graph_calendar_from",array('id'=>'graph_calendar_from','class'=>'input-sm')));
		$form->addElement(new Element_DateTimeLocal("To", "graph_calendar_to",array('id'=>'graph_calendar_to','class'=>'input-sm')));
		$form->addElement(new Element_Button("Send","submit",array('name'=>"send","class"=>"button light-gray btn-xs")));
	}
	
	public function graphs_address_selector_form($form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"",//"monitor/address_selector",
				"class"=>"entries_selector"
		));
		//$form->clearValues($form->getId());
		$options=$this->model['addresses'];
		$selected=$this->model['address_selected'];
		$form->addElement(new Element_Select("Select Host:", "graphs_address_selector", $options,array('value'=>$selected,'class'=>'input-sm')));
		$form->addElement(new Element_Button("Go","submit",array('name'=>"go","class"=>"button light-gray btn-xs","style"=>"display:inline;","data-loading-text"=>"Loading...")));
	
	}
	
	public function graph_element_build(){		
		$html = new sm_HTML();
		$html->setTemplateId("graph_element",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		$html->insertArray($this->model['graphs']);
		$html->insert("add_dashboard",'<a href="javascript:void(0)" onclick="graph_add_dashboard(this,\''.$this->model['graphs']['dashurl'].'\');" title="Add to dashboard"><img src="img/menu/add.png"></a>');
		//$this->view->setMainView($html);
		$this->uiView=$html;
	}
	
	/*************** METERS *******************/
	public function meters_page_build(){
		
		$html = new sm_HTML("meters_page");
		$html->setTemplateId("meters_page",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		//$this->setTemplateId("meters_page",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		$data=array();
	
		if(isset($this->model['meters']) && count($this->model['meters'])>0)
		{
			foreach($this->model['meters'] as $k=>$v)
			{
				$meters=array();
				foreach($v['meters'] as $ele=>$m)
				{
					if(isset($m['metric']))
					{
						$meter_element = new SM_MeterGraph();
						$meter_element->setTemplateId("meter_element",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
						foreach($m as $label=>$v)
							$meter_element->insert($label,$v);
						
						$meters[]=$meter_element;
					}
					else 
						$meters[]="No meters available";
					
				}
			//	$data[]=array("title"=>$m['host'],"meters"=>$meters,"class"=>"meters-group");
				
				$meters_group = new sm_HTML();
				$meters_group->setTemplateId("meters-group",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
				if(isset($m['host']))
					$meters_group->insert("title",$m['host']);
				$meters_group->insert("class","meters-group");
				$meters_group->insert("meters",$meters);
				
				
				$html->insert("meters", $meters_group);
			}
		}
		else 
		{
			$msg = new sm_HTML("meters_page_message");
			$msg->setTemplateId("Modal_dlg","ui.tpl.html");
			$msg->insert("title", "Message");
			$msg->insert("id", "meters_page_message");	
			//$script='<script type="text/javascript">$("#meters_page_message.modal").modal("show");setTimeout(function(){$("#meters_page_message.modal").modal("hide");},1000);</script>';
			//$msg->insert("body","<p>No data available</p>".$script);
			$script='$("#meters_page_message.modal").modal("show");
					 setTimeout(function(){$("#meters_page_message.modal").modal("hide");},5000);';
				
			$msg->insert("body","<p>No data available</p>");
			$msg->addJS($script,null);
			$html->insert("meters", $msg);
		}
		$selector="";
		if(count($this->model['filter'])>2)
			$selector=sm_Form::buildForm('meters_address_selector',$this);
		
		//$this->tpl->addTemplateDataRepeat("meters_page","meters_page",$data );
	
		
		
		//$html->insert("meters", $meters);
		$html->insert('refreshUrl',$this->model['refreshUrl']);
		$html->insert('time',date("d/m/y H:i:s",$this->model['time']));
		$html->insert('selector',$selector);
	//	$html->addJs("d3.v3.min.js","meters_page",SM_IcaroApp::getFolderUrl("js"));
	//	$html->addJs("gauge.js","meters_page",SM_IcaroApp::getFolderUrl("js"));
		$this->uiView=$html;
		//$this->view->setMainView($html);		
	}
	
	public function meters_address_selector_form($form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Inline,
				//"labelToPlaceholder" => 0,
				"action"=>"",//"monitor/address_selector",
				"class"=>"entries_selector"
		));
		//$form->clearValues($form->getId());
		$options=$this->model['filter'];
		$selected=$this->model['selected'];
		$form->addElement(new Element_HTML('<label>Show for'));
		$form->addElement(new Element_Select("", "meters_address_selector", $options,array('value'=>$selected,'class'=>'input-sm')));
		$form->addElement(new Element_HTML('</label>'));
		$form->addElement(new Element_Button("Go","submit",array('name'=>"go","class"=>"button light-gray btn-xs","style"=>"display:inline;","data-loading-text"=>"Loading...")));
	
	}
	
		
	/************ CONTROLS *************/
	
	public function controls_page_build(){
		$this->uiView = new sm_HTML();
		$this->uiView->setTemplateId("controls_page",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		if(isset($this->model['controls']) && count($this->model['controls'])>0)
		{
			$selections=array_keys($this->model['controls']);
			
			foreach($selections as $i=>$selected)
			{
				//$data=array();
				if(empty($this->model['controls'][$selected]))
					continue;
				$header=0;
				$table = new sm_Table($selected);
				$table->makeResponsive();
				$table->addHRow("",array("data-type"=>"table-header"));
				foreach ($this->model['controls'][$selected] as $k=>$value)
				{
					
					$table->addRow();
					$data=array();
					//$header=0;
					/*	if($k==0)
					 $data[]=array('data'=>$selected,'rowspan'=>count($this->model['controls'][$selected]),'class'=>'control_ip');*/
					$j=0;
					$title = $value['address'];
					unset($value['address']);
					foreach ($value as $l=>$v)
					{
						if($k==0) // && $i==0)
						{
							//$header[]['header']=ucfirst(str_replace("_"," ",$l));
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
							$header++;
	
						}
							
						if($l=='actions' && is_array($v))
							$v=implode("",$v);
					/*	if($k==0) // && $j==0)
							//$data[]=array('data'=>$v,'rowspan'=>count($this->model['controls'][$selected]),'class'=>'control_ip');
							$table->addCell($v,'control_ip',array('rowspan'=>count($this->model['controls'][$selected])));
						else if($k!=0 && $j==0)
						{
							$j++;
							continue;
						}
						else*/
							//$data[]['data']=$v;
						if($j==0)
							$table->addCell($v); //,null,array("scope"=>"row"));
						else
							$table->addCell($v); //,null,array("data-title"=>ucfirst(str_replace("_"," ",$l))));
						$j++;
							
					}					
				}
				$table->addFooterCell("","", array("colspan"=>$header));
					
				$control_table = new sm_HTML();
				$control_table->setTemplateId("controls-group",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
				$control_table->insert("title",$title);
				$control_table->insert("class","controls-group");
				$control_table->insert("controls",$table);
				$this->uiView->insert("controls",$control_table);
				
				
			}
		}
		else
		{
			$dlg = new sm_HTML("controls_page_message");
			$dlg->setTemplateId("Modal_dlg","ui.tpl.html");
			$dlg->insert("title", "Message");
			$dlg->insert("id", "controls_page_message");	
			//$script='<script type="text/javascript">setTimeout(function(){$("#controls_page_message.modal").modal("hide");},5000);$("#controls_page_message.modal").modal("show");</script>';
			$script='$("#controls_page_message.modal").modal("show");
					 setTimeout(function(){$("#controls_page_message.modal").modal("hide");},5000);';
			
			$dlg->insert("body","<p>No data available</p>");
			$dlg->addJS($script,null);
			$this->uiView->insert("controls",$dlg);
			
		}
	
		$selector="";
		if(count($this->model['filter'])>2)
			$selector=sm_Form::buildForm('monitor_address_selector',$this);
		$this->uiView->insert("selector",$selector);
		$this->uiView->addJS("monitor_cmd_control_init();","controls_page");
		//$this->view->setMainView($html);
	
	}
	
	public function events_page_build(){
		$this->uiView = $html=new sm_HTML();
		$html->setTemplateId("events_page",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		$data=$this->model;
		if($data){
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_Table("monitor_events_table");
			$content->makeResponsive();
			$content->addHRow("",array("data-type"=>"table-header"));
			if($data['type']!="hosts")
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
				if($data['type']!="hosts")
					$content->addCell($check['host']."<br><small>".$check['address']."</small>");
				$content->addCell($check['name']);
				$content->addCell($check['output']);
				$content->addCell($check['time']);
				$content->addCell($check['event']);
				$content->addCell($check['state']);
				$content->addCell($check['level']);
			}
			$panel->insert($content);	
			$this->uiView->insert("events",$panel);
		}
		
	}
	
	public function monitor_console()
	{
		$this->uiView = new sm_Panel("MonitorConsole");
	/*	$html = new sm_HTML();
		$html->setTemplateId("monitor_console",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
		$html->insert("cmd",$this->model['cmd']);
		$html->insert("id",$this->model['id']);
		$this->uiView->insert($html);*/
		$html = new sm_HTML("tail");
		$html->setTemplateId("tail",sm_TailPlugin::instance()->getFolderUrl("templates")."tail.tpl.html");
		$this->uiView->insert($html);
		$this->uiView->addCss("tail.css","main",sm_TailPlugin::instance()->getFolderUrl("css"));
		$this->uiView->addJs("jqtail.js","main",sm_TailPlugin::instance()->getFolderUrl("js"));
		$this->uiView->addJs("var tailRefreshUrl='".$this->model['refreshUrl']."';");
		$this->uiView->setTitle("Monitor Console");
		//$this->uiView->addJS("logtail.js","panel",SM_IcaroApp::getFolderUrl("js"));
	}
	
	public function monitor_cmd()
	{
		$this->uiView = new sm_JSON();
		$this->uiView->insert(json_encode($this->model));
		
	}
	
	public function monitor_address_selector_form($form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Inline,
				//"labelToPlaceholder" => 0,
				"action"=>"",//"monitor/address_selector",
				"class"=>"entries_selector"
		));
	//	$form->clearValues($form->getId());
		$options=$this->model['filter'];
		$selected=$this->model['selected'];
		$form->addElement(new Element_HTML('<label>Show for'));
		$form->addElement(new Element_Select("", "monitor_address_selector", $options,array('value'=>$selected,'class'=>'input-sm')));
		$form->addElement(new Element_HTML('</label>'));
		$form->addElement(new Element_Button("Go","submit",array('name'=>"go","class"=>"button light-gray btn-xs","style"=>"display:inline;","data-loading-text"=>"Loading...")));
	
	}
	

	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form"))
		{
			if($form->getName()=="monitor_table_business")
				$form->setSubmitMethod("configurationsListFormSubmit");
			else if($form->getName()=="monitor_table_system")
				$form->setSubmitMethod("systemConfigurationsListFormSubmit");
			else if($form->getName()=="monitor_hosts_table")
				$form->setSubmitMethod("hostsListFormSubmit");
			else if($form->getName()=="monitor_vmhosts_table")
				$form->setSubmitMethod("vmhostsListFormSubmit");
			else if($form->getName()=="monitor_checks_table")
				$form->setSubmitMethod("checksListFormSubmit");
		}
	}
	/**
	 *
	 * @param array $data
	 */
	
	public function hostsListFormSubmit($data)
	{
		$value=array();
		if(isset($data['monitor_status']))
		{
			$value['state']=$data['monitor_status'];
	
		}
		if(isset($data['monitor_search']))
		{
			$value['keywords']=$data['monitor_search'];
		
		}
		$_SESSION['monitor/hosts']=$value;
			
	}
	
	/**
	 *
	 * @param array $data
	 */
	
	public function vmhostsListFormSubmit($data)
	{
		$value=array();
		if(isset($data['monitor_status']))
		{
			$value['state']=$data['monitor_status'];
	
		}
		if(isset($data['monitor_search']))
		{
			$value['keywords']=$data['monitor_search'];
	
		}
		$_SESSION['monitor/vmhosts']=$value;
			
	}
	
	/**
	 *
	 * @param array $data
	 */
	
	public function checksListFormSubmit($data)
	{
		$value=array();
		if(isset($data['monitor_status']))
		{
			$value['state']=$data['monitor_status'];
	
		}
		if(isset($data['monitor_search']))
		{
			$value['keywords']=$data['monitor_search'];
	
		}
		$_SESSION['monitor/checks']=$value;
			
	}
	
	/**
	 *
	 * @param array $data
	 */
	
	public function configurationsListFormSubmit($data)
	{
		$value=array();
		if(isset($data['monitor_status']))
		{
			$value['status']=$data['monitor_status'];
	
		}
		if(isset($data['monitor_search']))
		{
			$value['keywords']=$data['monitor_search'];
	
		}
		$_SESSION['monitor/configurations']=$value;
			
	}
	
	/**
	 *
	 * @param array $data
	 */
	
	public function systemConfigurationsListFormSubmit($data)
	{
		$value=array();
		if(isset($data['monitor_status']))
		{
			$value['status']=$data['monitor_status'];
	
		}
		if(isset($data['monitor_search']))
		{
			$value['keywords']=$data['monitor_search'];
	
		}
		$_SESSION['monitor/infrastructure']=$value;
			
	}
	
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_ConfiguratorView")
		{
			$this->extendConfiguratorView($obj);
		}
		else if(get_class($obj)=="SM_SettingsView")
		{
			$this->extendSettingsView($obj);
		}
		else if(get_class($obj)=="sm_NotificationView" || is_a($obj,"sm_NotificationWidget"))
		{
			$this->extendNotificationView($obj);
		}
	}
	
	public function extendNotificationView(sm_Widget $obj)
	{
		if(is_a($obj,"sm_NotificationWidget"))
			$obj->view->addCSS("monitor_notification.css",SM_IcaroApp::getFolderUrl("css"));
		else 
			$obj->getUIView()->addCSS("monitor_notification.css","main",SM_IcaroApp::getFolderUrl("css"));
	}
	
	public function extendConfiguratorView(sm_Widget $obj)
	{
		
		if($obj->getOp()=='list')
		{
			$this->extendConfiguratorViewList($obj);
			return;
		}	
		if($obj->getOp()=='segment::info')
		{
			$this->extendConfiguratorViewSegmentInfo($obj);
			return;
		}				
	}
	
	protected function extendConfiguratorViewSegmentInfo(sm_ViewElement $view)
	{
		$data = $view->getModel();
		if(!isset($data['graphs']) && !isset($data['meters']))
			return;
		$state = isset($data['state'])?$data['state']:null;
		if($state){
			$p = new sm_Table("Host-Status",array("class"=>"segment-icon-container"));
			$p->addHRow();
			$p->addHeaderCell("Host Status");
			$p->addRow();
			$p->addCell("<div class='segment-icon segment-icon-".$state."'>".$state."</div>");
			$view->getUIView()->insert("picture",$p);
			//$view->getUIView()->insert("picture", "<div class='segment-icon-container well'><div class='segment-icon segment-icon-".$state."'>".$state."</div></div>");
		}
		
		if(isset($data['ping'])){
			$p = new sm_Table("Host-Ping-Data",array("class"=>"segment-icon-container"));
			$p->addHRow();
			$p->addHeaderCell("Last RTA (Ping)");
			$p->addRow();
			if(is_numeric($data['ping']['value']))
			{
				$m =new SM_MeterGraph();
				$m->insertArray($data['ping']);
				$p->addCell($m);
			}
			else
			{
				$p->addCell("<div id='host-ping-value'><span class='label label-danger'>".$data['ping']['value']." ".$data['ping']['unit']."</span></div>");
			}
			$view->getUIView()->insert("picture", $p);
		}
		$graphs=null;
		if(isset($data['graphs'])) 
		{	
				$graphs = new sm_Table("Uptime");
				$graphs->addHRow();
				$graphs->addHeaderCell("Last 24H Uptime");
				
				foreach ($data['graphs'] as $graph)
				{
					$gridCol[]=$grahArea = new sm_HTML();
					$grahArea->setTemplateId("graph_img",SM_IcaroApp::getFolder("templates")."monitor.tpl.html");
					$grahArea->insertArray($graph);
					//$graphs->insert("graph",$grahArea);
					$graphs->addRow();
					$graphs->addCell($grahArea);
				}
				
		}
		$table=null;
		if(isset($data['meters'])) 
		{
			$table = new sm_TableDataView("Performance");
			$table->makeResponsive();
			$table->addHRow();
			$table->addHeaderCell("Performance",null,array("colspan"=>"5"));
			$table->addHRow("",array("data-type"=>"table-header"));
			$table->addHeaderCell("Name");
			$table->addHeaderCell("Usage");
			$table->addHeaderCell("Value");
			$table->addHeaderCell("State");
			$table->addHeaderCell("Last Check");
			foreach ($data['meters'] as $name=>$_meter)
			{
		
				foreach($_meter as $meter)
				{
					if(!isset($meter['value']))
						continue;
					$usage = new sm_HTML();
					$usage->setTemplateId("progress_bar","ui.tpl.html");
					if(!isset($meter['value']) || ($meter['value']==0 && $meter['max']==0))
					{
						$meter['value']=0;
						$usage->insert("title",sprintf("value: %01.2f%s - max: %01.2f%s",$meter['value'],$meter['unit'],$meter['max'],$meter['unit']));
					}
					else
					{
						$usage->insert("title",sprintf("value: %01.2f%s - max: %01.2f%s",$meter['value'],$meter['unit'],$meter['max'],$meter['unit']));
						$meter['value']=100*$meter['value']/$meter['max'];
					}
					
					if(!isset($meter['state']))
					{
						$usage->insert("class","progress-bar-danger");
						$meter['state']='<span class="label label-danger">UNKW</span>';
					}
					else
					{
						if($meter['state']==0)
						{
							$usage->insert("class","progress-bar-success");
							$meter['state']='<span class="label label-success">OK</span>';
						}
						if($meter['state']==1)
						{
							if($meter['value']>=$meter['warning'])
							{
								$usage->insert("class","progress-bar-warning");
								$meter['state']='<span class="label label-warning">WARN</span>';
							}
							else
							{
								$usage->insert("class","progress-bar-success");
								$meter['state']='<span class="label label-success">OK</span>';
							}
						}
						if($meter['state']==2)
						{
							if($meter['value']>=$meter['critical'])
							{
								$usage->insert("class","progress-bar-danger");
								$meter['state']='<span class="label label-danger">CRIT</span>';
							}
							else if($meter['value']>=$meter['warning'])
							{
								$usage->insert("class","progress-bar-warning");
								$meter['state']='<span class="label label-warning">WARN</span>';
							}
							else
							{
								$usage->insert("class","progress-bar-success");
								$meter['state']='<span class="label label-success">OK</span>';
							}
						}
						if($meter['state']==3)
						{
							$usage->insert("class","progress-bar-danger");
							$meter['state']='<span class="label label-danger">UNKW</span>';
						}
					}
					
					$usage->insert("value",$meter['value']);
					$usage->insert("min",0);
					$usage->insert("max",100);
				
				
					$table->addRow();
					if($name == "Disk")
						$table->addCell($meter['display_name']."<br><small>".$meter['metric']."</small>");
					else
						$table->addCell($meter['display_name']);
					$table->addCell($usage);
					$table->addCell(sprintf("%d%%",$meter['value']));
					$table->addCell($meter['state']);
		
					$table->addCell(date("d/m/y H:i:s",$meter['last_check']));
				}
			}
		}
		if($table && $graphs)
		{
			
			//$panel->setTitle("Status");
			//$panel->setType("default");
			$layout = new sm_Grid();
			$layout->addRow(array($table,$graphs),array(7,5));
			$view->getUIView()->insert("childs",$layout);
			//$view->getUIView()->insert("childs",$graphs);
		}
		
		
		
	}
	
	protected function extendConfiguratorViewList($view)
	{
		$data = $view->getModel();
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
			$action=array();
			if($monitor_data['status']==0) //Waiting
			{
				$data['records'][$i]['Monitor Status']="<span class='label label-warning'>Waiting</span>";
				if(sm_ACL::checkPermission("Monitor::Edit"))
					$action['monitor_start']='<a title="Start monitor" class=configuration_action href="monitor/insert/'.$d['cid'].'"><img src="'.SM_IcaroApp::getFolderUrl("img").'on.png" /></a>';
			}
			else if($monitor_data['status']==1)//Monitoring
			{
				$data['records'][$i]['Monitor Status']="<span class='label label-success'>Monitoring</span>";
				if(sm_ACL::checkPermission("Monitor::Edit"))
					$action['monitor_stop']='<a title="Stop monitor" class=configuration_action href="monitor/stop/'.$d['cid'].'"><img src="'.SM_IcaroApp::getFolderUrl("img").'off.png" /></a>';
			}
			else if($monitor_data['status']==2)//Stopped
			{
				$data['records'][$i]['Monitor Status']="<span class='label label-danger'>Stopped</span>";
				if(sm_ACL::checkPermission("Monitor::Edit"))
					$action['monitor_stop']='<a title="Start monitor" class=configuration_action href="monitor/start/'.$d['cid'].'"><img src="'.SM_IcaroApp::getFolderUrl("img").'on.png" /></a>';
			}
			else if($monitor_data['status']==3)//Failed
			{
				$data['records'][$i]['Monitor Status']="<span class='label label-danger'>Failed</span>";
				if(sm_ACL::checkPermission("Monitor::Edit"))
					$action['monitor_stop']='<a title="Start monitor" class=configuration_action href="monitor/insert/'.$d['cid'].'"><img src="'.SM_IcaroApp::getFolderUrl("img").'on.png" /></a>';
			}
		
		
			if(!empty($action))
				$data['records'][$i]['actions']=array_merge($action,$actions);
		}
		$view->setModel($data);
	}
	
	public function extendSettingsView($obj)
	{
		
				$panel=$obj->getUIView()->getUIElement("AppicationSettingsPanel");
				$this->data=$obj->getData();
				$panel->insert(sm_Form::buildForm("monitor_config", $this));
			
		
	}
	
	function monitor_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_Monitor';
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.str_replace("SM_","",$m).'</legend>'));
			foreach($this->data[$m] as $item=>$i)
			{
				$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			$form->addElement(new Element_Button("Save","",array("class"=>"button light-gray")));
			$form->addElement(new Element_HTML('</div>'));
		}
	
	
	}
	
	protected function monitor_navbar($activeLink,$type="main"){
		
		if($type=="secondary"){
			$menu = new sm_NavBar("MonitorNavBar");
			$menu->setTemplateId("small_button_bar");
			
		}
		if($type=="main"){
			$menu = new sm_NavBar("MonitorMainBar");
			$menu->setActive($activeLink);
			
		}
		$item = new sm_MenuItem();
		
		$menu->insert("brand","Monitor");
		$item->loadByPath("monitor/configurations");
		$title = $item->gettitle()!=""? $item->gettitle():'Business';
		$menu->insert("business_list", array("url"=>"monitor/configurations","title"=>$title,"icon"=>"sm-icon sm-icon-business"));
		
		$item->loadByPath("monitor/infrastructure");
		$title = $item->gettitle()!=""? $item->gettitle():'Host Groups';
		$menu->insert("system_list", array("url"=>"monitor/infrastructure","title"=>$title,"icon"=>"sm-icon sm-icon-cluster"));
		
		$item->loadByPath("monitor/hosts");
		$title = $item->gettitle()!=""? $item->gettitle():'Hosts';
		$menu->insert("hosts_list", array("url"=>"monitor/hosts","title"=>$title,"icon"=>"sm-icon sm-icon-hosts"));
		
		$item->loadByPath("monitor/vmhosts");
		$title = $item->gettitle()!=""? $item->gettitle():'Virt. Machines';
		$menu->insert("vmhosts_list", array("url"=>"monitor/vmhosts","title"=>$title,"icon"=>"sm-icon sm-icon-vms"));
		
		$item->loadByPath("monitor/devices");
		$title = $item->gettitle()!=""? $item->gettitle():'Devices';
		$menu->insert("devices_list", array("url"=>"monitor/devices","title"=>$title,"icon"=>"sm-icon sm-icon-devices"));
		
		$item->loadByPath("monitor/checks");
		$title = $item->gettitle()!=""? $item->gettitle():'Checks';
		$menu->insert("checks_list", array("url"=>"monitor/checks","title"=>$title,"icon"=>"sm-icon sm-icon-checks"));
		
		return $menu;
	}
	
	

	
	static function menu(sm_MenuManager $menu)
	{
		//$menu->setSubLink("Monitor","Configuration","monitor/configuration");
		$menu->setMainLink("Monitor","#","cog");
		$menu->setSubLink("Monitor","Business","monitor/configurations");
		$menu->setSubLink("Monitor","Host Groups","monitor/infrastructure");
		$menu->setSubLink("Monitor","Hosts","monitor/hosts");
		$menu->setSubLink("Monitor","Virtual Machines","monitor/vmhosts");
		$menu->setSubLink("Monitor","Devices","monitor/devices");
		$menu->setSubLink("Monitor","Checks","monitor/checks");
		//$menu->setSubLink("Monitor","Configurations","monitor/configuration");
	}
}