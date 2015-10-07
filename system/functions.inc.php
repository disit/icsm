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
function sm_autoloader($class) {
	include "config.inc.php";
	foreach($classPath as $_path=>$p){
		if(file_exists($p . $class . '.class.php'))
		{
			include $p . $class . '.class.php';
			return;
		}
		else if(file_exists($p . $class . '.php'))
		{
			include $p . $class . '.php';
			return;
		}
		$dirs=glob($p."/{includes/,models/}", GLOB_BRACE | GLOB_ONLYDIR | GLOB_MARK );
		if(is_array($dirs))
		{
			foreach($dirs as $d=>$path)
			{
				if(file_exists($path.$class.".class.php"))
				{
					include $path.$class.".class.php";
					return;
				}
				else if(file_exists($path . $class . '.php'))
				{
					include $path . $class . '.php';
					return;
				}
			}
		}
		$dirs=glob($p."/ui/", GLOB_BRACE | GLOB_ONLYDIR | GLOB_MARK );
		if(is_array($dirs))
		{
			foreach($dirs as $d=>$path)
			{
				if(file_exists($path.$class.".class.php"))
				{
					include $path.$class.".class.php";
					return;
				}
				else if(file_exists($path . $class . '.php'))
				{
					include $path . $class . '.php';
					return;
				}
			}
		}
	}//exit();
	foreach($libPath as $path=>$p){
		if(file_exists($p . $class . '.class.php'))
		{
			include $p . $class . '.class.php';
			return;
		}
		else if(file_exists($p . $class . '.php'))
		{
			include $p . $class . '.php';
			return;
		}
	}
}

function sm_get_calling_class()
{
	//get the trace
	$trace = debug_backtrace();

	// Get the class that is asking for who awoke it
	$class = $trace[1]['class'];

	// +1 to i cos we have to account for calling this function
	for ( $i=1; $i<count( $trace ); $i++ ) {
		if ( isset( $trace[$i] ) ) // is it set?
		if ( $class != $trace[$i]['class'] ) // is it a different class
			return $trace[$i]['class'];
	}
	return null;

}

function sm_set_message($msg)
{
	$_SESSION['message'][]=$msg;
}

function sm_set_error($msg)
{
	$_SESSION['error'][]=$msg;
}

function sm_get_message($reset=true)
{
	$msg = isset($_SESSION['message'])?$_SESSION['message']:null;
	if($reset)
		unset($_SESSION['message']);
	return $msg;
}

function sm_get_error($reset=true)
{
	$msg = isset($_SESSION['error'])?$_SESSION['error']:null;
	if($reset)
		unset($_SESSION['error']);
	return $msg;
}

function sm_call_method($name,$args,$modules=null,$class=null)
{
	if(!$modules)
		$modules=sm_get_all_module();
	
	foreach($modules as $k=>$m)
	{
		
		if(class_exists($m) && method_exists(($m),$name))
		{
			if($class && !is_subclass_of($m,$class))
				continue;
			if(is_array($args))
				call_user_func_array(array($m, $name), $args);
			else	
				call_user_func(array($m, $name),$args);
		}
	}
}

function sm_trigger_event(sm_Event &$event)
{
	sm_EventManager::handle($event);
}

function sm_array_combine($keys, $values)
{
	$result = array();
	foreach ($keys as $i => $k) {
		$result[$k][] = $values[$i];
	}
	//array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
	return    $result;
}

function loadClass($class)
{
	$dir=glob("{apps,includes,lib}/*",GLOB_ONLYDIR | GLOB_BRACE);
	$paths="{".implode(",",$dir)."}";
	$dir=glob("{apps/*",GLOB_ONLYDIR);
	$results=glob("{".$paths."/{controller,model,view}/".$class.".class.php}",GLOB_BRACE);
	echo '<pre>',print_r($results,true),'</pre>';
}

