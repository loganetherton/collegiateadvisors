<?php

	class LoginForm extends Legato_Form
	{
		
		public function __construct()
		{
			
			// Initialize this form.
			parent::__construct( 'admin_login' );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'login_information', 'Login Information' ) );
			
			$this->add( new Legato_Form_Element_Text( 'username', 'Username:' ) )
			     ->rule( 'rangelength', array( 4, 15 ) )
			     ->default_value( $_GET['username'] );
			
			$this->add( new Legato_Form_Element_Password( 'password', 'Password:' ) )
			     ->rule( 'rangelength', array( 5, 15 ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Submit( 'login', 'Login' ) );
			
			// If a username was passed in, give them a message.
			if ( $_GET['username'] != '' )
				$this->username->error( 'For security reasons, you must login again.<br />If you would like to bypass this second login step, please bookmark this page<br />and use it to login from now on.' );
			
		}
		
		
		public function validate()
		{
			if ( !parent::validate() )
				return false;
			
			// Sleep for 3 seconds.
			// This is to protect against brute force hackers.
			sleep( 3 );
			
			// Authenticate user.
			$admin_id = Admin::authenticate( $this->username->value, $this->password->value );
			
			// User authenticated?
			if ( !$admin_id )
			{
			
				// Wrong user or pass, add error.
				$this->username->error( 'The username/password combination you entered was wrong.' );
				
				// Failure.
				return false;
				
			}
			
			// Set the admin ID so that this page can access it now.
			$GLOBALS['admin_id'] = $admin_id;
			
			// Success!
			return true;			
			
		}
		
	}

?>