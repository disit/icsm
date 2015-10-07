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

/**
 * PHP Class to user access (login, register, logout, etc)
 */
define('USERTABLENAME','users');

class sm_User implements sm_Module {

	/*Settings*/
   
   /**
   * The database table that holds all the information
   * var string
   */
  var $dbTable  = USERTABLENAME;
  
  /**
   * The session variable ($_SESSION[$sessionVariable]) which will hold the data while the user is logged on
   * var string
   */
  var $sessionVariable = 'userSessionValue';
  /**
   * Those are the fields that our table uses in order to fetch the needed data. The structure is 'fieldType' => 'fieldName'
   * var array
   */
  var $tbFields = array(
  	'userID'=> 'id', 
  	'login' => 'username',
  	'pass'  => 'password',
  	'email' => 'email',
  	'active'=> 'active'
  );
	/**
   * When user wants the system to remember him/her, how much time to keep the cookie? (seconds)
   * var int
   */
  var $remTime = 2592000;//One month
  /**
   * The name of the cookie which we will use if user wants to be remembered by the system
   * var string
   */
  var $remCookieName = 'ckSavePass';
  /**
   * The cookie domain
   * var string
   */
  var $remCookieDomain = '';
  /**
   * The method used to encrypt the password. It can be sha1, md5 or nothing (no encryption)
   * var string
   */
  var $passMethod = 'md5';
  /**
   * Display errors? Set this to true if you are going to seek for help, or have troubles with the script
   * var bool
   */
  var $displayErrors = true;
  /*Do not edit after this line*/
  var $userID;
  var $dbConn;
  var $userData=array();
 
