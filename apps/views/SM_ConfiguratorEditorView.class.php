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

class SM_ConfiguratorEditorView extends sm_ViewElement
{
	protected $filters;
	function __construct($data=NULL)
	{
		parent::__construct($data);
		$this->uiView=new sm_Page("SM_ConfiguratorEditorView");
	}

	/*public function pre_build(){

	}*/

	/**
	 * Create the HTML code for the module.
	 */
	public function build() {

		switch ($this->op)
		{
			case 'edit':
				$this->configuration_editor_build();
				break;
			case 'edit::service':
				$this->service_editor_build();
				break;
		}
	}
	function configuration_editor_build()
	{
		$panel = new sm_Panel();
		if(!preg_match("/new-/",$this->model['id']))
			$panel->setTitle($this->model['configuration']->getname());
		else
			$panel->setTitle("New Configuration");
		//$panel->icon("<i class='configurator_queue_icon'></i>");
		$panel->insert(sm_Form::buildForm("configuration_data", $this));
		$panel->setType("default");
		
		
		
		$nav_menu_edit=new sm_NavBar("ConfigurationEditMenu");
		$nav_menu_edit->setTemplateId("submenu");
		$nav_menu_edit->setClass("dropdown-menu");
		$nav_menu_edit->insert("Configuration",array("url"=>"configurator/editor/".$this->model['id'],"title"=>"Configuration"));
		$nav_menu_edit->insert("Applications",array("url"=>"configurator/editor/".$this->model['id']."/Applications","title"=>"Applications"));
		$nav_menu_edit->insert("Tenants",array("url"=>"configurator/editor/".$this->model['id']."/Tenants","title"=>"Tenants"));
		$nav_menu_edit->insert("Hosts",array("url"=>"configurator/editor/".$this->model['id']."/Hosts","title"=>"Hosts"));
		$nav_menu_edit->insert("Devices",array("url"=>"configurator/editor/".$this->model['id']."/Devices","title"=>"Devices"));
		
		$nav_menu=new sm_NavBar("ConfigurationEditTabMenu");
		$nav_menu->setTemplateId("nav_bar");
		$nav_menu->insert("New",array("url"=>"configurator/editor/","title"=>"New"));
		$nav_menu->insert("Save",array("url"=>"configurator/editor/save/".$this->model['id'],"title"=>"Save"));
		$nav_menu->insert("Load",array("url"=>"configurator/editor/load/","title"=>"Load"));
		$nav_menu->insert("Open",array("url"=>"configurator/editor/open/","title"=>"Open"));
		$nav_menu->insert("Edit",array("url"=>"#","title"=>"Edit","class"=>"dropdown","link_attr"=>"data-toggle='dropdown'","caret"=>"caret"));
		
		
		/*$nav_menu->insert("Configuration",array("url"=>"configurator/editor/".$this->model['id'],"title"=>"Configuration"));
		$nav_menu->insert("Applications",array("url"=>"configurator/editor/".$this->model['id']."/Applications","title"=>"Applications"));
		$nav_menu->insert("Tenants",array("url"=>"configurator/editor/".$this->model['id']."/Tenants","title"=>"Tenants"));
		$nav_menu->insert("Hosts",array("url"=>"configurator/editor/".$this->model['id']."/Hosts","title"=>"Hosts"));
		$nav_menu->insert("Devices",array("url"=>"configurator/editor/".$this->model['id']."/Devices","title"=>"Devices"));
		*/
		$nav_menu->insertSubLevel("Edit", $nav_menu_edit);
		
		$nav_menu->setActive($this->getType());
		$nav_menu_edit->setActive($this->getType());
		//$tab_menu->insert("list",array("url"=>"menu/list","title"=>"Menus"));
		$main= new sm_HTML();
		$main->setTemplateId("configuration_page",SM_IcaroApp::getFolderUrl("templates")."configurator.tpl.html");
		
		$html = new sm_HTML("ConfigurationEditPage");
		$html->insert("nav_menu",$nav_menu);
		
		$html->insert("container","<div id=ConfigurationEditContainer>");

		/*$tabs = new sm_Tabs();
		$tabs->insert("Applications", array("tab_title"=>"Applications","tab_data"=>$this->applications_editor()));
		$tabs->insert("Tenants", array("tab_title"=>"Tenants","tab_data"=>$this->tenants_editor()));
		$tabs->insert("Hosts", array("tab_title"=>"Hosts","tab_data"=>$this->hosts_editor()));
		$tabs->insert("Devices", array("tab_title"=>"Devices","tab_data"=>$this->devices_editor()));
		//$tabs->insert("Services", array("tab_title"=>"Services","tab_data"=>$this->services_editor()));
		
		$tabs->setActive($this->getType());
		$content=$tabs;*/
		$content="";
		if($this->getType()=="Applications")
			$content =  $this->applications_editor();
		else if($this->getType()=="Tenants")
			$content = "";// $this->tenants_editor();
		else if($this->getType()=="Hosts")
			$content = $this->hosts_editor();
		else if($this->getType()=="Devices")
			$content =  $this->devices_editor();
		else if($this->getType()=="Services")
			$content =  $this->services_editor();
		else 
			$content=$panel;
		$main->insert("configuration_data", $content);
		
		$html->insert("content",$main);
		$html->insert("end-container","</div>");
	
	
		$this->uiView = new sm_Page();
		$this->uiView->setTitle("Configuration Editor");
		$this->uiView->insert($html);
		$this->uiView->addJs("configurator_editor.js","main",SM_IcaroApp::getFolderUrl("js"));
		$this->uiView->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->configuration_tree_build();
		//$this->uiView->addJS("configurator.js","main",SM_IcaroApp::getFolderUrl("js"));
		//$this->addView();
	}
	
