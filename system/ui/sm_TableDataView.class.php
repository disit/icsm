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

class sm_TableDataViewActions extends sm_HTML
{
	function insertArray($commands=array())
	{
		foreach ($commands as $k=>$item)
		{
			
			if(isset($item['method']))
			{
				parent::insert($k,$this->actionForm($item));
			}
			else
			{
				parent::insert($k,$this->actionLink($item));
			}
		}
	}
	
	function insert($var, $command)
	{
		if(isset($item['method']))
		{
			parent::insert($var,$this->actionForm($item));
		}
		else
		{
			parent::insert($var,$this->actionLink($item));
		}
	}
	
	function actionForm($command)
	{
		$icon = isset($command['icon'])?$command['icon']:null;
		if($icon)
		{
			if(isset($command['data']))
				$command['data']='<i class="sm-icon-16 '.$icon.'"></i>'.$command['data'];
			else 
				$command['data']='<i class="sm-icon-16 '.$icon.'"></i>';
		}
			
		$this->newTemplate("actions_forms","ui.tpl.html");
		$this->addTemplateDataRepeat("actions_forms", 'action_form', array($command));
		return $this->display("actions_forms");
	}
	
	function actionLink($command)
	{
		$id=isset($command['id'])?$command['id']:"";
		$name=isset($command['name'])?$command['name']:"";
		$class=isset($command['class'])?$command['class']:"";
		$confirm=isset($command['confirm'])?$command['confirm']:"";
		$title=isset($command['title'])?$command['title']:"";
		$icon = isset($command['icon'])?$command['icon']:"";
		$href = isset($command['url'])?$command['url']:"#";
		$data = isset($command['data'])?$command['data']:"";
		$toggle = isset($command['toggle'])?$command['toggle']:"";
		$target = isset($command['target'])?$command['target']:"";
		if($icon!="")
			$data='<i class="sm-icon-16 '.$icon.'"></i>'.$data;
		return '<button id="'.$id.'" name="'.$name.'" data-confirm="'.$confirm.'" data-toggle="'.$toggle.'" data-target="'.$target.'" class="button action_form_cmd'.$class.'" href="'.$href.'" title="'.$title.'">'.$data.'</button>';
	}
}


class sm_TableDataView extends sm_HTML//sm_Table
{
	
	//Options
	protected $sortable;
	protected $editable;
	protected $seletectedCmd;
	protected $pager;
	protected $pageLink;
	protected $table;
	protected $rows;
	protected $header;
	protected $filters;
	protected $title;
	protected $ajax;
	
	/**
	 *
	 * @param string $id
	 * @param array $opts
	 */
	function __construct($id=null, $opts=array())
	{
		parent::__construct($id);
		$this->table = new sm_Table($id,array('class'=>"sm_TableDataView"));
		$this->header = &$this->table->getHeaderRows();
		$this->rows = &$this->table->getRows();
		$this->setTemplateId("tabledataview","table.tpl.html");
		$this->seletectedCmd=isset($opts['seletectedCmd'])?$opts['seletectedCmd']:null;
		$this->sortable=isset($opts['sortable'])?$opts['sortable']:false;
		$this->editable=isset($opts['editable'])?$opts['editable']:null;
		$this->pager=isset($opts['pager'])?$opts['pager']:null;
		$this->pageLink=isset($opts['pageLink'])?$opts['pageLink']:"";
		if($this->pager && $this->pageLink=="")
			$this->pageLink=$this->pager->get_pageLink();
		$this->filters=array();
		$this->title=null;
		$this->ajax=isset($opts['ajax'])?$opts['ajax']:null;
		
	}
	
	/**
	 * 
	 * @param unknown $method
	 * @param unknown $args
	 * @return mixed
	 */
	public function __call($method,$args)
	{
		return call_user_func_array(array($this->table,$method),$args);
	}
	
	function addFilter($filterElement)
	{
		if(is_array($filterElement))
			$this->filters=array_merge($this->filters,$filterElement);
	}
	/**
	 * 
	 * @param integer $count
	 */
	
