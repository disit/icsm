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

class SM_SCEMail extends sm_Template
{
	protected $filter; 
	protected $alias;
	protected $fieldOrder;
	
	public function __construct()
	{
		$this->newTemplate("sce_mail_body", "../".SM_SCEPlugin::instance()->getFolder("templates")."sce.tpl.html");
		$this->filter = array(
				"call_url",
				"metric","sla",
				"host_machine",
				"virtual_machine",
				"metric_unit",
				"relation"
				
		);
		$this->alias = array(
				"business_configuration"=>"Configuration Id",
				"timestamp"=>"Alarm Time",
				"metric_name"=>"Metric",
				"virtual_machine_name"=>"VM",
				"metric_timestamp"=>"Last Check",
				
		);
		$this->fieldOrder=array(
				"metric_name",
				"value",
				"threshold",
				"virtual_machine_name",
				"metric_timestamp"
		);
		
	}
	
	public function mailRender(sm_Notification &$notification)
	{
		sm_Logger::write("Rendering Mail From Smart Cloud Engine Adaptor");
		$array=XML2Array::createArray($notification->message);
		
		unset($array['event']['@attributes']);
		$table = "";
		$metrics = $array['event']['metric'];
		unset($array['event']['metric']);
		foreach($array['event'] as $k=>$v)
		{
			$label = $k;
			if(isset($this->alias[$k]))
				$label = $this->alias[$k];
			$data[]=array("label"=>$label,"text"=>$v);
		}
		
		$table = count($metrics)>0?$this->makeMetricsTable($metrics):"";
	//	sm_Logger::write($table);
		$this->addTemplateData("sce_mail_body", array("title"=> $notification->subject,"metrics"=>$table));
		$this->addTemplateDataRepeat("sce_mail_body", "data",$data);
		$notification->message = $this->display("sce_mail_body");
		$notification->contentType=SM_RestFormat::HTML;
	}
	
	protected function makeMetricsTable($metrics)
	{
		$this->newTemplate("sce_table", "../".SM_SCEPlugin::instance()->getFolder("templates")."sce.tpl.html");
		
		$keys=array_keys($metrics);
		if(!is_numeric($keys[0]))
			$data=array($metrics);
		else 
			$data=$metrics;
		$th=array_fill(0, count($this->fieldOrder), null);
		foreach (array_keys($data[0]) as $key)
		{
			if(in_array($key, $this->filter))
				continue;
			
			if(isset($this->alias[$key]))
				$th[array_search($key,$this->fieldOrder)]=array("th"=>$this->alias[$key]);
			else
				$th[array_search($key,$this->fieldOrder)]=array("th"=>$key);
		}
		$this->addTemplateDataRepeat("sce_table", "th",$th);
		$rows=array();
		foreach ($data as $k=>$v)
		{
			
			$td=array_fill(0, count($this->fieldOrder), null);
			$this->newTemplate("table_row", "../".SM_SCEPlugin::instance()->getFolder("templates")."sce.tpl.html");
			foreach ($v as $key=>$value)
			{
				if(in_array($key, $this->filter))
					continue;
				
				
				$val=$key=="value" || $key=="threshold"?sprintf("%01.2f %s",$value,$v['metric_unit']):$value;
				if($key=="threshold")
				{
					if($v['relation']=="hasMetricValueLessThan")
						$val="&lt; ".$val;
					else 
						$val="&gt; ".$val;
				}
				$td[array_search($key,$this->fieldOrder)]=array("td"=>$val);
				
			}
			$this->addTemplateDataRepeat("table_row", "td",$td);
			$rows[]=array("row"=>$this->display("table_row"));
		}
		$this->addTemplateDataRepeat("sce_table", "rows",$rows);
		return $this->display("sce_table");
	}
}