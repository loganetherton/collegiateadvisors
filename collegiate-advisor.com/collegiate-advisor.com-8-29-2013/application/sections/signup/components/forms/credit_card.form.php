<?php

	class CreditCardForm extends Legato_Form
	{
		
		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'credit_card_information' );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'billing_information' ) );

			$card_type_options = array
			( 
				'Visa' => 'Visa', 
				'MasterCard' => 'MasterCard', 
				'Discover' => 'Discover', 
				'Amex' => 'American Express'
			);
			
			$this->add( new Legato_Form_Element_Select( 'card_type', 'Payment Method:', $card_type_options ) );
			
			$this->add( new Legato_Form_Element_Text( 'billing_first_name' ) );
			$this->add( new Legato_Form_Element_Text( 'billing_last_name' ) );
			
			$this->add( new Legato_Form_Element_Text( 'card_number' ) )
			     ->rule( 'credit_card', true );
			     
			$months = array();
			for ( $i = 1; $i <= 12; $i++ )
				$months[sprintf( "%02d", $i )] = date( 'M - m', (mktime( 0, 0, 0, $i ) - 1) );
			
			$year = date( 'Y' );
			$years = array();
			for ( $i = 0; $i < 12; $i++ )
				$years[($year + $i)] = $year + $i;
				
			$this->add( new Legato_Form_Group( 'exp', 'Expiration Date:' ) );
			
			$this->exp->add( new Legato_Form_Element_Select( 'expiration_month', false, $months ) );
			$this->exp->add( new Legato_Form_Element_Select( 'expiration_year', false, $years ) );
			
			$this->add( new Legato_Form_Element_Text( 'cvv', 'CVV Code:' ) )
			     ->rule( array( 'numeric' => true, 'rangelength' => array( 3, 4 ) ) );
			     
			$this->add( new Legato_Form_Element_Text( 'address1', 'Billing Address:' ) );
			$this->add( new Legato_Form_Element_Text( 'address2', ' ' ) )
			     ->rule( 'required', false );
			     
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
			
			$this->add( new Legato_Form_Element_Text( 'zip_code' ) )
			     ->rule( 'rangelength', array( 5, 10 ) );
			
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Update Information' ) );
			
			if ( $_SESSION['update_credit_information'] )
				$this->default_values( unserialize( $_SESSION['update_credit_information'] ) );
			
		}


		public function validate()
		{
			
			if ( !parent::validate() )
				return false;
		
			// Get the data for authorization.
			$data = $this->values();
			$data['type'] = 'Authorization';
			$data['amount'] = '1.00';  // Authorize a minimal amount.
			
			// Set up the paypal object and make the call.
			$paypal = new PayPal();
			
			$request = $paypal->encode( $data );
			$response = $paypal->call( 'DoDirectPayment', $request );
			
			// We have to void the authorization.
			$transaction_id = $response['TRANSACTIONID'];		
			if ( $transaction_id )
			{
				$void_request = $paypal->encode( array( 'authorization_id' => $transaction_id, 'note' => 'Authorization of your account.' ) );
				$void_response = $paypal->call( 'DoVoid', $void_request );
			}
			
			// Get any errors that were thrown.
			list( $error_flag, $error_field, $error_message ) = $paypal->errors( $response );
			
			// Any errors?
			if ( !$error_flag )
			{
				
				// Output the errors.
				if ( $error_field == '' )
					$error_field = 'card_type';
				
				$this->{$error_field}->error( $error_message );
				return false;
				
			}

			return true;
		}

	}

?>