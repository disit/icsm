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

////////////////////////////////////////////////////////////////////////////////
//
// 
//
//
////////////////////////////////////////////////////////////////////////////////

define('USERCONFIGCTRL',"apiuser");
define('PWDCONFIGCTRL',"apipassword");

/**
 * Constants used in RestServer Class.
 */
class SM_RestFormat
{

	const PLAIN = 'text/plain';
	const HTML = 'text/html';
	const XML = 'application/xml';
	const JSON = 'application/json';
	const PNG ='image/png';
	static public $formats = array(
		'plain' => SM_RestFormat::PLAIN,
		'text/plain' => SM_RestFormat::PLAIN,
		'text/html' => SM_RestFormat::HTML,
		'application/html' => SM_RestFormat::HTML,
		'application/xml' => SM_RestFormat::XML,
		'text/xml' => SM_RestFormat::XML,
		'application/json' => SM_RestFormat::JSON,
		//'image/webp'=>SM_RestFormat::IMAGE,
		'image/png'=>SM_RestFormat::PNG
	);
	
	static public $filterFormat= array(
		SM_RestFormat::XML,
		SM_RestFormat::HTML,
		SM_RestFormat::JSON,
		SM_RestFormat::PLAIN,
		SM_RestFormat::PNG,
	);
}


class SM_RestException extends Exception
{

	public function __construct($code, $message = null)
	{
		parent::__construct($message, $code);
	}

}






/**
 * Description of SM_RestServer
 *
 */
class SM_RestServer implements sm_Module
{
	public $url;
	public $method;
	public $params;
	public $format;
	public $cacheDir = '.';
	public $realm;
	public $mode;
	public $root;
	public $data;
	protected $map = array();
	protected $errorClasses = array();
	protected $cached;
	protected $registry;
	/**
	 * The constructor.
	 * 
	 * @param string $mode The mode, either debug or production
	 */
	public function  __construct($mode = 'debug', $realm = 'Rest Server')
	{
		$this->mode = $mode;
		$this->realm = $realm;
		$dir = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
		$this->root = ($dir == '.' ? '' : $dir . '/');
		$this->registry = new SM_RestServerRegistry();
		$this->data="";
	}
	
	public function  __destruct()
	{
		if ($this->mode == 'production' && !$this->cached) {
			if (function_exists('apc_store')) {
				apc_store('urlMap', $this->map);
			} else {
				file_put_contents($this->cacheDir . '/urlMap.cache', serialize($this->map));
			}
		}
	}
	
	public function refreshCache()
	{
		$this->map = array();
		$this->cached = false;
	}
	
	public function unauthorized($ask = false)
	{
		if ($ask) {
			header("WWW-Authenticate: Basic realm=\"$this->realm\"");
		}
		throw new SM_RestException(401, "You are not authorized to access this resource.");
	}
	
	public function authorize()
	{
		$username = isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:null;
		$password =  isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']:null;
		// validate input and log the user in
		$user=sm_Config::get('USERCONFIGCTRL',USERCONFIGCTRL);
		$pwd=sm_Config::get('PWDCONFIGCTRL',PWDCONFIGCTRL);
		if($username == $user && $password==$pwd)
		{
			return true;
		}
	
		return false;
	}
	
	public function handle()
	{
		//sm_Logger::write($_SERVER);
		$this->url = $this->getPath();
		$this->method = $this->getMethod();
		$this->format = $this->getFormat();
		
		if ($this->method == 'PUT' || $this->method == 'POST') {
			$this->data = $this->getData();
			//sm_Logger::write($this->data);
		}
		$this->trace();
		list($obj, $method, $params, $this->params, $noAuth) = $this->findUrl();
		
		if ($obj) {
			if (is_string($obj)) {
				if (class_exists($obj)) {
					$obj = new $obj();
				} else {
					throw new Exception("Class $obj does not exist");
				}
			}
			
			$obj->setServer($this);
			
			try {
				if (method_exists($obj, 'init')) {
					$obj->init();
				}
				
				if (!$noAuth) // && method_exists($obj, 'authorize'))
				{
					
					if (!$this->authorize()) {
						$this->sendData($this->unauthorized(true));
						exit;
					}
				}
				
				$result = call_user_func_array(array($obj, $method), $params);
				
				if ($result !== null) {
					$this->sendData($result);
				}
			} catch (SM_RestException $e) {
				$this->handleError($e->getCode(), $e->getMessage());
			}			
		
		} else {
			$this->handleError(404);
		}
	}

