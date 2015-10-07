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

class SM_NagiosController extends SM_RestController
{
	protected $nagios=null;
	function __construct()
	{
		$this->nagios=new SM_NagiosClient();
	}
	
	/**
	 * @desc Gets Nagios restarting
	 *
	 * @url GET /nagios/restart
	 *
	 */
	function restart()
	{
		 $result=$this->nagios->restart();
		
		 if($result)
		 {
		 		$response['response']= new SM_Response('message','Nagios Restart Info',$result);
		 }
		 else
		 	throw new SM_RestException(400, "Invalid Parameters");
		return $response;
	}
	
	/**
	 * @desc Gets Nagios reloading
	 *
	 * @url GET /nagios/reload
	 *
	 */
	function reload()
	{
		$result=$this->nagios->reload();
	
		if($result)
		{
			$response['response']= new SM_Response('message','Nagios Reload Info',$result);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response;
	}
	
	/**
	 * @desc Gets check of Nagios configuration
	 *
	 * @url GET /nagios/check
	 *
	 */
	function check()
	{
		$result=$this->nagios->verifyCfg();
	
		if($result)
		{
			$response['response']= new SM_Response('message','Nagios Check Configuration Info',$result);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response;
	}
	
		
	/**
	 * @desc Post a Nagios Event Notification
	 *
	 * @url POST nagios/event/:type/:from/:to
	 *
	 */
	function nagios_event($type=null, $from=null, $to=-1, $data=null)
	{
		if($type && $from && $data)
		{	
			$xml=new DOMDocument();
			$xml->loadXML($data);
			$xpath = new DOMXpath($xml);	 
			
			$eventStatusData=(Object)array(
					'type'=>"status",
					'segment'=>(Object)array(
							'ref' => $xpath->evaluate('string(/event/hid)'),
							'status' => $xpath->evaluate('string(/event/state)'),
							'type'=>$xpath->evaluate('string(/event/@type)')
							),			
			);
			
		
			$render= new SM_NagiosMail();
			$notification = new sm_Notification($this);
			$notification->to_user = $to;
			$notification->from_user = $from;
			$notification->type = "alert";
			$notification->message = $data;
			$notification->contentType = null;
			$notification->subject = "Nagios ".ucfirst($xpath->evaluate('string(/event/@type)'))." ".ucfirst($type);
			$notification->setRenderer($render);
			sm_EventManager::handle(new sm_Event("MonitorEvent",$eventStatusData));
			sm_EventManager::handle(new sm_Event("NotificationEvent",$notification));
			$response['response']= new SM_Response('message','NagiosEvent',true);
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
}
