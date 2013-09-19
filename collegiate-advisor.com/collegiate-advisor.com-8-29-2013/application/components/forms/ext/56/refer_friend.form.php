<?php

	class Ext_56_ReferFriendForm extends Legato_Form
	{

		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'refer_friend' );

			// Your information fieldset.
			$this->add( new Legato_Form_Fieldset( 'Your Information' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_name', 'Your Full Name:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_address', 'Street Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_city', 'City:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_state', 'State:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_zip', 'Zip Code:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_email', 'Email Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'referrer_primary_phone', 'Primary Phone:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'referrer_secondary_phone', 'Secondary Phone:' ) )
			     ->rule( 'required', false );

			// Friend's information fieldset.
			$this->add( new Legato_Form_Fieldset( 'Friend\'s Information' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_name', 'Friend\'s Full Name:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_address', 'Street Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_city', 'City:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_state', 'State:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_zip', 'Zip Code:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_email', 'Email Address:' ) );

			$this->add( new Legato_Form_Element_Text( 'referred_primary_phone', 'Primary Phone:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Text( 'referred_secondary_phone', 'Secondary Phone:' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Submit( 'submit_form', 'Send Referral' ) );

		}

	}