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

define("NOTIFICATIONTABLE","notification");
define("NOTIFICATIONTABLEUSERSPREFERENCES","notification_users_preferences");
define("NOTIFICATIONDAILY",24*3600);
define("NOTIFICATIONBIDAILY",24*3600*2);
define("NOTIFICATIONWEEKLY",24*3600*7);

class sm_Notification implements sm_Module
{
	public $type;
	public $to_user;
	public $from_user;
	public $message;
	public $timestamp;
	public $newcount;
	public $contentType;
	public $timemap=array("dayly"=>LOGDAILY,"bi-daily"=>LOGBIDAILY,"weekly"=>LOGWEEKLY);
	public $subject;
	protected $db;
	protected $parent;
	protected $renderer;
	function __construct($parent=null)
	{
		$this->contentType=null;
		$this->parent=$parent;
		$this->db=sm_Database::getInstance();
		$this->renderer=null;
		$this->subject="No subject";
	}
	
	public function setParentModule($parent=null){
		$this->parent=$parent;
	}
	
	public function setRenderer($renderer)
	{
		$this->renderer=$renderer;
	}
	
	public function getParentModule(){
		return $this->parent;
	}
	
	public function getAllCount($where=null)
	{
		$where=$this->db->buildWhereClause(NOTIFICATIONTABLE,$where);
		$query = "SELECT COUNT(*) as count from `".NOTIFICATIONTABLE."` ".$where;
		
		$r=$this->db->query($query);
		return $r[0]['count'];
	}
	
	public function getAll($limit,$where=null)
	{
		$where=$this->db->buildWhereClause(NOTIFICATIONTABLE,$where);
		$query="SELECT *, timestamp as time from `".NOTIFICATIONTABLE."` ".$where." order by timestamp DESC ".$limit;
		
		$r=$this->db->query($query);
		return $r;
	}
	
	public function getAllUserNotifications(sm_User $user) {
		$limit=sm_Config::get("NOTIFICATIONMESSAGES",1);
		if(isset($user->userData['notification']))
			$limit=$user->userData['notification']['preferences']['NOTIFICATIONMESSAGES'];
		$this->newcount = $this->newCount($user);
		$sql = "SELECT n.*, u.username FROM ".NOTIFICATIONTABLE." n INNER JOIN users u ON u.id = n.to_user where n.to_user = {$user->userID} ORDER BY `timestamp` DESC LIMIT ".$limit;
		$result = $this->db->query($sql);
		if ($result) {
			return $result;
		}
		return false; //none found
	}
	
	function handle(){	
		$this->clean();
		sm_EventManager::handle(new sm_Event("NotificationAddEvent",$this));
		$ret = false;
		if($this->add())
		{
			sm_EventManager::handle(new sm_Event("NotificationSendMailEvent",$this));
			$ret = $this->mail();
		}
		return $ret;
	}
	
	public function clean(){
	/*	$t=time();
		$s=strtotime(date('Y-m-d 00:00:00',$t)); //Mezzanotte del giorno corrente 00:00
		$rtime=sm_Config::get("LOGGERROTATIONTIME",'weekly');
		$limit_time = $this->$timemap[$rtime];*/
	}
	
	public function add() {
		$sql = "INSERT INTO ".NOTIFICATIONTABLE." (to_user,from_user,message,type,subject) VALUES ({$this->to_user},{$this->from_user},'{$this->message}','{$this->type}','{$this->subject}')";
		return $this->db->query($sql);
	}
	
	public function saveUserPreferences($uid,$preferences=null) {
		$where=null;
		if($this->existUserPreferences($uid))
			$where['uid']=$uid;
		else
			$preferences['uid']=$uid; 
			
		return $this->db->save(NOTIFICATIONTABLEUSERSPREFERENCES,$preferences,$where);
	}
	
	public function existUserPreferences($uid)
	{
		$where['uid']=$uid;
		$result=$this->db->select(NOTIFICATIONTABLEUSERSPREFERENCES,$where,array('uid'));
		return isset($result[0])&&$result[0]['uid']==$uid;
	}
	
