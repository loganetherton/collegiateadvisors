<?php

	class UserForm extends Legato_Form
	{
		
		public function __construct( $act, $user = NULL, $default_advisor_id = NULL )
		{
			
			$url = ($user != NULL) ? $act . '/' . $user->get( 'id' ) : $act;

			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_user', array( 'form_action' => SITE_URL . '/admin/users/' . $url ) );

			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'user_information', 'User Information' ) );

            if ( $user != NULL )
				$default_advisor_id = $user->get('advisor_id');

			$advisors = Legato_Resource::fetch( 'Advisor' );

			$advisor_options[0] = 'Select an Advisor';

			foreach( $advisors as $id => $advisor )
				$advisor_options[$id] = $advisor->get( 'business_name' );

			$this->add( new Legato_Form_Element_Select( 'advisor_id', 'Advisor:', $advisor_options ) );

			$this->add( new Legato_Form_Element_Text( 'username', 'Username:' ) );
			$this->add( new Legato_Form_Element_Text( 'password', 'Password:' ) )
			     ->rule( 'rangelength', array( 5, 15 ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Text( 'mycareer_username', 'My Career Username:' ) );
			$this->add( new Legato_Form_Element_Text( 'mycareer_password', 'My Career Password:' ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Text( 'testgear_username', 'Test Gear Username:' ) );
			$this->add( new Legato_Form_Element_Text( 'testgear_password', 'Test Gear Password:' ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Text( 'first_name', 'First Name:' ) );
			$this->add( new Legato_Form_Element_Text( 'last_name', 'Last Name:' ) );
			
			$this->add( new Legato_Form_Element_Text( 'phone_number', 'Phone Number:' ) );
			$this->add( new Legato_Form_Element_Text( 'email_address', 'Email Address:' ) );
			
			$this->add( new Legato_Form_Element_Text( 'address', 'Address:' ) );
			$this->add( new Legato_Form_Element_Text( 'city', 'City:' ) );
			
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
			
			$this->add( new Legato_Form_Element_Text( 'zip', 'Zip:' ) );
			
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
			
			$this->add( new Legato_Form_Element_Text( 'extra', 'Extra Information:' ) );
			
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Submit' ) );

			// Add the rules.
			$this->username->rule( 'rangelength', array( 4, 20 ) );
			$this->mycareer_username->rule( array( 'required' => false, 'rangelength' => array( 4, 20 ) ) );
			$this->testgear_username->rule( array( 'required' => false, 'rangelength' => array( 4, 20 ) ) );
			
			$this->password->rule( array( 'required' => false, 'rangelength' => array( 4, 15 ) ) );
			$this->mycareer_password->rule( array( 'required' => false, 'rangelength' => array( 3, 15 ) ) );
			$this->testgear_password->rule( array( 'required' => false, 'rangelength' => array( 3, 15 ) ) );
			
			$this->phone_number->rule( 'required', false );
			$this->email_address->rule( 'required', false );
			$this->address->rule( 'required', false );
			$this->city->rule( 'required', false );
			$this->zip->rule( 'required', false );
			
			$this->extra->rule( 'required', false );

			if ( $act == 'add' )
				$this->password->rule( 'required', true );
			
			$this->advisor_id->default_value( $default_advisor_id );

			// Set the defaults.
			if ( $user != NULL )
			{
				
				$this->username->default_value( $user->get( 'username' ) );
				$this->mycareer_username->default_value( $user->get( 'mycareer_username' ) );
				$this->testgear_username->default_value( $user->get( 'testgear_username' ) );
				$this->first_name->default_value( $user->get( 'first_name' ) );
				$this->last_name->default_value( $user->get( 'last_name' ) );	
				$this->phone_number->default_value( $user->get( 'phone_number' ) );	
				$this->email_address->default_value( $user->get( 'email_address' ) );	
				$this->address->default_value( $user->get( 'address' ) );	
				$this->city->default_value( $user->get( 'city' ) );
				$this->state->default_value( $user->get( 'state' ) );
				$this->zip->default_value( $user->get( 'zip' ) );
				$this->birth_date_month->default_value( date( 'm', (int)$user->get( 'birth_date' ) ) );
				$this->birth_date_day->default_value( date( 'j', (int)$user->get( 'birth_date' ) ) );
				$this->birth_date_year->default_value( date( 'Y', (int)$user->get( 'birth_date' ) ) );
				$this->graduation_year->default_value( $user->get( 'graduation_year' ) );
				$this->extra->default_value( $user->get( 'extra' ) );
				
				// Create the encryption object.
				$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $user->get( 'username' ) ), 'twofish' );
				
				// Set the career dimensions and bridges password.
				$this->mycareer_password->default_value( $encryption->decrypt( $user->get( 'mycareer_password' ) ) );
				$this->testgear_password->default_value( $encryption->decrypt( $user->get( 'testgear_password' ) ) );
					
			}
			
		}


		public function validate( $act )
		{
			$error = parent::validate();
			
			if ( !$error )
				return false;

			if ( $this->advisor_id->value == 0 )
			{
				$this->advisor_id->error( 'You must select an advisor for this user.' );
				$error = false;
			}
			
			if ( $act == 'add' && !User::username_available( $this->username->value ) )
			{
				$this->username->error( 'A user with this name already exists. Please choose another one.' );
				$error = false;
			}

			return $error;
		}

	}

?>