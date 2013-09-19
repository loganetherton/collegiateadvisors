<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form
	// Desc: Allows you to easily create powerful forms.
	//--------------------------------------------------------------------------
	class Legato_Form
	{


		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $elements       = array();  // The elements in the form.
		public $groups         = array();  // The groups in the form.
		public $submit_button  = null;     // The submit button.


		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $_id               = '';       // The form's ID.
		private $_options          = array();  // The form object's options.
		private $_fieldsets        = array();  // The fieldsets.
		private $_active_fieldset  = null;     // The active fieldset.


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
			$this->_id = $id;

			// Store the optional options.
			$this->_options['action']         = $options_array['action'];
			$this->_options['redirect_url']   = $options_array['redirect_url'];
			$this->_options['js_validate']    = $options_array['js_validate'] ? true : false;

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
			else if ( $this->_fieldsets[$id] )
				return $this->_fieldsets[$id];
				
			// If we couldn't find anything, just return false.
			return false;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: add()
		// Desc: Adds an element/group/fieldset to the form.
		//------------------------------------------------------------------------
		public function add( $object )
		{
			
			// Which type?
			if ( $object instanceof Legato_Form_Fieldset )
			{
				
				$fieldset = $object;
				
				// Check if we have a fieldset with this ID already stored.
				if ( array_key_exists( $fieldset->id, $this->_fieldsets ) === true )
				{
	
					// Add debugging item.
					Legato_Debug_Debugger::add_item( 'The fieldset ' . $fieldset->id . ' has already been added to the system. <br /> Can\'t add a fieldset with the same ID twice.' );
					return false;
	
				}  // End if fieldset already stored.
	
				// Store the fieldset.
				$this->_fieldsets[$fieldset->id] = $fieldset;
				
				// Set the fieldset's data.
				$fieldset->form = &$this;
	
				// Set as the active fieldset.
				$this->_active_fieldset = &$this->_fieldsets[$fieldset->id];
	
				// Success!
				return true;
				
			}  // End if fieldset.
			else if ( $object instanceof Legato_Form_Group )
			{
				
				$group = $object;
				
				// Set the form.
				$group->form = $this;
	
				// Store the group.
				$this->_active_fieldset->elements[] = $group;
				$this->groups[$group->id] = $group;
	
				// Return the newly created group.
				return $this->groups[$group->id];
				
			}  // End if group.
			else if ( $object instanceof Legato_Form_Element )
			{
				
				$element = $object;
				
				// If the element is false, return false.
				if ( !$element )
				{
	
					Legato_Debug_Debugger::add_item( 'Could not add element ' . $element->id . ' to form.' );
					return false;
	
				}  // End if failed.
	
				// Store the element.
				$this->_active_fieldset->elements[] = $element;
				$this->elements[$element->id] = $element;
				
				// Set the data for the element.
				$element->form = &$this;
				
				// Special element conditional.
				if ( $element instanceof Legato_Form_Element_Submit || $element instanceof Legato_Form_Element_Image )
					$this->submit_button = $element;
				else if ( $element instanceof Legato_Form_Element_File )
					$this->multipart_enctype = true;
	
				// Return the newly created element.
				return $this->elements[$element->id];
				
			}  // End if element.
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: default_values()
		// Desc: Takes in an array where the keys are the elements to set and the
		//       values are the default values.
		//------------------------------------------------------------------------
		public function default_values( $data )
		{
			
			// Loop through all the elements.
			foreach ( $data as $id => $value )
			{
				
				if ( $this->elements[$id] )
					$this->elements[$id]->default_value( $value );
				else if ( $this->groups[$id] )
					$this->groups[$id]->default_value( $value );
				
			}

		}
		
		
		//------------------------------------------------------------------------
		// Name: values()
		// Desc: Retrieves and returns all the values in this form as an array.
		//------------------------------------------------------------------------
		public function values()
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
		// Name: output()
		// Desc: Returns the output of the form. Can take as input an optional
		//       form template.
		//------------------------------------------------------------------------
		public function output( $template = '' )
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
				$filename = ROOT . Legato_Settings::get( 'stage', 'forms_folder' ) . '/' . $template . '.form.phtml';
			
			if ( file_exists( $filename ) )
			{
				// Include the template, ensuring that PHP is interpreted.
				ob_start();
				include( $filename );
				$html = ob_get_clean();
			}
			else
				$using_template = false;

			// Loop through every fieldset and display them.
			foreach ( $this->_fieldsets as $fieldset_id => $fieldset )
				$fieldset->output( $html, $using_template );

			// What enctype?
			if ( $this->multipart_enctype )
				$enctype = 'multipart/form-data';
			else
				$enctype = 'application/x-www-form-urlencoded';

			// Output the HTML.
			$html = '<form id="' . $this->_id . '" name="' . $this->_id . '" method="post" action="' . $this->_options['action'] . '" enctype="' . $enctype . '"><input type="hidden" name="' . $this->_id . '_submit_checker" value="submitted" />' . $html . '</form>';

			// Output the JavaScript to go with this form.
			$html .= $this->_output_js();

			// Return the output.
			return $html;

		}
		
		
		/*
			Function: validate()
			Attempts to validate the form.
		
			Syntax:
				bool validate()
				
			Returns:
				True if the form validated successfully. False if the form did not pass validation.
								
			Examples:
			(begin code)
			
				if ( $form->validate() )
				{
					// Form validated successfully.
				}
				else
				{
					// Form did not validate.
				}
				
			(end)
			
			See Also:
				<Legato_Form_Element::rule()>
		*/
		public function validate()
		{
			
			// Early out.
			if ( Legato_Input::post( $this->_id . '_submit_checker' ) == '' )
				return null;
			
			// Create a validator object to validate the form data.
			$form_validator = new Legato_Form_Validator( $this );

			// Validate the form.
			$return_val = $form_validator->validate( $javascript );
			
			// Return success or failure.
			return $return_val;

		}
		
		
		/*
			Function: output_errors()
			Returns any errors as either JSON or XML. Used for returning errors in an XHR/AJAX call.
		
			Syntax:
				string output_errors( [ string $type = 'json' ] )
		
			Parameters:
				string $type - *optional* - The type of output data: either 'json' or 'xml'. Defaults to 'json'.
				
			Returns:
				A string of errors depending upon the type you passed in.
				If there are no errors, it will return an empty string.
								
			Examples:
			(begin code)
			
				if ( $form->validate() )
				{
					// Form validated successfully.
				}
				
				echo $form->output_errors( 'xml' );
				
			(end)
			
			See Also:
				<Legato_Form::validate()>
		*/
		public function output_errors( $type = 'json' )
		{
			
			$errors = '';
			
			// Make sure the header and footer is turned
			// of, as well as the debugger output.
			Legato_Settings::set( 'stage', 'show_layout', false );
			Legato_Settings::set( 'debugger', 'enable_reporting', false );
			
			// Should we output the errors in any particular format?
			if ( $type == 'json' )
			{
				
				$errors = array();
				
				// Loop through each element/error.
				foreach ( $this->elements as $element )			
					foreach ( $element->errors as $error )
						$errors[$element->id] = $error;
						
				// Output the JSON data.
				$errors = json_encode( $errors );
				
			}
			else if ( $type == 'xml' )
			{
				
				// Loop through each element/error.
				foreach ( $this->elements as $element )	
					foreach ( $element->errors as $error )
						$errors .= '<error><id>' . $element->id . '</id><message>' . $error . '</message></error>';
						
				$errors = '<?xml version="1.0" encoding="UTF-8"?><errors>' . $errors . '</errors>';
				
			}
			
			return $errors;

		}
		
		
		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: _output_js()
		// Desc: Return all the JavaScript that's needed.
		//------------------------------------------------------------------------
		private function _output_js()
		{
			
			// Create the form class in JS.
			$html .= 'var form_' . $this->_id . ' = new Legato_Form( "'. $this->_id .'", {';
			
			// Submit button.
			if ( $this->submit_button != null )
				$html .= 'submit_button: document.getElementById( "' . $this->submit_button->id . '" )';

			// Redirect URL?
			if ( $this->_options['redirect_url'] != '' )
				$html .= ', redirect_url: "'. $this->_options['redirect_url'] .'"';

			// Submit the form?
			if ( $this->_options['action'] != '' || $this->_options['redirect_url'] == '' )
				$html .= ', submit_form: true';
				
			// Validate with JS?
			if ( $this->_options['js_validate'] && $this->_options['action'] )
				$html .= ', request_url: "' . $this->_options['action'] . '"';

			// Finish creating the Form class.
			$html .= '} );';

			// Add the errors.
			foreach ( $this->elements as $element )			
				foreach ( $element->errors as $error )
					$html .= 'form_'. $this->_id .'.postError( "'. $element->id .'", "'. $error .'" );';
			
			// Output the HTML.
			$html = str_replace( '\n', '\\n', $html );
			$html = str_replace( '\r', '\\r', $html );
			$html = addcslashes( $html, "'" );
			
$html = <<<END
			<script type="text/javascript">

				function {$this->_id}Load()
				{
					var elem = document.createElement( "script" );
					elem.type = "text/javascript";
					elem.text = '{$html}';
					document.getElementById( '{$this->_id}' ).appendChild( elem );
				}
				
				Legato_Events_Handler.DOMReady( {$this->_id}Load );

			</script>
END;

			// Return the output.
			return $html;

		}

  }