<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element_Checkbox
	// Desc: Manages a single checkbox element.
	//--------------------------------------------------------------------------
	class LW_Form_Element_Checkbox extends LW_Form_Element
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type = LW_Form_Element::TYPE_CHECKBOX;
		public $title          = '';  // The title of the checkbox.
		public $name           = '';  // The name of the checkboxes. Filled in by a group.
		public $element_value  = '';  // The value that will be assigned when a user clicks the checkbox.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = ' ', $element_value = '', $title = '', $descriptive_name = '' )
		{
			
			// Call the parent constructor.
			parent::__construct( $id, $label, $descriptive_name );
			
			$this->element_value = ($element_value != '') ? $element_value : $id;
			$this->title = str_replace( '$', '\$', $title );

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
			{
			
				// Required?
				if ( $this->rules['required'] )
					$element_html .= '<label for="'. $this->id .'"><em>'. $this->label .'</em></label>';
				else
					$element_html .= '<label for="'. $this->id .'">'. $this->label .'</label>';
					
			}  // End if not in a group.
			
			if ( $this->name != '' )
			{
				$name = $this->name . '[]';
				$value = $this->element_value;
			}
			else
			{
				$name = $this->id;
				$value = '';
			}
			
			$element_html .= '<input type="checkbox" class="checkbox" id="' . $this->id . '" name="' . $name . '" ';
			
			// Value?
			if ( $value != '' )
				$element_html .= 'value="' . $value . '" ';
			
			// Should this element be checked?
			if ( $this->value == true )
				$element_html .= 'checked="checked" ';
			
			$element_html .= '/>';
			
			// If it is in a group and has a label add a label
			if ( $backtrace[1]['class'] == 'LW_Form_Group' && $this->label != '' )
				$element_html .= '<label for="'. $this->id .'">' . $this->label . '</label>';
			
			// If it has a title add a title
			if ($this-> title != '' )
				$element_html .= '<div class="title">'. $this->title .'</div>';
				
			if ( $backtrace[1]['class'] != 'LW_Form_Group' )
				$element_html .= '<br />';
				
			$element_html .= '</div>';
			
			if ( $template )
				$html = preg_replace( '/\{' . $this->id . '\}/', $element_html, $html, 1 );
			else
				$html .= $element_html;

		}

  }

?>