  function __construct() 
  {
  	//$this->dbTable=SM_DB_PREFIX.$this->dbTable;
  	//parent::__construct();
  	
  	$this->dbConn = sm_Database::getInstance();
  	

	    $this->remCookieDomain = $this->remCookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->remCookieDomain;
	 
	  /*  if( !isset( $_SESSION ) ) 
	    	session_start();*/
	    if ( !empty($_SESSION[$this->sessionVariable]) )
	    {
	    	
		    $this->loadUser( $_SESSION[$this->sessionVariable] );
	    }
	    //Maybe there is a cookie?
	    if ( isset($_COOKIE[$this->remCookieName]) && !$this->is_loaded()){
	      //echo 'I know you<br />';
	      
	      $u = unserialize(base64_decode($_COOKIE[$this->remCookieName]));
	      $this->login($u['uname'], $u['password']);    	
	      	
	    }				  
	    
  }
  
  
  /**
  	* Login function
  	* @param string $uname
  	* @param string $password
  	* @param bool $loadUser
  	* @return bool
  */
  public function login($uname, $password, $remember = false, $loadUser = true)
  {
    	$uname    = $this->escape($uname);
    	$password = $originalPassword = $this->escape($password);
    	
    	//Encrypt password for database
    	$salt = 's+(_a*'; 
		switch(strtolower($this->passMethod)){
		  case 'sha1':
		  	$password = "SHA1('".$password.$salt."')"; break;
		  case 'md5' :
		  	$password = "MD5('".$password.$salt."')";break;
		  case 'nothing':
		  	$password = "'$password'";
		}
		
		$res = $this->query("SELECT * FROM `{$this->dbTable}` 
		WHERE `{$this->tbFields['login']}` = '$uname' AND `{$this->tbFields['pass']}` = $password LIMIT 1",__LINE__);
		/*sm_Logger::write("SELECT * FROM `{$this->dbTable}` 
		WHERE `{$this->tbFields['login']}` = '$uname' AND `{$this->tbFields['pass']}` = $password LIMIT 1");
		*/
		if ( count($res) == 0 || $res[0]['active']==0)
			return false;
		//$cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
		
		session_destroy();
		if ( $loadUser )
		{
			//session_id($cookie);
			session_start();
			$this->userData = $res[0];
			$this->userID = $this->userData[$this->tbFields['userID']];
			$_SESSION[$this->sessionVariable] = $this->userID;
			if ( $remember ){
			  $cookie = base64_encode(serialize(array('uname'=>$uname,'password'=>$originalPassword)));
			  $a = setcookie($this->remCookieName, 
			  $cookie,time()+$this->remTime, '/', $this->remCookieDomain);
			}
		}
		
		return true;
  }
  
  /**
  	* Logout function
  	* param string $redirectTo
  	* @return bool
  */
  public function logout($redirectTo = '')
  {
  	setcookie($this->remCookieName,
  	null,time()-$this->remTime, '/', $this->remCookieDomain);
    $_SESSION[$this->sessionVariable] = '';
    session_regenerate_id();
    
    $this->userData = '';
    if ( $redirectTo != '' && !headers_sent())
    {
	   header('Location: '.$redirectTo );
	   exit;//To ensure security
	}
  }
  /**
  	* Function to determine if a property is true or false
  	* param string $prop
  	* @return bool
  */
  protected function is($prop){
  	return $this->get_property($prop)==1?true:false;
  }
  
    /**
  	* Get a property of a user. You should give here the name of the field that you seek from the user table
  	* @param string $property
  	* @return string
  */
  function get_property($property)
  {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
   
    if (!isset($this->userData[$property])) 
    	$this->error('Unknown property <b>'.$property.'</b>', __LINE__);
    return $this->userData[$property];
  }
  /**
  	* Is the user an active user?
  	* @return bool
  */
  function is_active()
  {
    return $this->userData[$this->tbFields['active']];
  }
  
  /**
   * Is the user loaded?
   * @ return bool
   */
  function is_loaded()
  {
    return empty($this->userID) ? false : true;
  }
  /**
  	* Activates the user account
  	* @return bool
  */
  function activate()
  {
    if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
    if ( $this->is_active()) $this->error('Allready active account', __LINE__);
    $res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
	WHERE `{$this->tbFields['userID']}` = '".$this->escape($this->userID)."' LIMIT 1");
    if (@mysqli_affected_rows($this->dbConn->getLink()) == 1)
	{
		$this->userData[$this->tbFields['active']] = true;
		return true;
	}
	return false;
  }
  /*
   * Creates a user account. The array should have the form 'database field' => 'value'
   * @param array $data
   * return int
   */  
  function insertUser($data){
    if (!is_array($data))
    	 $this->error('Data is not an array', __LINE__);
    //sm_Logger::write($data);
   if(!$this->checkUserData($data))
   	return;
    
    $pass=$data[$this->tbFields['pass']];
    //Encrypt password for database
    $salt = 's+(_a*';
    $pass .= $salt;
    switch(strtolower($this->passMethod)){
	  case 'sha1':
	  	$password = "SHA1('".$pass."')"; break;
	  case 'md5' :
	  	$password = "MD5('".$pass."')";break;
	  case 'nothing':
	  	$password = $data[$this->tbFields['pass']];
	}
    foreach ($data as $k => $v ) $data[$k] = "'".$this->escape($v)."'";
    $data[$this->tbFields['pass']] = $password;
    $this->query("INSERT INTO `{$this->dbTable}` (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")");
    //sm_Logger::write(("INSERT INTO `{$this->dbTable}` (`".implode('`, `', array_keys($data))."`) VALUES (".implode(", ", $data).")"));
    
    $uid = (int)$this->dbConn->getLastInsertedId();
   	sm_EventManager::handle(new sm_Event("InsertUser",$uid));
    return $uid;
  }
  
  
  function updateUser($data)
  {
  	if (!is_array($data))
  		$this->error('Data is not an array', __LINE__);
  	//sm_Logger::write($data);
  	if(!$this->checkUserData($data,false,false))
  		return;
  	
  	$pass=$data[$this->tbFields['pass']];
  	//Encrypt password for database
  	$salt = 's+(_a*';
  	$pass .= $salt;
  	switch(strtolower($this->passMethod)){
  		case 'sha1':
  			$password = sha1($pass); break;
  		case 'md5' :
  			$password = md5($pass);break;
  		case 'nothing':
  			$password = $data[$this->tbFields['pass']];
  	}
  	$userData=array();
  	foreach ($data as $k => $v ) 
  	{
  		if(isset($this->tbFields[$k]))
  			$userData[$k] = $this->escape($v);
  	}
  	
  	$userData[$this->tbFields['pass']] = $password;
  	$uid=$userData['userID'];
  	unset($userData['userID']);
  	if(!$this->dbConn->save($this->dbTable, $userData,array("id"=>$uid)))
  		return false;	
  	sm_EventManager::handle(new sm_Event("UpdateUser",$data));
  	return true;
  	
  }
  
  /*
   * Delete a user account.
  * @param $userID
  * return int
  */
  function removeUser($userID)
  {
  	$sql="DELETE from `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."'";
  	$res = $this->query($sql);
  	if($res)
  		sm_EventManager::handle(new sm_Event("RemoveUser",$userID));
  	return $res;
  }
  
  /*
  * Enable a user account.
  * @param $userID
  * return int
  */
  function enableUser($userID)
  {
  	$res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
	WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
    if (@mysqli_affected_rows($this->dbConn->getLink()) == 1)
	{
		$this->userData[$this->tbFields['active']] = true;
		sm_EventManager::handle(new sm_Event("EnableUser",$userID));
		return true;
	}
	return false;
  }
  
  /*
  * Disable a user account.
  * @param $userID
  * return int
  */
  function disableUser($userID)
  {
  	$res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 0 
	WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
    if (@mysqli_affected_rows($this->dbConn->getLink()) == 1)
	{
		$this->userData[$this->tbFields['active']] = false;
		sm_EventManager::handle(new sm_Event("DisableUser",$this));
		return true;
	}
	return false;
  }
  
  /*
   * Creates a random password. You can use it to create a password or a hash for user activation
   * param int $length
   * param string $chrs
   * return string
   */
  function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
    for($i = 0; $i < $length; $i++) {
        $pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
    }
    return $pwd;
  }
  ////////////////////////////////////////////
  // PRIVATE FUNCTIONS
  ////////////////////////////////////////////
  
