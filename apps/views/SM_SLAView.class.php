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

class SM_SLAView extends sm_ViewElement
{
	function __construct($data=null)
	{
		parent::__construct($data);
	}
	
	public function build()
	{
		switch ($this->op)
		{
			case 'sla::info':
				$this->sla_info_build();
				break;
			case 'sla::view':
				$this->sla_view_build();
				break;
			default:
				parent::build();
				break;
		}
	}
	
	public function sla_view_build(){
		$this->uiView = $panel = new sm_Panel("segment_view");
		$panel->setTitle("SLA");
		$panel->icon("<img src='".SLA_ICO."' />");
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
	
	public function sla_info_build(){
		$this->uiView=$html=new sm_HTML();
		$html->setTemplateId("sla_info",SM_IcaroApp::getFolder("templates")."configurator.tpl.html");
		$html->insert("id","Info_container");
		$html->insert("class","cView_panel");
	}
	
	public function dashboard_overall($data=null)
	{
		$html=null;
		if($data){
			$html=new sm_HTML();
			$html->insert("pre","<div class='dashboard-element col-lg-6 col-md-12 col-sm-12'>");
			$panel = new sm_Panel($data['id']);
			$panel->setTitle($data['title']);
			$panel->setType("default");
			$content=new sm_Table("sla_health_table");
			$content->addHRow();
			$content->addHeaderCell("Host");
			$content->addHeaderCell("Name");
			$content->addHeaderCell("Output");
			$content->addHeaderCell("Time");
			//	$content->addHeaderCell("Next Check");
			$content->addHeaderCell("State");
			$content->addHeaderCell("Level");
			foreach ($data['checks'] as $check)
			{
				$content->addRow();
				$content->addCell($check['host']."<br><small>".$check['address']."</small>");
				$content->addCell($check['name']);
				$content->addCell($check['output']);
				$content->addCell($check['time']);
				//$content->addCell($check['next check']);
				$content->addCell($check['state']);
				$content->addCell($check['level']);
			}
			$panel->insert($content);
			$html->insert("panel",$panel);
			$html->insert("end","</div>");
		}
		return $html;
	}
	
	public function dashboard_local($sla=null){
		
		$html = new sm_HTML();
		$html->insert("message", "No data available");
		return $html;
	}
}