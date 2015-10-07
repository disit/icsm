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

class SM_ConfiguratorEditorUIController extends sm_ControllerElement
{
	protected $model;
	
	function __construct(){
		$this->model=new SM_Configurator();
		$this->view= new SM_ConfiguratorEditorView();
	}
	
	/**
	 * @desc Get edit form for a new/existing configuration
	 *
	 * @url GET /configurator/editor/:id/:type/:sid
	 * @url GET /configurator/editor/:id/:type
	 * @url GET /configurator/editor/:id
	 * @url GET /configurator/editor
	 *
	 */
	function configurator_editor($id=null,$type=null,$sid=null)
	{
		$data=null;
		if(!$id){
			unset($_SESSION["configurator/edit"]);
			$id="new-".time();
		}
		$conf = $this->configurator_configuration_edit($id);
		if($conf)
		{	
			if(!$type)
			{
				$type="Configuration";
				$segments=$conf->getSegment("*");
			}
			else 
			{
				$type=ucfirst($type);
				$segments=$conf->getSegment(strtolower($type),$sid);
				$data[$type]=isset($segments)?$segments[strtolower($type)]:array();
			}
			if(!preg_match("/new-/",$id))
				$data['id']=$conf->getdescription();
			else 
				$data['id']=$conf->getcid();
			$data['configuration']=$conf;
			if($sid)
			{
				$sid=explode(":", $sid);
				$data['sid']=isset($sid[1])?$sid[1]:$sid[0];
			}
			else 
				$data['sid']=null;
			
				
			$this->view->setType($type);
				
		}
		$this->view->setOp("edit");
		$this->view->setModel($data);
	
	}
	