  /**
  	* SQL query function
  	* @access private
  	* @param string $sql
  	* @return string
  */
  function query($sql, $line = 'Uknown')
  {
    //if (defined('DEVELOPMENT_MODE') ) echo '<b>Query to execute: </b>'.$sql.'<br /><b>Line: </b>'.$line.'<br />';
	$res = $this->dbConn->query($sql); //mysql_db_query($this->dbName, $sql, $this->dbConn);
	$err = mysqli_error($this->dbConn->getLink());
	if ( $err!="" )
		$this->error($err, $line);
	return $res;
  }
  
  /**
  	* A function that is used to load one user's data
  	* @access private
  	* @param string $userID
  	* @return bool
  */
  function loadUser($userID)
  {
	$res = $this->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$this->escape($userID)."' LIMIT 1");
    if (count($res) == 0 )
    	return false;
    $this->userData = $res[0];
    $this->userID = $userID;
    sm_EventManager::handle(new sm_Event("LoadUser",$this));
   // $_SESSION[$this->sessionVariable] = $this->userID;
    return true;
  }

  /**
  	* Produces the result of addslashes() with more safety
  	* @access private
  	* @param string $str
  	* @return string
  */  
  function escape($str) {
    $str = get_magic_quotes_gpc()?stripslashes($str):$str;
    $str = mysqli_real_escape_string($this->dbConn->getLink(),$str);
    return $str;
  }
  
  /**
  	* Error holder for the class
  	* @access private
  	* @param string $error
  	* @param int $line
  	* @param bool $die
  	* @return bool
  */  
  function error($error, $line = '', $die = false) {
    if ( $this->displayErrors )
    	sm_set_error('<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line));
    //if ($die) exit;
    return false;
  }
  
  /*
   * Check user account data. The array should have the form 'database field' => 'value'
  * @param array $data
  * return bool
  */
  function checkUserData($data,$checkUser=true, $checkMail=true)
  {
  		$res = true;
	  	if(!$data['username'])
	  	{
	  		sm_set_error("<p>Please enter a username.</p>");
	  		$res=false;
	  	}
	  	
	  	elseif(strlen($data['username'])<3 || strlen($data['username'])>15)
	  	{
	  		sm_set_error("<p>Username must be between 3 and 15 characters.</p>");
	  		$res=false;
	  	}
	  	
	  	elseif($checkUser && $this->uniqueUser($data['username']))
	  	{
	  		sm_set_error("Username already taken.");
	  		$res=false;
	  	}
	  	
	  	
	  	if(!$data['password'])
	  	{
	  		sm_set_error("<p>Please enter a password.</p>");
	  		$res=false;
	  	}
	  	
	  	elseif(strlen($data['password'])<5)
	  	{
	  		sm_set_error("<p>Password must be atleast 5 characters.</p>");
	  		$res=false;
	  	}
	  	
	  	if(!$data['email'])
	  	{
	  		sm_set_error("<p>Please enter an email address.</p>");
	  		$res=false;
	  	}
	  	
	  	elseif(sm_validateEmail($_POST['email']))
	  	{
	  		sm_set_error("<p>Invalid email address.</p>");
	  		$res=false;
	  	}
	  	
	  	elseif($checkMail && $this->uniqueEmail($data['email']))
	  	{
	  		sm_set_error("<p>Email taken. Please select another email address.</p>");
	  		$res=false;
	  	}
	  //	var_dump($data);
  		return $res;
  }
  

  //*******************************unique**************************
  //---------checks if record stored in db already exists or not--------
  
  function uniqueUser($user)
  {
  	$user=$this->escape($user);
  	$sql = "SELECT username FROM `{$this->dbTable}` WHERE username = '" . $user ."' ";
  	$res = $this->query($sql);
  	$num = count($res);
  	
  	if ($num > 0)
  		return true;
  	return false;
  }
  
  function uniqueEmail($email)
  {
  	$email=$this->escape($email);
  	$sql = "SELECT COUNT(*) as NUMBER FROM `{$this->dbTable}` WHERE email = '" . $email ."' ";
  	$res = $this->query($sql);
  	$num = $res[0]["NUMBER"];
  
  	if ($num > 0)
  		return true;
  	return false;
  }
  
  //----------Function for checking existence of users----------
  function checkUserInfo($id)
  {
  	$id = $this->escape($id);
  
  	$sql = "SELECT id FROM `{$this->dbTable} WHERE id='".$id."'";
  	$res = $this->query($sql);
  	$rows = count($res);
  
  	if($rows == 0) return TRUE;
  	return FALSE;
  }
   
  
 static public function install($db)
 {
 	/****** ACL Section *******************/
 	sm_Logger::write("Installing Permissions: User::Edit");
 	sm_ACL::installPerm(array('permID'=>null,'permName'=>'User Edit','permKey'=>'User::Edit'));
 	sm_Logger::write("Installing Permissions: User::View");
 	sm_ACL::installPerm(array('permID'=>null,'permName'=>'User View','permKey'=>'User::View'));
 	sm_Logger::write("Installing Permissions: User::Ban");
 	sm_ACL::installPerm(array('permID'=>null,'permName'=>'User Ban','permKey'=>'User::Ban'));
 	
 	sm_Logger::write("Users Permissions Installed");
 	
 	$sql="CREATE TABLE IF NOT EXISTS `".USERTABLENAME."` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`username` varchar(255) NOT NULL,
		`password` varchar(255) NOT NULL,
		`email` varchar(255) NOT NULL,
 		`active` int(1) NOT NULL DEFAULT 0,	
 		`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,			
		PRIMARY KEY  (`id`)
		) 
		ENGINE=MyISAM DEFAULT CHARSET=utf8;";
 
	$sql_admin ="INSERT INTO `users` (`id`, `username`, `password`, `email`, `active`) VALUES
		(1, 'admin', MD5('".$db->escapeValue("admins+(_a*")."'), 'admin@domain.ext', 1);";
	
		$result=$db->query($sql) && $db->query($sql_admin);
		if($result)
		{
			sm_Logger::write("Installed ".USERTABLENAME." table");
			return true;
		}
		sm_Logger::write("Error when installing ".USERTABLENAME." table");
		return false;
		
	}
	
	static public function uninstall($db)
	{
		$sql="DROP TABLE `".USERTABLENAME."`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
  
	public function getAllCount($where=array())
	{
		$clause = !empty($where)?$this->dbConn->buildWhereClause(USERTABLENAME,$where):"";
		$r=$this->query("SELECT COUNT(*) as count from `".USERTABLENAME."` ".$clause);
		return $r[0]['count'];
	}
	
	public function getAll($fields="*",$where=array(),$limit="")
	{
		if(is_array($fields))
			$fields=implode(",",$fields);
		$clause = !empty($where)?$this->dbConn->buildWhereClause(USERTABLENAME,$where):"";
		$r=$this->query("SELECT ".$fields." from `".USERTABLENAME."` ".$clause." order by id DESC ".$limit);
		return $r;
	}
	
	static function current()
	{
		$user = new sm_User();
		//if(isset($_SESSION[$user->sessionVariable]))
		{
			//$user->loadUser($_SESSION[$user->sessionVariable]);
			return $user;
		}
		
	}
  
}
?>