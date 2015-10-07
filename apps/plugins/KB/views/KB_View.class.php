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

class KB_View extends sm_ViewElement
{


	function __construct($data=null)
	{

		parent::__construct($data);

		if(isset($data["tpl"]))
			$this->setTemplateId($data["tpl"],SM_KBPlugin::instance()->getFolderUrl("templates")."KB.tpl.html");
	}
	
	/**
	 * Create the HTML code for the module.
	 */
	
	public function build() {
		switch($this->op)
		{
			case "install":
				$this->KBInstall();
				break;
			case "edit":
				$this->KBEdit();
				break;
			case "view::tax":
				$this->KBViewXML();
				break;
	
		}
	}
	
	
	public function KBViewXML()
	{
		$data = $this->model;
		$title = $data['sparql']['head']['variable']['@attributes']['name'];
		$results=$data['sparql']['results']['result'];
		$html = new sm_HTML();
		$html->insert("title", "<div class=well><legend>".$title."</legend>");
		$html->insert("ul-start","<ul class=list-group>");
		
		foreach($results as $i=>$result)
		{
			$uri = explode("#",$result['binding']['uri']);
			$items[]=array("name"=>$uri[1],"uri"=>$result['binding']['uri']);
			$html->insert("li-".$i,"<li class=list-group-item><div><b>".$uri[1]."</b></div><div><small>".$result['binding']['uri']."</small></li>");
			
		}
		$html->insert("ul-end","</ul></div>");
		$this->uiView=$html;
		
	}
	
	
	public function KBInstall()
	{
		$panel = new sm_Panel();
		$panel->setTitle("KB Install Taxonomy Data");
		$panel->icon("<i class='HLM_metric_icon'></i>");
		$panel->insert($this->KB_Taxonomy_manager());
	
		$page = new sm_Page();
		$page->setTitle("KB Taxonomy");
		$page->insert($panel);
		
		//$page->addCss("configurator.css","main",SM_IcaroApp::getFolderUrl("css"));
		$this->uiView=$page;
		$this->uiView->addJS("KB.js","main",SM_KBPlugin::instance()->getFolderUrl("js"));
		
		//$this->addView();
	}
	
	public function KB_Taxonomy_manager(){
		
		$Urls['KBAPPQUERYURL']=sm_Config::instance()->conf['KBAPPQUERYURL'];
		$Urls['KBHYPEROSQUERYURL']=sm_Config::instance()->conf['KBHYPEROSQUERYURL'];
		$Urls['KBVMOSQUERYURL']=sm_Config::instance()->conf['KBVMOSQUERYURL'];
		$Urls['KBSERVICEQUERYURL']=sm_Config::instance()->conf['KBSERVICEQUERYURL'];
		
		$html = new sm_HTML();
		$html->insert("start","<div class=row>");
		foreach($Urls as $k=>$url)
		{
			$this->url=array("name"=>$k,"data"=>$url);
			$html->insert("start-div-".$k,"<div class=col-md-6>");
			$html->insert($k,sm_Form::buildForm('KB_install_tax',$this));
			$html->insert("end-div-".$k,"</div>");
		}
		
		$html->insert("end",("</div>"));
		
		$dlg = new sm_HTML();
		$dlg->setTemplateId("Modal_dlg","ui.tpl.html");
		$dlg->insert("id","DataModal");
		$dlg->insert('title',"KB Check Url");
		$dlg->insert('body',"<div style='text-align:center'><img src='img/wait.gif' /> Loading...</div>");
		$html->insert("dlg", $dlg);
		
		return $html;
	}

	/**
	 * Create the HTML code for the module.
	 */
	public function KBEdit() {
		if(isset($this->model))
		{
			$url=$this->model["tool_url"];
			$html = new sm_HTML();
			$html->insert("<iframe src='".$url."' id='KB_editor_view' ></iframe>");
			$html->addCss("KB.css","main",SM_KBPlugin::instance()->getFolderUrl("css"));
			
			$this->uiView=$html;
			
			//$this->addView();
		}
	}
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(get_class($obj)=="SM_SettingsView")
		{
			$this->extendSettingsView($obj);
		}
	}
		
	public function extendSettingsView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			$userUIView->addCss("KB.css","main",SM_KBPlugin::instance()->getFolderUrl("css"));
			$panel=$userUIView->getUIElement("AppicationSettingsPanel");
			$this->data=$obj->getData();
			$panel->insert("<div id='KB_config'>");
			$panel->insert(sm_Form::buildForm("KB_settings", $this));
			$panel->insert("</div>");
			$dlg = new sm_HTML();
			$dlg->setTemplateId("Modal_dlg","ui.tpl.html");
			$dlg->insert("id","DataModal");
			$dlg->insert('title',"KB Check Url");
			$dlg->insert('body',"<div style='text-align:center'><img src='img/wait.gif' /> Loading...</div>");
			$panel->insert($dlg);
		}
	}
	
	
	function KB_install_tax_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"KB/install"
		));	
			
			$url = $this->url;
			$url['data']['description']=str_ireplace("Knowledge Base Query Url for gettings", "Url for", $url['data']['description']);
			$form->addElement(new Element_HTML("<fieldset><legend>".$url['data']['description']."</legend>"));
			$form->addElement(new Element_Hidden("var",$url['name']));
			$form->addElement(new Element_Url("Url","url",array('id'=>$url['name'],'value'=>$url['data']['value'],'shortDesc'=>"<p style='padding-top:10px;'><button type='button' name='".$url['name']."' class='checkUrl button light-gray btn btn-default btn-xs'>Test Url</button>
							<input type='submit' value='Save' name='' class='button light-gray btn btn-xs'></p>")));
			$form->addElement(new Element_HTML("</fieldset>"));
	}	
	
	function KB_settings_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//	"view" => new View_Vertical,
				//"labelToPlaceholder" => 0,
				"action"=>"SM/settings"
		));
	
		//foreach($this->data['HLM'] as $m=>$p)
			$m='KB';
		{
			
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
	
	function KB_install_tax_form_submit($data,&$form)
	{
		$url=$data['url'];
		$var=$data['var'];
		sm_Config::set($var, array("value"=>$url),"KB");
		$kb=new KB();
		$str = $kb->dowloadXml($url);
		$data=XML2Array::createArray($str);
		$title = $data['sparql']['head']['variable']['@attributes']['name'];
		$results=$data['sparql']['results']['result'];
		
		$parent=SM_Taxonomy::insert(array("name"=>$title,"parent"=>0));
		
		foreach($results as $i=>$result)
		{
			$uri = explode("#",$result['binding']['uri']);
			SM_Taxonomy::insert(array("name"=>$uri[1],"uri"=>$result['binding']['uri'],"parent"=>$parent));				
		}
		
	}
	
	public function dashboard_system($data=null,$tpl=null,$css=null){
		$html=null;
		if($data){
			$html=array();
			
			$this->setTemplateId("KB_status",$tpl['path']);
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
				$html['css']=$css;
		}
		return $html;
	}
	
	static function menu(sm_MenuManager $menu)
	{
		//$menu->setMainLink("KB",'#',"edit");
		
		$menu->setSubLink("Configurations","KB Editor","KB/configuration");
	}
}