<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_Hidden
	// Desc: Manages a single hidden element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_Hidden extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_HIDDEN;


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $value = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id );
			
			// Set the value.
			$this->value = $value;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{
			
			$html .= '<input type="hidden" id="'. $this->id .'" name="'. $this->id .'" value="' . $this->value . '" />';

		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Overwrites and does nothing.
		//------------------------------------------------------------------------
		public function validate()
		{
			
			/* Do Nothing */

		}

  }

?>
