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

class SM_Response
{
	public $type;
	public $title;
	public $body;
	
	public function __construct($type=null,$title=null,$data=null)
	{
		$this->type=isset($type)?$type:"message";
		$this->title=isset($title)?$title:"";
		$this->body=isset($data)?$data:"";
	}
	
	public function setType($type)
	{
		$this->type=$type;
	}
	
	public function setTitle($title)
	{
		$this->title=$title;
	}
	
	public function setData($data)
	{
		$this->body=$data;
	}
	
}
