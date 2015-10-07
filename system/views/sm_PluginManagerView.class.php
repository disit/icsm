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

class sm_PluginManagerView extends sm_ViewElement
{
	function __construct($model=null)
	{
		parent::__construct($model);	
		$this->uiView =new sm_Page("PluginManagerView");
		$this->uiView->setTitle("Plugins");
		$this->uiView->addCSS("plugins.css");
	}
	
	function setOp($op=null) //,$args=null)
	{
		$this->op=$op;
	
	}
	function build()
	{
		// display main template
	
		switch ($this->op)
		{
			case 'list':
				$this->plugin_list();
			break;
				
		}
	}
	
	function plugin_list()
	{		
	
		$panel = new sm_Panel("PluginsInstalled");
		$panel->setTitle("Plugin Installed");
		$panel->setClass("pluginManagerList");
		$panel->icon("<i class='glyphicon glyphicon-cog'></i>");
		foreach($this->model['installed'] as $k=>$plugin_data)
		{
			$this->plugin_data=$plugin_data;
			$panel->insert(sm_Form::buildForm("plugin_installed",$this));
		}
		$this->uiView->insert($panel);
		
		$panel = new sm_Panel("PluginsAvailable");
		$panel->setTitle("Plugin Available");
		$panel->setClass("pluginManagerList");
		$panel->icon(sm_formatIcon("paperclip"));
		foreach($this->model['available'] as $k=>$plugin_data)
		{
			$this->plugin_data=$plugin_data;
			$panel->insert(sm_Form::buildForm("plugin_available",$this));
		}
		$this->uiView->insert($panel);
		
		//$this->addView();

	}
	
	function plugin_installed_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"plugins/list/actions",
				
		));
	
	
	    $count=0;
	    //$form->addElement(new Element_HTML("<h4>Installed</h4>"));
		//foreach($this->model['installed'] as $k=>$plugin_data)
	    $plugin_data=$this->plugin_data;
		{
			$form->configure(array("id"=>$plugin_data['class'],"type"=>"plugin_installed"));
			/*if($count%4==0)
				$form->addElement(new Element_HTML("<div class='row'>"));*/
			$this->setTemplateId("plugin","plugins.tpl.html");
			ob_start();
			if(!isset($plugin_data['status']))
			{
				$btn = new Element_GenericButton("Install","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
			}
			else if($plugin_data['status']==PLUGIN_INSTALLED)
			{
				$btn = new Element_GenericButton("Uninstall","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
				$btn = new Element_GenericButton("Disable","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
			}
			else if($plugin_data['status']==PLUGIN_DISABLED)
			{
				$btn = new Element_GenericButton("Uninstall","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
				$btn = new Element_GenericButton("Enable","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
			}
			 
			$plugin_data['actions']=ob_get_contents();
			ob_end_clean();
		//	var_dump($plugin_data)	;	
			$this->tpl->addTemplatedata(
					'plugin',
					$plugin_data
			);
			$form->addElement(new Element_HTML("<div class='col-lg-3 col-md-4 col-sm-6'>"));
			//$form->addElement(new Element_Checkbox("","plugin",array($v),array("class"=>"")));
			$form->addElement(new Element_HTML($this->tpl->getTemplate("plugin")));
			
			$form->addElement(new Element_HTML("</div>"));
		/*	if(($count!=0 && ($count+1)%4==0) || count($this->model['installed'])==$count+1)
				$form->addElement(new Element_HTML("</div>"));
			$count++;*/
		}
		
		//exit();		
	
	
	}
	
	function plugin_available_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"plugins/list/actions",
				
		));
	
	
		$count=0;
		//$form->addElement(new Element_HTML("<h4>Installed</h4>"));
		//foreach($this->model['available'] as $k=>$plugin_data)
		$plugin_data=$this->plugin_data;
		{
			$form->configure(array("id"=>$plugin_data['class'],"type"=>"plugin_available"));
			/*if($count%4==0)
				$form->addElement(new Element_HTML("<div class='row'>"));*/
			$this->setTemplateId("plugin","plugins.tpl.html");
			ob_start();
			
				$btn = new Element_GenericButton("Install","",array("name"=>$plugin_data['class'],"class"=>"button light-gray btn-xs btn"));
				$btn->render();
				
			$plugin_data['actions']=ob_get_contents();
			ob_end_clean();
			//	var_dump($plugin_data)	;
			$this->tpl->addTemplatedata(
					'plugin',
					$plugin_data
			);
			$form->addElement(new Element_HTML("<div class='col-md-3'>"));
			//$form->addElement(new Element_Checkbox("","plugin",array($v),array("class"=>"")));
			$form->addElement(new Element_HTML($this->tpl->getTemplate("plugin")));
				
			$form->addElement(new Element_HTML("</div>"));
		/*	if(($count!=0 && ($count+1)%4==0) || count($this->model['available'])==$count+1)
				$form->addElement(new Element_HTML("</div>"));
			$count++;*/
		}
	
		//exit();
	
	
	}
	
	static public function menu(sm_MenuManager $menu)
	{
		if($menu)
		{
			$menu->setSubLink("Settings",'Plugins','plugins/list');
		}	
	}
}