	function setPageLink($link)
	{
		$this->pageLink=$link;
	}
	
	/**
	 * 
	 * @param mixed $title
	 */
	
	function setTitle($title)
	{
		$this->title=$title;
	}
	

	/**
	 *
	 * @param string $bool
	 */
	function setSortable($bool=true)
	{
		$this->sortable=$bool;
	}
	
	/**
	 *
	 * @param string $bool
	 */
	function setPager(sm_Pager $pager)
	{
		$this->pager=$pager;
		$this->pageLink=$this->pager->get_pageLink();
	}

	/**
	 *
	 * @param string $bool
	 */
	function setSeletectedCmd($cmd=null)
	{
		$this->seletectedCmd=$cmd;
	}

	/**
	 *
	 * @param string $bool
	 */
	function setEditable($bool=true)
	{
		$this->editable=$bool;
	}
	
	/**
	 *
	 * @param array $opts
	 */
	function setAjax($opt=null)
	{
		$this->ajax=$opt;
	}

	
	/**
	 *
	 * @see sm_UIElement::render()
	 */
	function render()
	{
		if($this->pager && $this->pager->get_total()==0)
		{
						
			$noData=new sm_HTML();
			$noData->setTemplateId('message', 'ui.tpl.html');
			$noData->insertArray(array(
					'type'=>'danger',
					'message'=>"No data available")
			);
			
			$this->insert("noData",$noData);
			
		}
		
		
		
		$jsOptions=array();
		$footerCmd=new sm_HTML();
		
		$filters=null;
		if($this->seletectedCmd)
		{
			$options = $this->addSelectors();
			if($options)
				$jsOptions=array_merge($jsOptions,$options);
		}

		
		
		$footerCmd->insert('paginator',$this->makePaginator());
		$footerCmd->insert("chooser", $this->makeFooterFilters());
		$footerCmd->insert("total", $this->makeTotal());
		$this->addFooterCell($footerCmd,"cmd",array("id"=>"footer-cmds","colspan"=>$this->getHeaderCount()));		

		$cmdBar = $this->makeCmdBar();
		$headerFilters= $this->makeHeaderFilters();
		if($this->pager && $this->pager->get_total()>10)
			$total= $this->makeTotal();
		else 
			$total=null;
		if($cmdBar || $headerFilters || $total)
		{
			$headerBar=new sm_HTML();
			$headerBar->insert("pre",'<nav class="navbar navbar-default" role="navigation">');
			$headerBar->insert("header-bar-pre","<div id=header-bar class='container-fluid cmd'>");
			if($cmdBar)
				$headerBar->insert("bar", $cmdBar);
			if($headerFilters)
				$headerBar->insert("chooser", $headerFilters);
			if($total)
				$headerBar->insert("total", $total);
			$headerBar->insert("header-bar-post","</div>");
			$headerBar->insert("end","</nav>");
			$this->insert("headerBar",$headerBar);
		}
		
	/*	$cell = new sm_Table_Cell("header-cmds",array("class"=>"cmd sorter-false","colspan"=>$this->getHeaderCount()));
		$cell->insertArray(array(
				"tag"=>"th",
				"class"=>"tbw-chooser sorter-false",
				"data"=>$headerBar));
		$row[]=array(
				"class"=>"tbw-cmd sorter-false",
				"attrs"=>array("id"=>"cmd"),
				"cells"=>array());
		$row[0]['cells'][]=$cell;
		$this->header=array_merge($row,$this->header);*/
		
		if($this->title)
		{
			$t = new sm_HTML();
			$t->insert("title",$this->title);
			$cell = new sm_Table_Cell("title",array("class"=>"sorter-false","colspan"=>$this->getHeaderCount()));
			$cell->insertArray(array(
					"tag"=>"th",
					"class"=>"sorter-false",
					"data"=>$t));
			$trow[]=array(
					"class"=>null,
					"attrs"=>null,
					"cells"=>array());
			$trow[0]['cells'][]=$cell;
			$this->header=array_merge($trow,$this->header);
		}
		$this->insert("table", $this->table);
		if($this->sortable)
			$this->makeSortable();
		$this->table->addJS('bootbox.js',$this->tpl_id,'js/bootbox/');
		$this->table->addCSS("sm_tabledataview.css",$this->tpl_id,"css/sm_ui/");
		
		if($this->ajax)
		{
			$jsOptions['ajax']=$this->ajax;
		}
		if(count($jsOptions)>0)
		{
			$this->table->addJS('sm_tabledataview.js',$this->tpl_id,'js/sm_ui/');
			$json_string = json_encode($jsOptions);
			$this->table->addJs('$(document).ready(
					function(){
						$("div#smTableDataView").smTableDataView(
							'.$json_string.'
						);
					});');
		}
		
		return parent::render();

	}

