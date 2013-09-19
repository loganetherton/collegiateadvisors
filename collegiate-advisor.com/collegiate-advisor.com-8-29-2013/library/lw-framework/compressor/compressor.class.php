<?php

	/**
	 * @package Compressor
	 * @author David DeCarmine
	 * @copyright Copyright (c) 2008, David DeCarmine
	 */
	 
	/**
	 * Allows you to include multiple JS files into one file and compresses them
	 * using gzip.
	 */
	class LW_Compressor
	{
		
		
		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $urls           = array();
		private static $output_buffer  = '';
		
		
		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------ 
		/**
		 * Private so that the class can't be instantiated.
		 */
		private function __construct()
		{
		  
			/* Do Nothing */
		
		}
		
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------ 		
		/**
		* Gets the URL to use to access the compressor for a particular package.
		*
		* @param string $package The package that you'd like to get the URL for.
		* @return string The URL to access the compressor.
		*/
		public static function get_url( $package )
		{
			
			session_start();
			
			// Do we have a URL stored yet?
			if ( !self::$urls[$package] )
			{
				
				$url = LW_Settings::get( 'compressor', $package . '.url' );
				$base = LW_Settings::get( 'compressor', 'base_path' );
				
				if ( $url )
					self::$urls[$package] = SITE_URL . $url;
				else if ( $base )
					self::$urls[$package] = SITE_URL . $base . '/' . strtolower( $package );
				else
					self::$urls[$package] = SITE_URL . '/compressor/' . strtolower( $package );
				
			}
				
			// Store it in the session variable.
			$_SESSION['LW_compressor_urls'][$package] = self::$urls[$package];
			$_SESSION['LW_compressor_packages'][self::$urls[$package]] = $package;
				
			// Return the url for this package.
			return self::$urls[$package];
			
		}
		
		
		/**
		* Clears the items from the session variables for a particular package.
		*
		* @param string $package The package that you'd like to clear.
		*/
		public static function clear( $package )
		{
			
			// Start the session if it hasn't yet been started.
			if ( isset( $_SESSION ) )
			{
				unset( $_SESSION['LW_compressor_urls'][$package] );
				unset( $_SESSION['LW_compressor_packages'][$package] );	
			}
			
		}
		
		
		/**
		* Includes and compresses all the items and outputs it. Also clears out
		* the items that should be outputted.
		*
		* @param string $package The package of the items you're outputting.
		*/
		public static function output( $package = '' )
		{
			
			$encoding = false;
			
			// Only proceed if compressor is enabled.
			if ( !LW_Settings::get( 'compressor', 'enable' ) )
				return;
			
			// Start the session.
			session_start();
				
			// If a URL was passed in, get the package.
			if ( !$package )
				$package = $_SESSION['LW_compressor_packages'][$_SERVER['REQUEST_URI']];
			
			// Turn off header and footer.
			LW_Settings::set( 'stage', 'show_header_footer', false );
			LW_Settings::set( 'debugger', 'reporting_on', false );
			
			// Get the encoding types.
			$enc_types = explode( ',', preg_replace( '/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'] ) );
			
			// Make sure we can encode.
			if ( (in_array( 'x-gzip', $enc_types ) || in_array( 'gzip', $enc_types )) && function_exists( 'ob_gzhandler' ) && !ini_get( 'zlib.output_compression' ) )
				$encoding = in_array( 'x-gzip', $enc_types ) ? 'x-gzip' : 'gzip';
			
			// Let's set the correct headers.
			$type = self::get_package_type( $package );
			if ( $type == 'js' )
				header( 'Content-Type: text/javascript' );
			else if ( $type == 'css' )
				header( 'Content-Type: text/css' );
			
			header( 'Pragma: private' );
			header( 'Cache-Control: private, max-age=' . LW_Settings::get( 'compressor', 'cache_max_age' ) );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + LW_Settings::get( 'compressor', 'cache_max_age' ) ) . ' GMT' );
			header( 'Vary: Accept-Encoding' );

			// Should we gzip it or not?
			if ( $encoding == false || !LW_Settings::get( 'compressor', 'enable_compression' ) )
			{
				
				// Cache?
				if ( LW_Settings::get( 'compressor', 'enable_caching' ) )
				{
					
					// Get the key for the cache.
					$key = str_replace( '/', '_', $_SESSION['LW_compressor_urls'][$package] );
					
					// Create a new cache object.
					$cache = new LW_Cache( array( 'handler' => LW_Settings::get( 'compressor', 'cache_handler' ), 
					                              'namespace' => 'LW_Compressor', 
												  'ttl' => LW_Settings::get( 'compressor', 'cache_max_age' ) ) );
					
					// Try to get the cache.
					self::$output_buffer = $cache->get( $key );
									
					if ( !self::$output_buffer )
					{
						// If no item in the cache, create and store it.
						self::store_items( $package );			
						$cache->set( $key, self::$output_buffer );
					}
					
				}
				else
					self::store_items( $package );
							
			}  // End if not compressed.
			else
			{
				
				// Cache?
				if ( LW_Settings::get( 'compressor', 'enable_caching' ) )
				{
					
					// Get the key for the cache.
					$key = str_replace( '/', '_', $_SESSION['LW_compressor_urls'][$package] . 'gz' );
					
					// Create a new cache object.
					$cache = new LW_Cache( array( 'handler' => LW_Settings::get( 'compressor', 'cache_handler' ), 
					                              'namespace' => 'LW_Compressor', 
												  'ttl' => LW_Settings::get( 'compressor', 'cache_max_age' ) ) );
					
					// Try to get the cache.
					self::$output_buffer = $cache->get( $key );
					
					if ( !self::$output_buffer )
					{
						// If no item in the cache, create and store it.
						self::store_items( $package );						
						self::$output_buffer = gzencode( self::$output_buffer, LW_Settings::get( 'compressor', 'compression_level' ), FORCE_GZIP );
						
						$cache->set( $key, self::$output_buffer );
					}
					
				}
				else
				{				
					self::store_items( $package );					
					self::$output_buffer = gzencode( self::$output_buffer, LW_Settings::get( 'compressor', 'compression_level' ), FORCE_GZIP );					
				}
				
				// Set the correct content encoding.
				header( 'Content-Encoding: ' . $encoding );	
				
			}  // End if compressed.
			
			// Output the data.
			echo self::$output_buffer;
			
			// Clear the items in the session.
			self::clear( $package );
			
		}
		
		
		//------------------------------------------------------------------------
		// Private Static Member Functions
		//------------------------------------------------------------------------ 
		/**
		* Gets the items from the settings and from the session variable and
		* returns it.
		*
		* @param string $package The package of the items your storing.
		* @return array Each item in the array is the URL for the file.
		*/
		public static function store_items( $package )
		{
			
			$files = explode( ',', LW_Settings::get( 'compressor', $package ) );
			
			foreach ( $files as $file )
				self::store_file( LW_Settings::get( 'compressor', 'include_folder' ) . trim( $file ) );
			
		}
		
		
		/**
		* Returns the type of files that are in a certain package.
		*
		* @param string $package The package you're trying to get a type for.
		* @return string The type of this package.
		*/
		public static function get_package_type( $package )
		{
			
			// Get the items for this package.
			$items = explode( ',', LW_Settings::get( 'compressor', $package ) );
				
			// Now check.
			if ( strpos( $items[0], '.js' ) !== false )
				return 'js';
			else if ( strpos( $items[0], '.css' ) !== false )
				return 'css';
			
		}
		
		
		/**
		* Stores a file in this classes output buffer.
		*
		* @param string $file The file to store.
		*/
		public static function store_file( $file )
		{
			
			$full_filename = ROOT . $file;
			
			// Make sure it's a real file.
			if ( !is_file ( $full_filename ) )
				return;
				
			// Prepend the file to our output buffer.
			self::$output_buffer .= file_get_contents( $full_filename );
			
		}
			
	}
	
	
	//----------------------------------------------------------------------------
	// Configuration Settings
	//----------------------------------------------------------------------------
	$data['enable'] = true;
	$data['enable_compression'] = true;
	$data['enable_caching'] = true;
	$data['cache_handler'] = 'file';
	$data['compression_level'] = 9;
	$data['cache_max_age'] = 2592000;
	$data['include_folder'] = '';
	$data['base_path'] = '';
	
	LW_Settings::set_default( 'compressor', $data );

?>
