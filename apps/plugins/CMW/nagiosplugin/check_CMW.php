#!/usr/bin/php
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

//--------------------------------------------------------------------//
// NOTES
//--------------------------------------------------------------------//
//
// This file should be installed in your nagios libexec folder
// eg. /usr/local/nagios/libexec/
//

//--------------------------------------------------------------------//
// CONFIG
//--------------------------------------------------------------------//
//Nagios Pipe Cmd for Passive Checks
define("NAGIOS_PIPE_CMD","/usr/local/nagios/var/rw/nagios.cmd");
// revision
$strRevision = "0.0.1";

// usage
$strUsage = "USAGE : <name> -H hostname -u username -p password -u url -i id  -s passivecheckname [-w ] [-c ]";

// help
$strHelp = "";

// options
$arrOptions = Array('i'=>'id:', 'url'=>'url:','s'=>'passivecheckname:');

// basedir
$strBaseDir	= "/usr/local/nagios/libexec/";


//$nagiosPipeCmd="/usr/local/nagios/var/rw/nagios.cmd";

//--------------------------------------------------------------------//
// REPLICATION PLUGIN CLASS
//--------------------------------------------------------------------//

// require nagios class
require_once('CMW/Nagios_Plugin.class.php');
require_once('CMW/CMWConfig.php');

class SMCMWPlugin extends Nagios_Plugin
{
	private $_arrStatus;
	private $metrics;
	
	private $config;
	private $type;
	function __construct($strRevision=NULL, $strUsage=NULL, $strHelp=NULL, $arrOptions=NULL)
	{
		// call parent constructor
		parent::__construct($strRevision, $strUsage, $strHelp, $arrOptions);
		$this->type=null;
		$this->metrics=null;
		if(USECMWAPI && isset($this->arrOptions['id']))
		{
			if(!isset($this->arrOptions['url']))
			{	
				if(preg_match("/datacenter/i",$this->arrOptions['id']))
				{
					$this->arrOptions['url']=null;
					$this->type="DC";
				}
				else
				{
					$this->arrOptions['url']=CMWMETRICAPIURL;
					$this->type="BC";
				}

			}		
			if(!isset($this->arrOptions['username']) )
				$this->arrOptions['username']=CMWMETRICAPIUSER;
			if(!isset($this->arrOptions['password']) )
				$this->arrOptions['password']=CMWMETRICAPIPWD;
		}
	}
	
	function execute()
	{
		// check for required options
		if (!isset($this->arrOptions['hostname']) || !isset($this->arrOptions['id']) ) // || !isset($this->arrOptions['username']) || !isset($this->arrOptions['password']) || !isset($this->arrOptions['id']) )
		{
			// output help
			$this->printHelp();
		}
		
		// default critical to 1 hour
			$intCritical = isset($this->arrOptions['critical'])?(int)$this->arrOptions['critical']:3600;
		// default warning to 1 hour
			$intWarning = isset($this->arrOptions['warning'])?(int)$this->arrOptions['warning']:3600;
		
		
		$this->getMetricsJSON();
		
		
		$output=array();
		
		
		$errorOut="";
		$pluginOut=array();
		$pluginOut['metricCount']=0;
		$pluginOut['metricService']=0;
		$pluginOut['metricStatus'][STATE_OK]=0;
		$pluginOut['metricStatus'][STATE_WARNING]=0;
		$pluginOut['metricStatus'][STATE_CRITICAL]=0;
		$pluginOut['metricStatus'][STATE_UNKNOWN]=0;
		$passiveData=array('perfData'=>array());
		
		if( $this->type=="BC")
		{
			if(!file_exists($this->metrics))	
			{
				
				$errorOut='metrics output for '.$this->arrOptions['id'].' not availabe';
				//$this->output('CRITICAL :HLM metrics output not availabe', STATE_CRITICAL);
			}
			else
			{
				
				$jsonString = file_get_contents($this->metrics);
				unlink($this->metrics);
				$data = $this->parseData($jsonString);
				$pluginOut['metricCount']+=$data['metricCount'];
				$pluginOut['metricData']=$data['metricCount'];
				$pluginOut['metricStatus'][STATE_OK]+=$data['metricStatus'][STATE_OK];
				$pluginOut['metricStatus'][STATE_WARNING]+=$data['metricStatus'][STATE_WARNING];
				$pluginOut['metricStatus'][STATE_CRITICAL]+=$data['metricStatus'][STATE_CRITICAL];
				$pluginOut['metricStatus'][STATE_UNKNOWN]+=$data['metricStatus'][STATE_UNKNOWN];
				//$this->writePluginOutput($data);
				$this->writePassiveCheck($data,"Service");
				
				//$this->output("OK : HLM metrics written for ".$this->arrOptions['id'], STATE_OK);
				
			}
			if($errorOut!="" )
				$this->output('CRITICAL : CMW '.$errorOut, STATE_CRITICAL);
			else
			{
				$this->writePluginOutput($pluginOut);
				$this->output("OK : CMW metrics written for ".$this->arrOptions['id'], STATE_OK);
			}
			
		}
		
	}
	
