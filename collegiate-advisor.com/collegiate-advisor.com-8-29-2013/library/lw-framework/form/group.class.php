<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Group
	// Desc: Manages a group of elements. Extends the element class.
	//--------------------------------------------------------------------------
	class LW_Form_Group extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $spacer      = '';       // The group's spacer.
		public $elements    = array();  // The group's elements.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = ' ', $elements = array(), $spacer = '', $descriptive_name = '' )
		{
			
			parent::__construct( $id, $label, $descriptive_name );

			// Store the data.
			$this->elements = $elements;
			$this->spacer = $spacer;

		}
		
		
		//------------------------------------------------------------------------
		// Name: set_default()
		// Desc: Sets the default value for the group. Currently only used with
		//       radio buttons and checkboxes.
		//------------------------------------------------------------------------
		public function set_default( $default )
		{

			// Radio or checkbox?
			if ( is_array( $default ) )
			{
				
				foreach( $default as $element_id )
					$this->form->set_default( $element_id, true );
				
			}  // End if checkbox.
			else
			{
				
				$this->form->set_default( $default, true );
				
			}  // End if radio button.
			
			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{
			
			// Check if there is a literal for this group.
			if ( $template ) $literal_flag = preg_match( '/\{' . $this->id . ' \/\}/', $html );
			

			$begin_html = '<div class="group">';
			
			// Show the label.
			if ( $this->label != '' )
			{
				
				// Required?
				if ( $this->rule['required'] )
			    	$begin_html .= '<label><em>'. $this->label .'</em></label>';
				else
					$begin_html .= '<label>'. $this->label .'</label>';
					
			}
			
			$begin_html .= '<div class="group_elements">';
			
			// Loop through each element in the group.
			$i = 1;
			foreach ( $this->elements as $element )
			{

				// Which iteration is this?
				if ( $i != 1 )
				{

					// Show the spacer.
					$group_html .= $this->spacer;

				}  // End if not first iteration.

				// Display this element.
				if ( !$template || $literal_flag )
					$element->display( $group_html );
				else
					$element->display( $html, $template );

				$i++;

			}  // Next element.

			$end_html .= '</div><br /></div>';
			

			// Update the HTML accordingly.
			if ( $literal_flag )
			{
				$html = preg_replace( '/\{' . $this->id . ' \/\}/', $begin_html . $group_html . $end_html, $html, 1 );
			}
			else if ( $template )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', $begin_html, $html, 1 );
				$html = preg_replace( '/\{\/' . $this->id . '\}/', $end_html, $html, 1 );
			}
			else
			{
				$html .= $begin_html . $group_html . $end_html;
			}

		}

  }

?>
