<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Form_Element
	// Desc: An abstract element class that contains the common functionality
	//       of an element.
	//--------------------------------------------------------------------------
	abstract class Legato_Form_Element
	{
		
		//------------------------------------------------------------------------
		// Constants
		//------------------------------------------------------------------------
		const TYPE_ELEMENT            = 0;
		const TYPE_TEXT               = 1;
		const TYPE_PASSWORD           = 2;
		const TYPE_FILE               = 3;
		const TYPE_TEXTAREA           = 4;
		const TYPE_RADIO              = 5;
		const TYPE_CHECKBOX           = 6;
		const TYPE_SELECT             = 7;
		const TYPE_SELECT_MULTIPLE    = 8;
		const TYPE_SUBMIT             = 9;
		const TYPE_RESET              = 10;
		const TYPE_BUTTON             = 11;
		const TYPE_HIDDEN             = 12;
		const TYPE_IMAGE              = 13;
		const TYPE_CHECKBOX_MULTIPLE  = 14;
		

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $type              = Legato_Form_Element::TYPE_ELEMENT;  // The type of the element.
		public $form              = NULL;     // The form that holds this element.
		public $id                = '';       // The element's ID.
		public $label             = '';       // The element's label, if it has one.
		public $descriptive_name  = '';       // The descriptive name of the element. Used in validation.
		public $rules             = array();  // The array of rules if the element has any.
		public $filters           = array();  // The array of filters if the elemtn has any.
		public $errors            = array();  // The array of errors for this element. Populated when validate() is called.
		public $validations       = array();  // The array of completed validations.
		
		
		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		protected $_value      = '';      // The element's value, if it has one.
		protected $_raw_value  = '';      // The element's raw, unfiltered value, if it has one.
		protected $_filtered   = false;   // Whether or not the value has been filtered.
		

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
			
			// Add the default rules.
			$this->rules['required'] = true;
			
			// Add the default filters.
			$this->filters['html'] = true;
			
			// If no label passed in, try to create one.
			if ( $this->label === '' )
				$this->label = ucwords( str_replace( array( '_', '-' ), ' ', $this->id ) ) . ':';
			
			// If no descriptive name passed in, try to create one.
			if ( !$this->descriptive_name )
				$this->descriptive_name = strtolower( trim( $this->label, '[]{}()<>:-?.!|\'\"' ) );

		}
		
		
		//------------------------------------------------------------------------
		// Name: __set()
		// Desc: Used for the value, so we can perform some filtering on it before
		// storing it.
		//------------------------------------------------------------------------
		public function __set( $name, $value )
		{
			
			// Store the filtered value.
			if ( $name == 'value' )
			{
				$this->_value = $value;
				$this->_raw_value = $value;
				$this->_filtered = false;
			}

		}
		
		
		//------------------------------------------------------------------------
		// Name: __get()
		// Desc: Used to return the value.
		//------------------------------------------------------------------------
		public function __get( $name )
		{
			
			// Return the value.
			if ( $name == 'value' )
			{	
				return $this->_filter_value( $this->_value );
			}
			else if ( $name == 'raw_value' )
			{
				return $this->_raw_value;
			}

		}
		
		
		//------------------------------------------------------------------------
		// Name: output()
		// Desc: Displays the element.
		//------------------------------------------------------------------------
		public function output( &$html, $template = false )
		{

			

		}
		
		
		//------------------------------------------------------------------------
		// Name: default_value()
		// Desc: Sets the default value for this element.
		//------------------------------------------------------------------------
		public function default_value( $value )
		{

			// Set the default value.
			$this->value = ($this->value != '') ? $this->value : $value;
			
			return $this;

		}
		
		
		//------------------------------------------------------------------------
		// Name: rule()
		// Desc: Sets a rule or multiple rules for this element.
		//------------------------------------------------------------------------
		public function rule()
		{
			
			$args = func_get_args();
			
			if ( count( $args ) == 1 )
				$this->rules = array_merge( $this->rules, $args[0] );
			else
				$this->rules[$args[0]] = $args[1];

			return $this;

		}
		
		
		//------------------------------------------------------------------------
		// Name: filter()
		// Desc: Sets a filter or multiple filters for this element.
		//------------------------------------------------------------------------
		public function filter()
		{
			
			$args = func_get_args();
			
			if ( count( $args ) == 1 )
				$this->filters = array_merge( $this->filters, $args[0] );
			else
				$this->filters[$args[0]] = $args[1];

			return $this;

		}
		
		
		//------------------------------------------------------------------------
		// Name: error()
		// Desc: Adds an error to be displayed in the form.
		//------------------------------------------------------------------------
		public function error( $error )
		{

			// Add the error.
			$this->errors[] = $error;
			
			return $this;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: validate()
		// Desc: Validates the element.
		//------------------------------------------------------------------------
		public function validate()
		{
			
			// Required?.
			if ( $this->rules['required'] && $this->value == '' )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field is empty. <br /> You must fill it in.';
				return false;
			}
			
			// Not required.
			if ( !$this->rules['required'] && $this->value == '' )
			{
				// Catch the case where it's not required and nothing was put in.
				return true;				
			}
			
			// Min length.
			if ( $this->rules['minlength'] && !Legato_Validation::minlength( $this->value, $this->rules['minlength'] ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field is too short. <br /> It must be at least ' . $this->rules['minlength'] . ' characters long.';
				return false;
			}
			
			// Max length.
			if ( $this->rules['maxlength'] && !Legato_Validation::maxlength( $this->value, $this->rules['maxlength'] ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field is too long. <br /> It can not be longer than ' . $this->rules['maxlength'] . ' characters.';
				return false;
			} 
			
			// Range length.
			if ( $this->rules['rangelength'] && !Legato_Validation::rangelength( $this->value, $this->rules['rangelength'][0], $this->rules['rangelength'][1] ) )
			{
				if ( !Legato_Validation::minlength( $this->value, $this->rules['rangelength'][0] ) )
				{
					$this->errors[] = 'The ' . $this->descriptive_name . ' field is too short. <br /> It must be between ' . $this->rules['rangelength'][0] . ' and ' .  $this->rules['rangelength'][1] . ' characters long.';
					return false;
				}
				else if ( !Legato_Validation::maxlength( $this->value, $this->rules['rangelength'][1] ) )
				{
					$this->errors[] = 'The ' . $this->descriptive_name . ' field is too long. <br /> It must be between ' . $this->rules['rangelength'][0] . ' and ' .  $this->rules['rangelength'][1] . ' characters long.';
					return false;
				}
			}
			
			// Length.
			if ( $this->rules['length'] && !Legato_Validation::length( $this->value, $this->rules['length'] ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field is not the correct length. <br /> It must be  ' . $this->rules['length'] . ' characters long.';
				return false;
			}
			
			// Alpha.
			if ( $this->rules['alpha'] && !Legato_Validation::alpha( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field can only contain alphabetic characters. <br /> It can not contain numbers or punctuation characters.';
				return false;
			}
			
			// Numeric.
			if ( $this->rules['numeric'] && !Legato_Validation::numeric( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field must be a numerical value.';
				return false;
				
			}
			
			// Alpha numeric.
			if ( $this->rules['alphanumeric'] && !Legato_Validation::alpha_numeric( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field can only contain letters and numbers. <br /> No special characters.';
				return false;
				
			}
			
			// Non-zero.
			if ( $this->rules['nonzero'] && !Legato_Validation::nonzero( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field must be a nonzero numerical value.';
				return false;
				
			}
			
			// Email.
			if ( $this->rules['email'] && !Legato_Validation::email_address( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' you entered was not a correct email address.';
				return false;				
			}
			
			// Credit card.
			if ( $this->rules['credit_card'] && !Legato_Validation::credit_card( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' you entered is not a valid credit card number.';
				return false;				
			}
			
			// Phone number.
			if ( $this->rules['phone_number'] && !Legato_Validation::phone_number( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' you entered is not a valid phone number';
				return false;				
			}
			
			// URL.
			if ( $this->rules['url'] && !Legato_Validation::url( $this->value ) )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' you entered is not a valid URL';
				return false;				
			}
			
			// Regular expression.
			if ( $this->rules['regex'] && preg_match( $this->rules['regex'], $this->value ) == 0 )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' field is not in the correct format.';
				return false;				
			} 
			
			// Compare.
			if ( $this->rules['compare'] && $this->value != $this->form->{$this->rules['compare']}->value )
			{
				$this->errors[] = 'The ' . $this->descriptive_name . ' and ' . $this->form->{$this->rules['compare']}->descriptive_name . ' fields are not identical.';
				return false;
			}
			
			// No problems!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Protected Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: _filter_value()
		// Desc: Returns the filtered value.
		//------------------------------------------------------------------------
		protected function _filter_value( &$value )
		{
			
			// Are we already filtered?
			if ( $this->_filtered )
				return $value;
			
			if ( is_array( $value ) )
			{
				
				// If this is an array, recursively go through the values and filter.
				foreach ( $value as $index => $child_value )
				{
					
					// We set ourselves unfiltered each iteration so that it
					// doesn't skip the filtering the next time it loops.
					$this->_filtered = false;
					$this->_filter_value( $child_value );
					$value[$index] = $child_value;
					
				}
				
			}  // End if an array was passed in.
			else
			{
	
				if ( $this->filters['html'] === true )
				{
					
					// Run htmlentities on it.
					$value = htmlentities( $value );
					
				}
				else if ( is_array( $this->filters['html'] ) )
				{
									
					// Get the filtering method.
					$method = $this->filters['html'][0] ? $this->filters['html'][0] : 'popoon';
					
					// Clean the value.
					$value = Legato_Filter::clean
					( 
						$value, 
						$method,
						$this->filters['html'][1]  // The config array, if there was any set.
					);
					
				}
			
			}  // End if not an array.
			
			// Set as filtered.
			$this->_filtered = true;
			
			// Return the filtered value.
			return $value;

		}

  }