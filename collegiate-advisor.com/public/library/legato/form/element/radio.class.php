<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element_Radio
	// Desc: Manages a single radio element.
	//--------------------------------------------------------------------------
	class Legato_Form_Element_Radio extends Legato_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type          = Legato_Form_Element::TYPE_RADIO;
		public $title         = '';  // The title of the radio button.
		public $name          = '';  // The name of the radios.
		public $element_value = '';  // The value that will be assigned when a user clicks the radio.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $name = '', $label = '', $element_value = '', $title = '', $descriptive_name = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );
			
			$this->name = ($name != '') ? $name : $id;
			$this->element_value = ($element_value != '') ? $element_value : $id;
			$this->title = $title;

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
			
			$element_html .= '<input type="radio" class="radio" id="'. $this->id .'" name="'. $this->name .'" value="' . $this->element_value . '" ';
			
			// Is there a value for this element?
			if ( $this->value == true )
				$element_html .= 'checked="checked" ';
				
			$element_html .= '/>';
			
			// If it is in a group and has a label add a label
			if ( $backtrace[1]['class'] == 'Legato_Form_Group' && $this->label != '' )
				$element_html .= '<label for="'. $this->id .'">' . $this->label . '</label>';
			
			// If it has a title add a title
			if ($this->title != '' )
				$element_html .= '<div class="title">'. $this->title .'</div>';
				
			if ( $backtrace[1]['class'] != 'Legato_Form_Group' )
				$element_html .= '<br />';
			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $element_html ), $html, 1 );
			else
				$html .= $element_html;

		}

  }