//function sm_get_all_module($folder="*,classes/*",$suffix=".class",$ext=".php")
function sm_get_all_module($type="",$folders=null,$suffix=".class",$ext=".php")
{
	//unset($_SESSION['all']);
	if($type=="" && isset($_SESSION['all']))
	{
		return $_SESSION['all'];
	}
	if($type!="" && isset($_SESSION[$type]))
	{
		return $_SESSION[$type];
	}
		
	include "config.inc.php";
	
	$modules=array();
	$system=array();
	if(!$folders)
		$dirs=array_merge($classPath,$pluginPath);
	else 
		$dirs=$folders;
	
	$check=$suffix.$ext;
	foreach($dirs as $d=>$dir)
	{
		$dir=trim($dir,"/");
		$results=glob($dir."/{".implode(",",$classPathStructure)."}{*".$type.$check."}", GLOB_BRACE );
		//$results = glob("{".$dir."}{/*".$type.$check."}",GLOB_BRACE);
		foreach ($results as $filename)
		{
			// include_once $filename;
			$k=explode("/",str_replace($suffix.$ext, "", $filename));
			$k=$k[count($k)-1];//str_replace($folder."/", "", $k);
			if(substr($k,0,strlen("sm"))==="sm")
				$system[]=$k;
			else
				$modules[]=$k;
		}
	}
	$modules=array_merge($system,$modules);
	if($type=="" && !isset($_SESSION['all']))
	{
		$_SESSION['all']=$modules;
	}
	if($type!="" && !isset($_SESSION[$type]))
	{
		$_SESSION[$type]=$modules;
	}
	return $modules;
}

function sm_relativeURL ($path) {
	include "config.inc.php";
	$dir = str_replace('\\', '/', $path);
	// Resolves inconsistency with PATH_SEPARATOR on Windows vs. Linux
	// Use dirname(__FILE__) in place of __DIR__ for older PHP versions
	return str_replace($baseDir,"",substr($dir, strlen($_SERVER['DOCUMENT_ROOT'])));
	// Clip off the part of the path outside of the document root
}

/**
 * Send headers to the browser to avoid caching
 */
