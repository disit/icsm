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

class SM_SCEView extends sm_ViewElement
{
	protected $filter;
	protected $alias;
	protected $fieldOrder;
	
	function __construct($data=null)
	{
		parent::__construct($data);
		$this->filter = array(
				"call_url",
				"metric","sla",
				"host_machine",
				"virtual_machine",
				"metric_unit",
				"relation"
		
		);
		$this->alias = array(
				"business_configuration"=>"Configuration Id",
				"timestamp"=>"Alarm Time",
				"metric_name"=>"Metric",
				"virtual_machine_name"=>"VM",
				"metric_timestamp"=>"Last Check",
		
		);
		$this->fieldOrder=array(
				"metric_name",
				"value",
				"threshold",
				"virtual_machine_name",
				"metric_timestamp"
		);
	}
	
	/**
	 * Create the HTML code for the module.
	 */
	public function build() {
		switch($this->op)
		{
			case "sla::alarms":
				$this->sla_status();
				break;
			case 'response':
				$this->uiView=new sm_JSON();
				$this->uiView->insert($this->model);
				break;
	
		}
	}
	
	public function sla_status(){
		
		$layout=new sm_Grid();
		$col[]=$this->sla_events_list();
		$col[]=$this->sla_periodical_report();
		$layout->addRow($col,array(6,6));
		$this->uiView = $html = new sm_HTML();
		$html->insert("pre","<div id='Alarms_container' class='cView_panel'>");
		$html->insert("pre",$layout);
		$html->insert("pre","</div>");
	}
	
	public function sla_periodical_report(){
		$panel = new sm_Panel("SLA_health");
		$panel->setTitle("Weekly Report");
		$panel->setType("default");
		return $panel;
	}
	
	public function sla_events_list(){
		
		$panel = new sm_Panel("SLA_health");
		$panel->setTitle("Last 10 SLA Alarms");
		$panel->setType("default");
		
		if(count($this->model['alarms'])>0)
		{
			$accordion=new sm_Accordion("sla_health");
			foreach ($this->model['alarms'] as $i=>$alarm)
			{
				$array=XML2Array::createArray($alarm->getdetails());
				$metrics = $array['metrics']['metrics'];
				$p = new sm_PanelAccordion("alarm-".$i);
				$p->setId("alarm-".$i);
				$p->icon('<img src="./img/details.gif" title="Click for details" style="vertical-align: text-bottom;"/>');
				$p->setTitle($alarm->gettime()."<span class='label label-danger pull-right'>".$alarm->getviolations()."</span>");
				$p->setType("default");
				$p->insert($this->makeMetricsTable($metrics,"table-alarm-".$i));
				$accordion->insert("alarm-".$i, $p);
				
			}
			$panel->insert("<h5>Click on date for details</h5>");
			$content=$accordion;
		}
		else 
		{
			$content="<h4 class=well>No Alarms Received</h4>";
			
		}
		
		$panel->insert($content);
		$panel->addCss("SM_SCE.css","panel",SM_SCEPlugin::instance()->getFolderUrl("css"));
		return $panel;
	}
	
