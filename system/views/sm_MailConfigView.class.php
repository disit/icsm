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

class sm_MailConfigView extends sm_SystemConfigView
{
	
	function onExtendView(sm_Event &$event)
	{
		$obj = $event->getData();
		if(is_object($obj) && is_a($obj,"sm_SystemConfigView"))
		{
			$this->extendSystemConfigView($obj);
		}
	}
	
	public function extendSystemConfigView(sm_Widget $obj)
	{
		$userUIView = $obj->getUIView();
		if(is_a($userUIView,"sm_Page"))
		{
			$menu = $userUIView->getMenu();
			$menu->insert("mail",array("url"=>"config/mail","title"=>'Mail',"icon"=>"sm-icon sm-icon-mail"));
		}
	}
	
	
	function build()
	{
		parent::build();
	}
	
	function mail_config_form($form){
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				//"view" => new View_Grid(),
				//"labelToPlaceholder" => 1,
				"action"=>"config/mail",
				"id"=>"mail-form"
		));
		$form->addElement(new Element_HTML('<div class="cView_panel" id="MailConfig">'));
		$form->addElement(new Element_HTML('<legend>Mail</legend>'));
					
			
			$form->addElement(new Element_Textbox('Web site e-mail address','WEB_EMAIL',array('value'=>sm_Config::get('WEB_EMAIL',"user@domain.it"),'longdesc'=>'E-mail address of the web site administrator')));
			
			$form->addElement(new Element_Textarea('Mail signature',"TEXT_SIGNATURE_EMAIL", array('value'=>sm_Config::get('TEXT_SIGNATURE_EMAIL',"@SITE_NAME"),'longdesc'=>'Mail signature. @SITE_NAME will be automatically replaced by site name.')));
			
			$options[0]="Disabled";
			$options[1]="Enabled";
			$form->addElement(new Element_Select('Enable Authenticated SMTP Mail server','AUTHENTICATED_SMTP',$options,array('value'=>array(sm_Config::get('AUTHENTICATED_SMTP',1)),'longdesc'=>'Enable/Disable authenticated E-mail')));
			
			$form->addElement(new Element_Textbox('SMTP Server','AUTHENTICATED_SMTP_SERVER',array('value'=>sm_Config::get('AUTHENTICATED_SMTP_SERVER',"smtp.domain.it"),'longdesc'=>'SMPT server address for authenticated mail')));
			
			$form->addElement(new Element_Textbox('SMTP Server Port','AUTHENTICATED_SMTP_PORT',array('value'=>sm_Config::get('AUTHENTICATED_SMTP_PORT',"25"),'longdesc'=>'SMPT server port')));
			
			$form->addElement(new Element_Textbox('SMTP Server User','AUTHENTICATED_SMTP_USER',array('value'=>sm_Config::get('AUTHENTICATED_SMTP_USER',"smpt-user"),'longdesc'=>'SMPT user login')));
			
			$form->addElement(new Element_Textbox('SMTP Server Password','AUTHENTICATED_SMTP_PASSWORD',array('value'=>sm_Config::get('AUTHENTICATED_SMTP_PASSWORD',"smpt-password"),'longdesc'=>'SMTP user password')));
			
		
		$form->addElement(new Element_Button("Save","",array("name"=>"Save",'class'=>"button light-gray btn btn-primary")));
		$form->addElement(new Element_HTML('</div>'));
		$form->setSubmitMethod("mail_config_form_submit");
	
	}
	
	function mail_config_form_submit($data)
	{
	
		unset($data['form']);
		foreach ($data as $key=>$v)
		{
			sm_Config::set($key,array('value'=>$v));
			sm_set_message("Mail: ".$key." successfully saved!");
		}
	}
	
	
	
	/*static public function menu(sm_MenuManager $menu)
	{
		
			$items['configuration/mail']=array(
					'title'=>t('Mail'),
					'path'=>'configuration/mail',
					'callback'=>'mail_configure',
					'parent path'=>'configuration',
					'weight'=>81,
					'access'=>'admin',
					'type'=>'MENU',
			);
		
	}*/
	
}

