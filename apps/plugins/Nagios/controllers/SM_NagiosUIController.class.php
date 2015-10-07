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

class SM_NagiosUIController extends sm_ControllerElement
{
	
	function __construct(){
		
	}
	
	/**
	 * @desc View Nagios Model
	 *
	 * @url GET /nagios/view/:id/
	 *
	 * @callback
	 */
	public function nagios_data($id = null)
	{
		//include SM_NagiosPlugin::instance()->getFolderUrl("includes")."SM_NagiosConfigurator.class.php";
		$data=array();
		
		$data['title']="Nagios";
		$data['icon']=SM_NagiosPlugin::instance()->getFolderUrl("img")."nagiosclient.png";
		
		$nagiosConf=new SM_NagiosConfigurator();
		$conf=$nagiosConf->getConfigurationData("*",$id);
		$dom=Array2XML::createXML("configuration",$conf['configuration']);
		$nagiosXML = $nagiosConf->mapNagiosData($dom->saveXML(),true);
		if($nagiosXML)
		{
			$nagios=XML2Array::createArray($nagiosXML);
			$nagiosConf->prepareNagiosData($nagios);
			$dump = print_r($nagios, true);
		}
		else 
			$dump="Error when mapping Nagios XML!";
		
		$data['html']="<pre>".$dump."</pre>";
		$this->view=new sm_NagiosView();
		$this->view->setData($data);
		$this->view->setOp("view");
	}
	
	/**
	 * @desc Nagios Configurator Rollback Command
	 *
	 * @url GET /nagios/rollback/:id/
	 * 
	 * @access System Administrator
	 * 
	 * @callback
	 */
	public function nagios_rollback($id = null)
	{
		$nagiosConf=new SM_NagiosConfigurator();
		$conf=$nagiosConf->getConfigurationData("*",$id);
		$nagiosConf->rollback($id);
		$result = $nagiosConf->getReport();
		foreach($result as $k=>$msg)
		{
			if(is_array($msg))
			{
				foreach($msg as $i=>$s)
				{
					if($k=="error")
						sm_set_error($s);
					else
						sm_set_message($s);
				}
			}
		}
		if(isset($_SERVER['HTTP_REFERER']))
			sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
	/**
	 * @desc Nagios Configurator Rollback Command
	 *
	 * @url GET /nagios/synch/:id/
	 *
	 * @access System Administrator
	 *
	 * @callback
	 */
	public function nagios_synch($id = null)
	{
		$nagiosConf=new SM_NagiosConfigurator();
		$nagiosConf->synchronize($id);
		$result = $nagiosConf->getReport();
		foreach($result as $k=>$msg)
		{
			if(is_array($msg))
			{
				foreach($msg as $i=>$s)
				{
					if($k=="error")
						echo($s."<br>");
					else
						echo($s."<br>");
				}
			}
		}
		exit();
	}
}