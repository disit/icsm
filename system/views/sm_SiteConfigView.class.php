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

class sm_SiteConfigView  extends sm_SystemConfigView
{
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj) && is_a($obj,"sm_SystemConfigView"))
		{
			$this->extendSystemConfigView($obj);
		}
	}
	
	public function extendSystemConfigView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			$menu = $userUIView->getMenu();
			$menu->insert("site",array("url"=>"config/site","title"=>'Site',"icon"=>"sm-icon sm-icon-site"));
		}
	}

	function build()
	{
		parent::build();
	}

	/*static public function install($db)
	{
		
	}

	static public function uninstall($db)
	{
		
	}*/

	function site_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//"view" => new View_Vertical(),
				//"labelToPlaceholder" => 1,
				"action"=>"config/site"
		));

				
		foreach($this->model as $m=>$p)
		{
			$form->addElement(new Element_HTML('<div class="cView_panel" id="'.$m.'">'));
			$form->addElement(new Element_HTML('<legend>'.$m.'</legend>'));
			foreach($p as $item=>$i)
			{
				if(isset($i['options']))
					$form->addElement(new Element_Select($i['description'], $i['name'], $i['options'],array('value'=>$i['value'])));
				else 
					$form->addElement(new Element_Textbox($i['description'],$i['name'],array('value'=>$i['value'],'label'=>$i['description'])));
			}
			$form->addElement(new Element_Button("Save","",array("name"=>"Save",'class'=>"button light-gray btn btn-primary")));
			$form->addElement(new Element_HTML('</div>'));
		}
		//$form->setSubmitMethod("home_config_form_submit");


	}
	
	static function menu(sm_MenuManager $menu)
	{}
	


}