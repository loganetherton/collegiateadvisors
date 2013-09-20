<?php

	class Ext_56_EfcCalculatorForm extends Legato_Form
	{

		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'refer_friend' );

			// Your information fieldset.
			$this->add( new Legato_Form_Fieldset( 'Your Information' ) );

			$this->add( new Legato_Form_Element_Text( 'name', 'Your Full Name:' ) );

			$this->add( new Legato_Form_Element_Text( 'address', 'Street Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'city', 'City:' ) );

			$this->add( new Legato_Form_Element_Text( 'state', 'State:' ) );

			$this->add( new Legato_Form_Element_Text( 'zip', 'Zip Code:' ) );

			$this->add( new Legato_Form_Element_Text( 'email', 'Email Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'phone', 'Contact Phone:' ) );

			$this->add( new Legato_Form_Element_Submit( 'submit_form', 'Get Your EFC Calculator' ) );

		}

	}