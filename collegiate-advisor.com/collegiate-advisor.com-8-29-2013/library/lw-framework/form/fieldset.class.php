<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Fieldset
	// Desc: Manages a single fieldset, which contains multiple elements.
	//--------------------------------------------------------------------------
	class LW_Form_Fieldset
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $form      = NULL;     // The form that holds this fieldset.
		public $id        = '';       // The fieldset's ID.
		public $legend    = array();  // The fieldset's legend attribute.
		public $elements  = array();  // The array of elements that are in this fieldset.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $legend = '' )
		{

			// Store the data.
			$this->id = $id;
			$this->legend = $legend;
			
			// If no legend passed in, try to create one.
			if ( !$this->legend )
				$this->legend = ucwords( str_replace( '_', ' ', $this->id ) );

		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the fieldset.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{
			
			// Check if there is a literal for this fieldset.
			if ( $template ) $literal_flag = preg_match( '/\{' . $this->id . ' \/\}/', $html );
			
			
			$begin_html = '<fieldset id="'. $this->id .'"><legend>'. $this->legend .'</legend>';

			// Loop through each element in this fieldset.
			foreach ( $this->elements as $element )
			{
				
				// Display this element.
				if ( !$template || $literal_flag )
					$element->display( $fieldset_html );
				else
					$element->display( $html, $template );

			}  // Next element.
			
			$end_html = '</fieldset>';
			
			
			// Update the HTML accordingly.
			if ( $literal_flag )
			{
				$html = preg_replace( '/\{' . $this->id . ' \/\}/', $begin_html . $fieldset_html . $end_html, $html, 1 );
			}
			else if ( $template )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', $begin_html, $html, 1 );
				$html = preg_replace( '/\{\/' . $this->id . '\}/', $end_html, $html, 1 );
			}
			else
			{
				$html .= $begin_html . $fieldset_html . $end_html;
			}
				
		}

  }

?>