	protected function makeSortable()
	{

		/**** Table sorter ****/
		$this->table->addJS('jquery.tablesorter.min.js',$this->tpl_id,'js/tablesorter/');
		$this->table->addJS('jquery.tablesorter.widgets.min.js',$this->tpl_id,'js/tablesorter/');
		$this->table->addCss("theme.default.css",$this->tpl_id,"css/tablesorter/");

		$this->table->addJs('$(document).ready(
				function(){
					$("table#'.$this->id.'").tablesorter({
				headerTemplate : "{content}{icon}",
				cancelSelection: false,

									   	});
				});',$this->tpl_id);
	}
	
	
	
	protected function addSelectors()
	{
		if(count($this->rows)>0)
		{
				$this->table->setColGroup("<col span=1 class='wide'/>");
				foreach($this->rows as $i=>$row)
				{
					$v=isset($row['attrs']['id'])?$row['attrs']['id']:0;
					$cell[0] = new sm_Table_Cell();
					$cell[0]->insertArray(array(
							"tag"=>"td",
							"class"=>"tbw-selector",
							"data"=>"<input class=tbwchk type=checkbox name=id[] value=".$v." />"));
					$this->rows[$i]['cells']=array_merge($cell,$this->rows[$i]['cells']);
					
				}
				$cell = new sm_Table_Cell();
				$cell->insertArray(array(
						"tag"=>"th",
						"class"=>"tbw-selector sorter-false",
						"data"=>"<input type=checkbox name=all value=1 />"));
				$this->header[0]['cells']=array_merge(array($cell),$this->header[0]['cells']);
				
				$progress = new sm_HTML();
				$progress->setTemplateId("progress_bar_dlg","ui.tpl.html");
				$progress->insert("id","tbwProgressModal");
				$this->insert("html",$progress);
				
				
				if(isset($this->seletectedCmd))
				{
					$cmds['tbwCommands']=array_keys($this->seletectedCmd);
					return $cmds;
				}
		}
		return null;
		
				
	}
	
	protected function makeCmdBar(){
			$bar=null;
			if($this->seletectedCmd) // && $this->pager && $this->pager->get_total()>0)
			{
				$bar = new sm_HTML();
				$bar->insert("pre",'<div id="header-cmd"><ul class="nav navbar-nav">');
				
				$bar->insert("item",'<li><a id="tbw-refresh-cmd" title="Reload" href="'.$this->pageLink.'"><i class="glyphicon glyphicon-refresh"></i></a></li>');
				foreach($this->seletectedCmd as $command)
				{
					$name=isset($command['name'])?$command['name']:"";
					$class=isset($command['class'])?$command['class']:"";
					$confirm=isset($command['data-confirm'])?$command['data-confirm']:"";
					$title=isset($command['title'])?$command['title']:"";
					$icon = isset($command['icon'])?$command['icon']:"";
					$href = isset($command['href'])?$command['href']:"#";
					$label = isset($command['label'])?$command['label']:"";
					$toggle = isset($command['data-toggle'])?$command['data-toggle']:"";
					$target = isset($command['data-target'])?$command['data-target']:"";
					$bar->insert("item",'<li><a name="'.$name.'" data-confirm="'.$confirm.'" data-toggle="'.$toggle.'" data-target="'.$target.'" class="tbw_command '.$class.'" href="'.$href.'" title="'.$title.'"><i class="'.$icon.'"></i>'.$label.'</a></li>');
				}
				$bar->insert("end",'</ul></div>');
			}
		return $bar;
	}
	
