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

class sm_Message extends sm_Widget {

	function __construct() {
		parent::__construct();
		
		
	}
	
	
	/**
	 * Create the HTML code for the message.
	 */
	public function build()
	{
		$msg = sm_get_message();
		$errors = sm_get_error();
		
		if(count($msg)>0)
		{
			$html=new sm_HTML();
			$html->setTemplateId('message', 'ui.tpl.html');
			$html->insertArray(array(
					'type'=>'success',
					'message'=>implode("<br>",$msg))
			);
			sm_View::insert('message',$html);
			
		}
		if(count($errors)>0)
		{	
			$html=new sm_HTML();
			$html->setTemplateId('message', 'ui.tpl.html');
			$html->insertArray(array(
					'type'=>'danger',
					'message'=>implode("<br>",$errors))
			);
			sm_View::insert('message',$html);
		}
		
	}
}