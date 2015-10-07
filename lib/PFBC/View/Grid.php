<?php
class View_Grid extends View {
	protected $class = "View_Grid";
	public $map;

	public function render() {
		$this->_form->appendAttribute("class", $this->class);

		echo '<form', $this->_form->getAttributes(), '><fieldset>';
		$this->_form->getErrorView()->render();

		$elements = $this->_form->getElements();
		$elementSize = sizeof($elements);
		$elementCount = 0;
		$first=true;

		$layoutIndex=0;
		$elementCountVariable = 0;

		for($e = 0; $e < $elementSize; ++$e) {
			$element = $elements[$e];

			if($element instanceof Element_Hidden || $element instanceof Element_HTML)
				$element->render();
			elseif($element instanceof Element_Button) {
				if($e == 0 || !$elements[($e - 1)] instanceof Element_Button)
					echo '<div class="form-actions">';
				else
					echo ' ';

				$element->render();

				if(($e + 1) == $elementSize || !$elements[($e + 1)] instanceof Element_Button)
					echo '</div>';
			}
			else {

				$field_per_row=1;
				if(isset($this->map['layout'][$layoutIndex]))
					$field_per_row=$this->map['layout'][$layoutIndex];

				//print $field_per_row." : ".$elementCountVariable;
				if($first) 
					print '<div class="row">'; 
				
				elseif($elementCountVariable>=$field_per_row)
				{
					print '<div class="row">';		
					$elementCountVariable=0;
				}

				$span_class='';
				if(isset($this->map['widths'][$elementCount]))
					$span_class=" col-md-".$this->map['widths'][$elementCount];

				if(!$element instanceof Element_Checkbox && !$element instanceof Element_Radio && !$element instanceof Element_YesNo)
					$element->appendAttribute("class","form-control");
				echo '<div class="form-group'.$span_class.'">', $this->renderLabel($element), "<div>",$element->render(),"</div>",$this->renderDescriptions($element), '</div>';
				++$elementCount;
				$elementCountVariable++;
				$first=false;
				if($elementCountVariable==$field_per_row)
				{
					print '</div>';
					$layoutIndex++;
				}
			}
		}

		//echo '</div></fieldset></form>';
		echo '</fieldset></form>';
	}

	protected function renderLabel(Element $element) {
		$label = $element->getLabel();
		if(!empty($label)) {
			echo '<label class="control-label" for="', $element->getAttribute("id"), '">';
			if($element->isRequired())
				echo '<span class="required">* </span>';
			echo $label, '</label>';
		}
	}
}