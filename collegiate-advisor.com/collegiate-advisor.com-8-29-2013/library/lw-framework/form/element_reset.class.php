<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_Reset
	// Desc: Manages a single reset element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_Reset extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_RESET;
		public $title = '';  // The title of the reset button.


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
			$this->title = $title;

		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{
			
			$element_html .= '<div class="element">';
			
			// If not in a group, display the label.
			$backtrace = debug_backtrace();
			
			if ( $backtrace[1]['class'] != 'LW_Form_Group' )
				$element_html .= '<label></label>';
			
			$element_html .= '<input type="reset" class="reset" id="'. $this->id .'" name="'. $this->id .'" value="'. $this->title .'" />';
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', $element_html, $html, 1 );
			else
				$html .= $element_html;

		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Overwrites the validate function.
		//------------------------------------------------------------------------
		public function validate()
		{

			return false;

		}

  }

?>
