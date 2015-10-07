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

class sm_NotificationView extends sm_ViewElement
{
	protected $titles=array('alert'=>"Alerts",'message'=>"Messages",'mail'=>"Mails",'comment'=>"Comments");
	protected $icons=array('alert'=>"bell",'message'=>"envelope",'mail'=>"envelope",'comment'=>"pencil");
	
	function __construct($data=null)
	{
		parent::__construct($data);
		/*if(!isset($this->model['widget_type']))
			$this->model['widget_type']="bar";*/
	}

	/**
	 * Create the HTML code for the module.
	 * Then the tpl_id set in $this->getTemplateId() will be added to the main template automatically
	 */
	public function build() {

		
		switch($this->op)
		{
			case 'page':
				$this->notification_page();
				break;
			case 'view':
				$this->uiView=new sm_JSON();
				$this->uiView->insert($this->model);
				break;
			case 'update':
				$this->notification_bar();
				break;
			case 'response':
				$this->uiView=new sm_JSON();
				$this->uiView->insert($this->model);
			break;
		}
	}
	
	function notification_page(){
	
		$_models = $this->getModel();
		$notifications=$_models['notifications'];
	
		$table = new sm_TableDataView("Notifications",$_models);
		$table->setSortable();
		$commands=null;
		if(sm_ACL::checkPermission("Notification::Edit"))
		{
			$commands=array();
			$commands['DeleteTBWSelectedNotification']=array('name'=>'DeleteTBWSelectedNotification','title'=>'Delete Selection',"icon"=>"glyphicon glyphicon-trash");
		}
		$table->setSeletectedCmd($commands);
		$header=array();
		$table->addHRow();
		foreach ($notifications as $k=>$value)
		{
			if(sm_ACL::checkPermission("Notification::Edit"))
				$value['actions'][]=array("id"=>"notify-delete-".$value['id'],"title"=>"Delete Message","url"=>'notification/remove/'.$value['id'],"data"=>"<i class=notify-delete-icon></i>","method"=>"POST");
			$table->addRow("",array("id"=>$value['id']));
			unset($value['id']);
			unset($value['timestamp']);
			
			$data=array();
			unset($value['type']);
			unset($value['to_user']);
			unset($value['seen']);
			foreach ($value as $l=>$v)
			{
				if($k==0)
				{
					if($l=="from_user")
						$header[]['header']=ucfirst(str_replace("_"," ","from"));
					else 
						$header[]['header']=ucfirst(str_replace("_"," ",$l));
						
					if($l=='actions')
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)),"sorter-false");
					else
						$table->addHeaderCell(ucfirst(str_replace("_"," ",$l)));
	
				}
				if($l=="seen")
					$v=$v>0?'<span class="label label-success">Yes</span>':'<span class="label label-danger">No</span>';
				if($l=='actions' && is_array($v))
				{
					$this->setTemplateId("actions_forms","ui.tpl.html");
					$this->tpl->addTemplateDataRepeat("actions_forms", 'action_form', $v);
					$v=$this->tpl->getTemplate("actions_forms");
						
				}
				else if($l=="from_user" || $l=="to_user")
				{
					$user = new sm_User();
					$user->loadUser($v);
					$v=$user->userData['username'];
				}
				else if($l=="type")
				{
					$v="<i class='notify-".$v."-icon' title='".ucfirst($v)."'></i>";
				}
				$data[]['data']=$v;
				$table->addCell($v);
	
			}
		}
		$filter['from']=array("Date","Filter from", "from",array('class'=>'input-sm','value'=>$this->model["from"],'placeholder'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")",'title'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")"));
		$filter['to']=array("Date","to", "to",array('class'=>'input-sm','value'=>$this->model['to'],'placeholder'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")",'title'=>"DD-MM-YYYY (e.g. " . date("d-m-Y") . ")"));
		$table->addFilter($filter);
		$panel = new sm_Panel();
		$panel->setTitle($this->titles[$_models['type']]);
		$panel->icon(sm_formatIcon(($this->icons[$_models['type']])));
		$panel->insert($table);
		$this->uiView = new sm_Page("Notifications");
		$this->uiView->setTitle("Notifications");
		$this->uiView->insert($panel);
	//	$this->addView();
	}
	
	/**
	 *
	 * @param sm_Event $event
	 */
	public function onFormAlter(sm_Event &$event)
	{
	
		$form = $event->getData();
		if(is_object($form) && is_a($form,"sm_Form") && $form->getName()=="Notifications")
		{
			$form->setSubmitMethod("notificationPageFormSubmit",$this);
		}
	
	}
	
	public function notificationPageFormSubmit($data)
	{
		if(!isset($_SESSION['notification/page']))
			$_SESSION['notification/page']=array();
	
		if(isset($data['from']))
		{
			$data['from']=strtotime($data['from']);
			if(isset($_SESSION['notification/page']['from']) && $_SESSION['notification/page']['from']!=$data['from'])
				$_SESSION['notification/page']['from']=$data['from'];
			else
				$_SESSION['notification/page']['from']=$data['from'];
				
	
		}
		if(isset($data['to']))
		{
			$data['to']=strtotime($data['to']);
			if(isset($_SESSION['notification/page']['to']) && $_SESSION['notification/page']['to']!=$data['to'])
				$_SESSION['notification/page']['to']=$data['to'];
			else
				$_SESSION['notification/page']['to']=$data['to'];
	
	
		}
	}
	
	function notification_bar()	{
		$notifications=$this->model['notifications'];
		$html=null;
		$data=null;
		$tplPath = sm_NotificationPlugin::instance()->getFolder("templates")."notification.tpl.html";
		$bar=new sm_HTML();
		if ($notifications) 
		{
			$unseen_ids=array();
			//$html = $this->model['newcount'] . "|";
			$data['newcount']=$this->model['newcount'];
			$unseen_ids = array();
			foreach ($notifications as $i=>$object) {
				if ($object['seen'] == 0) 
					$unseen_ids[] = $object['id'];
				$object['iconBaseUrl']=sm_NotificationPlugin::instance()->getFolderUrl("img");
				$from = new sm_User();
				$from->loadUser($object['from_user']);
				$object['from_user_name']=$from->userData['username'];
				switch($object['type'])
				{
					default:
					 	$object['iconFile']="alert.png";
					break;  
		        	case "message":  
		      		 	$object['iconFile']="message.png";
		             break;  
		                     //TODO: add cases for other notifications  
		     	}
		     	$object['class']="notification_msg";
		     	if ($object['seen'] == 0)
		     		$object['class']="notification_msg notification_unseen_msg";
		     	$html = new sm_HTML();
		     	$html->setTemplateId("notification_item",$tplPath);
		     	$html->insertArray(
		     			$object
		     	);
		     	$bar->insert($i,$html);
		     	
		     		
		   }  
		   $data['html']=$bar->render();
		   $data['unseen_ids']=$unseen_ids;
		  
		  
		 }
		 $this->uiView = new sm_JSON();
		 $this->uiView->insert($data);

	}
	
	
	
	static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("Notifications", "#","warning-sign");
		$menu->setSubLink("Notifications", "Alerts","notification/page");
		//$menu->setSubLink("Notifications", "Messages","notification/messages");
	}
	
}