<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_Select
	// Desc: Manages a single select element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_SelectMultiple extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = Legato_Form_Element::TYPE_SELECT_MULTIPLE;
		public $options  = array();  // The array of this select's options.
		public $size     = 5;        // The number of selections shown.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $options = array(), $size = 5, $descriptive_name = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );
			
			$this->options = $options;
			$this->size = $size;

		}
		
		
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
			
			$element_html .= '<select class="select" id="'. $this->id .'" name="'. $this->id .'[]" size="' . $this->size . '" multiple="multiple">';
			
			// Loop through the options.
			foreach ( $this->options as $value => $option )
			{
				
				// Is this the selected option?
				if ( in_array( $value, $this->value ) )
					$element_html .= '<option value="' . $value . '" selected="selected">' . $option . '</option>';
				else
					$element_html .= '<option value="' . $value . '">' . $option . '</option>';
				
			}  // Next option.
				
			$element_html .= '</select>';			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}

  }