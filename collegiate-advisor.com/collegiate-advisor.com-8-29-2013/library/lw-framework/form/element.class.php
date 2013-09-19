<?php

	//--------------------------------------------------------------------------
	// Name: LW_Form_Element
	// Desc: An abstract element class that contains the common functionality
	//       of an element.
	//--------------------------------------------------------------------------
	abstract class LW_Form_Element
	{
		
		//------------------------------------------------------------------------
		// Constants
		//------------------------------------------------------------------------
		const TYPE_ELEMENT          = 0;
		const TYPE_TEXT             = 1;
		const TYPE_PASSWORD         = 2;
		const TYPE_FILE             = 3;
		const TYPE_TEXTAREA         = 4;
		const TYPE_RADIO            = 5;
		const TYPE_CHECKBOX         = 6;
		const TYPE_SELECT           = 7;
		const TYPE_SELECT_MULTIPLE  = 8;
		const TYPE_SUBMIT           = 9;
		const TYPE_RESET            = 10;
		const TYPE_BUTTON           = 11;
		const TYPE_HIDDEN           = 12;
		

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type              = LW_Form_Element::TYPE_ELEMENT;  // The type of the element.
		public $form              = NULL;     // The form that holds this element.
		public $id                = '';       // The element's ID.
		public $label             = '';       // The element's label, if it has one.
		public $descriptive_name  = '';       // The descriptive name of the element. Used in validation.
		public $value             = '';       // The element's value, if it has one.
		public $rules             = array();  // The array of rules if the element has any.
		public $errors            = array();  // The array of errors for this element. Populated when validate() is called.
		public $dependants        = array();  // The array of element's that are dependant on this element.
		public $dependant_values  = array();  // The array of dependant values.
		public $validations       = array();  // The array of completed validations.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $id, $label = '', $descriptive_name = '' )
		{
			
			// Store the data.
			$this->id = $id;
			$this->label = $label;
			$this->descriptive_name = $descriptive_name;
			
			// Reset the validations array.
			$this->reset_validations();
			
			// Add the required rule.
			$this->rules['required'] = true;
			
			// If no label passed in, try to create one.
			if ( $this->label == '' )
				$this->label = ucwords( str_replace( '_', ' ', $this->id ) ) . ':';
			
			// If no descriptive name passed in, try to create one.
			if ( !$this->descriptive_name )
				$this->descriptive_name = strtolower( trim( $this->label, '[]{}()<>:-?.!|\'\"' ) );
				
			// Filter out the $ signs in the label and descriptive name.
			$this->label = str_replace( '$', '\$', $this->label );
			$this->descriptive_name = str_replace( '$', '\$', $this->descriptive_name );

		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function display( &$html, $template = false )
		{

			

		}
		
		
		//------------------------------------------------------------------------
		// Name: set_default()
		// Desc: Sets the default value for this element.
		//------------------------------------------------------------------------
		public function set_default( $value )
		{

			// Set the default value.
			$this->value = ($this->value != '') ? $this->value : $value;
			
			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_rule()
		// Desc: Adds a rule to this element.
		//------------------------------------------------------------------------
		public function add_rule( $rule, $value )
		{
			
			// Add the rule.
			$this->rules[$rule] = $value;

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_rules()
		// Desc: Adds rules to this element.
		//------------------------------------------------------------------------
		public function add_rules( $rules )
		{
			
			// Add the rule.
			$this->rules = $rules;

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_dependants()
		// Desc: Adds dependants to this element.
		//------------------------------------------------------------------------
		public function add_dependants( $dependants, $values = array() )
		{

			// Add the dependants.
			$this->dependants        = $dependants;
			$this->dependant_values  = $values;

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Name: add_error()
		// Desc: Adds an error to be displayed in the form.
		//------------------------------------------------------------------------
		public function add_error( $error )
		{

			// Add the error.
			$this->errors[] = $error;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Validates the element.
		//------------------------------------------------------------------------
		public function validate()
		{

			// Validate.
			if ( $this->validations['required'] != true && $this->rules['required'] != '' && $this->value == '' )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field is empty. <br /> You must fill it in.';

			}  // Required.
			else if ( $this->validations['required'] != true && $this->rules['required'] == '' && $this->value == '' )
			{
				
				// Catch the case where it's not required and nothing was put in.
				/* Do Nothing */
				
			}  // Not required.
			else if ( $this->validations['minlength'] != true && $this->rules['minlength'] != '' && strlen( $this->value ) < $this->rules['minlength'] )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field is too short. <br /> It must be at least ' . $this->rules['minlength'] . ' characters long.';

			}  // Min length.
			else if ( $this->validations['maxlength'] != true && $this->rules['maxlength'] != '' && strlen( $this->value ) > $this->rules['maxlength'] )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field is too long. <br /> It can not be longer than ' . $this->rules['maxlength'] . ' characters.';
				
			}  // Max length.
			else if ( $this->validations['rangelength'] != true && $this->rules['rangelength'] != '' && (strlen( $this->value ) < $this->rules['rangelength'][0] || strlen( $this->value ) > $this->rules['rangelength'][1]) )
			{

				if ( strlen( $this->value ) < $this->rules['rangelength'][0] )
				{

					$this->errors[] = 'The ' . $this->descriptive_name . ' field is too short. <br /> It must be between ' . $this->rules['rangelength'][0] . ' and ' .  $this->rules['rangelength'][1] . ' characters long.';
					
				}
				else if ( strlen( $this->value ) > $this->rules['rangelength'][1] )
				{

					$this->errors[] = 'The ' . $this->descriptive_name . ' field is too long. <br /> It must be between ' . $this->rules['rangelength'][0] . ' and ' .  $this->rules['rangelength'][1] . ' characters long.';
					
				}

			}  // Range length.
			else if ( $this->validations['length'] != true && $this->rules['length'] != '' && strlen( $this->value ) != $this->rules['length'] )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field is too not the correct length. <br /> It must be  ' . $this->rules['length'] . ' characters long.';
				
			}  // Length.
			else if ( $this->validations['alpha'] != true && $this->rules['alpha'] != '' && preg_match( '/^[a-zA-Z\s\t\n\r]+$/', $this->value ) == 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field can only contain alphabetic characters. <br /> It can not contain numbers or punctuation characters.';
				
			}  // Alpha.
			else if ( $this->validations['numeric'] != true && $this->rules['numeric'] != '' && preg_match( '/^[\d]+$/', $this->value ) == 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field must be a numerical value.';
				
			}  // Numeric.
			else if ( $this->validations['alphanumeric'] != true && $this->rules['alphanumeric'] != '' && preg_match( '/^[a-zA-Z0-9\s\t\n\r]+$/', $this->value ) == 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field can only contain letters and numbers. <br /> No special characters.';
				
			}  // Alpha-numeric.
			else if ( $this->validations['nonzero'] != true && $this->rules['nonzero'] != '' && preg_match( '/^[0\D]*$/', $this->value ) != 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field must be a nonzero numerical value.';
				
			}  // Nonzero.
			else if ( $this->validations['email'] != true && $this->rules['email'] != '' && preg_match( '/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i', $this->value ) == 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' you entered was not a correct email address.';
				
			}  // Email.
			else if ( $this->validations['regex'] != true && $this->rules['regex'] != '' && preg_match( $this->rules['regex'], $this->value ) == 0 )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' field is not in the correct format.';
				
			}  // Regex.
			else if ( $this->validations['compare'] != true && $this->rules['compare'] != '' && $this->value != $this->form->get_value( $this->rules['compare'] ) )
			{

				$this->errors[] = 'The ' . $this->descriptive_name . ' and ' . $this->form->elements[$this->rules['compare']]->descriptive_name . ' fields are not identical.';
				
			}  // Compare.
			
			// Reset the validations array.
			$this->reset_validations();

			return (count( $this->errors ) != 0);

		}
		
		
		//------------------------------------------------------------------------
		// Protected Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: reset_validation()
		// Desc: Resets the validation array.
		//------------------------------------------------------------------------
		protected function reset_validations()
		{
			
			// Reset the validations array.
			$this->validations = array( 'required' => false,
			                            'minlength' => false,
										'maxlength' => false,
										'rangelength' => false,
										'length' => false,
										'alpha' => false,
										'numeric' => false,
										'alphanumeric' => false,
										'nonzero' => false,
										'email' => false,
										'regex' => false,
										'compare' => false );
			
		}

  }

?>
