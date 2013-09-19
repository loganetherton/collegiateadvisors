<?php

	class EssaysController extends Legato_Controller
	{

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

			if ( $this->level < 2 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

		}


		public function index( $advisor_id = 0 )
		{

            $query_options = array( $advisor_id );

			if ( $_POST['advisor_id'] != '' )
			{
				header( 'Location: ' . SITE_URL . '/admin/essays/' . $_POST['advisor_id'] );
				return;
			}

			$advisors = Legato_Resource::order_by( 'business_name' )->fetch( 'Advisor' );

			if ( $advisor_id == '' || $advisor_id == 0 )
				$advisor_id = reset( $advisors )->get( 'id' );

			// Get all the essays.
			$essays = User_Essay::get_essays( $advisor_id );

			$this->layout->title = 'Manage Essays';
			$this->assign( 'page', 'essays' );

			$this->assign( 'advisors', $advisors );
			$this->assign( 'essays', $essays );
			$this->assign( 'advisor_id', $advisor_id );

			$this->render_view( 'essays/index' );

		}


		public function view( $essay_id, $filename )
		{

			Legato_Settings::set( 'stage', 'show_header_footer', false );

			// Create a new essay object.
			$essay = new User_Essay( $essay_id, urldecode( $filename ) );

			// Display the essay.
			$essay->display();

		}


		public function delete()
		{

			Legato_Settings::set( 'stage', 'show_header_footer', false );

			// Create a new essay object.
			$essay = new User_Essay( $_POST['advisor_id'], urldecode( $_POST['filename'] ) );

			// Delete the essay.
			$essay->delete();

		}

	}

?>