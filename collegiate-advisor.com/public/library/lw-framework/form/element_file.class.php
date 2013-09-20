<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_File
	// Desc: Manages a single file element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_File extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_FILE;
		public $accept = '';  // What file types to accept.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $accept = '', $descriptive_name = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );
			
			// Store the accept data.
			$this->accept = $accept;

		}
		
		
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
			
			$element_html .= '<input type="file" class="file" id="'. $this->id .'" name="'. $this->id .'" ';
				
			// Accept.
			if ( $this->accept != '' )
				$element_html .= 'accept="' . $this->accept . '" ';
				
			$element_html .= '/>';			
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', $element_html, $html, 1 );
			else
				$html .= $element_html;

		}

  }

?>
