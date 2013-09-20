<?php

	//--------------------------------------------------------------------------
	// Class: Legato_Form_Group
	// Manages a group of elements.
	//--------------------------------------------------------------------------
	class Legato_Form_Group extends Legato_Form_Element
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
		// Constructor: __construct()
		// The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $spacer = '', $descriptive_name = '' )
		{
			
			parent::__construct( $id, $label, $descriptive_name );

			// Store the data.
			$this->spacer = $spacer;

		}
		
		
		//------------------------------------------------------------------------
		// Function: add()
		// Adds an element to the group.
		//------------------------------------------------------------------------
		public function add( $element )
		{
			
			// Set the type of this group.
			if ( $this->type == Legato_Form_Element::TYPE_ELEMENT )
				$this->type = $element->type;
			
			// Add the element to this group.
			$this->elements[] = $element;
			
			// Add the element to the form.
			$this->form->elements[$element->id] = $element;	
			$element->form = &$this->form;
			
			// Special element conditional.
			if ( $element instanceof Legato_Form_Element_Submit )
				$this->form->submit_button = $element;
			else if ( $element instanceof Legato_Form_Element_File )
				$this->form->multipart_enctype = true;
			else if ( $element instanceof Legato_Form_Element_CheckboxMultiple )
				$element->name = $this->id . '[]';
			else if ( $element instanceof Legato_Form_Element_Radio )
				$element->name = $this->id;
				
			// Get rid of validation rules.
			$element->rule( 'required', false );
	
			// Return the newly added element.
			return $element;

		}
		
		
		//------------------------------------------------------------------------
		// Function: default_value()
		// Sets the default value for the group. Currently only used with radio
		// buttons and checkboxes.
		//------------------------------------------------------------------------
		public function default_value( $default )
		{
			
			// Which type of elements in this group.
			if ( $this->type == Legato_Form_Element::TYPE_CHECKBOX_MULTIPLE )
			{
				
				foreach( $default as $element_id )
				{
					if ( !$element_id )
						continue;
						
					$this->form->$element_id->default_value( true );
				}
				
			}  // End if checkbox.
			else if ( $this->type == Legato_Form_Element::TYPE_RADIO )
			{
				
				foreach( $this->elements as $element )
				{
					
					if ( $element->value == $default )
					{
						$element->default_value( true );
						break;
					}
					
				}
				
			}  // End if radio button.
			
			return $this;

		}
		
		
		//------------------------------------------------------------------------
		// Function: output()
		// Displays the element.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{
			
			// Check if there is a literal for this group.
			if ( $template ) 
				$match_count = preg_match_all( '/\{' . $this->id . '\}/', $html, $matches );	
			

			$begin_html = '<div class="group">';
			
			// Show the label.
			if ( $this->label !== false )
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
				if ( !$template || ($match_count == 1) )
					$element->output( $group_html );
				else
					$element->output( $html, $template );

				$i++;

			}  // Next element.

			$end_html .= '</div><br /></div>';
			

			// Update the HTML accordingly.
			if ( $match_count == 1 )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', ($begin_html . $group_html . $end_html) ), $html, 1 );
			}
			else if ( $match_count == 2 )
			{
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $begin_html ), $html, 1 );
				$html = preg_replace( '/\{' . $this->id . '\}/', str_replace( '$', '\$', $end_html ), $html, 1 );
			}
			else
			{
				$html .= $begin_html . $group_html . $end_html;
			}

		}

  }