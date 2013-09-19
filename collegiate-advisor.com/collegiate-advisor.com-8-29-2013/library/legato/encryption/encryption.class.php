<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Encryption
	// Desc: An encryption engine. Contains functions to encrypt and decrypt
	//       text.
	//--------------------------------------------------------------------------
	class Legato_Encryption
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		private $_cypher; // The cypher algorithm.
		private $_mode; // The encryption mode.
		private $_td; // The TD for mcrypt.
		private $_private_key; // The private key.

		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: Class constructor.
		//------------------------------------------------------------------------
		public function __construct( $private_key, $cypher = 'blowfish', $mode = 'cfb' )
		{

			// Make sure everything was filled in.
			if ( $private_key == "" || $cypher == "" || $mode == "" )
			{
				Legato_Debug_Debugger::add_item( 'Invalid parameters for encryption. NULL passed in.' );
				return false;
			}

			// Assign the class variables to those passed in.
			$this->_cypher = $cypher;
			$this->_mode = $mode;

			// Get the TD.
			$this->_td = mcrypt_module_open( $this->_cypher, '', $this->_mode, '' );

			// Get the expected key size based on mode and cipher  .
			$expected_key_size = mcrypt_enc_get_key_size( $this->_td );

			// We dont need to know the real key, we just need to be able to confirm a hashed version.
			$this->_private_key = substr( md5($private_key), 0, $expected_key_size );

		}

		//------------------------------------------------------------------------
		// Name: encrypt()
		// Desc: Encrypts the plaint text passed in.
		//------------------------------------------------------------------------
		public function encrypt( $plaintext )
		{

			// Create the IV.
			$iv = mcrypt_create_iv( mcrypt_enc_get_iv_size($this->_td), MCRYPT_RAND );

			// Initialize the mcrypt engine.
			mcrypt_generic_init( $this->_td, $this->_private_key, $iv );

			// Encode/encrypt the text.
			$crypttext = base64_encode( mcrypt_generic($this->_td, $plaintext) );

			// Shut down mcrypt.
			mcrypt_generic_deinit( $this->_td );

			// Return the iv prefixed to the encrypted text.
			return $iv . $crypttext;

		}

		//------------------------------------------------------------------------
		// Name: decrypt()
		// Desc: Decrypts the encrypted text passed in.
		//------------------------------------------------------------------------
		public function decrypt( $crypttext )
		{

			// Get the iv from the beginning of the encrypted text.
			$iv_size = mcrypt_enc_get_iv_size( $this->_td );
			$iv = substr( $crypttext, 0, $iv_size );

			// Get the encrypted text.
			$crypttext = substr( $crypttext, $iv_size );
			$plaintext = '';

			// Attempt to decrypt the text.
			if ( $iv )
			{

				// Initialize the mcrypt engine.
				mcrypt_generic_init( $this->_td, $this->_private_key, $iv );

				// Decode the crypted text, then decrypt it, then trim it of whitespaces.
				$plaintext = trim( mdecrypt_generic($this->_td, base64_decode($crypttext)) );

				// Shut down mcrypt.
				mcrypt_generic_deinit( $this->_td );

			} // End if $iv true.

			// Return the plain text.
			return $plaintext;

		}

	}