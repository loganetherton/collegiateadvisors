<?php

	class LoginForm extends Legato_Form
	{
		
		public function __construct()
		{
			
			// Initialize this form.
			parent::__construct( 'login_form', array( 'form_action' => SITE_URL . '/login' ) );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'login_information' ) );
			
			$this->add( new Legato_Form_Element_Text( 'username' ) )
			     ->rule( 'rangelength', array( 4, 15 ) );
			     
			$this->add( new Legato_Form_Element_Password( 'password' ) )
			     ->rule( 'rangelength', array( 5, 15 ) )
				 ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Submit( 'login', 'Login' ) );
			
		}
		
		
		public function validate()
		{
			
			$error = parent::validate();
			
			if ( !$error )
				return false;
			
			// Sleep for 3 seconds.
			// This is to protect against brute force hackers.
			sleep( 3 );
			
			// Authenticate user.
			$userid = User::authenticate( $this->username->value, $this->password->value );
			
			// User authenticated?
			if ( $userid === false )
			{
				
				// If the user ID is false, check to see if it's an admin logging in.
				$admin_id = Admin::authenticate( $this->username->value, $this->password->value );
				
				// Is it an admin?
				if ( $admin_id !== false )
				{
					
					// Let's try to redirect them to the admin control panel.
					header( 'Location: ' . SITE_URL . '/admin/login/?username=' . $this->username->value );
					
				}
			
				// Wrong user or pass, add error.
				$this->username->error( 'The username/password combination you entered was wrong.' );
				
				// Failure.
				return false;
				
			}
			
			// Set the user ID so that this page can access it now.
			$GLOBALS['user_id'] = $userid;
		
			// Success!
			return true;			
			
		}
		
	}