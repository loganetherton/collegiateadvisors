<?php

	//--------------------------------------------------------------------------
	// Class: LW_Settings
	// Holds all the settings for any class in the LW Framework. Provides get
	// and set methods as well as the option to initialize it from an INI file.
	//--------------------------------------------------------------------------
	class LW_Settings
	{

		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $initialized  = false;    // Was the class initialized yet?
		private static $settings     = array();  // The settings pulled from the INI file.


		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------
		
		//------------------------------------------------------------------------
		// Function: __construct()
		//
		// The class constructor.
		// It is private so that it can't be instantiated.
		//------------------------------------------------------------------------
		private function __construct()
		{

			/* Do Nothing */

		}
		
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------

		//------------------------------------------------------------------------
		// Function: initialize()
		//
		// This must be called at least once before any settings and pulled from
		// this class. It pulls the settings from an INI file passed in. If this
		// function is called multiple times, it will just override old settings
		// with new ones passed in.
		//
		// Parameters:
		//     $ini_file - The filename of the INI file that you'd like to pull
		//                 the settings from.
		//------------------------------------------------------------------------
		public static function initialize( $ini_file )
		{
			
			// Get the settings.
			$settings = parse_ini_file( $ini_file, true );
			
			// If we have already initialized, let's overwrite values.
			if ( self::$initialized )
			{
				
				// Loop through each section and merge.
				foreach ( $settings as $section_name => $section_settings )
				{
					if ( !self::$settings[$section_name] )
						 self::$settings[$section_name] = $section_settings;
					else
						self::$settings[$section_name] = array_merge( self::$settings[$section_name], $section_settings );
				}
				
			}
			else
				self::$settings = $settings;
			
			// Set initialized to true.
			self::$initialized = true;			
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: get()
		//
		// Returns the requested setting(s).
		//
		// Parameters:
		//     $section - The section of the settings you'd like to get.
		//     $key - The setting you'd like to get. If you leave the key blank,
		//            this function will return all the settings for the section
		//            passed in.
		//
		// Returns:
		//     The value of the setting you requested or all the values for the
		//     section you requested.
		//------------------------------------------------------------------------
		public static function get( $section, $key = '' )
		{
			
			// Make sure the class was initialized.
			if ( !self::$initialized )
			{
				LW_Debug_Debugger::add_item( 'The LW_Settings class must be initialized before it can be used.' );
				return false;
			}
			
			// Return the requested setting.
			if ( $key != '' )
				return self::$settings[$section][$key];
			else
				return self::$settings[$section];
			
		}


		//------------------------------------------------------------------------
		// Function: set()
		// 
		// Sets the specified setting with the value passed in.
		//
		// Parameters:
		//     $section - The section of the setting that you'd like to set.
		//     $key - The setting that you'd like to set.
		//     $value - The new value that you'd like the setting to have.
		//
		//     OR
		//
		//     $section - The section of the setting that you'd like to set.
		//     $values_array - An array of settings with key/value pairs to set.
		//------------------------------------------------------------------------
		public static function set()
		{
			
			// Make sure the class was initialized.
			if ( !self::$initialized )
			{
				LW_Debug_Debugger::add_item( 'The LW_Settings class must be initialized before it can be used.' );
				return false;
			}
			
			// Get the arguments passed in.
			$args = func_get_args();
			
			// Setting a section or key?
			if ( count( $args ) == 3 )
				self::$settings[$args[0]][$args[1]] = $args[2];
			else
			{
				
				if ( !is_array( self::$settings[$args[0]] ) )
					self::$settings[$args[0]] = array();
					
				self::$settings[$args[0]] = array_merge( self::$settings[$args[0]], $args[1] );
				
			}
			
			// Success.
			return true;
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: set_default()
		//
		// Sets the default value for the particular setting. It checks if a value
		// is already stored for the setting specified, and if not, stores it.
		// Will not overwrite a setting if there's already a value stored for it.
		//
		// Parameters:
		//     $section - The section of the setting that you'd like to set.
		//     $key - The setting that you'd like to set.
		//     $value - The default value that you'd like the setting to have.
		//
		//     OR
		//
		//     $section - The section of the setting that you'd like to set.
		//     $values_array - An array of settings with key/value pairs to set.
		//------------------------------------------------------------------------
		public static function set_default()
		{
			
			// Make sure the class was initialized.
			if ( !self::$initialized )
			{
				LW_Debug_Debugger::add_item( 'The LW_Settings class must be initialized before it can be used.' );
				return false;
			}
			
			// Get the arguments passed in.
			$args = func_get_args();
			
			// Setting a section or key?
			if ( count( $args ) == 3 )
			{
				
				if ( !isset( self::$settings[$args[0]][$args[1]] ) )
					self::$settings[$args[0]][$args[1]] = $args[2];
					
			}
			else
			{
				
				if ( !is_array( self::$settings[$args[0]] ) )
					self::$settings[$args[0]] = array();
				
				self::$settings[$args[0]] = array_merge( $args[1], self::$settings[$args[0]] );
				
			}
			
			// Success.
			return true;
			
		}

	}

?>
