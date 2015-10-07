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

class sm_Panel extends sm_UIElement
{
	
	protected $title;
	protected $icon;
	protected $type;
	protected $class;
	protected $footer;
	
	function __construct($id=null,$parent=null)
	{
		parent::__construct($id);
		$this->parent=$parent;
		$this->title="";
		$this->footer="";
		$this->type='primary';
		$this->tpl_id="panel";
		$this->class=array();
		$this->newTemplate($this->tpl_id,"ui.tpl.html");
	}
	
	function setClass($class)
	{
		$classes=explode(" ", $class);
		$this->class=array_merge($this->class,$classes);
	}
	
	function setFooter($obj)
	{
		$this->footer=$obj;
	}
	
	function setType($type)
	{
		$this->type=$type;
	}
	
	function setTitle($title)
	{
		$this->title=$title;
	}
	
	function insert($obj,$var="content")
	{
		parent::insert("content", $obj);
		//$this->sortItems("content");
	}
	
	function icon($icon)
	{
		$this->icon=$icon;
	}
	
	function render()
	{
		$content="";
		foreach($this->items as $ix=>$t){
				$content.=$this->_render($t);
			
		}
		if($this->footer!="")
		{
			$content.="</div><div class=panel-footer>".$this->_render($this->footer);
		}
		$this->addTemplatedata(
				$this->tpl_id,
				array(
						'panel_id'=>$this->id,
						'panel_type'=>$this->type,
						'panel_title'=>$this->title,
						'panel_content' => $content,
						'panel_icon'=>$this->icon,
						"class"=>implode(" ",$this->class)
				)
		);
		return $this->display($this->tpl_id);
	}
}
