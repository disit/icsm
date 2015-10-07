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

class sm_Table_Cell extends sm_HTML
{
	protected $attrs;
	protected $data;
	
	function sm_Table_Cell($id=null,$attrs=array())
	{
		parent::__construct($id);
		$this->attrs=$attrs;
		$this->data="";
		$this->setTemplateId("table_cells","table.tpl.html");
	}
	
	function render(){
		$attrs=array();
		foreach($this->attrs as $k=>$value)
		{
			$attrs[]=$k." = '".$value."'";
		}
		$this->insert("attrs",count($attrs)>0?implode(" ",$attrs):"");
		return parent::render();
	}
	
	function addAttrs($attributes=null)
	{
		if($attributes)
		{
			$this->attrs=array_merge($this->attrs,$attributes);
		}
	}
	
	function insertArray($array=array())
	{
		$this->data=isset($array["data"])?$array["data"]:"";
		parent::insertArray($array);
	}
	
	function getData(){
		return $this->data;
	}
	
}

class sm_Table extends sm_HTML
{
	protected $header;
	protected $footer;
	protected $rows;
	protected $class;
	protected $hRows;
	protected $colgroup;
	protected $tableHeader;
	/*
	 * 	Make a responsive table if set to true
	 */
	protected $responsive;
	
	const STRIPED ="table-striped";
	const CONDENSED ="table-condensed";
	const BORDERED = "table-bordered";
	const HOVER = "table-hover";
	/**
	 * 
	 * @param string $id
	 * @param array $opts
	 */
	function __construct($id=null, $opts=array())
	{
		parent::__construct($id);
		
		$this->setTemplateId("table","table.tpl.html");
		$this->class=isset($opts['class'])?$opts['class']:array();
		$this->footer['attrs']=array();
		$this->responsive = isset($opts['responsive'])?$opts['responsive']:false;
		//$this->header['attrs']=array();
		$this->rows=array();
		$this->colgroup="";
		$this->tableHeader=array();
		$this->style=array();
	}
	
	function setStyle($name)
	{
		$this->style[]=$name;
	}
	
	function setColGroup($col)
	{
		$this->colgroup=$col;
	}
			
	/**
	 * Set the main attributes for thead section
	 * @param array $attr 
	 */
	function setHeadersAttr($attr=array())
	{
		$this->header['attrs']=$attrs;
	}
	
	/**
	 * Set the main attributes for tfoot section
	 *
	 * @param array $attr
	 *
	 */
	
	function setFooterAttr($attr=array())
	{
		
		$this->footer['attrs']=$attrs;
	}
	
	/**
	 * Add cell and its attributes into header
	 *
	 * @param $data mixed - string, sm_UIElement object
	 *
	 */
	function addHeaderCell($data, $class="", $attr=array())
	{
		$cell = new sm_Table_Cell(null,$attr);
		$cell->insertArray(array(
				"tag"=>"th",
				"class"=>$class,
				"data"=>$data));
	
		//$this->header['cells'][]=$cell;
		$this->header[$this->curHRow]['cells'][]=$cell;
	}
	
	/**
	* Add cell and its attributes into footer
	* 
	* @param $data mixed - string, sm_UIElement object
	*
	*/
	function addFooterCell($data, $class="", $attr=array())
	{
		$cell = new sm_Table_Cell(null,$attr);
		$cell->insertArray(array(
				"tag"=>"td",
				"class"=>$class,
				"data"=>$data));
		$this->footer['cells'][]=$cell;
	}
	/**
	 * Add a new row in the table (tbody)
	 *
	 * @param string $class
	 * @param array $attr 
	 *
	 */
	function addRow($class="",$attr=array())
	{
		$this->rows[]=array(
				"class"=>$class,
				"attrs"=>$attr,
				"cells"=>array()
				);
		$this->curRow=count($this->rows)-1;
	}
	
