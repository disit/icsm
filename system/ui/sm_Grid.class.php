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

class sm_Grid extends sm_HTML
{
	protected $rows;
	
	function __construct($id=null,$parent=null)
	{
		parent::__construct($id,$parent);
		$this->rows=array();		
	}

	function addRow($cols=array(),$width=array()){
		$this->rows[]=array("data"=>$cols,"width"=>$width);
	}
	
	function prependRow($cols=array(),$width=array()){
		$first = array("data"=>$cols,"width"=>$width);
		$this->rows=$first+$this->rows;
	}
	
	function deleteRow($n)
	{
		unset($this->rows[$n]);	
	}
	
	function render()
	{
		$content="";
		$this->insert("pre","<div id='".$this->id."'>");
		foreach($this->rows as $ix=>$cols){
			$this->insert("start-row-".$ix,"<div class=row id=row-".$ix.">");		
			$class="col-md-".floor(12/count($cols['data']));
			foreach ($cols['data'] as $j=>$c)
			{
				if(isset($cols['width'][$j]))
					$class="col-md-".$cols['width'][$j];
				
				$this->insert("s-col-".$ix."-".$j,"<div class='".$class."'>");
				$this->insert("col-".$ix."-".$j,$c);
				$this->insert("e-col-".$ix."-".$j,"</div>");
			}
			$this->insert("end-row-".$ix,"</div>");
		}
		$this->insert("end","</div>");
		
		return parent::render();	
	}
}