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

class SM_AppView extends sm_ViewElement 
{
	public function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj) && is_a($obj,"sm_ViewElement") && !sm_Controller::instance()->isCallback())
		{
				$ui=$obj->getUIView();
				$ui->addCSS("SM.css",$ui->getTemplateId(),SM_IcaroApp::getFolderUrl("css"));
		}
	}
}