	public function sendMail2User(sm_User $user){
		$preferences = $this->loadUserNotificationPreferences($user->userData['id']);
		if(isset($preferences[0]) && $preferences[0]['NOTIFICATIONSENDMAIL'])
		{
			return $this->sendMail($user->userData['email']);
		}
	}
	
	public function sendMail($mailAddress){
		
			
			if(is_object($this->renderer))
			{
				if(method_exists($this->renderer, "mailRender"))
					$this->renderer->mailRender($this);
				else if(is_callable($this->renderer))
					call_user_func_array($this->renderer,array(&$this));
				else 
					sm_Logger::error("Mail Render function not found in ".get_class($this->renderer));
			}
			$signature="\r\n\r\n".str_replace("@SITE_NAME",sm_Config::get('SITE_TITLE',"") ,sm_Config::get("TEXT_SIGNATURE_EMAIL","@SITE_NAME"));
			if($this->contentType!="text/plain")
				$this->message.=nl2br($signature);
			else 
				$this->message.=$signature;
			$subject=$this->subject?$this->subject: $this->type;
			if(!sm_Mail::send_mail(null, $mailAddress, $this->message, $subject,'',$this->contentType))
			{
				sm_Logger::error("Error when sending mail to ".$mailAddress);
				return false;
			}
			return true;
	}
	
	public function mail(){
		$ret = true;
		if($this->to_user>0)
		{
			$user = new sm_User();
			$user->loadUser($this->to_user);
			$preferences = $user->userData['notification']['preferences'];//$this->loadUserNotificationPreferences($user->userData['id']);
			if(isset($preferences['NOTIFICATIONSENDMAIL']) && $preferences['NOTIFICATIONSENDMAIL'])
			{
				$ret &= $this->sendMail($user->userData['email']);
			}
		}
		if(sm_Config::get("NOTIFICATIONSENDMAIL",false))
		{
			$responsible =sm_Config::get("NOTIFICATIONEMAIL",null);
			if($responsible)
				$ret &= $this->sendMail($responsible);
		}
		return $ret;
	}
	
	static function seen($id, sm_User $user)
	{	
		$sql = "UPDATE ".NOTIFICATIONTABLE." SET seen = 1 WHERE id = {$id} AND to_user = {$user->userID}";
		sm_Database::getInstance()->query($sql);
	}
	
	static function newCount(sm_User $user) {
		if(isset($user) && $user->userID>0)
		{
			$sqlcnt = "SELECT count(*) FROM ".NOTIFICATIONTABLE." WHERE to_user = {$user->userID} AND seen = 0";
			$result = sm_Database::getInstance()->query($sqlcnt);
			if($result)
				return $result[0]['count(*)'];
		}
		
		return 0;
	}
	
	static function loadNotification($id) {
		$sql = "SELECT * FROM ".NOTIFICATIONTABLE." WHERE id = {$id} ";
		return sm_Database::getInstance()->query($sql);
	}
	
	static function loadUserNotificationPreferences($uid) {
		$sql = "SELECT * FROM ".NOTIFICATIONTABLEUSERSPREFERENCES." WHERE uid = {$uid} ";
		return sm_Database::getInstance()->query($sql);
	}
	
	static function deleteNotification($id) {
		$sql = "DELETE FROM ".NOTIFICATIONTABLE." WHERE id = {$id} ";
		return sm_Database::getInstance()->query($sql);
	}
	
	static function deleteUserNotification($uid) {
		$sql = "DELETE FROM ".NOTIFICATIONTABLE." WHERE to_user = {$uid} ";
		return sm_Database::getInstance()->query($sql);
	}
	
	static function deleteUserNotificationPreferences($uid) {
		$sql = "DELETE FROM ".NOTIFICATIONTABLEUSERSPREFERENCES." WHERE uid = {$uid} ";
		return sm_Database::getInstance()->query($sql);
	}
	
