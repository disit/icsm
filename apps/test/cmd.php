<?php
/*$subject='php $USER1$/check_php.php -H $HOSTADDRESS$ -u $ARG1$ -p $ARG2$ -m $ARG5$ -args "$ARG6$" $ARG7$';
//preg_match("/(?<=[-{1,2}|/])(?<name>[a-zA-Z0-9]*)[ |:|\"]*(?<value>[\w|.|?|=|&|+| |:|/|\\]*)(?=[ |\"]|$)/", str_replace("$","",$subject),$matches);
//str_replace("$","",$subject)
var_dump(str_replace("$","",$subject));
preg_match_all('/-{1,2}?([A-Za-z0-9]+)?\s+"{0,1}\$(ARG[0-9]+)\$"{0,1}/', $subject, $matches);
var_dump($matches);
foreach($matches[1] as $i=>$v)
	$args[$v]=$matches[2][$i];
var_dump($args);*/

include "../SM_IcaroApp.php";
include "../../system/functions.inc.php";
error_reporting(E_ALL);
ini_set("display_errors",1);
echo $_SERVER['DOCUMENT_ROOT']."<br>";
echo SM_IcaroApp::getFolderUrl('js');