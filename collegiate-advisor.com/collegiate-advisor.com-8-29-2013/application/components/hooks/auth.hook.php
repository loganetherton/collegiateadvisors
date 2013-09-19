<?php

	class AuthHook extends Legato_Hook
	{
		
		public function pre_system()
		{
			$namespace = false;
			$server = explode( '.', $_SERVER['HTTP_HOST'] );
			if( sizeof( $server ) >= 4 )
			{
				$namespace = $server[0] != 'www' ? $server[0] : $server[1];
			}
			elseif( sizeof( $server ) >= 3)
			{
				$namespace = $server[0] != 'www' ? $server[0] : false;
			}
			
			// If there is no namespace, stop.
			if ( !$namespace )
				die( 'Error 1032: No user specified' );
			
			// Globals.
			$GLOBALS['advisor'] = new Advisor( array( 'namespace' => $namespace ) );
			$GLOBALS['user'] = false;
			
			// Make sure the advisor is a real advisor and that the advisor is not disabled.
			if ( !$GLOBALS['advisor']->get( 'status' ) )
				die( 'Error 1033: No user ' . $namespace );
		
			// Create new cookie.			
			$auth = new Legato_Authentication();
			
			// Validate.
			if ( $auth->validate() )
			{
			
				$id = $auth->get_userid();
			
				// If everything went okay, then get the user.
				$GLOBALS['user'] = new User( $id );
				
				// If they have a mycareer login, set mycareer to true
				$GLOBALS['mycareer'] = ( $GLOBALS['user']->get( 'mycareer_username' ) == '' ) ? false : true;
				
				// Set the cookie again.
				$auth->set( $id );
				
			}
			
		}
		
	}