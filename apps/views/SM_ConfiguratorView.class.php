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

DEFINE("DEFAULT_ICO","./apps/img/configuration.png");
DEFINE("APPLICATIONS_ICO","./apps/img/applications.png");
DEFINE("TENANTS_ICO","./apps/img/tenancy.gif");
DEFINE("HOSTS_ICO","./apps/img/host.png");
DEFINE("HOST_ICO","./apps/img/host_.png");
DEFINE("VMHOST_ICO","./apps/img/vmhost.png");
DEFINE("DEVICES_ICO","./apps/img/drive.png");
DEFINE("FIREWALL_ICO","./apps/img/firewall.png");
DEFINE("ROUTER_ICO","./apps/img/router.gif");
DEFINE("EXTERNALSTORAGE_ICO","./apps/img/externalstorage.png");
DEFINE("METRICS_ICO","./apps/img/metrics.png");
DEFINE("SERVICES_ICO","./apps/img/service.png");
DEFINE("SLA_ICO","./apps/img/agreement.png");


class SM_ConfiguratorView extends sm_ViewElement
{
	protected $filters;
	function __construct($data=NULL)
	{
		parent::__construct($data);
		$this->filters["hosts"]=array("name","os","type","ip_address","actions");
		$this->filters["applications"]=array("id","name","description","contacts","type","actions");
		$this->filters["devices"]=array("id","name","device_type","type","ip_address","actions");
		$this->filters["tenants"]=array("id","name","runOn","contacts","actions");		
		$this->uiView=new sm_Page("SM_ConfiguratorView");
	}
	
	/*public function pre_build(){
		
	}*/

