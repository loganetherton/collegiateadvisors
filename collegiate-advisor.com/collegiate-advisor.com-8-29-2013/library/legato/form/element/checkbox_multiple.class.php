<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_CheckboxMultiple
	// Desc: Manages a single checkbox element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_CheckboxMultiple extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = Legato_Form_Element::TYPE_CHECKBOX_MULTIPLE;
		public $name           = '';  // The name of the checkboxes. Filled in by a group.
		public $element_value  = '';  // The value that will be assigned when a user clicks the checkbox.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $element_value = '', $descriptive_name = '' )
		{
			
			if ( $label === '' )
				$label = ucwords( str_replace( array( '_', '-' ), ' ', $id ) );
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );
			
			$this->element_value = ($element_value != '') ? $element_value : $id;

		}
		
		
		//------------------------------------------------------------------------
		// Name: output()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{
			
			$element_html .= '<div class="element">';
			
			$element_html .= '<input type="checkbox" class="checkbox" id="' . $this->id . '" name="' . $this->name . '" ';
			$element_html .= 'value="' . $this->element_value . '" ';
			
			// Should this element be checked?
			if ( $this->value == true )
				$element_html .= 'checked="checked" ';
			
			$element_html .= '/>';
			
			if ( $this->label !== false )
				$element_html .= '<label for="'. $this->id .'">' . $this->label . '</label>';
							
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}

  }