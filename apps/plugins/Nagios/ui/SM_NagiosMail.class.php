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

class SM_NagiosMail extends sm_Template
{
	public function __construct()
	{
		$this->newTemplate("nagios_mail_body", "../".SM_NagiosPlugin::instance()->getFolder("templates")."nagios.tpl.html");
	}
	public function mailRender(sm_Notification &$notification)
	{
		sm_Logger::write("Rendering Mail From Nagios");
		$array=XML2Array::createArray($notification->message);
		unset($array['event']['@attributes']);
		foreach($array['event'] as $k=>$v)
		{
			$data[]=array("label"=>$k,"text"=>$v);
		}
		
		$this->addTemplateData("nagios_mail_body", array("title"=> $notification->subject));
		$this->addTemplateDataRepeat("nagios_mail_body", "data",$data);
		$notification->message = $this->display("nagios_mail_body");
		$notification->contentType=SM_RestFormat::HTML;
	}
}