	function getMetricsJSON()
	{
		$this->metrics=null;
		if(isset($this->arrOptions['username']) && isset($this->arrOptions['password']) && isset($this->arrOptions['url']) )
		{
			$this->metrics=__DIR__."/CMW/tmp/".$this->arrOptions['id']."_metrics.json";
			$cred = sprintf( 'Authorization: Basic %s',
					base64_encode( $this->arrOptions['username'].':'.$this->arrOptions['password'] )
		    );
			$opts = array(
					'http'=>array(
							'timeout' => 1200,
							'method'=>"GET",
							'header'=>"Accept: application/json\r\n".$cred
	  
					)
			);
			$contractId=str_ireplace("urn:cloudicaro:BusinessConfiguration:", "", $this->arrOptions['id']);
			$url = str_replace("ID",$contractId,$this->arrOptions['url']);
			$context = stream_context_create($opts);
			$json = file_get_contents($url,false,$context);
			file_put_contents($this->metrics, $json);		
		}	
	}
	
	
	function parseData($data){
		
		/*"id":3,"value":"0","metricKey":"tagAttivi","intervalInSeconds":10,"contractId":"cg-123","expiredMetric":false,"timeStamp":"12/15/2014 18:46:19","agentTimeStamp":"12/15/2014 18:46:21"*/
		$metrics = json_decode($data,true);
		
		$result=array();
		$result['metricCount']=0;
		//$result['metricCount']=count($metrics);
		$result['metricStatus']=array(
				STATE_OK=>0,
				STATE_WARNING=>0,
				STATE_CRITICAL=>0,
				STATE_UNKNOWN=>0,
		);
		
		foreach ($metrics as $metric) {
		
			$metricN= $metric['metricKey'];
			$time= $metric['timeStamp'];
			$value= $metric['value'];
			$floatVal = floatval($value);
			// If the parsing succeeded and the value is not equivalent to an int
			if($floatVal && intval($floatVal) != $floatVal)
			{
				$value=number_format($floatVal ,2,'.', '');
			}
			
			$unit=isset($metric['unit'])?$metric['unit']:"#";
			
			
			if($unit=="%")
				$max= isset($metric['max'])?$metric['max']:100;
			else
				$max= isset($metric['max'])?$metric['max']:2*$value;
			$min= isset($metric['min'])?$metric['min']:0;
			
			$critical = isset($metric['critical'])?$metric['critical']:0.90*$max;
			$warning = isset($metric['warning'])?$metric['warning']:0.75*$max;
			
			$perfData=array(
					'value'=>$value,
					'unit'=>$unit,
					'warning'=>$warning,
					'critical'=>$critical,
					'max'=>$max,
					'min'=>$min,
						
			);
			
			$status=$this->evaluatePerformanceData($perfData);
			$perfData['status']=$status;			
			$result['metricStatus'][$perfData['status']]++;
			//$result['perfData'][$hostname][$metricN]=$perfData;
			$hostname=$this->arrOptions['hostname'];
			$result['perfData'][$hostname][$metricN]=$perfData;
			$result['metricCount']++;
		}
		return $result;
	}