	protected function makePaginator()
	{
		
		$bar=new sm_HTML();
		if($this->pager && $this->pager->get_total()>10)
		{
			$bar->insert("paginator",new sm_Paginator($this->pager));
		}
		return $bar;
	}
	
	protected function makeFooterFilters()
	{
		$bar=new sm_HTML();
		if($this->pager && $this->pager->get_total()>10)
		{
			$bar->insert("chooser-pre","<div id=chooser>");
			$bar->insert("chooser",sm_Form::buildForm("chooser", $this));
			$bar->insert("chooser-post","</div>");
		}
		return $bar;
	}
	
	protected function makeHeaderFilters()
	{
		
		$bar=null;
		if(($this->pager && $this->pager->get_total()>10) || count($this->filters)>0)
		{
			$bar=new sm_HTML();
			$bar->insert("filters-pre","<div id=filters class='navbar-form navbar-left'>");
			$bar->insert("filters",sm_Form::buildForm("TableDataViewFilters", $this,$this->id));
			$bar->insert("filters-post","</div>");
		}
		return $bar;
	}
	
	protected function makeTotal(){
		$bar=null;
		if($this->pager)
		{
			$bar=new sm_HTML();
			$bar->insert("total","<div id='tableview-total-rows' class='navbar-text pull-right'>Total: ".$this->pager->get_total()."</div>");
		}
		return $bar;
	}
		
	public function chooser_form(sm_Form $form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Inline,
				//"labelToPlaceholder" => 0,
				"action"=>$this->pageLink,
				"class"=>"entries_limit_selector"
		));
	
		$form->addElement(new Element_Hidden("link",$this->pageLink));
		$form->addElement(new Element_HTML('<label>'));
		$form->addElement(new Element_Select("", "limit", $this->pager->get_perPageOptions(),array('value'=>$this->pager->get_perPage(),'class'=>'input-sm')));
		$form->addElement(new Element_HTML(' entries</label>'));
		$form->addElement(new Element_Button("Show","submit",array('name'=>"show","class"=>"button light-gray btn-xs")));
	}
	
	public function TableDataViewFilters_form(sm_Form $form)
	{
	
		$form->configure(array(
				"prevent" => array("bootstrap", "jQuery", "focus"),
				"view" => new View_Search,
				//"labelToPlaceholder" => 0,
				"action"=>$this->pageLink,
				"class"=>"",
		));
		
		if(count($this->filters)>0)
		{ 
			$form->addElements($this->filters);
		}
		
		if($this->pager && $this->pager->get_total()>0)
		{
			//$form->addElement(new Element_HTML('<label>'));
			$form->addElement(new Element_Select("", "limit", $this->pager->get_perPageOptions(),array('value'=>$this->pager->get_perPage(),'class'=>'input-sm','shortDesc'=>"<label> entries</label>")));
			//$form->addElement(new Element_HTML(' entries</label>'));
			
		}
		$form->addElement(new Element_Button("Show","submit",array('name'=>"show","class"=>"button light-gray btn-xs")));
		$form->addElement(new Element_Hidden("link",$this->pageLink));
	}
	
	public function TableDataViewFilters_form_submit($data,sm_Form &$form)
	{
		if(sm_Pager::hasChangedLimit($data)){
			sm_Pager::set_limit($data);
		}
		$form->setRedirection($data['link']);
	}
	
	public function chooser_form_submit($data,sm_Form &$form)
	{
		if(sm_Pager::hasChangedLimit($data)){
			sm_Pager::set_limit($data);
		}
		$form->setRedirection($data['link']);
	}
}