<?php

	class RecoveryForm extends Legato_Form
	{
		
		public function __construct()
		{
			
			// Initialize this form.
			parent::__construct( 'recovery_form', array( 'form_action' => SITE_URL . '/recovery' ) );
			
			// Add the elements.
			$this->add_fieldset( new Legato_Form_Fieldset( 'information_recovery', 'Information Recovery' ) );
			
			$this->add_element( new Legato_Form_Element_Text( 'email_address', 'E-mail Address:' ) );	
			$this->add_element( new Legato_Form_Element_Submit( 'submit', 'Submit' ) );
			
			// Add the rules.
			$this->email_address->add_rule( 'email', true );
			
		}
	
	}
?>