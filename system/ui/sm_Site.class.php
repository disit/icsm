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

class sm_Site extends sm_UIElement
	{

		function __construct($id=null,$parent=null)
		{	
			parent::__construct($id);
			$this->setTemplateId();
			$this->parent=$parent;
			//$this->createHTMLHeaderData();
		}
	
		function setTemplateId($tpl_id="main",$tpl_file="main.tpl.html"){
			$this->items=array();
			$this->tplVars=array();
			$this->newTemplate($tpl_id,$tpl_file);
			$this->tpl_id=$tpl_id;
			$this->tplVars=$this->getTemplateVars($tpl_id);
			$this->createHTMLHeaderData();
		}
		
		/**
		 * Create the HTML code for the module.
		 * First the createHTMLLabels() will be called to add all labels to the template,
		 * Then the tpl_id set in $this->getTemplateId() will be added to the main template automatically
		 */
		public function createHTMLHeaderData() {		
			$this->insert('title', sm_Config::get("SITE_TITLE",""));
			$this->insert('baseUrl', sm_Config::get("BASEURL","/"));
			$tpl_id=$this->tpl_id;
			// add JS and CSS files
			$this->addCSS('bootstrap.min.css',$tpl_id,'css/bootstrap3/');
			$this->addCSS('style.css',$tpl_id,'css/');
			if(sm_Config::get("theme","sb-admin.css")!="")
				$this->addCSS((String)sm_Config::get("theme",'sb-admin.css'),$tpl_id,'css/');
			
			$this->addJS('jquery-1.11.0.min.js',$tpl_id,'js/');
			$this->addJS('jquery-ui-1.10.3.js',$tpl_id,'js/');
			$this->addJS('bootstrap.min.js',$tpl_id,'js/bootstrap3/');
		
		
		
		/*	$s="$(document).ready(function () {
				$('.dropdown-toggle').dropdown();
        		});";
			$this->addJS($s,$tpl_id);*/
		}
	
		function render()
		{
			
			$data=array();
			foreach($this->tplVars as $key)
			{
				if(isset($this->items[$key]))
					$data[$key]=$this->_render($this->items[$key]);
			}
			$this->addTemplateData($this->tpl_id, $data);
			return $this->display($this->tpl_id);
		
		}
	
}