	function applications_editor()
	{
		$nav_menu=new sm_TabMenu("ApplicationsEditNavBar");
		if(count($this->model['Applications'])>0 && !isset($this->model['sid']))
		{
			$this->model['aid']=$this->model['Applications'][0]->getaid();
			$this->model['current_application']=$this->model['Applications'][0];
			$nav_menu->setActive(0);
		}
		foreach($this->model['Applications'] as $i=>$application)
		{
			if($application->getaid()==$this->model['sid'])
			{
				$this->model['current_application']=$application;
				$this->model['aid']=$application->getaid();
				$nav_menu->setActive($i);
			}
			$title = $application->getname();
			$nav_menu->insert($i,array("url"=>"configurator/editor/".$this->model['id']."/Applications/".$application->getaid(),"title"=>$title,"link_class"=>"button"));
		}
		$nav_menu->insert("add",array("url"=>"#","title"=>"New Application","class"=>"tab-right","link_class"=>"button","tab"=>'modal',"link_attr"=>"data-target='#AddAppDlg'"));
		$title = isset($this->model['current_application'])?$this->model['current_application']->getname():"";
	
		$services=new sm_HTML("Services");
		$services->insert("title","<h4><i>".$title."</i> Services</h4>");
	
		$services->insert(0,"<div id=services_editor_menu >");
		$services->insert(1,'<button class=button class="btn" data-action="expand-all">Expand All</button>');
		$services->insert(2,'<button class=button class="btn"  data-action="collapse-all">Collapse All</button>');
		if(isset($this->model['aid']))
			$services->insert(2,"<button class=button href='configurator/service/editor/".$this->model['id']."/".$this->model['aid']."' title=Edit data-toggle='modal' data-target='#AddServiceDlg'>Add Service</button>");
	
		$services->insert(4,"</div>");
	
		$services->insert(5,"<div id=services_list class='dd'>");
		$code=$this->build_service_list();
		$services->insert(6,$code);
		//	$panel->insert(7,sm_Form::buildForm("menu_reorder", $this));
		$services->insert(8,"</div>");
		$panel=new sm_HTML("ApplicationsData");
		$panel->insert("title","<h4><i>".$title."</i> Data</h4>");
		if(isset($this->model['aid']))
			$panel->insert("form", sm_Form::buildForm("application_data", $this));
	
		$html = new sm_HTML();
		$html->insert("nav",$nav_menu);
		$html->insert("container","<div id=applications_editor_container class=component_editor>");
		$html->insert("column_left","<div class=col-md-6>");
		$html->insert("panel1",$panel);
		$html->insert("column_left_end","</div>");
		$html->insert("column_right","<div class=col-md-6>");
	
	
		$html->insert("panel2",$services);
		$html->insert("column_right_end","</div>");
	
		$deleteServiceDlg=new sm_HTML("DeleteServiceDlg");
		$deleteServiceDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteServiceDlg->insert("title", "Delete Confirmation");
		$deleteServiceDlg->insert("body", "Do you want to proceed?");
		$deleteServiceDlg->insert("id", "DeleteServiceDlg");
	
		$deleteAppDlg=new sm_HTML("DeleteAppDlg");
		$deleteAppDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteAppDlg->insert("title", "Delete Confirmation");
		$deleteAppDlg->insert("body", "All services will be deleted. Do you want to proceed?");
		$deleteAppDlg->insert("id", "DeleteAppDlg");
	
		$addServiceDlg=new sm_HTML("AddServiceDlg");
		$addServiceDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addServiceDlg->insert("title", "Edit Service");
		$addServiceDlg->insert("id", "AddServiceDlg");
		$addServiceDlg->insert("btn1", "Close");
		$addServiceDlg->insert("btn2", "Save");
	
		$addAppDlg=new sm_HTML("AddAppDlg");
		$addAppDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addAppDlg->insert("title", "Add New Application");
		$addAppDlg->insert("id", "AddAppDlg");
		$addAppDlg->insert("class", "AddItemtDlg");
		$addAppDlg->insert("body",sm_Form::buildForm("application_new", $this));
		$addAppDlg->insert("btn1", "Close");
		$addAppDlg->insert("btn2", "Save");
	
		//	$html->insert("form", sm_Form::buildForm("menu_delete_item", $this));
		$html->insert("end-container","</div>");
		$html->insert("delete-service-dialog", $deleteServiceDlg);
		$html->insert("delete-app-dialog", $deleteAppDlg);
		$html->insert("add-app-dialog", $addAppDlg);
		$html->insert("add-service-dialog", $addServiceDlg);
	
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Applications');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($html);
	
		return $panel;
		
	}
	
