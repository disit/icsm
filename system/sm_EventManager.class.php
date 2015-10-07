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

/**
 * Class Manager for events
 *
 * This "class" two static functions for managing events
 * 
 */

class sm_EventManager {

    /* Global array of hooks, mapping eventname => array of callables */

    protected static $_handlers = array();

    /**
     * Add an event handler
     *
     * To run some code at a particular point.
     *
     * The arguments to the handler vary by the event. Handlers can return
     * two possible values: false means that the event has been replaced by
     * the handler completely, and no default processing should be done.
     * Non-false means successful handling, and that the default processing
     * should succeed. (Note that this only makes sense for some events.)
     *
     * Handlers can also abort processing by throwing an exception; these will
     * be caught by the closest code and displayed as errors.
     *
     * @param string   $name    Name of the event
     * @param callable $handler Code/Function to run
     *
     * @return void
     */

    public static function addHandler($name, $handler) {
    	if(!sm_EventManager::hasHandler($name,$handler[0]))
    	{	
	        if (array_key_exists($name, sm_EventManager::$_handlers)) {
	            sm_EventManager::$_handlers[$name][] = $handler;
	        } else {
	            sm_EventManager::$_handlers[$name] = array($handler);
	        }
    	}
    }

    /**
     * Handle an event
     *
     * Events are any point in the code that we want to expose for admins
     * or third-party developers to use.
     *
     * We pass in an array of arguments (including references, for stuff
     * that can be changed), and each assigned handler gets run with those
     * arguments. Exceptions can be thrown to indicate an error.
     *
     * @param string $name Name of the event that's happening
     * @param array  $args Arguments for handlers
     *
     * @return boolean flag saying whether to continue processing, based
     *                 on results of handlers.
     */

   // public static function handle($name, $args=array()) {
    public static function handle(sm_Event &$event) {
        $result = null;
        $name=$event->getType();
        if (array_key_exists($name, sm_EventManager::$_handlers)) {
            foreach (sm_EventManager::$_handlers[$name] as $handler) {
            	$obj = new $handler[0];
            	$result = call_user_func_array(array($obj,$handler[1]), array(&$event));
            	unset($obj);
                if ($result === false || $event->hasToStop()) {
                    break;
                }
            }
        }
        return ($result !== false);
    }

    /**
     * Check to see if an event handler exists
     *
     * Look to see if there's any handler for a given event, or narrow
     * by providing the name of a specific class.
     *
     * @param string $name Name of the event to look for
     * @param string $plugin Optional name of the plugin class to look for
     *
     * @return boolean flag saying whether such a handler exists
     *
     */

    public static function hasHandler($name, $class=null) {
        if (array_key_exists($name, sm_EventManager::$_handlers)) {
            if (isset($class)) {
                foreach (sm_EventManager::$_handlers[$name] as $handler) {
                    if ($handler[0] == $class) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * Disables any and all handlers that have been set up so far;
     * use only if you know it's safe to reinitialize all plugins.
     */
    public static function clearHandlers() {
        sm_EventManager::$_handlers = array();
    }

	/*
	 * foreach (get_class_methods($this) as $method) {
            if (mb_substr($method, 0, 2) == 'on') {
                Event::addHandler(mb_substr($method, 2), array($this, $method));
            }
	 */
    
    static public function addEventHandler($class)
    {   
    	$reflection = new ReflectionClass($class);
    	$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    	foreach ($methods as $method) {
    		if (mb_substr($method->getName(), 0, 2) == 'on') {
    			sm_EventManager::addHandler(mb_substr($method->getName(), 2), array($class, $method->getName()));
    		}
    	}
    }
}