	static function install($db)
	{
		/****** ACL Section *******************/
		sm_Logger::write("Installing Permissions: Notification::Edit");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Notification Edit','permKey'=>'Notification::Edit'));
		sm_Logger::write("Installing Permissions: Notification::View");
		sm_ACL::installPerm(array('permID'=>null,'permName'=>'Notification View','permKey'=>'Notification::View'));
		sm_Logger::write("Permissions Installed");
		
		$res = false;
		
		$sql="CREATE TABLE IF NOT EXISTS `".NOTIFICATIONTABLE."` (
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`from_user` int(10) NOT NULL DEFAULT '0',
		`to_user` int(10) NOT NULL DEFAULT '0',
		`subject`  text NOT NULL,
		`message`  text NOT NULL,
		`type` enum('alert','message','mail','comment') NOT NULL,
		`seen` tinyint(4) NOT NULL DEFAULT '0',
		`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		KEY `to_user` (`to_user`),
		KEY `from_user` (`from_user`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".NOTIFICATIONTABLE." table");
			sm_set_message("'".NOTIFICATIONTABLE."' table installed successfully");
			$res = true;
		}
		else
		{
			sm_Logger::write("Not Installed ".NOTIFICATIONTABLE." table");
			sm_set_error("'".NOTIFICATIONTABLE."' table installation error");
		}
		
		
		sm_Config::set("NOTIFICATIONMESSAGES",array('value'=>5,"description"=>"Set the max number of messages in the alerts bar"));
		sm_set_message("Max number of message in the alerts bar successfully saved!");

		sm_Config::set("NOTIFICATIONSENDMAIL",array('value'=>1,"description"=>"Send mail on alert to specified address"));
		sm_set_message("Send mail on alert to specified address successfully saved!");
	
		sm_Config::set("NOTIFICATIONEMAIL",array('value'=>"","description"=>"E-mail where send alert"));
		sm_set_message("E-mail where send alert successfully saved!");
		
		
		$sql="CREATE TABLE IF NOT EXISTS `".NOTIFICATIONTABLEUSERSPREFERENCES."` (
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`uid` int(10) NOT NULL DEFAULT '0',
		`NOTIFICATIONMESSAGES` int(10) NOT NULL DEFAULT '10',
		`NOTIFICATIONSENDMAIL`  int(1) NOT NULL DEFAULT '0',
		`NOTIFICATIONCLEAN` enum('daily','weekly','monthly','always') NOT NULL DEFAULT 'always',
		`NOTIFICATIONFILTER` text NOT NULL,		
		PRIMARY KEY (`id`),
		KEY `uid` (`uid`)
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed ".NOTIFICATIONTABLEUSERSPREFERENCES." table");
			sm_set_message("'".NOTIFICATIONTABLEUSERSPREFERENCES."' table installed successfully");
			$res = true;
		}
		else 
		{
			sm_Logger::write("Not Installed ".NOTIFICATIONTABLEUSERSPREFERENCES." table");
			sm_set_error("'".NOTIFICATIONTABLEUSERSPREFERENCES."' table installation error");
		}		
		return $res;
		
	}
	
	static public function uninstall($db)
	{
		
		$sql="DROP TABLE `".NOTIFICATIONTABLE."`;";
		$result=$db->query($sql);
		if($result)
			sm_set_message("'".NOTIFICATIONTABLE."' table uninstalled successfully");
		else 
			sm_set_error("'".NOTIFICATIONTABLE."' table does not uninstalled properly!");
		$sql="DROP TABLE `".NOTIFICATIONTABLEUSERSPREFERENCES."`;";
		$result=$db->query($sql);
		if($result)
			sm_set_message("'".NOTIFICATIONTABLEUSERSPREFERENCES."' table uninstalled successfully");
		else 
			sm_set_error("'".NOTIFICATIONTABLEUSERSPREFERENCES."' table does not uninstalled properly!");
		
		sm_Config::delete("NOTIFICATIONMESSAGES");
		sm_set_message("Max number of message in the alerts bar successfully removed!");
		
		sm_Config::delete("NOTIFICATIONSENDMAIL");
		sm_set_message("Send mail on alert to specified address successfully removed!");
		
		sm_Config::delete("NOTIFICATIONEMAIL");
		sm_set_message("E-mail where send alert successfully removed!");
		
		if($result)
			return true;
		return false;
	}
	
}