	/**
	 * Create the HTML code for the module.
	 */
	public function build() {

			switch ($this->op)
			{
				case 'list':
					$this->configuration_list_build();
					break;
				case 'view':
					$this->configuration_page_build();
				break;
				case 'segment::view':
					$this->segment_view_build();
					break;
				case 'segment::list':
					$this->segment_list_build();
					break;
				case 'segment::info':
					$this->segment_info_build();
					break;
				case 'segment::metrics':
					$this->segment_metrics_build();
					break;
				case 'queue_list':
					$this->configuration_queue_build();
				break;
				case 'history_view':
					$this->configuration_history_build();
				break; 
				case 'history_dlg':
					$this->configuration_history_dlg_build();
				break;
				case 'xml':
					$this->configuration_xml();
				break;
				case 'response':
					$this->uiView=new sm_JSON();
					$this->uiView->insert($this->model);
				break;
			}
		//}
	}
	
	
	
	
	function configuration_xml()
	{
		$xml=Array2XML::createXML("configuration",$this->model['configuration']);
		$xslDoc = new DOMDocument();
		$xslDoc->load(SM_IcaroApp::getFolder("schema")."configurationTo_xml_flat.xsl");
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xslDoc);
		$xml_str=$proc->transformToXml($xml);
		$this->uiView = new sm_XML();
		$this->uiView->insert($xml_str);
	}
	
	function configuration_queue_build()
	{
		//$table="No data available";
		//if(count($this->model['records'])>0)
		//{
			$table = new sm_TableDataView("configuration_queue",$this->model);
			$table->setSortable();
			$header=0;
			$table->addHRow();
			foreach ($this->model['records'] as $k=>$value)
			{
				$table->addRow();
				foreach ($value as $l=>$v)
				{
					if($k==0)
					{
						$header++;
						if($l=='actions')
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
						else 
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
					}
					$table->addCell($v);
				}		
			}
		//}

		$panel = new sm_Panel();
		$panel->setTitle("Queue");
		$panel->icon("<i class='configurator_queue_icon'></i>");
		$panel->insert($table);
		$this->uiView = new sm_Page();
		$this->uiView->setTitle("Configurator");
		$this->uiView->insert($panel);
		
		$this->uiView->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		//$this->addView();	
	}
	
	function configuration_list_build(){

		//$table="No data available";
		//if(count($this->model['records'])>0)
			$confirmDlg=false;
			$table = new sm_TableDataView("configurations_table",$this->model);
			$header=0;
			$table->addHRow();
			foreach ($this->model['records'] as $k=>$value)
			{

				//$table->addRow();
				$table->addRow("",array("id"=>$value['cid']));
				unset($value['cid']);
				foreach ($value as $l=>$v)
				{
					if($k==0)
					{
						$header++;
						if($l=='actions')
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
						else 
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
					}
					
					if($l=='actions' && is_array($v))
					{
						foreach($v as $i=>$action)
						{	
							if(isset($action['class']) && preg_match('/confirm/', $action['class']))
							{
								$v[$i]['target']="#confirmConfigurationCommand";
								$confirmDlg=true;
							}
						}
						//$v=implode("",$v);
						$this->setTemplateId("actions_forms","ui.tpl.html");
						$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
						$v=$this->tpl->getTemplate("actions_forms");
					}
					
					
					$table->addCell($v);
				}
			}		
			$table->setSortable();
			if(isset($this->model['commands']))
				$table->setSeletectedCmd($this->model['commands']);
			$options=array("All"=>"All","System"=>"System Configuration","Business"=>"Business Configuration");
			$filterElement['configuration_search']=array("Search","", "configuration_search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
			$filterElement['configurationType']=array("Select","Filter for", "configurationType", $options,array('value'=>$this->model['type_selector'],'class'=>'input-sm'));
			
			$table->addFilter($filterElement);
		
		
		$panel = new sm_Panel();
		$panel->setTitle("Inventory");
		$panel->icon("<i class='configurator_status_icon'></i>");
		//$panel->insert(sm_Form::buildForm('configurator_entries',$this));
		$panel->insert($table);
		
		$page = new sm_Page();
		$page->setTitle("Configurations");
		$page->insert($panel);
		
		if($confirmDlg)
		{
			$dlg = new sm_Dialog("confirmConfigurationCommand",CONFIRMATION_DLG);
			$dlg->setConfirmationFormClass("confirm");
			$panel->insert($dlg);
		}
		
		if(isset($this->model['commands']))
			$page->addJs("configuration.js","main",SM_IcaroApp::getFolderUrl("js"));
		if(isset($this->model['commands']))
		$page->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView=$page;
		//$this->addView();
		
	}
	
	function configuration_history_dlg_build(){
		$this->uiView = new sm_HTML();
		$this->uiView->setTemplateId("configuration_data_remote_Dlg",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$this->uiView->insertArray(
				array(
						'title'=>"History Data",
						'body' => "<textarea>".$this->model['data']."</textarea>",
				)
		);
		//$this->view->setMainView($this->uiView);
	}
	
	function configuration_history_build(){
		
		
		//$table="No data available";
		//if(count($this->model['records'])>0)
		{
			$table = new sm_TableDataView("configuration_history",$this->model);
			$table->setAjax(array("container"=>"#configuration_data"));
			$table->makeResponsive();
			$table->addHRow("",array("data-type"=>"table-header"));
			foreach ($this->model['records'] as $k=>$value)
			{
		
				$table->addRow();
				foreach ($value as $l=>$v)
				{
					if($k==0)
					{
						if($l=='actions')
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
						else
							$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
					}
						
					if($l=='actions' && is_array($v))
					{
						$v=implode("",$v);
						
					}
						
					$table->addCell($v);
				}
			}
			$table->setSortable();
		}
	
		
		if(isset($this->model['icon']))
			$panel_icon="<img src='".$this->model['icon']."' />";
	
		$this->uiView = $panel = new sm_Panel();
		$panel->setTitle('History');
		$panel->icon($panel_icon);
		$panel->insert($table);
		$dlg = new sm_HTML();
		$dlg->setTemplateId("configuration_data_dlg",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$dlg->insert("id","DataModal");
		$dlg->insert('title',"History Data");
		$dlg->insert('body',"<div style='text-align:center'><img src='img/wait.gif' /> Loading...</div>");
		$panel->insert($dlg);
		//$panel->insert('<div id="DataModal" class="modal fade"><div class=modal-body></div>');
	
		//$this->view->setMainView($panel);
	}

	
	
	function configuration_page_build(){

		unset($this->model['configuration']['@attributes']);
		$script='$(document).ready(function(){
			$("#configuration_tree").jstree({
				"core" : { "initially_open" : [ '.$this->model['id'].',"applications","devices","tenants","hosts","SLA" ] },
				"xml_data" : {
				"ajax" : {
				"url" : "'.$this->model['xmlUrl'].'",
				"cache":false
				},
			"xsl" : "flat",
				},
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
							$( "#configuration_data" ).hide();
							$("#configuration_container-rotor").show();
							$.get(url, function( data ) {
								$("#configuration_container-rotor").hide();
								$( "#configuration_data" ).html( data );
								$( "#configuration_data" ).show();
							});	
						}
			});
		});';
		/* CSS */
		$this->uiView->addCss("jstreestyle.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addCss("imgareaselect-default.css","main","css/imgareaselect/");
		$this->uiView->addCss("jquery-ui-1.10.2.custom.css","main","css/smoothness/");
		$this->uiView->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addCss("logos.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView->addCss("monitor.css","main",SM_IcaroApp::getFolderUrl("css"));
		/* Javascript */
		$this->uiView->addJS("jquery.jstree.js","main","js/");
		$this->uiView->addJS("configurator.js","main",SM_IcaroApp::getFolderUrl("js"));
		$this->uiView->addJs("jquery-ui-1.10.2.custom.min.js","main","js/");
		$this->uiView->addJs("jquery.imgareaselect.js","main","js/imgareaselect/");
		
		$this->uiView->addJS($script);
		
	
		$baseUrl="";
		$url="";
		unset($this->model['configuration']['@attributes']);
		$configuration_data = new sm_HTML();
		$configuration_data->setTemplateId("configuration_data",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		
		$configuration_data->insertArray(array_merge(
						array(
								'label_bid'=>"Contract",
								'label_identifier'=>"Id",
								'label_name'=>"Name",
								'label_description'=>"Description",
								'label_contacts'=>'Contacts',
								'label_type'=>'Type',
								//'label_actions'=>'Actions',
	
						),$this->model['configuration']));
		
		$panel = new sm_Panel("ConfigurationDescription");
		$panel->setTitle("Description Data");
		$panel->insert($configuration_data);
		$panel->setClass("metadataBox");
	
		$menu = new sm_NavBar("cView_menu");
	//	$url="";
		if(isset($this->model['menu']))
		{
			reset($this->model['menu']);
		//	$m=current($this->model['menu']);
	//		$url=$m['url'];
			foreach($this->model['menu'] as $k=>$v)
				$menu->insert($k,$v);
		}
		
		
		$confarea = new sm_HTML("configuration_page");
		$confarea->setTemplateId("configuration_page",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$confarea->insert('description_data',$panel);
		$confarea->insert('name',$this->model['configuration']['name']);
		
		$this->uiView->setTitle($this->model['configuration']['name']."<br><small>".$this->model['configuration']['bid']."</small>");
		$this->uiView->insert($confarea);
		$this->uiView->menu($menu);
		
		
		
		//$this->addView();
	}
	
	function _segment_entry_build($_segment, $title)
	{
		$html="";
		unset($_segment['@attributes']);
	    $data=array();
	    $entry=array();
	    if($title=="Data")
	    	$entry['id']="Info_container";
	    if($title=="service")
	    	$entry['class']="service_data";
	    else 
	    	$entry['class']="cView_panel";
		foreach ($_segment as $k=>$value)
		{
			if(is_array($value))
			{
				if(isset($value['metrics']['metric']))
				{
					$icon=constant(strtoupper("metrics")."_ICO");
					if(empty($icon))
						$icon=DEFAULT_ICO;
					$table=$this->_segment_table_build($value['metrics']['metric']);
					$this->setTemplateId("panel_custom","ui.tpl.html");
					$panel_data=array(
							'panel_type'=>'default',
							'panel_title' => "metrics", 
							'panel_content' =>$table->render()
							);
					if($icon)
						$panel_data['panel_icon']="<img src='$icon' />";
					$this->tpl->addTemplatedata(
							'panel_custom',
							$panel_data
					);
					$entry['childs']=$this->tpl->getTemplate("panel_custom");
					
				}
				else if(isset($value['service']))
				{
					$html="";
					$tabs=array();
					foreach($value['service'] as $i=>$m)
					{
						$tabs[]=array(
								"tab_id"=>"service".$i,
								"tab_title"=>$m['id'],
								"tab_data"=>$this->_segment_entry_build($m,"service"),
								"tab_active"=>$i==0?"active":""
						);
					}
					//var_dump($tabs); exit();
					//$html=array_merge($html,$value);
					$this->setTemplateId("tabs","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat($this->getTemplateId(), 'li', $tabs);
					$this->tpl->addTemplateDataRepeat($this->getTemplateId(), 'div', $tabs);
					$entry['childs']="<h4 class=page-header>Services:</h4>".$this->tpl->getTemplate("tabs");
					
					
					
				}
			} 	
			else
			{
					//$value=$this->_segment_entry_build($k,$value);
				if($k=="os")
				{
					if(!empty($value) && $value!="None")
						$v="<i class='sm-icon-24 icon-".$value."' title='".$value."'></i><span class=os-type>".$value."</span>";
					else
						$v=empty($value)?"N.A":$value;
					$data[]=array('label_key'=>ucfirst(str_replace("_"," ",$k)),'title'=>empty($value)?"N.A":$value,'value'=>$v);
				}
				else 
					$data[]=array('label_key'=>ucfirst(str_replace("_"," ",$k)),'title'=>empty($value)?"N.A":$value,'value'=>empty($value)?"N.A":$value);	
			}		
		}
		if(count($data)>0)
		{
			$entry['title']=$title;
			//$this->setTemplateId("panel","ui.tpl.html");
			$this->setTemplateId("segment_entry",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
			$this->tpl->addTemplateDataRepeat("segment_entry", 'entry',$data );
			$this->tpl->addTemplateData("segment_entry", $entry);
			
			return $this->tpl->getTemplate("segment_entry");
			
		}
		return $html;
	}
	
	function segment_table_build($_segment)
	{
		$table = new sm_Table();
		$header=0;
		$table->makeResponsive();
		$table->addHRow("",array("data-type"=>"table-header"));
		foreach ($_segment as $k=>$value)
		{
			unset($value['@attributes']);
			$table->addRow();
			foreach ($value as $l=>$v)
			{
				if($k==0)
				{
					$header++;
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
					
				}
				if($l=="os")
				{
					$v="<i class='sm-icon icon-".$v."' title='".$v."'></i><span class=os-type>".$v."</span>";
				}
				$table->addCell($v);
			}
		}
		$table->addFooterCell("","",array("colspan"=>$header));
		return $table; //->render();
	}
	
	function segment_view_build()
	{
		//$view=$this->segment_data();
		
		$type =$this->model['type'];
		$title=$this->model['segments'][$type][0]['name'];
		
		
		if(isset($this->model['segments'][$type][0]['type']) && defined(strtoupper($this->model['segments'][$type][0]['type']."_ICO")))
		{
			$icon=@constant(strtoupper($this->model['segments'][$type][0]['type']."_ICO"));
			
		}
		else if(defined(strtoupper($type."_ICO")))
		{
			$icon=@constant(strtoupper($type."_ICO"));	
		}
		if(empty($icon))
			$icon=DEFAULT_ICO;
		
		$this->uiView = $panel = new sm_Panel("segment_view");
		$panel->setTitle($title);
		$panel->icon("<img src='$icon' />");
		//if(isset($this->model['sid']))
		{			
			$menu = new sm_NavBar("cView_navbar");
			$menu->setTemplateId("configurator_menu_group",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
			foreach($this->model['menu'] as $k=>$v)
				$menu->insert($k, $v);	
			$first = reset($this->model['menu']);
			$menu->setActive($first['id']);
			
			$panel->insert($menu);
			$panel->insert("<div id='cView_container'></div>");
			$script='cView_init();';
			$panel->addJs($script,"panel");
			$script='cView_load("'.$first['url'].'");';
			$panel->addJs($script,"panel");
		}
		//else 
		//	$panel->insert($info['html']);
		
		//$this->view->setMainView($panel);
	}
	
	function segment_list_build()
	{		
		$html="No data available";
		if(isset($this->model['segment_type']))
			$icon=constant(strtoupper($this->model['segment_type'])."_ICO");
		if(empty($icon))
			$icon=DEFAULT_ICO;
		$data=array();
		$title=$this->model['segment_type'];
		$type =$this->model['type'];
		$v=$this->model['segments'][$type];
		foreach ($v as $j=>$s)
			foreach($this->filters[$title] as $i=>$f)
				if(isset($s[$f]))
					$data[$j][$f]=$s[$f];
		$html=$this->segment_table_build($data);
		
		$this->uiView = $panel = new sm_Panel("segment_list");
		$panel->icon("<img src='".$icon."' />");
		$panel->setTitle(ucfirst($title));
		$panel->insert($html);
	
	}
	
	function segment_info_build()
	{
		$type =$this->model['type'];
		$data= $this->segment_data($this->model['segments'][$type][0]);
		$this->uiView=$html=new sm_HTML();
		$html->setTemplateId("segment_info",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$html->insert("id","Info_container");
		$html->insert("class","cView_panel");
		$html->insert("type",ucfirst($type));
		$icon_type=isset($this->model['segments'][$type][0]['type'])?$this->model['segments'][$type][0]['type']:"null";
		$html->insert("picture", "<div class='segment-icon-container well'><div class='segment-icon segment-icon-".$icon_type."'></div></div>");
		$html->addTemplateDataRepeat("segment_info","entry",$data);
		/*$info=$this->segment_info();
		$this->uiView=new sm_HTML();
		$this->uiView->insert("info",$info['html']);*/
	}
	
	function segment_metrics_build()
	{
		$this->uiView=$html=new sm_HTML();
		$html->setTemplateId("metrics_panel",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$data= $this->segment_table_build($this->model['metrics']);
		
		$html->insert("metrics",$data);
		/*$info=$this->segment_info();
			$this->uiView=new sm_HTML();
		$this->uiView->insert("info",$info['html']);*/
	}
	
	protected function segment_data($_segment){
		$data=array();
		foreach ($_segment as $k=>$value)
		{
			if(is_array($value))
				continue;
			if($k=="os")
			{
				if(!empty($value) && $value!="None")
					$v="<i class='sm-icon-24 icon-".$value."' title='".$value."'></i><span class=os-type>".$value."</span>";
				else
					$v=empty($value)?"N.A":$value;
				$data[]=array('label_key'=>ucfirst(str_replace("_"," ",$k)),'title'=>empty($value)?"N.A":$value,'value'=>$v);
			}
			else
				$data[]=array('label_key'=>ucfirst(str_replace("_"," ",$k)),'title'=>empty($value)?"N.A":$value,'value'=>empty($value)?"N.A":$value);
		}
		return $data;
	}
	
	
	protected function segment_info()
	{
		$icon=null;
		$this->setTemplateId("segment_entry",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		if(isset($this->model['segment_type']))
			$icon=constant(strtoupper($this->model['segment_type'])."_ICO");
		if(empty($icon))
			$icon=DEFAULT_ICO;
		$html=array();
		$this->tpl->addTemplateData("segment_entry", array('id'=>$this->model['segment_type']));
		
		foreach ($this->model['segments'] as $i=>$v)
		{
				
			if(isset($this->model['id']) && count($v)==1)
			{
				if($v[0]['id']==$this->model['id'])
				{
					$ico=null;
					if($i=="device" && isset($v[0]['device_type']))
					{
						if(defined(strtoupper($v[0]['type'])."_ICO"))
							$ico=constant(strtoupper($v[0]['device_type'])."_ICO");
							
					}
					else if(isset($v[0]['type']) && defined(strtoupper($v[0]['type'])."_ICO"))
						$ico=constant(strtoupper($v[0]['type'])."_ICO");
					if(!empty($ico))
						$icon=$ico;
		
					$html=$this->_segment_entry_build($v[0],"Data");
					$title=isset($v[0]['name'])?$v[0]['name']:$v[0]['id'];
					$title=$v[0]['type']." - ".$title;
					break;
				}
			}
		}
		$data['html']=$html;
		$data['title']=ucfirst($title);
		$data['icon']=$icon;
		return $data;
	}

	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("Configurations",'#',"cloud");
		$menu->setSubLink("Configurations","Inventory","configurator/configuration");
		$menu->setSubLink("Journal","Queue Status","configurator/queue");
	}
	
	public function dashboard_system($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=array();
			
			$this->setTemplateId($tpl['tpl'],$tpl['path']);
			$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl']."_queue",$data['queue']);
			$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl']."_last",$data['last']);
			$html['html']=$this->tpl->getTemplate($tpl['tpl']);
			
			
			if(isset($css))
				$html['css']=$css;
		}
		return $html;
	}
	
	public function dashboard_overall($data=null,$tpl=null,$css=null){
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
			$html->addCSS("configurator.css","panel",SM_IcaroApp::getFolderUrl("css"));
		/*	$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl']."_queue",$data['queue']);
			$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl']."_last",$data['last']);
			$html['html']=$this->tpl->getTemplate($tpl['tpl']);
				
				
			if(isset($css))
				$html['css']=$css;*/
		}
		return $html;
	}
	
	/**** FORMS Events and methods**********/
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="configurations_table")
		{
			$form->setSubmitMethod("configurationsListFormSubmit");
		}
	}
	/**
	 * 
	 * @param array $data
	 */
	
	public function configurationsListFormSubmit($data)
	{
		$value=array();
		if(isset($data['configurationType']))
		{
			$value['type']=$data['configurationType'];
				
		}
		if(isset($data['configuration_search']))
		{
			$value['keywords']=$data['configuration_search'];		
		}
		$_SESSION['configurator/configuration']=$value;
	}
	
	public function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj))
		{
			if(get_class($obj)=="SM_SettingsView")
			{
				$panel=$obj->getUIView()->getUIElement("AppicationSettingsPanel");
				$this->data=$obj->getData();
				$panel->insert(sm_Form::buildForm("configurator_config", $this));
			}
		}
	}
	
	function configurator_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_Configurator';
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
	
	public function setFilters($type,$filter=array())
	{
			$this->filters[$type]=$filter;			
	}
	
	public function getFilters($type)
	{
		$this->filters[$type]=$filter;
	}
	
}
