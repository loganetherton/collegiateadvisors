<?php
	 
	//----------------------------------------------------------------------------
	// Class: Legato_Mail
	// This class assists in sending emails.
	//----------------------------------------------------------------------------
	class Legato_Cookie
	{
		
		//------------------------------------------------------------------------
		// Private Member Variables
		//------------------------------------------------------------------------
		protected $_encrypt = true;
		protected $_encryption = null;
		protected $_glue = '|';
		
		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		protected static $_instance = null;
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------
		public function instance()
		{
		
			empty( self::$_instance ) && new Legato_Cookie;
			
			return self::$_instance;
			
		}
		
		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------ 
		
		//------------------------------------------------------------------------
		// Constructor: __construct()
		// Cannot be called.
		//------------------------------------------------------------------------
		public function __construct()
		{
			
			if ( empty( self::$_instance ) )
			{
				
				$this->_encrypt = Legato_Settings::get( 'cookie', 'encrypt' );
				$this->_glue = Legato_Settings::get( 'cookie', 'glue' );
				
				$this->_encryption = ( $this->_encrypt ) ? new Legato_Encryption( Legato_Settings::get( 'cookie', 'encryption_key' ) ) : null;

				self::$_instance = $this;
				
			}
			
		}
	
		//------------------------------------------------------------------------
		// Function: set()
		// Verifies whether the string is only alpha characters.
		//
		// Parameters:
		//     	$str - The string to validate.
		//------------------------------------------------------------------------
		public function set( $name, $value, $ttl = null, $path = null, $domain = null, $secure = null, $httponly = null )
		{
			
			// If the Headers were already sent, return
			if ( headers_sent() )
				return Legato_Debug_Debugger::add_item( 'Cookie could not be set because headers were already sent' );
				
			$value = $this->package( $value );
			
			// If the item was not set, use the settings value
			foreach ( array( 'ttl', 'path', 'domain', 'secure', 'httponly' ) as $item )
				$$item = ( isset( $$item ) ) ? $$item : Legato_Settings::get( 'cookie', $item );
			
			$ttl = ( $ttl == 0 ) ? 0 : time() + (int)$ttl;
			
			// ...then set it.
			return setcookie( $name, $value, $ttl, $path, $domain, $secure, $httponly );

		}
		
		//------------------------------------------------------------------------
		// Function: get()
		// Verifies whether the string is only alpha characters.
		//
		// Parameters:
		//     	$str - The string to validate.
		//------------------------------------------------------------------------
		public function get( $name )
		{
		
			return Legato_Input::cookie( $name );
			
		}
		
		//------------------------------------------------------------------------
		// Function: delete()
		// Verifies whether the string is only alpha characters.
		//
		// Parameters:
		//     	$str - The string to validate.
		//------------------------------------------------------------------------
		public function delete( $name, $path = null, $domain = null )
		{
			
			return $this->set( $name, '', 1, $path, $domain, false, false );
		
		}
		
		//------------------------------------------------------------------------
		// Function: package()
		// Verifies whether the string is only alpha characters.
		//
		// Parameters:
		//     	$str - The string to validate.
		//------------------------------------------------------------------------
		public function package( $parts )
		{
			
			// If we are deleting this cookie, just return.
			if ( !$parts )
				return '';
			
			// Glue it all up together.
			$cookie = implode( $this->_glue, $parts );
			
			// Encrypt the Cookie
			if ( $this->_encrypt )
				$cookie = $this->_encryption->encrypt( $cookie );
			
			// Return the encrypted/serialized data.
			return $cookie;
		
		}
		
		//------------------------------------------------------------------------
		// Function: unpackage()
		// Verifies whether the string is only alpha characters.
		//
		// Parameters:
		//     	$str - The string to validate.
		//------------------------------------------------------------------------
		public function unpackage( $cookie )
		{
		
			// If Encryption is set, Decrypt
			if ( $this->_encrypt )
				$cookie = $this->_encryption->decrypt( $cookie );
				
			return explode( $this->_glue, $cookie );
			
		}

	}