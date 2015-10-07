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

include ("../../../lib/XML2Array.class.php");
error_reporting(E_ALL);
$xml = file_get_contents("hkb.xml");
$dom = new DOMDocument();
$dom->loadXML($xml);
$metrics = $dom->getElementsByTagName('ServiceMetric');
foreach ($metrics as $metric) {
	
	$records['metric']= $metric->getElementsByTagName('hasMetricName')->item(0)->nodeValue;
	$records['time']= $metric->getElementsByTagName('atTime')->item(0)->nodeValue;
	$records['value']= $metric->getElementsByTagName('hasMetricValue')->item(0)->nodeValue;
	$records['unit']= $metric->getElementsByTagName('hasMetricUnit')->item(0)->nodeValue;
	
	
	$records['dependsOn']= $metric->getElementsByTagName('dependsOn')->item(0)->attributes->item(0)->nodeValue;
	$records['hostname']= $metric->getElementsByTagName('hasName')->item(0)->attributes->item(0)->nodeValue;
	
	$w=$metric->getElementsByTagName('hasWarning');
	
	$records['warning']= $w->length!=0?$w->item(0)->nodeValue:"";
	
	$w=$metric->getElementsByTagName('hasCritical');
	$records['critical']= $w->length!=0?$w->item(0)->nodeValue:"";
	
	$w=$metric->getElementsByTagName('hasMax');
	$records['max']= $w->length!=0?$w->item(0)->nodeValue:"";
	
	var_dump($records);
	
	
	/*$metric['metric']=*/ //echo $metric->getElementsByTagName('icr:hasMetricName')->nodeValue, PHP_EOL;
	/*$metric['time']=*/ //echo $metric->getElementsByTagName('icr:atTime')->nodeValue, PHP_EOL;
	/*$metric['value']=*/ //echo $metric->getElementsByTagName('icr:hasMetricValue')->nodeValue, PHP_EOL;
	/*$metric['dependsOn']=*/ //echo $metric->getElementsByTagName('icr:dependsOn']['@attributes']['resource'];
	/*$metric['hostname']=*/ //echo $metric->getElementsByTagName('icr:hasName']['@attributes']['resource'];
	/*$metric['unit']=*/ //echo $metric->getElementsByTagName('icr:hasMetricUnit')->nodeValue, PHP_EOL; //?$record['icr:hasMetricUnit']:"";
	/*$metric['warning']=*/ //echo $metric->getElementsByTagName('icr:hasWarning')->nodeValue, PHP_EOL; //?$record['icr:hasMetricUnit']:"";
	/*$metric['critical']=*/ //echo $metric->getElementsByTagName('icr:hasCritical')->nodeValue, PHP_EOL; //?$record['icr:hasCritical']:"";
	/*$metric['max']=*/ //echo $metric->getElementsByTagName('icr:hasMax')->nodeValue, PHP_EOL; //?$record['icr:hasMax']:0;
}
/*$records = XML2Array::createArray($xml);
var_dump($records); exit();
foreach ($records["rdf:RDF"]['icr:ServiceMetric'] as $record)
{
	var_dump($record['icr:atTime']['@value']);
}*/


