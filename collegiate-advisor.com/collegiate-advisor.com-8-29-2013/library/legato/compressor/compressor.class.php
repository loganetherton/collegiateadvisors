<?php

	/*
		Class: Legato_Compressor
		Allows you to include multiple files (JavaScript or CSS) into one file and optionally
		compress them using gzip.
	*/
	class Legato_Compressor
	{
		
		/*	
			(Exclude)
			
			Var: $_urls
			The URLs of all the packages.
			
			Var: $_output_buffer
			The contents of all the compressed files.
		*/
		
		private static $_urls           = array();
		private static $_output_buffer  = '';
		
		
		/*
			Group: Functions
		*/
		
		/*
			(Exclude)
			Constructor: __construct()
			Static class.
		*/
		private function __construct()
		{
		  
			/* Do Nothing */
		
		}
		
		
		/*
			Function: get()
			Returns the URL to use to access the compressor for a particular package.
				
			Syntax:
				string get( string $package )
				
			Parameters:
				string $package - The package that you'd like to get the URL for.
				
			Returns:
				The URL for the package you entered.
										
			Examples:
			(begin code)
			 	// Including a package.
				<script type="text/javascript" src="<?php echo Legato_Compressor::get( 'JS_Top' ); ?>"></script>
				
				// Including files directly.
				<link rel="stylesheet" type="text/css" href="<?php echo Legato_Compressor::get( '/css/style.css,/css/pages.css' ); ?>" />			
			(end)
		*/
		public static function get( $package )
		{
			
			session_start();
			
			// Do we have a URL stored yet?
			if ( !self::$_urls[$package] )
			{
				
				// Get the section that the setting was defined in.
				$section = Legato_Settings::get_section( 'compressor', $package );
				
				$url = Legato_Settings::get( 'compressor', $package . '.url' );
				$base = Legato_Settings::get( 'compressor', 'base_path', $section );				
				$package_url = $package;
				
				// Check to see if this is a package or just some files passed in.
				// If so, hash the URL so it doesn't get too long.
				if ( !Legato_Settings::get( 'compressor', $package ) )
					$package_url = hash( 'md5', $package_url );
				
				if ( $url )
					self::$_urls[$package] = SITE_URL . $url;
				else if ( $base )
					self::$_urls[$package] = SITE_URL . $base . '/' . strtolower( $package_url );
				else
					self::$_urls[$package] = SITE_URL . '/compressor/' . strtolower( $package_url );
					
				// Now that we have the URL, get the version.
				self::$_urls[$package] .= '/' . self::_get_version( $package );
				
			}
				
			// Store it in the session variable.
			$_SESSION['Legato_compressor_urls'][$package] = self::$_urls[$package];
			$_SESSION['Legato_compressor_packages'][self::$_urls[$package]] = $package;
				
			// Return the url for this package.
			return self::$_urls[$package];
			
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
				unset( $_SESSION['Legato_compressor_urls'][$package] );
				unset( $_SESSION['Legato_compressor_packages'][$package] );	
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
			if ( !Legato_Settings::get( 'compressor', 'enable' ) )
				return;
			
			// Start the session.
			session_start();
				
			// If a URL was passed in, get the package.
			if ( !$package )
				$package = $_SESSION['Legato_compressor_packages'][$_SERVER['REQUEST_URI']];
				
			// Turn off header and footer.
			Legato_Settings::set( 'stage', 'show_layout', false );
			Legato_Settings::set( 'debugger', 'enable_reporting', false );
			
			// Get the version of this package.
			$package_version = Legato_Settings::get( 'compressor', $package . '.version' );
			$version = ($package_version) ? $package_version : Legato_Settings::get( 'compressor', 'version' );
			
			// Get the encoding types.
			$enc_types = explode( ',', preg_replace( '/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'] ) );
			
			// Make sure we can encode.
			if ( (in_array( 'x-gzip', $enc_types ) || in_array( 'gzip', $enc_types )) && function_exists( 'ob_gzhandler' ) && !ini_get( 'zlib.output_compression' ) )
				$encoding = in_array( 'x-gzip', $enc_types ) ? 'x-gzip' : 'gzip';
			
			// Let's set the correct headers.
			$type = self::_get_package_type( $package );
			if ( $type == 'js' )
				header( 'Content-Type: text/javascript' );
			else if ( $type == 'css' )
				header( 'Content-Type: text/css' );
			
			header( 'Pragma: private' );
			header( 'Cache-Control: private, max-age=' . Legato_Settings::get( 'compressor', 'cache_max_age' ) );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + Legato_Settings::get( 'compressor', 'cache_max_age' ) ) . ' GMT' );
			header( 'Vary: Accept-Encoding' );
			
			// Get the key for the cache.
			$key = str_replace( '/', '_', substr( $_SESSION['Legato_compressor_urls'][$package], 0, strrpos( $_SESSION['Legato_compressor_urls'][$package], '/' ) ) );

			// Should we gzip it or not?
			if ( $encoding == false || !Legato_Settings::get( 'compressor', 'enable_compression' ) )
			{
				
				// Cache?
				if ( Legato_Settings::get( 'compressor', 'enable_caching' ) )
				{
					
					// Create a new cache object.
					$cache = new Legato_Cache( array
					( 
						'handler' => Legato_Settings::get( 'compressor', 'cache_handler' ), 
					    'namespace' => 'Legato_Compressor', 
						'ttl' => Legato_Settings::get( 'compressor', 'cache_max_age' )
					) );
					
					// Try to get the cache.
					$buffer = $cache->get( $key );	
					
					// If the buffer was stored in the cache, check to make
					// sure that the versions are the same. If not, delete the
					// old cache file and get ready to make a new file in the cache.			
					if ( $buffer )
					{						
						$buffer = unserialize( $buffer );						
						if ( $buffer['version'] != $version )
						{
							$cache->delete( $key );
							$buffer = false;
						}					
					}
					
					if ( !$buffer )
					{
						// If no item in the cache, create and store it.
						self::_store_items( $package );			
						$cache->set( $key, serialize( array( 'version' => $version, 'output' => self::$_output_buffer ) ) );
					}
					else
						self::$_output_buffer = $buffer['output'];
					
				}
				else
					self::_store_items( $package );
							
			}  // End if not compressed.
			else
			{
				
				// Cache?
				if ( Legato_Settings::get( 'compressor', 'enable_caching' ) )
				{
					
					// Get the key for the cache.
					$key .= 'gz';
					
					// Create a new cache object.
					$cache = new Legato_Cache( array
					( 
						'handler' => Legato_Settings::get( 'compressor', 'cache_handler' ), 
					    'namespace' => 'Legato_Compressor', 
						'ttl' => Legato_Settings::get( 'compressor', 'cache_max_age' ) 
					) );
					
					// Try to get the cache.
					$buffer = $cache->get( $key );	
					
					// If the buffer was stored in the cache, check to make
					// sure that the versions are the same. If not, delete the
					// old cache file and get ready to make a new file in the cache.			
					if ( $buffer )
					{						
						$buffer = unserialize( $buffer );						
						if ( $buffer['version'] != $version )
						{
							$cache->delete( $key );
							$buffer = false;
						}					
					}
					
					if ( !$buffer )
					{
						// If no item in the cache, create and store it.
						self::_store_items( $package );						
						self::$_output_buffer = gzencode( self::$_output_buffer, Legato_Settings::get( 'compressor', 'compression_level' ), FORCE_GZIP );
						
						$cache->set( $key, serialize( array( 'version' => $version, 'output' => self::$_output_buffer ) ) );
					}
					else
						self::$_output_buffer = $buffer['output'];
					
				}
				else
				{				
					self::_store_items( $package );					
					self::$_output_buffer = gzencode( self::$_output_buffer, Legato_Settings::get( 'compressor', 'compression_level' ), FORCE_GZIP );					
				}
				
				// Set the correct content encoding.
				header( 'Content-Encoding: ' . $encoding );	
				
			}  // End if compressed.
			
			// Output the data.
			echo self::$_output_buffer;
			
			// Clear the items in the session.
			self::clear( $package );
			
		}
		
		
		/*
			Function: minify()
			Minifies the input passed in and returns it.
				
			Syntax:
				string minify( string $input )
				
			Parameters:
				string $input - The input string that you would like minified.
				
			Returns:
				The minified string.
										
			Examples:
				>	$minified = Legato_Compressor::minify( $js_code );
					
			See Also:
				- <Legato_Compressor::output()>
		*/
		public static function minify( $input )
		{
				
			include_once( LEGATO . '/packages/jsmin/jsmin.php' );
			
			return JSMin::minify( $input );
			
		}
		
		
		/**
		* Gets the items from the settings and from the session variable and
		* returns it.
		*
		* @param string $package The package of the items your storing.
		* @return array Each item in the array is the URL for the file.
		*/
		private static function _store_items( $package )
		{
			
			$section = Legato_Settings::get_section( 'compressor', $package );			
			$folder = Legato_Settings::get( 'stage', 'compressor_folder', $section );
			$package_folder = Legato_Settings::get( 'compressor', $package . '.folder', $section );
			
			if ( $package_folder )
				$folder = $package_folder;
			
			$files = explode( ',', Legato_Settings::get( 'compressor', $package ) );
			
			// This is if a file or multiple file was requested directly.
			if ( !$files[0] )
				$files = explode( ',', $package );
			
			foreach ( $files as $file )
				self::_store_file( $folder . trim( $file ) );
				
			// Should we minify?
			if ( self::_get_package_type( $package ) == 'js' && Legato_Settings::get( 'compressor', 'enable_minification' ) )
			{
				
				include_once( LEGATO . '/packages/jsmin/jsmin.php' );
				
				self::$_output_buffer = JSMin::minify( self::$_output_buffer );
				
			}
			
		}
		
		
		/**
		* Stores a file in this classes output buffer.
		*
		* @param string $file The file to store.
		*/
		private static function _store_file( $file )
		{
			
			$full_filename = ROOT . $file;
			
			// Make sure it's a real file.
			if ( !file_exists( $full_filename ) )
				return;
				
			// Prepend the file to our output buffer.
			self::$_output_buffer .= "\r\n" . file_get_contents( $full_filename );
			
		}
		
		
		/**
		* Returns the type of files that are in a certain package.
		*
		* @param string $package The package you're trying to get a type for.
		* @return string The type of this package.
		*/
		private static function _get_package_type( $package )
		{
			
			// Get the items for this package.
			$items = explode( ',', Legato_Settings::get( 'compressor', $package ) );
			
			// This is if a file or multiple file was requested directly.
			if ( !$items[0] )
				$items = explode( ',', $package );
				
			// Now check.
			if ( strpos( $items[0], '.js' ) !== false )
				return 'js';
			else if ( strpos( $items[0], '.css' ) !== false )
				return 'css';
			
		}
		
		
		/*
			(Exclude)
			Function: _get_version)
			Returns a version to use for a particular package so that the browser cache gets invalidated
			when it
		*/
		private static function _get_version( $package )
		{
			
			// Try to get the version.
			$main_version = Legato_Settings::get( 'compressor', 'version' );
			$package_version = Legato_Settings::get( 'compressor', $package . '.version' );
			
			// Any version?
			$version = $main_version ? $main_version : $package_version;
			
			// Try to automatically guess at a version.
			// Will end up being a timestamp of the last modified file in the package.
			if ( !$version )
			{
				
				$section = Legato_Settings::get_section( 'compressor', $package );	
				
				// Get the correct folder to include files from.
				$folder = Legato_Settings::get( 'stage', 'compressor_folder', $section );
				$package_folder = Legato_Settings::get( 'compressor', $package . '.folder' );
				
				if ( $package_folder )
					$folder = $package_folder;
				
				// Get the files in the package.
				$files = explode( ',', Legato_Settings::get( 'compressor', $package ) );
				
				// Loop through and see if the last modified file time is greater than the one we have.
				foreach ( $files as $file )
				{
					$file = ROOT . $folder . trim( $file );
					$last_modified = filemtime( $file );					
					$version = ($last_modified > $version) ? $last_modified : $version;
				}
				
			}
			
			return $version;
			
		}
			
	}