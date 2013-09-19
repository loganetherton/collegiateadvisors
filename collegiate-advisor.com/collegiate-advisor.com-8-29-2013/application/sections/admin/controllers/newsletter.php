<?php

	class NewsletterController extends Legato_Controller
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
		

		public function index()
		{
			
			// Restrict access.
			if ( $this->level < 2 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}
			
			// Get the most recent newsletter's news.
			$date = strtotime( date( 'm/1/Y' ) );
			$news = Legato_Resource::limit( 1 )->fetch( 'Newsletter_News', array( 'date >=' => $date ) );
			
			// Was any news found?
			if ( !$news )
			{
				$id = Legato_Resource::create( 'Newsletter_News', array( 'date' => time() ) );
				$news = new Newsletter_News( $id );							
			}
			else
				$news = reset( $news );
			
			// Do the form thing.
			$form = new NewsletterForm( $news );
			
			// If the form validates, update the news.
			if ( $form->validate() )
			{
				$news->update( $form->values() );
			}
			
			// Set up the page.
			$this->layout->title = 'Manage Newsletter';
			$this->assign( 'page', 'newsletter' );
			$this->assign( 'news', $news );
			$this->assign( 'form', $form );
			
			$this->render_view( 'newsletter/index' );
			
		}

	}

?>