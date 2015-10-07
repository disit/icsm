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

class sm_Pager{

	/**
	 * set the number of items per page.
	 *
	 * @var numeric
	 */
	private $_perPage;

	/**
	 * set get parameter for fetching the page number
	 *
	 * @var string
	 */
	private $_instance;

	/**
	 * sets the page number.
	 *
	 * @var numeric
	 */
	private $_page;

	/**
	 * set the limit for the data source
	 *
	 * @var string
	 */
	private $_limit;

	/**
	 * set the total number of records/items.
	 *
	 * @var numeric
	 */
	private $_totalRows = 0;

	private $_pageLink;

	/**
	 *  __construct
	 *
	 *  pass values when class is istantiated
	 *
	 * @param numeric  $_perPage  sets the number of iteems per page
	 * @param numeric  $_instance sets the instance for the GET parameter
	 */
	public function __construct($pageLink="",$instance="p"){
		$this->_perPage=10;
		$this->_pageLink=$pageLink;
		if(isset($_SESSION['pager'][$this->_pageLink]['limit']))
			$this->_perPage=$_SESSION['pager'][$this->_pageLink]['limit'];
		else 
			$_SESSION['pager'][$this->_pageLink]['limit']=$this->_perPage;
		
		$this->_instance = $instance;
		//$this->_perPage = $perPage;
		$this->set_instance();
	}

	/**
	 * get_start
	 *
	 * creates the starting point for limiting the dataset
	 * @return numeric
	 */
	private function get_start(){
		return ($this->_page * $this->_perPage) - $this->_perPage;
	}

	/**
	 * set_instance
	 *
	 * sets the instance parameter, if numeric value is 0 then set to 1
	 *
	 * @var numeric
	 */
	private function set_instance(){
		$this->_page = (int) (!isset($_GET[$this->_instance]) ? 1 : $_GET[$this->_instance]);
		$this->_page = ($this->_page == 0 ? 1 : $this->_page);
		
	}

	/**
	 * set_total
	 *
	 * collect a numberic value and assigns it to the totalRows
	 *
	 * @var numeric
	 */
	public function set_total($_totalRows){
		$this->_totalRows = $_totalRows;
	}
	
	/**
	 * get_total
	 *
	 * returns a numberic value of totalRows
	 *
	 * @return numeric
	 */
	public function get_total(){
		return $this->_totalRows;
	}
	
	/**
	 * get_page
	 *
	 * returns a numberic value of current page
	 *
	 * @return numeric
	 */
	public function get_page(){
		return $this->_page;
	}
	
	/**
	 * get_perPage
	 *
	 * returns a numberic value of current perPage
	 *
	 * @return numeric
	 */
	public function get_perPage(){
		return $this->_perPage;
	}

	/**
	 * get_limit
	 *
	 * returns the limit for the data source, calling the get_start method and passing in the number of items perp page
	 *
	 * @return string
	 */
	public function get_limit(){
		return "LIMIT ".$this->get_start().",$this->_perPage";
	}
	
	/**
	 * get_instance 
	 * 
	 * returns the GET variable for paging
	 * 
	 * @return string
	 */
	public function get_instance()
	{
		return $this->_instance;
	}
	
	/**
	 * 
	 * @param unknown $data
	 */
	public static function set_limit($data)
	{
		if(isset($data['limit']) && isset($data['link']))
		{
			$_SESSION['pager'][$data['link']]['limit']=$data['limit'];
			
		}
	}
	
	public static function hasChangedLimit($data)
	{
		if(isset($data['limit']) && isset($data['link']))
		{
			return $_SESSION['pager'][$data['link']]['limit']!=$data['limit'];
				
		}
		return false;
	}
	
	function set_pageLink($pageLink)
	{
		$this->_pageLink=$pageLink;
	}
	
	function get_pageLink()
	{
		return $this->_pageLink;
	}
	
	function get_currPageLink()
	{
		return $this->_pageLink."?".$this->_instance."=".$this->_page;
	}
	
	function get_perPageOptions()
	{
		return array(10,25,50,100,200,250);
	}
	
}