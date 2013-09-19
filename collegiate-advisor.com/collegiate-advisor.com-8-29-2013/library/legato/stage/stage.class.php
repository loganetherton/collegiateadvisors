<?php

	/*
		Class: Legato_Stage
		This class is the starting point for every site using the framework. It does
		all the set up and forwards the flow of the program on to the appropriate
		controller/action.
	*/
	class Legato_Stage
	{
		
		/*
			Group: Variables
			
			Var: $section
			*string* The active section that we're in.
		*/
		
		public static $hooks = array();                 // The array of hooks.
		public static $sections = array( 'default' );   // An array of the hierarchy of sections to the active section.
		public static $section = 'default';             // The section.
		
		
		/*
			(Exclude)
			
			Var: $_request
			The request array.
		*/
		
		private static $_request = array();


		/*
			Group: Functions
		*/
		
		/*
			(Exclude)
			Constructor: __construct()
			Static class.
		*/
		private function __construct()
		{

			/* Do Nothing */

		}
		
		
		/*
			Function: initialize()
			Loads in all the settings for a certain section and loads the hooks for
			it. Can be used if you would like to use pieces of the framework and have
			their settings loaded in, but don't want to use controllers and views.
			
			Syntax:
				void initialize( [string $section = ''] )
				
			Parameters:
				string $section - *optional* - The section to be initialized. Defaults to the default section.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize();
		*/
		public static function initialize( $section = '' )
		{
			
			if ( $section == '' )
			{
			
				// Initialize the Default Framework Settings
				Legato_Settings::initialize( LEGATO . '/settings.ini' );
				
				// Automatically include the settings files.
				if ( file_exists( ROOT . '/application/settings/settings.ini' ) ) 
					Legato_Settings::initialize( ROOT . '/application/settings/settings.ini' );
				
				// Development settings.
				if ( ENVIRONMENT_TYPE == 'development' && file_exists( ROOT . '/application/settings/settings_dev.ini' ) )
					Legato_Settings::initialize( ROOT . '/application/settings/settings_dev.ini' );	
					
				// Staging settings.
				if ( ENVIRONMENT_TYPE == 'staging' && file_exists( ROOT . '/application/settings/settings_stage.ini' ) )
					Legato_Settings::initialize( ROOT . '/application/settings/settings_stage.ini' );	
					
			}
			else
			{
				
				// Set the default settings for a section.
				$section_folder = Legato_Settings::get( 'stage', 'sections_folder' ) . '/' . $section;
				
				Legato_Settings::set( 'stage', array
				(
					'controllers_folder' => $section_folder . '/controllers',
					'views_folder' => $section_folder . '/views',
					'resources_folder' => $section_folder . '/resources',
					'sections_folder' => $section_folder . '/sections',
					'compressor_folder' => $section_folder . '/compressor',
					'autoloader_folder' => $section_folder . '/components',
					'forms_folder' => $section_folder . '/components/forms',
					'hooks_folder' => $section_folder . '/components/hooks',
					'helpers_folder' => $section_folder . '/components/helpers',
					'scripts_folder' => $section_folder . '/components/scripts'
				) );
				
				Legato_Settings::set( 'compressor', array
				( 
					'base_path' => '/' . $section . '/compressor'
				) );
				
				// Automatically include the settings files.
				if ( file_exists( ROOT . $section_folder . '/settings/settings.ini' ) ) 
					Legato_Settings::initialize( ROOT . $section_folder . '/settings/settings.ini' );
				
				// Development settings.
				if ( ENVIRONMENT_TYPE == 'development' && file_exists( ROOT . $section_folder . '/settings/settings_dev.ini' ) )
					Legato_Settings::initialize( ROOT . $section_folder . '/settings/settings_dev.ini' );	
					
				// Staging settings.
				if ( ENVIRONMENT_TYPE == 'staging' && file_exists( ROOT . $section_folder . '/settings/settings_stage.ini' ) )
					Legato_Settings::initialize( ROOT . $section_folder . '/settings/settings_stage.ini' );
				
			}
					
		}
		
		
		/*
			Function: run()
			Starts the processing of the system and will forward the flow of the program
			on to the appropriate controller/action.
		
			Syntax:
				void run( )
								
			Examples:
			>	Legato_Stage::run();
		*/
		public static function run()
		{
			
			// Load the Settings
			self::initialize();
			
			// Start the timer.
			if ( Legato_Settings::get( 'debugger', 'enable_reporting' ) )
				Legato_Debug_Debugger::start_timer();
				
			// Get the URI.
			$uri = $_SERVER['REQUEST_URI'];
			$uri = trim( SITE_URL ) == '' ? substr( $uri, 1 ) : str_replace( SITE_URL . '/', '', $uri );

			// Perform routing.
			self::_perform_routing( $uri );
			$uri = trim( $uri, '/' );
			
			// Split up the URL into it's constituent parts.
			$uri_array = explode( '/', $uri );
			
			// Get the section.
			$section = self::_get_section( $uri_array );
			
			// Load the hooks.
			self::_load_hooks();
			
			// Pre system hook.
			foreach ( self::$hooks as $hook )
				$hook->pre_system();
			
			// Check for compressor URL.
			if ( current( $uri_array ) == 'compressor' )
				$compressor = true;
			
			// Get the sub-folder and controller.
			list( $sub_folder, $sub_folders ) = self::_get_sub_folder( $uri_array );
			list( $controller, $controller_filename ) = self::_get_controller( $sub_folder, $sub_folders, $uri_array );
			
			// Check for compressor.
			if ( $compressor )
			{
				// Check for the compressor.
				// If it returns true, then it automatically took care of 
				// the compressor and we should stop processing.
				$compressor_return = self::_check_compressor( $controller, $controller_filename, $uri_array );
				if ( $compressor_return ) return;
			}			
			
			// Get the action and query values.
			list( $action, $reflection ) = self::_get_action( $controller, $uri_array );
			$query_values = self::_get_query_values( $uri_array );			
			
			// Pre run.
			self::_pre_run();
			
			// Pre controller hook.
			foreach ( self::$hooks as $hook )
				$hook->pre_controller();
				
			// Set up the request object.
			$uri_parts = explode( '/', $uri );
			if ( $uri_parts[(count( $uri_parts ) - 1)] == '' )
				unset( $uri_parts[(count( $uri_parts ) - 1)] );
				
			self::$_request = array
			(			
				'section' => $section,
				'controller' => $controller_filename,
				'action' => $action, 
				'generated_uri' => '/' . $sub_folders . $controller_filename . '/' . $action,
				'uri_parts' => $uri_parts,
				'method' => $_SERVER['REQUEST_METHOD'],
				'action_parameters' => $query_values
			);
			
			// Instantiate the controller.
			// Done before the header is shown.
			$controller_object = new $controller;			
			$controller_object->request = &self::$_request;
			
			// Start output buffering for the action.
			ob_start();
			
			// Do we have any missing required parameters?
			$required_params = $reflection->getNumberOfRequiredParameters();
			$query_value_count = count( $query_values );
			if ( $required_params > $query_value_count )
			{
				
				// Alert the user that there's not enough parameters passed in.
				Legato_Debug_Debugger::add_item( 'Not enough parameters passed in for action "' . $action . '". Parameters passed in: ' . count( $query_value_count ) . '. Parameters needed: ' . $required_params );
			
				// Fill in the missing parameters with NULL.
				for ( $i = $query_value_count; $i < $required_params; $i++ )
					$query_values[($i - 1)] = null;
			
			}  // End if missing required parameters.
			
			// Call the action.	
			$action_return = call_user_func_array( array( $controller_object, $action ), $query_values );
			
			// Get the contents of the output and clean the buffer.
			$action_output = ob_get_clean();
			
			// Start output buffering again.
			ob_start();
			
			// Let's handle any errors that may have happened.
			// Note that if there was an error, this function will try to call the error's delegation
			// from the error controller if there is any. It will modify the output automatically.
			self::_handle_error( $action_return, $action_output );
			
			// Should we show the layout?
			if ( Legato_Settings::get( 'stage', 'show_layout' ) )
			{
				
				// Add the content to the layout object.
				$layout = Legato_Layout::instance();
				$layout->content = $action_output;
				
				// Call the layout delegation.
				self::delegate( 'Index', Legato_Settings::get( 'stage', 'layout_delegation' ) );
				
			}
			else
				echo $action_output;
				
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
			self::_post_run();
			
			// Post system hook.
			foreach ( self::$hooks as $hook )
				$hook->post_system();
			
		}
		
		
		/*
			Function: delegate()
			Delegates the control of the program over to another controller/action.
			
			Syntax:
				void delegate( string $controller, string $delegation, [array $data = array()] )
				
			Parameters:
				string $controller - The controller that the delegation belongs to. Note that you can call a controller that's in a separate folder
				that the controller you're currently in. Just separate the directory names with a /. The paths are relative to the <Stage::controllers_folder>
				setting. You may use any sort of capitalization in the folder names. They'll all be converted to lowercase before using them.				
				string $delegation - The name of the delegation to call without the beginning underscore.
				array $data - *optional* - The array of data to be passed in to the delegation. Note that the values will be passed in as the parameters to
				the delegation's function.
				
			Notes:
				You should try to restrict yourself to calling this just in controllers and views.
								
			Examples:
			(begin code)
				class IndexController extends Legato_Controller
				{
					public function index()
					{
						
						$this->render_view( 'top' );
						Legato_Stage::delegate( 'Blog/Comments', 'view', array( 'all' ) );
						$this->render_view( 'bottom' );
						
					}				
				}
			(end)
			
			See Also:
				- <Controllers-Delegations>
		*/
		public static function delegate( $controller, $delegation, $data = array() )
		{
			
			// Try to construct the delegate's filename.
			$filename = strtolower( $controller );
			$filename = ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $filename . '.php';
			$controller = basename( $controller );
			$controller = $controller . 'Controller';
			$delegation = '_' . $delegation;
			
			// Only do this if the file has not already been included.
			if ( !class_exists( $controller ) )
			{
			
				// Check to make sure the file exists.
				if ( !file_exists( $filename ) )
					return Legato_Debug_Debugger::add_item( 'Delegation could not be loaded. No such controller with filename: ' . $filename );
					
				// Include the controller.
				include( $filename );
				
			}  // End if class doesn't already exist.

			// Instantiate the controller.
			// Make sure to set it up with the request array.
			$controller_object = new $controller;
			$controller_object->request = &self::$_request;
			
			// Call the delegation.
			call_user_func_array( array( $controller_object, $delegation ), $data );

		}
		
		
		/*
			(Exclude)
			Function: _perform_routing()
			Performs the routing on the URI. Will directly modify the URI passed in.
		*/
		private static function _perform_routing( &$uri )
		{
			
			// Trim the URI.
			$uri = trim( $uri, '/' );
			
			// Get the routes.
			$routes = Legato_Settings::get( 'routing' );
			
			// Any routes?
			if ( !$routes )
				return false;
			
			// Loop through each route.
			foreach ( $routes as $destination => $match )
			{
				
				$new_uri = $uri;
				$regex = $match;
				
				// Direct match.
				if ( $new_uri == $match )
				{			
					$uri = $destination;
					return true;					
				}
				
				// Regular expression.				
				if ( $match[0] == '/' && $match[(strlen( $match ) - 1)] == '/' )
				{
					
					// Switch all references in the form :number to $number for
					// regular expression backreference matching.
					$destination = preg_replace( '/:(\d)+/', '\$$1', $destination );
					
					// Try to replace with this regular expression.
					$count = 0;
					preg_replace( $match, $destination, $new_uri, 1, $count );
					
					// If the replace was successful, modify the URI and return.
					if ( $count )
					{
						$uri = $new_uri;
						return true;
					}
					
				}  // End if regular expression.
				
				// Simple syntax.
				if ( strpos( $match, ':num' ) !== false || strpos( $match, ':any' ) !== false || strpos( $match, '*' ) !== false )
				{
					
					// Create a regular expression to match the simple syntax they provided.
					$regex = str_replace
					( 
						array( '/', ':num', ':any', '*' ), 
						array( '\/', '(\d+)', '([^ \t\r\n\v\f\/]+)', '([^ \t\r\n\v\f]*)' ), 
						$regex 
					);
					
					// Switch all references in the form :number to $number for
					// regular expression backreference matching.
					$destination = preg_replace( '/:(\d)+/', '\$$1', $destination );
					
					// Try to replace with the regular expression created.
					$count = 0;
					$new_uri = preg_replace( '/^' . $regex . '$/', $destination, $new_uri, 1, $count );
					
					// If the replace was successful, modify the URI and return.
					if ( $count )
					{
						$uri = $new_uri;
						return true;
					}
					
				}  // End if simple syntax.
				
			}  // Next route.
			
			return false;
		
		}
		
		
		/*
			(Exclude)
			Function: _get_section()
			Checks if there's a section in the URI array.
		*/
		private static function _get_section( &$uri_array )
		{
			
			$default_section = Legato_Settings::get( 'stage', 'default_section' );
			
			$uri_part = strtolower( current( $uri_array ) );
			$uri_part = preg_replace( '/[^a-zA-Z0-9_\\/-]/', '', str_replace( '-', '_', $uri_part ) );
			
			// If this is a valid section, add it.
			if ( $uri_part && is_dir( ROOT . Legato_Settings::get( 'stage', 'sections_folder' ) . '/' . $uri_part ) )
			{
				
				array_unshift( self::$sections, $uri_part );
				self::$section = $uri_part;
				self::initialize( $uri_part );
				next( $uri_array );
				
				return $uri_part;

			}  // End if valid section.
			else if ( $default_section )
			{
				
				array_unshift( self::$sections, $default_section );
				self::$section = $default_section;
				self::initialize( $default_section );
				
				return $default_section;
				
			}  // End if there is a default section.
			
		}
		
		
		/*
			(Exclude)
			Function: _get_sub_folder()
			Checks if there's a sub-folder in the URI array.
		*/
		private static function _get_sub_folder( &$uri_array )
		{
			
			$sub_folders = '';
			$sub_folder = '';
			
			// There may be nested sub-folders.
			// Loop through until no more sub-folders.
			while( true )
			{
				
				$uri_part = strtolower( current( $uri_array ) );
				$uri_part = preg_replace( '/[^a-zA-Z0-9_\\/-]/', '', str_replace( '-', '_', $uri_part ) );
				
				// If this is a valid sub-folder, add it.
				if ( $uri_part != '' && is_dir( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $sub_folders . $uri_part ) )
				{
					
					$sub_folders .= $uri_part . '/';
					$sub_folder = $uri_part;
					next( $uri_array );
	
				}  // End if valid sub-folder.
				else
				{
					
					// If not a valid sub-folder, stop processing.
					return array( $sub_folder, $sub_folders );
					
				}  // End if no more sub-folders.
				
			}  // Next sub-folder.
			
		}
		
		
		/*
			(Exclude)
			Function: _get_controller()
			Checks if there's a controller in the URI array.
		*/
		private static function _get_controller( $sub_folder, $sub_folders, &$uri_array )
		{
			
			$uri_part = strtolower( current( $uri_array ) );
			$uri_part = preg_replace( '/[^a-zA-Z0-9_\\/-]/', '', str_replace( '-', '_', $uri_part ) );
					
			// If this is a valid controller, add it.
			if ( file_exists( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $sub_folders . $uri_part . '.php' ) )
			{
				
				$controller_filename = $uri_part;
				
				// Include the controller.
				include( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $sub_folders . $uri_part . '.php' );
				
				$controller_parts = explode( '_', $controller_filename );
				$controller = '';
				foreach ( $controller_parts as $uc )
					$controller .= ucfirst( $uc );
									
				$controller .= 'Controller';
			
				next( $uri_array );
				
				return array( $controller, $controller_filename );
				
			}  // End if valid controller.
				
			// Check for a default controller in this sub-folder.
			if ( $sub_folder != '' && file_exists( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $sub_folders . $sub_folder . '.php' ) )
			{
				
				$controller_filename = $sub_folder;
				
				// Include the controller.
				include( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/' . $sub_folders . $controller_filename . '.php' );
				
				$controller_parts = explode( '_', $controller_filename );
				$controller = '';
				foreach ( $controller_parts as $uc )
					$controller .= ucfirst( $uc );
									
				$controller .= 'Controller';
				
				return array( $controller, $controller_filename );
							
			}
			
			// If no controllers found yet, try to find an index controller for this section.
			if ( file_exists( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/index.php' ) )
			{
				
				$controller_filename = 'index';
				
				// Include the controller.
				include( ROOT . Legato_Settings::get( 'stage', 'controllers_folder' ) . '/index.php' );
				
				$controller_parts = explode( '_', $controller_filename );
				$controller = '';
				foreach ( $controller_parts as $uc )
					$controller .= ucfirst( $uc );
									
				$controller .= 'Controller';
				
				return array( $controller, $controller_filename );
				
			}
			
		}
		
		
		/*
			(Exclude)
			Function: _get_action()
			Checks if there's an action in the URI array.
		*/
		private static function _get_action( $controller, &$uri_array )
		{
			
			$uri_part = strtolower( current( $uri_array ) );
			$uri_part = preg_replace( '/[^a-zA-Z0-9_\\/-]/', '', str_replace( '-', '_', $uri_part ) );
			$reflection = null;
			
			// If this is a valid action, add it.
			try 
			{ 
				
				// Action can not start with an underscore.
				// Delegates start with an underscore and are protected from public view.
				// Try to reflect the method to see if it exists.
				if ( $uri_part[0] != '_' )
				{
					$reflection = new ReflectionMethod( $controller, $uri_part ); 				
					next( $uri_array );
					
					return array( $uri_part, $reflection );
				}
			
			}
			catch( ReflectionException $e ) 
			{
				/* Fall Through */
			}
			
			// If no method, return a default one.
			$reflection = new ReflectionMethod( $controller, 'index' ); 
			return array( 'index', $reflection );
			
		}
		
		
		/*
			(Exclude)
			Function: _get_query_values()
			Checks if there's any query values in the URI array.
		*/
		private static function _get_query_values( &$uri_array )
		{
			
			$query_values = array();
			
			// Loop through all the rest of the parts of the URI.
			while( true )
			{
				
				$uri_part = current( $uri_array );
				
				if ( $uri_part != null )
					$query_values[] = $uri_part;
				else
					return $query_values;
				
				next( $uri_array );
				
			}  // Next URI part.
			
		}
		
		
		/*
			(Exclude)
			Function: _check_compressor()
			Checks to see if there is a compressor embedded in the URL.
			If so, output the compressor and stop processing of the stage class.
		*/
		private static function _check_compressor( $controller, $controller_filename, $uri_array )
		{
			
			// Get the URI part.
			$uri_part = strtolower( current( $uri_array ) );
			$uri_part = preg_replace( '/[^a-zA-Z0-9_\\/-]/', '', str_replace( '-', '_', $uri_part ) );
			
			// If the controller found was a compressor controller, we have to
			// check if it contains the correct action to process the request.	
			if ( $controller_filename == 'compressor' )
			{
				
				// Reflect the class.
				$reflection = new ReflectionClass( $controller );
				
				// If the controller found has an action for this package, use it.
				// It will also use the index controller if it has it.
				// We return and let the Stage class finish running. It will take care of everything else.
				if ( $reflection->hasMethod( $uri_part ) || $reflection->hasMethod( 'index' ) )
					return false;
				
			}
			
			// Output the compressor and stop processing of Stage.
			Legato_Compressor::output();			
			return true;
			
		}
		
		
		/*
			(Exclude)
			Function: _pre_run()
			Called prior to running for any set up that might need to be done.
		*/
		private static function _pre_run()
		{
			
			// Set output buffering on, and gzip.
			//ob_start( 'ob_gzhandler' );
			
		}
		
		
		/*
			(Exclude)
			Function: _post_run()
			Called after running for any tear down that may need to be done.
		*/
		private static function _post_run()
		{
			
			if ( Legato_Settings::get( 'debugger', 'enable_reporting' ) )
			{
				
				// Stop the timer.
				$time = Legato_Debug_Debugger::stop_timer();
				
				// Post debugging information.
				Legato_Debug_Debugger::print_debug_info( $time );
				
			}
			
		}
		
		
		/*
			(Exclude)
			Function: _load_hooks()
			Loads all the hooks into the system.
		*/
		private static function _load_hooks()
		{
			
			$hooks_string = Legato_Settings::get( 'hooks', 'hooks' );
			
			// Make sure there are hooks.
			if ( $hooks_string != '' )
			{
				
				// Explode the hooks string.
				$hooks = explode( ',', $hooks_string );
				
				// Loop through all the hooks.
				foreach ( $hooks as $hook )
				{
					
					$hook = trim( $hook );
					$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $hook ) );
					
					// Try to get the hook they want. Look through all sections downwards.
					$found = false;
					foreach( self::$sections as $section )
					{
						$full_filename = ROOT . Legato_Settings::get( 'stage', 'hooks_folder', $section ) . '/' . $filename . '.hook.php';
						
						if ( file_exists( $full_filename ) )
						{
							require( $full_filename );
							$found = true;
							break;
						}
					}
					
					// Was any file found?
					if ( !$found )
					{
						Legato_Debug_Debugger::add_item( 'Could not load the hook, ' . $hook . '. No file found.' );
						continue;
					}
					
					// Instantiate the hook.
					$hook = $hook . 'Hook';
					$hook_object = new $hook;
					
					// Store it.
					self::$hooks[] = $hook_object;
					
				}
				
			}
		
		}
			
			
		/*
			(Exclude)
			Function: _handle_error()
			If any error was returned, will try to call the appropriate error's delegation
			of the Error controller, if there is any. Will modify the output, since it's
			passed by reference, to show what was outputted through the error's delegation.
		*/
		private static function _handle_error( $action_return, &$action_output )
		{
			
			// If false was returned, default to a 404 error.
			if ( $action_return === false )
				$action_return = 404;
		
			// An error?
			if ( is_int( $action_return ) && $action_return !== 0 )
			{
				
				// Start output buffering for the delegation.
				ob_start();
				
				// Delegate to the action.
				self::delegate( 'Error', 'error' . $action_return );
				
				// Get the contents of the output and clean the buffer.
				// Store it in the action output variable.
				$action_output = ob_get_clean();	
				
			}
		
		}
		
		
		/* 
			Group: Settings
			
			Var: default_section
			Whether or not to use a default section. A default section will be used if no section is passed in through
			the URL. You can use this to organize all the pieces of your site in sections and have the global section hold
			files that are purely for all the sections of your site.
			Defaults to nothing.
			
			Var: show_layout
			Whether to call the _layout (<Legato_Controller::_layout()>) delegation on the section's index controller.
			Defaults to true.
			For more information on delegations, see <Controllers-Delegations>
			
			Var: layout_view
			The layout view's filename. This is only if you didn't override the _layout delegation.
			Defaults to "layout".
			
			Var: layout_delegation
			If you're handling your own layout processing, this should be the delegation that will be called without the leading underscore.
			Defaults to "layout".
			
			Var: sections_folder
			The folder that the system will look in to find sections.
			Relative to the ROOT directory.
			Defaults to "/application/sections" unless under another section, in which case it's relative to that section's root folder.
			
			Var: controllers_folder
			The folder that the system will look in to find controllers.
			Relative to the ROOT directory.
			Defaults to "/application/controllers" unless under another section, in which case it's relative to that section's root folder.
			
			Var: views_folder
			The folder that the system will look in to find views.
			Relative to the ROOT directory.
			Defaults to "/application/views" unless under another section, in which case it's relative to that section's root folder.
			
			Var: resources_folder
			The folder that the system will look in to find resources.
			Relative to the ROOT directory.
			Defaults to "/application/resources" unless under another section, in which case it's relative to that section's root folder.
			
			Var: compressor_folder
			The folder that the system will look in to find files requested by the compressor.
			Relative to the ROOT directory.
			Defaults to "/application/compressor" unless under another section, in which case it's relative to that section's root folder.
			
			Var: hooks_folder
			The folder that the system will look in to find hooks.
			Relative to the ROOT directory.
			Defaults to "/application/components/hooks" unless under another section, in which case it's relative to that section's root folder.
			
			Var: forms_folder
			The folder that the system will look in to find forms.
			Relative to the ROOT directory.
			Defaults to "/application/components/forms" unless under another section, in which case it's relative to that section's root folder.
			
			Var: helpers_folder
			The folder that the system will look in to find helpers.
			Relative to the ROOT directory.
			Defaults to "/application/components/helpers" unless under another section, in which case it's relative to that section's root folder.
			
			Var: scripts_folder
			The folder that the system will look in to find controllers.
			Relative to the ROOT directory.
			Defaults to "/application/components/scripts" unless under another section, in which case it's relative to that section's root folder.
			
			Var: autoloader_folder
			The folder that the system will look in to find files requested by the autoloader.
			Relative to the ROOT directory.
			Defaults to "/application/components" unless under another section, in which case it's relative to that section's root folder.
		*/

	}