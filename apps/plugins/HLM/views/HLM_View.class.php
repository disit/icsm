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

class HLM_View extends sm_ViewElement
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
			case "install":
				$this->HLM_Install();
				break;
			case "list":
				$this->HLM_metric_list();
				break;
			case 'response':
				$this->uiView=new sm_JSON();
				$this->uiView->insert($this->model);
				break;
				
		}
	}
	
	
	public function HLM_Install()
	{
		$panel = new sm_Panel();
		$panel->setTitle("HLM Database Configuration");
		$panel->icon("<i class='HLM_metric_icon'></i>");
		$panel->insert(sm_Form::buildForm('HLM_install',$this));
		
		
		$page = new sm_Page();
		$page->setTitle("HLM Database Installer");
		$page->insert($panel);
		
		//$page->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView=$page;
		//$this->addView();
	}
	
	public function HLM_metric_list(){
		
		
			$table = new sm_TableDataView("HLM_metric_table",$this->model);
			$header=0;
			$table->addHRow("",array("data-type"=>"table-header"));
			$table->makeResponsive();
			//if(sm_ACL::checkPermission("HLM::Edit"))
			{
				$commands=array();
				$commands['DeleteTBWSelectedHLM']=array('name'=>'DeleteTBWSelectedHLM','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
			}
			$table->setSeletectedCmd($commands);
			foreach ($this->model['records'] as $k=>$value)
			{
				//if(sm_ACL::checkPermission("HLM::Edit"))
					$value['actions'][]=array("id"=>"HLM-delete-".$value['id'],"title"=>"Delete Metric","url"=>'HLM/remove/'.$value['id'],"data"=>"<i class=notify-delete-icon></i>","method"=>"POST");
				$table->addRow("",array("id"=>$value['id']));
				unset($value['id']);
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
						$this->setTemplateId("actions_forms","ui.tpl.html");
						$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
						$v=$this->tpl->getTemplate("actions_forms");
					}
						
					$table->addCell($v);
				}
			}
			$table->setSortable();
			//$options=array("All"=>"All","System"=>"System Configuration","Business"=>"Business Configuration");
			//$filterElement['configurationType']=array("Select","Filter for", "configurationType", $options,array('value'=>$this->model['type_selector'],'class'=>'input-sm'));
			//$table->addFilter($filterElement);
		$from = $this->model['timestamp']?date("Y-m-d\TH:i:s",$this->model['timestamp']):"";
		$filter['search']=array("Search","", "search", array('placeholder'=>"Search",'value'=>$this->model['keywords'],'class'=>'input-sm form-control'));
		$filter['timestamp']=array("DateTimeLocal","Filter time from", "timestamp",array('class'=>'input-sm','value'=>$from,'pattern'=>"d{2}-d{2}-d{4} d{2}:d{2}",'placeholder'=>"DD-MM-YYYY H:i (e.g. " . date("d-m-Y H:i") . ")",'title'=>"DD-MM-YYYY H:i (e.g. " . date("d-m-Y H:i") . ")"));
		$filter['timestampTo']=array("DateTimeLocal","to", "timestampTo",array('class'=>'input-sm','value'=>date("Y-m-d\TH:i:s",$this->model['timestampTo']),'pattern'=>"d{2}-d{2}-d{4} d{2}:d{2}",'placeholder'=>"DD-MM-YYYY H:i (e.g. " . date("d-m-Y H:i") . ")",'title'=>"DD-MM-YYYY H:i (e.g. " . date("d-m-Y H:i") . ")"));
		
		$table->addFilter($filter);
		$panel = new sm_Panel('HLM_metrics');
		$panel->setTitle("HLM Metrics List");
		$panel->icon("<i class='HLM_metric_icon'></i>");
		//$panel->insert(sm_Form::buildForm('configurator_entries',$this));
		$panel->insert($table);
		
		$page = new sm_Page('HLM');
		$page->setTitle("High Level Metrics");
		$page->insert($panel);
		$page->addJs("HLM.js","main",SM_HLMPlugin::instance()->getFolderUrl("js"));
		$page->addCss("HLM.css","main",SM_HLMPlugin::instance()->getFolderUrl("css"));
		$this->uiView=$page;
		//$this->addView();
	}
		
	public function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj))
		{
			if(get_class($obj)=="SM_SettingsView")
			{
				$obj->tpl->addCss("HLM.css","main",SM_HLMPlugin::instance()->getFolderUrl("css"));
				$panel=$obj->getUIView()->getUIElement("AppicationSettingsPanel");
				$this->data=$obj->getData();
				$panel->insert(sm_Form::buildForm("HLM_config", $this));
			}
		}
	}
	
	
	
	
	
	/**** FORMS ***********************/
	
	function HLM_install_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"HLM/install"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='HLM';
			
			foreach($this->model[$m] as $item=>$i)
			{
				if($i['name']=="HLMDBCREATED")
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('disabled'=>'disabled','value'=>$i['value']?"Ready":"Missing",'label'=>$i['description'])));
				else
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			if(!sm_Config::get('HLMDBCREATED',false))
				$form->addElement(new Element_Button("Install DB","",array("class"=>"button light-gray")));
			
		}
	
	
	}
	
	
	
	function HLM_config_form($form){
		if(!sm_Config::get('HLMDBCREATED',false))
			$action="HLM/install";
		else 
			$action = "SM/settings";
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>$action
		));

			$options["0"]="No";
			$options["1"]="Yes";
			$m='HLM';
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.str_replace("SM_","",$m).'</legend>'));
			foreach($this->data[$m] as $item=>$i)
			{
				if($i['name']=="HLMDBCREATED")
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('disabled'=>'disabled','value'=>$i['value']?"Ready":"Missing",'label'=>$i['description'])));
				else if($i['name']=="HLMDWRITEINTOKB")
					$form->addElement(new Element_YesNo($i['description'],$i['name'],array('value'=>$i['value'])));
				else
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			if(!sm_Config::get('HLMDBCREATED',false))
				$form->addElement(new Element_Button("Install DB","",array("class"=>"button light-gray")));
			else 
				$form->addElement(new Element_Button("Save","",array("class"=>"button light-gray")));
			$form->addElement(new Element_HTML('</div>'));

	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="HLM_metric_table")
		{
			$form->setSubmitMethod("HLMTableFormSubmit");
		}
	}
	
	public function HLMTableFormSubmit($data)
	{
		$_SESSION['HLM/page']=array();
		
		if(isset($data['timestamp']))
		{
			$data['timestamp']=strtotime($data['timestamp']);
			$_SESSION['HLM/page']['timestamp']=$data['timestamp'];
		}
		if(isset($data['timestampTo']))
		{
			$data['timestampTo']=strtotime($data['timestampTo']);
			$_SESSION['HLM/page']['timestampTo']=$data['timestampTo'];
		
		}

		if(isset($data['search']))
		{
			$_SESSION['HLM/page']['search']=$data['search'];		
		}
	}
	
	
	
	/*** Dashboard **/
	public function dashboard($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=array();
			
		/*	$this->setTemplateId("KB_status",$tpl['path']);
			if(is_array($data['internal_status']))
			{
				$this->tpl->addTemplatedataRepeat("KB_status","KB_status_data",$data['internal_status']);
			}
			else
				$this->tpl->addTemplatedata("KB_status",array("no_data"=>$data['internal_status']));
				
			$data['internal_status']=$this->tpl->getTemplate("KB_status");
			
			$this->setTemplateId($tpl['tpl'],$tpl['path']);
			$this->tpl->addTemplatedata($tpl['tpl'],$data);
			$html['html']=$this->tpl->getTemplate($tpl['tpl']);
			if(isset($css))
				$html['css']=$css;*/
		}
		return $html;
	}
	
	public function dashboard_overall($data=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML("hlm_health");
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_Table("hlm_health_table");
			$content->makeResponsive();
			$content->addHRow("",array("data-type"=>"table-header"));
			$content->addHeaderCell("Host");
			$content->addHeaderCell("Metric");
			$content->addHeaderCell("Value");
			$content->addHeaderCell("Time");
			//	$content->addHeaderCell("Next Check");
			//$content->addHeaderCell("State");
			//$content->addHeaderCell("Level");
			foreach ($data['metrics'] as $check)
			{
				$content->addRow();
				$content->addCell(str_replace("@","<br><small>",$check['hostname'])."</small>");
				$content->addCell($check['metric']);
				$content->addCell(number_format($check['value'],2,",",".")." ".$check['unit']);
				$content->addCell($check['time']);
				//$content->addCell($check['next check']);
			//	$content->addCell($check['state']);
			//	$content->addCell($check['level']);
			}
			
			$panel->insert($content);
			$panel->addCss("HLM.css","panel",SM_HLMPlugin::instance()->getFolderUrl("css"));
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
		}
		
		return $html;
	}
	
	public function dashboard_local($data=null){ 
	
		$html=null;
		if($data){
			$html_main=new sm_HTML("HLM_Container");
			
			if(isset($data['metrics']['hosts']))
			{
				$html=new sm_HTML();
				if(isset($data['metrics']['services']))
					$html->insert("pre","<div class='monitor_dashboard dashboard-element col-lg-6 col-md-12 col-sm-12'>");
				else 
					$html->insert("pre","<div class='monitor_dashboard dashboard-element col-lg-12 col-md-12 col-sm-12'>");
				
				/*$panel = new sm_Panel($data['id']."hosts");
				$panel->setTitle("Hosts");
				$panel->setType("default");*/
				
				
				$content=new sm_Table("hlm_hosts_table");
				$content->makeResponsive();
				$content->addHRow("",array("data-type"=>"table-header"));
				$content->addHeaderCell("Host");
				$content->addHeaderCell("Metric");
				$content->addHeaderCell("Value");
				$content->addHeaderCell("State");
				$content->addHeaderCell("Time");
				
				foreach ($data['metrics']['hosts'] as $check)
				{
					if(!isset($check['metric']))
						continue;
					$content->addRow();
					$content->addCell(str_replace("@","<br><small>",$check['host'])."</small>");
					$content->addCell($check['metric']);
					$content->addCell(number_format(floatval($check['value']),2,",",".")." ".$check['unit']);
					
					if($check['state']==0)
					{
						$check['state']='<span class="label label-success">OK</span>';
					}
					elseif($check['state']==1)
					{					
						$check['state']='<span class="label label-warning">WARN</span>';
					}
					elseif($check['state']==2)
					{
						
						$check['state']='<span class="label label-danger">CRIT</span>';
					}
					elseif($check['state']==3)
					{
						$check['state']='<span class="label label-danger">UNKW</span>';
					}
					$content->addCell($check['state']);
					$content->addCell(date("d/m/y H:i:s",$check['last_check']));
				
				}
				$panel=new sm_HTML($data['id']."hosts");
				$panel->setTemplateId("HLM_summary_element_dashboard",SM_HLMPlugin::instance()->getFolder("templates")."HLM.tpl.html");
				$panel->insert("title","Hosts");
				$panel->insert('content',$content);
				$panel->addCss("HLM.css","HLM_summary_element_dashboard",SM_HLMPlugin::instance()->getFolderUrl("css"));
				$html->insert("panel",$panel);
				$html->insert("end","</div>");
				$html_main->insert("hosts",$html);
				
			}
			if(isset($data['metrics']['services']))
			{
				$html=new sm_HTML();
				if(isset($data['metrics']['hosts']))
					$html->insert("pre","<div class='monitor_dashboard dashboard-element col-lg-6 col-md-12 col-sm-12'>");
				else 
					$html->insert("pre","<div class='monitor_dashboard dashboard-element col-lg-12 col-md-12 col-sm-12'>");
				
				/*$panel = new sm_Panel($data['id']."services");
				$panel->setTitle("Services");
				$panel->setType("default");*/
				
				$content=new sm_Table("hlm_services_table");
				$content->makeResponsive();
				$content->addHRow();
				$content->addHeaderCell("Host");
				$content->addHeaderCell("Metric");
				$content->addHeaderCell("Value");
				$content->addHeaderCell("State");
				$content->addHeaderCell("Time");
				//	$content->addHeaderCell("Next Check");
				//$content->addHeaderCell("State");
				//$content->addHeaderCell("Level");
				foreach ($data['metrics']['services'] as $check)
				{
					$content->addRow();
					$content->addCell(str_replace("@","<br><small>",$check['host'])."</small>");
					$content->addCell($check['metric']);
					$content->addCell(number_format(floatval($check['value']),2,",",".")." ".$check['unit']);
					if($check['state']==0)
					{
						$check['state']='<span class="label label-success">OK</span>';
					}
					elseif($check['state']==1)
					{
						$check['state']='<span class="label label-warning">WARN</span>';
					}
					elseif($check['state']==2)
					{
					
						$check['state']='<span class="label label-danger">CRIT</span>';
					}
					elseif($check['state']==3)
					{
						$check['state']='<span class="label label-danger">UNKW</span>';
					}
					$content->addCell($check['state']);
					$content->addCell(date("d/m/y H:i:s",$check['last_check']));
					//$content->addCell($check['next check']);
					//	$content->addCell($check['state']);
					//	$content->addCell($check['level']);
				}
				$panel=new sm_HTML($data['id']."services");
				$panel->setTemplateId("HLM_summary_element_dashboard",SM_HLMPlugin::instance()->getFolder("templates")."HLM.tpl.html");
				$panel->insert("title","Services");
				$panel->insert('content',$content);
				$html->insert("panel",$panel);
				$html->insert("end","</div>");
				$html_main->insert("services",$html);
			}
		}
		if(!isset($data['metrics']))
			$html_main->insert("message","<div class='well well-lg'><h4 style='color:black;'>No High Level Metrics Received</h4></div>");
	//	$html->addCSS("HLM.css")
		return $html_main;
	}
	
	static function menu(sm_MenuManager $menu)
	{
		//$menu->setSubLink("Configurator","HLM","HLM/page");
	}
}