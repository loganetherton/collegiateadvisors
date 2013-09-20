<?php

	class Ext_56_TestPrepFulfillmentForm extends Legato_Form
	{

		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'fulfillment_form' );

			// Your information fieldset.
			$this->add( new Legato_Form_Fieldset( 'enter_information' ) );

			$this->add( new Legato_Form_Element_Text( 'parent_name', 'Parent\'s Name:' ) );

			$this->add( new Legato_Form_Element_Text( 'parent_email', 'Parent\'s Email Address:' ) )
			     ->rule( 'email', true );

			$this->add( new Legato_Form_Element_Text( 'parent_primary_phone', 'Parent\'s Primary Phone:' ) );

			$this->add( new Legato_Form_Element_Text( 'parent_secondary_phone', 'Parent\'s Secondary Phone:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'address', 'Home Street Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'city' ) );

			$this->add( new Legato_Form_Element_Text( 'state' ) );

			$this->add( new Legato_Form_Element_Text( 'zip', 'Zip Code:' ) );

			$this->add( new Legato_Form_Element_Text( 'student_name', 'Student\'s Name:' ) );

			$this->add( new Legato_Form_Element_Text( 'student_email', 'Student\'s Email Address:' ) )
			    ->rule( 'email', true );

			$this->add( new Legato_Form_Element_Text( 'student_phone', 'Student\'s Phone:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Submit( 'submit_form', 'Submit' ) );

		}

	}