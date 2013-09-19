<?php

	class NewsForm extends Legato_Form
	{
		
		public function __construct( $act, $news = NULL )
		{
			// Set up work...
			$url = ($news != NULL) ? $act . '/' . $news->get( 'id' ) : $act;
			
			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_news', array( 'form_action' => SITE_URL . '/admin/news/' . $url ) );

			//------------------------------------
			// Basic Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'news_basic_information' ) );

			// Add the elements.
			$this->add( new Legato_Form_Group( 'date', 'Date:', ' ' ) );
			
			$date_month_options = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
			$this->date->add( new Legato_Form_Element_Select( 'date_month', ' ', $date_month_options ) );
			
			$date_day_options = range( 0, 31 );
			$this->date->add( new Legato_Form_Element_Select( 'date_day', ' ', array_combine( $date_day_options, $date_day_options ) ) );
			
			$date_year_options = range( date( Y ), date( Y ) + 10 );
			$this->date->add( new Legato_Form_Element_Select( 'date_year', ' ', array_combine( $date_year_options, $date_year_options ) ) );
	
			$this->add( new Legato_Form_Element_Text( 'news' ) );
			$this->add( new Legato_Form_Element_Text( 'link' ) );

			$this->add( new Legato_Form_Element_Text( 'link_description' ) );
			
			$this->add( new Legato_Form_Element_Submit( 'add_news', 'Submit' ) );
			
			// Set the defaults.
			if ( $news != NULL )
			{

				$this->news->default_value( $news->get( 'news' ) );
				$this->link->default_value( $news->get( 'link' ) );
				$this->link_description->default_value( $news->get( 'link_description' ) );
				
				if ( $news->get( 'date' ) != '' )
				{
					$this->date_month->default_value( date( 'n', $news->get( 'date' ) ) - 1 );					
					$this->date_day->default_value( date( 'j', $news->get( 'date' ) ) );
					$this->date_year->default_value( date( 'Y', $news->get( 'date' ) ) );
				}
				
			}
			else
			{
				$this->date_month->default_value( date( 'n' ) - 1 );
				$this->date_day->default_value( date( 'j' ) );
				$this->date_year->default_value( date( 'Y' ) );
			}
			
		}
		
		
		public function validate( $act )
		{
			$error = parent::validate();
			
			if ( !$error )
				return false;

			return $error;
		}
		
	}

?>