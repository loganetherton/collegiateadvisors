<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Validator
	// Desc: Exploses methods to validate the input parameters passed in to the
	//       form class.
	//--------------------------------------------------------------------------
	class LW_Form_Validator
	{


		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $form_object = NULL;  // The Form object for the form we are validating.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( &$form_object )
		{

			// Store the data passed in.
			$this->form_object = $form_object;

		}


		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Validates the form.
		//------------------------------------------------------------------------
		public function validate()
		{

			$errors = false;
			
			// Loop through each element in the form.
			foreach ( $this->form_object->elements as $element_id => $element )
			{
				
				// Only store values for elements other than the listed.
				if ( $element->type != LW_Form_Element::TYPE_BUTTON && 
				     $element->type != LW_Form_Element::TYPE_SUBMIT && 
					 $element->type != LW_Form_Element::TYPE_RESET )
				{
					
					$this->store_value( $element_id );
					
				}  // End if storing value.
				
			}  // Next element.
			
			// We have to loop through the elements once to store and then we can
			// validate them.
			foreach ( $this->form_object->elements as $element_id => $element )
			{
				
				// Validate the element.
				$return_val = $element->validate();
				
				if ( $return_val )
					$errors = true;
				
			}  // Next element.
			
			// Loop through each group in the form.
			foreach ( $this->form_object->groups as $group_id => $group )
			{
				// Only store values for groups containing checkboxes.
				$this->store_group_value( $group_id );
				
				// Validate the element.
				//$return_val = $element->validate();
				
				//if ( $return_val )
				//	$errors = true;
				
			}  // Next element.
			
			return !$errors;

		}

		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: store_value()
		// Desc: Gets the value for a certain element and stores it.
		//------------------------------------------------------------------------
		private function store_value( $element_id )
		{
			
			// Get the element.
			$element = $this->form_object->elements[$element_id];
			
			// Element type?
			if ( $element->type == LW_Form_Element::TYPE_CHECKBOX )
			{			
				
				if ( $element->name != '' && is_array( $_POST[$element->name] ) )
					$element->value = in_array( $element->element_value, $_POST[$element->name] );
				else	
					$element->value = ($_POST[$element_id] == 'on');
				
			}  // End if checkbox.
			else if ( $element->type == LW_Form_Element::TYPE_RADIO )
			{			
					
				$element->value = ($_POST[$element->name] == $element->element_value);
				
			}  // End if radio.
			else if ( $element->type == LW_Form_Element::TYPE_FILE )
			{
				
				$element->value = $_FILES[$element_id];
				
			}  // End if file.
			else
			{
				
				$element->value = $_POST[$element_id];
				
			}  // End else if.
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: store_group_value()
		// Desc: Gets the value for a group and stores it.
		//------------------------------------------------------------------------
		private function store_group_value( $group_id )
		{
			
			// Get the group.
			$group = $this->form_object->groups[$group_id];
			
			// Group type?
			if ( $group->elements[0]->type == LW_Form_Element::TYPE_CHECKBOX ||
			     $group->elements[0]->type == LW_Form_Element::TYPE_RADIO )
			{
				
				// Store the value.
				$group->value = $_POST[$group_id];
				
			}
			else if ( $group->elements[0]->type == LW_Form_Element::TYPE_TEXT ||
			          $group->elements[0]->type == LW_Form_Element::TYPE_TEXTAREA ||
			          $group->elements[0]->type == LW_Form_Element::TYPE_FILE ||
			          $group->elements[0]->type == LW_Form_Element::TYPE_PASSWORD ||
			          $group->elements[0]->type == LW_Form_Element::TYPE_SELECT )
			{
				
				// Loop through each element in the group.
				foreach ( $group->elements as $element )
				{
					
					// Store the value.
					if ( $element->value != '' )
						$group->value[$element->id] = $element->value;
					
				}  // Next element.
				
			}
			
		}
		
	}

?>
