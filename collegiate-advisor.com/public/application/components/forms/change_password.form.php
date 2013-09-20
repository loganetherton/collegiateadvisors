<?php

	class ChangePasswordForm extends Legato_Form
	{
		
		public function __construct( $url )
		{
			
			// Initialize this form.
			parent::__construct( 'change_user_password', array( 'form_action' => SITE_URL . '/change_password' . $url ) );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'change_password', 'Change Password' ) );
			
			$this->add( new Legato_Form_Element_Password( 'password', 'New Password:', 'password' ) )
			     ->filter( 'html', false );
			
			$this->add( new Legato_Form_Element_Password( 'retyped_password', 'Retype Password:', 'retyped password' ) )
			     ->filter( 'html', false );
	
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Change Password' ) );
			
			// Add the rules.
			$this->password->rule( array( 'compare' => 'retyped_password', 'rangelength' => array( 6, 15 ) ) );
			$this->retyped_password->rule( 'rangelength', array( 6, 15 ) );
			
		}
	
	}
?>