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

require_once('SM_Nagios_Plugin.class.php');

class test
{
	public 	$arrOptions;
	public  $metrics;
	
	function evaluatePerformanceData($perfData)
	{
		return STATE_OK;
	}
	
	function getMetricsXML()
	{
		$this->metrics=null;
		if(isset($this->arrOptions['username']) && isset($this->arrOptions['password']) && isset($this->arrOptions['url']) )
		{
			$this->metrics=__DIR__."/tmp/".$this->arrOptions['id']."_metrics.json";
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
			$unit=isset($metric['unit'])?$metric['unit']:"#";
				
			$critical= isset($metric['critical'])?$metric['critical']:"";
			$warning= isset($metric['warning'])?$metric['warning']:"";
			$max= isset($metric['max'])?$metric['max']:"";
			$min= isset($metric['min'])?$metric['min']:"";
				
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
			//$result['perfData'][$hostname][$metricN]=$perfData;
			$hostname=$this->arrOptions['hostname'];
			$result['perfData'][$hostname][$metricN]=$perfData;
			$result['metricCount']++;
		}
		return $result;
	}
}

$t= new test();
$t->arrOptions['hostname']="pippo";
$t->arrOptions['username']="admin";
$t->arrOptions['password']="admin";
$t->arrOptions['url']="http://10.254.101.120:8080/wp/applicationMetric?contractId=ID";
$t->arrOptions['id']="cg-123";
$t->getMetricsXML();
$jsonString = file_get_contents($t->metrics);
var_dump($t->parseData($jsonString));
?>