	function hosts_editor()
	{
		$nav_menu=new sm_TabMenu("HostEditNavBar");
		if(count($this->model['Hosts'])>0 && !isset($this->model['sid']))
		{
			$this->model['hid']=$this->model['Hosts'][0]->gethid();
			$this->model['current_host']=$this->model['Hosts'][0];
			$nav_menu->setActive(0);
		}
		foreach($this->model['Hosts'] as $i=>$host)
		{
			if($host->gethid()==$this->model['sid'])
			{
				$this->model['current_host']=$host;
				$this->model['hid']=$host->gethid();
				$nav_menu->setActive($i);
			}
			$title = $host->getname();
			$nav_menu->insert($i,array("url"=>"configurator/editor/".$this->model['id']."/Hosts/".$host->gethid(),"title"=>$title,"link_class"=>"button"));
		}
		$nav_menu->insert("add",array("url"=>"#","title"=>"New Host","class"=>"tab-right","link_class"=>"button","tab"=>'modal',"link_attr"=>"data-target='#AddHostDlg'"));
	
		$title = isset($this->model['current_host'])?$this->model['current_host']->getname():"";
	
		$metrics=new sm_HTML("Metrics");
		$metrics->insert("title","<h4><i>".$title."</i> Metrics</h4>");
		$metrics->insert(0,"<div id=metrics_editor_menu >");
		if(isset($this->model['hid']))
		{
			//$metrics->insert(1,'<button class=button class="btn" data-action="expand-all">Expand All</button>');
			//	$metrics->insert(2,'<button class=button class="btn"  data-action="collapse-all">Collapse All</button>');
			$metrics->insert(3,"<button class=button href='configurator/editor/add/metric/".$this->model['id']."/Hosts/".$this->model['hid']. "' title=Edit data-toggle='modal' data-target='#AddMetricDlg'>Add Metric</button>");
		}
		$metrics->insert(4,"</div>");
		$metrics->insert(5,"<div id=metrics_editor class='dd'>");
		//	$code=$this->build_menu($items);
		//	$panel->insert(6,$code);
		//	$panel->insert(7,sm_Form::buildForm("menu_reorder", $this));
		$metrics->insert(8,"</div>");
	
		$hostData=new sm_HTML("HostData");
		$hostData->insert("title","<h4><i>".$title."</i> Data</h4>");
		if(isset($this->model['hid']))
			$hostData->insert("form", sm_Form::buildForm("host_data", $this));
	
		$deleteMetricDlg=new sm_HTML("DeleteMetricDlg");
		$deleteMetricDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteMetricDlg->insert("title", "Delete Confirmation");
		$deleteMetricDlg->insert("body", "Do you want to proceed?");
		$deleteMetricDlg->insert("id", "DeleteMetricDlg");
	
		$deleteHostDlg=new sm_HTML("DeleteHostDlg");
		$deleteHostDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteHostDlg->insert("title", "Delete Confirmation");
		$deleteHostDlg->insert("body", "All metrics will be deleted. Do you want to proceed?");
		$deleteHostDlg->insert("id", "DeleteHostDlg");
	
		$addMetricDlg=new sm_HTML("AddMetricDlg");
		$addMetricDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addMetricDlg->insert("title", "Edit Metric");
		$addMetricDlg->insert("id", "AddMetricDlg");
		$addMetricDlg->insert("btn1", "Close");
		$addMetricDlg->insert("btn2", "Save");
	
		$addHostDlg=new sm_HTML("AddHostDlg");
		$addHostDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addHostDlg->insert("title", "Add New Host");
		$addHostDlg->insert("id", "AddHostDlg");
		$addHostDlg->insert("class", "AddItemtDlg");
		$addHostDlg->insert("body",sm_Form::buildForm("host_new", $this));
		$addHostDlg->insert("btn1", "Close");
		$addHostDlg->insert("btn2", "Save");
	
		$html = new sm_HTML();
		$html->insert("nav",$nav_menu);
		$html->insert("container","<div id=hosts_editor_container class=component_editor>");
		$html->insert("column_left","<div class=col-md-6>");
		$html->insert("panel1",$hostData);
		$html->insert("column_left_end","</div>");
		$html->insert("column_right","<div class=col-md-6>");
		$html->insert("panel2",$metrics);
		$html->insert("column_right_end","</div>");
		//	$html->insert("form", sm_Form::buildForm("menu_delete_item", $this));
		$html->insert("end-container","</div>");
		$html->insert("delete-metric-dialog", $deleteMetricDlg);
		$html->insert("delete-host-dialog", $deleteHostDlg);
		$html->insert("add-host-dialog", $addHostDlg);
		$html->insert("add-metric-dialog", $addMetricDlg);
	
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Hosts');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($html);
	
		return $panel;
		
	}
	
