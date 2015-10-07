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

class sm_DashboardView extends sm_ViewElement
{
	
	function __construct($data=null)
	{
		if(is_a($data,"sm_Dashboard"))
		{
			$model['title']=$data->getTitle();
			$model['boards']=$data->getBoards();
			$model['refreshUrl']=$data->getRefreshUrl();
			$model['id']=$data->getId();
		}
		else
			$model = $data;
		parent::__construct($model);
		
	}
	
	/**
	 * Create the HTML code for the module.
	 */
	public function build() {
		
		switch($this->op)
		{
			case 'view':
				$this->dashboard_page();
				break;
			case 'panel':
				$this->dashboard_panel();
				break;
			default:
				break;
		}
	}
	
	public function dashboard_page() {
	
		if(isset($this->model['title']))
			$title=$this->model['title'];
		else 
			$title="Dashboard";
		$content=$this->dashboard_create();
		$boards=array();
		$css=array();
		if(count($content)>0)
		{
			foreach($content as $c=>$val)
			{
				$board = new sm_HTML();
				if(isset($val['html']))
				{
					$board->insert($c,$val['html']);
					if(isset($val['css']))
					{
						$css[$val['css']['file']]=$val['css']['path'];
					}
				}
				else 
				{
					foreach ($val as $i=>$item)
					{
						if(is_array($item))
						{
							$board->insert($c."_".$i,$item['html']);
							if(isset($item['css']))
							{
								$css[$item['css']['file']]=$item['css']['path'];
							}
						}
						else 
							$board->insert($c."_".$i,$item);
						
						
						
					}
				}
				$boards[]=$board;
				
			}
		}

		$this->uiView=new sm_Page("Dashboard");
		$this->uiView->setTitle($title);
		
		$html = new sm_HTML();
	
		$html->insert("pre","<div id='dashboard'>");
		$html->insert("time","<div id=server_time><p class='label label-default'>Server Time: ".date("d/m/Y H:i:s",time())."</p></div>");
		$html->insert("boards",$boards);
		$html->insert("post","</div>");
		$this->uiView->insert($html);
		foreach($css as $file=>$path)
		{
			$this->uiView->addCss($file,"main",$path);
		}
		$this->uiView->addCss("dashboard.css","main",sm_DashboardPlugin::$instance->getFolderUrl("css"));
		$this->uiView->addJs("dashboard.js","main",sm_DashboardPlugin::$instance->getFolderUrl("js"));
		$refreshurl = isset($this->model['refreshUrl'])?$this->model['refreshUrl']:"dashboard/refresh";
		$refresh="setInterval(function(){
				dashboard_refresh('".$refreshurl."');
				},1000*60);";
		$this->uiView->addJs($refresh);
		//$this->addView();
		
	}
	
	public function dashboard_panel() {
	
		if(!empty($this->model['title']))
			$title=$this->model['title'];
		else 
			$title="Dashboard";
		
		$content=$this->dashboard_create();
		$boards="";
		$this->uiView = $panel = new sm_Panel("dashboard");
		$panel->setTitle($title);
		if(count($content)>0)
		{
			foreach($content as $c=>$val)
			{
				//if($boards.=$val['html'];
				if(isset($val['html']))
					$panel->insert($val['html']);
				else 
				{
					foreach ($val as $i=>$item)
					{
						if(is_array($item))
							$panel->insert($item['html']);
						else
							$panel->insert($item);
					}
				}
			}
		}
		$panel->icon("<img src='img/dashboard.png' />");
		
	}
	
	public function dashboard_create()
	{
		$content=array();
		if(isset($this->model['boards']))
		{
			foreach($this->model['boards'] as $i=>$board)
			{
				if(isset($this->model['id']))
					$board->setref_id($this->model['id']);
				$class = $board->getmodule();	
				$method = $board->getmethod()!=""?$board->getmethod():"dashboard";
				if(class_exists($class,true) && method_exists($class,$method))
				{
					
					$data=call_user_func_array(array(new $class, $method), array($board));
					
					if(isset($data['tpl']) && isset($data['tpl_path']))
					{
							$this->setTemplateId($data['tpl'],$data['tpl_path']);
							$this->tpl->addTemplatedata($data['tpl'],$data['content']);
							$html['html']=$this->tpl->getTemplate($data['tpl']);

							if(isset($data['css']) && isset($data['css_path']))
								$html['css']=array("file"=>$data['css'],"path"=>$data['css_path']);
							$content[$board->gettitle()][]=$html;
						
					}
					else if(isset($data['callback']))
						$content[$board->gettitle()][]=call_user_func_array(array(new $data['callback']['class'], $data['callback']['method']),$data['callback']['args']);
					
				}
				
			}
		}
		
		return $content;
	}
	
	/*static function menu(sm_MenuManager $menu)
	{
		$menu->setMainLink("Dashboard","dashboard","dashboard");
	}*/
}