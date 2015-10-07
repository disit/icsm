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

class SM_ConfiguratorController extends SM_RestController
{
	protected $configurator;
	
	
	public function __construct()
	{
		$this->configurator = new SM_Configurator();
	}
	
	
	/**
	 * @desc Gets the version of controller
	 *
	 * @url GET configurator/info
	 *
	 */
	public function getInfo()
	{
		
		$info['name']="SM Configurator for Nagios Api";
		$info['version']=SMCONFIGURATORVERSION;
		$info['identifier']=sm_Config::get('SMCONFIGURATORINSTANCEID', SMCONFIGURATORINSTANCEID);
		$response['response']= new SM_Response('message','Configurator Info',$info);
		return $response; //$user; // serializes object into JSON
	}
	
	
	
	/**
	 * @desc Gets structured data of a configuration by id
	 * @desc Gets structured data of a configuration by id for a specific segment (applications, hosts, tenants, devices)
	 * 
	 * @url GET /configurator/configuration/:id
	 * @url GET /configurator/configuration/:id/:segment
	 * @url GET /configurator/configuration/:id/:segment/:filter
	 *
	 */
	public function getConfiguration($id = null,$segment=null,$filter=null)
	{
		if(!isset($id)) // || !is_numeric($id))
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			if(!$segment)
				$segment="*";
			$data=$this->configurator->getConfigurationData($segment,$id,$filter);
			if($data)
			{
				$response['response']= new SM_Response('message','Monitor Configuration Data',$data);
			}
			else
				throw new SM_RestException(400, "Invalid Parameters or Info not available");
		}
	
		return $response; //$user; // serializes object into JSON
	}
	
	
	/**
	 * @desc Gets info about a queued configuration by id and field
	 *
	 * @url GET /configurator/queue/:id
	 * @url GET /configurator/queue/:id/:field
	 *
	 */
	public function getConfigurationQueueInfo($id=null,$field = null)
	{
		if(!isset($id) )
			throw new SM_RestException(400, "Invalid Parameters");
		else
		{
			if(!isset($field))
				$field='*';
			$info = $this->configurator->getQueueData($field,$id); // possible user loading method
			if($info)
			{
				$response['response']= new SM_Response('message','Monitor Configuration Queue Data',$info);
			}
			else
				throw new SM_RestException(400, "Invalid Parameters or Info not available");
		}
	
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Get the list of configurations stored in the SM database
	 * @desc Get the a page list of configurations stored in the SM database starting :from limit to :howmany
	 *
	 * @url GET /configurator
	 * @url GET /configurator/:from/:howmany
	 */
	
	public function listConfiguration($from=null, $howmany=null)
	{
		if($this->server)
		{
			sm_Logger::write("Getting list of configuration");
			sm_Logger::write("Received from: ".$from." howmany: ".$howmany);
			$result['total'] = $this->configurator->getConfiguration()->getAllCount();
			$limit=null;
			if(!is_null($from) && $howmany)
				$limit="LIMIT ".$from.",".$howmany;
			else if(is_null($from))
				$limit="LIMIT 0,".$result['total'];
			$result['limit']=$howmany;
			$ids=$this->configurator->getConfiguration()->getAll($limit,array(),array('cid'));
			
			foreach($ids as $i=>$v)
			{
				$conf=$this->configurator->getConfigurationData("header", $v['cid']);
				$data[]=$conf['configuration'];
			}
			$result['result']=count($data);
			sm_Logger::write($result);
			$result['configurations']['configuration']=$data;
			if(isset($result['error']))
				//$response['response']= new SM_Response('error','List Configuration',$result['error']);
				throw new SM_RestException(400, $result['error']);
			else
				$response['response']= new SM_Response('message','List Configuration',$result);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Insert a Business configuration in the SM database
	 *
	 * @url POST /configurator
	 * 
	 */
	public function insertBusinessConfiguration($data=null)
	{
		return $this->insertConfiguration($data);
	}
	
	/**
	 * @desc Insert a System configuration in the SM database
	 *
	 * @url POST /configurator/system
	 *
	 */
	public function insertSystemConfiguration($data=null)
	{
		return $this->insertConfiguration($data,"System");
	}
	
	protected function insertConfiguration($data=null,$type="Business")
	{
		if($data && $this->server)
		{
	
			if (is_string($data)) {
				$configuration=$data;
			}
			else if(is_array($data) && isset($data['configuration'])){
				$configuration = Array2XML::createXML('configuration',$data['configuration']); //json_decode($data);
			}
			else
				throw new SM_RestException(400, "Invalid Parameters");
	
			$result = $this->configurator->insert($configuration, $type);
			if(isset($result['error']))
				//$response['response']= new SM_Response('error','Insert Configuration',$result['error']);
				throw new SM_RestException(400, $result['error']);
			else
				$response['response']= new SM_Response('message','Insert Configuration',$result['mid']);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Modify the configuration by mid and post data
	 *
	 * @url PUT /configurator/:id
	 */
	public function updateBusinessConfiguration($id=null,$data=null)
	{
		return $this->updateConfiguration($id, $data);
	}
	
	/**
	 * @desc Modify the configuration by mid and post data
	 *
	 * @url PUT /configurator/system/:id
	 */
	public function updateSystemConfiguration($id=null,$data=null)
	{
		return $this->updateConfiguration($id, $data,"System");
	}
	
	protected function updateConfiguration($id,$data,$type="Business")
	{
		if($id && $data)
		{
			if(is_numeric($id))
				$cid=$id;
			else 
				$cid = $this->configurator->getId(array("description"=>$id));
			if(isset($cid))
			{
				if (is_string($data)) {
					$configuration=$data;
				}
				else if(is_array($data) && isset($data['configuration'])){
					$configuration = Array2XML::createXML('configuration',$data['configuration']); //json_decode($data);
				}
				else
					throw new SM_RestException(400, "Invalid Data");
				
				$result = $this->configurator->update($cid,$configuration,$type);
				if(isset($result['error']))
					throw new SM_RestException(400, $result['error']);
				else
					$response['response']= new SM_Response('message','Update Configuration',$result['mid']);
			}
			else
				throw new SM_RestException(400, "Configuration not found. Invalid or wrong parameter: ".$id);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Delete the configuration by id
	 *
	 * @url DELETE /configurator/:id
	 */
	public function removeConfiguration($id=null)
	{
		if($id)
		{
			$cid = $this->configurator->getMonitor()->getInternalIdbyDescription($id);
			if(isset($cid))
			{
				sm_EventManager::handle(new sm_Event("DeleteConfiguration",new SM_Configuration($cid)));
				$status = $this->configurator->remove($cid);
				if($status)
				{
					$status=$this->configurator->getMonitor()->deleteConfigurationMonitor($cid);
					if($status)
						$response['response']= new SM_Response('message','Delete Configuration',$status);
					else 
						throw new SM_RestException(404, 'Error when deleting Configuration Monitor Data for: '.$id);
				}
				else 
						throw new SM_RestException(404, 'Error when deleting Configuration Data for: '.$id);
			}
			else
				throw new SM_RestException(400, "Configuration not found. Invalid or wrong parameter: ".$id);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
	}

}
