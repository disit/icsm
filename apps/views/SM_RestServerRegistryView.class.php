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

class SM_RestServerRegistryView extends sm_ViewElement
{
	
	function __construct($data=NULL)
	{
		parent::__construct($data);
		
	}

	/**
	 * Create the HTML code for the module.
	 * First the createHTMLLabels() will be called to add all labels to the template,
	 * Then the tpl_id set in $this->getTemplateId() will be added to the main template automatically
	 */
	
	public function build() {
	
	
		switch($this->op)
		{
			case 'list':
				$this->registry_list();
				break;
			case 'response':
				$this->uiView=new sm_JSON();
				$this->uiView->insert($this->model);
				break;
		}
	}
	
	public function registry_list() {
		$this->uiView=new sm_Page("RestServer");
		$this->uiView->setTitle("Journal");
		// add entries to table
		
			
		$table = new sm_TableDataView("RestServerRegister",$this->model);
		$table->setSortable();
		$table->setSeletectedCmd($this->model['commands']);
		$table->addHRow();
		
		foreach($this->model['records'] as $i=>$records)
		{
			$table->addRow("",array("id"=>$records['id']));
			unset($records['id']);
			unset($records['arrival_time']);
			foreach($records as $k=>$r)
			{
				if($i==0)
					$table->addHeaderCell(ucfirst(str_replace("_"," ",$k)));
				$table->addCell($r);
			}
		}
		$filter['method']=array("Select","Method", "method",array(""=>"All","GET"=>"GET","POST"=>"POST","PUT"=>"PUT","DELETE"=>"DELETE"),array('class'=>'input-sm','value'=>$this->model['method']));
		$filter['time']=array("Date","Filter from", "time",array('class'=>'input-sm','value'=>$this->model["time"],'placeholder'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")",'title'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")"));
		$filter['to_time']=array("Date","to", "to_time",array('class'=>'input-sm','value'=>$this->model['to_time'],'placeholder'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")",'title'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")"));
		$table->addFilter($filter);
		
	
		$panel = new sm_Panel();
		$panel->setTitle("Rest Server Registry");
		$panel->icon("<img src='".SM_IcaroApp::getFolderUrl("img")."rest.gif' />");
	
		$panel->insert($table);
		$this->uiView->insert($panel);
		$this->uiView->addJs("restserver.js","main",SM_IcaroApp::instance()->getFolderUrl("js"));
		//$this->addView();
	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="RestServerRegister")
		{
			$form->setSubmitMethod("RestServerRegisterFormSubmit",$this);
		}
		
	}
	
	public function RestServerRegisterFormSubmit($data)
	{
		if(!isset($_SESSION['restserver/registry']))
			$_SESSION['restserver/registry']=array();
				
		if(isset($data['time']))
		{
			$data['time']=strtotime($data['time']);
			if(isset($_SESSION['restserver/registry']['time']) && $_SESSION['restserver/registry']['time']!=$data['time'])
				$_SESSION['restserver/registry']['time']=$data['time'];
			else
				$_SESSION['restserver/registry']['time']=$data['time'];
			
				
		}
		if(isset($data['to_time']))
		{
			$data['to_time']=strtotime($data['to_time']);
			if(isset($_SESSION['restserver/registry']['to_time']) && $_SESSION['restserver/registry']['to_time']!=$data['to_time'])
				$_SESSION['restserver/registry']['to_time']=$data['to_time'];
			else
				$_SESSION['restserver/registry']['to_time']=$data['to_time'];
				
		
		}
		if(isset($data['method']))
		{
			if(isset($_SESSION['restserver/registry']['method']) && $_SESSION['restserver/registry']['method']!=$data['method'])
				$_SESSION['restserver/registry']['method']=$data['method'];
			else
				$_SESSION['restserver/registry']['method']=$data['method'];
		
		}
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
				$panel->insert(sm_Form::buildForm("restserver_config", $this));
			}
		}
	}
	
	function restserver_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
		{
			$m='SM_RestServer';
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
	
	/*public function rest_server_form($form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Inline,
				//"labelToPlaceholder" => 0,
				"action"=>"/restserver/registry",
				"class"=>"entries_limit_selector"
		));
	
		$options=array(10,25,50,100);
		$form->addElement(new Element_HTML('<label>Show '));
		$form->addElement(new Element_Select("", "restserver_registry_limit", $options,array('value'=>$this->model['limit'],'class'=>'input-sm')));
		$form->addElement(new Element_HTML(' entries</label>'));
	
		$form->addElement(new Element_Button("Go","submit",array('name'=>"go","class"=>"button light-gray btn-xs")));
	}*/

	static function menu(sm_MenuManager $menu)
	{
		$menu->setSubLink("Journal","Rest Server","restserver/registry");
	}
	
	public function dashboard($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=array();
				
			$this->setTemplateId($tpl['tpl'],$tpl['path']);
			$this->tpl->addTemplatedataRepeat($tpl['tpl'],$tpl['tpl']."_last",$data['last']);
			$html['html']=$this->tpl->getTemplate($tpl['tpl']);
				
				
			if(isset($css))
				$html['css']=$css;
		}
		return $html;
	}

}
