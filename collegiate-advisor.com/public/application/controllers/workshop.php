<?php

	class WorkshopController extends Legato_Controller
	{
		
		private $plugins = array();
		
		
		public function __construct()
		{
		
			parent::__construct();
			
			session_start();
			
			$this->plugins = $GLOBALS['advisor']->get( 'plugins' );
			$this->assign( 'plugins', $this->plugins );
			
			if ( !$this->plugins['workshops'] )
			{
				header( 'Location: ' . SITE_URL . '/' );
				return;
			}
			
			$this->assign( 'page', 'workshop' );
			
		}
		

		public function index( $workshop_id )
		{

			
			$this->layout->title = 'College Workshops &amp; Webinars from ';
			
			// If no workshops, warn them.
			if ( count( Workshop::get_current_workshops() ) == 0 )
			{
				$this->render_view( 'workshop/no_workshops' );
				return;
			}
						
			$form = new WorkshopForm( $workshop_id );

			// Does it validate?
			if ( !$form->validate() )
			{
				
				$this->assign( 'form', $form );

				$this->render_view( 'workshop/index' );
				
			}
			else
			{
				
				Legato_Settings::set( 'stage', 'show_header_footer', false );

				// Create the registrant.
				$registrant_data = $form->values();
				
				$_SESSION['registrant_id'] = Legato_Resource::create( 'Workshop_Registrant', $registrant_data ); // Success!
				
				// Get the workshop.
				$workshop = new Workshop( $registrant_data['workshop_id'] );
				
				// Format the grades.
				$grades = array();
				foreach ( $registrant_data['grades'] as $grade )
				{
					if      ( $grade == 'grade1' ) $grades[] = 'Grade 1';
					else if ( $grade == 'grade2' ) $grades[] = 'Grade 2';
					else if ( $grade == 'grade3' ) $grades[] = 'Grade 3';
					else if ( $grade == 'grade4' ) $grades[] = 'Grade 4';
					else if ( $grade == 'grade5' ) $grades[] = 'Grade 5';
					else if ( $grade == 'grade6' ) $grades[] = 'Grade 6';
					else if ( $grade == 'grade7' ) $grades[] = 'Grade 7';
					else if ( $grade == 'grade8' ) $grades[] = 'Grade 8';
					else if ( $grade == 'grade9' ) $grades[] = 'Grade 9';
					else if ( $grade == 'grade10' ) $grades[] = 'Grade 10';
					else if ( $grade == 'grade11' ) $grades[] = 'Grade 11';
					else if ( $grade == 'grade12' ) $grades[] = 'Grade 12';
					else if ( $grade == 'grade13' ) $grades[] = 'Freshman in College';
					else if ( $grade == 'grade14' ) $grades[] = 'Sophomore in College';
					else if ( $grade == 'grade15' ) $grades[] = 'Junior in College';
					else if ( $grade == 'grade16' ) $grades[] = 'Senior in College';
				}
				
				// Create the email array
				$message['subject'] = $GLOBALS['advisor']->get( 'business_name' ) . ' - Workshop Registration Confirmation';
				$message['to'] = $registrant_data['email_address'];
				$message['from'] = $GLOBALS['advisor']->get( 'contact_email_address' );
				
				// Assign the View Variables
				$message['data']['registrant_data'] = $registrant_data;
				$message['data']['grades'] = $grades;
				$message['data']['workshop'] = $workshop;
				
				// Select Which View to render
				$message['view'] = 'emails/workshop_registered';

				// Send the Email
				EmailHelper::send( $message );
				
				$message['subject'] = 'Collegiate Advisors: Registrant Confirmation Email for your Workshop';
				$message['to'] = $GLOBALS['advisor']->get( 'contact_email_address' );
				$message['from'] = 'register@collegiate-advisor.com';
				
				EmailHelper::send( $message );

				// Redirect to the registered page.
				header( 'Location: ' . SITE_URL . '/workshop/registered' );
				return;

			}

		}
		
		
		public function registered()
		{
			
			$this->layout->title = 'Thank You for Registering - ';
			$this->render_view( 'workshop/registered' );
			
		}

	}

?>