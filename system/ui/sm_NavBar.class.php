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

class sm_NavBar extends sm_UIElement
{
	
	protected $active;
	protected $tpl_id;
	protected $brand;
	protected $subitem;
	protected $class;

	function __construct($id="sm_navbar",$parent=null)
	{
		parent::__construct($id);		
		$this->setTemplateId();
		$this->brand="";
		$this->subitem=array();
		$this->class="";
	}
	
	function setTemplateId($tpl_id="button_bar",$tpl_file="menu.tpl.html"){
		$this->items=array();
		$this->newTemplate($tpl_id,$tpl_file);
		$this->tpl_id=$tpl_id;
	}
	
	function setActive($itemId)
	{
		$this->active=$itemId;
	}
	
	function setClass($class)
	{
		$this->class=$class;
	}
	
	function insertBrand($obj)
	{
		$this->insert("brand",$obj);
	}
	
	function insertSubLevel($id,$obj)
	{
		if(isset($this->items[$id]))
		{
			if(is_a($obj,"sm_UIElement"))
				$this->items[$id]['sublevel']=$obj->render();
			else 
				$this->items[$id]['sublevel']=$obj;
		}
	}

	function insert($var,$obj)
	{
		if(!is_numeric($var) && $var=="brand")
			$this->brand=$obj;
		else 
		{
			$this->items[$var]=$obj;
			if(!isset($obj['id']))
				$this->items[$var]['id']=$var;
		}
		
	}
	
	function prepend($var,$obj)
	{
		$item[$var]=$obj;
		if(!isset($obj['id']))
			$item[$var]['id']=$var;
		$this->items=$item+$this->items;
	}
	
	function getActive(){
		return isset($this->items[$this->active])?$this->items[$this->active]:null;
	}
	
	function render()
	{
		if(isset($this->items[$this->active]))
		{
			if(isset($this->items[$this->active]['tab_class']))
				$this->items[$this->active]['class'].='active';
			else 
				$this->items[$this->active]['class']='active';
		}
	
		$this->addTemplateDataRepeat($this->tpl_id,$this->tpl_id,$this->items);
		$this->addTemplateData($this->tpl_id,
				array(
					"id"=>$this->id,
					"brand"=>$this->brand,
					"class"=>$this->class
				));
	
		return $this->display($this->tpl_id);
	}
}
