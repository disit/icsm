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

class sm_Dispatcher
{
    /**
     * The suffix used to append to the class name
     * @var string
     */
    protected $suffix;
    
     /**
     * The prefix used to prepend to the class name
     * @var string
     */
    protected $prefix;

    /**
     * The path to look for classes (or controllers)
     * @var string
     */
    protected $classPath;
    
    /**
     * The obj where dispacthed for classes (or controllers)
     * @var object
     */
    protected $obj;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setSuffix('');
        $this->obj=null;
    }

    /**
     * Attempts to dispatch the supplied Route object. Returns false if it fails
     * @param Route $route
     * @param mixed $context
     * @throws classFileNotFoundException
     * @throws badClassNameException
     * @throws classNameNotFoundException
     * @throws classMethodNotFoundException
     * @throws classNotSpecifiedException
     * @throws methodNotSpecifiedException
     * @return mixed - result of controller method or FALSE on error
     */
    public function dispatch( sm_Route $route, $context = null )
    {
        $class      = trim($route->getMapClass());
        $method     = trim($route->getMapMethod());
        $arguments  = $route->getMapArguments();
		$httpMethod = $route->getHttpMethod();
		if ($httpMethod == 'PUT' || $httpMethod == 'POST') {
			$arguments['data'] = $this->getData();
		}
		
        if( '' === $class )
            throw new classNotSpecifiedException('Class Name not specified');

        if( '' === $method )
            throw new methodNotSpecifiedException('Method Name not specified');

        //Because the class could have been matched as a dynamic element,
        // it would mean that the value in $class is untrusted. Therefore,
        // it may only contain alphanumeric characters. Anything not matching
        // the regexp is considered potentially harmful.
        $class = str_replace('\\', '', $class);
        preg_match('/^[a-zA-Z0-9_]+$/', $class, $matches);
        if( count($matches) !== 1 )
            throw new badClassNameException('Disallowed characters in class name ' . $class);

    /*    //Apply the suffix
        $file_name = glob($this->classPath . $this->prefix.$class . $this->suffix,GLOB_BRACE); //$this->classPath . $class . $this->suffix;
        //$class = $class . str_replace($this->getFileExtension(), '', $this->suffix);
        //var_dump($method);
        //At this point, we are relatively assured that the file name is safe
        // to check for it's existence and require in.
        if( FALSE === $file_name || count($file_name)==0) //FALSE === file_exists($file_name) )
            throw new classFileNotFoundException('Class file not found');
        else
            require_once($file_name[0]);*/
        foreach($this->classPath as $path=>$p){
        	$file_name = glob($p . $this->prefix.$class . $this->suffix,GLOB_BRACE);
        	//At this point, we are relatively assured that the file name is safe
        	// to check for it's existence and require in.
        	if( FALSE !== $file_name && count($file_name)>0) //FALSE === file_exists($file_name) )
        		break;
        	else
        		$file_name=null;
        	
        }
        if( !$file_name)
            throw new classFileNotFoundException('Class file not found');
        else
            require_once($file_name[0]);
		 $s=explode("/",$file_name[0]);
		 $class=str_replace($this->suffix,"",$s[count($s)-1]);
        //Check for the class class
        if( FALSE === class_exists($class) )
            throw new classNameNotFoundException('class not found ' . $class);

        //Check for the method
        if( FALSE === method_exists($class, $method))
            throw new classMethodNotFoundException('method not found ' . $method);
		
        //All above checks should have confirmed that the class can be instatiated
        // and the method can be called
        return $this->dispatchController($class, $method, $arguments, $context);
    }
    
    /**
     * Create instance of controller and dispatch to it's method passing
     * arguments. Override to change behavior.
     * 
     * @param string $class
     * @param string $method
     * @param array $args
     * @return mixed - result of controller method
     */
    protected function dispatchController($class, $method, $args, $context = null)
    {
        $this->obj = new $class($context);
        return call_user_func_array(array($this->obj, $method), $args);
    }

    /**
     * Sets a suffix to append to the class name being dispatched
     * @param string $suffix
     * @return Dispatcher
     */
    public function setSuffix( $suffix )
    {
        $this->suffix = $suffix . $this->getFileExtension();

        return $this;
    }
    
/**
     * Sets a prefix to prepend to the class name being dispatched
     * @param string $prefix
     * @return Dispatcher
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the path where dispatch class (controllers) reside
     * @param string $path
     * @return Dispatcher
     */
    public function setClassPath( $path )
    {
    	$this->classPath=array();
        if(is_string($path))
        	$this->classPath[] = preg_replace('/\/$/', '', $path) . '/';
        elseif(is_array($path))
        	foreach($path as $i=>$p)
        	$this->classPath[] = preg_replace('/\/$/', '', $p) . '/';

        return $this;
    }

    public function getFileExtension()
    {
        return '.php';
    }
    
    public function getObjectDispatched()
    {
    	return $this->obj;
    }
    
    public function getData()
    {
    	$data = file_get_contents('php://input');
    	$values=array();
    	if($data)
    	{
    		parse_str($data, $values);
    		if(count($values)>0)
    			$data=$values;
    	}
    		
    	/*if ($this->format == SM_RestFormat::XML) {
    		$data = XML2Array::createArray($data);
    	} else {
    		$data = json_decode($data);
    	}*/
    
    	return $data;
    }
    
    
}

class badClassNameException extends Exception{}
class classFileNotFoundException extends Exception{}
class classNameNotFoundException extends Exception{}
class classMethodNotFoundException extends Exception{}
class classNotSpecifiedException extends Exception{}
class methodNotSpecifiedException extends Exception{}