function sm_no_cache() {
	header("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}

function sm_send_error($code,$html=true)
{
	$codes = array(
			'100' => 'Continue',
			'200' => 'OK',
			'201' => 'Created',
			'202' => 'Accepted',
			'203' => 'Non-Authoritative Information',
			'204' => 'No Content',
			'205' => 'Reset Content',
			'206' => 'Partial Content',
			'300' => 'Multiple Choices',
			'301' => 'Moved Permanently',
			'302' => 'Found',
			'303' => 'See Other',
			'304' => 'Not Modified',
			'305' => 'Use Proxy',
			'307' => 'Temporary Redirect',
			'400' => 'Bad Request',
			'401' => 'Unauthorized',
			'402' => 'Payment Required',
			'403' => 'Forbidden',
			'404' => 'Not Found',
			'405' => 'Method Not Allowed',
			'406' => 'Not Acceptable',
			'409' => 'Conflict',
			'410' => 'Gone',
			'411' => 'Length Required',
			'412' => 'Precondition Failed',
			'413' => 'Request Entity Too Large',
			'414' => 'Request-URI Too Long',
			'415' => 'Unsupported Media Type',
			'416' => 'Requested Range Not Satisfiable',
			'417' => 'Expectation Failed',
			'500' => 'Internal Server Error',
			'501' => 'Not Implemented',
			'503' => 'Service Unavailable'
	);
	if(!$html)
	{
		header_remove();
		header(':', true, $code);
	}
	else
	{
		if($code==400)
		{
			$url=$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			$code.="?url=".$url;
		}
		sm_app_redirect("error/".$code);
	}
}

/**
 * Set redirection to the app
 */
function sm_app_redirect($location) {
	include "config.inc.php";
	$base=str_replace("/","\\/",$baseUrl);
	if(preg_match("/^(http|".$base.")/", $location))
		sm_App::getInstance()->setRedirection($location);
	else 
	{
		sm_App::getInstance()->setRedirection($baseUrl.$location);
	}
}

//*******************************Function for validating an email address**************************

function sm_validateEmail($email)
{
	//$email=secureInput($email);
	return ( ! preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $email)) ? TRUE : FALSE;
}


//-----Function for Validating a given string against numeric characters----------

function sm_validateNumeric($str)
{
	//$str=secureInput($str);
	return ( ! preg_match("/^[0-9\.]+$/", $str)) ? FALSE : TRUE;
}

/************************************************************************************************/
function sm_obj2array($obj)
{
	$reflectionClass = new ReflectionClass(get_class($obj));
	$array = array();
	foreach ($reflectionClass->getProperties() as $property) {
		$property->setAccessible(true);
	/*	if (is_array($property->getValue($obj)) || is_object($property->getValue($obj)))
		{
			$result = array();
			foreach ($property->getValue($obj) as $key => $value)
			{
				$array[$property->getName()][strtolower(get_class($value))][] = sm_obj2array($value);
			}
		}
		else*/
			$array[$property->getName()] = $property->getValue($obj);
		$property->setAccessible(false);
	}
	return $array;
}
//Recursive
function sm_obj2array_r($obj)
{
	$reflectionClass = new ReflectionClass(get_class($obj));
	$array = array();
	foreach ($reflectionClass->getProperties() as $property) {
		$property->setAccessible(true);
		if (is_array($property->getValue($obj)) || is_object($property->getValue($obj)))
		 {
		$result = array();
		foreach ($property->getValue($obj) as $key => $value)
		{
		$array[$property->getName()][strtolower(get_class($value))][] = sm_obj2array($value);
		}
		}
		else
		$array[$property->getName()] = $property->getValue($obj);
		$property->setAccessible(false);
	}
	return $array;
}

function sm_formatIcon($icon)
{
	$ico="";
	if(!empty($icon))
	{
		$ico="<i class='glyphicon glyphicon-".$icon."'></i>";
	}

	return $ico;
}

/**
 * Multi-array search
 *
 * @param array $array
 * @param array $search
 * @return array
 */
function sm_multi_array_search($array, $search)
{

	// Create the result array
	$result = array();

	// Iterate over each array element
	foreach ($array as $key => $value)
	{

		// Iterate over each search condition
		foreach ($search as $k => $v)
		{

			// If the array element does not meet the search condition then continue to the next element
			if (!isset($value[$k]) || $value[$k] != $v)
			{
				continue 2;
			}

		}

		// Add the array element's key to the result array
		$result[] = $key;

	}

	// Return the result array
	return $result;

}


function sm_upload_file($path,$fileKey='myfile',$allowedExtensions=array())
{
	$key=$fileKey;
	$res=false;
	$maxSize= 30*1024*1024;
	$msg=t("Error in File Upload!");
	if(empty($allowedExtensions))
		$allowedExtensions=array('png','gif','jpeg','jpg','pdf','swf','flv');
	if (isset($_FILES[$key]) && is_uploaded_file($_FILES[$key]['tmp_name']))
	{
		// Controllo che il file non superi i 18 KB
		if ($_FILES[$key]['size'] > $maxSize) {
			$msg = t("File too big. Max size is 30 MB!!");
			$res=false;
		}
		else
		{
			// Ottengo le informazioni sull'immagine
			// list($width, $height, $type, $attr) = getimagesize($_FILES[$key]['tmp_name']);
			// Controllo che le dimensioni (in pixel) non superino 160x180
			//if (($width > 160) || ($height > 180)) {
			//  $msg = "<p>Dimensioni non corrette!!</p>";
			//  break;
			//}
			// Controllo che il file sia in uno dei formati GIF, JPG o PNG
			// if (($type!=1) && ($type!=2) && ($type!=3)) {
			$filename=strtolower($_FILES[$key]['name']);
			if(!in_array(end(explode(".",$filename)),$allowedExtensions))
			{
				$msg = t("Incorrect file format!!");
				$res=false;
			}
			else
			{
				$_FILES[$key]['name']=utf8_decode($_FILES[$key]['name']);
				$filename=$_FILES[$key]['name'];
				$filepath=realpath($path); //,$imgUploadDir);
				if($filepath[strlen($filepath)-1]!=DIRECTORY_SEPARATOR)
					$filepath.=DIRECTORY_SEPARATOR;
				$file=$filepath.$filename;
				// Verifico che sul sul server non esista gi� un file con lo stesso nome
				// In alternativa potrei dare io un nome che sia funzione della data e dell'ora
				 
				 
				if (file_exists($filepath))
				{
					//$filename=$_FILES[$key]['name'];
					//$file=$filepath.$filename;
					if (!file_exists($file))
					{
						if (!@move_uploaded_file($_FILES[$key]['tmp_name'], $file)) {
							$msg = t("Error during the upload")." ".$filename."!!";
							$res=false;
						}
						else
						{

							$msg=t("File saved at ").$file;
							$res=true;
						}
					}
					else
					{
						$msg=t("File saved at ").$file;
						$res=true;
					}
				}
				else
				{
					$msg = t("Upload folder does not exist")." (".$filepath.")!!";
					$res=false;
				}
			}
		}
	}

	$ret=array('msg'=>$msg,
			'res'=>$res,
			'file'=>$filename,
	);
	return $ret;
}

function t($s)
{
	return $s;
}