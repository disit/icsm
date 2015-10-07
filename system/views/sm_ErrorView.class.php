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

class sm_ErrorView extends sm_ViewElement
{
	function build(){
		
		$error = new sm_HTML("Error");
		$error->setTemplateId("error","error.tpl.html");
		$error->insertArray($this->getErrorDescription());
		$this->uiView=$error;
		
		//$this->addView();
	}
	
	function getErrorDescription() {
		switch ($this->model['code']) {
			case 400 :
				$errorname = 'Error 400 - Bad Request';
				$errordesc = '<h1>Bad Request</h1>
  					<h2>Error Type: 400</h2>
					  <p>
					  The URL that you requested &#8212; http://' . $this->model['requested_url'] . ' &#8212; does not exist on this server. You might want to re-check the spelling and the path.</p>
					  
					  <p>You can use the menu at the top of the page or at the right to navigate to another section.</p>';
				break;
			
			// Error 401 - Authorization Required
			case 401 :
				$errorname = 'Error 401 - Authorization Required';
				$errordesc = '<h1>Authorization Required</h1>
				  <h2>Error Type: 401</h2>
				  <p>
				  The URL that you requested requires pre-authorization to access.</p>';
				  
				break;
			
			// Error 403 - Access Forbidden
			case 403 :
				$errorname = 'Error 403 - Access Forbidden';
				$errordesc = '<h1>Access Forbidden</h1>
				  <h2>Error Type: 403</h2>
				  <p>
				  Access to the URL that you requested is forbidden.</p>';
				break;
			
			// Error 404 - Page Not Found
			case 404 :
				$errorname = 'Error 404 - Page Not Found';
				$errordesc = '<h1>File Not Found</h1>
				  <h2>Error Type: 404</h2>
				  <p>
				  Ooops! The page you are looking for &#8212; http://' . $this->model['requested_url'] . ' &#8212; cannot be found. This may be because:</p>
				  <ul>
				    <li>the path to the page was entered wrong;</li>
				    <li>the page no longer exists; or</li>
				    <li>there has been an error on the Web site.</li>
				  </ul>
				  <p>You can use the menu at the top of the page or at the right to navigate to another section.</p>';
				break;
			
			// Error 500 - Server Configuration Error
			case 500 :
				$errorname = 'Error 500 - Server Configuration Error';
				$errordesc = '<h1>Server Configuration Error</h1>
			  <h2>Error Type: 500</h2>
			  <p>
			  The URL that you requested &#8212; <a href="http://' . $this->model['requested_url'] . '">http://' . $this->model['requested_url'] . '</a> &#8212; resulted in a server configuration error. It is possible that the condition causing the problem will be gone by the time you finish reading this.</p>';
				break;
			
			// Unknown error
			default :
				$errorname = 'Unknown Error';
				$errordesc = '<h2>Unknown Error</h2>
				  <p>The URL that you requested &#8212; <a href="http://' . $this->model['requested_url'] . '">http://' . $this->model['requested_url'] . '</a> &#8212; resulted in an unknown error. It is possible that the condition causing the problem will be gone by the time you finish reading this. </p>';
		}
		return array("errorname"=>$errorname,"errordesc"=>$errordesc);
	}
}