	function devices_editor()
	{
		$nav_menu=new sm_TabMenu("DeviceEditNavBar");
		if(count($this->model['Devices'])>0 && !isset($this->model['sid']))
		{
			$this->model['did']=$this->model['Devices'][0]->getdid();
			$this->model['current_device']=$this->model['Devices'][0];
			$nav_menu->setActive(0);
		}
		foreach($this->model['Devices'] as $i=>$device)
		{
			if($device->getdid()==$this->model['sid'])
			{
				$this->model['current_device']=$device;
				$this->model['did']=$device->getdid();
				$nav_menu->setActive($i);
			}
			$title = $device->getname();
			$nav_menu->insert($i,array("url"=>"configurator/editor/".$this->model['id']."/Devices/".$device->getdid(),"title"=>$title,"link_class"=>"button"));
		}
		$nav_menu->insert("add",array("url"=>"#","title"=>"New Device","class"=>"tab-right","link_class"=>"button","tab"=>'modal',"link_attr"=>"data-target='#AddDeviceDlg'"));
	
		$title = isset($this->model['current_device'])?$this->model['current_device']->getname():"";
	
		$metrics=new sm_HTML("Metrics");
		$metrics->insert("title","<h4><i>".$title."</i> Metrics</h4>");
		$metrics->insert(0,"<div id=metrics_editor_menu >");
		if(isset($this->model['hid']))
		{
			//$metrics->insert(1,'<button class=button class="btn" data-action="expand-all">Expand All</button>');
			//	$metrics->insert(2,'<button class=button class="btn"  data-action="collapse-all">Collapse All</button>');
			$metrics->insert(3,"<button class=button href='configurator/editor/add/metric/".$this->model['id']."/Devices/".$this->model['did']. "' title=Edit data-toggle='modal' data-target='#AddMetricDlg'>Add Metric</button>");
		}
		$metrics->insert(4,"</div>");
		$metrics->insert(5,"<div id=metrics_editor class='dd'>");
		//	$code=$this->build_menu($items);
		//	$panel->insert(6,$code);
		//	$panel->insert(7,sm_Form::buildForm("menu_reorder", $this));
		$metrics->insert(8,"</div>");
	
		$deviceData=new sm_HTML("DeviceData");
		$deviceData->insert("title","<h4><i>".$title."</i> Data</h4>");
		if(isset($this->model['did']))
			$deviceData->insert("form", sm_Form::buildForm("device_data", $this));
	
		$deleteMetricDlg=new sm_HTML("DeleteMetricDlg");
		$deleteMetricDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteMetricDlg->insert("title", "Delete Confirmation");
		$deleteMetricDlg->insert("body", "Do you want to proceed?");
		$deleteMetricDlg->insert("id", "DeleteMetricDlg");
	
		$deleteDeviceDlg=new sm_HTML("DeleteDeviceDlg");
		$deleteDeviceDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteDeviceDlg->insert("title", "Delete Confirmation");
		$deleteDeviceDlg->insert("body", "All metrics will be deleted. Do you want to proceed?");
		$deleteDeviceDlg->insert("id", "DeleteHostDlg");
	
		$addMetricDlg=new sm_HTML("AddMetricDlg");
		$addMetricDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addMetricDlg->insert("title", "Edit Metric");
		$addMetricDlg->insert("id", "AddMetricDlg");
		$addMetricDlg->insert("btn1", "Close");
		$addMetricDlg->insert("btn2", "Save");
	
		$addDeviceDlg=new sm_HTML("AddDeviceDlg");
		$addDeviceDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addDeviceDlg->insert("title", "Add New Device");
		$addDeviceDlg->insert("id", "AddDeviceDlg");
		$addDeviceDlg->insert("class", "AddItemtDlg");
		$addDeviceDlg->insert("body",sm_Form::buildForm("device_new", $this));
		$addDeviceDlg->insert("btn1", "Close");
		$addDeviceDlg->insert("btn2", "Save");
	
		$html = new sm_HTML();
		$html->insert("nav",$nav_menu);
		$html->insert("container","<div id=devices_editor_container class=component_editor>");
		$html->insert("column_left","<div class=col-md-6>");
		$html->insert("panel1",$deviceData);
		$html->insert("column_left_end","</div>");
		$html->insert("column_right","<div class=col-md-6>");
		$html->insert("panel2",$metrics);
		$html->insert("column_right_end","</div>");
		//	$html->insert("form", sm_Form::buildForm("menu_delete_item", $this));
		$html->insert("end-container","</div>");
		$html->insert("delete-metric-dialog", $deleteMetricDlg);
		$html->insert("delete-device-dialog", $deleteDeviceDlg);
		$html->insert("add-device-dialog", $addDeviceDlg);
		$html->insert("add-metric-dialog", $addMetricDlg);
	
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Devices');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($html);
	
		return $panel;
		
	}
	