	function evaluatePerformanceData($perfData)
	{
		return STATE_OK;
	}
	
	
		
	function writePluginOutput($data)
	{
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s ","metrics computed",$data['metricCount'],"#","5","1","0","1000");	
		$this->addPerformanceData($output);
		
		/*$output=sprintf("%s=%d.0%s;%s;%s;%s;%s ","metrics host",$data['metricHost'],"#","5","1","0",$data['metricCount']);
		$this->addPerformanceData($output);
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s ","metrics services",$data['metricService'],"#","5","1","0",$data['metricCount']);
		$this->addPerformanceData($output);*/
		
		

	}
	
	//[<timestamp>] PROCESS_SERVICE_CHECK_RESULT;<host_name>;<svc_description>;<return_code>;<plugin_output>
	
	function writePassiveCheck($data,$type){
		$commandfile=NAGIOS_PIPE_CMD;
		if(isset($this->arrOptions['passivecheckname']) && isset($data['perfData']))
		{
			//$output=sprintf("%s=%s%s;%s;%s;%s",$metric,$value,$unit,$warning,$critical,$max);
			
			
			$svc_description=$this->arrOptions['passivecheckname']; //." ".$type;
			foreach ($data['perfData'] as $hostname=>$perfData)
			{
				$output=array();
				foreach($perfData as $metric=>$p)
				{
					if($metric!="" && strlen($metric)>4)
						$output[]=sprintf("%s=%s%s;%s;%s;%s;%s",$metric,$p['value'],$p['unit'],$p['warning'],$p['critical'],$p['min'],$p['max']);
				}
				if(!empty($output))
				{
					$timestamp=time();
					$message = "OK : ".count($output)." CMW metrics written";
					$plugin_output=$message."|".implode(" ",$output)."\n";
					//$plugin_output=$this->buildPluginOutput($message,$output);
					$out="[$timestamp] PROCESS_SERVICE_CHECK_RESULT;$hostname;$svc_description;".STATE_OK.";$plugin_output";
				}
				else 
				{
					$timestamp=time();
					$plugin_output="CRITICAL : ".count($output)." CMW metrics written;";
					$out="[$timestamp] PROCESS_SERVICE_CHECK_RESULT;$hostname;$svc_description;".STATE_CRITICAL.";$plugin_output";
				}
				$cmd = "echo \"$out\""; // > $commandfile";
				exec($cmd);
			}
		}
		//[<timestamp>] PROCESS_HOST_CHECK_RESULT;<host_name>;<host_status>;<plugin_output>
		$hostname=$this->arrOptions['hostname'];
		$plugin_output="OK Check Done";
		$timestamp=time();
		$out="[$timestamp] PROCESS_HOST_CHECK_RESULT;$hostname;".STATE_OK.";$plugin_output";
		$cmd = "echo \"$out\""; // > $commandfile";
		exec($cmd);
	}

	function buildPluginOutput($strMessage,$output=array(),$arrLongText=array())
	{
		$strOutput = $strMessage;
		if (count($output))
		{
			$strOutput .= "|". array_shift($output);	
		}
		$strOutput .= "\n";
		
		// add long text lines
		$strOutput .= implode("\n", $arrLongText);
			
		// add additional lines of performance data
		if (count($output))
		{
			$strOutput .= "|";
			foreach ($output as $strLine)
			{
				$strOutput .= $strLine ."\n";
			}	
		}
		
		// add a trailing \n
		if (substr($strOutput, -1) != "\n")
		{
			$strOutput	.= "\n";	
		}
		//echo $strOutput;
		return $strOutput;
	}
	
}


//--------------------------------------------------------------------//
// SCRIPT
//--------------------------------------------------------------------//

$objPlugin = new SMCMWPlugin($strRevision, $strUsage, $strHelp, $arrOptions);

$objPlugin->execute();

?>