	public function addControllerClass($class, $basePath = '')
	{
		$this->loadCache();
		
		if (!$this->cached) {
			if (is_string($class) && !class_exists($class)){
				throw new Exception('Invalid method or class');
			} elseif (!is_string($class) && !is_object($class)) {
				throw new Exception('Invalid method or class; must be a classname or object');
			}
			
			if (substr($basePath, 0, 1) == '/') {
				$basePath = substr($basePath, 1);
			}
			if ($basePath && substr($basePath, -1) != '/') {
				$basePath .= '/';
			}
			sm_EventManager::addEventHandler($class);
			$this->generateMap($class, $basePath);
		}
	}
	
	public function addErrorClass($class)
	{
		$this->errorClasses[] = $class;
	}
	
	public function handleError($statusCode, $errorMessage = null)
	{
		$method = "handle$statusCode";
		foreach ($this->errorClasses as $class) {
			if (is_object($class)) {
				$reflection = new ReflectionObject($class);
			} elseif (class_exists($class)) {
				$reflection = new ReflectionClass($class);
			}
			
			if ($reflection->hasMethod($method))
			{
				$obj = is_string($class) ? new $class() : $class;
				$obj->$method();
				return;
			}
		}
		
		$message = $this->codes[$statusCode] . ($errorMessage && $this->mode == 'debug' ? ': ' . $errorMessage : '');
		
		$this->setStatus($statusCode);
		$this->sendData(array('error' => array('code' => $statusCode, 'message' => $message)));
	}
	
	protected function loadCache()
	{
		if ($this->cached !== null) {
			return;
		}
		
		$this->cached = false;
		
		if ($this->mode == 'production') {
			if (function_exists('apc_fetch')) {
				$map = apc_fetch('urlMap');
			} elseif (file_exists($this->cacheDir . '/urlMap.cache')) {
				$map = unserialize(file_get_contents($this->cacheDir . '/urlMap.cache'));
			}
			if ($map && is_array($map)) {
				$this->map = $map;
				$this->cached = true;
			}
		} else {
			if (function_exists('apc_delete')) {
				apc_delete('urlMap');
			} else {
				if(file_exists($this->cacheDir . '/urlMap.cache'))
					@unlink($this->cacheDir . '/urlMap.cache');
			}
		}
	}
	
	protected function findUrl()
	{
		$urls = $this->map[$this->method];
		if (!$urls) return null;
		
		foreach ($urls as $url => $call) {
			$args = $call[2];
			
			if (!strstr($url, ':')) {
				if ($url == $this->url) {
					
						if(is_array($args) && count($args)>0)
						{
							$params = array_fill(0, count($args), null);
							if (isset($args['data']))
							//$params = array_fill(0, $args['data'] + 1, null);
								$params[$args['data']] = $this->data;
							$call[2] = $params;
						}
				
					return $call;
				}
				
				
		
			} else {
				
				$regex = preg_replace('/\\\\\:([\w\d]+)\.\.\./', '(?P<$1>.+)', str_replace('\.\.\.', '...', preg_quote($url)));
				$regex = preg_replace('/\\\\\:([\w\d]+)/', '(?P<$1>[^\/]+)', $regex);
				if (preg_match(":^$regex$:", urldecode($this->url), $matches)) {
					$params = array();
					$paramMap = array();
					if (isset($args['data'])) {
						$params[$args['data']] = $this->data;
					}
					
					foreach ($matches as $arg => $match) {
						if (is_numeric($arg)) continue;
						$paramMap[$arg] = $match;
						
						if (isset($args[$arg])) {
							$params[$args[$arg]] = $match;
						}
					}
					ksort($params);
					// make sure we have all the params we need
					end($params);
					$max = key($params);
					for ($i = 0; $i < $max; $i++) {
						if (!key_exists($i, $params)) {
							$params[$i] = null;
						}
					}
					ksort($params);
					$call[2] = $params;
					$call[3] = $paramMap; 
					return $call;
				}
			}
		}
	}

