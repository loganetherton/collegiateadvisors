<?php

	//--------------------------------------------------------------------------
	// Name: LW_Debug_Debugger
	// Desc: A debugging engine. Used to store all the debugging information
	//       and relay it. It uses the JS Debug_Debugger class to output the
	//       information.
	//--------------------------------------------------------------------------
	class LW_Debug_Debugger
	{

		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $debug_items     = array();  // The actual debug items.
		private static $num_items       = 0;        // The number of debug items.
		private static $queries         = array();  // The actual queries.
		private static $query_count     = 0;        // The number of queries executed.
		private static $execution_time  = 0;        // The time it took the Stage class to complete execution.


		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: Class constructor.
		// Note: It is private, so this class can not be instantiated.
		//------------------------------------------------------------------------
		private function __construct()
		{

			/* Do Nothing */

		}


		//-----------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: start_timer()
		// Desc: Starts the debug timer for page execution time.
		//------------------------------------------------------------------------
		public static function start_timer()
		{

			// Store the current time.
			self::$execution_time = microtime( true );

		}


		//------------------------------------------------------------------------
		// Name: stop_timer()
		// Desc: Stops the debug timer for page execution time.
		//------------------------------------------------------------------------
		public static function stop_timer()
		{

			// Get the current time.
			$end_time = microtime( true );

			// Store the page execution time.
			self::$execution_time = sprintf( '%f', $end_time - self::$execution_time );

		}


		//------------------------------------------------------------------------
		// Name: add_item()
		// Desc: Adds a debug item to the debugger.
		//------------------------------------------------------------------------
		public static function add_item( $message, $error_code = NULL )
		{

			// Make sure the message is not empty.
			if ( $message == "" )
				return;

			$search_values = array( "\r\n", "\n", "\t" );

			// Get the backtrace.
			$bt = debug_backtrace();

			// Assign to the values passed in.
			self::$debug_items[self::$num_items]['message']     = addslashes( htmlentities( str_replace( $search_values, ' ', $message ) ) );
			self::$debug_items[self::$num_items]['error_code']  = $error_code;

			self::$debug_items[self::$num_items]['file']        = addslashes( htmlentities( $bt[0]['file'] ) );
			self::$debug_items[self::$num_items]['line']        = $bt[0]['line'];
			self::$debug_items[self::$num_items]['class']       = htmlentities( $bt[1]['class'] );
			self::$debug_items[self::$num_items]['function']    = htmlentities( $bt[1]['function'] );
			self::$debug_items[self::$num_items]['type']        = htmlentities( $bt[1]['type'] );
			self::$debug_items[self::$num_items]['args']        = $bt[1]['args'];

			// Increment the number of items in the array.
			self::$num_items++;

		}

		//------------------------------------------------------------------------
		// Name: add_query()
		// Desc: Stores a copy of the query, and increments the query count.
		//------------------------------------------------------------------------
		public static function add_query( $query )
		{

			$search_values = array( "\r\n", "\n", "\t" );

			// Get the backtrace.
			$bt = debug_backtrace();

			// Add the query to our list.
			self::$queries[self::$query_count]['query'] = addslashes( htmlentities( str_replace( $search_values, ' ', $query ) ) );

			// Add the file and line number.
			self::$queries[self::$query_count]['file']  = addslashes( htmlentities( $bt[1]['file'] ) );
			self::$queries[self::$query_count]['line']  = $bt[1]['line'];

			// Increment the query counter.
			++self::$query_count;

		}

		//------------------------------------------------------------------------
		// Name: print_debug_info()
		// Desc: Loops through each debug item and prints it to the screen.
		//------------------------------------------------------------------------
		public static function print_debug_info()
		{

			if ( !LW_Settings::get( 'debugger', 'reporting_on' ) )
				return;
			
			echo '<script language="javascript" type="text/javascript">';

			// Put the page execution time.
			echo 'LW_Debug_Debugger.execution_time = "'. self::$execution_time .'";';
			
			// Loop through each debug item.
			$i = 0;
			foreach ( self::$debug_items as $debug_item )
			{

				echo 'LW_Debug_Debugger.addItem( "'. $debug_item['message'] .'", "'. $debug_item['error_code'] .'", "'. $debug_item['file'] .'", "'. $debug_item['line'] .'", "'. $debug_item['class'] .'", "'. $debug_item['function'] .'", "'. $debug_item['type'] .'" );';

				// Loop through the args.
				foreach ( $debug_item['args'] as $arg )
				{

					echo 'LW_Debug_Debugger.addFunctionArg( "'. $i .'", "'. $arg .'" );';

				}  // Next arg.

				// Increment.
				$i++;

			}  // Next debug item.

			// Loop through each query.
			foreach ( self::$queries as $query )
			{

				echo 'LW_Debug_Debugger.addQuery( "'. $query['query'] .'", "'. $query['file'] .'", "'. $query['line'] .'" );';

			}  // Next query.

			// Get the resource debug info.
			/*
			$resource_debug_info = LW_Resource_Manager::get_debug_info();

			// Loop through each database.
			foreach ( $resource_debug_info['databases'] as $database )
			{

				echo 'LW_Debug_Debugger.addDBResource( "'. $database['host'] .'", "'. $database['name'] .'" );';

			}  // Next database.

			// Loop through each resource class name.
			foreach ( $resource_debug_info['resources'] as $class_name => $resources )
			{

				// Loop through each resource.
				foreach ( $resources as $resource )
				{

			    	echo 'LW_Debug_Debugger.addResource( "'. $class_name .'", "'. $resource['id'] .'" );';

				}  // Next resource.

			}  // Next database.
			*/
			echo 'LW_Debug_Debugger.showDebugInfo();';

			echo '</script>';
			
		}

	}

?>