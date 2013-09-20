//------------------------------------------------------------------------
// Name: LW_Debug_Debugger
// Desc: Works with the PHP LW_Debug_Debugger class to display debugging
//       information.
//------------------------------------------------------------------------
LW_Debug_Debugger =
{
  
	debug_items:         Array(),  // The debug items.
	queries:             Array(),  // The queries array.
	database_resources:  Array(),  // The database resources array.
	resources:           Array(),  // The resources array.
	execution_time:      "",       // The execution time of the page.
	
	
	//----------------------------------------------------------------------
	// Name: addSystemSetting()
	// Desc: Adds a setting.
	//----------------------------------------------------------------------
	addSystemSetting: function( category, setting, value )
	{

		// Add the setting.
		LW_Debug_Debugger.system_settings[category].push( { setting: setting,
		                                                    value:   value } );

	},
	

	//----------------------------------------------------------------------
	// Name: addItem()
	// Desc: Adds a debug item.
	//----------------------------------------------------------------------
	addItem: function( message, error_code, file, line, php_class, php_function, type )
	{

		// Add the debug item.
		LW_Debug_Debugger.debug_items.push( { message:       message,
		                                      error_code:    error_code,
		                                      file:          file,
		                                      line:          line,
		                                      php_class:     php_class,
		                                      php_function:  php_function,
		                                      type:          type,
		                                      args:          Array() } );

	},


	//----------------------------------------------------------------------
	// Name: addFunctionArg()
	// Desc: Adds a function's argument.
	//----------------------------------------------------------------------
	addFunctionArg: function( id, arg )
	{

		// Get the debug item.
		var debug_item = LW_Debug_Debugger.debug_items[id];

		// Add the function argument.
		debug_item.args.push( arg );

	},


	//----------------------------------------------------------------------
	// Name: addQuery()
	// Desc: Adds a query.
	//----------------------------------------------------------------------
	addQuery: function( query, file, line )
	{

		// Add the debug item.
		LW_Debug_Debugger.queries.push( { query:  query,
																	    file:   file,
																	    line:   line } );

	},


	//----------------------------------------------------------------------
	// Name: addDBResource()
	// Desc: Adds a database resource.
	//----------------------------------------------------------------------
	addDBResource: function( host, name )
	{

		// Add the database resource.
		LW_Debug_Debugger.database_resources.push( { host:  host,
																	               name:  name } );

	},


	//----------------------------------------------------------------------
	// Name: addResource()
	// Desc: Adds a resource.
	//----------------------------------------------------------------------
	addResource: function( class_name, id )
	{

		// Add the resource.
		LW_Debug_Debugger.resources.push( { class_name: class_name, id: id } );

	},


	//----------------------------------------------------------------------
	// Name: showDebugInfo()
	// Desc: Pops up a window with the debug information.
	//----------------------------------------------------------------------
	showDebugInfo: function()
	{

		var window_html         = "";
		var debug_window        = null;
		var last_resource_class = "";

		// Start getting the debug HTML.
		window_html += "<head>";

		window_html += "<title>Debug Information</title>";

		window_html += "<style>";
		window_html += "html { color: #000;	text-align: justify; background-color: #FFF; }";
		window_html += "body { margin: 0; padding: 0; color: #000; font-family: Verdana, Helvetica, sans-serif;	font-size: 11px; }";
		window_html += "h1, h2, h3, h4, h5, h6 { font-family: Trebuchet MS, verdana, sans-serif; margin: 0px; }";
		window_html += "h1 { font-size: 17px; color: #3870A9; border-bottom: 1px solid #CCC; padding-left: 10px; margin-top: 15px; }";
		window_html += "h2 { font-size: 14px; color: #3870A9; border-bottom: 1px solid #CCC; text-align: right; margin-top: 15px; }";
		window_html += ".item { background-color: #EEE; margin: 0px 10px 10px 10px; padding: 5px; }";
		window_html += "table { border: 1px solid #CCC; border-top: 0px; width: 100%; font-size: 11px; margin-bottom: 20px; }";
		window_html += "th { padding: 3px 0px 3px 0px; border-bottom: 1px solid #CCC; background-color: #EEE; width: 50%; }";
		window_html += "td { padding: 3px 15px 3px 5px; border-right: 1px solid #CCC; border-bottom: 1px solid #EEE; }";
		window_html += "</style>";

		window_html += "</head>";

		////////////////////////////////////////
		// Execution Information
		window_html += "<h1>Execution Information</h1>";

		window_html += "<div class='item'>";
		window_html += "<strong>Page Execution Time: </strong> <br /> <em>" + LW_Debug_Debugger.execution_time + " Seconds</em> <br /><br />";
		window_html += "<strong>Number of Debug Items: </strong> <em>" + LW_Debug_Debugger.debug_items.length + "</em> <br />";
		window_html += "<strong>Number of Queries: </strong> <em>" + LW_Debug_Debugger.queries.length + "</em> <br />";
		window_html += "<strong>Number of Resources: </strong> <em>" + LW_Debug_Debugger.resources.length + "</em> <br />";
		window_html += "</div>";

		////////////////////////////////////////
		// Debug items.
		window_html += "<h1>Debug Information</h1>";

		// Loop through each debug item.
		formatted_debug_items = LW_Debug_Debugger.formatDebugItems();

		for ( var i = 0; i < formatted_debug_items.length; i++ )
		{

			// Get the debug item.
			var debug_item = formatted_debug_items[i];

			window_html += "<h2>Item #" + (i + 1) + "</h2>";
			window_html += "<div class='item'>";
			window_html += debug_item;
			window_html += "</div>";

		}  // Next debug item.

		////////////////////////////////////////
		// Database queries.
		window_html += "<h1>Database Queries (" + LW_Debug_Debugger.queries.length + ")</h1>";

		// Loop through each query.
		for ( var i = 0; i < LW_Debug_Debugger.queries.length; i++ )
		{

			// Get the query.
			var query = LW_Debug_Debugger.queries[i];

			// Show the query.
			window_html += "<h2>Query #" + (i + 1) + "</h2>";
			window_html += "<div class='item'>";
			window_html += '"' + query.query + '" <br />';
			window_html += "<strong>File:</strong> <em>" + query.file + "</em> <br />";
			window_html += "<strong>Line:</strong> <em>" + query.line + "</em> <br />";
			window_html += "</div>";

		}  // Next query.

		////////////////////////////////////////
		// Resource information.
		window_html += "<h1>Resource Information (" + LW_Debug_Debugger.resources.length + ")</h1>";

		// Loop through each DB resource.
		for ( var i = 0; i < LW_Debug_Debugger.database_resources.length; i++ )
		{

			// Get the DB resource.
			var resource = LW_Debug_Debugger.database_resources[i];

			// Show the DB resource.
			window_html += "<h2>DB #" + (i + 1) + "</h2>";
			window_html += "<div class='item'>";
			window_html += "<strong>DB Host:</strong> <em>" + resource.host + "</em> <br />";
			window_html += "<strong>DB Name:</strong> <em>" + resource.name + "</em> <br />";
			window_html += "</div>";

		}  // Next DB resource.

		// Loop through each resource.
		for ( var i = 0, n = 0; i < LW_Debug_Debugger.resources.length; i++, n++ )
		{

			// Get the resource.
			var resource = LW_Debug_Debugger.resources[i];

			// If we haven't encountered this resource yet.
			if ( last_resource_class != resource.class_name )
			{

				// Reset n.
				n = 0;

				// Store this new resource's class name.
				last_resource_class = resource.class_name;

			}

			// Show the resource.
			window_html += "<h2>" + resource.class_name + " #" + (n + 1) + "</h2>";
			window_html += "<div class='item'>";
			window_html += "<strong>ID:</strong> <em>" + resource.id + "</em> <br />";
			window_html += "</div>";

		}  // Next resource.

		// Open a window for the output.
		debug_window = window.open( "", "DebugWindow", "menubar=yes,scrollbars=yes,status=yes,width=500,height=500" );

		// Show the output.
		debug_window.document.open();
		debug_window.document.write( window_html );
		debug_window.document.close();

	},


	//----------------------------------------------------------------------
	// Name: showDebugInfo()
	// Desc: Loops through each debug item and formats it ready for printing.
	//----------------------------------------------------------------------
	formatDebugItems: function()
	{

		var formatted_items = Array();

		// Loop through each debug item.
		for ( var i = 0; i < LW_Debug_Debugger.debug_items.length; i++ )
		{

			var debug_item = LW_Debug_Debugger.debug_items[i];

			formatted_items[i] = "";

			// Error code.
			if ( debug_item.error_code != "" )
			  formatted_items[i] += "<strong>[ " + debug_item.error_code + " ]</strong> - ";

			// Message.
			if ( debug_item.message != "" )
			  formatted_items[i] += '"<em>' + debug_item.message + '</em>" <br />';

			// File.
			if ( debug_item.file != "" )
			  formatted_items[i] += "<strong>File:</strong> <em>" + debug_item.file + "</em> <br />";

			// Line.
			if ( debug_item.line != "" )
			  formatted_items[i] += "<strong>Line:</strong> <em>" + debug_item.line + "</em> <br />";

			// Class.
			if ( debug_item.php_class != "" )
			  formatted_items[i] += "<strong>Class:</strong> <em>" + debug_item.php_class + "</em> <br />";

			// Function.
			if ( debug_item.php_function != "" )
			  formatted_items[i] += "<strong>Function:</strong> <em>" + debug_item.php_function + "</em> <br />";

			// Type.
			if ( debug_item.php_function != "" )
			{

				// Type of function?
				if ( debug_item.type == "->" )
				{
					formatted_items[i] += "<strong>Type:</strong> <em>Class Function</em> <br />";
				}
				else if ( debug_item.type == "::" )
				{
					formatted_items[i] += "<strong>Type:</strong> <em>Static Function</em> <br />";
				}
				else
				{
					formatted_items[i] += "<strong>Type:</strong> <em>Normal Function</em> <br />";
				}

			}  // End type.

			// Prototype.
			if ( debug_item.php_function != "" )
			{

				formatted_items[i] += "<strong>Prototype:</strong> <em>";

				// Any class?
				if ( debug_item.php_class != "" )
				  formatted_items[i] += debug_item.php_class;

				// Any type?
				if ( debug_item.type != "" )
				  formatted_items[i] += debug_item.type;

				// Put the function name.
				formatted_items[i] += debug_item.php_function + "( ";

				// Loop through the args.
				for ( var n = 0; n < debug_item.args.length; n++ )
				{

					formatted_items[i] += debug_item.args[n];

					if ( n != debug_item.args.length - 1 )
					  formatted_items[i] += ", ";

				}  // Next arg.

			  // End the function.
				formatted_items[i] += " )";

				formatted_items[i] += "</em> <br />";

			}  // End prototype.

			formatted_items[i] += "<br />";

		}  // Next debug item.

		// Return the formatted items.
		return formatted_items;

	}

};

