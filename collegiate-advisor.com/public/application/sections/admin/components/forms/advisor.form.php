<?php

	class AdvisorForm extends Legato_Form
	{

		public function __construct( $act, $advisor = NULL, $advisor_admin = NULL )
		{
			// Set up work...
			$level = $GLOBALS['admin']->get('level');
			$url = ($advisor != NULL) ? $act . '/' . $advisor->get( 'id' ) : $act;

			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_advisor', array( 'form_action' => SITE_URL . '/admin/advisors/' . $url ) );

			//------------------------------------
			// Login Information
			//------------------------------------
			if ( $level > 1 )
			{

				$this->add( new Legato_Form_Fieldset( 'advisors_login_information', 'Advisor\'s Login Information' ) );

				// Add the elements.
				$this->add( new Legato_Form_Element_Text( 'username' ) )
				     ->rule( 'rangelength', array( 4, 15 ) )
				     ->filter( 'html', false );

				$this->add( new Legato_Form_Element_Text( 'password' ) )
				     ->rule( 'rangelength', array( 5, 15 ) )
				     ->filter( 'html', false );

				// Add the rules.
				if ( $act == 'edit' )
					$this->password->rule( 'required', false );

				// Set the defaults.
				if ( $advisor != NULL )
					$this->username->default_value( $advisor_admin->get( 'username' ) );

			}

			//------------------------------------
			// Basic Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'advisors_basic_information', 'Advisor\'s Basic Information' ) );

			// Add the elements.
			$this->add( new Legato_Form_Element_Text( 'first_name' ) );
			$this->add( new Legato_Form_Element_Text( 'last_name' ) );

			$this->add( new Legato_Form_Element_Text( 'certifications' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'email_address', 'Private E-mail Address:', 'e-mail address' ) );

			if ( $level > 2 )
			{

				$index_plugin_options = array( 'None', 'Index 1', 'Index 2' );
				$this->add( new Legato_Form_Element_Select( 'index_plugin', 'Index Page:', $index_plugin_options ) );

				$this->add( new Legato_Form_Group( 'plugins', 'Active Pages:', '<br />' ) );

				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'about' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'contact' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'workshops' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'retirement', 'Retirement Package' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'credit', 'Credit Package' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'eighthundred_number', '800 Number' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'user_signup', 'User Sign Up' ) );
				$this->plugins->add( new Legato_Form_Element_CheckboxMultiple( 'take_a_tour', 'Take a Tour' ) );

				$this->add( new Legato_Form_Element_Text( 'namespace', 'Namespace:' ) );
				$this->add( new Legato_Form_Element_Text( 'style_id', 'Style ID:' ) )
				     ->rule( 'required', false );

				$this->add( new Legato_Form_Group( 'start_date', 'Start Date:', ' ' ) );

				$start_date_month_options = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
				$this->start_date->add( new Legato_Form_Element_Select( 'start_date_month', ' ', $start_date_month_options ) );

				$start_date_day_options = array( '1', '15' );
				$this->start_date->add( new Legato_Form_Element_Select( 'start_date_day', ' ', array_combine( $start_date_day_options, $start_date_day_options ) ) );

				$start_date_year_options = array( date( 'Y' ) - 2, date( 'Y' ) - 1, date( 'Y' ), date( 'Y' ) + 1, date( 'Y' ) + 2 );
				$this->start_date->add( new Legato_Form_Element_Select( 'start_date_year', ' ', array_combine( $start_date_year_options, $start_date_year_options ) ) );

			}

			if ( $level > 1 && $advisor != NULL )
			{

				// Get the users that we can use for the test user.
				$users = $advisor->User->fetch();

				// Format them into an options array.
				$test_user_options = array( '0' => '' );
				foreach( $users as $user_id => $user )
					$test_user_options[$user_id] = $user->get( 'username' );

				$this->add( new Legato_Form_Element_Select( 'test_user_id', 'Test User:', $test_user_options ) );

			}

			if ( $level > 1 )
				$this->add( new Legato_Form_Element_Text( 'extra', 'Extra Information:' ) )
				     ->rule( 'required', false );

			// Set the defaults.
			if ( $level > 2 && $advisor == NULL || $level > 2 && $advisor != NULL && $advisor->get( 'start_date' ) == '' )
			{

				if ( date( 'j' ) == 1 )
				{
					// This will set a default value of the current month, first day, and current year.
					$this->start_date_month->default_value( date( 'n' ) - 1 );
					$this->start_date_day->default_value( '1' );
					$this->start_date_year->default_value( date( 'Y' ) );
				}
				else if ( date( 'j' ) > 1 && date( 'j' ) <= 15 )
				{
					// This will set a default value of the current month, 15th day, and current year.
					$this->start_date_month->default_value( date( 'n' ) - 1 );
					$this->start_date_day->default_value( '15' );
					$this->start_date_year->default_value( date( 'Y' ) );
				}
				else if ( date( 'j' ) > 15 && date( 'n' ) != 12 )
				{
					// This will set a default value of the next month, first day, and current year.
					$this->start_date_month->default_value( date( 'n' ) );
					$this->start_date_day->default_value( '1' );
					$this->start_date_year->default_value( date( 'Y' ) );
				}
				else
				{
					// This will set a default value of the first day of the first month of the new year.
					$this->start_date_month->default_value( 0 );
					$this->start_date_day->default_value( '1' );
					$this->start_date_year->default_value( date( 'Y' ) + 1 );
				}

			}

			if ( $advisor != NULL )
			{

				$this->first_name->default_value( $advisor->get( 'first_name' ) );
				$this->last_name->default_value( $advisor->get( 'last_name' ) );

				$this->certifications->default_value( $advisor->get( 'certifications' ) );

				$this->email_address->default_value( $advisor->get( 'email_address' ) );

                if ( $level > 2 )
				{
					$plugin_array = $advisor->get( 'plugins' );

					$this->index_plugin->default_value( $plugin_array['index'] );
					$this->about->default_value( $plugin_array['about'] );
					$this->contact->default_value( $plugin_array['contact'] );
					$this->workshops->default_value( $plugin_array['workshops'] );
					$this->retirement->default_value( $plugin_array['retirement'] );
					$this->credit->default_value( $plugin_array['credit'] );
					$this->eighthundred_number->default_value( $plugin_array['eighthundred_number'] );
					$this->user_signup->default_value( $plugin_array['user_signup'] );
					$this->take_a_tour->default_value( $plugin_array['take_a_tour'] );

					$this->namespace->default_value( $advisor->get( 'namespace' ) );
					$this->style_id->default_value( $advisor->get( 'style_id' ) );

					if ( $advisor->get( 'start_date' ) != '' )
					{
						$this->start_date_month->default_value( date( 'n', $advisor->get( 'start_date' ) ) - 1 );
						$this->start_date_day->default_value( date( 'j', $advisor->get( 'start_date' ) ) );
						$this->start_date_year->default_value( date( 'Y', $advisor->get( 'start_date' ) ) );
					}
				}

				if( $level > 1 )
					$this->test_user_id->default_value( $advisor->get( 'test_user_id' ) );

			}

			if ( $level > 1 )
				$this->extra->default_value( $advisor->extra );

			//------------------------------------
			// Business Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'advisors_business_information', 'Advisor\'s Business Information' ) );

			// Add the elements.
            if ( $level > 1 )
			{
				$this->add( new Legato_Form_Element_Text( 'business_name' ) );
				$this->add( new Legato_Form_Element_Text( 'web_site' ) )
				     ->rule( 'required', false );
			}

			$this->add( new Legato_Form_Element_Text( 'slogan') )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'contact_phone_number', 'Phone Number:' ) );
			$this->add( new Legato_Form_Element_Text( 'contact_fax_number', 'Fax:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'contact_email_address', 'E-mail Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'address' ) );
			$this->add( new Legato_Form_Element_Text( 'city' ) );

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

			$this->add( new Legato_Form_Element_Text( 'zip' ) );
			
			$this->add( new Legato_Form_Element_Checkbox( 'niccp', 'NICCP Member' ) )->rule( 'required', false );
			
			$this->add( new Legato_Form_Element_Checkbox( 'cfs', 'CFS Subscriber' ) )->rule( 'required', false );

			// Set the defaults.
			if ( $advisor != NULL )
			{
				if ( $level > 1 )
				{
					$this->business_name->default_value( $advisor->get( 'business_name' ) );
					$this->web_site->default_value( $advisor->get( 'web_site' ) );
				}

				$this->slogan->default_value( $advisor->get( 'slogan' ) );

				$this->contact_phone_number->default_value( $advisor->get( 'contact_phone_number' ) );
				$this->contact_fax_number->default_value( $advisor->get( 'contact_fax_number' ) );

				$this->contact_email_address->default_value( $advisor->get( 'contact_email_address' ) );

				$this->address->default_value( $advisor->get( 'address' ) );
				$this->city->default_value( $advisor->get( 'city' ) );
				$this->state->default_value( $advisor->get( 'state' ) );
				$this->zip->default_value( $advisor->get( 'zip' ) );
				
				$this->niccp->default_value( $advisor->get( 'niccp' ) );
				$this->cfs->default_value( $advisor->get( 'cfs' ) );
			}


			//------------------------------------
			// Billing Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'advisors_billing_information', 'Advisor\'s Billing Information' ) );

			$this->add( new Legato_Form_Element_Text( 'yearly_service_charge', 'One Time Service Charge:' ) );
			$this->add( new Legato_Form_Element_Text( 'monthly_service_charge' ) );
			
			$this->add( new Legato_Form_Element_Text( 'twostage_yearly_service_charge', 'Two-stage Setup Fee:' ) );
			$this->add( new Legato_Form_Element_Text( 'twostage_monthly_service_charge', 'Two-stage Monthly Fee:' ) );
			
			// Set the defaults.
			if ( $advisor != NULL )
			{
				$this->yearly_service_charge->default_value( $advisor->yearly_service_charge );
				$this->monthly_service_charge->default_value( $advisor->monthly_service_charge );
				$this->twostage_yearly_service_charge->default_value( $advisor->twostage_yearly_service_charge );
				$this->twostage_monthly_service_charge->default_value( $advisor->twostage_monthly_service_charge );
			}
			else
			{
				$this->yearly_service_charge->default_value( '699.00' );
				$this->monthly_service_charge->default_value( '69.00' );
				$this->twostage_yearly_service_charge->default_value( '299.00' );
				$this->twostage_monthly_service_charge->default_value( '29.00' );
			}

			$this->add( new Legato_Form_Element_Submit( 'add_advisor', 'Submit' ) );

		}


		public function validate( $act )
		{
			if ( !parent::validate() )
				return false;

			// Format the service charge values.
			$this->yearly_service_charge->value = preg_replace( '/[^\d\\\.]/', '', $this->yearly_service_charge->value );
			$this->monthly_service_charge->value = preg_replace( '/[^\d\\\.]/', '', $this->monthly_service_charge->value );
			$this->twostage_yearly_service_charge->value = preg_replace( '/[^\d\\\.]/', '', $this->twostage_yearly_service_charge->value );
			$this->twostage_monthly_service_charge->value = preg_replace( '/[^\d\\\.]/', '', $this->twostage_monthly_service_charge->value );

			if ( $act == 'add' && !User::username_available( $this->username->value ) )
			{
				$this->username->error( 'A user with this name already exists. Please choose another one.' );
				return false;
			}

			if ( preg_match( '/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\\\.[0-9]{2})?$/', $this->yearly_service_charge->value ) )
			{
				$this->yearly_service_charge->error( 'Yearly service charge not in the correct format. (xxx.xx)' );
				return false;
			}

			if ( preg_match( '/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\\\.[0-9]{2})?$/', $this->monthly_service_charge->value ) )
			{
				$this->monthly_service_charge->error( 'Monthly service charge not in the correct format. (xxx.xx)' );
				return false;
			}

			if ( preg_match( '/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\\\.[0-9]{2})?$/', $this->twostage_yearly_service_charge->value ) )
			{
				$this->twostage_yearly_service_charge->error( 'Two-stage Yearly service charge not in the correct format. (xxx.xx)' );
				return false;
			}

			if ( preg_match( '/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\\\.[0-9]{2})?$/', $this->twostage_monthly_service_charge->value ) )
			{
				$this->twostage_monthly_service_charge->error( 'Two-stage Monthly service charge not in the correct format. (xxx.xx)' );
				return false;
			}

			return true;

		}

	}

?>