<?php
	
	/*
		Class: Legato_Settings
		Holds all the settings for any component in the Legato Framework.
		Settings are initialized from an INI file.
	*/
	class Legato_Settings
	{
		
		/*
			(Exclude)
			
			Var: $_loaded
			The sections that have been loaded.
			
			Var: $_settings
			The settings that have been loaded.
			
			Var: $_settings_sections
			Holds which sections each setting was defined in.
		*/	
		
		private static $_loaded    = array();         // The sections that have been loaded.
		private static $_settings  = array();         // The settings pulled from the INI file.
		private static $_setting_sections = array();  // Holds which sections each setting was defined in.
		
		
		/*
			Group: Functions
		*/
		
		/*
			(Exclude)
			Function: __construct()
			The class constructor.
			This is a purely static class, so the constructor is private.
		*/
		private function __construct()
		{

			/* Do Nothing */

		}
		

		/*
			Function: initialize()
			This should be called for each settings INI file that you would like to be loaded.
			Note that settings are automatically loaded in by the Stage component.
			If you call this function multiple times, it will just override old settings with
			the new ones passed in.			
			
			Syntax:
				void initialize( string $ini_file [, string $section = '' ] )
				
			Parameters:
				string $ini_file - The filename of the INI file that you'd like to pull the settings from.
				string $section - *optional* - The section that you'd like to load settings for. If left blank
				this function will pull settings for the current section in use.
								
			Examples:
				> Legato_Settings::initialize( ROOT . '/application/settings/cool_settings.ini' );
		*/
		public static function initialize( $ini_file, $section = '' )
		{
			
			// Get the section.
			if ( !$section ) 
				$section = Legato_Stage::$section;
				
			// If this isn't the default section and it hasn't been initialized yet,
			// carry over the default section's setting.
			if ( $section != 'default' && !self::$_loaded[$section] )
			{
				self::$_settings[$section] = self::$_settings['default'];
				self::$_loaded[$section] = true;
			}
				
			// Get the settings.
			$settings = parse_ini_file( $ini_file, true );
			
			// Loop through each category and merge.
			foreach ( $settings as $category_name => $category_settings )
			{
				
				// Make sure the arrays are initialized.
				if ( !self::$_settings[$section][$category_name] )
					self::$_settings[$section][$category_name] = array();
					
				if ( !self::$_setting_sections[$category_name] )
					self::$_setting_sections[$category_name] = array();
				
				// Store the settings.
				self::$_settings[$section][$category_name] = $category_settings + self::$_settings[$section][$category_name];
				
				// Save which sections these settings were defined in.
				self::$_setting_sections[$category_name] = array_fill_keys( array_keys( $category_settings ), $section ) + self::$_setting_sections[$category_name];
			
			}
			
			// Set the section as loaded.
			self::$_loaded[$section] = true;
			
		}
		
			
		/*
			Function: get()
			Returns the requested setting.
			You can also use this to return all the settings for a particular category.
			
			Syntax:
				mixed get( string $category, string $key [, string $section = '' ] )
				
			Parameters:
				string $category - The category of the settings you'd like to get.				
				
				string $key - *optional* - The setting you'd like to get. If you leave the key blank,
				this function will return all the settings for the category passed in.
				
				string $section - *optional* - The section that you'd like to get the settings from. If left blank
				this function will pull settings for the current section in use.
				
			Returns:
				The value of the setting you requested, or all of the values for the category
				you requested.
								
			Examples:
			(begin code)
				// Get one setting.
				$setting = Legato_Settings::get( 'stage', 'autoloader_folder' );
				
				// Get all the settings for the stage component.
				$stage_settings = Legato_Settings::get( 'stage' );
				
				// Get the compression level for the compressor from the
				// default section.
				$level = Legato_Settings::get
				( 
					'compressor', 
					'compression_level', 
					'default' 
				);			
			(end)
		*/
		public static function get( $category, $key = '', $section = '' )
		{
			
			// Get the section.
			if ( !$section )
				$section = Legato_Stage::$section;
				
			// If this isn't the default section and it hasn't been initialized
			// yet, carry over the default section's setting.
			if ( $section != 'default' && !self::$_loaded[$section] )
			{
				self::$_settings[$section] = self::$_settings['default'];
				self::$_loaded[$section] = true;
			}
			
			// Return the requested setting.
			if ( $key != '' )
				return self::$_settings[$section][$category][$key];
			else
				return self::$_settings[$section][$category];
			
		}


		/*
			Function: set()
			Sets the specified setting with the value passed in.
			
			Syntax:
				void set( string $category, string $key, mixed $value )
				
				void set( string $category, array $settings )
				
			Parameters:
				string $category - The category of the setting you'd like to set.
				string $key - The setting that you'd like to set.
				mixed $value - The new value that you'd like the setting to have.
				
				OR
				
				string $category - The category of the settings that you'd like to set.
				array $settings - An array of settings with key/value pairs to set.
								
			Examples:
			(begin code)
				// Set a setting.
				Legato_Settings::set( 'compressor', 'enable_compression', false );
				
				// Set multiple settings with one call.
				Legato_Settings::set
				(
					'compressor',
					array
					(
						'enable_compression' => true,
						'compression_level' => 7,
						'enable_caching' => true
					)
				);
			(end)
		*/
		public static function set()
		{
			
			// Get the section.
			$section = Legato_Stage::$section;
			
			// If this isn't the default section and it hasn't been initialized
			// yet, carry over the default section's setting.
			if ( $section != 'default' && !self::$_loaded[$section] )
			{
				self::$_settings[$section] = self::$_settings['default'];
				self::$_loaded[$section] = true;
			}
			
			// Get the arguments passed in.
			$args = func_get_args();
			
			// Setting a category or key?
			if ( count( $args ) == 3 )
			{
				self::$_settings[$section][$args[0]][$args[1]] = $args[2];
				self::$_setting_sections[$args[0]][$args[1]] = $section;
			}
			else
			{
				
				if ( !is_array( self::$_settings[$section][$args[0]] ) )
					self::$_settings[$section][$args[0]] = array();
					
				self::$_settings[$section][$args[0]] = array_merge( self::$_settings[$section][$args[0]], $args[1] );
				
				// We have to overwrite which settings belong to which sections.
				if ( !self::$_setting_sections[$args[0]] )
					self::$_setting_sections[$args[0]] = array();
					
				self::$_setting_sections[$args[0]] = array_merge( self::$_setting_sections[$args[0]], array_fill_keys( array_keys( $args[1] ), $section ) );
				
			}
			
			// Success.
			return true;
			
		}
		
		
		/*
			Function: get_section()
			You pass in a setting and it returns which section that setting was defined in.
				
			Syntax:
				string get_section( string $category, string $setting )
				
			Parameters:
				string $category - The category that the setting is in.
				string $setting - The setting you'd like to get a section for.
				
			Returns:
				Returns the section that the setting you passed in was defined in.
				NULL if the section could not be found.
										
			Examples:
				>	$section = Legato_Settings::get_section( 'stage', 'views_folder' );
		*/
		public static function get_section( $category, $setting )
		{
			
			return self::$_setting_sections[$category][$setting];
			
		}

	}