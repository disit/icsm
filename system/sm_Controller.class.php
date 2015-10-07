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

class sm_Controller implements sm_Module
{
	/*The Route Manager*/
	protected $routers;
	
	/*The Route Dispatcher Manager*/
	protected $dispatcher;
	
	/*The selected route*/
	protected $theRoute;
	
	public static $instance=null;
	
	/*The Root Dir */
	protected $root;
	
	/*The Controllers' Path */
	protected $controllerPaths;
	
	/*The Controllers' Folder */
	protected $controllerFolder;
	
	public function __construct()
	{
		//parent::__construct();
		include "config.inc.php";
		$this->controllerPaths=array();
		$this->controllerFolder=isset($classPathStructure["controllers"])?$classPathStructure["controllers"]:"/controllers";
		foreach($classPath as $_path=>$p){
			$this->controllerPaths[]=$p.$this->controllerFolder;
		}
		spl_autoload_register(array($this,'controller_autoloader'));
		$dir = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
		$this->root = ($dir == '.' ? '' : $dir . '/');
		$this->routers['GET'] = new sm_Router();
		$this->routers['POST'] = new sm_Router();
		$this->routers['PUT'] = new sm_Router();
		$this->routers['DELETE'] = new sm_Router();
		$this->dispatcher = new sm_Dispatcher();
	}
	
	function bootstrap()
	{
		//$this->dispatcher->setClassPath('{'.implode(",",$this->controllerPaths).'}'); //apps/*/controllers,system/controllers
		$this->dispatcher->setClassPath($this->controllerPaths);
		$this->dispatcher->setSuffix('.class');
		$this->dispatcher->setPrefix('');
		$this->theRoute=null;
		$this->loadControllers();
	}
	
	public function controller_autoloader($class) {
	
		foreach($this->controllerPaths as $path=>$p){
			
			if(substr($p,-1)!="/" || substr($p,-1)!="\\")
				$p.="/";
			if(strlen($p.$class . ".class.php")>250)
			{
				$i=0;
			}
				$file_name = glob($p.$class . ".class.php",GLOB_BRACE); //$this->classPath . $class . $this->suffix;
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
	
	protected function generateMap($class, $basePath="")
	{
		if (is_object($class)) {
			$reflection = new ReflectionObject($class);
		} elseif (class_exists($class)) {
			$reflection = new ReflectionClass($class);
		}
		
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
	
		foreach ($methods as $method) {
			$doc = $method->getDocComment();
			$noAuth = strpos($doc, '@noAuth') !== false;
			$callback = strpos($doc, '@callback') !== false;
			$perms=array();
			$regx='/@perms[ \t]+([ \w":,]+)\s?/i';
			if(preg_match_all($regx, $doc,$matches,PREG_SET_ORDER))
			{
				$s=str_replace('"',"",$matches[0][1]);
				$s=str_replace(', ',",",$s);
				if(!empty($s))
				{
					$perms=explode(",",$s);
				}
			}
			$access=array();
			$regx='/@access[ \t]+([ \w",]+)\s?/i';
			if(preg_match_all($regx, $doc,$matches,PREG_SET_ORDER))
			{
				$s=str_replace('"',"",$matches[0][1]);
				$s=str_replace(', ',",",$s);
				if(!empty($s))
				{
					$access=explode(",",$s);
				}
			}
				
			if (preg_match_all('/@url[ \t]+(GET|POST|PUT|DELETE|HEAD|OPTIONS)[ \t]+\/?(\S*)/s', $doc, $matches, PREG_SET_ORDER)) {
	
				foreach ($matches as $match) {
					$params = $method->getParameters();
					$route = new sm_Route();
					$route->setMapClass($class);
					$route->setMapMethod( $method->getName() );
					$route->setAuthorize(!$noAuth);
					$route->setCallback($callback);
					if(count($perms)>0 || count($access)>0)
					{
						$authorized=false;
						foreach($perms as $permKey)
						{
							$authorized|=sm_ACL::checkPermission($permKey);
						}
						foreach($access as $roleName)
						{
							$authorized|=sm_ACL::checkRole($roleName);
						}	
						$route->setAuthorize($authorized);
						//$route->setAccess($access);
					}
					$httpMethod = $match[1];
					$url = $basePath . $match[2];
					if ($url && $url[strlen($url) - 1] == '/') {
						$url = substr($url, 0, -1);
					}
					//$call = array($class, $method->getName());
					/*$args = array();
					foreach ($params as $param) {
						//$args[$param->getName()] = $param->getPosition();
						//$route->addDynamicElement($key, $value);
					}*/
					//$call[] = $args;
					//$call[] = null;
					//$call[] = $noAuth;
						
					//$this->map[$httpMethod][$url] = $call;
					
					$params=explode("/",$url);
					foreach ($params as $param=>$p) {
						if($p[0]==":")
							$route->addDynamicElement($p, substr($p,0));
					}
					$route->setHttpMethod($httpMethod);
					
					$route->setPath($url);
					$this->routers[$httpMethod]->addRoute($url, $route);
				}
				
				
			}
		}
	
	}
	
	
	protected function loadControllers()
	{
		$this->controllers=array();
		foreach($this->controllerPaths as $path=>$p){
			$file_name = glob($p."/*Controller.class.php",GLOB_BRACE); //$this->classPath . $class . $this->suffix;
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
					if(is_subclass_of($k,"sm_ControllerElement"))
					{
						$this->controllers[]=$k;
						sm_EventManager::addEventHandler($k);
						$this->generateMap($k);
					}
				}				
			}
		}

	}
	
