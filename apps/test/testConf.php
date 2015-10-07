<?php

include "../../lib/xml2Array.class.php";
include "../../lib/array2xml.class.php";
include "../../lib/XSLT/XSLT_Processor.class.php";
include "../../system/sm_Database.class.php";
include "../../system/sm_Module.class.php";
include "../../system/sm_Logger.class.php";
include "../../system/sm_Config.class.php";
include "../../system/functions.inc.php";




function load($class)
{
	if(file_exists("../models/".$class.".class.php"))
		include "../models/".$class.".class.php";
	else if(file_exists("../".$class.".class.php"))
		include "../".$class.".class.php";
	else if(file_exists("../controllers/".$class.".class.php"))
		include "../controllers/".$class.".class.php";
	else if(file_exists("../includes/".$class.".class.php"))
		include "../includes/".$class.".class.php";
}

error_reporting(E_ALL);
ini_set("display_errors", "on");
spl_autoload_register("load");
//set_error_handler(array("sm_Logger","logErrorHandler"));
sm_Logger::$logfolder="./";
sm_Logger::removeLog();

//$NagiosConf->execute();
//$NagiosConf->save();
$nagiosConf=new SM_NagiosConfigurator();
$conf=$nagiosConf->getConfigurationData("*",8);
$dom=Array2XML::createXML("configuration",$conf['configuration']);
$nagiosXML = $nagiosConf->mapNagiosData($dom->saveXML(),true);
if($nagiosXML)
{
	$nagios=XML2Array::createArray($nagiosXML);
	$nagiosConf->preNagiosData($nagios);
	$nagiosConf->writeNagiosConfiguration($nagios);
}