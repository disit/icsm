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

class sm_Page extends sm_UIElement
{
	
	protected $title;
	protected $icon;
	protected $menu;
	protected $id;
	

	function __construct($id=null,$parent=null)
	{
		parent::__construct($id);

		$this->title="";
		$this->parent=$parent;
		$this->menu=null;
		$this->tpl_id="page";
		$this->newTemplate("page","ui.tpl.html");
	}

	function setTitle($title)
	{
		$this->title=$title;
		
	}

	function getTitle()
	{
		return $this->title;
	}

	function insert($obj,$var="content")
	{
		parent::insert($var, $obj);
	}

	function icon($icon)
	{
		$this->icon=$icon;
	}
	
	function menu($obj)
	{
		$this->menu=$obj;
	}
	
	function getMenu()
	{
		return $this->menu;
	}

	function render()
	{
		$vars=array();
		if($this->title)
			parent::insert("header", $this->title);
		
		$content="";
		foreach($this->items as $ix=>$t){
			if(!isset($vars[$ix]))
				$vars[$ix]=$this->_render($t);
			else 
				$vars[$ix].=$this->_render($t);
			
		}
		if($this->menu)
		{
			if(is_object($this->menu))
			{
				if(is_callable(array($this->menu, 'render')))
					$vars['menu']=$this->menu->render();
				
			} 
			else
			{
				$vars['menu']=$this->menu;
			}
		}
		$vars['id']=$this->getId();
		$this->addTemplatedata(
				"page",
				$vars
		);
		return $this->display("page");
	}
}