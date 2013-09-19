<?php

	/*
		Class: Legato_Cache
		Manages a cache handler. Abstracts the handlers out so that you don't have to worry
		what kind of cache handler you're using.
	*/
	class Legato_Cache
	{

		/*
			Group: Variables
			
			Var: $namespace
			*string* The default namespace for this cache to set data into. Defaults to the global namespace.
			
			Var: $ttl
			*number* The default time-to-live for the data set by this cache. Defaults to <Legato_Cache::ttl>
			
			Var: $cache_dir
			*string* The default cache directoy for this handler. Defaults to <Legato_Cache::cache_dir>	
		*/	
		
		public $namespace = '';
		public $ttl = '';
		public $cache_dir = '';

		/*
			(Exclude)
			
			Var: $cache_handler
			The cache handler used by this cache.
		*/

		private $cache_handler  = NULL;  // The cache handler used by this cache.
		
		
		/*
			Group: Functions
		*/

		/*
			Constructor: __construct()
			Class constructor.
			Note that the default cache handler used is the one specified by the setting <Legato_Cache::default_handler>
			
			Syntax:
				void __construct( [ array $param = array() ] )
				
			Parameters:
				array $options - Any default variables to set on this object in the form of an array.
				See: <Legato_Cache::Variables> for the options.
				
			Returns:
				False on error. Legato_Cache object on success.
								
			Examples:
			(begin code)
				$cache = new Legato_Cache();  // Created based on the default settings.
				
				// Let's override some default settings.
				$cache = new Legato_Cache( array
				(
					'ttl' => 3600,
					'namespace' => 'Secret Files'
				) );
			(end)
		*/
		public function __construct( $options = array() )
		{
			
			$cache_handler = '';
			
			// Store the cache handler.
			if ( $options['handler'] != '' )
				$cache_handler = $options['handler'];
			else
				$cache_handler = Legato_Settings::get( 'cache', 'default_handler' );			
			
			// Create the handler.
			if ( $cache_handler == 'apc' )
				$this->cache_handler = new Legato_Cache_Handler_APC( $this );
			else if ( $cache_handler == 'memcache' )
				$this->cache_handler = new Legato_Cache_Handler_Memcache( $this );
			else if ( $cache_handler == 'sqlite' )
				$this->cache_handler = new Legato_Cache_Handler_SQLite( $this );
			else if ( $cache_handler == 'file' )
				$this->cache_handler = new Legato_Cache_Handler_File( $this );
			
			// Correct cache handler?
			if ( $this->cache_handler == NULL )
				return Legato_Debug_Debugger::add_item( 'Incorrect handler: ' . $cache_handler . '.' );
			
			// Store the time to live.
			if ( $options['ttl'] != '' )
				$this->ttl = $options['ttl'];
			else
				$this->ttl = Legato_Settings::get( 'cache', 'ttl' );
				
			// Store the cache directory.
			if ( $options['cache_dir'] != '' )
				$this->cache_dir = $options['cache_dir'];
			else
				$this->cache_dir = dirname( dirname( __FILE__ ) ) . Legato_Settings::get( 'cache', 'cache_dir' );
			
			// Store the namespace.
			if ( $options['namespace'] != '' )
				$this->namespace = $options['namespace'];
			
		}
		
		
		/*
			Function: set()
			Sets an item in the cache. If it's already there, it will overwrite it. If the item's not
			already there, it will add it.
			
			Syntax:
				bool set( string $key, string $value [, string $namespace = false, number $ttl = false ] )
				
			Parameters:
				string $key - The key that you'd like to set.
				
				string $value - The value you'd like the item to have. Note that you can not store objects. If you
				would like to store an object or array, you must serialize it first.
				
				string $namespace - *optional* - The namespace you'd like to set to. If you leave this blank,
				it will default to the default namespace defined in <Legato_Cache::$namespace>.
				
				number $ttl - *optional* - The time-to-live for this item.
				
			Returns:
				True if the item was set successfully. False otherwise.
								
			Examples:
				> $cache->set( 'query_return', $query['value'] );
				
			(begin code)
				// Set into the secret namespace and have it live for 30 seconds.
				$cache->set( 'secret of life', '42', 'Secret_Namespace', 30 );
			(end)
			
			See Also:
				- <Legato_Cache::get()>
		*/
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
		
		
		/*
			Function: get()
			Returns an item from the cache, if it exists.
			
			Syntax:
				string get( string $key [, string $namespace = false ] )
				
			Parameters:
				string $key - The key of the item that you'd like to get.
				
				string $namespace - *optional* - The namespace you'd like to get from. If you leave this blank,
				it will default to the default namespace defined in <Legato_Cache::$namespace>.
				
			Returns:
				The value of the item in the cache on success. 
				False if there is no item with that key or if the time-to-live has expired.
								
			Examples:
			(begin code)
				$cache->set( 'secret_value', '42' );
				
				if ( $cache->get( 'secret_value' ) )
					echo 'It has been found!';
			(end)
			
			See Also:
				- <Legato_Cache::set()>
		*/
		public function get( $key, $namespace = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
			
			// Forward to the handler.
			return $this->cache_handler->get( $key, $namespace );
			
		}
		
		
		/*
			Function: start()
			Used for caching a piece of code. If it finds a cached version already in the cache, it will
			output that and return true. If not, it will start caching and return false.
			
			Syntax:
				bool start( string $key [, string $namespace = false, number $ttl = false ] )
				
			Parameters:
				string $key - The key that you'd like to assign this chunk of code to.
				
				string $namespace - *optional* - The namespace you'd like this item to be put in to. If you leave this blank,
				it will default to the default namespace defined in <Legato_Cache::$namespace>.
				
				number $ttl - *optional* - The time-to-live for this item.
				
			Returns:
				True if there's already a cached item for this chunk of code.
				False if no cached item for this chunk of code yet, or if there was an error.
				
			Notes:
				You must define a stopping part of the chunk of code or it will keep caching until the end
				of the script. You do this with the <Legato_Cache::stop()> function.
								
			Examples:
			(begin code)
				if ( !$cache->start( 'View User' ) )
				{
					
					echo 'This will all be cached.';
										
					// Maybe do some DB queries, get some dynamic data, etc.
					
					$cache->stop();
					
				}
			(end)
			
			See Also:
				- <Legato_Cache::stop()>
		*/
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
		
		
		/*
			Function: stop()
			Stops caching a piece of code started with <Legato_Cache::start()> and stores it in the cache.
			
			Syntax:
				bool stop()
				
			Returns:
				True on success, false on error.
								
			Examples:
			(begin code)
				if ( !$cache->start( 'View User' ) )
				{
					
					echo 'This will all be cached.';
											
					// Maybe do some DB queries, get some dynamic data, etc.
					
					$cache->stop();
					
				}
			(end)
			
			See Also:
				- <Legato_Cache::start()>
		*/
		public function stop()
		{
			
			// Foward to the handler.
			return $this->cache_handler->stop();
			
		}
		
		
		/*
			Function: delete()
			Removes an item from the cache.
			
			Syntax:
				bool delete( $key [, $namespace = false ] )
				
			Parameters:
				string $key - The key of the item you'd like to remove.
				string $namespace - *optional* - The namespace you'd like to remove from. If you leave this blank,
				it will default to the default namespace defined in <Legato_Cache::$namespace>.
				
			Returns:
				True on a successful deletion. False if the item didn't exist.
								
			Examples:
			(begin code)
				$cache->set( 'key', 'value', 'namespace' );
				
				// Some processing.
				
				if ( $value_modified )
					$cache->delete( 'key', 'namespace' );
			(end)
			
			See Also:
				- <Legato_Cache::invalidate()>
				- <Legato_Cache::clear()>
		*/
		public function delete( $key, $namespace = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Foward to the handler.
			return $this->cache_handler->delete( $key, $namespace );
			
		}
		
		
		/*
			Function: invalidate()
			Invalidates all the items in a particular namespace.
			
			Syntax:
				bool function_name( string $namespace = false )
				
			Parameters:
				string $namespace - *optional* - The namespace you'd like to clear out. If you leave this blank,
				it will default to the default namespace defined in <Legato_Cache::$namespace>.
				
			Returns:
				True if the namespace was invalidated successfully. False if the namespace could not be found.
								
			Examples:
			(begin code)
				$cache->set( 'key1', 'value', 'namespace' );
				$cache->set( 'key2', 'different value', 'namespace' );
				
				// Some processing.
				
				if ( $value_modified )
					$cache->invalidate( 'namespace' );
					// Removed both key1 and key2 since they were in 'namespace'.
			(end)
			
			See Also:
				- <Legato_Cache::delete()>
				- <Legato_Cache::clear()>
		*/
		public function invalidate( $namespace = false )
		{
			
			// Get the correct namespace.
			if ( $namespace === false )
				$namespace = $this->namespace;
				
			// Foward to the handler.
			return $this->cache_handler->invalidate( $namespace );
			
		}
		
		
		/*
			Function: clear()
			Clears out the whole cache (all namespaces, include the global namespace).
			
			Syntax:
				bool clear()
				
			Returns:
				Simply returns true.
								
			Examples:
			(begin code)
				$cache->set( 'key1', 'value', 'namespace' );
				$cache->set( 'key2', 'different value', 'namespace' );
				$cache->set( 'another key', 'another value', 'another namespace' );
				
				// Some processing.
				
				// Let's kill the cache now!
				$cache->clear();
				
				// Will return false.
				echo $cache->get( 'key1', 'namespace' );
			(end)
			
			See Also:
				- <Legato_Cache::delete()>
				- <Legato_Cache::invalidate()>
		*/
		public function clear()
		{
			
			// Forward to the handler.
			return $this->cache_handler->clear();
			
		}
		
		
		/* 
			Group: Settings
			
			Var: enable
			Whether to allow any caching to be done.
			Defaults to true.
			
			Var: enable_apc
			Whether to allow any APC cache handlers to cache data.
			Defaults to true.
			
			Var: enable_memcache
			Whether to allow any memcached cache handlers to cache data.
			Defaults to true.
			
			Var: enable_sqlite
			Whether to allow any SQLite cache handlers to cache data.
			Defaults to true.
			
			Var: enable_file
			Whether to allow any File cache handlers to cache data.
			Defaults to true.
			
			Var: default_handler
			The default handler to use for any cache objects created.
			Defaults to "file".
			The options are
				- apc
				- memcache
				- sqlite
				- file
				
			Var: ttl
			The default time-to-live (in seconds) for any cache objects created.
			Defaults to 3600 (1 hour).
			
			Var: cache_dir
			The directory to use for caching.
			Relative to the LEGATO global configuration setting.
			Defaults to "/tmp/cache".
		*/

	}