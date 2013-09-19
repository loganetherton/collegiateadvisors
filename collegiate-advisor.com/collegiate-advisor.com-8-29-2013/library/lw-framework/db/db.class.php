<?php
	 
	//----------------------------------------------------------------------------
	// Class: LW_DB
	// Used to get handles to different database connectors.
	//----------------------------------------------------------------------------
	class LW_DB
	{		
		
		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $connectors = array();
		
		
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
			if ( !self::$connectors[$connector] )
			{

				// Get all the database settings.
				$settings = LW_Settings::get( 'database' );
				
				// Get all this connectors settings.
				$db_name = $settings['connector.' . $connector];
				$type = $settings['connector.' . $connector . '.type'] ? $settings['connector.' . $connector . '.type'] : $settings['connector.type'];
				$host = $settings['connector.' . $connector . '.host'] ? $settings['connector.' . $connector . '.host'] : $settings['connector.host'];
				$user = $settings['connector.' . $connector . '.user'] ? $settings['connector.' . $connector . '.user'] : $settings['connector.user'];
				$pass = $settings['connector.' . $connector . '.pass'] ? $settings['connector.' . $connector . '.pass'] : $settings['connector.pass'];
				
				// Store them.
				self::$connectors[$connector]['settings'] = array( 'type' => $type, 
				                                                   'name' => $db_name, 
																   'host' => $host, 
																   'user' => $user, 
																   'pass' => $pass );
				
				// Get the connector's handle.
				if ( $type == 'mysql' )
					$handle = new LW_DB_MySQL( $user, $pass, $host, $db_name );
					
				// Store the handle.
				self::$connectors[$connector]['handle'] = $handle;
				
			}  // End if this connector isn't stored.
			
			// Return the connector's handle.
			return self::$connectors[$connector]['handle'];
		
		}
	
	}

?>
