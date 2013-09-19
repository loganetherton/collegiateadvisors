<?php

	class WorkshopRegistrantsController extends Legato_Controller
	{
		
		public $level = 0;
		
		public function __construct()
		{
			
			parent::__construct();
			
			if ( $GLOBALS['admin'] == false )
			{
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;
			}

            $this->level = $GLOBALS['admin']->get( 'level' );
			$this->assign( 'level', $this->level );
			
		}
		
		
		public function view( $registrant_id )
		{
			
			$registrant = new Workshop_Registrant( $registrant_id );
			$workshop = $registrant->get( 'workshop' );
			
			if ( ( $this->level < 2 ) && ( $workshop->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}
			
			$this->layout->title = 'View Workshop Registrant';
			$this->assign( 'registrant', $registrant );
			
			$this->render_view( 'workshop_registrants/view' );
			
		}
		
		
		public function print_all( $workshop_id )
		{
			
			Legato_Settings::set( 'stage', 'show_layout', false );
			
			$workshop = new Workshop( $workshop_id );

            if ( ( $this->level < 2 ) && ( $workshop->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$registrants = $workshop->Workshop_Registrant->fetch();

			$this->assign( 'registrants', $registrants );
			$this->assign( 'num_registrants', Workshop_Registrant::get_num_registrants( $registrants ) );
			
			$this->render_view( 'workshop_registrants/print_all' );
			
		}

	}

?>