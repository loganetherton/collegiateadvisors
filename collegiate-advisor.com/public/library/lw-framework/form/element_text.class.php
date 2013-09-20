<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_Text
	// Desc: Manages a single text element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_Text extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_TEXT;


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
			
			$element_html .= '<input type="text" class="text" id="'. $this->id .'" name="'. $this->id .'" ';
			
			// Is there a value for this element?
			if ( $this->value != '' )
				$element_html .= 'value="'. $this->value .'" ';
				
			$element_html .= '/>';			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', $element_html, $html, 1 );
			else
				$html .= $element_html;

		}

  }

?>
