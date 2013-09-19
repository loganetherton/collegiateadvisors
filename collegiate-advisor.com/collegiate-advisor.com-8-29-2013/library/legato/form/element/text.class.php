<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_Text
	// Desc: Manages a single text element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_Text extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = Legato_Form_Element::TYPE_TEXT;


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------		
		//------------------------------------------------------------------------
		// Name: output()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{
			
			$element_html .= '<div class="element">';
			
			// Required?
			if ( $this->label !== false )
			{
				if ( $this->rules['required'] )
					$element_html .= '<label for="'. $this->id .'"><em>'. $this->label .'</em></label>';
				else
					$element_html .= '<label for="'. $this->id .'">'. $this->label .'</label>';
			}
			
			$element_html .= '<input type="text" class="text" id="'. $this->id .'" name="'. $this->id .'" ';
			
			// Is there a value for this element?
			if ( $this->value != '' )
				$element_html .= 'value="'. $this->value .'" ';
				
			$element_html .= '/>';			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}

  }