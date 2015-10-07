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
define("PREFIX","SM_");

class SM_RestServerController extends SM_RestController
{
	/**
	 * @desc Returns a string to the browser for test purpose
	 * @desc Returns a string to the browser with the :id parameter for test purpose
	 *
	 * @url GET /test
	 * @url GET /test/:id
	 */
	public function test($id=null)
	{
		if($id)
			$s=  "Hi! I got this value: ".$id;
		else 
			$s= "Hello World!";
		$response['response']= new  SM_Response('message','Info',$s);
		return $response;
	}
	
	/**
	 * @desc Gets the version of controller
	 *
	 * @url GET /info
	 *
	 */
	public function getInfo()
	{
	
		$info="SM Api Server Ver. 1.0";
		$response['response']= new  SM_Response('message','Info',$info);
		return $response; //$user; // serializes object into JSON
	}
		
	/**
	 * @desc Gets the api list
	 *
	 * @url GET help
	 *
	 */
	public function help()
	{
	
		$info['name']="TEST Api Server";
		$info['version']="1.0";
		$map = $this->server->getMap();
		$info['api']=array();
		foreach($map as $method=>$api)
		{
			foreach($api as $url=>$desc)
			{
				$module=str_replace(PREFIX, "",$desc[0]);
				if(!isset($info['api'][$module]))
					$info['api'][$module]=array();
				$info['api'][$module]['method'][]=array(
						'url'=>$url,
						"method"=>$method,
						//"args"=>array_keys($desc[2]),
						"description"=>$desc[3]
				);
			}
		}
		$response['response']= new  SM_Response('message','API List',$info);
		return $response; //$user; // serializes object into JSON
	}
}