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

define('NAGIOSMONITORVERSION',"1.0");


class SM_NagiosMonitor implements sm_Module
{
	protected $db;
	protected $regExp;
	function __construct()
	{
		$this->db=sm_Database::getInstance();
		$this->regExp="/'?([\/0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)#\/\-]+)'?=([A-Za-z0-9%;\/#\.]+)/";
	}
	
	public function getInfo()
	{
		$info['name']="SM Monitor for Nagios Api";
		$info['version']=NAGIOSMONITORVERSION;
		return $info;
	}
	
	
	/******************************************************************************************************/
	function graph_list_old($host_name,$ip)
	{
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		//va recuperato l'informazione per impostare il filtro
		//$host_alias=$segment_obj[$segment]->getdescription();
		$data=array();
		$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($host_name).'@'.$ip);
		$live->setFilters($filters);
		$live->setColumns(array("perf_data","description","display_name")); //,"state"));
		$result = json_decode($live->execute());
		$data['_HOST_'][]=array(
				"title"=>"Host Data",
				"subtitle"=>"Round Trip Times",
				"metric"=>"_HOST_:0"
		);
		$data['_HOST_'][]=array(
				"title"=>"Host Data",
				"subtitle"=>"Packets Lost",
				"metric"=>"_HOST_:1"
		);
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
				$description=$v[1];
				$display_name=$v[2];
				//if(preg_match_all('/\'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)\'?=/', $v[0],$perf_data_array))
				if(preg_match_all($this->regExp, $v[0],$perf_data_array))
				{
					$perf_data_array=array_splice($perf_data_array,1);
					foreach($perf_data_array[0] as $j=>$perf)
					{
						//$metricParam=str_replace(" ","",$description).":".$j;
						$metricParam=$description.":".$j;
						
						$data[$display_name][]=array(
								"title"=>str_replace("_"," ",$display_name),
								"subtitle"=>str_replace("_"," ",$perf),
								"description"=>$description,
								"metric"=>$metricParam,
								"submetric"=>$j,

						);
						
					}
				}
			}
		}
		return $data;
	}
	
	function graph_list($name,$selection,$type)
	{
		$live=new SM_LiveStatusClient();
		$data=array();
		if($type=="host")
		{
			$live->setTable("services");
			$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($name).'@'.$selection);
			$data['_HOST_'][]=array(
						
					"title"=>"Host Data",
					"subtitle"=>"Round Trip Times",
					"metric"=>"_HOST_:0"
			);
			$data['_HOST_'][]=array(
					"title"=>"Host Data",
					"subtitle"=>"Packets Lost",
					"metric"=>"_HOST_:1"
			);
			
		} 
		if($type=="application" || $type=="servicegroup" || $type=="service")
		{
			$live->setTable("servicesbygroup");
			$filters=array("servicegroup_alias"=>$name,"host_address"=>$selection);
		}
		if(isset($filters))
				$live->setFilters($filters);
		$live->setColumns(array("perf_data","description","display_name","pnpgraph_present"));
		$result = json_decode($live->execute());
		
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
				$description=$v[1];
				$display_name=$v[2];
				//if(preg_match_all('/\'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)\'?=/', $v[0],$perf_data_array))
				if(preg_match_all($this->regExp, $v[0],$perf_data_array) && $v[3])
				{
					$perf_data_array=array_splice($perf_data_array,1);
					foreach($perf_data_array[0] as $j=>$perf)
					{
						//$metricParam=str_replace(" ","",$description).":".$j;
						$metricParam=$description.":".$j;
	
						$data[$display_name][]=array(
								"title"=>str_replace("_"," ",$display_name),
								"subtitle"=>str_replace("_"," ",$perf),
								"description"=>$description,
								"metric"=>$metricParam,
								"submetric"=>$j,
	
						);
	
					}
				}
				else if($v[3])
				{
					$j=0;
					$metricParam=$description.":".$j;
					
					$data[$display_name][]=array(
							"title"=>str_replace("_"," ",$display_name),
							"subtitle"=>str_replace("_"," ",$display_name),
							"description"=>$description,
							"metric"=>$metricParam,
							"submetric"=>$j,
					
					);
				}
			}
		}
		return $data;
	}
	
	function graph_data($host_name,$ip,$metric){
		$data=array();
		$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($host_name)."@".$ip);
		$subselection=0;
		
		if(isset($metric))
		{
			if(strstr($metric,":")!==false)
			{
				$srvs=explode(":",$metric);
				$filters['description']=($srvs[0]);
				$subselection=$srvs[1];
			}
			else
				$filters['description']=$metric;
		}
		if($filters['description']!="_HOST_")
		{
			$live=new SM_LiveStatusClient();
			$live->setTable("services");
			$live->setFilters($filters);
			$live->setColumns(array("perf_data","description","display_name","pnpgraph_present")); //,"state"));
			$result = json_decode($live->execute());
			if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
			{
				foreach($result[1] as $i=>$v)
				{
					$description=$v[1];
					$display_name=$v[2];
					//	var_dump($v[0]);echo("<br>");
					//if(preg_match_all("/'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)#\/]+)'?=([A-Za-z0-9%;\/#\.]+)/", $v[0],$perf_data_array))
					if(preg_match_all($this->regExp, $v[0],$perf_data_array))
					{
						//	var_dump($perf_data_array);echo("<br>");
						$perf_data_array=array_splice($perf_data_array,1);
						//	var_dump($perf_data_array);echo("<br>");
						foreach($perf_data_array[0] as $j=>$perf)
						{
							if($j!=$subselection)
								continue;
							$metricParam=$description;
							$data=array(
									"title"=>str_replace("_"," ",$description),
									"subtitle"=>str_replace("_"," ",$perf),
									"metric"=>$metricParam,
									"submetric"=>$subselection,
							);
						}
					}
					else if($v[3])
					{
						
						$metricParam=$description;						
						$data=array(
								"title"=>str_replace("_"," ",$description),
								"subtitle"=>str_replace("_"," ",$display_name),
								"metric"=>$metricParam,
								"submetric"=>$subselection,
									
						);
					}
				}
			}
		}
		else
		{
			$data=array(
					"title"=>"Host Data",
					"subtitle"=>$subselection==0?"Round Trip Times":"Packets Lost",
					"metric"=>"_HOST_",
					"submetric"=>$subselection,
						
			);
		}
		return $data;
	}
	
	//function meters($host_name,$ip){ //($id,$segment,$sid,$metric='_HOST_'){
	function _meters($name,$selection) //,$type)
	{
	
	
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		
		/*
		 * !!!! ATTENZIONE: va recuperato l'informazione per impostare il filtro !!!!
		 */
		
		$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($name).'@'.$selection); //"test-192.168.0.111");
		
		
		$live->setFilters($filters);
		$live->setColumns(array("perf_data","description","state","last_check","plugin_output"));
		
		
		$result = json_decode($live->execute());
		//sm_Logger::write($result);
		$data=array();
		
		$data['meters']=array();
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
				$description=$v[1];
				//$d=explode("; ",$v[0]);
				$output=$v[4];
				//preg_match_all("/'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)'?=([A-Za-z0-9%;\/#\.]+)/", $v[0],$perf_data_array);
				preg_match_all($this->regExp, $v[0],$perf_data_array);
				
				//echo("Primo<br>");
				//var_dump($perf_data_array);
				//echo("<br>");
				$d=$perf_data_array[0];
				foreach($d as $i=>$meters)
				{
					//var_dump($meters);
					//echo("<br>");
					//[A-Za-z0-9;\.]+
					//	if(preg_match_all('/\'?([A-Za-z_][A-Za-z0-9\s:_]+)\'?=(.+;*)/', $meters,$perf_data_array))
					//if(preg_match_all('/\'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)\'?=([A-Za-z0-9%;\/#\.]+)/', $meters,$perf_data_array))
					if(preg_match_all($this->regExp, $meters,$perf_data_array))
					{
						//	var_dump($perf_data_array);
						//	echo("<br>");
						$values=rtrim($perf_data_array[2][0],";");
							
						$perf=explode(";",$values);
						//	var_dump($perf[0]);
						//	echo("<br>");
						preg_match_all('/([0-9\.]+)([a-zA-Z %\/#\-]*)/',$perf[0], $value);
						//	var_dump($value);
						//	echo("<br>");
						$unit = $value[2][0];
						if(stripos($perf_data_array[1][0],"percent")>0)
							$unit='%';
						if($unit!="%" && count($perf)==1)
						{
	
							$val = $value[1][0];
							$newunit="";
							$this->convert_unit($val,$unit,$newunit);
							$unit=$newunit;
							//$val=intval($val);
							$min=0;
							$max=2*(intval($val)>=1?intval($val):1);
								
							$data['meters'][]=array(
									"title"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
									"value"=>$val,
									"min"=>$min,
									"max"=>number_format($max,2,'.', ''),
									"warning"=>75,
									"critical"=>90,
									"state"=>$v[2],
									"type"=>"gauge",
									"unit"=>$unit,
									"id"=>str_replace(" ","_",$description)."_".$i."_".str_replace(".","_",$selection),
									"last_check"=>$v[3]
							);
						}
						else
						{
							$val = $value[1][0];
							
							if($unit!='%')
							{
								if(isset($perf[4]) && $perf[4]!=0)
								{
									//$this->convert_unit($val,$unit);
									
									$min=0;
									$max=$perf[4];
									$w=isset($perf[1]) && $perf[1]>99 && $perf[1]!=99?intval(($perf[1]/$max)*100):80;
									$c=isset($perf[2]) && $perf[2]>99 && $perf[2]!=99?intval(($perf[2]/$max)*100):90;
									$this->convert_unit($max,$unit,$newunit);
									$this->convert_unit($val, $unit, $newunit);
									$unit=$newunit;
								}
								else
								{
									$min=0;
									$max=2*($val>=1?$val:1);
									if(isset($perf[2]))
										$max=max(array($max,intval($perf[2]*1.1)));
									$w=isset($perf[1]) && $perf[1]>0 && $perf[1]!=99?intval(($perf[1]/$max)*100):80;
									$c=isset($perf[2]) && $perf[2]>0 && $perf[2]!=99?intval(($perf[2]/$max)*100):90;
									$this->convert_unit($val, $unit, $newunit);
									$unit=$newunit;
								}
							}
							else
							{
								$max=100;
								$min=0;
								$w=isset($perf[1]) && $perf[1]>0 && $perf[1]!=99?$perf[1]:80;
								$c=isset($perf[2]) && $perf[2]>0 && $perf[2]!=99?$perf[2]:90;
							}
													
							$data['meters'][]=array(
									"title"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
									"value"=>$val,
									"min"=>$min,
									"max"=>number_format($max,2,'.', ''),
									"warning"=>$w,
									"critical"=>$c,
									"state"=>$v[2],
									"type"=>"gauge",
									"unit"=>$unit,
									"id"=>str_replace(" ","_",$description)."_".$i."_".str_replace(".","_",$selection),
									"state"=>$v[2],
									"last_check"=>$v[3]
							);
	
						}
	
					}
				}
			}
	
		}
		//exit();
		return $data;
	}
	
	function meters($name,$selection,$type,$metric=null)
	{
		return $this->meter($name,$selection,$type,$metric);
	}
	
	function meter($name,$selection,$type,$metric=null)
	{
		$live=new SM_LiveStatusClient();
		if($type=="host")
		{
			$live->setTable("services");
			$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($name).'@'.$selection);	
		} 
		if($type=="application" || $type=="servicegroup" || $type=="service")
		{
			$live->setTable("servicesbygroup");
			$filters=array("servicegroup_alias"=>$name);
			if(isset($selection))
				$filters["host_address"]=$selection;
		}
		if($type=="hostgroup" || $type=="hosts")
		{
			if(!isset($selection))
				$selection="";
			$live->setTable("servicesbyhostgroup");
			$filters=array("hostgroup_name"=>"~".$name.'@'.$selection);
		}
		if(isset($metric))
		{
			$filter=$this->translateInNagiosCheck($metric);
			$filters = array_merge($filters,array("display_name"=>$filter['metric'],"state"=>"<3"));
		}
		$live->setFilters($filters);
		$live->setColumns(array("perf_data","description","state","last_check","plugin_output","host_name","display_name","host_address"));
	
	
		$result = json_decode($live->execute());
		//sm_Logger::write($result);
		$data=array();
	
		$data['meters']=array();
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
				$description=$v[1];
				//$d=explode("; ",$v[0]);
				$output=$v[4];
				//preg_match_all("/'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)'?=([A-Za-z0-9%;\/#\.]+)/", $v[0],$perf_data_array);
				preg_match_all($this->regExp, $v[0],$perf_data_array);
	
				//echo("Primo<br>");
				//var_dump($perf_data_array);
				//echo("<br>");
				$d=$perf_data_array[0];
			
				foreach($d as $i=>$meters)
				{
					//var_dump($meters);
					//echo("<br>");
					//[A-Za-z0-9;\.]+
					//	if(preg_match_all('/\'?([A-Za-z_][A-Za-z0-9\s:_]+)\'?=(.+;*)/', $meters,$perf_data_array))
					//if(preg_match_all('/\'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)\'?=([A-Za-z0-9%;\/#\.]+)/', $meters,$perf_data_array))
					if(preg_match_all($this->regExp, $meters,$perf_data_array))
					{
						//	var_dump($perf_data_array);
						//	echo("<br>");
						$values=rtrim($perf_data_array[2][0],";");
							
						$perf=explode(";",$values);
						//sm_Logger::write($perf);
						//	echo("<br>");
						preg_match_all('/([0-9\.]+)([a-zA-Z %\/#\-]*)/',$perf[0], $value);
						//	var_dump($value);
						//	echo("<br>");
						$unit = isset($value[2][0])?$value[2][0]:"c";
						if(stripos($perf_data_array[1][0],"percent")>0)
							$unit='%';
						if($unit!="%" && count($perf)==1) //only perf[0]
						{
	
							$val = isset($value[1][0])?$value[1][0]:0;
							$val+=0;
							$max=2*(intval($val)>=1?intval($val):1);
							$newunit="";
							$this->convert_unit($max,$unit,$newunit);
							$this->convert_unit($val,$unit,$newunit);
							$unit=$newunit;
							//$val=intval($val);
							$min=0;
							
							$host=$type=="host"?$v[5]:str_replace("_"," ",$v[5]);
							$data['meters'][]=array(
									"title"=>$v[1],
									"display_name"=>$v[6],
									"metric"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
									"value"=>is_float($val)?sprintf("%01.2f",$val):$val,
									"min"=>$min,
									"max"=>number_format(floatval($max),2,'.', ''),
									"warning"=>75,
									"critical"=>90,
									"state"=>$v[2],
									"type"=>$max!=0?"gauge":"counter",
									"unit"=>$unit,
									"id"=>str_replace(" ","_",str_replace("@","-",$description))."_".$i."_".str_replace(".","_",$selection),
									"last_check"=>$v[3],
									"host"=>$host,
									"graphurl"=>"/monitor/img/".str_replace("@","/",$host)."/".$v[1].":".$i
							);
						}
						else
						{
							$val = isset($value[1][0])?$value[1][0]:0;
							$val+=0;
							if($unit!='%')
							{
								if(isset($perf[4]) && $perf[4]!=0)
								{
									//$this->convert_unit($val,$unit);
									$newunit="";
									$min=0;
									$max=$perf[4];
									$w=isset($perf[1]) && $perf[1]>99 && $perf[1]!=99?intval(($perf[1]/$max)*100):80;
									$c=isset($perf[2]) && $perf[2]>99 && $perf[2]!=99?intval(($perf[2]/$max)*100):90;
									$this->convert_unit($max,$unit,$newunit);
									$this->convert_unit($val, $unit, $newunit);
									//$this->convert_unit($w, $unit, $newunit);
									//$this->convert_unit($c, $unit, $newunit);
									$unit=$newunit;
								}
								else
								{
									$newunit="";
									$min=0;
									$max=2*($val>=1?$val:1);
									if(isset($perf[2]))
										$max=max(array($max,intval($perf[2]*1.1)));
									$w=isset($perf[1]) && $perf[1]>0 && $perf[1]!=99?intval(($perf[1]/$max)*100):80;
									$c=isset($perf[2]) && $perf[2]>0 && $perf[2]!=99?intval(($perf[2]/$max)*100):90;
									$this->convert_unit($max, $unit, $newunit);
									$this->convert_unit($val, $unit, $newunit);
									
									//$this->convert_unit($w, $unit, $newunit);
									//$this->convert_unit($c, $unit, $newunit);
									$unit=$newunit;
								}
							}
							else
							{
								$max=100;
								$min=0;
								$w=isset($perf[1]) && $perf[1]>0 && $perf[1]!=99?$perf[1]:80;
								$c=isset($perf[2]) && $perf[2]>0 && $perf[2]!=99?$perf[2]:90;
							}
							$host=$type=="host"?$v[5]:str_replace("_"," ",$v[5]);
							
							$data['meters'][]=array(
									"title"=>$v[1],
									"display_name"=>$v[6],
									"metric"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
									"value"=>is_float($val)?sprintf("%01.2f",$val):$val,
									"min"=>$min,
									"max"=>number_format(floatval($max),2,'.', ''),
									"warning"=>$w,
									"critical"=>$c,
									"state"=>$v[2],
									"type"=>$max!=0?"gauge":"counter",
									"unit"=>$unit,
									"id"=>str_replace(" ","_",str_replace("@","-",$description))."_".$i."_".str_replace(".","_",$selection),
									"state"=>$v[2],
									"last_check"=>$v[3],
									"host"=>$host,
									"graphurl"=>"/monitor/img/".str_replace("@","/",$host)."/".$v[1].":".$i
							);
	
						}
	
					}
				}
			}	
			/*if(count($data['meters'])==0)
				$data['meters'][]=array("host"=>$type=="host"?$v[5]:str_replace("_"," ",$v[5]));*/
				
		}
		
		//exit();
		return $data;
	}
	
	//GET%20servicesbygroup\\nFilter:%20servicegroup_alias%20=%20urn:cloudicaro:XLMSBalanced:eclap%20
	
	function controls($name,$selection,$type)
	{
		$live=new SM_LiveStatusClient();
		if($type=="host")
		{
			$live->setTable("services");
			$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($name).'@'.$selection);
		} 
		if($type=="application" || $type=="servicegroup" || $type=="service")
		{
			$live->setTable("servicesbygroup");
			$filters=array("servicegroup_alias"=>$name,"host_address"=>$selection);
		}
		$live->setFilters($filters);
		$live->setColumns(array("perf_data","description","state","last_check","next_check","active_checks_enabled","plugin_output","host_address","host_name","accept_passive_checks"));
		$result = json_decode($live->execute());
		$data=array();
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
					$mode="Active";
					if($v[9] && $v[5])
						$mode="Active/Passive";
					if($v[9] && !$v[5])
						$mode="Passive";
					$control=array(
						"host"=>$v[7],
						"check"=>$v[1],
						"state"=>$this->statusString($v[2]),
						"output"=>$v[6],
						"last check"=>date("d/m/y H:i:s",$v[3]),
						"next check"=>date("d/m/y H:i:s",$v[4]),
						"check type"=>$mode,
						"active"=>$v[5],
				);
					
			
				
				$data['controls'][$selection][]=$control;
			}
		}
		return $data;
	}
	
	function overallStatus($name,$selection,$type){//(SM_Configuration $conf){
		$live=new SM_LiveStatusClient();
		if($type=="host")
		{
			$live->setTable("hosts");
			$filters=array("host_name"=>SM_NagiosConfigurator::nagiosEscapeStr($name).'@'.$selection);
		}
		if($type=="application" || $type=="servicegroup")
		{
			$live->setTable("servicesbygroup");
			$filters=array("servicegroup_alias"=>$name,"host_address"=>$selection);
		}
		else
		{
			$live->setTable("hostgroups");
		//	$filters=array("name"=>SM_NagiosConfigurator::nagiosEscapeStr($conf->getname()).'@'.$conf->getidentifier());
			$filters=array("name"=>$name.'@'.$selection);
		}
		//"num_hosts","num_hosts_down","num_hosts_pending","num_hosts_unreach","num_hosts_up","num_services","num_services_crit",
		//"num_services_hard_crit","num_services_hard_ok","num_services_hard_unknown","num_services_hard_warn","num_services_ok",
		//"num_services_pending","num_services_unknown","num_services_warn","worst_host_state","worst_service_hard_state","worst_service_state"
		$live->setFilters($filters);
		$live->setColumns(array("num_hosts_up","num_hosts_down","num_services_ok","num_services_warn","num_services_crit"));
		$result = json_decode($live->execute());
		
		$status['hosts up']="";
		$status['hosts down']="";
		$status['services ok']="";
		$status['services warn']="";
		$status['services crit']="";
		
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
				$status['hosts up']=$v[0];
				$status['hosts down']=$v[1];
				$status['services ok']=$v[2];
				$status['services warn']=$v[3];
				$status['services crit']=$v[4];
			}
		}
		return $status;
	}
	
	
	function getFilters($SM_segment)
	{
		$filters=array();
		if(is_a($SM_segment,"Host"))
		{
			$ip = $SM_segment->getmonitor_ip_address();
			if(!empty($ip))
				 $filters[$ip]=$ip;
			else{
			 		foreach(explode(";", $SM_segment->getip_address()) as $i=>$p)
			 			$filters[$p]=$p;
			}
			 return $filters;
		}
		if(is_a($SM_segment,"Application"))
		{
			foreach ($SM_segment->getServices() as $s=>$service)
			{
				$host=$service->getHost();
				$ip=$host->getmonitor_ip_address();
				if(!empty($ip))
					$filters[$ip]=$host->getname();
				else {
					
						foreach(explode(";", $host->getip_address()) as $i=>$p)
							$filters[$p]=$host->getname();
					
				}
			}
			return $filters;
		}
		if(is_a($SM_segment,"Service"))
		{
				$host=$SM_segment->getHost();
				$ip=$host->getmonitor_ip_address();
				if(!empty($ip))
					$filters[$ip]=$host->getname();
				else {	
					foreach(explode(";", $host->getip_address()) as $i=>$p)
						$filters[$p]=$host->getname();
						
				}
			
			return $filters;
		}
		return $filters;	
	}
	
	function statusString($v)
	{
		$s="";
		switch($v){
			case 0:
				$s='<span class="label label-success">OK</span>';
			break;
			case 1:
				$s='<span class="label label-warning">WARN</span>';
			break;
			case 2:
				$s='<span class="label label-danger">CRIT</span>';
			break ;
			case 3:
				$s='<span class="label label-default">UNKN</span>';
			break;
		}
		return $s;
	}
	
	function stringToStatus($s)
	{
		$regx='/(^[~=<>!]+)\s*(.+)/';
		if(preg_match('/^(ok)/i', $s))
		{
			return 0;
		}
		if(preg_match('/^(warn)/i', $s))
		{
			return 1;
		}
		if(preg_match('/^(crit)/i', $s))
		{
			return 2;
		}
		if(preg_match('/^(unk)/i', $s))
		{
			return 3;
		}
		return -1;
	}
	
	/* Graph request */
	function graph($args=null)
	{
		$graph = new SM_NagiosPNP();
		$args['host']=SM_NagiosConfigurator::nagiosEscapeStr($args['host'])."@".$args['ip'];
		unset($args['ip']);
		return $graph->getGraph($args);
	}
	
	/* Stop/Pause Monitoring */
	function stop($host_name,$ip,$check)
	{
		$nagios=new SM_NagiosClient();
		return $nagios->disableCheck(SM_NagiosConfigurator::nagiosEscapeStr($host_name)."@".$ip,$check);
	}
	
	/* Start/Resume Monitoring */
	function start($host_name,$ip,$check)
	{
		$nagios=new SM_NagiosClient();
		return $nagios->enableCheck(SM_NagiosConfigurator::nagiosEscapeStr($host_name)."@".$ip,$check);
	}
	
	/* Reschedule an immediate check */
	function rescheduleCheck($host_name,$ip,$check)
	{
		$nagios=new SM_NagiosClient();
		if($nagios->rescheduleCheck(SM_NagiosConfigurator::nagiosEscapeStr($host_name)."@".$ip,$check))
		{
			return true;
		}
		return false;
	}
	
	/* Insert Configuration in Monitoring Tool*/
	function insert($id)
	{
		set_time_limit(3600);
		$nagiosConf= new SM_NagiosConfigurator();
		return $nagiosConf->configure($id);
	
	}
	
	/**
	 *  @desc Remove Configuration from Monitoring Tool
	 *  
	 */
	function remove($id)
	{
		$nagiosConf= new SM_NagiosConfigurator();
		return $nagiosConf->remove_configuration($id);
	}
	
	/** 
	 * @desc Get All Services in Monitoring Tool 
	 * 
	 * 
	 */
	function checksDefintions($where=null,$fields=null){
		$nagiosQL = new SM_NagiosQL();
		return $nagiosQL->getAllServices($where,$fields);
		
	}
	
	/** @desc Get All Metrics in Monitoring Tool
	 *
	 *
	 */
	function checksMetrics($status=null){
		if($status && $status=="active")
			$where=array('hostgroup_name'=>1,"active"=>1);
		else if($status && $status=="disabled")
			$where=array('hostgroup_name'=>1,"active"=>0);
		else 
			$where=array('hostgroup_name'=>1);
		$fields=array("service_description","display_name","active");
		$nagiosQL = new SM_NagiosQL();
		$services = $nagiosQL->getAllServices($where,$fields);
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		$live->setColumns(array("perf_data","description","display_name"));
		$live->setLimit(1);
		foreach($services as $i=>$service){
			$services[$i]['active']=$service['active']?"Yes":"No";
			$filters=array("description"=>$service["service_description"],"perf_data"=>"!= ");
			$live->setFilters($filters);
			$result = json_decode($live->execute());
			if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
			{
				foreach($result[1] as $k=>$v)
				{
					$description=$v[1];
					$display_name=$v[2];
					//if(preg_match_all('/\'?([0-9A-Za-z_][\\\\A-Za-z0-9 :_%\(\)\/#]+)\'?=/', $v[0],$perf_data_array))
					if(preg_match_all($this->regExp, $v[0],$perf_data_array))
					{
						$perf_data_array=array_splice($perf_data_array,1);
						foreach($perf_data_array[0] as $j=>$perf)
						{
							//$metricParam=str_replace(" ","",$description).":".$j;
							/*$metricParam=$description.":".$j;
			
							$data[$display_name][]=array(
									"title"=>str_replace("_"," ",$display_name),
									"subtitle"=>str_replace("_"," ",$perf),
									"description"=>$description,
									"metric"=>$metricParam,
									"submetric"=>$j,
			
							);*/
							$services[$i]['param'][]=str_replace("_"," ",$perf);
						}
					}
				}
			}
			
		}
		return $services;
	
	}
	
	/** Count Hosts
	 * 
	 * @param string $type
	 */
	
	function count_hosts($type='host',$where=array())
	{
		$count=array();
		$limit=$howmany(1+$page);
		$live=new SM_LiveStatusClient();
		$live->setTable("hosts");
		$filters=array("notes"=>$type);
		$live->setStats(array("sum"=>"notes"));
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$filters=array_merge($filters,$where);
		}
		$live->setFilters($filters);
		$result = json_decode($live->execute());
		
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			$count['Count']=$result[1][0];
		
		}
		return $count;
	}
	
	/** Get Hosts
	 * 
	 * @param string $type
	 * @param unknown $where
	 * @param unknown $fields
	 * @return multitype:NULL
	 */
	
	function hosts($type='host',$where=array(),$fields=array(),$howmany=10,$page=1){
		$hosts=array();
		$limit=$howmany*(1+$page);
		$live=new SM_LiveStatusClient();
		$live->setTable("hosts");
		$filters=array("notes"=>$type);
		
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$filters=array_merge($filters,$where);
		}
		if(count($fields)>0)
		{
			$p=array_search('description',$fields);
			if($p!==false)
			{
				$fields[$p]="alias";
				
			}
			$live->setColumns($fields);
		}
		$live->setLimit($limit);
		$live->setFilters($filters);
		$result = json_decode($live->execute());
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			if($howmany>0)
			{
				$offset=$howmany*($page-1);
				$hosts=array_slice($result[1], $offset,$howmany);
			}
			else 
				$hosts=$result[1];
			
				
		}
	
		return $hosts;
	}
	
	function services($type='host',$where=array(),$fields=array(),$howmany=10,$page=1){
		$services=array();
		$limit=$howmany*($page);
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		$filters=array("host_notes"=>$type);
	
		if(count($where)>0)
		{
			if(isset($where['host_description']))
			{
				$where['host_alias']=$where['host_description'];
				unset($where['host_description']);
			}
			if(isset($where['metric']))
			{
				$metric=$this->translateInNagiosCheck($where['metric']);
				$where['display_name']=$metric['metric'];
				unset($where['metric']);
			}
			if(isset($where['state']) && is_string($where['state']))
			{
				$where['state']=$this->stringToStatus($where['state']);
			}
			$filters=array_merge($filters,$where);
		}
		if(count($fields)>0)
		{
			$p=array_search('host_description',$fields);
			if($p!==false)
			{
				$fields[$p]="host_alias";
	
			}
			$live->setColumns($fields);
		}
		if($howmany>0)
			$live->setLimit($limit);
		$live->setFilters($filters);
		$result = json_decode($live->execute());
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			if($howmany>0)
			{
				$offset=$howmany*($page-1);
				$services=array_slice($result[1], $offset,$howmany);
			}
			else
				$services=$result[1];
				
	
		}
	
		return $services;
	}
	
	/** Get Hosts status
	 * 
	 * @param string $type
	 * @return multitype:
	 */ 
	
	function hosts_status($type='host',$where=array()){
		$status=array();
		$live=new SM_LiveStatusClient();
		$live->setTable("hosts");
		$filters=array("notes"=>$type);
		$stats=array();
		$stats[]=array("state",0);
		$stats[]=array("state",1);
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$filters=array_merge($filters,$where);
			
		}
		/*if(count($fields)>0)
		{
			$p=array_search($fields,'description');
			if($p!==false)
			{
				$fields[$p]="alias";
			}
			
		}*/
		
		$live->setFilters($filters);
		$live->setStats($stats);
		$result = json_decode($live->execute());
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
					$status['Up']=$result[1][0][0];
					$status['Down']=$result[1][0][1];
		}
		
		return $status;
	}
	
	function host_ping($type='host',$where=array())
	{
	
		$data=array(
				"value"=>"N.A.",
				"unit"=>"",);
		if(!SM_LiveStatusClient::isAlive())
			return $data;
		$live=new SM_LiveStatusClient();
		$live->setTable("hosts");
		$filters=array("notes"=>$type);
		
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$filters=array_merge($filters,$where);	
		}
		$live->setColumns(array("perf_data"));
		$live->setFilters($filters);
		
		$result = json_decode($live->execute());

		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			$ping=$result[1][0][0]; 
			if(preg_match_all($this->regExp, $ping,$perf_data_array))
			{
				//	var_dump($perf_data_array);
				//	echo("<br>");
				$values=rtrim($perf_data_array[2][0],";");
					
				$perf=explode(";",$values);
				//	var_dump($perf[0]);
				//	echo("<br>");
				preg_match_all('/([0-9\.]+)([a-zA-Z %\/#\-]*)/',$perf[0], $value);
				//	var_dump($value);
				//	echo("<br>");
				$unit = isset($value[2][0])?$value[2][0]:"c";
				
				//if(count($perf)==1)
				{
					
					$val = isset($value[1][0])?$value[1][0]:0;
					$val+=0;
					
					$min=0;
					$w=isset($perf[1]) && $perf[1]>0 && $perf[1]!=99?$perf[1]:0;
					$c=isset($perf[2]) && $perf[2]>0 && $perf[2]!=99?$perf[2]:2*val;
					$max=1.2*$c;
					$data=array(
							"meter"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
							"title"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
							"id"=>trim(str_replace("_"," ",$perf_data_array[1][0])),
							"value"=>is_float($val)?sprintf("%01.2f",$val):$val,
							"unit"=>$unit,
							"min"=>$min,
							"max"=>number_format(floatval($max),2,'.', ''),
							"warning"=>number_format(floatval($w/$max*100),2,'.', ''),
							"critical"=>number_format(floatval($c/$max*100),2,'.', ''),
							"type"=>"gauge",
				);
					
					

				}
			}
			
		}
	
		return $data;
	}
	
		
	/** Get Services status
	 * 
	 * @param unknown $query
	 * @return multitype:NULL
	 */
	 
	
	function services_status($where=array()){
		$status=array();
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		$cols=array();
		$stats[]=array("state",0);
		$stats[]=array("state",1);
		$stats[]=array("state",2);
		$stats[]=array("state",3);
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$live->setFilters($where);
		}
		$live->setStats($stats);
		$result = json_decode($live->execute());

		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			
			$status['OK']=$result[1][0][0];
			$status['Warning']=$result[1][0][1];
			$status['Critical']=$result[1][0][2];
			$status['Unknown']=$result[1][0][3];
			$status['Total']=$result[1][0][0]+$result[1][0][1]+$result[1][0][2]+$result[1][0][3];
		}
		//var_dump($status);
		return $status;
	}
	
	function application_services_status($where=array()){
		$status=array();
		$live=new SM_LiveStatusClient();
		$live->setTable("servicegroups");
		$live->setColumns(array("num_services_ok","num_services_warn","num_services_crit","num_services_unknown"));
		if(count($where)>0)
		{
			if(isset($where['description']))
			{
				$where['alias']=$where['description'];
				unset($where['description']);
			}
			$live->setFilters($where);
		}
		
		$result = json_decode($live->execute());
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
				
			$status['OK']=$result[1][0][0];
			$status['Warning']=$result[1][0][1];
			$status['Critical']=$result[1][0][2];
			$status['Unknown']=$result[1][0][3];
			$status['Total']=$result[1][0][0]+$result[1][0][1]+$result[1][0][2]+$result[1][0][3];
		}
		//var_dump($status);
		return $status;
	}
	
	function checks_list_count($where=array(),$state=null)
	{
		$data=array();
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		$stats=array();
		$stats[]=array("state",0);
		$stats[]=array("state",1);
		$stats[]=array("state",2);
		$stats[]=array("state",3);
		if(count($where)>0)
		{
			$where = explode(";", "~".str_replace(";",";~",implode(";",$where)));
			$filters=array();
			$searchIn=array("description","display_name","host_name","host_alias");
			foreach ($searchIn as $k)
			{
				$filters[$k]=$where;
			}
			$live->setFilters($filters,"Or");
		}
		if(!is_null($state))
			$live->setStats(array($stats[$state]));
		else 
			$live->setStats($stats);

		$result = json_decode($live->execute());
		$total=0;
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			if(!is_null($state))
				$total=$result[1][0][0];
			else 
			{
				$status[0]=$result[1][0][0];
				$status[1]=$result[1][0][1];
				$status[2]=$result[1][0][2];
				$status[3]=$result[1][0][3];
				$total=$result[1][0][0]+$result[1][0][1]+$result[1][0][2]+$result[1][0][3];
			}
		}
		return $total;
		
	}
	
	function checks_list($where=array(),$state=null,$howmany=10,$page=1)
	{
		$data=array();
		
		$live=new SM_LiveStatusClient();
		$live->setTable("services");
		$fields=array("description","display_name","host_name","state", "plugin_output","last_check","next_check","host_alias");
		$live->setColumns($fields);
		
		if(count($where)>0)
		{
			$where = explode(";", "~".str_replace(";",";~",implode(";",$where)));
			$filters=array();
			$searchIn=array("description","display_name","host_name");
			foreach ($searchIn as $k)
			{
				$filters[$k]=$where;
			}
			$live->setFilters($filters,"Or");
		}
		//$fields=array("description","display_name","state","last_check","next_check","active_checks_enabled","plugin_output","host_address","host_name");
		if(!is_null($state))
			$live->setFilters(array("state"=>$state));

		$live->setLimit($howmany*(1+$page));
		
		$result = json_decode($live->execute());
	
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			if($howmany>0)
			{
				$offset=$howmany*($page-1);
				$res=array_slice($result[1], $offset,$howmany);
			}
			else 
				$res=$result[1];
			
			foreach ($res as $r=>$v)
			{
				$v[3]=$this->statusString($v[3]);
				$v[6]=date("d/m/y H:i:s",$v[6]);
				$v[5]=date("d/m/y H:i:s",$v[5]);
				$data[$r]=array_combine($fields,$v);
			}
		}
		return $data;
	
	}
	
	/**
	 * 
	 * @param number $limit
	 * @param string $time
	 * @return Ambigous <multitype:, multitype:string unknown NULL >
	 */
	
	function all_last_events($limit=5,$time=null)
	{
		if(!isset($time))
			$time = time()-4*3600;
		$live=new SM_LiveStatusClient();
		$live->setTable("log");
		$filters=array("time"=>">= ".$time,"current_service_active_checks_enabled"=>1);
		$live->setLimit($limit);
		$live->setFilters($filters);
		$live->setColumns(array("current_service_perf_data","current_service_description","state","time","current_service_next_check","current_service_active_checks_enabled","plugin_output","host_address","host_name","state_type","type"));
		$result = json_decode($live->execute());
		$data=array();
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
		
				$control=array(
						"host"=>substr($v[8], 0,strpos($v[8], "@")),
						"address"=>$v[7],
						"state"=>$this->statusString($v[2]),
						"event"=>$v[10],
						"level"=>$v[9],
						"name"=>$v[1],
						"output"=>$v[6],
						"time"=>date("d/m/y H:i:s",$v[3]),
						"next check"=>date("d/m/y H:i:s",$v[4]),
						"active"=>$v[5]
				);
					
		
				$data['checks'][]=$control;
			}
		}
		return $data;
	}
	
	function last_events($type,$where=array(),$limit=null,$time=null)
	{
		if(!isset($time))
			$time = time()-4*3600;
		$live=new SM_LiveStatusClient();
		$live->setTable("log");
		$filters=array("time"=>">= ".$time,"current_service_active_checks_enabled"=>1);
		if(count($where)>0)
		{
			if(isset($where['description']) && $type=="hosts")
			{
				$where['current_host_alias']=$where['description'];
				unset($where['description']);
			}
			$filters=array_merge($filters,$where);
		}
		if(isset($limit))
			$live->setLimit($limit);
		$live->setFilters($filters);
		$live->setColumns(array("current_service_perf_data","current_service_description","state","time","current_service_next_check","current_service_active_checks_enabled","plugin_output","host_address","host_name","state_type","type"));
		$result = json_decode($live->execute());
		$data=array();
		$data['checks']=array();
		if($result[0][1]=="OK" && isset($result[1]) && count($result[1])>0)
		{
			foreach($result[1] as $i=>$v)
			{
	
				$control=array(
						"host"=>substr($v[8], 0,strpos($v[8], "@")),
						"address"=>$v[7],
						"state"=>$this->statusString($v[2]),
						"event"=>$v[10],
						"level"=>$v[9],
						"name"=>$v[1],
						"output"=>$v[6],
						"time"=>date("d/m/y H:i:s",$v[3]),
						"next check"=>date("d/m/y H:i:s",$v[4]),
						"active"=>$v[5]
				);
					
	
				$data['checks'][]=$control;
			}
		}
		return $data;
	}
	
	/**
	 * 
	 * @param unknown $metric
	 * @param string $type
	 */
	function metric_stats($metric,$type="host")
	{
		$metric = $this->translateInNagiosCheck($metric);
		$stats = $this->services_status(array("display_name"=>$metric['metric'],"host_notes"=>$type));
		$stats['Name']=$metric['label'];
		return $stats;
		
	}
	function convert_unit(&$v, $from, &$to)
	{
	
		if(strcasecmp($from,"bytes")==0 || strcasecmp($from,"B")==0) //preg_match("/[MGTPEZY]?B/i",$from)
		{
			if($to!="")
			{
				$r=$this->byteFormat($v,"B",$to);
				$v=$r['value'];
				
			}
			else 
			{
				$r=$this->byteFormat($v,"B");
				$v=$r['value'];
				$to=$r['unit'];
			}
		}
		else if(preg_match("/[MGTPEZY]{1}B/i",$from))
		{
			if($to!="")
			{
				$r=$this->byteFormat($v,$from,$to);
				$v=$r['value'];
				
			}
			else 
			{
				$r=$this->byteFormat($v,$from);
				$v=$r['value'];
				$to=$r['unit'];
			}	
		}
		else if(preg_match("/s[ecs]/i",$from))
		{
			$to=$from;
		}
		else if(strcasecmp($from,"c")==0 || $from=="")
		{
			$to="#";
		}
		else
			$to=$from;
	}
	
	function time_elapsed_string($datetime) {
	  //  $now = new DateTime;
	   // $ago = new DateTime($datetime);
	    $diff =  new DateTime($datetime);//$now->diff($ago);
	
	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;
	
	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	        		$v = $diff->$k;
	        		$u = $k;
	        } else {
	            unset($string[$k]);
	        }
	    }
	   
	    return array("value"=>$v,"unit"=>$u);
	}
	
	function byteFormat($bytes, $from="", $unit = "", $decimals = 2) {
		$units = array('B' => 0, 
				'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
				'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8, 
				'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4,
				'P' => 5, 'E' => 6, 'Z' => 7, 'Y' => 8
		);
	
		$value = 0;
		
		if ($bytes > 0) {
			if(array_key_exists($from, $units))
			{
				$bytes *= pow(1024,$units[$from]);
			}
			// Generate automatic prefix by bytes
			// If wrong prefix given
			if (!array_key_exists($unit, $units)) {
				$pow = floor(log($bytes)/log(1024));
				$unit = array_search($pow, $units);
			}
				
			// Calculate byte value by prefix
			$value = (floatval($bytes)/pow(1024,$units[$unit]));
		}
		else 
		{
			$value=$bytes;
			$unit=$from;
		}
	
		// If decimals is not numeric or decimals is less than 0
		// then set default value
		if (!is_numeric($decimals) || $decimals < 0) {
			$decimals = 2;
		}
	
		// Format output
		return array("value"=>sprintf('%.' . $decimals . 'f ', $value),"unit"=>$unit);
		//return sprintf('%.' . $decimals . 'f '.$unit, $value);
	}
	
	
	function translateInNagiosCheck($name)
	{
		//$vocabulary=$this->loadMeterDefinitionMap();
		$vocabulary=array(
			"Disk"=>array("label"=>"Disk Usage","metric"=>"Disk Usage"),
			"Memory"=>array("label"=>"Physical Memory Used","metric"=>"Physical Memory Used"),
			"CPU"=>array("label"=>"CPU","metric"=>"CPU AVG"),
			"Network"=>array("label"=>"Network","metric"=>"~ [VMWARE ]*Net Traffic$")
		);
		if(isset($vocabulary[$name]))
			return $vocabulary[$name];
		else
			return array("label"=>$name,"metric"=>$name);
	}
	
	static function install($db)
	{
		if(class_exists("SM_Monitor"))
		{
			SM_Monitor::registerTool(__CLASS__);
			sm_Logger::write("Registered ".__CLASS__." to SM_Monitor");
		
		}
		
	}
	
	static function uninstall($db)
	{
		if(class_exists("SM_Monitor"))
		{
			SM_Monitor::unregisterTool(__CLASS__);	
			sm_Logger::write("Unregistered ".__CLASS__." from SM_Monitor");
		}
	}
	
	function getChecksStateLabel(){
		return array("0"=>"Ok","1"=>"Warning","2"=>"Critical","3"=>"Unknown");
	}	
	
	
}