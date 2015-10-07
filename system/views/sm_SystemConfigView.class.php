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

class sm_SystemConfigView extends sm_ViewElement //implements  sm_Module
{
	
	function __construct($data=null)
	{
		parent::__construct($data);
		$this->uiView=new sm_Page("SystemConfigView");
		$this->uiView->setTitle("Settings");
		
	}
	
	public function build()
	{
		if($this->op=='response'){
			$this->uiView=new sm_JSON();
			$this->uiView->insert($this->model);
		}
		else 
			$this->build_view();
	}
	
	public function build_view()
	{	
		$menu = new sm_NavBar();
		$menu->setActive($this->type);
		$menu->insert("system", array("url"=>"config/system","title"=>"System","icon"=>"sm-icon sm-icon-system"));
		
		$container = new sm_HTML();
		$container->insert("start-container","<div id=cView_container>");
		$container->insert($this->getType().'_config',$this->build_config());
		$container->insert("end-container","</div>");
		
		$content = new sm_Panel("panel");
		$content->setTitle(ucfirst($this->getType())." Settings");
		$content->icon("<i class='sm-icon sm-icon-".$this->type."'></i>");
		$content->insert($container);
		
		$this->uiView->menu($menu);
		$this->uiView->insert($content);
		$this->uiView->addCss("textarea{width:98% !important;}",'main');
		
		//$this->addView();
	}

	public function render(){
	}
	
	
	function build_config(){
		return sm_Form::buildForm($this->getType().'_config',$this);
	}
	
	function system_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"config/system"
		));
	
		foreach($this->model as $m=>$p)
		{
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.$m.'</legend>'));
			foreach($p as $item=>$i)
			{
				$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			$form->addElement(new Element_Button("Save","",array("name"=>"Save","class"=>"button light-gray btn-xs")));
			$form->addElement(new Element_HTML('</div>'));
		}
		
		
	}
	
			
	/*static public function install($db)
	{
		
	}
	
	static public function uninstall($db)
	{
	 	
	}*/
	
	static function menu(sm_MenuManager $menu)
	{
			$menu->setMainLink("Settings","#","wrench");
			$menu->setSubLink("Settings","System","config/system");		
	}
}
