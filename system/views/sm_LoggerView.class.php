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

class sm_LoggerView extends sm_ViewElement
{

	function __construct($data=NULL)
	{
		parent::__construct($data);
		
	}

	/**
	 * Render the HTML code for the module.
	 * 
	 */
	public function build() {
		$table = new sm_TableDataView("LoggerTable",$this->model);
		$table->addHRow();
		$table->addHeaderCell("Time");
		$table->addHeaderCell("Class");
		$table->addHeaderCell("Method");
		$table->addHeaderCell("Message");
		foreach($this->model['records'] as $i=>$records)
		{
			$table->addRow("",array("id"=>$records['id']));
			unset($records['id']);
			foreach($records as $k=>$r)
				$table->addCell($r);
		}
		
		$table->setSortable();
		$filter['timestamp']=array("Date","Filter from", "timestamp",array('class'=>'input-sm','value'=>$this->model['timestamp']));
		$filter['timestamp_to']=array("Date","to", "timestamp_to",array('class'=>'input-sm','value'=>$this->model['timestamp_to']));
		$table->addFilter($filter);
		$panel = new sm_Panel();
		$panel->setTitle("Output Log");
		$panel->icon("<img src='./img/log.png' />");
	
		$panel->insert($table);
	
		$this->uiView=new sm_Page("LoggerView");
		$this->uiView->setTitle("Journal");
		$this->uiView->insert($panel);
		//$this->addView();
		
	}
	/**
	 * 
	 * @param sm_Menu $menu
	 */
	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("Journal","#","book");
		$menu->setSubLink("Journal", "Output Log","log/output");
		//$menu->setSubLink("Journal", "Error","log/error");
	}
	
	/**
	 * 
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
		
			$form = $event->getData();
			if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="LoggerTable")
			{
				$form->setSubmitMethod("loggerTableFormSubmit");
			}
	}
	
	public function loggerTableFormSubmit($data)
	{
		if(!isset($_SESSION['log/output']))
			$_SESSION['log/output']=array();
		if(isset($data['timestamp']))
		{
			if(isset($_SESSION['log/output']['timestamp']) && $_SESSION['log/output']['timestamp']!=$data['timestamp'])
				$_SESSION['log/output']['timestamp']=$data['timestamp'];
			else
				$_SESSION['log/output']['timestamp']=$data['timestamp'];
			
		}
		if(isset($data['timestamp_to']))
		{
			if(isset($_SESSION['log/output']['timestamp_to']) && $_SESSION['log/output']['timestamp_to']!=$data['timestamp_to'])
				$_SESSION['log/output']['timestamp_to']=$data['timestamp_to'];
			else
				$_SESSION['log/output']['timestamp_to']=$data['timestamp_to'];
				
		}
	}
}
