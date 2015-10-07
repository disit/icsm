#!/usr/bin/php
<?php
//----------------------------------------------------------------------------//
// nagiosPluginPHP (c) copyright 2008 CYKO Pty Ltd
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// THIS SOFTWARE IS GPL LICENSED
//----------------------------------------------------------------------------//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License (version 2) as 
//  published by the Free Software Foundation.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Library General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//----------------------------------------------------------------------------//

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

// revision
$strRevision = "0.0.1";

// usage
$strUsage = "USAGE : <name> -H hostname -u username -p password -u url -i id  [-w ] [-c ]";

// help
$strHelp = "";

// options
$arrOptions = Array('i'=>'id:', 'url'=>'url:');

// basedir
$strBaseDir	= "/usr/local/nagios/libexec/";

//--------------------------------------------------------------------//
// REPLICATION PLUGIN CLASS
//--------------------------------------------------------------------//

// require nagios class
require_once('HLM/SM_Nagios_Plugin.class.php');
require_once('HLM/XML2Array.class.php');

class SMHLMPlugin extends SM_Nagios_Plugin
{
	private $_arrStatus;
	
	function __construct($strRevision=NULL, $strUsage=NULL, $strHelp=NULL, $arrOptions=NULL)
	{
		// call parent constructor
		parent::__construct($strRevision, $strUsage, $strHelp, $arrOptions);
	}
	
	function execute()
	{
		// check for required options
		if (!isset($this->arrOptions['hostname']) || !isset($this->arrOptions['username']) || !isset($this->arrOptions['password']) || !isset($this->arrOptions['id']) )
		{
			// output help
var_dump($this->arrOptions);
			$this->printHelp();
		}
		
		// default critical to 1 hour
			$intCritical = isset($this->arrOptions['critical'])?(int)$this->arrOptions['critical']:3600;
		// default warning to 1 hour
			$intWarning = isset($this->arrOptions['warning'])?(int)$this->arrOptions['warning']:3600;
		
		$hlmjava=__DIR__."/HLM/hlm.jar";
		//request of metrics by id
		$hlmfile=__DIR__."/HLM/hkb.xml";//$this->arrOptions['hostname'];
		$metrics=__DIR__."/HLM/metrics.xml";
		$config=__DIR__."/HLM/config.xml";
		$cmd ="java -jar \"".$hlmjava."\" -m \"$metrics\" -c \"$config\"";
		
		$output=array();
		$xmlString="";
		exec($cmd,$output);
		if(!file_exists($hlmfile))
			$this->output('CRITICAL :HLM metrics output not availabe', STATE_CRITICAL);
		else
		{
			$xmlString = file_get_contents($hlmfile);
			$this->writePuginOutput($xmlString);
			unlink($hlmfile);
			$this->output("OK : HLM metrics written", STATE_OK);
		}
		
		
		//$this->output('MySQL Connect Failed', STATE_UNKNOWN);
		//$this->output('CRITICAL : not touched', STATE_CRITICAL);
		//$this->output("WARNING : Last touched {$arrResult['Seconds']} seconds ago", STATE_WARNING);
		//$this->output("OK : Last touched {$arrResult['Seconds']} seconds ago", STATE_OK);
	
		
	}
	
	function writePuginOutput($data)
	{
		$records=XML2Array::createArray($data);
		$r=true;
		foreach ($records["rdf:RDF"]['icr:ServiceMetric'] as $k=>$record)
		{
			//var_dump($record);
			//return true;
			$metric=$record['icr:hasMetricName'];
			$time=$record['icr:atTime']['@value'];
			$value=$record['icr:hasMetricValue']['@value'];
			$floatVal = floatval($value);
			// If the parsing succeeded and the value is not equivalent to an int
			if($floatVal && intval($floatVal) != $floatVal)
			{
   				 $value=number_format($floatVal ,2,'.', '');			
			}
				
			$unit=isset($record['icr:hasMetricUnit'])?$record['icr:hasMetricUnit']:"";
			$warning=isset($record['icr:hasWarning'])?$record['icr:hasMetricUnit']:"";
			$critical=isset($record['icr:hasCritical'])?$record['icr:hasCritical']:"";
			$max=isset($record['icr:hasMax'])?$record['icr:hasMax']:"";
			$output=sprintf("%s=%s%s;%s;%s;%s",$metric,$value,$unit,$warning,$critical,$max);
			$this->addPerformanceData($output);
		}
	}
}


//--------------------------------------------------------------------//
// SCRIPT
//--------------------------------------------------------------------//

$objPlugin = new SMHLMPlugin($strRevision, $strUsage, $strHelp, $arrOptions);

$objPlugin->execute();

?>
