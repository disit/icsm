<?php
include "../../lib/xml2Array.class.php";
include "../../lib/array2xml.class.php";
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


spl_autoload_register("load");



$m = new SM_Monitor();
$r = $m->getInternalIdbyDescription("urn:cloudicaro:DataCenter:TEST-008");

exit();

$ui=new SM_ConfiguratorUIController();
$ui->configurator_configuration_xml($_GET['id']);
exit();



$xml=file_get_contents("output.xml");
//print "<pre>".$xml."</pre>";
$v = XML2Array::createArray($xml);
//print_r((object)$v['configuration']);
$o = json_decode(json_encode($v['configuration']));
//print_r($o);

$conf = new Configuration();
$conf->write($o);
//echo $conf->validate();
$conf->save();exit();
if($conf->load($_GET['id']))
{
	
//
//var_dump(($conf));

//$conf->delete($_GET['id']);
}
