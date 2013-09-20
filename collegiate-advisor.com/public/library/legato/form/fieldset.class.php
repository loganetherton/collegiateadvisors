<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Fieldset
	// Desc: Manages a single fieldset, which contains multiple elements.
	//--------------------------------------------------------------------------
	class Legato_Form_Fieldset
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
		// Name: output()
		// Desc: Displays the fieldset.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{
			
			$match_count = 0;
			$matches = array();
			
			// Check if there is a literal for this fieldset.
			if ( $template ) 
				$match_count = preg_match_all( '/\{' . $this->id . '\}/', $html, $matches );	
			
			$begin_html = '<fieldset id="'. $this->id .'"><legend>'. $this->legend .'</legend>';

			// Loop through each element in this fieldset.
			foreach ( $this->elements as $element )
			{
				
				// Display this element.
				if ( !$template || ($match_count == 1) )
					$element->output( $fieldset_html );
				else
					$element->output( $html, $template );

			}  // Next element.
			
			$end_html = '</fieldset>';
			
			
			// Update the HTML accordingly.
			if ( $match_count == 1 )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', ($begin_html . $fieldset_html . $end_html) ), $html, 1 );
			}
			else if ( $match_count == 2 )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $begin_html ), $html, 1 );
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $end_html ), $html, 1 );
			}
			else
			{
				$html .= $begin_html . $fieldset_html . $end_html;
			}
				
		}
		
	}