	protected function makeMetricsTable($metrics,$id=null)
	{
		
		$keys=array_keys($metrics);
		if(!is_numeric($keys[0]))
			$data=array($metrics);
		else
			$data=$metrics;
		$th=array_fill(0, count($this->fieldOrder), null);
		foreach (array_keys($data[0]) as $key)
		{
			if(in_array($key, $this->filter))
				continue;
				
			if(isset($this->alias[$key]))
				$th[array_search($key,$this->fieldOrder)]=$this->alias[$key];
			else
				$th[array_search($key,$this->fieldOrder)]=$key;
		}
		$rows=array();
		foreach ($data as $k=>$v)
		{
				
			$td=array_fill(0, count($this->fieldOrder), null);
			
			foreach ($v as $key=>$value)
			{
				if(in_array($key, $this->filter))
					continue;
	
	
				$val=$key=="value" || $key=="threshold"?sprintf("%01.2f %s",$value,$v['metric_unit']):$value;
				if($key=="threshold")
				{
					if($v['relation']=="hasMetricValueLessThan")
						$val="&lt; ".$val;
					else
						$val="&gt; ".$val;
				}
				$td[array_search($key,$this->fieldOrder)]=$val;
	
			}
			
			$rows[]=$td;
		}
		$table = new sm_TableDataView($id);
		$table->setSortable();
		$table->makeResponsive();
		$table->addHRow("",array("data-type"=>"table-header"));
		foreach($th as $cell)
			$table->addHeaderCell(ucfirst($cell));
		foreach($rows as $row)
		{
			$table->addRow();
			foreach($row as $cell)
				$table->addCell($cell);
		}
		return $table;
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
				$panel->insert(sm_Form::buildForm("SCE_config", $this));
				return;	
			}
			if(get_class($obj)=="sm_NotificationView")
			{
				$ui=$obj->getUIView();
				$ui->addCSS("SM_SCE.css",$ui->getTemplateId(),SM_SCEPlugin::instance()->getFolderUrl("css"));
				return;
			}
			
		}
	}
	
	function SCE_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_SCE';
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.str_replace("SM_","",$m).'</legend>'));
			foreach($this->data[$m] as $item=>$i)
			{
				if($i['name']=='SCEWRITEINDB')
				{	
					$val=$i['value']?1:0;
					$form->addElement(new Element_YesNo($i['description'],$i['name'],array('value'=>$val,'label'=>$i['description'])));
				}
				else 
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			$form->addElement(new Element_Button("Save","",array("class"=>"button light-gray")));
			$form->addElement(new Element_HTML('</div>'));
		}
	
	
	}
	
	public function dashboard_overall($data=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML("SLA_health");
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_Table("sla_health_table");
			$content->makeResponsive();
			$content->addHRow("",array("data-type"=>"table-header"));
			
			$content->addHeaderCell("Configuration");
			$content->addHeaderCell("Alarm Time");
			$content->addHeaderCell("Violations");			
			//	
			//$content->addHeaderCell("State");
			//$content->addHeaderCell("Level");
			foreach ($data['alarms'] as $alarm)
			{
				$content->addRow();
				$content->addCell("<a href='monitor/configuration/view/".$alarm->getcid()."'>".$alarm->getcid()."</a>");
				$content->addCell($alarm->gettime());
				$content->addCell("<span class='label label-danger'>".$alarm->getviolations()."</span>");
			}
				
			$panel->insert($content);
			$panel->addCss("SM_SCE.css","panel",SM_SCEPlugin::instance()->getFolderUrl("css"));
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
		}
	
		return $html;
	}
	
	public function dashboard_local($data=null){
	
		$html=new sm_HTML("SLA_Container");
		if($data){
			$html->insert("pre","<div class='monitor_dashboard col-xg-6 col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel("SLA_health");
			$panel->setTitle("SLA Alarms");
			$panel->setType("default");
			if(isset($data['alarms']) && count($data['alarms'])>0)
			{
				$content=new sm_Table("sla_health_table");
				$content->makeResponsive();
				$content->addHRow("",array("data-type"=>"table-header"));
					
				//$content->addHeaderCell("Configuration");
				$content->addHeaderCell("Alarm Time");
				$content->addHeaderCell("Violations");
				//
				//$content->addHeaderCell("State");
				//$content->addHeaderCell("Level");
				foreach ($data['alarms'] as $alarm)
				{
					$content->addRow();
					//$content->addCell($alarm->getcid());
					$content->addCell($alarm->gettime());
					$content->addCell("<span class='label label-danger'>".$alarm->getviolations()."</span>");
				}
			}
			else 
				$content="<h4 class=well>No Alarms Received</h4>";
			$panel->insert($content);
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
		}
		return $html;
	}
}