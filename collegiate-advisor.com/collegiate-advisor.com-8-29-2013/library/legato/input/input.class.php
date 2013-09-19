<?php
	
	//----------------------------------------------------------------------------
	// Class: Legato_Mail
	// This class assists in sending emails.
	//----------------------------------------------------------------------------
	class Legato_Input
	{
		
		private static $_data = array();
		private static $_populated = array();
		
		//------------------------------------------------------------------------
		// Constructor: __construct()
		// Cannot be called.
		//------------------------------------------------------------------------
		private function __construct()
		{
			
			/* Do Nothing */
			
		}
		
		
		public static function get( $name )
		{
			
			return self::_get_superglobal( '_GET', $name );
			
		}
		
		
		public static function post( $name )
		{
			
			return self::_get_superglobal( '_POST', $name );
			
		}
		
		
		public static function cookie( $name )
		{
			
			return $_COOKIE[$name];
			
		}
		
		
		public static function files( $name )
		{
			
			return $_FILES[$name];
			
		}
		
		
		private static function _get_superglobal( $type, $name )
		{
			
			// If we already filtered this variable, return it.
			if ( self::$_populated[$name] )
				return self::$_data[$name];
			
			// Return the normal variable if no filtering.
			if ( !Legato_Settings::get( 'filter', 'superglobals' ) )
				return $GLOBALS[$type][$name];
				
			// If filtering is enabled, filter it.
			self::$_data[$name] = Legato_Filter::clean( $GLOBALS[$type][$name], Legato_Settings::get( 'filter', 'method' ) );
			self::$_populated[$name] = true;
			
			// Return the newly filtered data.
			return self::$_data[$name];
			
		}
		
	}