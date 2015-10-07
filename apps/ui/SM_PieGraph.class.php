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

class SM_PieGraph extends sm_HTML
{
	protected $title;
	
	public function SM_PieGraph($id=null){
		parent::__construct($id);
		$this->title="Add a Title";
		$this->setTemplateId("metric_pie",SM_IcaroApp::getFolderUrl("templates")."monitor.tpl.html");
		$this->addCSS("jquery.jqplot.min.css","metric_pie",SM_IcaroApp::getFolderUrl("css"));
		$this->addJS("jquery.jqplot.js","metric_pie",SM_IcaroApp::getFolderUrl("js"));
		$this->addJS("jqplot.pieRenderer.js","metric_pie",SM_IcaroApp::getFolderUrl("js/plugins"));
		$this->addJS("excanvas.js","metric_pie",SM_IcaroApp::getFolderUrl("js"));
		$this->addJS("monitor_piegraph.js","metric_pie",SM_IcaroApp::getFolderUrl("js"));
	}
	
	public function title($str)
	{
		$this->title = $str;
	}
	
	public function render()
	{
		$this->insert("id", $this->id);
		$this->insert("title",$this->title);
		
		return parent::render();
	}
}