	public function getRoute()
	{
		return $this->theRoute;
	}
	
	protected function getPath()
	{
	
		$path = substr(preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']), 0);
		// remove root from path
		if ($this->root) $path = str_replace($this->root, '', $path);
		if (strlen($path)!=0 && $path[strlen($path) - 1] == '/') {
			$path = substr($path, 0, -1);
		}
	
		// remove trailing format definition, like /controller/action.json -> /controller/action
		//$path = preg_replace('/\.(\w+)$/i', '', $path);
		
		return $path;
	}
	
	public function getRoutePath()
	{
		if(isset($this->theRoute))
			return $this->theRoute->getPath();
		return "";
	}
	
	public function dispatch()
	{
		$obj = null;
		$path=$this->getPath();
		if($path!="" && $path[0]!="/")
			$path="/".$path;
		try{
			if($path=="" || $path=="/index")
				$path=sm_Config::get("HOMEPAGE", "");
			$method=$this->getRequestMethod();
			$this->theRoute=$this->routers[$method]->findRoute($path);
		}
		catch(Exception $e)
		{
			//var_dump($path);
			//var_dump($this->router);
		}
		if($this->theRoute)
		{
			if($this->theRoute->getAuthorize())
			{
				try{
				$this->dispatcher->dispatch($this->theRoute);
				$obj =  $this->dispatcher->getObjectDispatched();
				sm_EventManager::handle(new sm_Event("ExtendController",$obj));
				}
				//$this->app->addWidget($this->dispatcher->getObjectDispatched());
				catch(Exception $e)
				{
					
					//trigger_error("adadfsfs");
				}
			}
			else 
			{
				sm_send_error(403,!$this->isCallback());
			}
		}
		return $obj;
	}
	
	public function invoke($method,$obj)
	{
		sm_call_method($method,$obj,$this->controllers,"sm_ControllerElement");
	}
	
	public function isCallback(){
		return ($this->theRoute && $this->theRoute->getCallback());
	}
	public function getRequestMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$override = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : (isset($_GET['method']) ? $_GET['method'] : '');
		if ($method == 'POST' && strtoupper($override) == 'PUT') {
			$method = 'PUT';
		} elseif ($method == 'POST' && strtoupper($override) == 'DELETE') {
			$method = 'DELETE';
		}
		return $method;
	}

	static function instance()
	{
		if(self::$instance ==null)
		{
			$c=__CLASS__;
			self::$instance=new $c();
		}
		return self::$instance;
	}
	
	static public function install($db)
	{
		self::instance()->loadControllers();
		self::instance()->invoke("install",$db);
		return true;
	}
	
	static public function uninstall($db)
	{
		
		self::instance()->invoke("uninstall",$db);
		return true;
	}
	
	static public function register($controller)
	{
		self::instance()->generateMap($controller);
	}
	
	static public function addControllerPath($path)
	{
		self::instance()->controllerPaths[]=$path.self::$instance->controllerFolder;
		self::instance()->dispatcher->setClassPath(self::$instance->controllerPaths);
	}
	
}