	/**
	 * @desc Get edit form for a new/existing configuration
	 *
	 * @url POST /configurator/editor/add/application
	 *
	 */
	function configurator_editor_add_application($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$app = new Application();
			$app->setname($data['name']);
			$aid=time();
			$app->setaid($aid);
			$app->setcid($data['id']);
			$conf->add($app);
			$_SESSION["configurator/edit"][$data['id']]=$conf->saveXML();
			sm_set_message("Application '".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$data['id']."/Applications/".$aid);
			return;
		}
		else
			sm_set_error("An Error occurred when adding application '".$data['name']."'!");
	}
	
	/**
	 * @desc Get edit form for a new/existing configuration
	 *
	 * @url POST /configurator/editor/add/host
	 *
	 */
	function configurator_editor_add_host($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$item = new Host();
			$item->setname($data['name']);
			$id=time();
			$item->sethid($id);
			$item->setcid($data['id']);
			$conf->add($item);
			$_SESSION["configurator/edit"][$data['id']]=$conf->saveXML();
			sm_set_message("Host '".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$data['id']."/Hosts/".$id);
			return;
		}
		else
			sm_set_error("An Error occurred when adding host '".$data['name']."'!");
	}
	
	/**
	 * @desc Get edit form for a new/existing configuration
	 *
	 * @url POST /configurator/editor/add/device
	 *
	 */
	function configurator_editor_add_device($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$item = new Device();
			$item->setname($data['name']);
			$id=time();
			$item->setdid($id);
			$item->setcid($data['id']);
			$conf->add($item);
			$_SESSION["configurator/edit"][$data['id']]=$conf->saveXML();
			sm_set_message("Device '".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$data['id']."/Devices/".$id);
			return;
		}
		else
			sm_set_error("An Error occurred when adding device '".$data['name']."'!");
	}
	
	/**
	 * Get edit form for a new/existing configuration
	 *
	 * @url POST /configurator/editor/add/tenant
	 *
	 */
	function configurator_editor_add_tenant($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$item = new Tenant();
			$item->setname($data['name']);
			$id=time();
			$item->setdid($id);
			$item->settid($data['id']);
			$conf->add($item);
			$_SESSION["configurator/edit"][$data['id']]=$conf->saveXML();
			sm_set_message("Tenant '".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$data['id']."/Tenants/".$id);
			return;
		}
		else
			sm_set_error("An Error occurred when adding tenant '".$data['name']."'!");
	}
	
	/**
	 * Get edit form for a new/existing configuration
	 *
	 * @url POST /configurator/editor/update/segment
	 *
	 */
	function configurator_editor_update_segment($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$id=$data['id'];
			$sid=$data['sid'];
			$segment = $data["segment"];
			unset($data['id']);
			unset($data['sid']);
			unset($data['segment']);
			$item = $conf->getSegmentObj($segment,$sid);
			$a = (object) $data;
			$item->write($a);
			$_SESSION["configurator/edit"][$id]=$conf->saveXML();
			sm_set_message("'".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$id."/".ucfirst($segment)."/".$sid);
			return;
		}
		else
			sm_set_error("An Error occurred when adding '".$data['name']."'!");
	}
	
	
	function configurator_configuration_edit($id=null) //, $type=null, $sid=null )
	{
		//$data=null;
		if(!preg_match("/new-/",$id))
		{
			//$data=array();
			$conf=SM_Configuration::load($id);
			if(!isset($_SESSION["configurator/edit"][$id]))
			{
				$_SESSION["configurator/edit"][$id]=$conf->saveXML();
			}
		}
		else
		{
			//$data=array();
			$conf=new SM_Configuration();
			$conf->setcid($id);
			$conf->setname($id);
			if(!isset($_SESSION["configurator/edit"][$id]))
			{
				$_SESSION["configurator/edit"][$id]=$conf->saveXML();
			}
		}
		if(isset($_SESSION["configurator/edit"][$id]))
			$conf->parse($_SESSION["configurator/edit"][$id]);
		//var_dump($_SESSION["configurator/edit"][$id]);
		return $conf;
	
	}
	
	/**
	 * @desc GET the service  edit form
	 *
	 * @url GET /configurator/service/editor
	 * @url GET /configurator/service/editor/:id/:aid
	 * @url GET /configurator/service/editor/:id/:aid/:sid
	 *
	 * @callback
	 */
	
	function configurator_editor_service($id=null,$aid=null,$sid=null){
		
		if(!isset($sid))
		{
			$service = new Service();
			$data['sid']=time();
		}
		else {
			$conf = $this->configurator_configuration_edit($id);
			$segment=$conf->getSegment("applications","aid:".$aid);
			$sid=explode(":", $sid);
			$data['sid']=isset($sid[1])?$sid[1]:$sid[0];
			$service = $segment->getService($data['sid']);
			
		}
		$data['id']=$id;
		$data['aid']=$aid;
		$data['service']=$service;
		$this->view->setOp("edit::service");
		$this->view->setModel($data);
	}
	
	/**
	 * @desc Add service to an existing application
	 *
	 * @url POST /configurator/editor/add/service
	 *
	 */
	function configurator_editor_add_service($data)
	{
		$conf = $this->configurator_configuration_edit($data['id']);
		if($data && $conf)
		{
			$id=$data['id'];
			$aid=$data['aid'];
			unset($data['id']);
			$conf = $this->configurator_configuration_edit($id);
			$segment=$conf->getSegmentObj("applications",$aid);
			$item = new Service();
			$item->write((object) $data);
			$segment->addService($item);
			$_SESSION["configurator/edit"][$id]=$conf->saveXML();
			sm_set_message("Service '".$data['name']."' added successfully!");
			sm_app_redirect("configurator/editor/".$id."/Applications/".$aid);
			return;
		}
		else
			sm_set_error("An Error occurred when adding service '".$data['name']."'!");
	}
	
	
	/**
	 * Save the configuration data
	 *
	 * @url POST /configurator/edit
	 */
	function configurator_configuration_save($data=null)
	{
		//if(isset($data['id'])
	}
	
	/**
	 * Save the configuration data
	 *
	 * @url POST /configurator/editor/load
	 */
	function configurator_configuration_load($data=null)
	{
		$this->model->getConfiguration()->parse($data);
	}
}