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
define("JAVA_PATH","/opt/jdk1.8.0_20/bin/");
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
require_once('HLM/Nagios_Plugin.class.php');
require_once('HLM/XML2Array.class.php');
require_once('HLM/HLMConfig.php');

class SMHLMPlugin extends Nagios_Plugin
{
	private $_arrStatus;
	private $metrics;
	private $hlmjava;
	private $config;
	private $type;
	function __construct($strRevision=NULL, $strUsage=NULL, $strHelp=NULL, $arrOptions=NULL)
	{
		// call parent constructor
		parent::__construct($strRevision, $strUsage, $strHelp, $arrOptions);
		$this->hlmjava=__DIR__."/HLM/hlm.jar";
		//request of metrics by id
		$this->metrics=__DIR__."/HLM/metrics.xml";
		$this->config=__DIR__."/HLM/config.xml";
		$this->type=null;
		if(USEHLMAPI && isset($this->arrOptions['id']))
		{
			if(!isset($this->arrOptions['url']))
			{	
				if(preg_match("/datacenter/i",$this->arrOptions['id']))
				{
					$this->arrOptions['url']=HLMMETRICAPIURLDC;
					$this->type="DC";
				}
				else
				{
					$this->arrOptions['url']=HLMMETRICAPIURL;
					$this->type="BC";
				}

			}		
			if(!isset($this->arrOptions['username']) )
				$this->arrOptions['username']=HLMMETRICAPIUSER;
			if(!isset($this->arrOptions['password']) )
				$this->arrOptions['password']=HLMMETRICAPIPWD;
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
		
		//$hlmjava=__DIR__."/HLM/hlm.jar";
		//request of metrics by id
		$hlmfile=__DIR__."/HLM/tmp/".$this->arrOptions['hostname'].".xml";
              $shlmfile=__DIR__."/HLM/tmp/serv_".$this->arrOptions['hostname'].".xml";
		//$metrics=__DIR__."/HLM/metrics.xml";
		$config=__DIR__."/HLM/tmp/".$this->arrOptions['hostname']."_config.xml";
		copy($this->config,$config);
		if(!file_exists($config))
		{
			$this->output("CRITICAL : HLM metrics config file not available for ".$this->arrOptions['id'], STATE_CRITICAL);
			return;
		}

		$this->getMetricsXML();
		if(!file_exists($this->metrics))
		{
			unlink($config);
			$this->output("CRITICAL : HLM metrics file not available for ".$this->arrOptions['id'], STATE_CRITICAL);
			return;
		}
		$cmd =JAVA_PATH."java -jar \"".$this->hlmjava."\" -m \"$this->metrics\" -c \"$config\" -o \"$hlmfile\" -s \"$shlmfile\"";
		
		$output=array();
		$xmlString="";
		exec($cmd,$output);
		$errorOutH="";
		$errorOutS="";
		$pluginOut=array();
		$pluginOut['metricCount']=0;
		$pluginOut['metricHost']=0;
		$pluginOut['metricService']=0;
		$pluginOut['metricStatus'][STATE_OK]=0;
		$pluginOut['metricStatus'][STATE_WARNING]=0;
		$pluginOut['metricStatus'][STATE_CRITICAL]=0;
		$pluginOut['metricStatus'][STATE_UNKNOWN]=0;
		$passiveData=array('perfData'=>array());
		unlink($this->metrics);
		unlink($config);
		if(!file_exists($hlmfile))
		{
			
			$errorOutH='metrics output for hostgroup not availabe';
			//$this->output('CRITICAL :HLM metrics output not availabe', STATE_CRITICAL);
		}
		else
		{
			
			$xmlString = file_get_contents($hlmfile);
			unlink($hlmfile);
			$data = $this->parseData($xmlString);
			$pluginOut['metricCount']+=$data['metricCount'];
			$pluginOut['metricHost']=$data['metricCount'];
			$pluginOut['metricStatus'][STATE_OK]+=$data['metricStatus'][STATE_OK];
			$pluginOut['metricStatus'][STATE_WARNING]+=$data['metricStatus'][STATE_WARNING];
			$pluginOut['metricStatus'][STATE_CRITICAL]+=$data['metricStatus'][STATE_CRITICAL];
			$pluginOut['metricStatus'][STATE_UNKNOWN]+=$data['metricStatus'][STATE_UNKNOWN];
			//$this->writePluginOutput($data);
			$this->writePassiveCheck($data,"Host");
			
			//$this->output("OK : HLM metrics written for ".$this->arrOptions['id'], STATE_OK);
			
		}
		
		if($this->type=="BC")
		{
			if(!file_exists($shlmfile))
			{
				$errorOutS.=' metrics output for servicegroup not availabe';
			}
			else
			{
				$xmlString = file_get_contents($shlmfile);
				unlink($shlmfile);
				$data = $this->parseData($xmlString);
				//$this->writePluginOutput($data);
				$this->writePassiveCheck($data,"Service");
				$pluginOut['metricCount']+=$data['metricCount'];
				$pluginOut['metricService']=$data['metricCount'];
				$pluginOut['metricStatus'][STATE_OK]+=$data['metricStatus'][STATE_OK];
				$pluginOut['metricStatus'][STATE_WARNING]+=$data['metricStatus'][STATE_WARNING];
				$pluginOut['metricStatus'][STATE_CRITICAL]+=$data['metricStatus'][STATE_CRITICAL];
				$pluginOut['metricStatus'][STATE_UNKNOWN]+=$data['metricStatus'][STATE_UNKNOWN];
			
				//$this->output("OK : HLM metrics written for ".$this->arrOptions['id'], STATE_OK);
			}			
		}
		$this->writePluginOutput($pluginOut);

		if($errorOutH!="" && $errorOutS!="" && $this->type=="BC")
			$this->output('CRITICAL : HLM '.$errorOutH.$errorOutS, STATE_CRITICAL);
		else if($errorOutH!="" && $this->type=="DC")
			$this->output('CRITICAL : HLM '.$errorOutH, STATE_CRITICAL);
		else
			$this->output("OK : HLM metrics written for ".$this->arrOptions['id'], STATE_OK);

		//$this->output('MySQL Connect Failed', STATE_UNKNOWN);
		//$this->output('CRITICAL : not touched', STATE_CRITICAL);
		//$this->output("WARNING : Last touched {$arrResult['Seconds']} seconds ago", STATE_WARNING);
		//$this->output("OK : Last touched {$arrResult['Seconds']} seconds ago", STATE_OK);
	
		
	}
	
	function getMetricsXML()
	{
		if(isset($this->arrOptions['username']) && isset($this->arrOptions['password']) && isset($this->arrOptions['url']) )
		{
			$this->metrics=__DIR__."/HLM/tmp/".$this->arrOptions['id']."_metrics.xml";
			$cred = sprintf( 'Authorization: Basic %s',
					base64_encode( $this->arrOptions['username'].':'.$this->arrOptions['password'] )
		    );
			$opts = array(
					'http'=>array(
							'timeout' => 1200,
							'method'=>"GET",
							'header'=>"Accept: application/xml\r\n".$cred
	  
					)
			);
		/*	$parts=array(
			'user'=>$this->arrOptions['username'],
			);
			$url = http_build_url('', $parts);*/
			$url = str_replace("ID",$this->arrOptions['id'],$this->arrOptions['url']);
			$context = stream_context_create($opts);
			$xml = file_get_contents($url,false,$context);
			$xml=str_replace("%URN%", $this->arrOptions['id'], $xml);
			file_put_contents($this->metrics, $xml);		
		}
		else
		{ 
			$xmlfile=__DIR__."/HLM/metrics.xml";
			$this->metrics=__DIR__."/HLM/tmp/".$this->arrOptions['id']."_metrics.xml";
			$xml=file_get_contents($xmlfile);	
			$xml=str_replace("%URN%", $this->arrOptions['id'], $xml);
			file_put_contents($this->metrics, $xml);
			
		}
		
	}
	
	function parseData($data){
		$dom = new DOMDocument();
	
		$dom->loadXML($data);
		$metrics = $dom->getElementsByTagName('ServiceMetric');
		
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
		
			$metricN= $metric->getElementsByTagName('hasMetricName')->item(0)->nodeValue;
			$time= $metric->getElementsByTagName('atTime')->item(0)->nodeValue;
			$value= $metric->getElementsByTagName('hasMetricValue')->item(0)->nodeValue;
			$unit= $metric->getElementsByTagName('hasMetricUnit')->item(0)->nodeValue;
			
			$hasName = $metric->getElementsByTagName('hasName')->item(0);
			$hostname= $hasName->attributes->item(0)->nodeValue;
			
			$w=$metric->getElementsByTagName('hasWarning');
			$warning= $w->length!=0?$w->item(0)->nodeValue:"";
			
			$w=$metric->getElementsByTagName('hasCritical');
			$critical= $w->length!=0?$w->item(0)->nodeValue:"";
			
			$w=$metric->getElementsByTagName('hasMax');
			$max= $w->length!=0?$w->item(0)->nodeValue:"";
			
			$w=$metric->getElementsByTagName('hasMin');
			$min= $w->length!=0?$w->item(0)->nodeValue:"";

			$floatVal = floatval($value);
			// If the parsing succeeded and the value is not equivalent to an int
			if($floatVal && intval($floatVal) != $floatVal)
			{
				$value=number_format($floatVal ,2,'.', '');
			}
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
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s ","metrics host",$data['metricHost'],"#","5","1","0",$data['metricCount']);
		$this->addPerformanceData($output);
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s ","metrics services",$data['metricService'],"#","5","1","0",$data['metricCount']);
		$this->addPerformanceData($output);
		
		/*$output=sprintf("%s=%d.0%s;%s;%s;%s;%s","ok",$data['metricStatus'][STATE_OK],"C","5","1","0",2*$data['metricCount']);
		$this->addPerformanceData($output);
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s","warning",$data['metricStatus'][STATE_WARNING],"C","1","5","0",2*$data['metricCount']);
		$this->addPerformanceData($output);
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s","critical",$data['metricStatus'][STATE_CRITICAL],"C","1","5","0",2*$data['metricCount']);
		$this->addPerformanceData($output);
		
		$output=sprintf("%s=%d.0%s;%s;%s;%s;%s","unknown",$data['metricStatus'][STATE_UNKNOWN],"C","1","5","0",2*$data['metricCount']);
		$this->addPerformanceData($output);*/

	}
	
	//[<timestamp>] PROCESS_SERVICE_CHECK_RESULT;<host_name>;<svc_description>;<return_code>;<plugin_output>
	
	function writePassiveCheck($data,$type){
		$commandfile=NAGIOS_PIPE_CMD;
		if(isset($this->arrOptions['passivecheckname']))
		{
			//$output=sprintf("%s=%s%s;%s;%s;%s",$metric,$value,$unit,$warning,$critical,$max);
			
			
			$svc_description=$this->arrOptions['passivecheckname']." ".$type;
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
					$message = "OK : ".count($output)." HLM metrics written";
					$plugin_output=$message."|".implode(" ",$output)."\n";
					//$plugin_output=$this->buildPluginOutput($message,$output);
					$out="[$timestamp] PROCESS_SERVICE_CHECK_RESULT;$hostname;$svc_description;".STATE_OK.";$plugin_output";
				}
				else 
				{
					$timestamp=time();
					$plugin_output="CRITICAL : ".count($output)." HLM metrics written;";
					$out="[$timestamp] PROCESS_SERVICE_CHECK_RESULT;$hostname;$svc_description;".STATE_CRITICAL.";$plugin_output";
				}
				$cmd = "echo \"$out\" > $commandfile";
				exec($cmd);
			}
		}
		//[<timestamp>] PROCESS_HOST_CHECK_RESULT;<host_name>;<host_status>;<plugin_output>
		$hostname=$this->arrOptions['hostname'];
		$plugin_output="OK Check Done";
		$timestamp=time();
		$out="[$timestamp] PROCESS_HOST_CHECK_RESULT;$hostname;".STATE_OK.";$plugin_output";
		$cmd = "echo \"$out\" > $commandfile";
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

$objPlugin = new SMHLMPlugin($strRevision, $strUsage, $strHelp, $arrOptions);

$objPlugin->execute();

?>