	protected function generateMap($class, $basePath)
	{
		if (is_object($class)) {
			$reflection = new ReflectionObject($class);
		} elseif (class_exists($class)) {
			$reflection = new ReflectionClass($class);
		}
		
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
		
		foreach ($methods as $method) {
			$doc = $method->getDocComment(); 
			$noAuth = strpos($doc, '@noAuth') !== false;
			$desc="";
			if (preg_match_all('/@([^ ]+)(?:\s+(.*?))?(?=(\n[ \t]*@|\s*$))/m', $doc, $matches)){
				$desc = sm_array_combine($matches[1], $matches[2]);
			}
			if (preg_match_all('/@url[ \t]+(GET|POST|PUT|DELETE|HEAD|OPTIONS)[ \t]+\/?(\S*)/s', $doc, $matches, PREG_SET_ORDER)) {
				
				$params = $method->getParameters();
				
				foreach ($matches as $i=>$match) {
					$httpMethod = $match[1];
					$url = $basePath . $match[2];
					if ($url && $url[strlen($url) - 1] == '/') {
						$url = substr($url, 0, -1);
					}
					$call = array($class, $method->getName());
					$args = array();
					foreach ($params as $param) {
						$args[$param->getName()] = $param->getPosition();
					}
					$call[] = $args;
					$call[] = isset($desc["desc"][$i])?$desc["desc"][$i]:"";
					$call[] = $noAuth;
					
					$this->map[$httpMethod][$url] = $call;
				}
			}
		}
		
	}

	public function getPath()
	{
		
		$path = substr(preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']), 0);
		// remove root from path
		if ($this->root) $path = str_replace($this->root, '', $path);
		if (strlen($path)!=0 && $path[strlen($path) - 1] == '/') {
			$path = substr($path, 0, -1);
		}
		
		// remove trailing format definition, like /controller/action.json -> /controller/action
		//$path = preg_replace('/\.(\w+)$/i', '', $path);
		//var_dump($this->root);
		return $path;
	}
	
	public function getMethod()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		$override = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : (isset($_GET['method']) ? $_GET['method'] : '');
		if ($method == 'POST' && strtoupper($override) == 'PUT') {
			$method = 'PUT';
		} elseif ($method == 'POST' && strtoupper($override) == 'DELETE') {
			$method = 'DELETE';
		}
		return $method;
	}
	
	public function getFormat()
	{
		$format = SM_RestFormat::PLAIN;
		$accept_mod = preg_replace('/\s+/i', '', $_SERVER['HTTP_ACCEPT']); // ensures that exploding the HTTP_ACCEPT string does not get confused by whitespaces
		$accept = explode(',', $accept_mod);
		$AcceptTypes=array();
		foreach ($accept as $a) {
			// the default quality is 1.
			$q = 1;
			// check if there is a different quality
			if (strpos($a, ';q=')) {
				// divide "mime/type;q=X" into two parts: "mime/type" i "X"
				list($a, $q) = explode(';q=', $a);
			}
			// mime-type $a is accepted with the quality $q
			// WARNING: $q == 0 means, that mime-type isn�t supported!
			$AcceptTypes[$a] = $q;
		}
		arsort($AcceptTypes);
		$accept=array_keys($AcceptTypes);
		$override=null;
		if (isset($_REQUEST['format']) || isset($_SERVER['CONTENT_TYPE']) || isset($_SERVER['HTTP_CONTENT_TYPE'])) {
			// give GET/POST precedence over HTTP request headers
			$override = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
			$override = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : $override;
			$override = isset($_REQUEST['format']) ? $_REQUEST['format'] : $override;
			$override = trim($override); 
		}
		
		// Check for trailing dot-format syntax like /controller/action.format -> action.json
		if(preg_match('/\.(\w+)$/i', $_SERVER['REQUEST_URI'], $matches)) {
			$override = $matches[1];
		}
		
		// Give GET parameters precedence before all other options to alter the format
		$override = isset($_GET['format']) ? $_GET['format'] : $override;
		if (isset(SM_RestFormat::$formats[$override])) {
			$format = SM_RestFormat::$formats[$override]; 
		} elseif (in_array(SM_RestFormat::HTML, $accept)) {
				$format = SM_RestFormat::HTML;
		} elseif (in_array(SM_RestFormat::PNG, $accept)) {
					$format = SM_RestFormat::PNG;
		} elseif (in_array(SM_RestFormat::XML, $accept)) {
			$format = SM_RestFormat::XML; 
		} elseif (in_array(SM_RestFormat::JSON, $accept)) {
			$format = SM_RestFormat::JSON; 
		}
		
		if(!in_array($format,SM_RestFormat::$filterFormat))
			throw new SM_RestException(404, "Invalid Format (".$format.")");
		
		return $format;
	}
	
