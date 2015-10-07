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

class SM_GraphController extends SM_RestController
{
	protected $graphManager;
	protected $db;

	public function __construct()
	{
		
		$this->graphManager = new SM_GraphManager();
		$this->db=sm_Database::getInstance();
	}


	
	/**
	 * @desc Gets a graph of a segment belong to a configuration by id
	 *
	 * @url GET /graph/:id/:segment/:sid/:ip/:metric/:from/:to/
	 * @url GET /graph/:id/:segment/:sid/:ip/:metric/:from/
	 * @url GET /graph/:id/:segment/:sid/:ip/:metric/
	 * @url GET /graph/:id/:segment/:sid
	 *
	 */
	public function getGraph($id = null, $segment = null, $sid = null, $ip=null, $metric="_HOST_", $from = null, $to = null)
	{
		if(!isset($id) && !isset($segment) && !isset($ip)) // && !isset($sid) && !isset($metric) && !isset($from) ) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			$configuration=new SM_Configuration($id);
			$segment_obj=$configuration->getSegment($segment,$sid);
			if($ip && $ip!='*')
				$host_name=$segment_obj[$segment]->getname().'@'.$ip;
			else
				$host_name=$segment_obj[$segment]->getname().'@'.$segment_obj[$segment]->getip_address();
			$args=array(
					"host"=>$host_name,
					"start"=>$from,
					"end"=>$to,
					"srv"=>$metric,
			);			
			$data=$this->graphManager->getGraph($args);
			if($data)
			{
				$response['response']= new SM_Response('message','Graph Manager',$data);
				$this->server->format="image/png";
			}
			else
				throw new SM_RestException(400, "Invalid Parameters or Info not available");
		}

		return $response;
	}
}
