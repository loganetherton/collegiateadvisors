<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form
	// Desc: Allows you to easily create powerful forms.
	//--------------------------------------------------------------------------
	class LW_Form
	{

		//------------------------------------------------------------------------
		// Public Static Variables
		//------------------------------------------------------------------------
		public static $element_types = array( 'text', 'button', 'checkbox', 'hidden', 'submit', 'reset', 'radio', 'file', 'password', 'select', 'textarea' );


		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $elements = array();  // The elements in the form.
		public $groups   = array();  // The groups in the form.


		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $id               = '';       // The form's ID.
		private $options          = array();  // The form object's options.
		private $fieldsets        = array();  // The fieldsets.
		private $active_fieldset  = null;     // The active fieldset.
		private $submit_button    = null;     // The submit button.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $options_array = array() )
		{

			// Store the form name.
			$this->id = $id;

			// Store the optional options.
			$this->options['form_action']          = $options_array['form_action'];
			$this->options['redirect_url']         = $options_array['redirect_url'];
			$this->options['javascript_validate']  = isset( $options_array['javascript_validate'] ) ? $options_array['javascript_validate'] : true;

		}
		
		
		//------------------------------------------------------------------------
		//(Exclude)
		// Function: __get()
		// Used so that the user can get elements without touching the internals.
		//------------------------------------------------------------------------
		public function __get( $id )
		{
			
			// Return what they requested.
			if ( $this->elements[$id] )
				return $this->elements[$id];
			else if ( $this->groups[$id] )
				return $this->groups[$id];
			else if ( $this->fieldsets[$id] )
				return $this->fieldsets[$id];
				
			// If we couldn't find anything, just return false.
			return false;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: add_fieldset()
		// Desc: Adds a fieldset to the form.
		//------------------------------------------------------------------------
		public function add_fieldset( $fieldset )
		{

			// Check if we have a fieldset with this ID already stored.
			if ( array_key_exists( $fieldset->id, $this->fieldsets ) === true )
			{

				// Add debugging item.
				LW_Debug_Debugger::add_item( 'The fieldset ' . $fieldset->id . ' has already been added to the system. <br /> Can\'t add a fieldset with the same ID twice.' );
				return false;

			}  // End if fieldset already stored.

			// Store the fieldset.
			$this->fieldsets[$fieldset->id] = $fieldset;
			
			// Set the fieldset's data.
			$fieldset->form = $this;

			// Set as the active fieldset.
			$this->active_fieldset = $fieldset;

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_element()
		// Desc: Adds an input element to the form.
		//------------------------------------------------------------------------
		public function add_element( $element )
		{

			// If the element is false, return false.
			if ( !$element )
			{

				LW_Debug_Debugger::add_item( 'Could not add element ' . $element->id . ' to form.' );
				return false;

			}  // End if failed.

			// Store the element.
			$this->active_fieldset->elements[] = $element;
			$this->elements[$element->id] = $element;
			
			// Set the data for the element.
			$element->form = $this;
			
			// Special element conditional.
			if ( is_a( $element, 'LW_Form_Element_Submit' ) )
				$this->submit_button = $element;
			else if ( is_a( $element, 'LW_Form_Element_File' ) )
				$this->multipart_enctype = true;

			// Return the newly created element.
			return $this->elements[$element->id];

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_group()
		// Desc: Groups input elements and adds them to the form.
		//------------------------------------------------------------------------
		public function add_group( $group )
		{
			
			// Set the form.
			$group->form = $this;
			
			// Store the elements.
			foreach ( $group->elements as $element )
			{
				
				$this->elements[$element->id] = $element;
				
				// Set the form.
				$element->form = $this;
				
				// Special element conditional.
				if ( is_a( $element, 'LW_Form_Element_Submit' ) )
					$this->submit_button = $element;
				else if ( is_a( $element, 'LW_Form_Element_File' ) )
					$this->multipart_enctype = true;
				else if ( is_a( $element, 'LW_Form_Element_Checkbox' ) )
					$element->name = $group->id;
				else if ( is_a( $element, 'LW_Form_Element_Radio' ) )
					$element->name = $group->id;
					
				// Get rid of validation rules.
				$element->add_rule( 'required', false );
				
			}

			// Store the group.
			$this->active_fieldset->elements[] = $group;
			$this->groups[$group->id] = $group;

			// Return the newly created group.
			return $this->groups[$group->id];

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_rule()
		// Desc: Adds a rule to a form element.
		//------------------------------------------------------------------------
		public function add_rule( $id, $rule, $value )
		{
			
			if ( $this->elements[$id] )
				return $this->elements[$id]->add_rule( $rule, $value );
			else if ( $this->groups[$id] )
				return $this->groups[$id]->add_rule( $rule, $value );
			else
				return false;  // Failure.

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_rules()
		// Desc: Adds rules to a form element.
		//------------------------------------------------------------------------
		public function add_rules( $id, $rules )
		{
			
			if ( $this->elements[$id] )
				return $this->elements[$id]->add_rules( $rules );
			else if ( $this->groups[$id] )
				return $this->groups[$id]->add_rules( $rules );
			else
				return false;  // Failure.

		}
		
		
		//------------------------------------------------------------------------
		// Name: set_default()
		// Desc: Sets a default value for the element passed in. Only set's the
		//       default value if there is no value stored in the element already.
		//------------------------------------------------------------------------
		public function set_default( $id, $value )
		{
				
			$id = $args[0];
			$value = $args[1];
		
			if ( $this->elements[$id] )
				return $this->elements[$id]->set_default( $value );
			else if ( $this->groups[$id] )
				return $this->groups[$id]->set_default( $value );
			else
				return false;  // Failure.

		}
		
		
		//------------------------------------------------------------------------
		// Name: set_defaults()
		// Desc: Takes in an array where the keys are the elements to set and the
		//       values are the default values.
		//------------------------------------------------------------------------
		public function set_defaults( $data )
		{
			
			// Loop through all the elements.
			foreach ( $data as $id => $value )
			{
				
				if ( $this->elements[$id] )
					$this->elements[$id]->set_default( $value );
				else if ( $this->groups[$id] )
					$this->groups[$id]->set_default( $value );
				
			}

		}
		
		
		//------------------------------------------------------------------------
		// Name: get_value()
		// Desc: Returns the value of an element in the form.
		//------------------------------------------------------------------------
		public function get_value( $id )
		{
			
			if ( $this->elements[$id] )
				return $this->elements[$id]->value;
			else if ( $this->groups[$id] )
				return $this->groups[$id]->value;
			else
				return false;  // Failure.
						
		}
		
		
		//------------------------------------------------------------------------
		// Name: get_all_values()
		// Desc: Retrieves and returns all the values in this form as an array.
		//------------------------------------------------------------------------
		public function get_all_values()
		{
			
			$values = array();
			
			// Loop through each group.
			foreach ( $this->groups as $group )
			{
				if ( $group->value != '' )
					$values[$group->id] = $group->value;
				else
					$values[$group->id] = array();
			}			
			
			// Loop through each element.
			foreach ( $this->elements as $element_id => $element )
			{
				$values[$element->id] = $element->value;
			}
			
			return $values;
						
		}
		
		
		//------------------------------------------------------------------------
		// Name: add_dependants()
		// Desc: Adds dependants to the form.
		//------------------------------------------------------------------------
		public function add_dependants( $element_id, $dependants, $values = array() )
		{

			// Add the dependants.
			return $this->elements[$element_id]->add_dependants( $dependants, $values );

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_error()
		// Desc: Adds an error to be displayed in the form.
		//------------------------------------------------------------------------
		public function add_error( $element_id, $error )
		{

			// Add the error.
			$this->elements[$element_id]->add_error( $error );
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the form. Can take as input an optional form template.
		//------------------------------------------------------------------------
		public function display( $template = '' )
		{
			
			// Any template passed in?
			$html = '';
			$filename = '';
			$using_template = true;
			
			if ( $template == '' )
			{	
				$reflect  = new ReflectionClass( get_class( $this ) );
				$filename = str_replace( '.form.php', '.form.phtml', $reflect->getFilename() );
			}
			else
				$filename = ROOT . LW_Settings::get( 'stage', 'forms_folder' ) . '/' . $template . '.form.phtml';
			
			if ( file_exists( $filename ) )
				$html = file_get_contents( $filename );
			else
				$using_template = false;

			// Loop through every fieldset.
			foreach ( $this->fieldsets as $fieldset_id => $fieldset )
			{

				$fieldset->display( $html, $using_template );

			}  // Next fieldset.
		
			// What enctype?
			if ( $this->multipart_enctype )
				$enctype = 'multipart/form-data';
			else
				$enctype = 'application/x-www-form-urlencoded';

			// Output the HTML.
			echo '<form id="'. $this->id .'" name="'. $this->id .'" method="post" action="'. $this->options['form_action'] .'" enctype="'. $enctype .'"><input type="hidden" name="'. $this->id .'_submit_checker" value="submitted" />'. $html .'</form>';

			// Output the JavaScript to go with this form.
			$this->display_js();

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Validates the form.
		//------------------------------------------------------------------------
		public function validate( $javascript = false )
		{
			
			// Early out.
			if ( $_POST[$this->id . '_submit_checker'] == '' )
				return false;
			
			// Create a validator object to validate the form data.
			$form_validator = new LW_Form_Validator( $this );

			// Validate the form.
			$return_val = $form_validator->validate( $javascript );
			
			// If the function returned false, there were errors, so return false.
			if ( !$return_val ) return false;
			
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: show_js()
		// Desc: Show all the JavaScript that's needed.
		//------------------------------------------------------------------------
		private function display_js()
		{
						
			//$html .= '<script language="javascript" type="text/javascript">';
			
			// Set up the dependants.
			$i = 1;
			$dependants_string = '';
			$dependants_default_string = '';
			
			// Loop through each element.
			foreach ( $this->elements as $element_id => $element )	
			{	
				
				// Continue if no dependants.
				if ( count( $element->dependants ) == 0 )
					continue;
					
				// Add the dependant group to the dependants string.
				if ( $i == 1 )
					$dependants_string .= $element_id .': { dependants: [';
				else
					$dependants_string .= ', '. $element_id .': { dependants: [';
			
				$dependants_string .= $this->format_dependant_js( $element->dependants, $element->dependant_values );

				$dependants_string .= ' }';
				
				$i++;
				
			}  // Next element.
			
			// Create the form class in JS.
			$html .= 'var form_'. $this->id .' = new LW_Form( "'. $this->id .'", { '. $dependants_string .' } );';

			// Create the Form_Validator class in JS.
			$html .= 'var form_validator_'. $this->id .' = new LW_Form_Validator( document.'. $this->id .', { ';

			// Submit button.
			if ( $this->submit_button != null )
				$html .= 'submit_button: document.' . $this->id . '.' . $this->submit_button->id;

			// Redirect URL?
			if ( $this->options['redirect_url'] != '' )
				$html .= ', redirect_url: "'. $this->options['redirect_url'] .'"';

			// Submit the form?
			if ( $this->options['form_action'] != '' || $this->options['redirect_url'] == '' )
				$html .= ', submit_form: true';

			// Finish creating the Form_Validator class.
			$html .= ' } );';

			// Loop through each element/error.
			foreach ( $this->elements as $element )			
				foreach ( $element->errors as $error )
					$html .= 'form_validator_'. $this->id .'.postError( "'. $element->id .'", "'. $error .'" );';
			
			// Output the HTML.			
			$html = str_replace( '\n', '\\n', $html );
			$html = str_replace( '\r', '\\r', $html );
			$html = addcslashes( $html, "'" );
			
echo <<<END
			<script type="text/javascript">

				function {$this->id}Load()
				{
					var elem = document.createElement( "script" );
					elem.type = "text/javascript";
					elem.text = '{$html}';
					document.body.appendChild( elem );
				}
				
				LW_Events_Handler.addEvent( window, "onload", {$this->id}Load );

			</script>
END;

		}
		
		
		//------------------------------------------------------------------------
		// Name: format_dependant_js()
		// Desc: Formats the JavaScript for dependants for a certain element.
		//------------------------------------------------------------------------
		private function format_dependant_js( $dependants, $values )
		{
			
			$dependants_string = '';
			
			// Loop through the dependants.
			$n = 1;
			foreach ( $dependants as $dependant )
			{
				
				// Dependant type? Group or element?
				if ( array_key_exists( $dependant, $this->groups ) )
				{
					
					// Loop through all the elements in the group.
					foreach ( $this->groups[$dependant]->elements as $element )
					{
						
						// Which iteration?
						if ( $n == 1 )
							$dependants_string .= '"'. $element->id .'"';
						else
							$dependants_string .= ', "'. $element->id .'"';
						
						$n++;
						
					}  // Next element.
					
				}  // End if group.
				else
				{
					
					// Which iteration?
					if ( $n == 1 )
						$dependants_string .= '"'. $dependant .'"';
					else
						$dependants_string .= ', "'. $dependant .'"';
					
					$n++;
					
				}  // End if element.
					
			}  // Next dependant.
			
			$dependants_string .= ']';
			
			// Any value?
			if ( count( $values ) != 0 )
			{

				$dependants_string .= ', value: [ ';

				// Loop through each value.
				$n = 1;
				foreach ( $values as $value )
				{

					// Which iteration?
					if ( $n == 1 )
						$dependants_string .= '"'. $value .'"';
					else
						$dependants_string .= ', "'. $value .'"';

					$n++;

				}  // Next value.

				$dependants_string .= ' ]';

			}  // End if value.
			
			return $dependants_string;
			
		}

  }

?>
