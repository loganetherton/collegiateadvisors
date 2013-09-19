<?php

	class NewsController extends Legato_Controller
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


		public function index( $message )
		{

			if ( $this->level < 2 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// Get all the news.
			$news = Legato_Resource::fetch( 'News' );

			$this->layout->title = 'Manage News';
			$this->assign( 'page', 'news' );
			$this->assign( 'news', $news );

			// Did we perform an action?
			if ( $message != '' )
				$this->assign( 'action', $message );

			$this->render_view( 'news/index' );

		}

		public function view( $article_id )
		{

            if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$news = new News( $article_id );

			$this->layout->title = 'View Article';
			$this->assign( 'page', 'news_view' );
			$this->assign( 'news', $news );

			$this->render_view( 'news/view' );

		}

		public function add()
		{

            if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->layout->title = 'Add News';
			$this->assign( 'page', 'add' );

			$form = new NewsForm( 'add' );

			// Does it validate?
			if ( !$form->validate( 'add' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'news/add' );

			}
			else
			{

				$news_data = $form->values();

				$news_data['date'] = mktime( 0, 0, 0, ($news_data['date_month'] + 1), $news_data['date_day'], $news_data['date_year'] );

				Legato_Resource::create( 'News', $news_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/news/added' );
				return;

			}

		}

		public function edit( $article_id )
		{

			if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$news = new News( $article_id );

			$this->layout->title = 'Edit News';
			$this->assign( 'page', 'edit' );

			$form = new NewsForm( 'edit', $news );

			// Does it validate?
			if ( !$form->validate( 'edit' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'news/edit' );

			}
			else
			{
				$news_data = $form->values();

				$news_data['date'] = mktime( 0, 0, 0, ($news_data['date_month'] + 1), $news_data['date_day'], $news_data['date_year'] );
				$news->update( $news_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/news/edited' );
				return;

			}

		}

		public function delete()
		{

			if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			Legato_Settings::set( 'stage', 'show_layout', false );

			// Remove the user.
			if ( $_POST['id'] )
				Legato_Resource::delete( 'News', $_POST['id'] );

		}

	}

?>