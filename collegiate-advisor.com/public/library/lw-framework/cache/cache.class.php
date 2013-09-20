<?php

	//--------------------------------------------------------------------------
	// Class: LW_Cache
	// Manages a cache handler. Abstracts the handlers out so that you don't
	// have to worry what kind of cache handler you're using.
	//--------------------------------------------------------------------------
	class LW_Cache
	{

		//----------------------------------------------------------------------
		// Public Variables
		//----------------------------------------------------------------------
		public $cache_handler  = NULL;  // The cache handler used by this cache.
		public $namespace      = '';    // The default namespace for this cache.
		public $ttl            = '';    // The default time to live.
		public $cache_dir      = '';    // The default cache directory.


		//----------------------------------------------------------------------
		// Public Member Functions
		//----------------------------------------------------------------------
		
		//----------------------------------------------------------------------
		// Constructor: __construct()
		// 
		// The class constructor.
		//
		// Parameters:
		//     $options - An array of options that you can set.
		//----------------------------------------------------------------------
		public function __construct( $options = array() )
		{
			
			$cache_handler = '';
			
			// Store the cache handler.
			if ( $options['handler'] != '' )
				$cache_handler = $options['handler'];
			else
				$cache_handler = LW_Settings::get( 'cache', 'default_handler' );			
			
			// Create the handler.
			if ( $cache_handler == 'apc' )
				$this->cache_handler = new LW_Cache_HandlerAPC( $this );
			else if ( $cache_handler == 'memcache' )
				$this->cache_handler = new LW_Cache_HandlerMemcache( $this );
			else if ( $cache_handler == 'sqlite' )
				$this->cache_handler = new LW_Cache_HandlerSQLite( $this );
			else if ( $cache_handler == 'file' )
				$this->cache_handler = new LW_Cache_HandlerFile( $this );
			
			// Correct cache handler?
			if ( $this->cache_handler == NULL )
			{
				LW_Debug_Debugger::add_item( 'Incorrect handler: ' . $cache_handler . '.' );
				return false;
			}
			
			// Store the time to live.
			if ( $options['ttl'] != '' )
				$this->ttl = $options['ttl'];
			else
				$this->ttl = LW_Settings::get( 'cache', 'ttl' );
				
			// Store the cache directory.
			if ( $options['cache_dir'] != '' )
				$this->cache_dir = $options['cache_dir'];
			else
				$this->cache_dir = LW_Settings::get( 'cache', 'cache_dir' );
			
			// Store the namespace.
			if ( $options['namespace'] != '' )
				$this->namespace = $options['namespace'];
			
		}
		
		
		//----------------------------------------------------------------------
		// Function: set()
		// 
		// Sets an item in the cache. If it's already there, it will overwrite
		// it. If the item's not already there, it will add it.
		//
		// Parameters:
		//     $key - The key that you'd like to set.
		//     $value - The value that you'd like the item to have.
		//     $namespace - The namespace that you'd like to set to.
		//                  If you leave this blank, it will default to the
		//                  global namespace.
		//     $ttl - The time to live for this item.
		//----------------------------------------------------------------------
		public function set( $key, $value, $namespace = false, $ttl = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Get the correct time to live.
			if ( $ttl === false )
				$ttl = $this->ttl;				
			
			// Forward to the handler.
			return $this->cache_handler->set( $key, $value, $namespace, $ttl );
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: get()
		//
		// Returns an item from the cache. 
		//
		// Parameters:
		//     $key - The key of the item that you'd like to get.
		//     $namespace - The namespace that you'd like to get from. If you
		//                  leave this blank, it will default to the global
		//                  namespace.
		//------------------------------------------------------------------------
		public function get( $key, $namespace = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
			
			// Forward to the handler.
			return $this->cache_handler->get( $key, $namespace );
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: start()
		//
		// Starts caching a piece of code. If it finds a cached version already in
		// the cache, it will output that and return true. If not it will start
		// caching and return false.
		//
		// Parameters:
		//     $key - The key that you'd like to assign to this chunk of code.
		//     $namespace - The namespace you'd like this item to be put into. If
		//                  you leave this blank, it will default to the global
		//                  namespace.
		//     $ttl - The time to live for this item.
		//------------------------------------------------------------------------
		public function start( $key, $namespace = false, $ttl = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Get the correct time to live.
			if ( $ttl === false )
				$ttl = $this->ttl;
				
			// Forward to the handler.
			return $this->cache_handler->start( $key, $namespace, $ttl );
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: stop()
		//
		// Stops caching the piece of code and stores it in the cache.
		//------------------------------------------------------------------------
		public function stop()
		{
			
			// Foward to the handler.
			return $this->cache_handler->stop();
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: delete()
		//
		// Removes the item from the cache.
		//
		// Parameters:
		//     $key - The key of the item you'd like to remove.
		//     $namespace - The namespace you'd like to remove from. If you leave
		//                  this blank, it will default to the global namespace.
		//------------------------------------------------------------------------
		public function delete( $key, $namespace = '' )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Foward to the handler.
			return $this->cache_handler->delete( $key, $namespace );
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: invalidate()
		//
		// Invalidates all the items in a particular namespace.
		//
		// Parameters:
		//     $namespace - The namespace you'd like to clear out. If you leave
		//                  this blank, it will clear out the global namespace.
		//------------------------------------------------------------------------
		public function invalidate( $namespace = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Foward to the handler.
			return $this->cache_handler->invalidate( $namespace );
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: clear()
		//
		// Clears out the whole cache (all namespaces, including the global
		// namespace).
		//------------------------------------------------------------------------
		public function clear()
		{
			
			// Forward to the handler.
			return $this->cache_handler->clear();
			
		}

	}
	
	//----------------------------------------------------------------------------
	// Configuration Settings
	//----------------------------------------------------------------------------
	$data['enable'] = false;
	$data['enable_apc'] = true;
	$data['enable_memcache'] = true;
	$data['enable_sqlite'] = true;
	$data['enable_file'] = true;
	$data['default_handler'] = 'file';
	$data['ttl'] = 3600;
	$data['cache_dir'] = PATH_TO_LW_API . '/tmp/cache';
	
	LW_Settings::set_default( 'cache', $data );

?>