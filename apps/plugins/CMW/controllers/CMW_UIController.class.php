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

class CMW_UIController extends sm_ControllerElement
{

	
	
	static public function install($db)
	{
		self::installDashboard();
		return true;
	
	}
	
	static public function installDashboard()
	{
		if(!class_exists("sm_Board") || !class_exists("sm_DashboardManager") )
			return;
		$dboard= new sm_DashboardManager();
		$dboard->delete(array("module"=>__CLASS__));
	
		//HLM Local Dashboard
		$args=array();
		$board = new sm_Board();
	
		$board->setweight(100);
		$board->setsegment("Monitor");
		$board->setmodule(__CLASS__);
		$board->setref_id(-1);
		$board->settitle("CMW");
		$board->setcallback_args(serialize($args));
		$board->setview_name("monitor");
		$board->setmethod("cmw_local_dashboard");
		$dboard->add($board);	
	
	}
	
		
	public function cmw_local_dashboard(sm_Board $board)
	{
		$args = unserialize($board->getcallback_args());
		$id=$board->getref_id();
		$conf = SM_Configuration::load($id);
		$name=$conf->getname();
		$id=$conf->getidentifier();
		$monitor = new SM_Monitor();
		$data=array();
		$metrics = $monitor->meters($name,$id,"hosts","CMW Metrics Collector");
			//	$conf = SM_Configuration::load($id);	
		if(isset($metrics['meters']) && count($metrics['meters'])>0)
			$data['callback']['args']['data']['metrics']['hosts']=$metrics['meters'];
		
		
		// TODO hlm computing
		$data['callback']['args']['data']['id']=$conf->getdescription();
		$data['callback']['args']['hlm']=array();
		$data['callback']['class']="CMW_View";
		$data['callback']['method']="dashboard_local";
	
		return $data;
	}
}