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

class sm_PluginManagerController extends sm_ControllerElement
{
	protected $model;
	protected $view;
	
	function __construct()
	{
		$this->model = sm_PluginManager::instance();
		$this->view = new sm_PluginManagerView();
	}
	
	/**
	 * Gets the plugin list and status
	 *
	 * @url GET /plugins/list
	 */
	function pluginsList()
	{
		$plugin_data=array();
		$plugin_data['installed']=$this->model->listPlugins();
		$data=$this->model->detectPlugins();
		$plugin_data['available']=array();
		foreach($data as $k=>$plugin)
		{
			//var_dump($plugin);
			$plugin_data['available'][$k]['name']=$plugin->getPluginName();
			$plugin_data['available'][$k]['version']=$plugin->getPluginVersion();
			$plugin_data['available'][$k]['description']=$plugin->getPluginDescription();
			$plugin_data['available'][$k]['class']=get_class($plugin);
			$plugin_data['available'][$k]['status']=null;
		}
		$this->view->setModel($plugin_data);
		$this->view->setOp('list');
	}
	
	/**
	 * Gets the plugin list and status
	 *
	 * @url POST /plugins/list/actions
	 * 
	 * 
	 */
	function pluginsListActions($data)
	{
		unset($_POST['form']);
		if(count($_POST)==1)
		{
			$res=false;
			$k=array_keys($_POST);
			switch(strtolower($_POST[$k[0]]))
			{
				case "disable":
					$res = $this->model->disablePlugin($k[0]);
					break;
				case "enable":
					$res = $this->model->enablePlugin($k[0]);
					break;
				case "install":
					$res = $this->model->installPlugin($k[0]);
					break;
				case "uninstall":
					$res = $this->model->uninstallPlugin($k[0]);
					break;
			}
			if($res)
				sm_set_message("OK: ".$k[0]." => ".$_POST[$k[0]]);
			else 
				sm_set_error("Error when: ".$k[0]." => ".$_POST[$k[0]]);
			
		}
		//sm_app_redirect($_SERVER['HTTP_REFERER']);
	}
	
}