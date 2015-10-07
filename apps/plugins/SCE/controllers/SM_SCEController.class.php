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

class SM_SCEController extends SM_RestController
{
	
	function __construct()
	{
		
	}
	
	/**
	 * @desc Gets the version of monitor
	 *
	 * @url GET SCE/version
	 *
	 */
	public function getInfo()
	{
		$info['name']="SM Smart Clound Engine Api";
		$info['version']="1.0";
		$info['identifier']=sm_Config::get('SMCONFIGURATORINSTANCEID', SMCONFIGURATORINSTANCEID);
		$response['response']= new SM_Response('message','Smart Clound Engine Info',$info);
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Post a Smart Cloud Engine Event Notification
	 *
	 * @url POST SCE/event/:type
	 *
	 */
	function handle_event($type=null, $data=null)
	{
		
		if($type && $data)
		{	
			
			$format = $this->getServer()->getFormat();
			$json=null;
			if($format==SM_RestFormat::XML)
			{
				$xml = $data;
				
			}
			else if($format==SM_RestFormat::JSON)
			{
				//
				$json['event']=json_decode(json_encode($data),true);
				$json['event']['@attributes']['type']='sce_sla_event';
				$xml = Array2XML::createXML('event',$json['event']);
				
			}
			
			
			$sce = new SM_SCE();
			$sce->save($json['event']);
			//$xpath = new DOMXpath($xml);
		
			$eventStatusData=(Object)array(
					'type'=>"SCE_Event",
					'segment'=>(Object)array(
							'ref' => "ref",
							'status' => "stats",
							'type'=>$type
							),			
			);
			
			
			$render= new SM_SCEMail();
			$notification = new sm_Notification($this);
			$notification->to_user = 1;
			$notification->from_user = 1;
			$notification->type = "alert";
			$notification->message = $xml->saveXML($xml->documentElement);
			$notification->contentType = SM_RestFormat::XML ;
			$notification->subject = "Smart Clound Engine SLA Alarms";
			$notification->setRenderer($render);
			sm_EventManager::handle(new sm_Event("MonitorEvent",$eventStatusData));
			sm_EventManager::handle(new sm_Event("NotificationEvent",$notification));
			$response['response']= new SM_Response('message','SCE Event',true);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; 
	
	}

	function onNotificationSendMailEvent(sm_Event &$event)
	{
		if(is_a($event->getData()->getParentModule(),__CLASS__))
		{
			$event->stopPropagation();
		}
	}
	
	function onDeleteConfiguration(sm_Event &$event)
	{
		$sce = new SM_SCE();
		$conf = $event->getData();
		$id = $conf->getConfiguration()->getdescription();
		sm_Logger::write("Deleting SCE data for ".$id);
		return $sce->deleteConfigurationData($id);
	}
}
