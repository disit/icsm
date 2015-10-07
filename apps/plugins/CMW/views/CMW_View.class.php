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

class CMW_View extends sm_ViewElement
{


	function __construct($data=null)
	{

		parent::__construct($data);

	}

	/**
	 * Create the HTML code for the module.
	 */
	
	public function build() {
		
	}
	
	
	
	
	public function dashboard_local($data=null){ 
	
		$html=null;
		if($data){
			$html_main=new sm_HTML("CMW_Container");
			
			if(isset($data['metrics']['hosts']))
			{
				$html=new sm_HTML();
				$html->insert("pre","<div class='monitor_dashboard dashboard-element col-lg-12 col-md-12 col-sm-12'>");
				
				/*$panel = new sm_Panel($data['id']."hosts");
				$panel->setTitle("Hosts");
				$panel->setType("default");*/
				
				
				$content=new sm_Table("cmw_hosts_table");
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
				$panel->insert("title","Application Metrics");
				$panel->insert('content',$content);
				$panel->addCss("HLM.css","HLM_summary_element_dashboard",SM_HLMPlugin::instance()->getFolderUrl("css"));
				$html->insert("panel",$panel);
				$html->insert("end","</div>");
				$html_main->insert("hosts",$html);
				
			}
		
		}
		if(!isset($data['metrics']))
			$html_main->insert("message","<div class='well well-lg'><h4 style='color:black;'>No Metrics Received from CMW</h4></div>");
	//	$html->addCSS("HLM.css")
		return $html_main;
	}
	
	static function menu(sm_MenuManager $menu)
	{
		//$menu->setSubLink("Configurator","HLM","HLM/page");
	}
}