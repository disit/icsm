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

class _Form extends Form
{
	protected $form_elements;
	function __construct($id="pfbc")
	{
		parent::__construct($id);
		$this->form_elements=array();
	}
	
	function addElement(Element $element)
	{
		$element_name = $element->getAttribute("name");
		if(empty($element_name))
			$element_name=uniqid("",true);
		$this->form_elements[$element_name]=$element;
	}
	
	function fill()
	{		
		foreach($this->form_elements as $f=>$e)
		{
			parent::addElement($e);
		}
	}
	
	public function addElementsBefore($elements,$name)
	{
		
		$old=$this->form_elements;
		$this->form_elements=array();
		foreach($elements as $k=>$v){
			$this->addElement($v);
		}
		$R=array();
		if($name)
		{
			
			foreach($old as $k=>$v){	
				if($k==$name)
					$R=array_merge($R,$this->form_elements);
				$R[$k]=$v;
			}
		}
		else
			$R=array_merge($this->form_elements,$old);
			
		$this->form_elements=$R;
	}
	
	public function getElement($element_name)
	{
		if(isset($this->form_elements[$element_name]))
			return $this->form_elements[$element_name];
		return null;
	}
	
	public function getView(){
		return $this->view;
	}
}

class sm_Form  extends sm_UIElement
{
	/**
	 * Form object
	 * @var object
	 */
	public $form;
	/**
	 * title string
	 * @var string
	 */
	protected $title;
	protected $properties;
	protected $id;
	protected $tpl;
	protected $name;
	
	public function __construct($id = "pfbc",$name=null)
	{
		$this->tpl = sm_Template::getInstance();
		$this->form = new _Form($id);
		$this->configure(array("prevent" => array("bootstrap","jQuery"),"name"=>$name)); //,"jQuery"
		$this->id=$id;
		$this->name=$name?$name:$id;
		if(isset($name))
			$this->form->configure(array("name"=>$name));
		$this->properties= array();
		$this->properties['caller']=sm_get_calling_class();
		$this->properties['submit'][sm_get_calling_class()]=$id."_form_submit";
		include "config.inc.php";
		$base=str_replace("\\","/",$baseUrl);
		$this->properties['redirect']= str_replace($base,"",$_SERVER['REQUEST_URI']);
		
		$this->title="Form Title";
	}
	
	static public function buildForm($id,$caller,$name=null)
	{
		$f = new sm_Form($id,$name);
		$method=$id."_form";
		
		if(is_object($caller))
			$f->properties['caller']=get_class($caller);
		else if(class_exists($caller))
			$f->properties['caller']=$caller;
		
		unset($f->properties['submit']);
		$f->properties['submit']=array();
		if($caller && method_exists($caller,$id."_form_submit"))
			$f->properties['submit'][$f->properties['caller']]=$id."_form_submit";
		$obj = $caller;
		if(method_exists($obj,$method))
		{
			//$obj = new $class();
			$obj->$method($f);
		}
		$f->clearValues($id);
		$f->clearErrors($id);
		//var_dump($f);
		sm_EventManager::handle(new sm_Event("FormAlter",$f));
		//sm_call_method("form_extend",$f);
		return $f;
		
	}
	
	static public function isValid($id,$clear=false)
	{
		$ret = Form::isValid($id,$clear);
		if(!$ret)
		{
			if(!empty($_SESSION["pfbc"][$id]["errors"]))
			{
				sm_Logger::write(var_export($_SESSION["pfbc"][$id]["errors"],true));
			}
			else 
				sm_Logger::write("Form not valid without errors!!!");
		}
		return $ret;
	}
	
	static public function recover($id)
	{
		if(!empty($_SESSION["pfbc"][$id]["form"]))
		{	
			$f=new sm_Form($id);
			$f->setForm(unserialize($_SESSION["pfbc"][$id]["form"]),$_SESSION["pfbc"][$id]['properties']);
			return $f;
		}
		else
			return "";
	}
	
	protected function setForm(Form $f,$properties=array())
	{
		$this->form=$f;
		$this->properties=$properties;
	}
	
	
	
	public function __call($method,$args)
	{
		//return $this->form->$method($args[0]);
		return call_user_func_array(array($this->form,$method),$args);
	}
	
	public function render()
	{
		$id=$this->form->getAttribute("id");
		$_SESSION["pfbc"][$id]['properties']=$this->properties;
		
		$this->form->addElement(new Element_Hidden("form",$id));
		$this->form->fill();
		$formHtml = $this->form->render(true);
		return $formHtml;
	}
	
	public function setSubmitMethod($method=null,$callObj=null)
	{
		if($callObj)
			$this->properties['submit'][get_class($callObj)]=$method;
		else 
			$this->properties['submit'][sm_get_calling_class()]=$method;
	}
	
	public function setRedirection($redirectUrl=null)
	{
		$this->properties['redirect']=$redirectUrl;
	}
	
	public function submit()
	{
		$values = array();
		if(!empty($_SESSION["pfbc"][$this->id]["values"]))
			$values = $_SESSION["pfbc"][$this->id]["values"];
		foreach ($this->properties['submit'] as $s=>$method)
		{
			
			if(method_exists($s,$method))
			{
				$obj = new $s();
				unset($values["form"]);
				$obj->$method($values,$this);
			}
		}
		if(!in_array("redirect",$this->getPrevent()))
		{
			if(isset($this->properties['redirect']) && $this->properties['redirect']!="")
			{			
				sm_app_redirect($this->properties['redirect']);
			}
		}
	}
	
	public function addElementsBefore($elements,$name)
	{
	 	$this->form->addElementsBefore($elements,$name);
	}
	
	public function getId(){
		return $this->form->getAttribute("id");
	}
	
	public function getName(){
		return $this->form->getAttribute("name");
	}
}