<?php 
$v="time=0.005268s;;;0.000000 size=2378B;;;0";
$perf_data=array();
preg_match_all('/\'?([A-Za-z_][A-Za-z0-9\s:_]+)\'?=(.+;*)/', $v,$perf_data);
var_dump($perf_data);
echo("<br>");
echo("<br>");
preg_match_all('/\'?([A-Za-z_][A-Za-z0-9\s:_]+)\'?=([A-Za-z0-9;\.]+)/', $v,$perf_data);
var_dump($perf_data);

?>