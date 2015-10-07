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

class sm_Route
{
    /**
     * The Route path consisting of route elements
     * @var string
     */
    private $path;

    /**
     * The name of the class that this route maps to
     * @var string
     */
    private $class;

    /**
     * The name of the class method that this route maps to
     * @var string
     */
    private $method;
    
    /**
     * Stores any set dynamic elements
     * @var array 
     */
    private $dynamicElements = array();
    
    /**
     * Stores any arguments found when mapping
     * @var array 
     */
    private $mapArguments = array();
    
    /**
     * Stores the authorize flag when mapping
     * @var boolean
     */
    private $authorize = false;
    
    /**
     * Stores the http method when mapping
     * @var string
     */
    private $httpMethod="";
    
    /**
     * Stores if the route is a callback/webservice
     * @var boolean
     */
    private $isCallback=false;
    
    /**
     * Stores the access list of grants
     * @var array
     */
    private $access = array();
    
    /**
     * Stores the description of the route
     * @var string
     */
    private $description = "";

    /**
     * Class Constructor
     * @param string $path optional
     */
    public function __construct( $path = NULL )
    {
    	
        if( NULL !== $path )
            $this->setPath( $path );
    }

    /**
     * Set the route path
     * @param string $path
     * @return Route
     */
    public function setPath( $path )
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the route path
     * @return string
     * @access public
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * Set the map class name
     * @param string $class
     * @return Route
     */
    public function setMapClass( $class )
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the map class name
     * @return string
     * @access public
     */
    public function getMapClass()
    {
        return $this->class;
    }
    
    /**
     * Sets the map method name
     * @param string $method
     * @return Route
     */
    public function setMapMethod( $method )
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Gets the currently set map method
     * @return string
     */
    public function getMapMethod()
    {
        return $this->method;
    }

    /**
     * Adds a dynamic element to the Route
     * @param string $key
     * @param string $value
     * @return Route
     */
    public function addDynamicElement( $key, $value )
    {
        $this->dynamicElements[$key] = $value;

        return $this;
    }

    /**
     * Get the dynamic elements array
     * @return array
     */
    public function getDynamicElements()
    {
        return $this->dynamicElements;
    }

    /**
     * Adds a found argument to the _mapArguments array
     * @param string $key
     * @param string $value
     * @return void
     */
    private function addMapArguments( $key, $value )
    {
        $this->mapArguments[$key] = $value;
    }
    
    /**
     * Gets the _mapArguments array
     * @return array
     */
    public function getMapArguments()
    {
        return $this->mapArguments;
    }

