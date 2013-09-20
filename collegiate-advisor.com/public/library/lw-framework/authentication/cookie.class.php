<?php
	 
	//----------------------------------------------------------------------------
	// Class: LW_Authentication_Cookie
	// Contains the functionality you need to authenticate a cookie. Usually used
	// for authenticating a user. 
	//----------------------------------------------------------------------------
	class LW_Authentication_Cookie
	{

		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $userid;      // The user's ID.
		private $version;     // Stores the user's authentication system version.
		private $encryption;  // The encryption engine class.

		private $cypher      = 'blowfish';       // The cypher used in encryption.
		private $mode        = 'cfb';            // The encryption mode.


		//------------------------------------------------------------------------
		// Public Functions
		//------------------------------------------------------------------------
		
		//------------------------------------------------------------------------
		// Constructor: __construct()
		//
		// The class constructor.
		//
		// Parameters:
		//     $userid - The ID of the user that you're authenticating.
		//------------------------------------------------------------------------
		public function __construct( $userid = false )
		{

			// Instantiate the encryption class.
			$this->encryption = new LW_Encryption( LW_Settings::get( 'cookie', 'private_key' ) );

			if ( $userid )
			{
				$this->userid = $userid;

			}  // End if user ID was put in.
			else
			{

				// Unpackage the cookie.
				if ( array_key_exists( LW_Settings::get( 'cookie', 'cookie_name' ), $_COOKIE ) )
					$this->unpackage( $_COOKIE[LW_Settings::get( 'cookie', 'cookie_name')] );
				else
					LW_Debug_Debugger::add_item( 'No cookie.' );

			}  // End if no user ID was put in.

		}


		//------------------------------------------------------------------------
		// Function: set()
		//
		// Sets the cookie.
		//
		// Parameters:
		//     $expiration - The timestamp that this cookie should expire on.
		//                   Leave blank for it to expire at the end of the
		//                   session (when the browser closes).
		//------------------------------------------------------------------------
		public function set( $expiration = 0 )
		{

			// First package it up...
			$cookie = $this->package();

			// ...then set it.
			setcookie( LW_Settings::get( 'cookie', 'cookie_name' ), $cookie, $expiration, LW_Settings::get( 'cookie', 'path' ), LW_Settings::get( 'cookie', 'domain' ) );

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

			// Is the cookie malformed?
			if ( !$this->version || !$this->userid )
			{
				LW_Debug_Debugger::add_item( 'Malformed cookie.' );
				return false;
			}

			// Is the version the same as the current one?
			if ( $this->version != LW_Settings::get( 'cookie', 'version' ) )
			{
				LW_Debug_Debugger::add_item( 'Version mismatch.' );
				return false;
			}

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
			return $this->userid;

		}


		//------------------------------------------------------------------------
		// Function: logout()
		//
		// Just unsets (invalidates) the cookie.
		//------------------------------------------------------------------------
		public function logout()
		{

			// Delete the information in the cookie.
			setcookie( LW_Settings::get( 'cookie', 'cookie_name' ), '', 1, LW_Settings::get( 'cookie', 'path' ), LW_Settings::get( 'cookie', 'domain' ) );

		}


		//------------------------------------------------------------------------
		// Private Functions
		//------------------------------------------------------------------------
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: package()
		//
		// Glues all the pieces of the cookie (all the information for it)
		// together and returns the encrypted cookie.
		//
		// Returns:
		//     The encrypted cookie string.
		//------------------------------------------------------------------------
		private function package()
		{

			// Make an array of the data.
			$parts = array( LW_Settings::get( 'cookie', 'version' ), $this->userid );

			// Glue it all up together.
			$cookie = implode( LW_Settings::get( 'cookie', 'glue' ), $parts );

			// Return the encrypted/serialized data.
			return $this->encryption->encrypt( $cookie );

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: unpackage()
		//
		// Unencrypts the encrypted cookie passed in and pulls all the pieces of
		// the encrypted cookie into this objects variables.
		//
		// Parameters:
		//     $cookie - The encrypted cookie.
		//------------------------------------------------------------------------
		private function unpackage( $cookie )
		{
			
			// Check for magic quotes.
			if ( get_magic_quotes_gpc() ) $cookie = stripslashes( $cookie );
			
			// Decrypt the cookie.
			$buffer = $this->encryption->decrypt( $cookie );

			// Explode the cookie.
			list( $this->version, $this->userid ) = explode( LW_Settings::get( 'cookie', 'glue' ), $buffer );

			// Make sure the cookie is not malformed.
			if ( $this->version != LW_Settings::get( 'cookie', 'version' ) || !$this->userid )
			{
				LW_Debug_Debugger::add_item( 'Malformed cookie.' );
				return false;
			}

		}

	}

?>
