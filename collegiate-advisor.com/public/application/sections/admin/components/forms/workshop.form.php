<?php

	class WorkshopForm extends Legato_Form
	{
		
		public function __construct( $act, $workshop = NULL, $default_advisor_id = NULL )
		{
			
			$url = ($workshop != NULL) ? $act . '/' . $workshop->get( 'id' ) : $act;

			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_workshop', array( 'form_action' => SITE_URL . '/admin/workshops/' . $url ) );

            if ( $workshop != NULL )
				$value = $workshop->get('advisor_id');
			elseif ( $default_advisor_id != NULL )
				$value = $default_advisor_id;

			$month_options = array( '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December' );
			$year_options = array( date( 'Y' ) => date( 'Y' ), date( 'Y' ) + 1 => date( 'Y' ) + 1 );
			$minute_options = array( '00' => '00', '05' => '05', '10' => '10', '15' => '15', '20' => '20', '25' => '25', '30' => '30', '35' => '35', '40' => '40', '45' => '45', '50' => '50', '55' => '55', '60' => '60' );
			$meridian_options = array( 'AM' => 'AM', 'PM' => 'PM' );
			
			$day_options = array();
			$hour_options = array();
			for ( $i = 1; $i <= 31; $i++ ) $day_options[$i] = $i;
			for ( $i = 1; $i <= 12; $i++ ) $hour_options[$i] = $i;
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'workshop_information' ) );

			$this->add( new Legato_Form_Element_Hidden( 'advisor_id', $value ) );
			
			$this->add( new Legato_Form_Group( 'time', 'Date / Time:', ' ', 'time' ) );
			
			$this->time->add( new Legato_Form_Element_Select( 'month', ' ', $month_options ) );
			$this->time->add( new Legato_Form_Element_Select( 'day', ' ', $day_options ) );
			$this->time->add( new Legato_Form_Element_Select( 'year', ' ', $year_options ) );
			$this->time->add( new Legato_Form_Element_Select( 'hour', ' ', $hour_options  ) );
			$this->time->add( new Legato_Form_Element_Select( 'minute', ' ', $minute_options ) );
			$this->time->add( new Legato_Form_Element_Select( 'meridian', ' ', $meridian_options ) );
			
			$this->add( new Legato_Form_Element_Text( 'phone_number', 'Phone Number:' ) );
			
			$this->add( new Legato_Form_Element_Text( 'location', 'Location:' ) )
			     ->rule( 'required', false );
			
			$this->add( new Legato_Form_Element_Text( 'address', 'Address:' ) );
			$this->add( new Legato_Form_Element_Text( 'city', 'City:' ) );
			$this->add( new Legato_Form_Element_Text( 'state', 'State:' ) );
			$this->add( new Legato_Form_Element_Text( 'zip', 'Zip:' ) );
			
			$this->add( new Legato_Form_Element_Textarea( 'description', 'Description:' ) )
			     ->rule( 'required', false );
			
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Submit' ) );
			
			// Add the defaults.
			if ( $workshop != NULL )
			{
				
				$this->default_values( $workshop->get() );
				
				$time = $workshop->get( 'time' );
				
				$this->month->default_value( date( '0n', $time ) );
				$this->day->default_value( date( 'j', $time ) );
				$this->year->default_value( date( 'Y', $time ) );
				$this->hour->default_value( date( 'g', $time ) );
				$this->minute->default_value( date( 'i', $time ) );
				$this->meridian->default_value( date( 'A', $time ) );
				
			}
			
		}
		
	}

?>