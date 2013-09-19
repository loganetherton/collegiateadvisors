<?php

	class IndexController extends Legato_Controller
	{
		
		public $level;

		public function __construct()
		{
			
			parent::__construct();
			
			if ( $GLOBALS['admin'] == true )
				$this->level = $GLOBALS['admin']->get( 'level' );

			$this->assign( 'level', $this->level );
			
		}
		

		public function index()
		{ 
			
			if ( $GLOBALS['admin'] == false )
			{
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;
			}
			
			$this->layout->title = '';
			$this->assign( 'page', 'index' );
			$this->assign( 'tutorials_view', new Legato_View( 'tutorials' ) );
			
			$this->render_view( 'index' );
			
		}
		
		
		public function tutorials()
		{ 
			
			if ( $GLOBALS['admin'] == false )
			{
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;
			}
			
			$this->layout->title = 'Tutorials';
			$this->assign( 'page', 'tutorials' );
			
			$this->render_view( 'tutorials' );
			
		}
		
		
		public function login( $error = false )
		{
			
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			
			// If they are logged in, redirect them.
			if ( $GLOBALS['admin'] != false )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}
			
			$cookie = new Legato_Authentication();
			
			$form = new LoginForm();
			
			// Does it validate?
			if ( $form->validate() )
			{
				
				// Set the cookie.
				$cookie->set( $GLOBALS['admin_id'] );
				
				// Store the last logged time.
				$admin = new Admin( $GLOBALS['admin_id'] );
				$admin->set_last_login();
				
				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin' );
				exit();
				
			}
			
			$this->layout->title = 'Login';
			$this->assign( 'form', $form );	
			$this->assign( 'page', 'login' );
			if( $error )
			{
				$error_messages = array(
										1 => 'Improperly formatted URL',
										2 => 'Improperly formatted URL',
										3 => 'Improperly hashed user id',
										4 => 'No user found with this email address'
									);
				$this->assign( 'error_message', $error_messages[$error] );
			}
			
			$this->render_view( 'login' );
			
		}
		
		
		public function logout()
		{
			
			$this->layout->title = 'Logout';
			$this->assign( 'page', 'logout' );
			
			// Log out.
			$cookie = new Legato_Authentication();
			$cookie->logout();
			
			// If this is an advisor, redirect them to their page.
			if ( $GLOBALS['admin'] && $GLOBALS['admin']->get( 'advisor_id' ) != 0 )
			{
				
				// Redirect them.
				header( 'Location: http://' . $GLOBALS['admin']->get( 'advisor' )->get( 'namespace' ) . '.' . MAIN_DOMAIN . '/logout' );
				return;
				
			}
			
			// Set the admin global to false so that the 
			// header knows that we are logged out.
			$GLOBALS['admin'] = false;
			
			$this->render_view( 'logout' );
			
		}

	}

?>