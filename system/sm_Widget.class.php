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

 abstract class sm_Widget {
	
/**
	 * Custom message
	 * @var string
	 */
	public $message;

	/**
	 * Current mode. Can be used by modules to determine
	 * what to do
	 * @var string
	 */
	public $mode;

	/**
	 * Current op. Can be used by modules to determine
	 * what api call to do
	 	* @var string
	 
	public $op; 
	*/
	 
	/**
	 * smDatabase object
	 * @var object
	 */
	protected $db;

	/**
	 * smTemplate object
	 * @var object
	 */
	protected $tpl;

	/**
	 * Template Id that should be added to the main template
	 * @var string
	 * @see setTemplateId() getTemplateId()
	 */
	protected $tpl_id;
	
	protected $view;
	
	protected $uiView;
	
	protected $tpl_var;
	
	protected $wid;

	function __construct() {
		$this->wid=-1;
		$this->db = sm_Database::getInstance();
		$this->view=sm_View::instance();
		$this->view->register($this);
		$this->tpl = new sm_Template();
		$this->uiView = new sm_UIElement();
		$this->tpl_var="content";
	}

	function __destruct()
	{
		
		$this->view->unregister($this->wid);
	}
	
	
	/**
	 * Create the HTML/UIElement code for the module.
	 * First the createHTMLLabels() will be called to add all labels to the template,
	 */
	public function createHTML() {
		$this->build();
	}

	
	/**
	 * Use this to add language specific labels to template
	 *
	 * @see createHTML()
	 */
	protected function createHTMLLabels() {
		
	}

	/**
	 * Set a template id that will be added to the main template automatically
	 * once you call the parent::createHTML()
	 *
	 * @param string $tpl_id
	 * @param string $tpl_file if given, the tpl_id will be created automatically from this file
	 * @see getTemplateId() createHTML()
	 */
	public function setTemplateId($tpl_id, $tpl_file = null) {
		$this->tpl_id = $tpl_id;

		if($tpl_file != null) {
			// tpl_file given, try to load the template..
			$this->tpl->newTemplate($tpl_id, $tpl_file);
		}
	}

	/**
	 * Get the mpalte id that will be added to the main template
	 *
	 * @return string
	 * @see setTemplateId()
	 */
	public function getTemplateId() {
		return $this->tpl_id;
	}
	
	public function existsTemplate($tpl_id)
	{
		return $this->tpl->getTemplate($tpl_id);
	}
	
	public function useTemplate($tpl_id)
	{
		$this->tpl_id = $tpl_id;
	}
	
	public function getTemplate() {
		return $this->tpl;
	}
	
	function setTemplateVar($tpl_var)
	{
		$this->tpl_var=$tpl_var;
	}
	
	function getTemplateVar($tpl_var)
	{
		return $this->tpl_var;
	}
	
	function addView($tpl_var=null)
	{
		return;

		if($tpl_var)
			$this->tpl_var=$tpl_var;
		if($this->uiView)
			$this->view->insert($this->tpl_var, $this->uiView);	
	}
	
	function add2View()
	{
		if($this->tpl_var && $this->uiView)
			$this->view->insert($this->tpl_var, $this->uiView);
	}
	
	/**
	 * 
	 */
	function getUIView()
	{
		return $this->uiView;
	}
	
	/**
	 * 
	 * @param unknown $w
	 */
	function setWid($w)
	{
		$this->wid=$w;
	}
	
	function getWid()
	{
		return $this->wid;
	}
	
	public function render()
	{
		
	}
	
	public function build()
	{
	
	}
	
 	public function bootstrap()
	{
		
	}
	
	/**
	 * 
	 * @param sm_MenuManager $menu
	 */
 	static public function menu(sm_MenuManager $menu)
	{
		return true;
	}
	
}
