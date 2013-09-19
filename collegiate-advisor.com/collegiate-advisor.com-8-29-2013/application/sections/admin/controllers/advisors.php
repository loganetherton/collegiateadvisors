<?php

	class AdvisorsController extends Legato_Controller
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

			if ( $this->level < 2 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// Get all the advisors.
			$advisors = Legato_Resource::order_by( 'last_name' )->fetch( 'Advisor' );

			$this->layout->title = 'Manage Advisors';
			$this->assign( 'page', 'advisors' );
			$this->assign( 'advisors', $advisors );

			// Did we perform an action?
			if ( $message != '' )
				$this->assign( 'action', $message );

			$this->render_view( 'advisors/index' );

		}

		public function view( $advisor_id )
		{

            if ( $this->level < 2 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$advisor = new Advisor( $advisor_id );

			$this->layout->title = 'View Advisor';
			$this->assign( 'page', 'advisors_view' );
			$this->assign( 'advisor', $advisor );

			$this->render_view( 'advisors/view' );

		}

		public function add()
		{

            if ( $this->level < 3 )
            {
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->layout->title = 'Add Advisor';
			$this->assign( 'page', 'add' );

			$form = new AdvisorForm( 'add' );

			// Does it validate?
			if ( !$form->validate( 'add' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'advisors/add' );

			}
			else
			{

				$advisor_data = $form->values();

				// Format the plugins correctly.
				foreach ($advisor_data['plugins'] as $plugin)
					$plugins[$plugin] = true;

				$plugins['index'] = $advisor_data['index_plugin'];

				$advisor_data['plugins'] = $plugins;

				// If no style ID was passed in, create a new style and
				// assign it.
				if ( $advisor_data['style_id'] == '' )
					$advisor_data['style_id'] = Legato_Resource::create( 'Style' );

				// Format the start date.
				$advisor_data['start_date'] = mktime( 0, 0, 0, ($advisor_data['start_date_month'] + 1), $advisor_data['start_date_day'], $advisor_data['start_date_year'] );

				// Make sure they're set as active.
				$advisor_data['status'] = 1;

				// Create the advisor.
				$advisor_id = Legato_Resource::create( 'Advisor', $advisor_data );

				// Gather in all the admin data and create the admin for this advisor.
				$admin_data['username'] = $advisor_data['username'];
				$admin_data['password'] = md5( $advisor_data['password'] );

				$admin_data['advisor_id'] = $advisor_id;
				$admin_data['level'] = 1;

				Legato_Resource::create( 'Admin', $admin_data );

				// Let's start creating the test user that this advisor will be using.
				$user_data['advisor_id'] = $advisor_id;

				$user_data['first_name'] = $advisor_data['first_name'];
				$user_data['last_name'] = $advisor_data['last_name'];
				$user_data['phone_number'] = $advisor_data['phone_number'];
				$user_data['email_address'] = $advisor_data['email_address'];
				$user_data['address'] = $advisor_data['address'];
				$user_data['city'] = $advisor_data['city'];
				$user_data['state'] = $advisor_data['state'];
				$user_data['zip'] = $advisor_data['zip'];

				$user_data['testgear_username'] = $user_data['username'] = strtolower( $advisor_data['first_name'][0] . $advisor_data['last_name'] . 1 );
				$user_data['testgear_password'] = $user_data['password'] = strtolower( $advisor_data['last_name'] . $advisor_data['first_name'][0] . 1 );

				// Let's hash and encrypt the passwords.
				$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $user_data['username'] ), 'twofish' );

				$user_data['password'] = md5( $user_data['password'] );
				$user_data['testgear_password'] = $encryption->encrypt( $user_data['testgear_password'] );

				// Create the test user in the database.
				$test_user_id = Legato_Resource::create( 'User', $user_data );

				// Now let's attach the test user we just created to the advisor.
				if ( $advisor_id )
					Legato_Resource::update( 'Advisor', $advisor_id, array( 'test_user_id' => $test_user_id ) );

				// Create the Directories for the New Advisor
				mkdir( ROOT . '/application/views/ext/' . $advisor_id );
				mkdir( SITE_ROOT . '/advisor_files/' . $advisor_id );
				mkdir( ROOT . '/private_files/advisors/' . $advisor_id );
				mkdir( ROOT . '/private_files/advisors/' . $advisor_id . '/essays' );

				// Set the correct permissions.
				chmod( ROOT . '/application/views/ext/' . $advisor_id, 0775 );
				chmod( SITE_ROOT . '/advisor_files/' . $advisor_id, 0775 );
				chmod( ROOT . '/private_files/advisors/' . $advisor_id, 0775 );
				chmod( ROOT . '/private_files/advisors/' . $advisor_id . '/essays', 0775 );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/advisors/added' );
				return;

			}

		}

		public function edit( $advisor_id = 0 )
		{

            if ( $this->level < 2 )
			{
                $advisor = new Advisor( $GLOBALS['admin']->get( 'advisor_id' ) );
			}
			else
			{
				$advisor = new Advisor( $advisor_id );
				$advisor_admin = new Admin( array( 'advisor_id' => $advisor_id ) );
			}

			$this->layout->title = 'Edit Advisor';
			$this->assign( 'page', 'edit' );

			$form = new AdvisorForm( 'edit', $advisor, $advisor_admin );

			// Does it validate?
			if ( !$form->validate( 'edit' ) )
			{

				$this->assign( 'form', $form );

				$this->render_view( 'advisors/edit' );

			}
			else
			{

				$advisor_data = $form->values();

				if ( $this->level > 1 )
				{

					if ( $this->level > 2 )
					{

						foreach ( $advisor_data['plugins'] as $plugin )
							$plugins[$plugin] = true;

						$plugins['index'] = $advisor_data['index_plugin'];

						$advisor_data['plugins'] = $plugins;

						$advisor_data['start_date'] = mktime( 0, 0, 0, ($advisor_data['start_date_month'] + 1), $advisor_data['start_date_day'], $advisor_data['start_date_year'] );

					}

                    $admin_data['username'] = $advisor_data['username'];

					if ( $advisor_data['password'] != '' )
						$admin_data['password'] = md5( $advisor_data['password'] );

					$advisor_admin->update( $admin_data );
				}

				$advisor->update( $advisor_data );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/advisors/edited' );
				return;

			}

		}


		public function disable( $advisor_id )
		{

			if ( $this->level < 2 || !$advisor_id )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// Set the advisors status to disabled.
			$advisor_data['status'] = 0;

			// Update the advisor with the data.
			if ( $advisor_id )
				Legato_Resource::update( 'Advisor', $advisor_id, $advisor_data );

			// Redirect the user to the appropriate page.
			if ( strpos( $_SERVER['HTTP_REFERER'], 'admin/advisors' ) !== false )
			{
				header( 'Location: ' . SITE_URL . '/admin/advisors/disabled' );
				return;
			}
			else if ( strpos( $_SERVER['HTTP_REFERER'], 'admin/financial' ) !== false )
			{
				header( 'Location: ' . SITE_URL . '/admin/financial/disabled' );
				return;
			}
			else
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

		}


		public function enable( $advisor_id )
		{

			if ( $this->level < 2 || !$advisor_id )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			// Set the advisors status to active.
			$advisor_data['status'] = 1;

			// Update the advisor with the data.
			if ( $advisor_id )
				Legato_Resource::update( 'Advisor', $advisor_id, $advisor_data );


			// Redirect the user to the appropriate page.
			if ( strpos( $_SERVER['HTTP_REFERER'], 'admin/advisors' ) )
			{
				header( 'Location: ' . SITE_URL . '/admin/advisors/enabled' );
				return;
			}
			else if ( strpos( $_SERVER['HTTP_REFERER'], 'admin/financial' ) )
			{
				header( 'Location: ' . SITE_URL . '/admin/financial/enabled' );
				return;
			}
			else
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

		}

	}

?>