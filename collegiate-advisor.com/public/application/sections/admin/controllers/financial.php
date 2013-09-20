<?php

	class FinancialController extends Legato_Controller
	{
		
		public $level = 0;
		
				
		public function __construct()
		{
			
			parent::__construct();
			
			// If the user is not logged in, redirect them.
			if ( $GLOBALS['admin'] == false )
			{
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;
			}
				
			// Get the level of the user.
			$this->level = $GLOBALS['admin']->get( 'level' );
			$this->assign( 'level', $this->level );
			
			// Check for permission.
			if ( $this->level < 2 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

		}


		public function index( $message = '' )
		{
			
			// Get all the advisors.
			$advisors = Legato_Resource::fetch( 'Advisor' );			

			// Set up the page variables.
			$this->layout->title = 'Financial Statement';
			$this->assign( 'page', 'financial' );
			$this->assign( 'advisors', $advisors );
			$this->assign( 'user_count', Advisor::get_user_count() );
			
			// Did we perform an action?
			$this->assign( 'action', $message );
			
			// Render the view.
			if ( $message == 'printable' )
			{
				Legato_Settings::set( 'stage', 'show_layout', false );
				$this->render_view( 'financial/printable' );
			}
			else
				$this->render_view( 'financial/index' );
			
		}

	}

?>