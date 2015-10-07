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

class sm_TailView extends sm_ViewElement
{
	
	function __construct($data=null)
	{
		parent::__construct($data);
		
	}
	
	/**
	 * Create the HTML code for the module.
	 * Then the tpl_id set in $this->getTemplateId() will be added to the main template automatically
	 */
	public function build() {
		
		switch($this->op)
		{
			case 'view':
				$this->page();
				break;
			case 'refresh':
				$this->refresh();
				break;
			default:
				break;
		}
	}
	
	public function page() {
		if(isset($this->model['title']))
			$title=$this->model['title'];
		else 
			$title="Tail";
		$this->uiView=new sm_Page("Tail");
		$this->uiView->setTitle($title);	
	
		$this->uiView->insert($this->panel());
		$this->uiView->addCss("tail.css","main",sm_TailPlugin::instance()->getFolderUrl("css"));
		$this->uiView->addJs("jqtail.js","main",sm_TailPlugin::instance()->getFolderUrl("js"));
		$this->uiView->addJs("var tailRefreshUrl='".$this->model['refreshUrl']."';");
		//$this->addView();	
	}
	
	public function panel(){
		$panel = new sm_Panel("TailPanel");
		$panel->setTitle($this->model['file']);
		$panel->icon("<i class='tail-icon'> </i>");
		$html = new sm_HTML("tail");
		$html->setTemplateId("tail",sm_TailPlugin::instance()->getFolderUrl("templates")."tail.tpl.html");
		$panel->insert($html);
		return $panel;
	}
	
	public function refresh() {
	
		$json = new sm_JSON();
		$json->insert($this->model);
		$this->uiView=$json;
	}
	
	
	
	
}