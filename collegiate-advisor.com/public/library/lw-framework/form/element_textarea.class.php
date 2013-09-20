<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_Textarea
	// Desc: Manages a single textarea element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_Textarea extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_TEXTAREA;


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{
			
			$element_html .= '<div class="element">';
			
			// Required?
			if ( $this->rules['required'] )
				$element_html .= '<label for="'. $this->id .'"><em>'. $this->label .'</em></label>';
			else
				$element_html .= '<label for="'. $this->id .'">'. $this->label .'</label>';			
			
			$element_html .= '<textarea class="textarea" id="' . $this->id . '" name="' . $this->id . '">';
			
			// Is there a value for this element?
			if ( $this->value != '' )
				$element_html .= $this->value;
				
			$element_html .= '</textarea>';			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', $element_html, $html, 1 );
			else
				$html .= $element_html;

		}

  }

?>
