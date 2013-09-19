<?php

	class UsersController extends Legato_Controller
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

			$conditional = array();

			// Get only the advisor's users, if not an admin.
			if ( $this->level < 2 )
			{
				$conditional['advisor_id'] = $GLOBALS['admin']->get( 'advisor_id' );
			}
			else
			{

				if ( $_POST['advisor_id'] != '' )
				{
					header( 'Location: ' . SITE_URL . '/admin/users/' . $_POST['advisor_id'] );
					return;
				}

				$advisors = Legato_Resource::order_by( 'business_name' )->fetch( 'Advisor' );
				$this->assign( 'advisors', $advisors );

				if ( $advisor_id != '' && $advisor_id != 0 )
				{
					$conditional['advisor_id'] = $advisor_id;
					$this->assign( 'selected', $advisor_id );
				}
				else
					$this->assign( 'selected', 0 );

				if ( $_POST['user_search'] != '' )
				{

					$conditional['last_name'] = array( $_POST['user_search'] );

					/*
					$query_options['conditional'] .= '(users.username LIKE "%' . $_POST['user_search'] . '%"'
					                                .' OR CONCAT( users_info.first_name, " ", users_info.last_name) LIKE "%' . $_POST['user_search'] . '%")';
					*/
				}
			}

			// Get all the users.
			$users = Legato_Resource::order_by( 'last_name' )->fetch( 'User', $conditional );

			if ( $users )
				$this->assign( 'users', $users );

			$this->layout->title = 'Manage Users';
			$this->assign( 'page', 'users' );
			$this->assign( 'advisor_id', $advisor_id );

			// Did we perform an action?
			if ( $message != '' )
				$this->assign( 'action', $message );

			$this->render_view( 'users/index' );

		}


		public function view( $user_id )
		{

			$this->layout->title = 'View User';
			$this->assign( 'page', 'users_view' );

			$user = new User( $user_id );

			// If an advisor tries to view a user that is not his client redirect to the home page
			if ( ( $this->level < 2 ) && ( $user->get('advisor_id') != $GLOBALS['admin']->get('advisor_id') ) )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->assign( 'user', $user );

			$this->render_view( 'users/view' );

		}


		public function add( $advisor_id )
		{

            if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->layout->title = 'Add User';
			$this->assign( 'page', 'users_add' );

			$form = new UserForm( 'add', NULL, $advisor_id );

			// Does it validate?
			if ( !$form->validate( 'add' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'users/add' );

			}
			else
			{

				$user_data = $form->values();

				// Create the encryption object.
				$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $user_data['username'] ), 'twofish' );

				// Set the passwords.
				$user_data['password'] = md5( $user_data['password'] );

				if ( $user_data['mycareer_password'] != '' )
					$user_data['mycareer_password'] = $encryption->encrypt( $user_data['mycareer_password'] );

				if ( $user_data['testgear_password'] != '' )
					$user_data['testgear_password'] = $encryption->encrypt( $user_data['testgear_password'] );

				$user_data['birth_date'] = strtotime( $user_data['birth_date_month'] . '/' . $user_data['birth_date_day'] . '/' . $user_data['birth_date_year'] );

				Legato_Resource::create( 'User', $user_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/users/' . $user_data['advisor_id'] . '/added' );
				return;

			}

		}

		public function edit( $user_id )
		{

            if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$user = new User( $user_id );

			$this->layout->title = 'Edit User';
			$this->assign( 'page', 'users_edit' );
			$this->assign( 'user', $user );

			$form = new UserForm( 'edit', $user );

			// Does it validate?
			if ( !$form->validate( 'edit' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'users/edit' );

			}
			else
			{

				$user_data = $form->values();

				// Create the encryption object.
				$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $user_data['username'] ), 'twofish' );

				// Normal password.
				if ( $user_data['password'] == '' )
					unset( $user_data['password'] );
				else
					$user_data['password'] = md5( $user_data['password'] );

				// Mycareer password.
				if ( $user_data['mycareer_password'] != '' )
					$user_data['mycareer_password'] = $encryption->encrypt( $user_data['mycareer_password'] );

				// Testgear password.
				if ( $user_data['testgear_password'] != '' )
					$user_data['testgear_password'] = $encryption->encrypt( $user_data['testgear_password'] );

				$user_data['birth_date'] = strtotime( $user_data['birth_date_month'] . '/' . $user_data['birth_date_day'] . '/' . $user_data['birth_date_year'] );

				Legato_Resource::update( $user, $user_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/users/' . $user_data['advisor_id'] . '/edited' );
				return;

			}

		}


		public function delete()
		{

			Legato_Settings::set( 'stage', 'show_layout', false );

			if ( $this->level < 2 )
				return;

			// Remove the user.
			if ( $_POST['id'] )
				Legato_Resource::delete( 'User', $_POST['id'] );

		}

	}

?>