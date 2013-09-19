<?php

	/*
		(Exclude)
		Class: Legato_Cache_HandlerAPC
		The cache handler for APC.
	*/
	class Legato_Cache_Handler_APC implements Legato_iCache_Handler
	{		
		
		private $cache              = null;   // The cache object that is holding this handler.
		private $started            = false;  // Whether or not the cache has been started.
		private $current_key        = '';     // The current key for start and stop functionality.
		private $current_namespace  = '';     // The current namespace for start and stop functionality. 
		private $current_ttl        = '';     // The current time to live for start and stop functionality.
		
		public static $namespace_keys  = array();  // The array of namespace keys.
		public static $cache_data      = array();  // The data retrieved from the cache.
		

		/*
			Construct: __construct()
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
			
			$namespace_key = $this->get_namespace_key( $namespace );
			
			// Store the item.
			$ret = apc_store( $namespace_key . $key, $value, $ttl );
			
			if ( !$ret )
				return false;
			 
			// Store the item in our cache.
			self::$cache_data[$namespace][$key] = $value;
			
			// Success!
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: get()
		*/
		public function get( $key, $namespace )
		{
			
			//Do we already have it stored?
			if ( self::$cache_data[$namespace][$key] != '' && self::$cache_data[$namespace][$key] != false )
			{
				return self::$cache_data[$namespace][$key];
			}
			else
			{
				$namespace_key = $this->get_namespace_key( $namespace );
				
				self::$cache_data[$namespace][$key] = apc_fetch( $namespace_key . $key );
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
			{
				Legato_Debug_Debugger::add_item( 'The cache must be started before it can be stopped.' );
				return false;
			}
			
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
			
			$namespace_key = $this->get_namespace_key( $namespace );
			
			// Remove it from our cache.
			self::$cache_data[$namespace][$key] = '';
			
			// Delete.
			return apc_delete( $namespace_key . $key );
			
		}
		
		
		/*
			(Exclude)
			Function: invalidate()
		*/
		public function invalidate( $namespace )
		{
			
			$namespace_key = '';
			
			// Store the new namespace key.
			$namespace_key = $this->generate_key();
			apc_store( 'nskey_' . $namespace, $namespace_key );
			
			// Update it in our array of namespace keys.
			self::$namespace_keys[$namespace] = $namespace_key;
			
			// Remove it from our cache.
			self::$cache_data[$namespace] = array();
			
			// Return it.
			return $namespace_key;
			
		}
		
		
		/*
			(Exclude)
			Function: clear()
		*/
		public function clear()
		{
			
			// Clear our cache.
			self::$cache_data = array();
			
			return apc_clear_cache( 'user' );
			
		}
		
		
		/*
			(Exclude)
			Function: get_namespace_key()
			Returns the stored namespace key, or creates a new one.
		*/
		private function get_namespace_key( $namespace )
		{
			
			$namespace_key = '';
			
			// Try to get the namespace key.
			if ( self::$namespace_keys[$namespace] != '' )
			{
				$namespace_key = self::$namespace_keys[$namespace];
			}
			else
			{
				$namespace_key = apc_fetch( 'nskey_' . $namespace );
				self::$namespace_keys[$namespace] = $namespace_key;
			}
			
			// Create a new one?
			if ( $namespace_key === false ) 
			{
				$namespace_key = $this->generate_key();
				apc_store( 'nskey_' . $namespace, $namespace_key );
				self::$namespace_keys[$namespace] = $namespace_key;
			}
			
			// Return the namespace key.
			return $namespace_key;
			
		}
		
		
		/*
			(Exclude)
			Function: generate_key()
			Generates and returns a namespace key.
		*/
		private function generate_key()
		{
			
			// Return the namespace key.
			return '(NS_' . md5( mt_rand() . '_' . mt_rand() ) .')';
			
		}

	}