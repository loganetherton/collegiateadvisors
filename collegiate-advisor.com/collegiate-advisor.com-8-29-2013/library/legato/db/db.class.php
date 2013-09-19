<?php
	 
	//----------------------------------------------------------------------------
	// Class: Legato_DB
	// Used to get handles to different database connectors.
	//----------------------------------------------------------------------------
	class Legato_DB
	{		
		
		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $_connectors = array();
		
		
		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------ 
		
		//------------------------------------------------------------------------
		// Constructor: __construct()
		// The class constructor.
		// It is private so that it can't be instantiated.
		//------------------------------------------------------------------------
		private function __construct()
		{
			
			/* Completely Static Class */	
		
		}
		
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------
		
		//------------------------------------------------------------------------
		// Function: get()
		//------------------------------------------------------------------------
		public function get( $connector )
		{
			
			// Do we have this connector stored already?
			if ( !self::$_connectors[$connector] )
			{

				// Get all the database settings.
				$settings = Legato_Settings::get( 'database' );
				
				// Get all this connectors settings.
				$dbname = $settings['connector.' . $connector];
				$type = $settings['connector.' . $connector . '.type'] ? $settings['connector.' . $connector . '.type'] : $settings['connector.type'];
				$host = $settings['connector.' . $connector . '.host'] ? $settings['connector.' . $connector . '.host'] : $settings['connector.host'];
				$user = $settings['connector.' . $connector . '.user'] ? $settings['connector.' . $connector . '.user'] : $settings['connector.user'];
				$pass = $settings['connector.' . $connector . '.pass'] ? $settings['connector.' . $connector . '.pass'] : $settings['connector.pass'];
				$port = $settings['connector.' . $connector . '.port'] ? $settings['connector.' . $connector . '.port'] : $settings['connector.port'];
				$memory = $settings['connector.' . $connector . '.memory'] ? $settings['connector.' . $connector . '.memory'] : $settings['connector.memory'];
				$path = $settings['connector.' . $connector . '.path'] ? $settings['connector.' . $connector . '.path'] : $settings['connector.path'];
				
				// Store them.
				self::$_connectors[$connector]['settings'] = array
				( 
					'type' => $type, 
				    'dbname' => $dbname, 
					'host' => $host, 
					'user' => $user, 
					'pass' => $pass, 
					'port' => $port,
					'memory' => $memory,
					'path' => $path
				);
				
				// Get the connector's handle.
				switch ( $type )
				{
					
					case 'mysql':
						$handle = new Legato_DB_Handler( 'mysql', array( 'user' => $user, 'pass' => $pass, 'host' => $host, 'dbname' => $dbname ) );
						break;
					case 'pgsql':
					case 'postgre':
						$handle = new Legato_DB_Handler( 'pgsql', array( 'user' => $user, 'pass' => $pass, 'host' => $host, 'port' => $port, 'dbname' => $dbname ) );
						break;
					case 'sqlite':
					case 'sqlite2':
						$handle = new Legato_DB_Handler( $type, array( 'path' => $path, 'memory' => $memory ) );
						break;
				
				}
						
					
				// Store the handle.
				self::$_connectors[$connector]['handle'] = $handle;
				
			}  // End if this connector isn't stored.
			
			// Return the connector's handle.
			return self::$_connectors[$connector]['handle'];
		
		}
	
	}