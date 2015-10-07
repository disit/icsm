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

define ("SM_PATH_TPL","templates/");
define ("SM_PATH_JS","js/");
define ("SM_PATH_CSS","css/");

class sm_Template extends smTemplate implements sm_Module 
{
	static protected $instance=null;
	
	
	function __construct() {
		// add the main template
		//$this->newTemplate('main', 'main.tpl.html');
		parent::__construct();
		
	}
	
	function getTemplateVars($id)
	{
		$template = $this->getTemplate($id);
		if (!isset($template)) {
			// file does not exist
			trigger_error('Template not found with id: '.$id);
			return false;
		}
		$tplVars=array();
		if(preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/", $template,$matches))
		{
			foreach($matches[1] as $var)
			{
				$tplVars[$var]="";
			}
		}
		return array_keys($tplVars);
	}
	
	/**
	 * Adds a css file to the list which will be added to the template when display() is called
	 *
	 * @param string $template_id
	 * @param string $filename
	 * @param string $path uses default set in config if non specified
	 */
	public function addCSS($filename, $template_id = 'main', $path = SM_PATH_CSS) {
		//var_dump($filename." ".$template_id." ".$path);
		parent::addCSS($filename, $template_id , $path);
	}
	
	/**
	 * Adds a javascript file to the list which will be added to the template when display() is called
	 *
	 * @param string $template_id
	 * @param string $filename path to file or CSS code to be added inline
	 * @param string $path uses default set in config if non specified
	 */
	public function addJS($filename, $template_id = 'main', $path = SM_PATH_JS) {
		parent::addJS($filename, $template_id , $path);
	}
	
	static function getInstance()
	{
		if(self::$instance==null)
		{
			//$c=__CLASS__;
			self::$instance = new sm_Template();
		}
		return self::$instance ;
	}
	
	static public function install($db)
	{
		 return true;
	}
	
	static public function uninstall($db)
	{
	 	return true;
	}
	
	
}

?>