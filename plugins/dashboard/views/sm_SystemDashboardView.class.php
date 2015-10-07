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

class sm_SystemDashboardView extends sm_DashboardView
{
	function __construct($data=null)
	{
		parent::__construct($data);
	}

	public function dashboard_page() {
		parent::dashboard_page();
		$this->uiView->setId("SystemDashboard");
		$resize="resize=true;";
		$this->uiView->addJs($resize);
	}

	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("System Info","system/dashboard","dashboard");
	}

}