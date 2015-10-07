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

define("LOGDAILY",24*3600);
define("LOGBIDAILY",24*3600*2);
define("LOGWEEKLY",24*3600*7);

class sm_Logger implements sm_Module
{
	public static $debug=false;
	public static $file=true;
	public static $usedb=true;
	public static $fileLog="SM_output.log";
	public static $errorLog="SM_error.log";
	public static $logfolder="logs/";
	public static $instance=null;
	public static $timemap=array("dayly"=>LOGDAILY,"bi-daily"=>LOGBIDAILY,"weekly"=>LOGWEEKLY);
	protected $db=null;
	
	public function __construct()
	{
		$this->db=sm_Database::getInstance();
		
	}
	
	static function write($data,$class=null,$method=null)
	{
		$t=time();
		
		$d = debug_backtrace();
		$caller="";
		if(!isset($class) && isset($d[1]['class']))
		{
			$caller=$d[1]['class']."::".$d[1]['function'];
			$class = $d[1]['class'];
			$method = $d[1]['function'];
		}
		else if($method)
			$caller=$method;
		
		if(is_object($data) || is_array($data))
			$data = var_export($data,true);
		self::logrotate();
		if(self::$file && is_string($data))
		{
			$filename=self::$logfolder.self::$fileLog;
			$msg="[".date("d/m/y H:i:s",$t)."]";
			$msg.=$caller." => ".$data."\n";
			$f=fopen($filename,"at");
			fwrite($f,$msg);
			fclose($f);
		}

		if(self::$usedb && is_string($data)){
			$data=array(
		
				'timestamp' => microtime(true)*1000,
				'class'=>$class?$class:"",
				'method'=>$method?$method:"",
				'message'=>$data
			);
			$result =sm_Database::getInstance()->save("log",$data);
		}
	}
	
	static function debug($data,$class=null,$method=null)
	{
		if(!self::$debug)
				return;
		self::write($data,$class,$method);
	}
	
	static function error($data,$class=null,$method=null)
	{
		$t=time();
	
		$d = debug_backtrace();
		$caller="";
		if(!isset($class) && isset($d[1]['class']))
		{
			$caller=$d[1]['class']."::".$d[1]['function'];
			$class = $d[1]['class'];
			$method = $d[1]['function'];
		}
		else if($method)
			$caller=$method;
	
		if(is_object($data) || is_array($data))
			$data = var_export($data,true);
	
		if(self::$errorLog)
		{
			$filename=self::$logfolder.self::$errorLog;
			$msg="[".date("d/m/y H:i:s",$t)."]";
			$msg.=$caller." => ".$data."\n";
			//self::logrotate();
			$f=fopen($filename,"at");
			fwrite($f,$msg);
			fclose($f);
		}
	}
	
	static function removeLog()
	{
		if(file_exists(self::$logfolder.self::$fileLog))
			unlink(self::$logfolder.self::$fileLog);
	}
	
	static function removeErrLog()
	{
		if(file_exists(self::$logfolder.self::$errorLog))
			unlink(self::$logfolder.self::$errorLog);
	}
	
	static function install($db)
	{
		sm_Config::set("LOGGERROTATIONTIME",array("description"=>"The Log rotation time interval","value"=>'weekly'));
		$sql="CREATE TABLE IF NOT EXISTS  `log` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `timestamp` bigint unsigned NOT NULL,
			  `class` varchar(45) NOT NULL,
			  `method` varchar(128) NOT NULL,
			  `message` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
		$result=$db->query($sql);
		if($result)
		{
			sm_Logger::write("Installed log table");
			return true;
		}
		sm_Logger::write("Not Installed log table");
		return false;
		
	}
	
	static public function uninstall($db)
	{
		sm_Config::delete("LOGGERROTATIONTIME");
		$sql="DROP TABLE `log`;";
		$result=$db->query($sql);
		if($result)
			return true;
		return false;
	}
	
	static public function logErrorHandler($errno, $errstr, $errfile, $errline)
	{
		$msg = $errstr." in ".$errfile." line: ".$errline;
		if(self::$debug)
			sm_set_error($msg);
		self::error($msg);
		return true;
	}
	
	static public function instance()
	{
		if(!self::$instance)
			self::$instance=new sm_Logger();
		return self::$instance;
	}
	
	public function getAllCount($where=null)
	{
		$where=$this->db->buildWhereClause("log",$where);
		$r=$this->db->query("SELECT COUNT(*) as count from `log` ".$where);
		return $r[0]['count'];
	}
	
	public function getAll($limit,$where=null)
	{
		$where=$this->db->buildWhereClause("log",$where);
		$r=$this->db->query("SELECT id, from_unixtime(timestamp/1000) as time, class,method,message from `log` ".$where." order by timestamp DESC ".$limit);
		return $r;
	}
	
	static public function logrotate()
	{
		$filename=self::$logfolder.self::$fileLog;
		$t=time();
		$s=strtotime(date('Y-m-d 00:00:00',$t)); //Mezzanotte del giorno corrente 00:00
		$rtime=sm_Config::get("LOGGERROTATIONTIME",'weekly');
		$limit_time = self::$timemap[$rtime];//$this->conf['axmedistwitter_log_time'];
		
		if(self::$file && file_exists($filename) && (filectime($filename) <$s))//(filemtime($filename)<$s) )
		{
			$files=str_replace(".log", "", $filename);
			foreach (glob($files."_*.log") as $file) {
				if($limit_time != 'always' && ($t-filemtime($file)) >= (int)$limit_time)
				{
					unlink($file);
				}
			}
	/*		$handle = opendir(self::$logfolder);
			 
			if ($handle && $limit_time != 'always')
			{
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						$file=self::$logfolder.$entry;
						if($file==$filename || !is_file($file) || strpos($haystack, $needle))
							continue;
						if(($t-filemtime($file)) >= (int)$limit_time)
						{
							unlink($file);
						}
					}
				}
				closedir($handle);
			}*/
			
			$dayBefore=date("d-m-Y",$t-86400);//data del giorno prima
			$oldfile=str_replace(".log","_".$dayBefore,$filename).".log";
			rename($filename,$oldfile);
		
			if(self::$usedb)
			{
				$result =sm_Database::getInstance()->delete("log","timestamp <".($t-$limit_time)*1000);
			}
		}
		
	}
	
	
}
