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

class sm_View //implements sm_Module
{
	
	/**
	 * smDatabase object
	 * @var object
	 */
	protected $db;
	/**
	 * 
	 * @var unknown
	 */
	protected $widgets;
	
	/**
	 * 
	 * @var unknown
	 */
	protected $viewsPaths;
	
	/**
	 * 
	 * @var unknown
	 */
	protected $route;
	
	/**
	 *
	 * @var unknown
	 */
	protected $mainView;
	
	/**
	 * 
	 * @var unknown
	 */
	static protected $instance=null;
	
	
	
	function __construct() {
		
		include "config.inc.php";
		$this->viewsFolder=isset($classPathStructure["views"])?$classPathStructure["views"]:"/views";
		$this->viewsPaths=array();
		foreach($classPath as $_path=>$p){
			$this->viewsPaths[]=$p.$this->viewsFolder;
		}
		spl_autoload_register(array($this,"view_autoloader"));
		$this->widgets=array();
		
		$this->db = sm_Database::getInstance();
		$this->mainView = new sm_Site();
	}
	
	function bootstrap(){
		$this->loadViews();
		foreach($this->widgets as $k=>$m)
		{
			if(method_exists($k,'bootstrap'))
				$m->bootstrap();
		}
	}
	
	protected function loadViews()
	{
		$this->views=array();
		foreach($this->viewsPaths as $path=>$p){
			$file_name = glob($p."/*View.class.php",GLOB_BRACE); //$this->classPath . $class . $this->suffix;
			//At this point, we are relatively assured that the file name is safe
			// to check for it's existence and require in.
			if( FALSE === $file_name || count($file_name)==0) //FALSE === file_exists($file_name) )
				continue;
			else
			{
				foreach($file_name as $k=>$filename)
				{
					$k=explode("/",str_replace(".class.php", "", $filename));
					$k=$k[count($k)-1];
					$this->views[]=$k;
					sm_EventManager::addEventHandler($k);
				}
			}
		}
		
	}
	
	public function view_autoloader($class) {
	
		foreach($this->viewsPaths as $path=>$p){
			$file_name = glob($p."/".$class . ".class.php",GLOB_BRACE); //$this->classPath . $class . $this->suffix;
			//At this point, we are relatively assured that the file name is safe
			// to check for it's existence and require in.
			if( FALSE === $file_name || count($file_name)==0) //FALSE === file_exists($file_name) )
				continue;
			else
			{
				include ($file_name[0]);
				return;
			}
		}
	}
	
	
	/**
	 * Get the current instance of the View
	 * @return sm_View 
	 */
	static public function instance()
	{
		if(self::$instance ==null)
		{
			$c=__CLASS__;
			self::$instance=new $c();
		}
		return self::$instance;
	}
	
		
	static public function addViewPath($path)
	{
		self::instance()->viewsPaths[]=$path.self::instance()->viewsFolder;

	}
	
	/**
	 * Add a widget to the list of widgets instances
	 * @param sm_Widget $w
	 */

	protected function addWidget(sm_Widget $w)
	{
		$id = count($this->widgets);
		$w->setWid($id);
		$this->widgets[]=$w;
	}
	
	/**
	 * Register a widget to the list of widgets instances
	 * @param sm_Widget $w
	 */
	public function register(sm_Widget $w)
	{
		$this->addWidget($w);
		
	}
	
	/**
	 * Unregister a widget to the list of widgets instances
	 * @param sm_Widget  or class name $w
	 */
	public function unregister($w)
	{
		$id=$w;
		if(is_object($w))
		{
			$id = $w->getwid();
		}
		if(isset($this->widgets[$id]))
		{
			$this->widgets[$id]=null;
		}
	
	}
	
	
	public function invoke($method,$obj)
	{
		sm_call_method($method,$obj,$this->views,"sm_ViewElement");
	}
	
	
	public function render(){
		echo $this->mainView->render();
	}
	
	/**
	 * Build the view
	 * 
	 */
	public function build()
	{
		foreach($this->widgets as $widget)
		{
			if(method_exists($widget,'build'))
			{			
				$widget->build();
				sm_EventManager::handle(new sm_Event("ExtendView",$widget));
				$widget->add2View();
			}
		}
	}
	
	function buildCallbackData(sm_ViewElement $view)
	{
		$view->build();
		sm_EventManager::handle(new sm_Event("ExtendView",$view));
		$data=$view->getUIView();
		if(is_a($data,"sm_JSON") || is_a($data,"sm_XML"))
			$this->mainView=$data;
		else 
		{
			$this->mainView = new sm_HTML();
			$this->mainView->setTemplateId("html","ui.tpl.html");
			$this->mainView->insert("html", $data);
		}
	}
	
	static public function insert($var,$obj)
	{
		self::$instance->mainView->insert($var, $obj);
	}
	
	static public function setMainView(sm_UIElement $obj)
	{
		self::$instance->mainView=$obj;
	}
	
	static public function getMainView()
	{
		return self::$instance->mainView;
	}
	
	static public function addCss($filename, $path = SM_PATH_CSS) 
	{
		$template_id = self::$instance->mainView->getTemplateId();
		self::$instance->mainView->addCss($filename, $template_id, $path);
	}
	
	static public function addJs($filename, $path = SM_PATH_JS) 
	{
		$template_id = self::$instance->mainView->getTemplateId();
		self::$instance->mainView->addJs($filename, $template_id, $path);
	}


}