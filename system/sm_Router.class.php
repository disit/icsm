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

class sm_Router
{
    /**
     * Stores the Route objects
     * @var array
     */
    private $routes = array();

    /**
     * A prefix to prepend when calling getUrl()
     * @var string
     */
    private $prefix = "";
    
    /**
     * Object constructor. Optionally pass array of routes to add
     * 
     * @param array[string]Route $routes 
     */
    public function __construct( $routes = array() )
    {
        $this->addRoutes($routes);
    }
    
    /**
     *
     * @param string $prefix 
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
    }
    
    /**
     * Adds a named route to the list of possible routes
     * @param string $name
     * @param Route $route
     * @return Router
     */
    public function addRoute( $name, $route )
    {
        $this->routes[$name] = $route;

        return $this;
    }

    /**
     * Adds an array of named routes to the list of possible routes
     * 
     * @param array[string]Route $routes
     * @return Router
     */
    public function addRoutes( $routes )
    {
        $this->routes = array_merge($this->routes, $routes);

        return $this;
    }

    /**
     * Returns the routes array
     * @return [Route]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Builds and gets a url for the named route
     * @param string $name
     * @param array $args
     * @param bool $prefixed
     * @throws NamedPathNotFoundException
     * @throws InvalidArgumentException
     * @return string the url
     */
    public function getUrl( $name, $args = array(), $prefixed = true )
    {
        if( TRUE !== array_key_exists($name, $this->routes) )
            throw new NamedPathNotFoundException;
        
        $match_ok = TRUE;

        //Check for the correct number of arguments
        if( count($args) !== count($this->routes[$name]->getDynamicElements()) )
            $match_ok = FALSE;
        
        /*
         * This will assure arguments that are more specific are replaced before.
         * That's important as if we have a route /:some/:something and we input :some before :something in the $args arrau :something's :some will also be replaced.
         */
        if (!function_exists('sortMoreSpecific')) {
          function sortMoreSpecific($a, $b) {
            return (strlen($b) - strlen($a));
          }
        }
        uksort($args, 'sortMoreSpecific');

        $path = $this->routes[$name]->getPath();
        foreach( $args as $arg_key => $arg_value )
        {
            $path = str_replace( $arg_key, $arg_value, $path, $count );
            if( 1 !== $count )
                $match_ok = FALSE;
        }

        //Check that all of the argument keys matched up with the dynamic elements
        if( FALSE === $match_ok )
          throw new InvalidArgumentException;

        if ($prefixed) {
            return $this->prefix . $path;
        } else {
            return $path;
        }
    }

    /**
     * Finds a maching route in the routes array using specified $path
     * @param string $path
     * @return Route
     * @throws RouteNotFoundException
     */
    public function findRoute( $path )
    {
        foreach( $this->routes as $route )
        {
            if( TRUE === $route->matchMap( $path ) )
            {
                return $route;
            }
        }

        throw new RouteNotFoundException;
    }
}

class RouteNotFoundException extends Exception{}
class NamedPathNotFoundException extends Exception{}
