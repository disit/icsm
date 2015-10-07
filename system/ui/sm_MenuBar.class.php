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

define("nav","nav navbar-nav");
define("side-nav","nav navbar-nav side-nav");
define("side-nav-minimized","nav navbar-nav side-nav minimized");


class sm_MenuBar extends sm_HTML
{
	
	protected $active;
	protected $tpl_id;
	protected $menuItems;
	protected $parentMenuIds;
	protected $class;
	protected $style;
	protected $styleKey;

	function __construct($id="MenuBar",$parent=null)
	{
		parent::__construct($id);		
		$this->menuItems=array();
		$this->parentMenuIds=array();
		$this->class="";
		$this->styleKey=null;
	}
	
	function setMenuStyleType($style)
	{
		//$s=constant($style);
		if(defined($style))
		{
			$this->styleKey=$style;
			$this->style=constant($style);
		}
		else 
			$this->style=$style;
	}
		
	function setActiveLink($link)
	{
		$this->active=$link;
	}


	
	function addMenuItem(sm_MenuItem $obj)
	{
		$var = $obj->getmid();
		$this->menuItems[$var]=$obj;
		//$this->parentMenuIds[]=$obj->getparent();
	}
	
	function addHtml($html)
	{
		$this->insert(count($this->items),$html);
	}
	
	function has_children($rows, $id) {
		foreach ( $rows as $row ) {
			if ($row->getparent () == $id)
				return true;
		}
		return false;
	}
	
	function build_menu($rows, $parent = 0) {
		if($parent==0)
			$this->addHtml('<ul class="'.$this->style.'">');
		else 
			$this->addHtml('<ul class="dropdown-menu">');
		
		foreach ( $rows as $row ) {
			if ($row->getparent () == $parent) {
				$icon = sm_formatIcon($row->geticon());
				if($parent!=0)
				{
					$active="";
					if($this->active==$row->getpath())
						$active="active open";
					$this->addHtml('<li class="dropdown-submenu '.$active.'"><a href="'.$row->getpath().'">'.$icon." <span class=title>". $row->gettitle() . '</span></a>');
				}
				else 
				{
					$active="";
					if($this->active==$row->getpath())
						$active="active open";
					if ($this->has_children ( $rows, $row->getmid () ))
						$this->addHtml('<li class="dropdown '.$active.'"><a class="dropdown-toggle" data-toggle="dropdown" href="'.$row->getpath().'">'.$icon." <span class=title>". $row->gettitle() . '</span><b class="caret"></b></a>');
					else 
						$this->addHtml('<li class="'.$active.'"><a href="'.$row->getpath().'">'.$icon." <span class=title>". $row->gettitle() . '</span></a>');
				}
				if ($this->has_children ( $rows, $row->getmid () ))
					$this->build_menu ( $rows, $row->getmid () );
				$this->addHtml('</li>');
			}
		}
		$this->addHtml("</ul>");
	
		
	}
	
	
	function render()
	{
		$this->build_menu($this->menuItems);
		if($this->styleKey=="side-nav-minimized" || $this->styleKey=="side-nav")
		{
			$this->addJS("sidenav.js");
			if($this->styleKey=="side-nav-minimized")
				$this->addCss("#wrapper{padding-left:50px;}");
			else 
				$this->addCss("#wrapper{padding-left:225px;}");
		}
		return parent::render();
	}
	
	static function getStyles()
	{
		return array("nav","side-nav","side-nav-minimized");
	}
}
