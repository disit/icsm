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

class SM_NotificationChController extends SM_RestController
{
	function __construct()
	{
		
		
	}
	
	/**
	 * @desc Gets the version of controller
	 *
	 * @url GET notify/info
	 *
	 */
	public function getInfo()
	{
	
		$info['name']="SM Notification Api";
		$info['version']="1.0";
		$info['identifier']=sm_Config::get('SMCONFIGURATORINSTANCEID', SMCONFIGURATORINSTANCEID);
		$response['response']= new SM_Response('message','SM Notification Info',$info);
		return $response; //$user; // serializes object into JSON
	}
	
	/**
	 * @desc Post a notification
	 *
	 * @url POST notify/:type/:from/:to
	 *
	 */
	function notify($type=null,$from=null,$to=-1,$data=null)
	{
		if($type && $from && $data)
		{
			$format = $this->getServer()->getFormat();
			$notification = new sm_Notification();
			$notification->to_user = $to;
			$notification->from_user = $from;
			$notification->type = $type;
			$notification->message = $data;
			$notification->contentType = $format;
			$status = $notification->handle();	
			$response['response']= new SM_Response('message','Notification',$status);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
		
	}
	
		
	function onNotificationEvent(sm_Event &$event)
	{
		$eventData=$event->getData();
		if(is_a($eventData,"sm_Notification"))
			$status = $eventData->handle();
	}
	
	public function onLoadUser(sm_Event &$event=null)
	{
		$user = $event->getData();
		$pref=sm_Notification::loadUserNotificationPreferences($user->userID);
		if(isset($pref[0]))
			$user->userData['notification']['preferences']=$pref[0];
		else
			$user->userData['notification']['preferences']=null;
	}
	
	
}
	