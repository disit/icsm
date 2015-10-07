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

class sm_UIElement extends sm_Template
{
	protected $id;
	protected $parent;
	protected $tplVars;
	protected $items;
	protected $tpl_id;
	protected $weight;
	
	function __construct($id=null,$tpl_var="content",$parent=null)
	{
		$this->id=isset($id)?$id:microtime(true)*10000;
		$this->parent=$parent;
		$this->tplVars=null;
		if($parent)
			$parent->insert($tpl_var,$this);
		$this->tpl_var=$tpl_var;
		$this->tpl_id=null;
		$this->items=array();
		$this->weight=-1;
	}
	
	function weight($w=-1)
	{
		$this->weight=$w;
	}
	
	function getWeight()
	{
		return $this->weight;
	}
	
	function getTplId()
	{
		return $this->tpl_id;
	}
	
	function setId($id)
	{
		$this->id=$id;
	}
	
	function getTplVar()
	{
		return $this->tpl_var;
	}
	
	protected function detachParent()
	{
		
	}
	
	function setParent($parent,$tpl_var="content")
	{
		if($this->parent)
			$this->detachParent();
		$this->parent=$parent;
		$this->tpl_var=$tpl_var;
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function render()
	{
		
	}
	
	function insertArray($array=array())
	{
		foreach ($array as $k=>$v)
		{
			
			//$this->items[$k][]=$v;
			$this->insert($k, $v);
		}
	}
	
	function insert($var,$obj)
	{
		if(!isset($this->items[$var]))
			$this->items[$var]=array();
		if(is_array($obj))
			$this->items[$var]=array_merge($this->items[$var],$obj);
		else
		{
			$this->items[$var][]=$obj;	
		}
	}
	
	function replace($var,$obj)
	{
		if(isset($this->items[$var]))
			unset($this->items[$var]);
		$this->insert($var, $obj);			
	}
	
	function remove($var)
	{
		if(isset($this->items[$var]))
			unset($this->items[$var]);
	}
	
	function _render($items, $is_sub=FALSE)
	{
		$content="";
		/*
		 * Loop through the array to extract item to be rendered
		 */
		foreach($items as $id => $item) {
		
			if(!is_array($item) && !is_object($item))
			{
				$content.=$item;
				continue;
			}
			
			if(is_array($item))
			{
				$content.=$this->_render($item); //, TRUE);
			}
			
			else
			{
							
				$content.=$item->render();
				$this->copyCSS($item);
				$this->copyJs($item);
				
				
			}
	
		}
		return $content;
	}
	
	function copyCSS(sm_UIElement $from)
	{
		$css=$from->getCSS();
		foreach($css as $id=>$data)
		{
			foreach($data as $k=>$v)
			{
				$path = substr($v['file'],0,strripos($v['file'], "/")+1);
				$this->addCSS($k,$this->tpl_id,$path);
				$from->removeCSS($id, $k);
			}
		}
	}
	
	function copyJs(sm_UIElement $from)
	{
		$js=$from->getJS();
		foreach($js as $id=>$data)
		{
			foreach($data as $k=>$v)
			{
				$path = substr($v['file'],0,strripos($v['file'], "/")+1);
				$this->addJS($k,$this->tpl_id,$path);
				$from->removeJS($id, $k);
			}
		}
	}
	
	
	function getUIElement($id)
	{
		$found=null;
		foreach($this->items as $k=>$item)
		{
			foreach($item as $i=>$t)
			{
				if(is_object($t))
				{
					if($t->getId()==$id)
					{
						$found = $t;
						break;
					}
					else
						$found = $t->getUIElement($id);
				}
			}
			if($found)
				break;
		}
		return $found;
	}
	
	function getTemplateId()
	{
		return $this->tpl_id;
	}
	
	function getItems()
	{
		return $this->items;
	}
	
	protected function sortItems($key)
	{
		usort($this->items[$key], array($this, "_sort"));
	}	
	
	protected function _sort($i, $j) 
	{
		if(is_a($i,"sm_UIElement") && is_a($j,"sm_UIElement"))
		{
			$a=$i->getWeight();
			$b=$j->getWeight();
			return ($a > $b) ? -1 : 1;
		}
		return 1;
	}
	
}