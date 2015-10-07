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

class sm_Paginator extends sm_UIElement
{
	protected $_page;
	protected $_perPage;
	protected $_totalRows;
	protected $_instance;
	protected $_pageLink;
	protected $parentId;
			
	function __construct(sm_Pager $pager=null){
		$this->_page=1;
		$this->_totalRows=0;
		$this->_instance="p";
		$this->_page=10;
		$this->_pageLink="";
		if($pager)
			$this->setPager($pager);
		
	}
	
	function setPager(sm_Pager $pager)
	{
		$this->_page=$pager->get_page();
		$this->_totalRows=$pager->get_total();
		$this->_instance=$pager->get_instance();
		$this->_perPage=$pager->get_perPage();
		$this->_pageLink = $pager->get_pageLink();
	}
	
	
	
	/**
	 * page_links
	 *
	 * create the html links for navigating through the dataset
	 *
	 * @var sting $path optionally set the path for the link
	 * @var sting $ext optionally pass in extra parameters to the GET
	 * @return string returns the html menu
	 */
	protected function page_links($path='?',$ext=null)
	{
		if(strpos("?", $path)===false)
			$path.="?";
		$adjacents = "2";
		$prev = $this->_page - 1;
		$next = $this->_page + 1;
		$lastpage = ceil($this->_totalRows/$this->_perPage);
		$lpm1 = $lastpage - 1;
	
		$pagination = "";
		if($lastpage > 1)
		{
			$pagination .= "<div id='pagination'><ul class='pagination'>";
			if ($this->_page > 1)
				$pagination.= "<li><a href='".$path."$this->_instance=$prev"."$ext'>« previous</a></li>";
			else
				$pagination.= "<li class='disabled'><span >« previous</span></li>";
	
			if ($lastpage < 7 + ($adjacents * 2))
			{
				for ($counter = 1; $counter <= $lastpage; $counter++)
				{
				if ($counter == $this->_page)
					$pagination.= "<li class=active><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
					else
						$pagination.= "<li><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
				}
				}
				elseif($lastpage > 5 + ($adjacents * 2))
				{
				if($this->_page < 1 + ($adjacents * 2))
				{
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
					{
					if ($counter == $this->_page)
						$pagination.= "<li class=active><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
						else
						$pagination.= "<li><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
					}
					$pagination.= "<li><span>...</span></li>";
					$pagination.= "<li><a href='".$path."$this->_instance=$lpm1"."$ext'>$lpm1</a></li>";
					$pagination.= "<li><a href='".$path."$this->_instance=$lastpage"."$ext'>$lastpage</a></li>";
					}
				elseif($lastpage - ($adjacents * 2) > $this->_page && $this->_page > ($adjacents * 2))
					{
					$pagination.= "<li><a href='".$path."$this->_instance=1"."$ext'>1</a></li>";
					$pagination.= "<li><a href='".$path."$this->_instance=2"."$ext'>2</a></li>";
					$pagination.= "<li><span>..</span></li>";
							for ($counter = $this->_page - $adjacents; $counter <= $this->_page + $adjacents; $counter++)
							{
							if ($counter == $this->_page)
								$pagination.= "<li class=active><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
								else
								$pagination.= "<li><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
							}
							$pagination.= "<li><span>..</span></li>";
							$pagination.= "<li><a href='".$path."$this->_instance=$lpm1"."$ext'>$lpm1</a></li>";
							$pagination.= "<li><a href='".$path."$this->_instance=$lastpage"."$ext'>$lastpage</a></li>";
					}
					else
					{
					$pagination.= "<li><a href='".$path."$this->_instance=1"."$ext'>1</a></li>";
					$pagination.= "<li><a href='".$path."$this->_instance=2"."$ext'>2</a></li>";
					$pagination.= "<li><span>..</span></li>";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
					{
					if ($counter == $this->_page)
							    $pagination.= "<li class=active><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
								    else
								    	$pagination.= "<li><a href='".$path."$this->_instance=$counter"."$ext'>$counter</a></li>";
								    }
								    }
								    }
	
								    if ($this->_page < $counter - 1)
			    $pagination.= "<li><a href='".$path."$this->_instance=$next"."$ext'>next »</a></li>";
								    else
								    	$pagination.= "<li class='disabled'><span >next »</span></li>";
								    	$pagination.= "</ul></div>\n";
				}
	
	
				return $pagination;
	}
	
	function render()
	{
		return $this->page_links($this->_pageLink);
	}
}