	function services_editor()
	{
		
	
		$this->model['service']=isset($this->model['Services'])?$this->model['Services']:null;
		$title = isset($this->model['Services'])?$this->model['Services']->getname():"";
	
		$metrics=new sm_HTML("Metrics");
		$metrics->insert("title","<h4><i>".$title."</i> Metrics</h4>");
		$metrics->insert(0,"<div id=metrics_editor_menu >");
		if(isset($this->model['sid']))
		{
			//$metrics->insert(1,'<button class=button class="btn" data-action="expand-all">Expand All</button>');
			//	$metrics->insert(2,'<button class=button class="btn"  data-action="collapse-all">Collapse All</button>');
			$metrics->insert(3,"<button class=button href='configurator/editor/add/metric/".$this->model['id']."/Services/".$this->model['sid']. "' title=Edit data-toggle='modal' data-target='#AddMetricDlg'>Add Metric</button>");
		}
		$metrics->insert(4,"</div>");
		$metrics->insert(5,"<div id=metrics_editor class='dd'>");
		//	$code=$this->build_menu($items);
		//	$panel->insert(6,$code);
		//	$panel->insert(7,sm_Form::buildForm("menu_reorder", $this));
		$metrics->insert(8,"</div>");
	
		$serviceData=new sm_HTML("ServiceData");
		$serviceData->insert("title","<h4><i>".$title."</i> Data</h4>");
		if(isset($this->model['sid']))
			$serviceData->insert("form", sm_Form::buildForm("service_data", $this));
	
		$deleteMetricDlg=new sm_HTML("DeleteMetricDlg");
		$deleteMetricDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteMetricDlg->insert("title", "Delete Confirmation");
		$deleteMetricDlg->insert("body", "Do you want to proceed?");
		$deleteMetricDlg->insert("id", "DeleteMetricDlg");
	
		$deleteDeviceDlg=new sm_HTML("DeleteDeviceDlg");
		$deleteDeviceDlg->setTemplateId("YesNo_Dlg","ui.tpl.html");
		$deleteDeviceDlg->insert("title", "Delete Confirmation");
		$deleteDeviceDlg->insert("body", "All metrics will be deleted. Do you want to proceed?");
		$deleteDeviceDlg->insert("id", "DeleteHostDlg");
	
		$addMetricDlg=new sm_HTML("AddMetricDlg");
		$addMetricDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addMetricDlg->insert("title", "Edit Metric");
		$addMetricDlg->insert("id", "AddMetricDlg");
		$addMetricDlg->insert("btn1", "Close");
		$addMetricDlg->insert("btn2", "Save");
	
		$addDeviceDlg=new sm_HTML("AddServiceDlg");
		$addDeviceDlg->setTemplateId("TwoButtonsModal_Dlg","ui.tpl.html");
		$addDeviceDlg->insert("title", "Add New Service");
		$addDeviceDlg->insert("id", "AddServiceDlg");
		$addDeviceDlg->insert("class", "AddItemtDlg");
		$addDeviceDlg->insert("body",sm_Form::buildForm("service_new", $this));
		$addDeviceDlg->insert("btn1", "Close");
		$addDeviceDlg->insert("btn2", "Save");
	
		$html = new sm_HTML();
		
		$html->insert("container","<div id=services_editor_container class=component_editor>");
		$layout=new sm_Grid();
		$layout->addRow(array($serviceData,$metrics),array(6,6));
		$html->insert("layout",$layout);
		//	$html->insert("form", sm_Form::buildForm("menu_delete_item", $this));
		$html->insert("end-container","</div>");
		$html->insert("delete-metric-dialog", $deleteMetricDlg);
		$html->insert("delete-device-dialog", $deleteDeviceDlg);
		$html->insert("add-device-dialog", $addDeviceDlg);
		$html->insert("add-metric-dialog", $addMetricDlg);
	
		$panel = new sm_Panel();
		$panel->setType("default");
		$panel->setTitle('Services');
		//$panel->icon(sm_formatIcon("user"));
		$panel->insert($html);
	
		return $panel;
	
	}
	
