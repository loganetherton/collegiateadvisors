<?php

	class PayPal
	{
		
		private $username = '';
		private $password = '';
		private $signature = '';
		private $endpoint = '';
		private $version = '';

		public function __construct()
		{
			
			$this->username = Legato_Settings::get( 'paypal', 'username' );
			$this->password = Legato_Settings::get( 'paypal', 'password' );
			$this->signature = Legato_Settings::get( 'paypal', 'signature' );
			$this->endpoint = Legato_Settings::get( 'paypal', 'endpoint' );
			$this->version = Legato_Settings::get( 'paypal', 'version' );
			
		}
		
		
		/*
			Function: call()
			Function to perform the API call to PayPal using the API signature.
		*/
		public function call( $method, $str )
		{
			
			//setting the curl parameters.
			$ch = curl_init( $this->endpoint );
			curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
		
			//turning off the server and peer verification(TrustManager Concept).
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			
			//NVPRequest for submitting to server
			$nvpreq = 'METHOD=' . urlencode( $method ) . '&VERSION=' . urlencode( $this->version ) . '&PWD=' . urlencode( $this->password ) . '&USER=' . urlencode( $this->username ) . '&SIGNATURE=' . urlencode( $this->signature ) . $str;
			
			//setting the nvpreq as POST FIELD to curl
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpreq );
		
			//getting response from server
			$response = curl_exec( $ch );
		
			//convrting NVPResponse to an Associative Array
			$nvpResArray = $this->decode( $response );
			
			if ( curl_errno( $ch ) )
			{
				return false;
				//var_dump( curl_errno( $ch ) );
				//var_dump( curl_error( $ch ) );
				
				// moving to display page to display curl errors
				//$_SESSION['curl_error_no'] = curl_errno( $ch );
				//$_SESSION['curl_error_msg'] = curl_error( $ch );
				//$location = 'APIError.php';
				
				//header("Location: $location");
				
			 }
			 else
				curl_close($ch);
		
			return $nvpResArray;
			
		}
		
		
		public function set_express( $data )
		{
		
			// For some reason PayPal won't redirect to david.localhost, so we have to have this in.	
			$domain = ($_SERVER['SERVER_ADDR'] == '127.0.0.1') ? DOMAIN : 'http://' . $_SERVER['HTTP_HOST'];
						
			$data['return_url'] = $domain . SITE_URL . '/signup/confirm/';
			$data['cancel_url'] = $domain . SITE_URL . '/signup/';
			
			$this->setup_recurring( $data );
			
			$request = $this->encode( $data );
			$response = $this->call( 'SetExpressCheckout', $request );
			
			$ack = strtoupper( $response['ACK'] );
			
			if ( $ack == 'SUCCESS' )
			{
				
				$token = urldecode( $response['TOKEN'] );
				$paypal_url = Legato_Settings::get( 'paypal', 'express_url' ) . $token;
				
				header( 'Location: ' . $paypal_url );
				exit();
				
			} 
			else  
				return false;
			
		}
		
		
		public function get_express( $token )
		{
			
			$request = array( 'token' => $token );
							
			$request = $this->encode( $request );
			$response = $this->call( 'GetExpressCheckoutDetails', $request );
			
			$ack = strtoupper( $response['ACK'] );
			
			if ( $ack == 'SUCCESS' )
				return $response;
			else  
				return false;
			
		}
		
		
		public function do_express( $token, $payer_id, $data )
		{
			
			$this->setup_recurring( $data );
			
			$data['token'] = $token;
			$data['payer_id'] = $payer_id;
			
			$request = $this->encode( $data );
			$response = $this->call( 'CreateRecurringPaymentsProfile', $request );		
			
			$ack = strtoupper( $response['ACK'] );
			
			if ( $ack != 'SUCCESS' ) 
				return false;
				
			return $response;
			
		}
		
		
		public function create_direct_profile( $data )
		{
			
			$this->setup_recurring( $data );
			
			$request = $this->encode( $data );
			$response = $this->call( 'CreateRecurringPaymentsProfile', $request );
			
			$ack = strtoupper( $response['ACK'] );
			
			if ( $ack != 'SUCCESS' ) 
				return false;
				
			return $response;
			
		}
		
		
		public function update_profile( &$data )
		{
			
			$request = $this->encode( $data );
			$response = $this->call( 'UpdateRecurringPaymentsProfile', $request );
			
			$ack = strtoupper( $response['ACK'] );
			
			if ( $ack != 'SUCCESS' ) 
				return false;
				
			return $response;
			
		}
		
		
		public function setup_recurring( &$data )
		{
			
			$data['billing_type'] = 'RecurringPayments';
			$data['billing_description'] = 'College Planning Services';
			$data['description'] = 'College Planning Services';
			
			$data['start_date'] = date( 'c' );
			
			$type = $data['billing_type_monthly'] || $data['billing_type_twostage'] ? 'monthly_' : 'yearly_';					
			$data['billing_period'] = Legato_Settings::get( 'paypal', $type . 'billing_period' );
			$data['billing_frequency'] = Legato_Settings::get( 'paypal', $type . 'billing_frequency' );
			$data['billing_cycles'] = Legato_Settings::get( 'paypal', $type . 'billing_cycles' );
			
			if( $data['billing_type_twostage'] )
			{
				$amounts = explode( '|', $data['amount'] );
				$data['amount'] = $amounts[1];
				$data['onetime_amount'] = $amounts[0];
			}
			
		}
		
		
		/*
			Function: encode()
			This function with take an array of values and create a name/value paired string.
		*/
		public function encode( $data )
		{
			
			$str = '';

			$keys = array
			( 
				'PAYMENTACTION' => 'type',
				'AMT' => 'amount',
				'CREDITCARDTYPE' => 'card_type',
				'ACCT' => 'card_number',
				'CVV2' => 'cvv',
				'FIRSTNAME' => 'billing_first_name',
				'LASTNAME' => 'billing_last_name',
				'STREET' => 'address1',
				'CITY' => 'city',
				'STATE' => 'state',
				'ZIP' => 'zip_code',
				'EMAIL' => 'email_address',
				'RETURNURL' => 'return_url',
				'CANCELURL' => 'cancel_url',
				'TOKEN' => 'token',
				'PAYERID' => 'payer_id',
				'PROFILESTARTDATE' => 'start_date',
				'BILLINGPERIOD' => 'billing_period',
				'BILLINGFREQUENCY' => 'billing_frequency',
				'TOTALBILLINGCYCLES' => 'billing_cycles',
				'L_BILLINGTYPE0' => 'billing_type',
				'L_BILLINGAGREEMENTDESCRIPTION0' => 'billing_description',
				'DESC' => 'description',
				'INITAMT' => 'initial_amount',
				'AUTHORIZATIONID' => 'authorization_id',
				'NOTE' => 'note',
				'PROFILEID' => 'profile_id',
			);
			
			if( $data['billing_type_twostage'] )
			{
				$keys['INITAMT'] = 'onetime_amount';
			}
		
			foreach ( $keys as $key => $value )
				if ( $data[$value] )
					$str .= '&' . $key . '=' . urlencode( $data[$value] );
			
			$str .= '&EXPDATE=' . urlencode( $data['expiration_month'] ) . urlencode( $data['expiration_year'] );
			$str .= '&COUNTRYCODE=US';
			$str .= '&CURRENCYCODE=USD';
			$str .= '&IPADDRESS=' . urlencode( $_SERVER['REMOTE_ADDR'] );

			return $str;
		
		}
		
		
		/*
			Function: decode()
			This function with take a name/value string and convert it to an array and decode the response.
		*/
		public function decode( $nvpstr )
		{
		
			$intial = 0;
		 	$nvpArray = array();
		
		
			while( strlen( $nvpstr ) )
			{
				
				//postion of Key
				$keypos = strpos( $nvpstr, '=' );
				
				//position of value
				$valuepos = strpos( $nvpstr, '&' ) ? strpos( $nvpstr, '&' ) : strlen( $nvpstr );
		
				/*getting the Key and Value values and storing in a Associative Array*/
				$keyval = substr( $nvpstr, $intial, $keypos );
				$valval = substr( $nvpstr, $keypos + 1, $valuepos - $keypos - 1 );
				
				//decoding the respose
				$nvpArray[urldecode( $keyval )] = urldecode( $valval );
				$nvpstr = substr( $nvpstr, $valuepos + 1, strlen( $nvpstr ) );
				
			}
			
			return $nvpArray;
		
		}
		
		
		/*
			Function: errors()
			Will try to extract any errors out of the response array passed in and return them.
		*/
		public function errors( $response )
		{
			
			// Let's extract any general errors.
			$ack = $response['ACK'];
			
			switch ( $ack )
			{
				
				case 'Success':
				
					/* Do Nothing */
					/* It successfully went through */
					break;				
				
				case 'SuccessWithWarning':
				
					// If there was a warning, try checking to make sure the address and
					// CVV code are correct.
							
					// Check for address verification issues.
					$avs = $response['AVSCODE'];
					
					switch ( $avs )
					{
						
						case 'X':
						case 'Y':
						case 'D':
						case 'F':
						
							/* Do Nothing */
							/* It correctly validated the address */
							/* Let it fall through to the next check, if any */
							break;
						
						case 'A':
						case 'B':
							
							return array( false, 'address1', 'Your zip code could not be validated.<br />Are you sure you entered the correct zip code?' );
							break;
							
						case 'C':
						case 'N':
						case 'G':
						case 'I':
						case 'P':
						case 'Z':
						case 'W':
						
							return array( false, 'address1', 'Your address could not be verified.<br />Please check over your information to make sure it is all correct.' );
							break;
							
						default:
						
							return array( false, '', 'There was a problem while processing your request.<br />Please try again.' );
							break;
						
					}			
					
					// Check for CVV issues.
					$cvv = $response['CVV2MATCH'];
					
					switch ( $cvv )
					{
						
						case 'M':
							
							/* Do Nothing */
							/* It correctly validated the CVV */
							/* Let it fall through to the next check, if any */
							break;
							
						case 'N':
						
							return array( false, 'cvv', 'Your CVV code did not verify.<br />Please make sure you entered it correctly.' );
							break;
							
						default:
						
							return array( false, '', 'There was a problem while processing your request.<br />Please try again.' );
							break;
						
					}
					
					break;
					
				default:
					
					return array( false, '', 'The card you entered could not be validated. Please check your card number again to make sure it\'s correct.' );
					break;
				
			}
			
			// No errors!
			return array( true, '', '' );
		
		}	
		
	}