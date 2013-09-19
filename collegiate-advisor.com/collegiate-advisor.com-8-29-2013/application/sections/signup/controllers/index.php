<?php

	class IndexController extends Legato_Controller
	{
		
		public function __construct()
		{
			
			parent::__construct();
			
			session_start();
			
			if ( $_SERVER['SERVER_ADDR'] != '127.0.0.1' && $_SERVER['HTTPS'] != 'on' )
			{
				header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				exit();
			}
			
		}
		
		public function index( $val = '' )
		{
			
			$change = false;
			
			if ( $val == 'change' )
				$change = true;
			else
				$advisor_id = $val;
			
			// Advisor ID passed in?
			if ( $advisor_id )
				unset( $_SESSION['information'] );
			else if ( !$advisor_id && !$_SESSION['advisor_id'] )
				return 400;
			else if ( $_SESSION['advisor_id'] )
				$advisor_id = $_SESSION['advisor_id'];
				
			// Valid advisor?
			$advisor = new Advisor( $advisor_id );
			if ( !$advisor->namespace )
				return 403;
			
			$form = new SignupForm( $advisor );
			
			if ( $form->validate() )
			{
				
				$data = $form->values();
				$_SESSION['advisor_id'] = $advisor_id;
				$_SESSION['information'] = serialize( $data );
				
				if ( $data['card_type'] == 'PayPal' )
				{										
					$paypal = new PayPal();
					$paypal->set_express( $data );					
				}
				else
				{	
					header( 'Location: ' . SITE_URL . '/signup/confirm/' );
					exit();
				}
				
			}
			
			$this->layout->title = 'Sign Up';
			$data['form'] = $form;
			
			$this->render_view( 'index', $data );
			
		}
		
		
		public function confirm()
		{
			
			if ( !$this->_check_access() )
				return 403;
			
			$this->layout->title = 'Confirm Information';
			
			$_SESSION['paypal_token'] = $_GET['token'];
			$_SESSION['paypal_payerid'] = $_GET['PayerID'];
			
			$data = unserialize( $_SESSION['information'] );
			
			if ( $data['card_type'] == 'PayPal' )
			{
				
				$paypal = new PayPal();
				$response = $paypal->get_express( $_SESSION['paypal_token'] );
				
				$data['paypal_info'] = array();
				$data['paypal_info']['account'] = $response['EMAIL'];
				
			}
			
			$data['payment_synopsis'] = new Legato_View( 'payment_synopsis', $data );
					
			$this->render_view( 'confirm', $data );

		}
		
		
		public function process()
		{
		
			if ( !$this->_check_access() )
				return 403;	
				
			$advisor = new Advisor( $_SESSION['advisor_id'] );
			
			$data = unserialize( $_SESSION['information'] );
			
			$paypal = new PayPal();
			
			if ( $data['card_type'] == 'PayPal' )
				$response = $paypal->do_express( $_SESSION['paypal_token'], $_SESSION['paypal_payerid'], $data );
			else
				$response = $paypal->create_direct_profile( $data );
			
			// If there was an error, go back to the main page.
			if ( !$response )
			{
				header( 'Location: ' . SITE_URL . '/signup/change/' );
				exit();
			}
			
			// Create the user.
			$data['advisor_id'] = $_SESSION['advisor_id'];
			$data['address'] = $data['address1'] . "\n" . $data['address2'];
			$data['zip'] = $data['zip_code'];
			$data['birth_date'] = strtotime( $data['birth_date_month'] . '/' . $data['birth_date_day'] . '/' . $data['birth_date_year'] );
			$data['state'] = ($data['card_type'] == 'PayPal') ? $advisor->state : $data['state'];
			$data['payment_profile_id'] = $response['PROFILEID'];
			$data['payment_type'] = ($data['card_type'] == 'PayPal') ? 2 : 1;
			
			User::my_create( $data );
			
			// Send the email.
			EmailHelper::send_signup_email( $_SESSION['advisor_id'], $data );
			
			// Redirect to the payment receipt page.
			header( 'Location: ' . SITE_URL . '/signup/receipt/' );
			exit();

		}
		
		
		public function receipt()
		{
			
			if ( !$this->_check_access() )
				return 403;
			
			$this->layout->title = 'Payment Receipt';
			
			$data = unserialize( $_SESSION['information'] );
			
			if ( $data['card_type'] == 'PayPal' )
			{
				
				$paypal = new PayPal();
				$response = $paypal->get_express( $_SESSION['paypal_token'] );
				
				$data['paypal_info'] = array();
				$data['paypal_info']['account'] = $response['EMAIL'];
				
			}
						

			$data['payment_synopsis'] = new Legato_View( 'payment_synopsis', $data );
					
			$this->render_view( 'receipt', $data );

		}
		
		
		public function agreement()
		{
			
			$this->layout->title = 'Agreement';
			
			$this->render_view( 'agreement' );
			
		}
		
		
		public function _agreement()
		{
			
			$this->render_view( '_agreement' );
			
		}
		
		
		private function _check_access()
		{
			
			if ( !$_SESSION['advisor_id'] )
				return false;
				
			return true;
			
		}
		
	}