	function application_new_form(sm_Form $form)
	{
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/add/application"
		));
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Textbox("Insert Application Name", "name"));
	}
	
	function host_new_form(sm_Form $form)
	{
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/add/host"
		));
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Textbox("Insert Host Name", "name"));
	}
	
	function tenant_new_form(sm_Form $form)
	{
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/add/tenant"
		));
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Textbox("Insert Tenant Name", "name"));
	}
	
	function device_new_form(sm_Form $form)
	{
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/add/device"
		));
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Textbox("Insert Device Name", "name"));
	}
	
	function configuration_data_form(sm_Form $form)
	{
		$var = sm_obj2array($this->model['configuration']->getConfiguration());
		
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/update/segment"
		));
	
		foreach($var as $i=>$v){
			if($i=="cid" || $i=="segment" || $i=="database")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		
		
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
	function application_data_form(sm_Form $form)
	{
		$var = sm_obj2array_r($this->model['current_application']);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/update/application"
		));
	
		foreach($var as $i=>$v){
			if($i=="cid"|| $i=="aid" || $i=="services")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Hidden("applications","aid:".$this->model['aid']));
		$form->addElement(new Element_Hidden("segment","applications"));
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
	function host_data_form(sm_Form $form)
	{
		$view = new View_Grid();
		$var = sm_obj2array_r($this->model['current_host']);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => $view,
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/update/segment"
		));
		$c=0;
		foreach($var as $i=>$v){
			if($i=="cid"|| $i=="hid" || $i=="minfo_id")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			if($i=="type")
				$form->addElement(new Element_Select(ucfirst($i), ucfirst($i), array("vmhost"=>"Virtual Machine","host"=>"Host"),array("value"=>$v)));
			/*else if($i=="os")
				$form->addElement(new Element_Select(ucfirst($i), ucfirst($i), array("vmhost"=>"Virtual Machine","host"=>"Host"),array("value"=>$v)));*/
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
			$c++;
			if($c%2==0)
			{
				$view->map['layout'][]=2;
				$view->map['widths'][]=6;
				$view->map['widths'][]=6;
			}
			
		}
		if($c%2!=0)
		{
			$view->map['layout'][]=1;
			$view->map['widths'][]=6;
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Hidden("segment","hosts"));
		$form->addElement(new Element_Hidden("sid","hid:".$this->model['hid']));
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
	function device_data_form(sm_Form $form)
	{
		$var = sm_obj2array_r($this->model['current_device']);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/update/segment"
		));
	
		foreach($var as $i=>$v){
			if($i=="cid"|| $i=="did" || $i=="minfo_id")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Hidden("sid","did:".$this->model['did']));
		$form->addElement(new Element_Hidden("segment","devices"));
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
	function tenant_data_form(sm_Form $form)
	{
		$var = sm_obj2array_r($this->model['current_tenant']);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/update/segment"
		));
	
		foreach($var as $i=>$v){
			if($i=="cid"|| $i=="tid")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Hidden("sid","tid:".$this->model['tid']));
		$form->addElement(new Element_Hidden("segment","tenants"));
		$form->addElement(new Element_Hidden("cmd"));
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
	
	}
	
		
	function configuration_tree_build(){
		
		$data=$this->model['configuration']->build("*");
		$data['@attributes']['url']="configurator/editor/";
		
		$xml=Array2XML::createXML("configuration",$data);
		
		$xslDoc = new DOMDocument();
		$xslDoc->load(SM_IcaroApp::getFolder("schema")."configurationTo_xml_flat.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		$xmlDom=$proc->transformToDoc($xml);
		
		$xml_str=$xmlDom->saveXML($xmlDom->getElementsByTagName("root")->item(0));
		$script='$(document).ready(function(){
			$("#configuration_tree").jstree({
				"core" : { "initially_open" : [ "'.$this->model['id'].'","applications","devices","tenants","hosts" ] },
				"xml_data":{"data":\''.$xml_str.'\'},
				"xsl" : "flat",
				"types" : {
					"valid_children" : [ "root","applications","devices","hosts","tenants","SLA" ],
					"types" : {
						"root" : {
							"icon" : {
								"image" : "'.DEFAULT_ICO.'"
							},
						},
						"applications" : {
							"icon" : {
								"image" : "'.APPLICATIONS_ICO.'"
							},
						},
						"tenants" : {
							"icon" : {
								"image" : "'.TENANTS_ICO.'"
							},
						},
						"hosts" : {
							"icon" : {
								"image" : "'.HOSTS_ICO.'"
							},
						},
						"host" : {
							"icon" : {
								"image" : "'.HOST_ICO.'"
							},
						},
						"vmhost" : {
							"icon" : {
								"image" : "'.VMHOST_ICO.'"
							},
						},
						"service" : {
							"icon" : { 
								"image" : "'.SERVICES_ICO.'" 
							},
						},
						"devices" : {
							"icon" : {
								"image" : "'.DEVICES_ICO.'"
							},
						},
						"firewall" : {
							"icon" : {
								"image" : "'.FIREWALL_ICO.'"
							},
						},
						"router" : {
							"icon" : {
								"image" : "'.ROUTER_ICO.'"
							},
						},
						"externalstorage" : {
							"icon" : {
								"image" : "'.EXTERNALSTORAGE_ICO.'"
							},
						},
						"SLA" : {
							"icon" : { 
								"image" : "'.SLA_ICO.'" 
							},
						},
				},
						},
				"plugins" : [ "themes", "xml_data", "types", "ui" ]
			}).bind("select_node.jstree", function (e, data) { 
						if($(data.rslt.obj).find("a").attr("href")!="#")
						{
							url = $(data.rslt.obj).find("a").attr("href");
							window.location=url;
						}
						else if($(data.rslt.obj).attr("rel")=="root")
						{
							window.location="'.$data['@attributes']['url'].$this->model['id'].'";
						}
						
			});
										
		});';
		
		
		/* CSS */
		$this->uiView->addCss("jstreestyle.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addCss("jquery-ui-1.10.2.custom.css","main","css/smoothness/");
		/* Javascript */
		$this->uiView->addJS("jquery.jstree.js","main","js/");
		$this->uiView->addJs("jquery-ui-1.10.2.custom.min.js","main","js/");
		$this->uiView->addJS($script);
	}
	/**************************Service Editor ***********************************************************/
	public function build_service_list(){
		
		if(!isset($this->model['current_application']))
			return "";
		$app=$this->model['current_application'];
		$services=$app->getServices();
		$html="<ul>";
		foreach ($services as $service)
		{
			$html.="<li>".$service->getname()."</li>";
		}
		$html.="</ul>";
		return $html;
	}
	public function service_editor_build()
	{
		$editDlg=new sm_HTML("ServiceEditDlg");
		$editDlg->setTemplateId("TwoButtonsModalRemote_Dlg","ui.tpl.html");
		$editDlg->insert("title", "Edit Service ");
		$editDlg->insert("id", "ServiceEditDlg");
		$editDlg->insert("btn1", "Close");
		$editDlg->insert("btn2", "Save");
		
		$editDlg->insert("body", sm_Form::buildForm("service_edit", $this));
		
		$this->uiView = $editDlg;
	}
	
	function service_data_form(sm_Form $form)
	{
		$this->service_edit_form($form);
		$form->addElement(new Element_Button("Save","submit"));
		$form->addElement(new Element_Button("Delete","",array("name"=>"delete","class"=>"confirm-toggle btn btn-primary","data-toggle"=>'modal', "data-target"=>'#MenuDeleteDlg')));
		
	}
	
	function service_edit_form(sm_Form $form)
	{
		$view = new View_Grid();
		$var = sm_obj2array_r($this->model['service']);
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => $view,
				//"labelToPlaceholder" => 1,
				"action"=>"configurator/editor/add/service"
		));
		$c=0;
		foreach($var as $i=>$v){
			if($i=="cid"|| $i=="aid" || $i=="minfo_id" || $i=="sid")
				//$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v,"readonly"=>true,"shortDesc"=>"<small>Internal Use</small>")));
				continue;
			else
				$form->addElement(new Element_Textbox(ucfirst($i), $i,array("value"=>$v)));
			$c++;
			if($c%2==0)
			{
				$view->map['layout'][]=2;
				$view->map['widths'][]=6;
				$view->map['widths'][]=6;
			}
		}
		if($c%2!=0)
		{
			$view->map['layout'][]=1;
			$view->map['widths'][]=6;
		}
		$form->addElement(new Element_Hidden("id",$this->model['id']));
		$form->addElement(new Element_Hidden("segment","services"));
		$form->addElement(new Element_Hidden("sid","sid:".$var['sid']));
		$form->addElement(new Element_Hidden("aid","aid:".$var['aid']));
		
	
	}
}