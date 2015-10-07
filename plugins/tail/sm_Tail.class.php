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

class sm_Tail implements sm_Module
{
	protected $instance;
	public function __construct()
	{
		
	}
	
	static public function install($db)
	{
		
	
	}
	
	static public function uninstall($db)
	{
		
	}
	
	static public function getInstance()
	{
		if(!self::$instance)
			self::$instance=new sm_Tail();
		return self::$instance;
	}
	
	function refresh($file){
				
		clearstatcache();
		$data=array();
		$data["refresh"]=true;
		$data["content"]="";
		if ( is_file($file) ) {
			if ( is_readable($file) ) {
		
				$archive=file($file);
				
				$totalLines=count($archive);
				$lines=10;
				$cont="";
				for($g=1;$g<$totalLines;$g++){
					if($g>=($totalLines-$lines)){
						$cont.=trim(htmlentities($archive[$g]))."<br>";
					}
				}
				if(!isset($_SESSION[$file]['content']) ){
					$_SESSION[$file]['content']=$cont;
				}
				$top="";
				$medium="";
				$bottom="";
				if(!isset($_SESSION[$file]['t']))
					$_SESSION[$file]['t']="";
				if($_SESSION[$file]['t'] != $cont){
					$t=explode("<br>",$cont);
					$k=explode("<br>",$_SESSION[$file]['content']);
					$b=$this->array_diff_all($t,$k);
					//sort($b['del']);
					foreach($b['del'] as $k => $value){
						if(!empty($value)){
							$top.=$value."<br>";
						}
					}
					
					for($m=0;$m<(count($b['equ'])-1);$m++){
						if(isset($b['equ'][$m]))
							$medium.=$b['equ'][$m]."<br>";
					}
					
					foreach($b['add'] as $k => $value){
						if(!empty($value)){
							$bottom.=$value."<br>";
						}
					}
					$_SESSION[$file]['t']=$cont;
					$_SESSION[$file]['content'].=$bottom;
					$data["refresh"]=true;
				}else{
					$data["refresh"]=false;
				}
				//echo $_SESSION['contenido']."<br><br>".$cont;
				$cont2=$top.$medium.$bottom;
				if(empty($cont2)){
					$data["content"]=$_SESSION[$file]['content'];
				}
				else 
					$data["content"]= $_SESSION[$file]['content']."<div id=\"tailLogCursor\">_</div>";
				
			} else {
				$data["content"]= " file without read permissions";
				
			}
		} else {
			$data["content"]= " file does not exist";
			$_SESSION[$file]['t']="";
			$_SESSION[$file]['content']="";
		}
		return $data;
	}	
	
	function array_diff_all($arr_new,$arr_old)
	{
		$arr_equ=array_intersect($arr_new,$arr_old);
		$arr_del=array_diff($arr_old,$arr_new);
		$arr_add=array_diff($arr_new,$arr_old);
		return $diff=array("equ"=>$arr_equ, "del"=>$arr_del, "add"=>$arr_add);
	}
	
	
	
		
}
