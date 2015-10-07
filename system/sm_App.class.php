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

class sm_App /*extends smWidget*/ {
	/**
	 * Custom data
	 * @var mixed
	 */
	protected $data;

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
	 */
	public $op; 
	
	protected $controller;
	protected $view;
	protected $menu;
	protected $observers;
	protected $widgets;
	protected $messages;
	protected $plugins;
	
	protected $redirect;
	
	static public $instance=null;
	
	function __construct(sm_UIElement $uiView=null) {
		$this->widgets=array();
		$this->controller = sm_Controller::instance();
		$this->view = sm_View::instance();
		if($uiView)
			$this->view->setMainView($uiView);	
		$this->observer=sm_Observer::instance();
		$this->plugins = sm_PluginManager::instance();
		$this->menu=sm_MenuManager::instance();
		$this->messages=new sm_Message();
		$this->redirect="";
		self::$instance=$this;
		
	}
		
	public function bootstrap()
	{
		if($this->plugins)
			$this->plugins->bootstrap();
		if($this->controller)
			$this->controller->bootstrap();
		if($this->view)
			$this->view->bootstrap();
		if($this->observer)
			$this->observer->bootstrap();
		if($this->menu)
			$this->menu->bootstrap();
		
	}
	
	public function handle()
	{
		$this->bootstrap();
		$controller=$this->controller->dispatch();
		if(!$this->isRedirection())
		{
			if(isset($controller))
			{
				if(!$this->controller->isCallback())
					$this->view->build();
				else 
				{			
					$viewCallBack = $controller->getView();
					if(isset($viewCallBack))
						$this->view->buildCallbackData($viewCallBack);
				}
				$this->view->render();
				return;
			}
			sm_send_error(400);
				
		}
		$this->redirect();
	}
	
	
	function isRedirection()
	{
		return $this->redirect!="";
	}
	
	function redirect(){
		if($this->redirect!="")
			header("Location:".$this->redirect);
	}
	
	function setRedirection($redirect)
	{
		$this->redirect=$redirect;
	}
		
	static public function getInstance()
	{
		if(self::$instance ==null)
		{
			$c=__CLASS__;
			self::$instance=new $c();
		}
		return self::$instance;
	}
	
}

