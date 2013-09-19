<?php

	define( 'NUM_GUESTS', 4 );

	class WorkshopForm extends Legato_Form
	{

		public function __construct( $workshop_id = NULL )
		{
			// Initialize this form.
			parent::__construct( 'workshop_form', array( 'form_action' => SITE_URL . '/workshop' ) );

			//------------------------------------
			// Workshop Registration
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'workshop_registration', 'Workshops/Webinars' ) );

			// If a workshop id was passed in
			if ( $workshop_id != '' )
				$workshops = array( new Workshop( $workshop_id ) );
			
			// If no workshop ID.
			if ( $workshop_id == '' || $workshops[0]->get( 'advisor_id' ) == NULL )
				$workshops = Workshop::get_current_workshops();
			
			// Add the workshops group.
			$this->add( new Legato_Form_Group( 'workshop_id', 'Workshops/Webinars:', '<br />' ) );
			
			// Add the elements.
			$i = 1;
			foreach ( $workshops as $workshop )
			{
				$title  = '<p><strong>' . date( 'F jS, Y', $workshop->get('time') ) . ' - ' . $workshop->get( 'city' ) . ', ' . $workshop->get( 'state' ) . '</strong><br />';
				if ( $workshop_id == NULL ) $title .= '<a href="'.SITE_URL.'/workshop/'.$workshop->get('id').'" title="Register for this workshop">Register for this Workshop</a><br />';
				$title .= $workshop->get( 'location' ) . '<br />';
				$title .= $workshop->get( 'address' ) . '<br />';
				$title .= $workshop->get( 'city' ) . ', ' . $workshop->get( 'state' ) . ' ' . $workshop->get( 'zip' ) . '<br />';
				$title .= 'Phone: ' . $workshop->get( 'phone_number' ) . '<br />';
				$title .= 'Time: ' . date( 'g:i A', $workshop->get('time') ) . '<br />';
				$title .= nl2br( $workshop->get( 'description' ) ) . '<br />';
				$title .= '<a href="http://maps.google.com/maps?f=q&hl=en&time=&date=&ttype=&q=' . $workshop->get( 'address' ) . ',' . $workshop->get( 'city' ) . ',' . $workshop->get( 'state' ) . ' ' . $workshop->get( 'zip' ) . '">Map it</a>';
				$title .= '</p>';

				$this->workshop_id->add( new Legato_Form_Element_Radio( 'workshop' . $i, '', ' ', $workshop->get( 'id' ), $title ) ); 

				$i++;
			}

			// Set the defaults.
			$this->workshop1->default_value( true );

			//------------------------------------
			// Registrant Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'registrant_information', 'Registrant Information' ) );

			// Add the elements.
			$this->add( new Legato_Form_Element_Text( 'first_name', 'First Name:' ) );
			$this->add( new Legato_Form_Element_Text( 'last_name', 'Last Name:' ) );

			$this->add( new Legato_Form_Group( 'guests', 'Guests:', '<br />' ) );

			for ( $i = 1; $i <= NUM_GUESTS; $i++ )
				$this->guests->add( new Legato_Form_Element_Text( 'guest' . $i, ' ' ) );

			$this->add( new Legato_Form_Element_Text( 'phone_number', 'Phone Number:' ) );
			$this->add( new Legato_Form_Element_Text( 'email_address', 'E-mail:' ) );

			$this->add( new Legato_Form_Group( 'grades', 'Your Children\'s Grades:' ) );

			for ( $i = 1; $i < 13; $i++ )
				$this->grades->add( new Legato_Form_Element_CheckboxMultiple( 'grade' . $i, 'Grade ' . $i ) );

			$this->grades->add( new Legato_Form_Element_CheckboxMultiple( 'grade13', 'Freshman' ) );
			$this->grades->add( new Legato_Form_Element_CheckboxMultiple( 'grade14', 'Sophomore' ) );
			$this->grades->add( new Legato_Form_Element_CheckboxMultiple( 'grade15', 'Junior' ) );
			$this->grades->add( new Legato_Form_Element_CheckboxMultiple( 'grade16', 'Senior' ) );

			$this->add( new Legato_Form_Element_Submit( 'register', 'Submit Registration' ) );

			// Set the defaults.
			if ( $GLOBALS['user'] )
			{
				$this->first_name->default_value( $GLOBALS['user']->get( 'first_name' ) );
				$this->last_name->default_value( $GLOBALS['user']->get( 'last_name' ) );
				$this->phone_number->default_value( $GLOBALS['user']->get( 'phone_number' ) );
				$this->email_address->default_value( $GLOBALS['user']->get( 'email_address' ) );
			}

			// Add the rules.
			$this->first_name->rule( 'rangelength', array( 3, 50 ) );
			$this->last_name->rule( 'rangelength', array( 3, 50 ) );

			$this->phone_number->rule( array( 'required' => false, 'rangelength' => array( 0, 15 ) ) );
			$this->email_address->rule( 'email', true );

		}

		public function validate()
		{
			$error = parent::validate();

			return $error;
		}

	}

?>