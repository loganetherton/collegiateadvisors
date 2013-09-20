<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_Button
	// Desc: Manages a single button element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_Button extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = Legato_Form_Element::TYPE_BUTTON;
		public $title = '';  // The title of the button.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $title )
		{
			
			// Call the parent constructor.
			parent::__construct( $id );
			
			// Store the title.
			$this->title = str_replace( '$', '\$', $title );

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
			
			if ( $backtrace[1]['class'] != 'Legato_Form_Group' )
				$element_html .= '<label></label>';			
			
			$element_html .= '<input type="button" class="button" id="'. $this->id .'" name="'. $this->id .'" value="'. $this->title .'" />';
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Overwrites the validate function.
		//------------------------------------------------------------------------
		public function validate()
		{

			return true;

		}

  }