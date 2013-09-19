<?php

	class ExtController extends Legato_Controller
	{
		
		public function __construct()
		{
			
			parent::__construct();
			
			// Make sure this page goes with this advisor.
			if ( !preg_match( '/[\/\\\]+' . $GLOBALS['advisor']->get( 'id' ) . '($|\D)/', $_SERVER['REQUEST_URI'] ) )
			{
				header( 'Location: ' . SITE_URL . '/' );
				return;
			}
			
		}
		
		
		public function index( $advisor_id, $requested_page )
		{
			
			// Was it the about page?
			if ( $requested_page == 'about' )
			{
				Legato_Stage::delegate( 'Ext', 'about' );
				return;
			}
			
			// Let's get this advisor's editable pages.
			$pages = $GLOBALS['advisor']->Advisor_EditablePage->fetch();
			
			// Loop through and see if any of these pages were requested.
			foreach ( $pages as $page )
			{
				
				if ( $page->get( 'filename' ) == $requested_page )
				{
					
					// Was it private?
					if ( $page->get( 'private' ) && !$GLOBALS['user'] )
					{			
						header( 'Location: ' . SITE_URL . '/login' );
						return;
					}
					
					// If it was found, show it and return.
					$this->layout->title = $page->title . ' - ';
					$this->assign( 'page', $page->get( 'filename' ) );
					
					$this->render_view( 'ext/' . $GLOBALS['advisor']->get( 'id' ) . '/' . $page->get( 'filename' ) );
				
					return;
					
				}
				
			}  // Next page.
			
			// If nothing was found, redirect them.
			header( 'Location: ' . SITE_URL . '/' );
			return;
			
		}
		
		
		public function _about()
		{
			
			$plugins = $GLOBALS['advisor']->get( 'plugins' );
			
			if ( !$plugins['about'] )
			{
				header( 'Location: ' . SITE_URL . '/' );
				return;
			}
			
			$this->layout->title = 'About ';
			$this->assign( 'page', 'about' );
			
			$this->render_view( 'ext/' . $GLOBALS['advisor']->get( 'id' ) . '/about' );
			
		}

	}