    /**
     * Attempt to match this route to a supplied path
     * @param string $path_to_match
     * @return boolean
     */
    public function matchMap( $path_to_match )
    {
        $found_dynamic_class  = NULL;
        $found_dynamic_method = NULL;
        $found_dynamic_args   = array();
        if(empty($path_to_match))
        	return false;
        if (strpos($path_to_match, '://') === FALSE)
        	if($path_to_match[0]!="/")
        		$path_to_match='http://example.com/' .$path_to_match;
        	else 
        		$path_to_match='http://example.com' .$path_to_match;
        //Ignore query parameters during matching
        $parsed = parse_url($path_to_match);
        $path_to_match = $parsed['path'];

        //The process of matching is easier if there are no preceding slashes
        $temp_this_path     = preg_replace('/^\//', '', $this->path);
        $temp_path_to_match = preg_replace('/^\//', '', $path_to_match);

        //Get the path elements used for matching later
        $this_path_elements  = explode('/', $temp_this_path);
        $match_path_elements = explode('/', $temp_path_to_match);

        //If the number of elements in each path is not the same, there is no
        // way this could be it.
        if( count($this_path_elements) !== count($match_path_elements) )
            return FALSE;

        //Construct a path string that will be used for matching
        $possible_match_string = '';
        foreach( $this_path_elements as $i => $this_path_element )
        {
            // ':'s are never allowed at the beginning of the path element
            if( preg_match('/^:/', $match_path_elements[$i]) )
            {
                return FALSE;
            }

            //This element may simply be static, if so the direct comparison
            // will discover it.
            if( $this_path_element === $match_path_elements[$i] )
            {
                $possible_match_string .= "/{$match_path_elements[$i]}";
                continue;
            }

            //Consult the dynamic array for help in matching
            if( TRUE === isset($this->dynamicElements[$this_path_element]) )
            {
                //The dynamic array either contains a key like ':id' or a
                // regular expression. In the case of a key, the key matches
                // anything
                if( $this->dynamicElements[$this_path_element] === $this_path_element )
                {
                    $possible_match_string .= "/{$match_path_elements[$i]}";

                    //The class and/or method may be getting set dynamically. If so
                    // extract them and set them
                    if( ':class' === $this_path_element && NULL === $this->getMapClass() )
                    {
                        $found_dynamic_class = $match_path_elements[$i];
                    }
                    else if( ':method' === $this_path_element && NULL === $this->getMapMethod() )
                    {
                        $found_dynamic_method = $match_path_elements[$i];
                    }
                    else if( ':class' !== $this_path_element && ':method' !== $this_path_element )
                    {
                        $found_dynamic_args[$this_path_element] = $match_path_elements[$i];
                    }

                    continue;
                }

                //Attempt a regular expression match
                $regexp = '/' . $this->dynamicElements[$this_path_element] . '/';
                if( preg_match( $regexp, $match_path_elements[$i] ) > 0 )
                {
                    //The class and/or method may be getting set dynamically. If so
                    // extract them and set them
                    if( ':class' === $this_path_element && NULL === $this->getMapClass() )
                    {
                        $found_dynamic_class = $match_path_elements[$i];
                    }
                    else if( ':method' === $this_path_element && NULL === $this->getMapMethod() )
                    {
                        $found_dynamic_method = $match_path_elements[$i];
                    }
                    else if( ':class' !== $this_path_element && ':method' !== $this_path_element )
                    {
                        $found_dynamic_args[$this_path_element] = $match_path_elements[$i];
                    }

                    $possible_match_string .= "/{$match_path_elements[$i]}";
                    continue;
                }
            }

            // In order for a full match to succeed, all iterations must match.
            // Because we are continuing with the next loop if any conditions
            // above are met, if this point is reached, this route cannot be
            // a match.
            return FALSE;
        }
        
        //Do the final comparison and return the result
        if( $possible_match_string === $path_to_match )
        {
            if( NULL !== $found_dynamic_class )
                $this->setMapClass($found_dynamic_class);

            if( NULL !== $found_dynamic_method )
                $this->setMapMethod($found_dynamic_method);

            foreach( $found_dynamic_args as $key => $found_dynamic_arg )
            {
  
                $this->addMapArguments(substr($key, 1), $found_dynamic_arg);
            }
        }
        
        return ( $possible_match_string === $path_to_match );
    }
    
    /**
     * Set the authorize flag
     * @param boolean $auth
     */
    public function setAuthorize( $auth )
    {
    	$this->authorize = $auth;
    
    }
    
    /**
     * Get the authorize flag
     * @return boolean 
     */
    public function getAuthorize( )
    {
    	return $this->authorize;
    
    }
    
    /**
     * Set the access array 
     * @param array $accessList or string semicolon separated
     */
    public function setAccess( $accessList )
    {
    	if(is_array($accessList))
    		$this->access = $accessList;
    	else 
    		$this->access=explode(";", $accessList);
    }
    
    /**
     * Get the access array
     * @return array 
     */
    public function getAccess()
    {
    	return $this->access;
    }
    
    /**
     * Set the callback flag
     * @param boolean $isCallback
     */
    public function setCallback( $isCallback )
    {
    	$this->isCallback = $isCallback;
    
    }
    
    /**
     * Get the callback flag
     * @return boolean
     */
    public function getCallback( )
    {
    	return $this->isCallback;
    
    }
    
     /**
     * Set the HttpMethod 
     * @param string $method
     */
    public function setHttpMethod($method)
    {
    	$this->httpMethod=$method;
    }
    
    /**
     * Get the HttpMethod flag
     * @return string
     */
    public function getHttpMethod( )
    {
    	return $this->httpMethod;
    
    }
    
    
}

