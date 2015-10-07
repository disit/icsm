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

class sm_Mail implements sm_Module
{
	static $logArray = array();
	
	public static function install($db)
	{
		sm_Config::set("AUTHENTICATED_SMTP_SERVER",array("value"=>"smtp.domain.ext","description"=>"SMTP server url"));
		sm_Config::set("AUTHENTICATED_SMTP_PORT",array("value"=>25,"description"=>"SMTP Server Port"));//"25";
		sm_Config::set("AUTHENTICATED_SMTP_TIMEOUT",array("value"=>30,"description"=>"SMTP Server Timeout"));//"30";
		sm_Config::set("AUTHENTICATED_SMTP_USER",array("value"=>"smtpuser","description"=>"SMTP Server User"));
		sm_Config::set("AUTHENTICATED_SMTP_PASSWORD",array("value"=>"smtppassword","description"=>"SMTP Server Password"));
		sm_Config::set("AUTHENTICATED_SMTP_LOCALHOST",array("value"=>"localhost","description"=>"SMTP Server localhost"));//"localhost";
		sm_Config::set("TEXT_SIGNATURE_EMAIL",array("value"=>"@SITE_NAME","description"=>"Mail signature"));//"localhost";
		sm_Config::set("WEB_EMAIL",array("value"=>"user@domain.it","description"=>"Mail sender"));//"localhost";
		
	}
	
	public static function uninstall($db)
	{
		sm_Config::delete("AUTHENTICATED_SMTP_SERVER");
		sm_Config::delete("AUTHENTICATED_SMTP_PORT");//"25";
		sm_Config::delete("AUTHENTICATED_SMTP_TIMEOUT");//"30";
		sm_Config::delete("AUTHENTICATED_SMTP_USER");
		sm_Config::delete("AUTHENTICATED_SMTP_PASSWORD");
		sm_Config::delete("AUTHENTICATED_SMTP_LOCALHOST");
		sm_Config::delete("TEXT_SIGNATURE_EMAIL");
		sm_Config::delete("WEB_EMAIL");
	}
		
	static public function send_mail($from=null,$to,$message,$subject,$cc='',$contentType=null)
	{
		if(!$from)
			$from = sm_Config::get("WEB_EMAIL",null);
		$nameFrom=sm_Config::get('SITE_TITLE',"");
		$conf['smtpServer']=sm_Config::get("AUTHENTICATED_SMTP_SERVER","smtp.domain.it"); 
		$conf['smtpPort']=sm_Config::get("AUTHENTICATED_SMTP_PORT",25);//"25";
		$conf['smtpTimeout']=sm_Config::get("AUTHENTICATED_SMTP_TIMEOUT",30);//"30";
		$conf['localhost']=sm_Config::get("AUTHENTICATED_SMTP_LOCALHOST","localhost");//"localhost";
		if(sm_Config::get("AUTHENTICATED_SMTP",false))
		{
			$conf['smtpUser']=sm_Config::get("AUTHENTICATED_SMTP_USER","smtpuser");
			$conf['smtpPassword']=sm_Config::get("AUTHENTICATED_SMTP_PASSWORD","smtppassword");	
		}
		if($contentType)
			$conf['content-type']=$contentType;
		return self::SendEmail($from, $nameFrom,$to, "",$subject, utf8_decode($message),$conf);
	
	}	
	
	static function SendEmail($from, $namefrom, $to, $nameto, $subject, $message, $conf)
	{
		//SMTP + SERVER DETAILS
		/* * * * CONFIGURATION START * * * */
		
		$smtpServer = $conf['smtpServer'];
		$port = $conf['smtpPort'];//"25";
		$timeout = $conf['smtpTimeout'];//"30";
		$username = isset($conf['smtpUser'])?$conf['smtpUser']:null;
		$password = isset($conf['smtpPassword'])?$conf['smtpPassword']:null;
		$localhost = isset($conf['localhost'])?$conf['localhost']:"";//"localhost";
		$type='text/plain';
		if(isset($conf['content-type']) )
			$type=$conf['content-type'];
		
		/* * * * CONFIGURATION END * * * * */
		$newLine = "\r\n";
		//Connect to the host on the specified port
		$smtpConnect = fsockopen($smtpServer, $port, $errno, $errstr, $timeout);
		
		if(empty($smtpConnect))
		{
			$output = "Failed to connect: $smtpServer" ;
			self::$logArray['connection']=$output;
			return false; //$output;
		}
		else
		{
			$smtpResponse = fgets($smtpConnect, 515);
			self::$logArray['connection'] = "Connected: $smtpResponse";
		}
	
		//Request Auth Login
		if($username && $password){
			fputs($smtpConnect,"AUTH LOGIN" . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			self::$logArray['authrequest'] = "$smtpResponse";
		
			//Send username
			fputs($smtpConnect, base64_encode($username) . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			self::$logArray['authusername'] = "$smtpResponse";
	
			//Send password
			fputs($smtpConnect, base64_encode($password) . $newLine);
			$smtpResponse = fgets($smtpConnect, 515);
			self::$logArray['authpassword'] = "$smtpResponse";
		}
		//Say Hello to SMTP
		fputs($smtpConnect, "HELO $localhost" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['heloresponse'] = "$smtpResponse";
	
		//Email From
		fputs($smtpConnect, "MAIL FROM: $from" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['mailfromresponse'] = "$smtpResponse";
	
		//Email To
		fputs($smtpConnect, "RCPT TO: $to" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['mailtoresponse'] = "$smtpResponse";
	
		//The Email
		fputs($smtpConnect, "DATA" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['data1response'] = "$smtpResponse";
		date_default_timezone_set(ini_get('date.timezone'));
		//Construct Headers
		$headers = "From: $namefrom <$from>" . $newLine;
		$headers.= "To: $nameto <$to>" . $newLine;
		$headers.= "Date: ".date('r')."\r\n";
		$headers.= 'Reply-To: '.$from . "\r\n";
		$headers.= 'Return-Path: '.$from . "\r\n";
		$headers.= "MIME-Version: 1.0" . $newLine;
		$headers.= "Content-type: ".$type."; charset=utf-8" . $newLine;
		//$headers .= "Content-transfer-Encoding: 8bit" . $newLine;
		
		$headers.= 'X-Mailer: PHP ' . phpversion(). "\r\n";
		
			
	
		fputs($smtpConnect, "Subject: $subject\n$headers\n\n$message\n.\n");
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['data2response'] = "$smtpResponse";
	
		// Say Bye to SMTP
		fputs($smtpConnect,"QUIT" . $newLine);
		$smtpResponse = fgets($smtpConnect, 515);
		self::$logArray['quitresponse'] = "$smtpResponse";
		return true;
	}
	
	static function getMailErrors()
	{
		return self::$logArray;
	}
}