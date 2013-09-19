<?php

	/*
		(Exclude)
		Class: Legato_Cache_HandlerFile
		The cache handler for a file system.
	*/
	class Legato_Cache_Handler_File implements Legato_iCache_Handler
	{		
		
		private $cache              = null;   // The cache object that is holding this handler.
		private $started            = false;  // Whether or not the cache has been started.
		private $current_key        = '';     // The current key for start and stop functionality.
		private $current_namespace  = '';     // The current namespace for start and stop functionality. 
		private $current_ttl        = '';     // The current time to live for start and stop functionality.

		public static $cache_data = array();  // The data retrieved from the cache.

		
		/*
			(Exclude)
			Constructor: __construct()
		*/
		public function __construct( $cache )
		{
			
			$this->cache = $cache;
			
		}
		
		
		/*
			(Exclude)
			Function: set()
		*/
		public function set( $key, $value, $namespace, $ttl )
		{
			
			// Get the correct caching directory.
			$cache_dir = $this->get_namespace_dir( $namespace );
				
			// Open a file pointer.
			$fp = fopen( $cache_dir . '/' . $key . '.cache', 'wb' );
			
			if ( !$fp )
			{
				fclose( $fp );
				return Legato_Debug_Debugger::add_item( 'Could not open cache file for writing.' );
			}
			
			// Write to the cache file.
			$ret = fwrite( $fp, $ttl . "\r\n" . time() . "\r\n" . $value );
			
			if ( !$ret )
			{
				fclose( $fp );
				return Legato_Debug_Debugger::add_item( 'Could not write to cache file.' );
			}
			
			// Store the item.
			self::$cache_data[$namespace][$key] = $value;
			
			// Success!
			fclose( $fp );
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: get()
		*/
		public function get( $key, $namespace )
		{
			
			// Do we already have it stored?
			if ( self::$cache_data[$namespace][$key] != '' && self::$cache_data[$namespace][$key] != false )
			{
				return self::$cache_data[$namespace][$key];
			}
			else
			{
				
				// Get the correct caching directory.
				$cache_dir = $this->get_namespace_dir( $namespace );
				
				// Go no further if the file doesn't exist.
				if ( !file_exists( $cache_dir . '/' . $key . '.cache' ) )
					return false;
				
				// Open a file pointer.
				$fp = fopen( $cache_dir . '/' . $key . '.cache', 'rb' );
				
				if ( !$fp )
				{
					Legato_Debug_Debugger::add_item( 'Could not open cache file for writing.' );
					return false;
				}
				
				// Get the information for this key.
				$ttl = str_replace( "\r\n", '', fgets( $fp ) );
				$time_created = str_replace( "\r\n", '', fgets( $fp ) );
				
				// Is it past its lifetime?
				if ( $ttl != '0' && time() - $time_created >= $ttl )
				{
					
					// Remove the file.
					fclose( $fp );
					unlink( $cache_dir . '/' . $key . '.cache' );
					
					// Clear out our cache.
					self::$cache_data[$namespace][$key] = '';
					
					// Return false.
					return false;
					
				}
				
				// Get the value.
				self::$cache_data[$namespace][$key] = fread( $fp, filesize( $cache_dir . '/' . $key . '.cache' ) );
				fclose( $fp );
				
				// Return it.
				return self::$cache_data[$namespace][$key];
				
			}
			
		}
		
		
		/*
			(Exclude)
			Function: start()
		*/
		public function start( $key, $namespace, $ttl )
		{
			
			// Started already?
			if ( $this->started )
			{
				Legato_Debug_Debugger::add_item( 'Cache has already been started. You must stop it first.' );
				return false;
			}
			
			$this->current_key = $key;
			$this->current_namespace = $namespace;
			$this->current_ttl = $ttl;
			
			$cache_data = $this->get( $key, $namespace );
			
			// Stored?
			if ( !$cache_data )
			{
				$this->started = true;
				ob_start();
				return false;
			}
			else
			{
				echo $cache_data;
				return true;
			}
			
			
		}
		
		
		/*
			(Exclude)
			Function: stop()
		*/
		public function stop()
		{
			
			// Has it been started?
			if ( !$this->started )
				return Legato_Debug_Debugger::add_item( 'The cache must be started before it can be stopped.' );
			
			$this->started = false;
			
			// Stop output buffering and store it.
			$buffer = ob_get_flush();
			
			$this->set( $this->current_key, $buffer, $this->current_namespace, $this->current_ttl );
			
		}
		
		
		/*
			(Exclude)
			Function: delete()
		*/
		public function delete( $key, $namespace )
		{
			
			// Remove it from our cache.
			self::$cache_data[$namespace][$key] = '';
			
			// Get the correct caching directory.
			$cache_dir = $this->get_namespace_dir( $namespace );
			
			// Go no further if the file doesn't exist.
			if ( !file_exists( $cache_dir . '/' . $key . '.cache' ) )
				return false;
				
			// Remove the file.
			unlink( $cache_dir . '/' . $key . '.cache' );
			
			// Return the item.
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: invalidate()
		*/
		public function invalidate( $namespace )
		{
			
			// Remove it from our cache.
			self::$cache_data[$namespace] = array();
			
			// Global namespace or user-defined?
			if ( $namespace == '' )
			{
				
				// Emtpy the directory.
				$this->empty_dir( $this->cache->cache_dir );
				
			}
			else
			{
				
				// Go no further if the folder doesn't exist.
				if ( !is_dir( $this->cache->cache_dir . '/' . $namespace ) )
					return false;
					
				// Emtpy the directory.
				$this->empty_dir( $this->cache->cache_dir . '/' . $namespace );
				
				// Remove the namespace's folder.
				rmdir( $this->cache->cache_dir . '/' . $namespace );
				
			}
			
			// Success!
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: clear()
		*/
		public function clear()
		{
			
			// Clear our cache.
			self::$cache_data = array();
			
			// Empty out the cache directory.
			$this->empty_dir( $cache_dir = $this->cache->cache_dir, true );
			
			// Success!
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: get_namespace_dir()
			Returns the namespace directory.
		*/
		public function get_namespace_dir( $namespace )
		{
			
			// Get the correct directory to use.
			if ( $namespace != '' )
				$cache_dir = $this->cache->cache_dir . '/' . $namespace;
			else
				$cache_dir = $this->cache->cache_dir;
			
			// Create the namespace if not created.
			if ( $namespace != '' && !is_dir( $cache_dir ) )
				mkdir( $cache_dir );
				
			// Return the cache dir.
			return $cache_dir;
			
		}
		
		
		/*
			(Exclude)
			Function: empty_dir()
			Empties the directory passed in.
		*/
		public function empty_dir( $directory, $recursive = false )
		{
			
			// Loop through all the files in the directory.
			foreach ( scandir( $directory ) as $item )
			{
				
                if ( !strcmp( $item, '.' ) || !strcmp( $item, '..' ) || (strpos( $item, '.' ) !== false && strpos( $item, '.' ) == 0) )
                    continue;
                    
                if ( file_exists( $directory . '/' . $item ) )
                {
                	unlink( $directory . '/' . $item );
                }
                else if ( $recursive && is_dir( $directory . '/' . $item ) )
                {
					$this->empty_dir( $directory . '/' . $item, true );
					rmdir( $directory . '/' . $item );
				}
                
            }
			
		}

	}