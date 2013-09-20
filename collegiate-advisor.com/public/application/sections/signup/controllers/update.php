<?php

	class UpdateController extends Legato_Controller
	{
		
		public function __construct()
		{
			
			parent::__construct();
			
			// We don't want people attempting to brute force us.
			sleep( 3 );
			
		}
		
		
		public function credit_card( $id, $checksum )
		{
			
			// Try to get the user.
			$GLOBALS['user'] = new User( $id );
			
			if ( !$GLOBALS['user']->first_name || $GLOBALS['user']->payment_type != 1 || $checksum != md5( $GLOBALS['user']->password . date( 'FY' ) ) )
				return 403;
				
			$paypal = new PayPal();
			
			$form = new CreditCardForm();
			
			if ( $form->validate() )
			{
				
				$data = $form->values();
				
				$_SESSION['update_credit_information'] = serialize( $data );
				
				// Update his payment profile.
				$data['profile_id'] = $GLOBALS['user']->payment_profile_id;
				$data['note'] = 'Updated billing information.';
				$response = $paypal->update_profile( $data );
				
				// If there was an error, go back to the main page.
				if ( !$response )
				{
					header( 'Location: ' . SITE_URL . '/signup/update/credit_card/' . $id . '/' . $checksum );
					exit();
				}
				
				header( 'Location: ' . SITE_URL . '/signup/update/credit_card_success' );
				exit();
				
			}
				
			$request = $paypal->encode( array( 'profile_id' => $GLOBALS['user']->payment_profile_id ) );
			$response = $paypal->call( 'GetRecurringPaymentsProfileDetails', $request );
			
			$this->layout->title = 'Update Billing Information';
			
			$data['form'] = $form;
			$data['payment_profile'] = $response;
			
			$this->render_view( 'update_credit_card', $data );
			
		}
		
		
		public function credit_card_success()
		{
			
			$this->layout->title = 'Successfully Updated';
			
			$this->render_view( 'update_credit_card_success' );
			
		}

	}

?>