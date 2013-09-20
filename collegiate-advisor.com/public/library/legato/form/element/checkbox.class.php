<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_Checkbox
	// Desc: Manages a single checkbox element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_Checkbox extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = Legato_Form_Element::TYPE_CHECKBOX;


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $descriptive_name = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );

		}
		
		
		//------------------------------------------------------------------------
		// Name: output()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{
			
			$element_html .= '<div class="element">';
			
			// If not in a group, display the label.
			$backtrace = debug_backtrace();
			
			if ( $backtrace[1]['class'] != 'Legato_Form_Group' && $this->label !== false )
			{
			
				// Required?
				if ( $this->rules['required'] )
					$element_html .= '<label for="'. $this->id .'"><em>'. $this->label .'</em></label>';
				else
					$element_html .= '<label for="'. $this->id .'">'. $this->label .'</label>';
					
			}  // End if not in a group.
			
			$element_html .= '<input type="checkbox" class="checkbox" id="' . $this->id . '" name="' . $this->id . '" ';
			
			// Should this element be checked?
			if ( $this->value == true )
				$element_html .= 'checked="checked" ';
			
			$element_html .= '/><br /></div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}

  }