	/**
	 * Add a new header row in the table (thead)
	 *
	 * @param string $class
	 * @param array $attr
	 *
	 */
	function addHRow($class="",$attr=array())
	{
		$header=array(
				"class"=>$class,
				"attrs"=>$attr,
				"cells"=>array()
		);
		if(isset($attr['data-type']) && $attr['data-type']=="table-header")
		{
			$this->tableHeader=$header;
			$this->header[]=&$this->tableHeader;
		}
		else
			$this->header[]=$header;
		$this->curHRow=count($this->header)-1;
	}
	
	
	/**
	 * @param $data mixed - string, sm_UIElement object
	 * 
	 */
	function addCell($data, $class="", $attr=array())
	{
		$cell = new sm_Table_Cell(null,$attr);
		$cell->insertArray(array(
			"tag"=>"td",
			"class"=>$class,
			"data"=>$data));
		
		$this->rows[$this->curRow]['cells'][]=$cell;
	}
	
	/**
	 * @param $data mixed - string, sm_UIElement object
	 *
	 */
	function addTHCell($data, $class="", $attr=array())
	{
		$cell = new sm_Table_Cell(null,$attr);
		$cell->insertArray(array(
				"tag"=>"th",
				"class"=>$class,
				"data"=>$data));
	
		$this->rows[$this->curRow]['cells'][]=$cell;
	}
	
	/**
	 * 
	 * @see sm_UIElement::render()
	 */
	function render()
	{
		$thead=array();
		
		foreach($this->header as $r_num=>$data)
		{
			$attrs=array();
			foreach($data['attrs'] as $k=>$value)
			{
				$attrs[]=$k." = '".$value."'";
			}
			
			$hrow=new sm_HTML();
			$hrow->setTemplateId("row","table.tpl.html");
			$hrow->insert("cells", $data['cells']);
			$hrow->insert("attrs",count($attrs)>0?implode(" ",$attrs):"");
			$thead[]=$hrow;
		
		}
		
		$rows=array();
		foreach($this->rows as $r_num=>$data)
		{
			$attrs=array();
			foreach($data['attrs'] as $k=>$value)
			{
				$attrs[]=$k." = '".$value."'";
			}
			if($this->tableHeader)
			{
				foreach ($data['cells'] as $i=>$cell)
				{
					if($i==0)
						$data['cells'][$i]->addAttrs(array("scope"=>"row"));
					else 
						$data['cells'][$i]->addAttrs(array("data-title"=>$this->tableHeader['cells'][$i]->getData()));
				}
			}
			$row=new sm_HTML();
			$row->setTemplateId("row","table.tpl.html");
			$row->insert("cells", $data['cells']);
			$row->insert("attrs",count($attrs)>0?implode(" ",$attrs):"");
			$rows[]=$row;
			
		}
		
			
		$tfoot="";
		if(isset($this->footer['cells']))
		{
			$attrs=array();
			foreach($this->footer['attrs'] as $k=>$value)
			{
				$attrs[]=$k." = '".$value."'";
			}
			$footer['cells']=$this->footer['cells'];		
			$footer['attrs']=count($attrs)>0?implode(" ",$attrs):"";
			$tfoot=new sm_HTML("tfoot");
			$tfoot->setTemplateId("row","table.tpl.html");
			$tfoot->insertArray($footer);
		}
	 
		if(!is_array($this->class))
			$this->class=array($this->class);
		if(empty($this->style)) //apply default style
			$this->style=array("table-striped","table-condensed");	
		$this->class[]="table";
		$this->class = array_merge($this->class, $this->style);

		$this->insertArray(array(
				"responsive"=>$this->responsive?"table-responsive sm-table-responsive":"table-responsive",
				"id"=>$this->id,
				"class"=>implode(" ",$this->class),
				"thead"=>$thead,
				"tfoot"=>$tfoot,				
				"rows"=>$rows,
				"colgroup"=>$this->colgroup));
		
		if($this->responsive)
			$this->addCSS("sm_responsiveTable.css",$this->tpl_id,"css/sm_ui/");
		
		return parent::render();
		
	}
	
	function getHeaderCount(){
		$max=0;
		foreach($this->header as $r_num=>$data)
		{
			$max=max(array($max,count($data['cells'])));
		}
		return $max;
	}
	
	function &getHeaderRows(){
		
		return $this->header;
	}
	
	function &getFooterRows(){
	
		return $this->footer;
	}
	
	function &getRows(){
	
		return $this->rows;
	}
	
	function makeResponsive()
	{
		$this->responsive=true;
	}
}