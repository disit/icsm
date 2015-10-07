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

class sm_Observer
{
	protected $observers;
	
	protected $observerPaths;
	
	static public $instance;
	
	public function __construct()
	{
		include "config.inc.php";
		$this->observerPaths=array();
		foreach($classPath as $_path=>$p){
			$this->observerPaths[]=$p."/observers";
		}
		spl_autoload_register(array($this,'observer_autoloader'));
		$this->observers=array();
		$this->loadObservers();
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
	
	public function observer_autoloader($class) {
				
		foreach($this->observerPaths as $path=>$p){
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
	
	protected function loadObservers()
	{
		$path="";
		$results=array();
		/*$results=glob("{".$path."{apps,apps/observers}/*Observer.class.php}",GLOB_BRACE);
		$results=array_merge($results,glob("{".$path."system/observers/*Observer.class.php}",GLOB_BRACE));
		*/
		foreach($this->observerPaths as $path=>$p)
		{	
			$file_names = glob($p."/*Observer.class.php",GLOB_BRACE);
			if( FALSE === $file_names || count($file_names)==0) //FALSE === file_exists($file_name) )
				continue;
			else
				$results=array_merge($results,$file_names);
		}
		
		foreach($results as $r=>$p)
		{
			$k=explode("/",str_replace(".class.php", "", $p));
			$k=$k[count($k)-1];
			$this->observers[$k]= new $k;
		}
		
	}
	
	public function bootstrap()
	{
		foreach($this->observers as $k=>$m)
		{
			if(method_exists($m,'bootstrap'))
				$m->bootstrap();
		}
	}
	
	static public function register($observer)
	{
		if(is_object($observer))
			self::instance()->observers[get_class($observer)]=$observer;
		elseif(is_string($observer))
			self::instance()->observers[$observer]=new $observer();
	}
	
	static public function unregister($observer)
	{
		if(is_object($observer))
			unset(self::instance()->observers[get_class($observer)]);
		elseif(is_string($observer))
			unset(self::instance()->observers[$observer]);
	}
}
