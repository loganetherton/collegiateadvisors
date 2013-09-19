<?php

	class StylesController extends Legato_Controller
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


		public function index( $message = '' )
		{

			if ( $this->level < 3 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// Get all the advisors.
			$styles = Legato_Resource::fetch( 'Style' );

			$advisors = Legato_Resource::get_sub_resources( $styles, 'Advisor' );

			$this->layout->title = 'Manage Styles';
			$this->assign( 'page', 'styles' );
			$this->assign( 'styles', $styles );
			$this->assign( 'advisors', $advisors );

			// Did we perform an action?
			if ( $message != '' )
				$this->assign( 'action', $message );

			$this->render_view( 'styles/index' );

		}

		public function view( $style_id )
		{

            if ( $this->level < 3 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$style = new Style( $style_id );

			$this->layout->title = 'View Style';
			$this->assign( 'page', 'styles_view' );
			$this->assign( 'style', $style );

			$this->render_view( 'styles/view' );

		}

		public function add()
		{

            if ( $this->level < 3 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->layout->title = 'Add Style';
			$this->assign( 'page', 'add' );

			$form = new StyleForm( 'add' );

			// Does it validate?
			if ( !$form->validate( 'add' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'styles/add' );

			}
			else
			{

				$style_data = $form->values();

				// Create the style.
				$style_id = Legato_Resource::create( 'Style', $style_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/styles/added' );
				return;

			}

		}

		public function edit( $style_id )
		{

            if ( $this->level < 3 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$style = new Style( $style_id );

			$this->layout->title = 'Edit Style';
			$this->assign( 'page', 'edit' );

			$form = new StyleForm( 'edit', $style );

			// Does it validate?
			if ( !$form->validate( 'edit' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'styles/edit' );

			}
			else
			{

				$style_data = $form->values();

				$style->update( $style_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/styles/edited' );
				return;

			}

		}

		public function delete()
		{

			if ( $this->level < 3 )
            	return;

			Legato_Settings::set( 'stage', 'show_layout', false );

			if ( $_POST['id'] )
				Legato_Resource::delete( 'Style', $_POST['id'] );

		}

	}

?>