<?php
	 
	/*
		Class: Legato_Authentication
		Contains the functionality you need to authenticate a cookie. Usually used for authenticating a user. 
	*/
	class Legato_Authentication
	{

		/*
			(Exclude)
		
			Var: $_userid
			Stores the current userid
			
			Var: $_version
			Stores the user's authentication version
			
			Var: $_cookie
			Stores the cookie object
		
		*/
		protected $_userid;      	// The user's ID.
		protected $_version;     	// Stores the user's authentication system version.
		protected $_cookie;			// The Cookie Object

		
		/*
			Constructor: __construct()
			Instantiates and stores the authentication storage object (cookie, session, database).
		
			Syntax:
				void __construct()
		
			Examples:
			>	$auth = new Legato_Authentication();
		*/
		public function __construct()
		{
			
			$this->_cookie = Legato_Cookie::instance();

		}

		/*
			Function: set()
			Sets the cookie.
		
			Syntax:
				void set( int $userid [, int $expiration = 0 ] )
		
			Parameters:
				$expiration - The timestamp that this cookie should expire on. Leave blank for it to expire at the end of the session (when the browser closes). 
							  If not blank it will be appended to time()
		
			Examples:
			>	$auth->set( 32, 3600 );
		*/
		public function set( $userid, $expiration = 0 )
		{
		
			// First package it up...
			$cookie = array( Legato_Settings::get( 'cookie', 'version' ), $userid );

			// ...then set it.
			$this->_cookie->set( Legato_Settings::get( 'cookie', 'name' ), $cookie, $expiration, Legato_Settings::get( 'cookie', 'path' ), Legato_Settings::get( 'cookie', 'domain' ) );

		}

		//------------------------------------------------------------------------
		// Function: validate()
		//
		// Makes sure that the cookie isn't malformed in any way (tampered with)
		// and that the versions match up.
		//
		// Returns:
		//     TRUE if the cookie validates and FALSE if it doesn't.
		//------------------------------------------------------------------------
		public function validate()
		{
			
			// Unpackage the cookie.
			if ( Legato_Input::cookie( Legato_Settings::get( 'cookie', 'name' ) ) )
				list( $this->_version, $this->_userid ) = $this->_cookie->unpackage( Legato_Input::cookie( Legato_Settings::get( 'cookie', 'name' ) ) );
			else
				return Legato_Debug_Debugger::add_item( 'No cookie.' );

			// Is the cookie malformed?
			//if ( !$this->_version || !$this->_userid )
				//return Legato_Debug_Debugger::add_item( 'Malformed cookie.' );

			// Is the version the same as the current one?
			if ( $this->_version != Legato_Settings::get( 'cookie', 'version' ) )
				return Legato_Debug_Debugger::add_item( 'Version mismatch.' );

			// Everything's good!
			return true;

		}

		//------------------------------------------------------------------------
		// Function: get_userid()
		//
		// Returns the user's ID stored in this cookie.
		//
		// Returns:
		//     The user's ID.
		//------------------------------------------------------------------------
		public function get_userid()
		{

			// Return the user's ID.
			return $this->_userid;

		}

		//------------------------------------------------------------------------
		// Function: logout()
		//
		// Just unsets (invalidates) the cookie.
		//------------------------------------------------------------------------
		public function logout()
		{

			// Delete the information in the cookie.
			$this->_cookie->delete( Legato_Settings::get( 'cookie', 'name' ), Legato_Settings::get( 'cookie', 'path' ), Legato_Settings::get( 'cookie', 'domain' ) );

		}

	}