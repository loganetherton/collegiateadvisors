<?php

	class UserInformationForm extends Legato_Form
	{
		
		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'update_information' );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'login_information' ) );
			
			$this->add( new Legato_Form_Element_Password( 'password' ) )
			     ->rule( array( 'required' => false, 'rangelength' => array( 4, 15 ) ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Password( 'retype_password' ) )
			     ->rule( array( 'required' => false, 'compare' => 'password' ) )
			     ->filter( 'html', false );
			

			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'basic_information' ) );
			     
			$this->add( new Legato_Form_Element_Text( 'first_name' ) );
			
			$this->add( new Legato_Form_Element_Text( 'last_name' ) );
			
			$this->add( new Legato_Form_Element_Text( 'phone_number' ) )
			     ->rule( 'required', false );
			     
			$this->add( new Legato_Form_Element_Text( 'email_address' ) )
			     ->rule( 'required', false );
			
			$this->add( new Legato_Form_Element_Text( 'address' ) )
			     ->rule( 'required', false );
			     
			$this->add( new Legato_Form_Element_Text( 'city' ) )
			     ->rule( 'required', false );
			     
			$states = array
			( 
				'AL', 'AK', 'AZ', 'AR',
				'CA', 'CO', 'CT', 'DE',
				'FL', 'GA', 'HI', 'ID',
				'IL', 'IN', 'IA', 'KS',
				'KY', 'LA', 'ME', 'MD',
				'MA', 'MI', 'MN', 'MS',
				'MO', 'MT', 'NE', 'NV',
				'NH', 'NJ', 'NM', 'NY',
				'NC', 'ND', 'OH', 'OK',
				'OR', 'PA', 'RI', 'SC',
				'SD', 'TN', 'TX', 'UT',
				'VT', 'VA', 'WA', 'WV',
				'WI', 'WY'
			);
			$states = array_combine( $states, $states );
			
			$this->add( new Legato_Form_Element_Select( 'state', '', $states ) );
			
			$this->add( new Legato_Form_Element_Text( 'zip' ) )
			     ->rule( 'required', false );
			     
			$months = array();
			for ( $i = 1; $i <= 12; $i++ )
				$months[sprintf( "%02d", $i )] = date( 'M - m', (mktime( 0, 0, 0, $i ) - 1) );
				
			$days = array();
			for ( $i = 1; $i <= 31; $i++ )
				$days[$i] = $i;
				
			$year = date( 'Y' ) - 30;
			$years = array();
			for ( $i = 0; $i < 21; $i++ )
				$years[($year + $i)] = $year + $i;
				
			$this->add( new Legato_Form_Group( 'birth_date' ) );
			
			$this->birth_date->add( new Legato_Form_Element_Select( 'birth_date_month', false, $months ) );
			$this->birth_date->add( new Legato_Form_Element_Select( 'birth_date_day', false, $days ) );
			$this->birth_date->add( new Legato_Form_Element_Select( 'birth_date_year', false, $years ) );
			
			$year = date( 'Y' );
			$years = array( 'Please Choose', 'Already Graduated' );
			for ( $i = 0; $i < 7; $i++ )
				$years[($year + $i)] = $year + $i;
				
			$this->add( new Legato_Form_Element_Select( 'graduation_year', 'Highschool Graduation Year:', $years ) );
			
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Submit' ) );
			
			$data = $GLOBALS['user']->get();
			unset( $data['password'] );
			$data['birth_date_month'] = date( 'm', (int)$data['birth_date'] );
			$data['birth_date_day'] = date( 'j', (int)$data['birth_date'] );
			$data['birth_date_year'] = date( 'Y', (int)$data['birth_date'] );
			$this->default_values( $data );
			
		}


		public function validate()
		{
			
			if ( !parent::validate() )
				return false;
			
			if ( $act == 'add' && !User::username_available( $this->username->value ) )
			{
				$this->username->error( 'A user with this name already exists. Please choose another one.' );
				return false;
			}

			return true;
		}

	}

?>