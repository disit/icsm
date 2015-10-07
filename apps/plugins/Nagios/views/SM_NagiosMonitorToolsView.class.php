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

class SM_NagiosMonitorToolsView extends sm_ViewElement
{
	
	function __construct($data=NULL)
	{
		parent::__construct($data);
	}
	
	/**
	 * Create the HTML code for the module.
	 */
	public function build() {
	
		if(isset($this->model))
		{
			$url=$this->model["tool_url"];
			$this->uiView = $html = new sm_HTML();
			$html->insert("tool_frame","<iframe src='".$url."' id='monitor_tool_view' ></iframe>");
			$html->addCss("monitortool.css","main",SM_NagiosPlugin::instance()->getFolderUrl("css"));
			//$this->addView();
		}
	}
	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("Monitor Tools",'',"eye-open");
		$menu->setSubLink("Monitor Tools","Nagios Core","monitor/tools/nagios");
		$menu->setSubLink("Monitor Tools","NagiosQL","monitor/tools/nagiosql");
		$menu->setSubLink("Monitor Tools","PNP4Nagios","monitor/tools/pnp4nagios");
		
		
	}

	public function dashboard_system($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=array();
			$this->setTemplateId($tpl['tpl'],$tpl['path']);
			$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl'],$data);
			$html['html']=$this->tpl->getTemplate($tpl['tpl']);
			if(isset($css))
				$html['css']=$css;
		}
		return $html;
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
				$panel->insert(sm_Form::buildForm("NAGIOSPNP_config", $this));
				$panel->insert(sm_Form::buildForm("LIVESTATUS_config", $this));
				$panel->insert(sm_Form::buildForm("NAGIOSClient_config", $this));
				$panel->insert(sm_Form::buildForm("QL_config", $this));
				$panel->insert(sm_Form::buildForm("QLClient_config", $this));
			}
		}
	}
	
	function NAGIOSPNP_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_NagiosPNP';
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
	
	function LIVESTATUS_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_LiveStatusClient';
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
	
	function NAGIOSClient_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_NagiosClient';
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
	
	function QL_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_NagiosQL';
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
	
	function QLClient_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_NagiosQLClient';
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
}