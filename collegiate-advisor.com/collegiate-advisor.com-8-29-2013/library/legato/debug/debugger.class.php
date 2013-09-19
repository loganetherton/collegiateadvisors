<?php

	//--------------------------------------------------------------------------
	// Name: Legato_Debug_Debugger
	// Desc: A debugging engine. Used to store all the debugging information
	//       and relay it. It uses the JS Debug_Debugger class to output the
	//       information.
	//--------------------------------------------------------------------------
	class Legato_Debug_Debugger
	{

		//------------------------------------------------------------------------
		// Private Static Variables
		//------------------------------------------------------------------------
		private static $_debug_items     = array();  // The actual debug items.
		private static $_num_items       = 0;        // The number of debug items.
		private static $_queries         = array();  // The actual queries.
		private static $_query_count     = 0;        // The number of queries executed.
		
		private static $_execution_time        = array();  // An array of times for the timer.
		private static $_execution_time_index  = 0;        // Since multiple timers can be started, this holds the index for the current timer.


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
			
			// Increment the index.
			++self::$_execution_time_index;

			// Store the current time.
			self::$_execution_time[self::$_execution_time_index] = microtime( true );

		}


		//------------------------------------------------------------------------
		// Name: stop_timer()
		// Desc: Stops the debug timer for page execution time.
		//------------------------------------------------------------------------
		public static function stop_timer()
		{

			// Get the current time.
			$end_time = microtime( true );

			// Return the execution time.
			$execution_time = sprintf( '%f', $end_time - self::$_execution_time[self::$_execution_time_index] );
			
			// Decrement the index.
			--self::$_execution_time_index;
			
			// Return the time.
			return $execution_time;

		}


		//------------------------------------------------------------------------
		// Name: add_item()
		// Desc: Adds a debug item to the debugger.
		//------------------------------------------------------------------------
		public static function add_item( $message, $error_code = NULL )
		{
			
			// Only add if reporting is on.
			if ( !Legato_Settings::get( 'debugger', 'enable_reporting' ) )
				return false;

			// Make sure the message is not empty.
			if ( $message == "" )
				return false;

			$search_values = array( "\r\n", "\n", "\t" );

			// Get the backtrace.
			$bt = debug_backtrace();

			// Assign to the values passed in.
			self::$_debug_items[self::$_num_items]['message']     = addslashes( htmlentities( str_replace( $search_values, ' ', $message ) ) );
			self::$_debug_items[self::$_num_items]['error_code']  = $error_code;

			self::$_debug_items[self::$_num_items]['file']        = addslashes( htmlentities( $bt[0]['file'] ) );
			self::$_debug_items[self::$_num_items]['line']        = $bt[0]['line'];
			self::$_debug_items[self::$_num_items]['class']       = htmlentities( $bt[1]['class'] );
			self::$_debug_items[self::$_num_items]['function']    = htmlentities( $bt[1]['function'] );
			self::$_debug_items[self::$_num_items]['type']        = htmlentities( $bt[1]['type'] );
			self::$_debug_items[self::$_num_items]['args']        = $bt[1]['args'];

			// Increment the number of items in the array.
			self::$_num_items++;
			
			// Return false so we can just return this in a function.
			return false;

		}
		

		//------------------------------------------------------------------------
		// Name: add_query()
		// Desc: Stores a copy of the query, and increments the query count.
		//------------------------------------------------------------------------
		public static function add_query( $query, $time )
		{
			
			// Only add if reporting is on.
			if ( !Legato_Settings::get( 'debugger', 'enable_reporting' ) )
				return;

			$search_values = array( "\r\n", "\n", "\t" );

			// Get the backtrace.
			$bt = debug_backtrace();

			// Add the query to our list.
			self::$_queries[self::$_query_count]['query'] = addslashes( htmlentities( str_replace( $search_values, ' ', $query ) ) );
			
			// Add the time.
			self::$_queries[self::$_query_count]['time'] = $time;

			// Add the file and line number.
			self::$_queries[self::$_query_count]['file']  = addslashes( htmlentities( $bt[1]['file'] ) );
			self::$_queries[self::$_query_count]['line']  = $bt[1]['line'];

			// Increment the query counter.
			++self::$_query_count;

		}


		//------------------------------------------------------------------------
		// Name: print_debug_info()
		// Desc: Loops through each debug item and prints it to the screen.
		//------------------------------------------------------------------------
		public static function print_debug_info( $execution_time )
		{

			if ( !Legato_Settings::get( 'debugger', 'enable_reporting' ) )
				return;
				
			$resources = self::_get_resources();
			$resource_count = count( $resources );
			$debug_items = self::_get_debug_items();
				
			$output = '
			<html>
			
				<head>
				
					<title>Debug Information</title>
			
					<style>
						
						html 
						{ 
							color: #000; 
							background-color: #FFF; 
							font-family: Verdana, sans-serif; 
							font-size: 11px;
						}
						
						body 
						{ 
							margin: 0; 
							padding: 0; 
						}
						
						h1, h2, h3, h4, h5, h6 { margin: 0px; }
						
						h1 
						{ 
							clear: both;
							margin-top: 25px;
							padding-left: 10px;
							color: #3870A9; 
							border-bottom: 1px solid #CCC; 
						}
						
						h2 
						{ 
							color: #3870A9; 
							margin-top: 10px;
							border-bottom: 1px solid #CCC;
							padding: 0px 0px 2px 50px; 
						}
						
						.item 
						{
							float: left;
							margin-bottom: 20px;
							width: 50%;
						}
						
						.item .content
						{
							background-color: #EEE;
							margin: 0px 4% 0px 4%; 
							padding: 10px; 
						}
						
						.highlight
						{
							background-color: #daeefa;
						}
						
					</style>
			
				</head>
				
				<body>
				
					<h1>Execution Information</h1>
					
					<div class="item">
						<div class="content">
							<strong>Page Execution Time: </strong> <em> ' . $execution_time . ' Seconds</em> <br /><br />
							<strong>Number of Debug Items: </strong> <em>' . self::$_num_items . '</em> <a href="" onclick="opener.scroll( \'items\' ); return false;">(go to)</a> <br />
							<strong>Number of Queries: </strong> <em>' . self::$_query_count . '</em> <a href="" onclick="opener.scroll( \'queries\' ); return false;">(go to)</a> <br />
							<strong>Number of Resources: </strong> <em>' . $resource_count . '</em> <a href="" onclick="opener.scroll( \'resources\' ); return false;">(go to)</a> <br />
						</div>
					</div>
			';
			
			// Debug items.
			if ( self::$_num_items )
			{
				
				$output .= '<h1 id="items">Debug Information</h1>';
				
				for ( $i = 0; $i < self::$_num_items; $i++ )
				{
					
					$output .= '
					<div class="item">
						<h2 id="item' . ($i + 1) . '">Item #' . ($i + 1) . '</h2>
						<div class="content">' . $debug_items[$i] . '</div>
					</div>
					';
					
					if ( $i % 2 )
						$output .= '<br style="clear: both;" />';
					
				}
				
			}
			
			// Queries.
			if ( self::$_query_count )
			{
				
				$output .= '<h1 id="queries">Database Queries (' . self::$_query_count . ')</h1>';
				
				for ( $i = 0; $i < self::$_query_count; $i++ )
				{
			
					$query = self::$_queries[$i];
			
					$output .= '
					<div class="item">
						<h2 id="query' . ($i + 1) . '">Query #' . ($i + 1) . '</h2>
						<div class="content">
							"' . $query['query'] . '" <br /><br />
							<strong>Time Taken:</strong> <em>' . $query['time'] . ' Seconds</em> <br /><br />
							<strong>File:</strong> <em>' . $query['file'] . '</em> <br />
							<strong>Line:</strong> <em>' . $query['line'] . '</em> <br />
						</div>
					</div>
					';
					
					if ( $i % 2 )
						$output .= '<br style="clear: both;" />';
			
				}  // Next query.
				
			}
			
			// Resources.
			if ( $resources )
			{
				
				$output .= '<h1 id="resources">Resources (' . $resource_count . ')</h1>';
				
				for ( $i = 0; $i < $resource_count; $i++ )
				{
					
					$resource = $resources[$i];
					
					$resource_data = '';
					foreach ( $resource['data'] as $data_key => $data_value )
						$resource_data .= '<strong>' . $data_key . ':</strong> ' . $data_value . '<br />';
						
					$output .= '
					<div class="item">
						<h2 id="resource_' . $resource['name'] . '_' . $resource['id'] . '">' . $resource['name'] . ' (' . $resource['id'] . ')</h2>
						<div class="content">
							' . $resource_data . '
						</div>
					</div>
					';
					
					if ( $i % 2 )
						$output .= '<br style="clear: both;" />';
					
				}
				
			}
			
			$output .= '
				</body>
			</html>
			';
	
			echo '	
			<script type="text/javascript">
			
				debug_window = window.open( "", "DebugWindow", "menubar=yes,scrollbars=yes,status=yes,width=900,height=600" );
		
				debug_window.document.open();
				debug_window.document.write( "<html>' . addslashes( str_replace( array( "\r\n", "\n", "\t" ), ' ', $output ) ) . '</html>" );
				debug_window.document.close();
				
				function scroll( elem )
				{
					
					var elem = debug_window.document.getElementById( elem );
					
					var selectedPosX = 0;
					var selectedPosY = 0;
					
					var theElement = elem;
					while ( theElement != null )
					{
						selectedPosX += theElement.offsetLeft;
						selectedPosY += theElement.offsetTop;
						theElement = theElement.offsetParent;
					}
				                        		      
					debug_window.scrollTo( selectedPosX, selectedPosY );
					
					highlightElement( elem );
				
				}
				
				highlightElement.highlighted = null;
				function highlightElement( elem )
				{
					
					if ( typeof( $ ) != "undefined" )
					{
						
						if ( highlightElement.highlighted )
							highlightElement.highlighted.removeClass( "highlight" );
						
						$( elem ).addClass( "highlight" );
						highlightElement.highlighted = elem;
						
					}
					
				}
			
			</script>
			';
			
		}
		
		
		private static function _get_resources()
		{
						
			$resource_debug_info = Legato_Resource::get_debug_info();
			
			// Loop through each type of resource.
			$return = array();
			foreach ( $resource_debug_info as $resource_name => $resources )
			{
			
				// Loop through each resource.
				foreach ( $resources as $resource_id => $resource_data )
				{
					
					if ( !is_array( $resource_data ) )
						continue;
					
					$data = array();
					if ( $resource_data )
					{
						foreach( $resource_data as $key => $value )
						{
							
							if ( is_array( $value ) )
								continue;
							
							if ( is_object( $value ) )
							{
								
								if ( !($value instanceof Legato_Resource) )
									continue;
									
								$debug = $value->get_resource_debug_info();
									
								$data[htmlspecialchars( $key )] = '<a href="" onclick="opener.scroll( \'resource_' . $debug[0] . '_' . $debug[1] . '\' ); return false;">' . $debug[0] . ' (' . $debug[1] . ')</a>';
								
							}
							else
							{
							
								$data[htmlspecialchars( $key )] = addslashes( str_replace( array( "\r\n", "\n", "\t" ), '<br />', htmlspecialchars( $value ) ) );
								
							}
							
						}
					}
					
					$return[] = array
					(
						'name' => $resource_name,
						'id' => $resource_id,
						'data' => $data
					);
			
				}  // Next resource.
				
			}  // Next resource type.
			
			return $return;
		
		}
		
		
		private static function _get_debug_items()
		{
			
			$debug_items = array();
			
			// Loop through each debug item.
			foreach ( self::$_debug_items as $item )
			{
	
				// Error code.
				if ( $item['error_code'] )
					$item_string .= '<strong>[ ' . $item['error_code'] . ' ]</strong> - ';
	
				// Message.
				if ( $item['message'] )
					$item_string .= '"<em>' . $item['message'] . '</em>" <br />';
	
				// File.
				if ( $item['file'] )
					$item_string .= '<strong>File:</strong> <em>' . $item['file'] . '</em> <br />';
	
				// Line.
				if ( $item['line'] )
					$item_string .= '<strong>Line:</strong> <em>' . $item['line'] . '</em> <br />';
	
				// Class.
				if ( $item['class'] )
					$item_string .= '<strong>Class:</strong> <em>' . $item['class'] . '</em> <br />';
	
				// Function.
				if ( $item['function'] )
					$item_string .= '<strong>Function:</strong> <em>' . $item['function'] . '</em> <br />';
	
				// Type.
				if ( $item['function'] )
				{
	
					// Type of function?
					if ( $item['type'] == '->' )
					{
						$item_string .= '<strong>Type:</strong> <em>Class Function</em> <br />';
					}
					else if ( $item['type'] == '::' )
					{
						$item_string .= '<strong>Type:</strong> <em>Static Function</em> <br />';
					}
					else
					{
						$item_string .= '<strong>Type:</strong> <em>Normal Function</em> <br />';
					}
	
				}  // End type.
	
				// Prototype.
				if ( $item['function'] )
				{
	
					$item_string .= '<strong>Prototype:</strong> <em>';
	
					// Any class?
					if ( $item['class'] )
						$item_string .= $item['class'];
	
					// Any type?
					if ( $item['type'] )
						$item_string .= $item['type'];
	
					// Put the function name.
					$item_string .= $item['function'] . '( ';
	
					// Loop through the args.
					$item_string .= implode( ', ', $item['args'] );
	
					// End the function.
					$item_string .= ' )';
					$item_string .= '</em> <br />';
	
				}  // End prototype.
	
				$item_string .= '<br />';
				
				$debug_items[] = $item_string;
	
			}  // Next debug item.
			
			return $debug_items;
			
		}

	}