	public function getData()
	{
		$data = file_get_contents('php://input');
		
		if ($this->format == SM_RestFormat::XML) {
			//$data = XML2Array::createArray($data);
			//$data = json_decode(json_encode($data));
		}
		else if($this->format == SM_RestFormat::JSON)
		{
			$data = json_decode($data);
		} 
		//sm_Logger::write($this->format);
		return $data;
	}
	

	public function sendData($data)
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: 0");
		header('Content-Type: ' . $this->format);

		if ($this->format == SM_RestFormat::XML || $this->format == SM_RestFormat::HTML) {
			$data=json_decode(json_encode($data),true);
			$keys=array_keys($data);
			$data=Array2XML::createXML($keys[0],$data[$keys[0]])->saveXML();
		}
		/*else if ($this->format == SM_RestFormat::HTML) {
			//$data=json_decode(json_encode($data),true);
			$data=$data['response']->body;
		}*/
		else if ($this->format == SM_RestFormat::PNG) {
			$image=$data['response']->body;
			//$image = @imagecreatefromstring($im);
			imagepng($image);
			imagedestroy($image);
			//exit();
			$data="";
		}
		else {
		
			if (is_object($data) && method_exists($data, '__keepOut')) {
				$data = clone $data;
				foreach ($data->__keepOut() as $prop) {
					unset($data->$prop);
				}
			}
			$data = json_encode($data);
			if ($data && $this->mode == 'debug') {
				$data = $this->json_format($data);
			}
		}

		echo $data;
	}

	public function setStatus($code)
	{
		$code .= ' ' . $this->codes[strval($code)];
		header("{$_SERVER['SERVER_PROTOCOL']} $code");
	}
	
	// Pretty print some JSON
	private function json_format($json)
	{
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;
		
		$len = strlen($json);
		
		for($c = 0; $c < $len; $c++) {
			$char = $json[$c];
			switch($char) {
				case '{':
				case '[':
					if(!$in_string) {
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					} else {
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string) {
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					} else {
						$new_json .= $char;
					}
					break;
				case ',':
					if(!$in_string) {
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					} else {
						$new_json .= $char;
					}
					break;
				case ':':
					if(!$in_string) {
						$new_json .= ": ";
					} else {
						$new_json .= $char;
					}
					break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\') {
						$in_string = !$in_string;
					}
				default:
					$new_json .= $char;
					break;					
			}
		}
		
		return $new_json;
	}


	private $codes = array(
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
	
	function trace()
	{
		$request=array(
				'sender_ip'=>$_SERVER['REMOTE_ADDR'],
				'sender_user'=>isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:"anonymous",
				'method'=>$_SERVER['REQUEST_METHOD'],
				'request'=>$_SERVER['REQUEST_URI'],
				'arrival_time'=>$_SERVER['REQUEST_TIME'],
				'agent'=>$_SERVER['HTTP_USER_AGENT'],
				'data'=>is_object($this->data)?json_encode($this->data):$this->data,
				'content_type'=>$this->format,
				
		);
		$this->registry->save($request);
	}
	
	
	
	function exportMap()
	{
		echo var_export($this->map,true);
	}
	
	function getMap()
	{
		return $this->map;
	}
	
	static public function install($db)
	{
		sm_Config::set('USERCONFIGCTRL',array('value'=>USERCONFIGCTRL,"description"=>'API Configuration Authorized Username'));
		sm_Config::set('PWDCONFIGCTRL',array('value'=>PWDCONFIGCTRL,"description"=>'API Configuration Authorized Password'));
	}
	
	
	
	static public function uninstall($db)
	{
		sm_Config::delete('USERCONFIGCTRL');
		sm_Config::delete('PWDCONFIGCTRL');
		return true;
	}
	
	
}


