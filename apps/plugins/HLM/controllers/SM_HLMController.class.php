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

class SM_HLMController extends SM_RestController
{
	protected $hlm;
	function __construct()
	{
		$this->hlm=new HLM();
	}
	
	/**
	 * Gets the HLM records
	 *
	 * @url GET HLM/list/:id
	 *
	 */
	function HLM_records($id=null)
	{
		$data=array();
		
		return new SM_Response();
	}
	
	
	/**
	 * @desc Gets the HLM metrics from KB for a configuration
	 *
	 * @url GET HLM/KB/metrics/:id
	 *
	 */
	function HLM_metrics($id=null)
	{
		$data=array();
		if(class_exists("KB"))
		{
			/*$configurator = new SM_Configurator();
			$conf = $configurator->getConfigurationData("header", $id);
			var_dump($id);exit(); 
			if($conf)
			{*/
				//$identifier = $conf['description'];
				$identifier = trim(str_replace("urn:cloudicaro:BusinessConfiguration:", "", $id));
				$KBClient =  KB::getMetrics($identifier);
				if($KBClient->getResponseCode()==200)
				{
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: 0");
					header('Content-Type: application/xml');
					echo trim($KBClient->getResponse());			
					exit();
				}
				else
					throw new SM_RestException($KBClient->getResponseCode(), $KBClient->getResponseMessage());
				
			/*}
			else
				throw new SM_RestException(400, "Invalid parameters");*/
		}
		else
			throw new SM_RestException(400, "KB module does not installed");
		 
	}
	
	/**
	 * Write the HLM records in db from a RDF xml string
	 *
	 * @url POST HLM/RDF/write
	 *
	 */
	function HLM_RDF_write($data=null)
	{
		
		if($data && $this->server)
		{
		
			if (is_string($data)) {
				$r=true;
				$dom = new DOMDocument();
				$dom->loadXML($data);
				$metrics = $dom->getElementsByTagName('ServiceMetric');
				foreach ($metrics as $metric) {
				
					$records['metric']= $metric->getElementsByTagName('hasMetricName')->item(0)->nodeValue;
					$records['time']= $metric->getElementsByTagName('atTime')->item(0)->nodeValue;
					$records['value']= $metric->getElementsByTagName('hasMetricValue')->item(0)->nodeValue;
					$records['unit']= $metric->getElementsByTagName('hasMetricUnit')->item(0)->nodeValue;
				
				
					$records['dependsOn']= $metric->getElementsByTagName('dependsOn')->item(0)->attributes->item(0)->nodeValue;
					
					//Va tolta per la KB
					$hasName = $metric->getElementsByTagName('hasName')->item(0);
					$records['hostname']= $hasName->attributes->item(0)->nodeValue;
					$metric->removeChild($hasName);
					
					//Va tolta per la KB
					$w=$metric->getElementsByTagName('hasWarning');	
					$records['warning']= $w->length!=0?$w->item(0)->nodeValue:"";
					if($w->length!=0)
						$metric->removeChild($w->item(0));
					//Va tolta per la KB
					$w=$metric->getElementsByTagName('hasCritical');
					$records['critical']= $w->length!=0?$w->item(0)->nodeValue:"";
					if($w->length!=0)
						$metric->removeChild($w->item(0));
					//Va tolta per la KB
					$w=$metric->getElementsByTagName('hasMax');
					$records['max']= $w->length!=0?$w->item(0)->nodeValue:"";
					if($w->length!=0)
						$metric->removeChild($w->item(0));
					$qRes = $this->hlm->insert($records);
					$r &= isset($qRes);
				}				
				if($r){
					$result[]="Success";
					sm_EventManager::handle(new sm_Event("HLMWriteEvent",$dom));
					if(class_exists("KB") && sm_Config::get("HLMDWRITEINTOKB",false))
					{
					 	if(KB::isAlive(60))
						{
							$data = $dom->saveXML();
							$KBClient = KB::postMetrics($data);
							$code = $KBClient->getResponseCode();
							if($code!=200 && $code!=204)
								sm_Logger::write("HLM: The following error occured when posting metrics to KB: ".$KBClient->getResponseMessage());
							else 
								sm_Logger::write("HLM: Code from KB: ".$KBClient->getResponseCode());
						}
						else 
							sm_Logger::write("HLM: KB was not unreachable at ".date("d/m/Y H:i:s",time()));
					}
				}
				
			}
			else
				$result['error']="Invalid Parameters";
			
			
			if(isset($result['error']))
				//$response['response']= new SM_Response('error','Insert Configuration',$result['error']);
				throw new SM_RestException(400, $result['error']);
			else
				$response['response']= new SM_Response('message','HLM Metric Writer',$result);
		}
		else
			throw new SM_RestException(400, "Invalid Parameters");
		return $response; //$user; // serializes object into JSON
		
	}
	
	
	
}