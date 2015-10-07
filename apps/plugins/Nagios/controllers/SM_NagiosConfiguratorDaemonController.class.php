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

class SM_NagiosConfiguratorDaemonController extends sm_ControllerElement
{

	/**
	 * @desc Execute a Nagios Configurator Daemon Command
	 *
	 * @url GET nagios/configurator/daemon/command/:command
	 * @url GET nagios/configurator/daemon/command/:command/:value
	 * 
	 * @callback
	 * 
	 * @access
	 * 
	 */
	function nagios_configurator_daemon_command($command=null,$value=null)
	{
		$data['result']=SM_NagiosConfiguratorDaemon::command($command,$value);
		$this->view=new SM_NagiosConfiguratorDaemonView($data);
		$this->view->setOp("command");
	}

	/**
	 * @desc Gets the Nagios Configurator Daemon page
	 *
	 * @url GET nagios/configurator/daemon
	 *
	 * @access
	 */
	function nagios_configurator_daemon()
	{
		$file="/var/log/SM_NagiosConfigurator.log";
		$data=array();
		$data['file']="SM_NagiosConfigurator.log";
		$data['refreshUrl']="nagios/configurator/daemon/refresh/log/?file=".$file;
		$data['title']="Nagios Configurator";
		$data['queue']=SM_NagiosConfiguratorDaemon::queue();
		$data['performance']=SM_NagiosConfiguratorDaemon::getPerformance();
		$data['settings']=SM_NagiosConfiguratorDaemon::getSettings();
		$this->view=new SM_NagiosConfiguratorDaemonView($data);
		$this->view->setOp("view");
	}
	
	
	/**
	 * @desc Gets the Nagios Configurator Daemon page refresh
	 *
	 * @url GET nagios/configurator/daemon/refresh/log
	 *
	 * @callback
	 *
	 */
	function nagios_configurator_daemon_refresh()
	{
		$file=$_GET['file'];
		$tail = new sm_Tail();
		$data=$tail->refresh($file);
		$this->view=new sm_TailView($data);
		$this->view->setOp("refresh");
	}
	
	/**
	 * @desc Gets the Nagios Configurator Daemon page refresh
	 *
	 * @url GET nagios/configurator/daemon/refresh/plot
	 *
	 * @callback
	 *
	 */
	function nagios_configurator_daemon_plot_refresh()
	{
		$data=SM_NagiosConfiguratorDaemon::getPerformance();
		$this->view=new SM_NagiosConfiguratorDaemonView($data);
		$this->view->setOp("refresh::plot");
	}
	
	/**
	 * @desc Gets the Nagios Configurator Daemon queue refresh
	 *
	 * @url GET nagios/configurator/daemon/refresh/queue
	 *
	 * @callback
	 *
	 */
	function nagios_configurator_daemon_queue_refresh()
	{
		$data['queue']=SM_NagiosConfiguratorDaemon::queue();
		$this->view=new SM_NagiosConfiguratorDaemonView($data);
		$this->view->setOp("refresh::queue");
	}
	
	/**
	 * @desc Set the Nagios Configurator Daemon settings 
	 *
	 * @url POST nagios/configurator/daemon/settings
	 *
	 * @callback
	 *
	 */
	function nagios_configurator_daemon_settings_submit($data)
	{
		/*$data=SM_NagiosConfiguratorDaemon::getPerformance();
		$this->view=new SM_NagiosConfiguratorDaemonView($data);
		$this->view->setOp("refresh::plot");*/
		
		$response['result']="Nagios Daemon Configurator: ";
		$response['result'].=SM_NagiosConfiguratorDaemon::saveSettings($data)?"Settings save successfully!":"Error when saving settings!";
		$this->view=new SM_NagiosConfiguratorDaemonView($response);
		$this->view->setOp("settings");
	}

}