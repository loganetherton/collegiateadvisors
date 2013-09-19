<?php

	//----------------------------------------------------------------------------
	// Class: LW_Stage	
	// This class is the starting point for every site using an MVC system. It
	// does all the set up and calls the appropriate controller/action.
	//----------------------------------------------------------------------------
	class LW_Stage
	{


		//------------------------------------------------------------------------
		// Public Static Variables
		//------------------------------------------------------------------------
		public static $globals  = array();  // Users can use this array to store global variables.
		
		
		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $hooks  = array();  // The array of hooks.


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

		  /* Do Nothing */

		}


		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------

		//------------------------------------------------------------------------
		// Function: run()
		// Calling this will start the processing of the system and will call the
		// correct controller/action.
		//------------------------------------------------------------------------
		public static function run()
		{

			// Start the timer.
			if ( LW_Settings::get( 'debugger', 'reporting_on' ) )
				LW_Debug_Debugger::start_timer();
				
			// Load the hooks.
			self::load_hooks();
			
			// Pre system hook.
			foreach ( self::$hooks as $hook )
				$hook->pre_system();

			// Get the URI.
			$uri = $_SERVER['REQUEST_URI'];

			// Split up the URL into it's constituent parts.
			$uri_array = explode( '/', (trim( SITE_URL ) == '') ? substr( $uri, 1 ) : str_replace( SITE_URL . '/', '', $uri ) );
			
			// Make sure it contains something.
			if ( $uri_array[0] == '' )
				$uri_array[0] = 'index';
			
			// Set up the variables before we go into the loop to get all
			// the correct details from the URI.
			$section = '';
			$controller = 'IndexController';
			$controller_filename = 'index';
			$action = 'index';
			$query_values = array();
			$instantiated_controller = false;
			$done_with_sections = false;
			$done_with_controller = false;
			$done_with_action = false;
			
			// Loop through all the parts of the URI and pull the correct information.
			$count = count( $uri_array );
			for ( $i = 0; $i < $count; $i++ )
			{

				$uri_part = strtolower( $uri_array[$i] );
				
				// Make sure it's a valid string.
				if ( $uri_part == '' )
					continue;
				
				// If this is a valid section, add it.
				if ( $done_with_sections == false && is_dir( ROOT . LW_Settings::get( 'stage', 'controllers_folder' ) . '/' . $section . $uri_part ) )
				{
					
					// If there isn't anything left, make sure we go at least one more round
					// to include the controller.
					if ( !$uri_array[($i + 1)] )
					{
						$uri_array[($i + 1)] = 'index';
						++$count;
					}

					$section .= $uri_part . '/';
					continue;

				}  // End if valid section.
				
				// If we get this far, we are done with sections.
				$done_with_sections = true;
				
				// If this is a valid controller, use it.
				if ( $done_with_controller == false && is_file( ROOT . LW_Settings::get( 'stage', 'controllers_folder' ) . '/' . $section . $uri_part . '.php' ) )
				{

					$controller_filename = $uri_part;

					// Include the controller.
					include( ROOT . LW_Settings::get( 'stage', 'controllers_folder' ) . '/' . $section . $controller_filename . '.php' );
					$controller = ucfirst( $controller_filename ) . 'Controller';
					$done_with_controller = true;
					continue;

				}  // End if valid controller.
				else if ( $done_with_controller == false )
				{
					
					// Include the controller.
					include( ROOT . LW_Settings::get( 'stage', 'controllers_folder' ) . '/' . $section . $controller_filename . '.php' );

				}  // End if using index controller.

				// If we get this far, we are done with controllers.
				$done_with_controller = true;
				
				if ( $done_with_action == false )
				{
					
					// Try to reflect the URI part.
					try 
					{ 
						$reflect_method = new ReflectionMethod( $controller, $uri_part ); 
						
						$action = $uri_part;
						$done_with_action = true;
						continue;
					
					}
					catch( ReflectionException $e ) 
					{}

				}  // End if valid action.

				// If we get this far, we are done with actions.
				$done_with_action = true;

				// The rest from here on out are query values.
				$query_values[] = $uri_part;

			}  // Next URI part.
			
			// Pre run.
			self::pre_run();
			
			// Pre controller hook.
			foreach ( self::$hooks as $hook )
				$hook->pre_controller();
			
			// Instantiate the controller.
			// Done before the header is shown.
			$controller_object = new $controller;
			$controller_object->query_values = $query_values;
			
			// Start output buffering for the action.
			ob_start();
			
			// Call the action.
			$controller_object->$action();
				
			// Get the contents of the output and clean the buffer.
			$action_output = ob_get_contents();	
			ob_clean();
			
			// Should we show a header?
			if ( LW_Settings::get( 'stage', 'show_header_footer' ) )
				$controller_object->render_view( LW_Settings::get( 'stage', 'header_view' ) );
			
			// Put the output from the action.
			echo $action_output;
			
			// Should we show a footer?
			if ( LW_Settings::get( 'stage', 'show_header_footer' ) )
				$controller_object->render_view( LW_Settings::get( 'stage', 'footer_view' ) );
				
			// Get the output buffer.
			$action_output = ob_get_clean();
			
			// Display hook.
			foreach ( self::$hooks as $hook )
				$action_output = $hook->display( $action_output );
			
			// Show the output of the action.
			echo $action_output;
				
			// Destroy the controller object.
			// Done after the footer is shown.
			unset( $controller_object );
			
			// Post controller hook.
			foreach ( self::$hooks as $hook )
				$hook->post_controller();

			// Post run.
			self::post_run();
			
			// Post system hook.
			foreach ( self::$hooks as $hook )
				$hook->post_system();

		}


		//------------------------------------------------------------------------
		// Private Static Member Functions
		//------------------------------------------------------------------------

		//------------------------------------------------------------------------
		// (Exclude)
		// Function: pre_run()
		// Called prior to running for any set up that might need to be done.
		//------------------------------------------------------------------------
		private static function pre_run()
		{

			// Set output buffering on, and gzip.
			ob_start( 'ob_gzhandler' );

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: post_run()
		// Called after running for any tear down that might need to be done.
		//------------------------------------------------------------------------
		private static function post_run()
		{

			if ( LW_Settings::get( 'debugger', 'reporting_on' ) )
			{
				
				// Stop the timer.
				LW_Debug_Debugger::stop_timer();
	
				// Post debugging information.
				LW_Debug_Debugger::print_debug_info();
				
			}

		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: load_hooks()
		// Loads all the hooks into the system.
		//------------------------------------------------------------------------
		private static function load_hooks()
		{

			$hooks_string = LW_Settings::get( 'hooks', 'hooks' );
			
			// Make sure there are hooks.
			if ( $hooks_string != '' )
			{
				
				// Explode the hooks string.
				$hooks = explode( ',', $hooks_string );
				
				// Loop through all the hooks.
				foreach ( $hooks as $hook )
				{
					
					$hook = trim( $hook );
					
					// Get the filename for the hook.
					$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $hook ) );
					$filename = ROOT . LW_Settings::get( 'stage', 'hooks_folder' ) . '/' . $filename . '.hook.php';
					
					// Make sure it's a real file.
					if ( !is_file( $filename ) )
					{
						LW_Debug_Debugger::add_item( 'Could not load the hook, ' . $hook . '. No file found.' );
						continue;
					}
					
					// Include the hook.
					require( $filename );
					
					// Instantiate the hook.
					$hook = $hook . 'Hook';
					$hook_object = new $hook;
					
					// Store it.
					self::$hooks[] = $hook_object;
										
				}
				
			}

		}

	}

	//----------------------------------------------------------------------------
	// Configuration Settings
	//----------------------------------------------------------------------------
	$data['show_header_footer'] = true;
	$data['header_view'] = 'header';
	$data['footer_view'] = 'footer';
	
	LW_Settings::set_default( 'stage', $data );

?>
