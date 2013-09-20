<?php

	class IndexController extends Legato_Controller
	{

		private $plugins = array();


		public function __construct()
		{

			parent::__construct();

			$this->plugins = $GLOBALS['advisor']->get( 'plugins' );
			$this->assign( 'plugins', $plugins );

		}


		public function _layout()
		{

			$data['section'] = $this->request['controller'];
			$data['plugins'] = $this->plugins;

			$this->render_view( 'layout', $data );

		}


		public function index()
		{

			if ( $this->plugins['index'] == '0' )
			{
				$this->services();
				return;
			}

			$this->assign( 'page', 'index' );
			$this->assign( 'index_view', new Legato_View( 'ext/' . $GLOBALS['advisor']->get( 'id' ) . '/index' ) );
			$this->assign( 'news_view', new Legato_View( 'news', array( 'news' => Legato_Resource::order_by( 'date', 'desc' )->limit( 4 )->fetch( 'News' ) ) ) );

			$this->render_view( 'index' . $this->plugins['index'] );

		}


		public function services()
		{

			$this->layout->title = 'Services We Offer - ';
			$this->assign( 'page', 'services' );

			$this->render_view( 'services' );

		}


		public function about()
		{

			if ( $this->plugins['about'] == false )
			{
				$this->index();
				return;
			}

			$this->layout->title = 'About ';
			$this->assign( 'page', 'about' );

			$this->render_view( 'about' );

		}
		
		public function pro_efc()
		{

			$this->assign( 'page', 'pro_efc' );
			
			$this->layout->title = 'Expected Family Contribution';
			
			$this->render_view( 'pro_efc' );
		}


		public function contact()
		{

			if ( $this->plugins['contact'] == false )
			{
				$this->index();
				return;
			}

			$this->layout->title = 'Contact ';
			$this->assign( 'page', 'contact' );

			$this->assign( 'contact_view', new Legato_View( 'ext/' . $GLOBALS['advisor']->get( 'id' ) . '/contact' ) );

			$this->render_view( 'contact' );

		}


		public function user_signup()
		{

			$this->layout->title = 'Sign Up - ';
			$this->assign( 'page', 'signup' );

			$this->render_view( 'signup' );

		}


		public function terms_and_conditions()
		{

			$this->layout->title = 'Terms and Conditions - ';

			$this->render_view( 'terms_and_conditions' );

		}


		public function privacy_policy()
		{

			$this->layout->title = 'Privacy Policy - ';

			$this->render_view( 'privacy_policy' );

		}


		public function login( )
		{

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

			// If they are logged in, redirect them.
			if ( $GLOBALS['user'] != false )
			{
				header( 'Location: ' . SITE_URL . '/member' );
				return;
			}

			// Create new cookie.
			$auth = new Legato_Authentication();

			// Get the form.
			$form = new LoginForm();

			// Does it validate?
			if ( $form->validate() )
			{

				// Set the cookie.
				$auth->set( $GLOBALS['user_id'] );

				// Store the last logged time.
				$user = new User( $GLOBALS['user_id'] );
				$user->set_last_login();

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/member/' );
				exit();

			}

			$this->layout->title = 'Login to ';
			$this->assign( 'page', 'login' );
			$this->assign( 'form', $form );

			$this->render_view( 'login' );

		}


		public function logout()
		{

			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

			$this->layout->title = 'Successfully Logged Out of ';
			$this->assign( 'page', 'logout' );

			// Log out.
			$auth = new Legato_Authentication();
			$auth->logout();

			// Set the user global to false so that the header knows that we are logged out.
			$GLOBALS['user'] = false;

			$this->render_view( 'logout' );

		}


		public function recovery()
		{

			$form = new RecoveryForm();
			$this->assign( 'form', $form );

			if ( $form->validate() )
			{

				$user = new User( array( 'email_address' => $form->get_value( 'email_address' ) ) );

				// If user exists.
				if ( $user )
				{

					// Get the current time and build the hex.
					$time = time();
					$hex = md5( $user->get( 'email_address' ) . $time . rand() );

					// Get a database handle.
					$dbh = Legato_DB::get( 'Main' );

					// Get the user if he exists.
					$query = 'insert into users_login_recovery( user_id, time, hex )
					          values( ?, ?, ? )';
					$stmt = $dbh->prepare( $query );
					$stmt->execute( $user->get( 'id' ), $time, $hex );

					// Link for users to change their password.
					$link = DOMAIN . SITE_URL . '/change_password/' . $user->get( 'id' ) . '/' . $hex;

					// Let's send the email now.
					require( ROOT . '/library/swift/Swift.php' );
					require( ROOT . '/library/swift/Swift/Connection/SMTP.php' );

					// Create a new SMTP connection and connect.
					$conn = new Swift_Connection_SMTP( Legato_Settings::get( 'smtp', 'host' ) );
					$conn->setUsername( Legato_Settings::get( 'smtp', 'username' ) );
					$conn->setPassword( Legato_Settings::get( 'smtp', 'password' ) );

					$swift = new Swift( $conn );

					// Let's build the message.
					$message = new Swift_Message( $GLOBALS['advisor']->get( 'business_name' ) . ' - Username and Password Recovery' );

					//--------------------------------------------
					// Render this message's body.

					ob_start();

					$this->assign( 'username', $user->get( 'username' ) );
					$this->assign( 'link', $link );

					$this->render_view( 'emails/recovery' );

					$message_content = ob_get_clean();
					$text_message_content = str_replace( array( "\r\n", "\r", "\n" ), '', $message_content );
					$text_message_content = str_replace( '<br />', "\r\n", $text_message_content );
					$text_message_content = strip_tags( $text_message_content );

					// End rendering of message.
					//--------------------------------------------

					$message->attach( new Swift_Message_Part( $text_message_content ) );
					$message->attach( new Swift_Message_Part( $message_content, 'text/html' ) );

					// Send it out.
					$swift->send( $message, $form->get_value( 'email_address' ), $GLOBALS['advisor']->get( 'contact_email_address' ) );

					$this->assign( 'message', '<p>An email has been sent to '. $form->get_value( 'email_address' ) . '. Please follow the link provided in the email to change your password and be sure to do it within an hour or you will need to re-enter your email address.</p>' );
				}
				else
				{
					$form->add_error( 'email_address', '<p>That email address does not exist in our database.</p>' );
				}
			}

			$this->layout->title = 'Information Recovery - ';

			$this->render_view( 'recovery' );

		}


		public function change_password( $user_id = '', $hex = '' )
		{

			// If a hex number was passed
			if ( ($user_id != '' && $this->query_values[1] != '') || $GLOBALS['user'] == true )
			{
				// If User is Logged in use that ID else use the one passed in by query value
				$id = ($GLOBALS['user'] == true) ? $GLOBALS['user']->get( 'id' ) : $user_id;

				// Get the User
				$user = new User( $id );

				// If a valid user...
				if ( $user->get( 'username' ) != '' )
				{

					// Get the Users Recovery Info
					$info = $user->get_recovery_info();

					// If the entry exists and the hex is the same
					if ( $info['time'] != NULL && $hex == $info['hex'] )
					{
						// Longer then 1 Hour, must resubmit email address
						if ( time() - $info['time'] > 3600 )
						{

							// Assign a Message and Call the Recovery Action
							$this->assign( 'message', '<p>More than one hour has elapsed since you put in a request to change your password.<br />Please resubmit your email address and you will get a new confirmation email.</p>' );
							$this->recovery();

							return;
						}
					}
					else
					{
						// If entry does not exist or hex does not line up.
						header( 'Location: '. SITE_URL . '/login' );
						exit();
					}
				}
				else
				{
					// If entry does not exist or hex does not line up.
					header( 'Location: '. SITE_URL . '/login' );
					exit();
				}

				$form = new ChangePasswordForm( '/' . $user_id . '/' . $hex );
				$this->assign( 'form', $form );

				if ( $form->validate() )
				{
					Legato_Resource::update( $user, array( 'password' => md5( $form->get_value( 'password' ) ) ) );

					// Get a database handle.
					$dbh = Legato_DB::get( 'Main' );

					// Delete the login recovery.
					$query = 'delete from users_login_recovery where user_id = ?';
					$stmt = $dbh->prepare( $query );
					$stmt->execute( $user_id );

					$this->assign( 'message', '<p>Password Changed</p>' );
				}

				$this->layout->title = 'Change Password - ';

				$this->render_view( 'change_password' );
				return;
			}

			header( 'Location: '. SITE_URL . '/login' );

		}


		public function view_pdf( $pdf_filename )
		{

			Legato_Settings::set( 'stage', 'show_layout', false );

			$filename = SITE_ROOT . '/files/' . $pdf_filename . '.pdf';

			// Only show it if the file exists.
			if ( file_exists($filename) )
			{

				$buffer = file_get_contents( $filename );

				header( 'Pragma: public' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Type: application/pdf' );
				header( 'Content-Length: ' . strlen($buffer) );
				header( 'Content-Disposition: attachment; filename="' . $pdf_filename . '.pdf"' );

				flush();

				echo $buffer;

			}

		}


		public function avatar()
		{

			Legato_Settings::set( 'debugger', 'enable_reporting', false );
			Legato_Settings::set( 'stage', 'show_layout', false );

			$this->render_view( 'avatar' );

		}

	}

?>