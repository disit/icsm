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

class sm_XML extends sm_UIElement
{
	protected $xml;
	function __construct($id=null,$parent=null)
	{
		parent::__construct($id,$parent);
		$this->xml = "";
	}

	function insert($xml,$var=null)
	{
		if(is_string($xml))
			$this->xml=$xml;
		else if(is_object($xml) && is_a($xml,"DOMDocument"))
			$this->xml=$xml->saveXML();
	}

	function render()
	{
		return $this->xml;
	}
}