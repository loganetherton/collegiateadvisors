<?php

	class WorkshopsController extends Legato_Controller
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


		public function index( $advisor_id, $message = '' )
		{

            $query_options = array();

            // Get only the advisors workshops, if not an admin
			if ( $this->level < 2 )
			{
				$advisor = new Advisor( $GLOBALS['admin']->get( 'advisor_id' ) );
			}
			else
			{

				// Check if an Advisor ID has been submitted and redirect to that page
				if ( $_POST['advisor_id'] != '' )
				{
					header( 'Location: ' . SITE_URL . '/admin/workshops/' . $_POST['advisor_id'] );
					return;
				}

				// Get all the advisors
				$advisors = Legato_Resource::order_by( 'business_name' )->fetch( 'Advisor' );

				if ( $advisor_id == '' || $advisor_id == 0 )
					$advisor_id = reset( $advisors )->get( 'id' );

				$advisor = new Advisor( $advisor_id );

				$this->assign( 'advisors', $advisors );

			}

			// Get all the workshops.
			$workshops = $advisor->Workshop->fetch();

			if ( $workshops )
			{
				$this->assign( 'workshops', $workshops );

				$registrants = array();
				foreach ( $workshops as $workshop )
				{
					$new_registrants = $workshop->Workshop_Registrant->fetch();
					if ( $new_registrants ) $registrants[$workshop->id] = $new_registrants;
				}

				$this->assign( 'registrants', $registrants );
			}

			$this->layout->title = 'Manage Workshops';
			$this->assign( 'page', 'workshops' );
			$this->assign( 'advisor_id', $advisor_id );

			// Did we perform an action?
			if ( $message != '' )
				$this->assign( 'action', $message );

			$this->render_view( 'workshops/index' );

		}


		public function view( $workshop_id )
		{

			$workshop = new Workshop( $workshop_id );

            if ( ( $this->level < 2 ) && ( $workshop->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// For some reason Apache is crashing when I try to format
			// the time in the view, so I'm going to format it here and pass it in.
			$workshop_time = date( 'n/j/Y - g:i A', $workshop->get( 'time' ) );

			$registrants = $workshop->Workshop_Registrant->fetch();

			$this->layout->title = 'View Workshop';
			$this->assign( 'page', 'workshops_view' );
			$this->assign( 'workshop', $workshop );
			$this->assign( 'workshop_time', $workshop_time );
			$this->assign( 'registrants', $registrants );
			$this->assign( 'num_registrants', Workshop_Registrant::get_num_registrants( $registrants ) );

			$this->render_view( 'workshops/view' );

		}


		public function add( $advisor_id = 0 )
		{

			$this->layout->title = 'Add Workshop';
			$this->assign( 'page', 'workshops_add' );

			if ( $this->level < 2 )
				$advisor_id = $GLOBALS['admin']->get('advisor_id');

			$form = new WorkshopForm( 'add', NULL, $advisor_id );

			// Does it validate?
			if ( !$form->validate() )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'workshops/add' );

			}
			else
			{

				$workshop_data = $form->values();

				if ( $form->meridian->value == 'PM' && $form->hour->value != '12' )
					$hour = $form->hour->value + 12;
				else if ( $form->meridian->value == 'AM' && $form->hour->value == '12' )
					$hour = 0;
				else
					$hour = $form->hour->value;

				$workshop_data['time'] = mktime( $hour, $form->minute->value, 0, $form->month->value, $form->day->value, $form->year->value );

				Legato_Resource::create( 'Workshop', $workshop_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/workshops/' . $workshop_data['advisor_id'] . '/added' );
				return;

			}

		}


		public function edit( $workshop_id )
		{

			$workshop = new Workshop( $workshop_id );

            if ( ( $this->level < 2 ) && ( $workshop->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->layout->title = 'Edit Workshop';
			$this->assign( 'page', 'workshops_edit' );
			$this->assign( 'workshop', $workshop );

			$form = new WorkshopForm( 'edit', $workshop );

			// Does it validate?
			if ( !$form->validate() )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'workshops/edit' );

			}
			else
			{

				$workshop_data = $form->values();

				if ( $form->meridian->value == 'PM' && $form->hour->value != '12' )
					$hour = $form->hour->value + 12;
				else if ( $form->meridian->value == 'AM' && $form->hour->value == '12' )
					$hour = 0;
				else
					$hour = $form->hour->value;

				$workshop_data['time'] = mktime( $hour, $form->minute->value, 0, $form->month->value, $form->day->value, $form->year->value );

				$workshop->update( $workshop_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/workshops/' . $workshop_data['advisor_id'] . '/edited' );
				return;

			}

		}


		public function delete()
		{

			Legato_Settings::set( 'stage', 'show_layout', false );

			// Get the workshop and it's registrants.
			$workshop = new Workshop( $_POST['id'] );

            if ( ( $this->level < 2 ) && ( $workshop->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
				return;

			$registrants = $workshop->Workshop_Registrant->fetch();

			// Remove the registrants and the workshop.
			//if ( $registrants )
			//	Legato_Resource::delete( 'Workshop_Registrant', $registrants );

			if ( $_POST['id'] )
				Legato_Resource::delete( 'Workshop', $_POST['id'] );

		}

	}

?>