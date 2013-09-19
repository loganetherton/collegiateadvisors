//------------------------------------------------------------------------

// Package: Structures

// The various structures used by the system.

//

// Topic: Dependencies

// - <DOM Library>

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Class: LW_Structure_Color

// Handles a single color.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Structure_Color()

// Class constructor.

//

// Parameters:

//     RGB - A six character string of Red, Green and Blue values, eg: "FF2345".

//

//     OR

//

//     R - Red value, 0 - 255.

//     G - Green value, 0 - 255.

//     B - Blue value, 0 - 255.

//------------------------------------------------------------------------

function LW_Structure_Color()

{



	// What type of argument was passed in?

	if ( arguments.length == 1 && (typeof arguments[0] == "string" || arguments[0] instanceof String) )

	{



		this.R = parseInt( arguments[0].substring( 0, 2 ), 16 );

		this.G = parseInt( arguments[0].substring( 2, 4 ), 16 );

		this.B = parseInt( arguments[0].substring( 4, 6 ), 16 );



	}  // End if hex string.

	else if ( arguments.length == 3 )

	{



		this.R = arguments[0];

		this.G = arguments[1];

		this.B = arguments[2];



	}  // End if numbers passed in.

	else

	{



		this.R = null;

		this.G = null;

		this.B = null;



	}  // End if null passed in.



}





//------------------------------------------------------------------------

// Public Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Function: toHexString()

// Returns the color as a string of hexidecimal values.

//------------------------------------------------------------------------

LW_Structure_Color.prototype.toHexString = function()

{



	var R = (this.R < 16) ? "0" + this.R.toString( 16 ) : this.R.toString( 16 );

	var G = (this.G < 16) ? "0" + this.G.toString( 16 ) : this.G.toString( 16 );

	var B = (this.B < 16) ? "0" + this.B.toString( 16 ) : this.B.toString( 16 );



	return R + G + B;



}





//------------------------------------------------------------------------

// Class: LW_Structure_Dimensions

// Handles a single set of dimensions.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Structure_Dimensions()

// Class constructor.

//

// Parameters:

//     width - The width to store.

//     height - The height to store.

//

//     OR

//

//     element - A DOM element, in which case it will store the dimensions

//               of that element at the time this structure was created.

//------------------------------------------------------------------------

function LW_Structure_Dimensions()

{



	// What type of arguments passed in?

	if ( arguments.length == 2 )

	{



		// Store the passed in parameters.

		this.width   = arguments[0];

		this.height  = arguments[1];



	}  // End if a width and height.

	else if ( arguments.length == 1 )

	{



		var element = arguments[0];



		// Get the element's dimensions.

		this.width = LW_DOM_Library.getWidth( element );

		this.height = LW_DOM_Library.getHeight( element );



	}  // End if element passed in.



}





//------------------------------------------------------------------------

// Class: LW_Structure_Position

// Handles a single position. For positioning objects relative to its

// containing element.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Structure_Position()

// Class constructor.

//

// Parameters:

//     top - The value from the top of the containing element.

//     right - The value from the right of the containing element.

//     bottom - The value from the bottom of the containing element.

//     left - The value from the left of the containing element.

//------------------------------------------------------------------------

function LW_Structure_Position( top, right, bottom, left )

{



	// Store the passed in parameters.

	this.top     = top;

	this.right   = right;

	this.bottom  = bottom;

	this.left    = left;



}





//------------------------------------------------------------------------

// Class: LW_Structure_Point

// Handles a single point.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Structure_Point()

// Class constructor.

//

// Parameters:

//     X - The X value of the point.

//     Y - The Y value of the point.

//------------------------------------------------------------------------

function LW_Structure_Point( X, Y )

{



	// Store the passed in parameters.

	this.X = X;

	this.Y = Y;



}





//------------------------------------------------------------------------

// Class: LW_Structure_Region

// Handles a single region.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Structure_Region()

// Class constructor.

//

// Parameters:

//     min_point - The <LW_Structure_Point> of the top left corner.

//     max_point - The <LW_Structure_Point> of the bottom right corner.

//

//     OR

//

//     element - A DOM element, in which case the region will be the

//               containing region of the element.

//------------------------------------------------------------------------

function LW_Structure_Region()

{



	// What was passed in?

	if ( arguments.length == 2 )

	{



		// Store the passed in parameters.

	  this.min_point  = arguments[0];

	  this.max_point  = arguments[1];



	}  // End if points passed in.

	else if ( arguments.length == 1 )

	{



		var element = arguments[0];



		// Get the element's position for the region's min point.

		this.min_point = LW_DOM_Library.getXY( element );



		// Get the max point for the region.

		this.max_point = new LW_Structure_Point( (this.min_point.X + element.offsetWidth), (this.min_point.Y + element.offsetHeight) );



	}  // End if HTML element passed in.



}





//------------------------------------------------------------------------

// Public Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Function: intersectRegion()

// Used to test if two <LW_Structure_Region> objects are intersecting

// each other.

//

// Parameters:

//     region - The <LW_Structure_Region> that you'd like to test for

//              intersection against this object.

//     in_test - (Optional) If this is set to true, the region passed in

//               must be fully contained by this object.

//     epsilon - (Optional) This is the allowable mistake that can happen

//               between the tests, eg: if this is 1, then if the region

//               is 1 pixel away from intersecting, this function will

//               still return true.

//------------------------------------------------------------------------

LW_Structure_Region.prototype.intersectRegion = function( region, in_test, epsilon )

{



	// Get the epsilon.

	epsilon = (epsilon) ? epsilon : 0;



	// Only do this if we are testing if it's completely contained.

	if ( in_test )

	{



	  var contained = true;



		// Is the region completely contained in this region?

		if ( region.min_point.X < (this.min_point.X + epsilon) || region.min_point.X > (this.max_point.X + epsilon) ) contained = false;

		else if ( region.min_point.Y < (this.min_point.Y + epsilon) || region.min_point.Y > (this.max_point.Y + epsilon) ) contained = false;

		else if ( region.max_point.X < (this.min_point.X + epsilon) || region.max_point.X > (this.max_point.X + epsilon) ) contained = false;

		else if ( region.max_point.Y < (this.min_point.Y + epsilon) || region.max_point.Y > (this.max_point.Y + epsilon) ) contained = false;



		// Return.

		return contained;



	}  // End if completely contained test.

	else

	{



		// Perform the intersection test.

		return ((this.min_point.X + epsilon) <= region.max_point.X) &&

						((this.min_point.Y + epsilon) <= region.max_point.Y) &&

						((this.max_point.X + epsilon) >= region.min_point.X) &&

						((this.max_point.Y + epsilon) >= region.min_point.Y);



	}  // End normal touching test.



}





//------------------------------------------------------------------------

// Function: containsPoint()

// Used to test if there is an <LW_Structure_Point> object within the

// bounds of this region.

//

// Parameters:

//     point - The <LW_Structure_Point> object.

//------------------------------------------------------------------------

LW_Structure_Region.prototype.containsPoint = function( point )

{



	var contained = true;



	// Is the point completely contained in this region?

	if ( this.min_point.X > point.X || this.max_point.X < point.X ) contained = false;

	if ( this.min_point.Y > point.Y || this.max_point.Y < point.Y ) contained = false;



	// Return.

	return contained;



}





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



// First, before we do anything, load the Yahoo! UI DOM library.

document.writeln("<script type='text/javascript' src='js/yahoo-min.js'><" + "/script>");

document.writeln("<script type='text/javascript' src='js/dom-min.js'><" + "/script>");





//------------------------------------------------------------------------

// Name: LW_DOM_Library

// Desc: Some helper functions to make working with the DOM easier.

//------------------------------------------------------------------------

LW_DOM_Library =

{



	//------------------------------------------------------------------------

	// Class Constants

	//------------------------------------------------------------------------

	CREATE_IFRAME:   0,

	CREATE_HTML:     1,

	CREATE_DOMNODE:  2,



	//----------------------------------------------------------------------

	// Name: noteToString()

	// Desc: Iterates through the node's children and retrieves

	//       all the node's text.

	//----------------------------------------------------------------------

	nodeToString: function( node )

	{



		var node_string = "";

		var suffix      = "";



		// Loop through each child node.

		for ( var i = 0; i < node.childNodes.length; i++ )

		{



			// Get the child node.

			child_node = node.childNodes[i];



			// Only store the text if it's not null.

			if ( child_node.data != undefined )

			  node_string += child_node.data;



			// If this node has a tag name, store the tags.

			if ( child_node.tagName != undefined )

			{

			   node_string += "<" + child_node.tagName + ">";

				 suffix += "</" + child_node.tagName + ">";

			}



			// If this node has child nodes, recurse.

			if ( child_node.childNodes.length != 0 )

			  node_string += LW_DOM_Library.nodeToString( child_node );



			// Add the suffix.

			node_string  = node_string + suffix;

			suffix       = "";



		}  // Next child node.



		// Return the collected string.

		return node_string;



	},





	//----------------------------------------------------------------------

	// Name: getViewportWidth()

	// Desc: Returns the viewport's width.

	//----------------------------------------------------------------------

	getViewportWidth: function()

	{



		return YAHOO.util.Dom.getViewportWidth();



	},





	//----------------------------------------------------------------------

  // Name: getViewportHeight()

  // Desc: Returns the viewport's height.

  //----------------------------------------------------------------------

	getViewportHeight: function()

	{



	  return YAHOO.util.Dom.getViewportHeight();



	},





	//----------------------------------------------------------------------

	// Name: getDocumentWidth()

	// Desc: Returns the document's width.

	//----------------------------------------------------------------------

	getDocumentWidth: function()

	{



		return YAHOO.util.Dom.getDocumentWidth();



	},





	//----------------------------------------------------------------------

	// Name: getDocumentHeight()

	// Desc: Returns the documents's height.

	//----------------------------------------------------------------------

	getDocumentHeight: function()

	{



		return YAHOO.util.Dom.getDocumentHeight();



	},





	//----------------------------------------------------------------------

	// Name: getScrollXOffset()

	// Desc: Returns the scrollbar's X offset value.

	//----------------------------------------------------------------------

	getScrollXOffset: function()

	{



		return YAHOO.util.Dom.getDocumentScrollLeft();



	},





	//----------------------------------------------------------------------

	// Name: getScrollYOffset()

	// Desc: Returns the scrollbar's Y offset value.

	//----------------------------------------------------------------------

	getScrollYOffset: function()

	{



		return YAHOO.util.Dom.getDocumentScrollTop();



	},





	//----------------------------------------------------------------------

	// Name: getStyle()

	// Desc: Gets a property from the CSS style of the passed in element.

	//----------------------------------------------------------------------

	getStyle: function( element, property )

	{



		return YAHOO.util.Dom.getStyle( element, property );



	},





	//----------------------------------------------------------------------

	// Name: setStyle()

	// Desc: Sets a property from the CSS style of the passed in element, to

	//       the new value passed in.

	//----------------------------------------------------------------------

	setStyle: function( element, property, new_value )

	{



		YAHOO.util.Dom.setStyle( element, property, new_value );



	},





	//----------------------------------------------------------------------

	// Name: getWidth()

	// Desc: Returns the width of the passed in element.

	//----------------------------------------------------------------------

	getWidth: function( element )

	{



		return element.offsetWidth;



	},





	//----------------------------------------------------------------------

	// Name: getHeight()

	// Desc: Returns the height of the passed in element.

	//----------------------------------------------------------------------

	getHeight: function( element )

	{



	  	return element.offsetHeight;



	},





	//----------------------------------------------------------------------

	// Name: getDimensions()

	// Desc: Returns the dimensions of the passed in element.

	//----------------------------------------------------------------------

	getDimensions: function( element )

	{



		return LW_Structure_Dimensions( element.offsetWidth, element.offsetHeight );



	},





	//----------------------------------------------------------------------

	// Name: getX()

	// Desc: Returns the X value on the page of the passed in element.

	//----------------------------------------------------------------------

	getX: function( element )

	{



	  return LW_DOM_Library.getXY( element ).X;



	},





	//----------------------------------------------------------------------

	// Name: getY()

	// Desc: Returns the Y value on the page of the passed in element.

	//----------------------------------------------------------------------

	getY: function( element )

	{



	  return LW_DOM_Library.getXY( element ).Y;



	},





	//----------------------------------------------------------------------

	// Name: getXY()

	// Desc: Returns the X and Y value on the page of the passed in

	//       element.

	//----------------------------------------------------------------------

	getXY: function( element )

	{



		// Get the positions.

    	pos = YAHOO.util.Dom.getXY( element );



		// Return the position of the element.

		return new LW_Structure_Point( pos[0], pos[1] );



	},





	//----------------------------------------------------------------------

	// Name: setX()

	// Desc: Sets the X value on the page grid of the passed in element.

	//----------------------------------------------------------------------

	setX: function( element, X )

	{



		YAHOO.util.Dom.setX( element, X );



	},



	//----------------------------------------------------------------------

	// Name: setY()

	// Desc: Sets the Y value on the page grid of the passed in element.

	//----------------------------------------------------------------------

	setY: function( element, Y )

	{



		YAHOO.util.Dom.setY( element, Y );



	},





	//----------------------------------------------------------------------

	// Name: setXY()

	// Desc: Sets the X and Y value on the page grid of the passed in

	//       element.

	//----------------------------------------------------------------------

	setXY: function( element, point )

	{



		YAHOO.util.Dom.setXY( element, [ point.X, point.Y ] );



	},





	//----------------------------------------------------------------------

	// Name: getElementsByClassName()

	// Desc: Returns an array of elements with the class passed in.

	//----------------------------------------------------------------------

	getElementsByClassName: function( name, tag, root )

	{



		return YAHOO.util.Dom.getElementsByClassName( name, tag, root );



	},





	//----------------------------------------------------------------------

	// Name: createContainer()

	// Desc: Creates a container element in the document.

	//----------------------------------------------------------------------

	createContainer: function( create_type, creation_data, dimensions, position, align )

	{



		position = (position != null) ? position : new LW_Structure_Position( null, null, null, null );



		// Create a new div for the floating container.

		var container = document.createElement( "div" );



		// Specify its parameters.

		LW_DOM_Library.setStyle( container, "position", "absolute" );



		// Dimensions.

		if ( dimensions != null )

		{



			if ( dimensions.width  != null ) LW_DOM_Library.setStyle( container, "width", dimensions.width + "px" );

			if ( dimensions.height != null ) LW_DOM_Library.setStyle( container, "height", dimensions.height + "px" );



		}



		// Alignment.

		if ( align != null )

		{



			// Retrieve the window height and width.

			var client_height = LW_DOM_Library.getViewportHeight();

			var client_width  = LW_DOM_Library.getViewportWidth();



			// Get the element's dimensions.

			// We get it here in case the dimensions are set with CSS.

			var elem_dimensions = new LW_Structure_Dimensions( container );



			// Horizontal align.

			if ( align.halign == "left" )

				position.left = (position.left != null) ? position.left : 0;



			if ( align.halign == "center" )

				position.left = (position.left != null) ? (client_width * 0.5 - elem_dimensions.width * 0.5 + position.left) : (client_width * 0.5 - elem_dimensions.width * 0.5);



			if ( align.halign == "right" )

				position.left = (position.left != null) ? (client_width - elem_dimensions.width + position.left) : (client_width - elem_dimensions.width);



			// Vertical align.

			if ( align.valign == "top" )

				position.top = (position.top != null) ? position.top : 0;



			if ( align.valign == "center" )

				position.top = (position.top != null) ? (client_height * 0.5 - elem_dimensions.height * 0.5 + position.top) : (client_height * 0.5 - elem_dimensions.height * 0.5);



			if ( align.valign == "bottom" )

				position.top = (position.top != null) ? (client_height - elem_dimensions.height + position.top) : (client_height - elem_dimensions.height);



		}



		// Positions.

		if ( position.top    != null ) LW_DOM_Library.setStyle( container, "top", position.top + "px" );

		if ( position.right  != null ) LW_DOM_Library.setStyle( container, "right", position.right + "px" );

		if ( position.bottom != null ) LW_DOM_Library.setStyle( container, "bottom", position.bottom + "px" );

		if ( position.left   != null ) LW_DOM_Library.setStyle( container, "left", position.left + "px" );



		// Now add the content.

		if ( create_type == LW_DOM_Library.CREATE_IFRAME )

		{



			// Create the iframe.

			container.innerHTML = "<iframe width='" + dimensions.width + "' height='" + dimensions.height + "' frameborder='0' src='" + creation_data + "'></iframe>";



		}  // End if iframe passed.

		else if ( create_type == LW_DOM_Library.CREATE_HTML )

		{



			// Add the HTML to the container.

			container.innerHTML = creation_data;



		}  // End if HTML passed.

		else if ( create_type == LW_DOM_Library.CREATE_DOMNODE )

		{



			// Append the DOM node to the container.

		  container.appendChild( creation_data );



		}  // End if DOM node passed.



		// Append the div to the document.

		document.body.appendChild( container );



		// Return the new container element.

		return container;



	}



};





//------------------------------------------------------------------------

// Name: LW_Form

// Desc: A helper class to help with the PHP Form class.

//------------------------------------------------------------------------

//------------------------------------------------------------------------

// Some Global Form Variables

//------------------------------------------------------------------------

LW_Form.forms = Object();





//------------------------------------------------------------------------

// Public Member Functions

//------------------------------------------------------------------------

//------------------------------------------------------------------------

// Name: LW_Form

// Desc: Class constructor.

//------------------------------------------------------------------------

function LW_Form( form_id, dependants )

{



	// Add this object to a global form array.

	LW_Form.forms[form_id] = this;



	this.form_id     = form_id;    // The form ID.

	this.dependants  = dependants; // The dependants.



	// Loop through each dependant group.

	for ( var element_id in this.dependants )

	{



		// Get the element.

		var element = document.getElementById( element_id );



		// Add the events.

		if ( element.type == "radio" )

		{



			// What is the default value?

			if ( !element.checked )

			{



				// Call the toggleDependants function on these dependants.

				this.toggleDependants( element_id, false );



			}  // End if not checked.



			// Get all the elements with the same name as this.

			var elements = document.getElementsByName( element.name );



			// Loop through each element found.

			for ( var i = 0; i < elements.length; i++ )

			{



				// Add the event.

				if ( elements[i].id != element_id )

					LW_Events_Handler.addEvent( elements[i], "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "', false );" ) );

				else

					LW_Events_Handler.addEvent( elements[i], "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "', true );" ) );



			}  // Next element found.



		}  // End if radio button.

		else if ( element.type == "checkbox" )

		{



			// Call the toggleDependants function.

			this.toggleDependants( element_id );



			// Add the event.

			LW_Events_Handler.addEvent( element, "onclick", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );



		}  // End if checkbox.

		else if ( element.type == "select-one" )

		{



			// Call the toggleDependants function.

			this.toggleDependants( element_id );



			// Add the event.

			LW_Events_Handler.addEvent( element, "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );



		}  // End if select-one.

		else if ( element.type == "select-multiple" )

		{



			// Call the toggleDependants function.

			this.toggleDependants( element_id );



			// Add the event.

			LW_Events_Handler.addEvent( element, "onchange", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );



		}  // End if select-multiple.

		else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )

		{



			// Call the toggleDependants function.

			this.toggleDependants( element_id );



			// Add the event.

			LW_Events_Handler.addEvent( element, "onkeyup", new Function( "LW_Form.forms['" + this.form_id + "'].toggleDependants( '" + element_id + "' );" ) );



		}  // End if text.



	}  // Next dependant group.



}





//------------------------------------------------------------------------

// Name: toggleDependants

// Desc: Toggles the dependants on or off depending upon whether their

//       element is on or off.

//------------------------------------------------------------------------

LW_Form.prototype.toggleDependants = function( element_id, on )

{



	// Get the element.

	var element = document.getElementById( element_id );



	// What type of element.

	if ( element.type == "checkbox" )

	{



		if ( element.checked )

			on = true;

		else

			on = false;



	}  // End if checkbox.

	else if ( element.type == "select-one" )

	{



		if ( element.value == this.dependants[element_id].value[0] )

			on = true;

		else

			on = false;



	}  // End if select-one.

	else if ( element.type == "select-multiple" )

	{



		// Set on to be initially true. We will find if it should

		// really be true later on.

		var on = true;



		// Loop through each value.

		for ( var i = 0; i < this.dependants[element_id].value.length; i++ )

		{



			// Loop through each option.

			for ( var n = 0; n < element.options.length; n++ )

			{



				if ( !element.options[n].selected && element.options[n].value == this.dependants[element_id].value[i] )

					on = false;



			}  // Next option.



		}  // Next value.



	}  // End if select-one.

	else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )

	{



		if ( element.value != '' )

			on = true;

		else

			on = false;



	}  // End if other.



	// Are we disabling or enabling?

	if ( on )

	{



		// Loop through each dependant.

		for ( var i = 0; i < this.dependants[element_id].dependants.length; i++ )

		{



			// Get the dependant.

			dependant = this.dependants[element_id].dependants[i];



			// Get the dependant's element.

			var dependant_element = document.getElementById( dependant );



			// Make sure nothing else is controlling this element.

			var free = this.isDependantFree( dependant );



			// Enable the element.

			if ( free ) dependant_element.disabled = false;



		}  // Next dependant.



	}  // End if enabling.

	else if ( !on )

	{



		// Loop through each dependant.

		for ( var i = 0; i < this.dependants[element_id].dependants.length; i++ )

		{



			// Get the dependant.

			dependant = this.dependants[element_id].dependants[i];



			// Get the dependant's element.

			var dependant_element = document.getElementById( dependant );



			// Disable the element.

			dependant_element.disabled = true;



		}  // Next dependant.



	}  // End if disabling.



}





//------------------------------------------------------------------------

// Name: isDependantFree

// Desc: Loops through every dependant being manage by the system and

//       makes sure this dependant is completely free.

//------------------------------------------------------------------------

LW_Form.prototype.isDependantFree = function( dependant )

{



	var ret = true;



	// Loop through each dependant group.

	for ( var element_id in this.dependants )

	{



		// Get the dependant group.

		var dependant_array = this.dependants[element_id].dependants;



		// Loop through each dependant.

		for ( var i = 0; i < dependant_array.length; i++ )

		{



			// If the dependant was found.

			if ( dependant == dependant_array[i] )

			{



				// Get the main dependant element.

				var element = document.getElementById( element_id );



				// If the element's main dependant is not checked, return false.

				if ( element.type == "checkbox" )

				{



					if ( !element.checked )

						ret = false;



				}  // End if checkbox.

				else if ( element.type == "radio" )

				{



					if ( !element.checked )

						ret = false;



				}  // End if radio.

				else if ( element.type == "select-one" )

				{



					if ( element.value != this.dependants[element_id].value )

						ret = false;



				}  // End if select-one.

				else if ( element.type == "text" || element.type == "password" || element.type == "textarea" || element.type == "file" )

				{



					if ( element.value == '' )

						ret = false;



				}  // End if other.



			}  // End if found.



		}  // Next dependant.



	}  // Next dependant group.



	return ret;



}





//------------------------------------------------------------------------

// Static Functions

//------------------------------------------------------------------------

//------------------------------------------------------------------------

// Name: submitForm

// Desc: You pass it a form DOM element and it submits the form, calling

//       the form's obsubmit event.

//------------------------------------------------------------------------

LW_Form.submitForm = function( form )

{



	// Call the onsubmit event of the form.

	form.onsubmit();



}





//------------------------------------------------------------------------

// Name: LW_Form_Validator

// Desc: Exploses methods to validate the input parameters passed in to a

//       form.

//------------------------------------------------------------------------

//------------------------------------------------------------------------

// Some Global Form_Validator Variables

//------------------------------------------------------------------------

LW_Form_Validator.validators = Object();





//------------------------------------------------------------------------

// Name: Form_Validator

// Desc: Class constructor.

//------------------------------------------------------------------------

function LW_Form_Validator( form, options_object )

{



	// Add this object to a global validator array.

	LW_Form_Validator.validators[form.id] = this;



	this.form                  = form;

	this.input_elements        = new Array();

	this.groups                = new Array();



	// Store the parameters.

	this.submit_button         = options_object.submit_button;

	this.submit_form           = (options_object.submit_form == null) ? false : true;

	this.redirect_url          = options_object.redirect_url;

	this.modified_request_url  = "";

	this.errors                = new Array();



	this.processing            = false;



	// Add on onsubmit event for the form.

	LW_Events_Handler.addEvent( this.form, "onsubmit", new Function( "return LW_Form_Validator.validators['" + this.form.id + "'].validateForm();" ) );



}





//----------------------------------------------------------------------

// Name: validateForm()

// Desc: Start the validation.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.validateForm = function()

{



	var errors = false;



	// Are we processing?

	if ( this.processing == true )

		return false;

	else

		this.processing = true;



	// Disable the submit button.

	if ( this.submit_button != null ) this.submit_button.disabled = true;



	// First make sure the form is clean.

	this.cleanupForm();



	// Call the form's onprevalidate function.

	if ( this.form['onprevalidate'] != null )

		this.form['onprevalidate']();

	/*

	// Were there any errors?

	if ( errors == true )

	{



		this.postError( null, "There were errors while processing the form. Please fix them and try submitting the form again." );



		// Set processing to false.

		this.processing = false;



		// Enable the submit button.

		if ( this.submit_button != null ) this.submit_button.disabled = false;



		// Return. We don't want to process any more.

		return false;



	}



	// If there is a request URL, send the request.

	if ( this.request_url != null )

	{



	  var query_string = this.getQueryString();



		// Send the request.

		LW_RequestManager.makeRequest( this.modified_request_url, new Function( "response", "LW_Form_Validator.validators['" + this.form.id + "'].processResponse( response );" ), query_string );



		// Return. We will continue processing in the processResponse() function.

		return false;



	}

	*/

	// Finish the validation.

	return this.finishValidation();



}





//----------------------------------------------------------------------

// Name: processResponse()

// Desc: Processes the response from the XHR request.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.processResponse = function( response )

{



	var response = response.responseXML;



	// Hide the message container, if there is one.

	if ( this.message_container != null )

		LW_DOM_Library.setStyle( this.message_container, "visibility", "hidden" );



	// Get the errors.

	var errors = response.getElementsByTagName( "error" );



	// Is there any errors?

	if ( errors.length != 0 )

	{



		// Loop through each error.

		for ( var i = 0; i < errors.length; i++ )

		{



			// Get the error details.

			var id = errors[i].getElementsByTagName( "id" );



			if ( id.length == 0 )

				id = null;

			else

				id = id[0].firstChild.data;



			var message = errors[i].getElementsByTagName( "message" )[0].firstChild.data;



			// Post the error.

			this.postError( id, message );



		}  // Next error.



		// Should we alert the errors?

		if ( this.alert_errors )

		{



		  // Show the first error as an alert.

		  alert( this.errors[0] );



		  // Empty out the errors array.

		  this.errors.length = 0;



		}  // End if alerting errors.



	}  // End if errors.

	else

	{



		// Finish validation.

		LW_Form_Validator.validators[this.form.id].finishValidation();



		// Return.

		return;



	}  // End if no errors.



	// If there was errors, show the generic error message.

	if ( errors.length > 0 && !this.alert_errors ) this.postError( null, "There were errors while processing the form. Please fix them and try submitting the form again." );



	// Set processing to false.

	this.processing = false;



	// Enable the submit button.

	if ( this.submit_button != null ) this.submit_button.disabled = false;



}





//----------------------------------------------------------------------

// Name: finishValidation()

// Desc: Finishes the validation.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.finishValidation = function()

{



	// Set processing to false.

	this.processing = false;



	// Enable the submit button.

	if ( this.submit_button != null ) this.submit_button.disabled = false;



	// Check for a redirect URL and if there is one redirect them.

//	if ( this.redirect_url != null )

	//	window.location = this.redirect_url;



	// If the submit form flag is set, submit the form.

	if ( this.submit_form )

		return true;

	else

		return false;



}





//----------------------------------------------------------------------

// Name: postError()

// Desc: Posts an error to the form with information about what went

//       wrong and why it went wrong.

// Note: If null is passed in for element_id, the error will be placed

//       at the end of the form.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.postError = function( element_id, error )

{



	// Create the error node.

	var error_node = document.createElement( "p" );

	error_node.className = "error";



	// Stick the text into the error node.

	error_node.innerHTML = error;



	// What type of placement?

	if ( element_id == null )

	{



		// Do we have a submit button?

		if ( this.submit_button != null )

		{



			this.submit_button.parentNode.parentNode.insertBefore( error_node, this.submit_button.parentNode.parentNode.firstChild );



		}  // End if submit button.

		else

		{



			document.getElementById( this.form.id ).appendChild( error_node );



		}  // End if no submit button.



	}  // End if general error message.

	else

	{



		var html_element = document.getElementById( element_id );



		// Group or normal?

		if ( html_element.parentNode.parentNode.className == "group" )

		{



			// Insert the error node before the input element.

			html_element.parentNode.parentNode.insertBefore( error_node, html_element.parentNode.parentNode.firstChild );



		}  // End if group.

		else

		{



			// Insert the error node before the input element.

			html_element.parentNode.insertBefore( error_node, html_element.parentNode.firstChild );



		}  // End if normal.



	}  // End if normal/group error message.



}





//----------------------------------------------------------------------

// Name: cleanupForm()

// Desc: Cleans up the form to make it ready for form validation.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.cleanupForm = function()

{



	// Get all the forms error's.

	var errors = this.form.getElementsByTagName( "p" );



	// Loop through each error.

	// We get the length before hand, because we take elements away from the

	// array in the loop.

	for ( var i = 0, length = errors.length, index = 0; i < length; i++ )

	{



		// Get the element. We retrieve the 0th element because we remove

		// the child below, and the next one will fall in this place.

		var error_element = errors[index];



		// Is this an error element?

		if ( error_element.getAttribute( "class" ) == "error" )

			error_element.parentNode.removeChild( error_element );  // Remove the element.

		else

			index++; // Increment the index if we have skipped an element.





	}  // Next error node.



}





//----------------------------------------------------------------------

// Name: getQueryString()

// Desc: Concatenates all the managed input elements in to a query

//       string suitable for appending to a URL.

//----------------------------------------------------------------------

LW_Form_Validator.prototype.getQueryString = function()

{



	var values = Array();



	// Loop through each input element in the form.

	for ( var i = 0; i < this.form.elements.length; i++ )

	{



		var element = this.form.elements[i];



		// What type of element?

		switch ( element.type )

		{



		// Simple elements.

	  case 'text':

		case 'password':

		case 'file':

		case 'textarea':

		case 'hidden':

		case 'select-one':



		  values.push( (element.name + "=" + encodeURIComponent( element.value )) );

			break;



		// Checkboxes and radio buttons.

		case 'checkbox':

		case 'radio':



		  // Only add if checked.

			if ( element.checked )

			  values.push( (element.name + "=" + encodeURIComponent( element.value )) );



			break;



		// Select multiples.

		case 'select-multiple':



		  // Loop through each option.

			for ( var n = 0; n < element.options.length; n++ )

			{



				// Only add if option is selected.

				if ( element.options[n].selected )

				  values.push( (element.name + "=" + encodeURIComponent( element.options[n].value )) );



			}  // Next option.



			break;



		}  // End what type of elements.



	}  // Next input element.



	// Put the values together into a string.

	var query_string = values.join( "&" );



	// Get the pieces of the request URL.

	var pieces = this.request_url.match( /(\S*)\?(\S*)/ );



	// If the request_url does not have a ? in it, get rid of the query values from

	// the request URL, and add it to the query string.

	if ( pieces != null )

	{



	  this.modified_request_url = pieces[1];

		query_string              = pieces[2] + "&" + query_string;



	}

	else

	{



		this.modified_request_url = this.request_url;



	}



	// Return the query string.

	return query_string;



}





//------------------------------------------------------------------------

// Package: Animation

// For animating elements in the DOM in various ways.

//

// Topic: Dependencies

// - <Events Handler>

// - <Structures>

// - <DOM Library>

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Class: LW_Animation_Controller

// Stores the animations parameters.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Animation_Controller()

// Class constructor. You don't need to instantiate this as it is used by

// the system.

//------------------------------------------------------------------------

function LW_Animation_Controller()

{



	// Store the default values.

	this.move              = { to:    new LW_Structure_Point(),

	                           by:    new LW_Structure_Point(),

							   ease:  LW_Animation.EASE_NONE };



	this.width             = { to:    null,

	                           by:    null,

							   ease:  LW_Animation.EASE_NONE };



	this.height            = { to:    null,

	                           by:    null,

							   ease:  LW_Animation.EASE_NONE };



	this.opacity           = { to:    null,

	                           by:    null,

							   ease:  LW_Animation.EASE_NONE };



	this.background_color  = { to:    new LW_Structure_Color(),

	                           by:    new LW_Structure_Color() };



	this.border_color      = { to:    new LW_Structure_Color(),

	                           by:    new LW_Structure_Color() };



	this.text_color        = { to:    new LW_Structure_Color(),

	                           by:    new LW_Structure_Color() };



	this.delay             = 0;



}





//------------------------------------------------------------------------

// Class: LW_Animation

// Holds a single animation for an element and the necessary methods to

// handle it.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Class Constants

//------------------------------------------------------------------------

LW_Animation.EASE_NONE          = 0;

LW_Animation.EASE_IN            = 1;

LW_Animation.EASE_OUT           = 2;

LW_Animation.EASE_BOTH          = 3;

LW_Animation.STRONG_EASE_IN     = 4;

LW_Animation.STRONG_EASE_OUT    = 5;

LW_Animation.STRONG_EASE_BOTH   = 6;

LW_Animation.BACK_EASE_IN       = 7;

LW_Animation.BACK_EASE_OUT      = 8;

LW_Animation.BACK_EASE_BOTH     = 9;

LW_Animation.BOUNCE_EASE_IN     = 10;

LW_Animation.BOUNCE_EASE_OUT    = 11;

LW_Animation.BOUNCE_EASE_BOTH   = 12;

LW_Animation.ELASTIC_EASE_IN    = 13;

LW_Animation.ELASTIC_EASE_OUT   = 14;

LW_Animation.ELASTIC_EASE_BOTH  = 15;





//------------------------------------------------------------------------

// Public Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Animation()

// Class constructor.

//

// Parameters:

//     element - The DOM element that you'd like to animate.

//     run_time - The length that you'd like the animation to run.

//------------------------------------------------------------------------

function LW_Animation( element, run_time )

{



	// Store the values.

	this.element               = element;

	this.element_properties    = Object();

	this.run_time              = run_time;

	this.controller            = new LW_Animation_Controller();



	this.onStart               = null;

	this.onInterval            = null;

	this.onAdvance             = null;

	this.onEventFrame          = null;

	this.onStop                = null;

	this.onFinish              = null;



	// These values are used by the LW_Animation system internally.

	this.status                = false;

	this.event_frames          = new Array();



	this.start_time            = null;

	this.current_time          = null;



	this.begin_width           = null;

	this.begin_height          = null;

	this.begin_pos             = null;

	this.begin_back_color      = null;

	this.begin_border_color    = null;

	this.begin_text_color      = null;

	this.begin_opacity         = null;



	this.offset_width          = null;

	this.offset_height         = null;

	this.offset_pos            = new LW_Structure_Point();

	this.offset_back_color     = new LW_Structure_Color();

	this.offset_border_color   = new LW_Structure_Color();

	this.offset_text_color     = new LW_Structure_Color();

	this.offset_opacity        = null;



	this.desired_width         = null;

	this.desired_height        = null;

	this.desired_pos           = new LW_Structure_Point();

	this.desired_back_color    = null;

	this.desired_border_color  = null;

	this.desired_text_color    = null;

	this.desired_opacity       = null;



}





//------------------------------------------------------------------------

// Function: addEventFrame()

// Adds an event frame to the animation.

//

// Parameters:

//      time_offset - The offset from the beginning of the animation that

//                    you'd like this event to be fired.

//      event_func - The function that you'd like to execute.

//------------------------------------------------------------------------

LW_Animation.prototype.addEventFrame = function( time_offset, event_func )

{



  // Add the new event frame to the animation.

	this.event_frames.push( { time_offset: time_offset, event_func: event_func, triggered: false } );



}





//------------------------------------------------------------------------

// Function: start()

// Sets the animation to start playing.

//------------------------------------------------------------------------

LW_Animation.prototype.start = function()

{



	// Don't start if in the middle of playing.

	if ( this.status ) return;



	// Set the necessary values.

	this.status      = true;

	this.start_time  = new Date();



	// Width.

	if ( this.controller.width.to != null || this.controller.width.by != null )

	{



		// Get the element's width.

		this.begin_width = LW_DOM_Library.getWidth( this.element );



		// Get the desired width and offset width.

		this.desired_width  = (this.controller.width.to != null) ? (this.controller.width.to) : (this.begin_width + this.controller.width.by);

		this.offset_width   = this.desired_width - this.begin_width;



	}



	// Height.

	if ( this.controller.height.to != null || this.controller.height.by != null )

	{



		// Get the element's height.

		this.begin_height = LW_DOM_Library.getHeight( this.element );



		// Get the desired height and offset height.

		this.desired_height  = (this.controller.height.to != null) ? (this.controller.height.to) : (this.begin_height + this.controller.height.by);

		this.offset_height   = this.desired_height - this.begin_height;



	}



	// Position.

	if ( this.controller.move.to.X != null || this.controller.move.to.Y != null || this.controller.move.by.X != null || this.controller.move.by.Y != null )

	{



		// Get the element's position.

		this.begin_pos = LW_DOM_Library.getXY( this.element );



		// Get the desired X position and X offset position.

		if ( this.controller.move.to.X != null || this.controller.move.by.X != null )

		{



			this.desired_pos.X  = (this.controller.move.to.X != null) ? (this.controller.move.to.X) : (this.begin_pos.X + this.controller.move.by.X);

			this.offset_pos.X   = this.desired_pos.X - this.begin_pos.X;



		}



		// Get the desired Y position and Y offset position.

		if ( this.controller.move.to.Y != null || this.controller.move.by.Y != null )

		{



			this.desired_pos.Y  = (this.controller.move.to.Y != null) ? (this.controller.move.to.Y) : (this.begin_pos.Y + this.controller.move.by.Y);

			this.offset_pos.Y   = this.desired_pos.Y - this.begin_pos.Y;



		}



	}



	// Opacity.

	if ( this.controller.opacity.to != null || this.controller.opacity.by != null )

	{



		// Get the element's opacity.

		this.begin_opacity = LW_DOM_Library.getStyle( this.element, "opacity" );



		// Get the desired opacity and offset opacity.

		this.desired_opacity  = (this.controller.opacity.to != null) ? (this.controller.opacity.to) : (this.begin_opacity + this.controller.opacity.by);

		this.offset_opacity   = this.desired_opacity - this.begin_opacity;



	}



	// Background Color.

	if ( this.controller.background_color.to.R != null ||

	     this.controller.background_color.to.G != null ||

		 this.controller.background_color.to.B != null ||

	     this.controller.background_color.by.R != null ||

		 this.controller.background_color.by.G != null ||

		 this.controller.background_color.by.B != null )

	{



		// Get the element's background color.

		this.begin_back_color   = new LW_Structure_Color( LW_DOM_Library.getStyle( this.element, "backgroundColor" ).substring( 1 ) );

		this.desired_back_color = new LW_Structure_Color( this.begin_back_color.toHexString() );



		// Get the desired red value and offset value.

		if ( this.controller.background_color.to.R != null || this.controller.background_color.by.R != null )

		{



			this.desired_back_color.R  = (this.controller.background_color.to.R != null) ? (this.controller.background_color.to.R) : (this.begin_back_color.R + this.controller.background_color.by.R);

			this.offset_back_color.R   = this.desired_back_color.R - this.begin_back_color.R;



		}



		// Get the desired green value and offset value.

		if ( this.controller.background_color.to.G != null || this.controller.background_color.by.G != null )

		{



			this.desired_back_color.G  = (this.controller.background_color.to.G != null) ? (this.controller.background_color.to.G) : (this.begin_back_color.G + this.controller.background_color.by.G);

			this.offset_back_color.G   = this.desired_back_color.G - this.begin_back_color.G;



		}



		// Get the desired blue value and offset value.

		if ( this.controller.background_color.to.B != null || this.controller.background_color.by.B != null )

		{



			this.desired_back_color.B  = (this.controller.background_color.to.B != null) ? (this.controller.background_color.to.B) : (this.begin_back_color.B + this.controller.background_color.by.B);

			this.offset_back_color.B   = this.desired_back_color.B - this.begin_back_color.B;



		}



	}



	// Border Color.

	if ( this.controller.border_color.to.R != null ||

	     this.controller.border_color.to.G != null ||

		 this.controller.border_color.to.B != null ||

	     this.controller.border_color.by.R != null ||

		 this.controller.border_color.by.G != null ||

		 this.controller.border_color.by.B != null )

	{



		// Get the element's border color.

		this.begin_border_color   = new LW_Structure_Color( LW_DOM_Library.getStyle( this.element, "borderColor" ).substring( 1 ) );

		this.desired_border_color = new LW_Structure_Color( this.begin_border_color.toHexString() );



		// Get the desired red value and offset value.

		if ( this.controller.border_color.to.R != null || this.controller.border_color.by.R != null )

		{



			this.desired_border_color.R  = (this.controller.border_color.to.R != null) ? (this.controller.border_color.to.R) : (this.begin_border_color.R + this.controller.border_color.by.R);

			this.offset_border_color.R   = this.desired_border_color.R - this.begin_border_color.R;



		}



		// Get the desired green value and offset value.

		if ( this.controller.border_color.to.G != null || this.controller.border_color.by.G != null )

		{



			this.desired_border_color.G  = (this.controller.border_color.to.G != null) ? (this.controller.border_color.to.G) : (this.begin_border_color.G + this.controller.border_color.by.G);

			this.offset_border_color.G   = this.desired_border_color.G - this.begin_border_color.G;



		}



		// Get the desired blue value and offset value.

		if ( this.controller.border_color.to.B != null || this.controller.border_color.by.B != null )

		{



			this.desired_border_color.B  = (this.controller.border_color.to.B != null) ? (this.controller.border_color.to.B) : (this.begin_border_color.B + this.controller.border_color.by.B);

			this.offset_border_color.B   = this.desired_border_color.B - this.begin_border_color.B;



		}



	}



	// Text Color.

	if ( this.controller.text_color.to.R != null ||

	     this.controller.text_color.to.G != null ||

		 this.controller.text_color.to.B != null ||

	     this.controller.text_color.by.R != null ||

		 this.controller.text_color.by.G != null ||

		 this.controller.text_color.by.B != null )

	{



		// Get the element's text color.

		this.begin_text_color   = new LW_Structure_Color( LW_DOM_Library.getStyle( this.element, "color" ).substring( 1 ) );

		this.desired_text_color = new LW_Structure_Color( this.begin_text_color.toHexString() );



		// Get the desired red value and offset value.

		if ( this.controller.text_color.to.R != null || this.controller.text_color.by.R != null )

		{



			this.desired_text_color.R  = (this.controller.text_color.to.R != null) ? (this.controller.text_color.to.R) : (this.begin_text_color.R + this.controller.text_color.by.R);

			this.offset_text_color.R   = this.desired_text_color.R - this.begin_text_color.R;



		}



		// Get the desired green value and offset value.

		if ( this.controller.text_color.to.G != null || this.controller.text_color.by.G != null )

		{



			this.desired_text_color.G  = (this.controller.text_color.to.G != null) ? (this.controller.text_color.to.G) : (this.begin_text_color.G + this.controller.text_color.by.G);

			this.offset_text_color.G   = this.desired_text_color.G - this.begin_text_color.G;



		}



		// Get the desired blue value and offset value.

		if ( this.controller.text_color.to.B != null || this.controller.text_color.by.B != null )

		{



			this.desired_text_color.B  = (this.controller.text_color.to.B != null) ? (this.controller.text_color.to.B) : (this.begin_text_color.B + this.controller.text_color.by.B);

			this.offset_text_color.B   = this.desired_text_color.B - this.begin_text_color.B;



		}



	}



	// Call the onStart function if there is any.

	if ( this.onStart != null )

	  this.onStart( this );



	// Call the incrementAnimation function. It will start the animation.

	LW_Animation_Manager.addAnimation( this );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceWidth()

// Advances the width.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceWidth = function()

{



	// Get the new width.

	var new_width = LW_Animation.tweenValue( this.controller.width.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_width, this.offset_width );



	// Bounds.

	new_width = Math.max( new_width, 0 );



	// Set the new width on the element.

	LW_DOM_Library.setStyle( this.element, "width", new_width + "px" );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceHeight()

// Advances the height.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceHeight = function()

{



	// Get the new height.

	var new_height = LW_Animation.tweenValue( this.controller.height.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_height, this.offset_height );



	// Bounds.

	new_height = Math.max( new_height, 0 );



	// Set the new height on the element.

	LW_DOM_Library.setStyle( this.element, "height", new_height + "px" );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advancePosition()

// Advances the position.

//------------------------------------------------------------------------

LW_Animation.prototype.advancePosition = function()

{



	// Updating X position?

	if ( this.offset_pos.X != null )

	{



		// Get the new X position.

		var new_X_pos = LW_Animation.tweenValue( this.controller.move.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_pos.X, this.offset_pos.X );



		// Bounds.

		new_X_pos = Math.max( new_X_pos, 0 );



		// Set the new X position on the element.

		LW_DOM_Library.setX( this.element, new_X_pos );



	}  // End if updating X position.



	// Updating Y position?

	if ( this.offset_pos.Y != null )

	{



		// Get the new Y position.

		var new_Y_pos = LW_Animation.tweenValue( this.controller.move.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_pos.Y, this.offset_pos.Y );



		// Bounds.

		new_Y_pos = Math.max( new_Y_pos, 0 );



		// Set the new Y position on the element.

		LW_DOM_Library.setY( this.element, new_Y_pos );



	}  // End if updating Y position.



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceOpacity()

// Advances the opacity.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceOpacity = function()

{



	// Get the new opacity.

	var new_opacity = (LW_Animation.tweenValue( this.controller.opacity.ease, (this.current_time - this.controller.delay), this.run_time, (this.begin_opacity * 100), (this.offset_opacity * 100) ) / 100);



	// Bounds.

	new_opacity = Math.min( Math.max( new_opacity, 0 ), 1 );



	// Set the new opacity on the element.

	LW_DOM_Library.setStyle( this.element, "opacity", new_opacity );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceBackgroundColor()

// Advances the background color.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceBackgroundColor = function()

{



	// Set the new back color as the beginning color.

	var new_back_color = new LW_Structure_Color( this.begin_back_color.toHexString() );



	// Updating red value?

	if ( this.offset_back_color.R != null )

	{



		// Get the new background color.

		new_back_color.R = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.R, this.offset_back_color.R ) );



	}  // End if updating red value.



	// Updating green value?

	if ( this.offset_back_color.G != null )

	{



		// Get the new background color.

		new_back_color.G = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.G, this.offset_back_color.G ) );



	}  // End if updating red value.



	// Updating blue value?

	if ( this.offset_back_color.B != null )

	{



		// Get the new background color.

		new_back_color.B = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.B, this.offset_back_color.B ) );



	}  // End if updating red value.



	// Bounds.

	new_back_color.R = Math.min( Math.max( new_back_color.R, 0 ), 255 );

	new_back_color.G = Math.min( Math.max( new_back_color.G, 0 ), 255 );

	new_back_color.B = Math.min( Math.max( new_back_color.B, 0 ), 255 );



	// Set the new background color on the element.

	LW_DOM_Library.setStyle( this.element, "backgroundColor", "#" + new_back_color.toHexString() );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceBorderColor()

// Advances the border color.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceBorderColor = function()

{



	// Set the new border color as the beginning color.

	var new_border_color = new LW_Structure_Color( this.begin_border_color.toHexString() );



	// Updating red value?

	if ( this.offset_border_color.R != null )

	{



		// Get the new border color.

		new_border_color.R = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.R, this.offset_border_color.R ) );



	}  // End if updating red value.



	// Updating green value?

	if ( this.offset_back_color.G != null )

	{



		// Get the new border color.

		new_border_color.G = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.G, this.offset_border_color.G ) );



	}  // End if updating red value.



	// Updating blue value?

	if ( this.offset_border_color.B != null )

	{



		// Get the new border color.

		new_border_color.B = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.B, this.offset_border_color.B ) );



	}  // End if updating red value.



	// Bounds.

	new_border_color.R = Math.min( Math.max( new_border_color.R, 0 ), 255 );

	new_border_color.G = Math.min( Math.max( new_border_color.G, 0 ), 255 );

	new_border_color.B = Math.min( Math.max( new_border_color.B, 0 ), 255 );



	// Set the new border color on the element.

	LW_DOM_Library.setStyle( this.element, "borderColor", "#" + new_border_color.toHexString() );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceTextColor()

// Advances the text color.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceTextColor = function()

{



	// Set the new text color as the beginning color.

	var new_text_color = new LW_Structure_Color( this.begin_text_color.toHexString() );



	// Updating red value?

	if ( this.offset_text_color.R != null )

	{



		// Get the new text color.

		new_text_color.R = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.R, this.offset_text_color.R ) );



	}  // End if updating red value.



	// Updating green value?

	if ( this.offset_text_color.G != null )

	{



		// Get the new text color.

		new_text_color.G = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.G, this.offset_text_color.G ) );



	}  // End if updating red value.



	// Updating blue value?

	if ( this.offset_text_color.B != null )

	{



		// Get the new text color.

		new_text_color.B = Math.ceil( LW_Animation.tweenValue( LW_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.B, this.offset_text_color.B ) );



	}  // End if updating red value.



	// Bounds.

	new_text_color.R = Math.min( Math.max( new_text_color.R, 0 ), 255 );

	new_text_color.G = Math.min( Math.max( new_text_color.G, 0 ), 255 );

	new_text_color.B = Math.min( Math.max( new_text_color.B, 0 ), 255 );



	// Set the new text color on the element.

	LW_DOM_Library.setStyle( this.element, "color", "#" + new_text_color.toHexString() );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: advanceFrame()

// Carries out the next frame of the animation.

//------------------------------------------------------------------------

LW_Animation.prototype.advanceFrame = function()

{



	// If the animation is stopped, return false.

	if ( !this.status )

	  return false;



	// Update the current time.

	this.current_time = new Date() - this.start_time;



	// Only start incrementing if we have passed the delay time.

	if ( this.current_time > this.controller.delay )

	{



		// Animating width?

		if ( this.desired_width != null )

			this.advanceWidth();



		// Animating height?

		if ( this.desired_height != null )

			this.advanceHeight();



		// Animating position?

		if ( this.desired_pos.X != null || this.desired_pos.Y != null )

			this.advancePosition();



		// Animating opacity?

		if ( this.desired_opacity != null )

			this.advanceOpacity();



		// Animating background color?

		if ( this.desired_back_color != null )

			this.advanceBackgroundColor();



		// Animating border color?

		if ( this.desired_border_color != null )

			this.advanceBorderColor();



		// Animating text color?

		if ( this.desired_text_color != null )

			this.advanceTextColor();



		// Loop through each event frame.

		var event_triggered = false;

		for ( var i = 0; i < this.event_frames.length; i++ )

		{



			// If it is time (or passed time) to trigger the event, do so.

			if ( !this.event_frames[i].triggered && this.current_time >= this.controller.delay + this.event_frames[i].time_offset )

			{



			  this.event_frames[i].event_func( this );

				this.event_frames[i].triggered = true;

				event_triggered = true;



			}



		}  // Next event frame.



		// If an event was triggered and there's an onEventFrame function, call it.

		if ( event_triggered && this.onEventFrame )

		  this.onEventFrame( this );



		// Call the onAdvance function if there is any.

		if ( this.onAdvance )

			this.onAdvance( this );



	}  // End if delay is over.



	// Call the onAdvance function if there is any.

	if ( this.onInterval )

		this.onInterval( this );



	// Should we continue processing?

	if ( this.current_time < this.run_time + this.controller.delay )

		return true;

	else

		return false;



}





//------------------------------------------------------------------------

// Function: stop()

// Stops the animation where it currently is. Does not finish it.

//------------------------------------------------------------------------

LW_Animation.prototype.stop = function()

{



	// Set the animation's status to not playing.

	this.status = false;



	// Call the onStop function if there is any.

	if ( this.onStop )

	  this.onStop( this );



}





//------------------------------------------------------------------------

// (Exclude)

// Function: finish()

// Does the required clean up of the animation.

//------------------------------------------------------------------------

LW_Animation.prototype.finish = function()

{



	// Set the animation's status to not playing.

	this.status = false;



	// Get rid of any animation errors. Set the desired values on the elements.

	if ( this.desired_width        ) LW_DOM_Library.setStyle( this.element, "width", this.desired_width + "px" );

	if ( this.desired_height       ) LW_DOM_Library.setStyle( this.element, "height", this.desired_height + "px" );



	if ( this.desired_pos.X        ) LW_DOM_Library.setX( this.element, this.desired_pos.X );

	if ( this.desired_pos.Y        ) LW_DOM_Library.setY( this.element, this.desired_pos.Y );



	if ( this.desired_opacity      ) LW_DOM_Library.setStyle( this.element, "opacity", this.desired_opacity );



	if ( this.desired_back_color   ) LW_DOM_Library.setStyle( this.element, "backgroundColor", "#" + this.desired_back_color.toHexString() );



	if ( this.desired_border_color ) LW_DOM_Library.setStyle( this.element, "borderColor", "#" + this.desired_border_color.toHexString() );



	if ( this.desired_text_color   ) LW_DOM_Library.setStyle( this.element, "color", "#" + this.desired_text_color.toHexString() );



	// Call the onFinish function if there is any.

	if ( this.onFinish )

	  this.onFinish( this );



}





//------------------------------------------------------------------------

// Public Static Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// (Exclude)

// Function: tweenValue()

// Tweens the value.

//------------------------------------------------------------------------

LW_Animation.tweenValue = function( ease_type, current_time, duration, begin_val, change_val )

{



	// What easing equation?

	switch( ease_type )

	{



	// EASE NONE

	case LW_Animation.EASE_NONE:

		return change_val * (current_time / duration) + begin_val;



	// EASE IN

	case LW_Animation.EASE_IN:

		return change_val * (current_time /= duration) * current_time + begin_val;



	// EASE OUT

	case LW_Animation.EASE_OUT:

		return -change_val * (current_time /= duration) * (current_time - 2) + begin_val;



	// EASE BOTH

	case LW_Animation.EASE_BOTH:



		if ( (current_time /= duration / 2) < 1 )

			return change_val / 2 * current_time * current_time + begin_val;



		return -change_val / 2 * ((--current_time) * (current_time - 2) - 1) + begin_val;



	// STRONG EASE IN

	case LW_Animation.STRONG_EASE_IN:

		return change_val * (current_time /= duration) * current_time * current_time * current_time + begin_val;



	// STRONG EASE OUT

	case LW_Animation.STRONG_EASE_OUT:

		return -change_val * ((current_time = current_time / duration - 1) * current_time * current_time * current_time - 1) + begin_val;



	// STRONG EASE BOTH

	case LW_Animation.STRONG_EASE_BOTH:



		if ( (current_time /= duration / 2) < 1 )

			return change_val / 2 * current_time * current_time * current_time * current_time + begin_val;



		return -change_val / 2 * ((current_time -= 2) * current_time * current_time * current_time - 2) + begin_val;



	// BACK EASE IN

	case LW_Animation.BACK_EASE_IN:

		return change_val * (current_time /= duration) * current_time * (2.70158 * current_time - 1.70158) + begin_val;



	// BACK EASE OUT

	case LW_Animation.BACK_EASE_OUT:

		return change_val * ((current_time = current_time / duration - 1) * current_time * (2.70158 * current_time + 1.70158) + 1) + begin_val;



	// BACK EASE BOTH

	case LW_Animation.BACK_EASE_BOTH:



		if ( (current_time /= duration / 2) < 1 )

			return change_val / 2 * (current_time * current_time * (3.5949095 * current_time - 2.5949095)) + begin_val;



		return change_val / 2 * ((current_time -= 2) * current_time * (3.5949095 * current_time + 2.5949095) + 2) + begin_val;



	// BOUNCE EASE IN

	case LW_Animation.BOUNCE_EASE_IN:



		current_time = duration - current_time;



		if ( (current_time /= duration) < (1 / 2.75) )

			return change_val - (change_val * (7.5625 * current_time * current_time)) + begin_val;

		else if ( current_time < (2 / 2.75 ) )

			return change_val - (change_val * (7.5625 * (current_time -= (1.5 / 2.75)) * current_time + 0.75)) + begin_val;

		else if ( current_time < (2.5 / 2.75) )

			return change_val - (change_val * (7.5625 * (current_time -= (2.25 / 2.75)) * current_time + 0.9375)) + begin_val;

		else

			return change_val - (change_val * (7.5625 * (current_time -= (2.625 / 2.75)) * current_time + 0.984375)) + begin_val;



	// BOUNCE EASE OUT

	case LW_Animation.BOUNCE_EASE_OUT:



		if ( (current_time /= duration) < (1 / 2.75) )

		  return change_val * (7.5625 * current_time * current_time) + begin_val;

		else if ( current_time < (2 / 2.75 ) )

		  return change_val * (7.5625 * (current_time -= (1.5 / 2.75)) * current_time + 0.75) + begin_val;

		else if ( current_time < (2.5 / 2.75) )

		  return change_val * (7.5625 * (current_time -= (2.25 / 2.75)) * current_time + 0.9375) + begin_val;

		else

		  return change_val * (7.5625 * (current_time -= (2.625 / 2.75)) * current_time + 0.984375) + begin_val;



	// BOUNCE EASE BOTH

	case LW_Animation.BOUNCE_EASE_BOTH:



		if ( current_time < duration / 2 )

		{



			current_time = duration - (current_time * 2);



			if ( (current_time /= duration) < (1 / 2.75) )

				return (change_val - (change_val * (7.5625 * current_time * current_time))) * 0.5 + begin_val;

			else if ( current_time < (2 / 2.75 ) )

				return (change_val - (change_val * (7.5625 * (current_time -= (1.5 / 2.75)) * current_time + 0.75))) * 0.5 + begin_val;

			else if ( current_time < (2.5 / 2.75) )

				return (change_val - (change_val * (7.5625 * (current_time -= (2.25 / 2.75)) * current_time + 0.9375))) * 0.5 + begin_val;

			else

				return (change_val - (change_val * (7.5625 * (current_time -= (2.625 / 2.75)) * current_time + 0.984375))) * 0.5 + begin_val;



		}



		current_time = current_time * 2 - duration;



		if ( (current_time /= duration) < (1 / 2.75) )

		  return change_val * (7.5625 * current_time * current_time) * 0.5 + change_val * 0.5 + begin_val;

		else if ( current_time < (2 / 2.75 ) )

		  return change_val * (7.5625 * (current_time -= (1.5 / 2.75)) * current_time + 0.75) * 0.5 + change_val * 0.5 + begin_val;

		else if ( current_time < (2.5 / 2.75) )

		  return change_val * (7.5625 * (current_time -= (2.25 / 2.75)) * current_time + 0.9375) * 0.5 + change_val * 0.5 + begin_val;

		else

		  return change_val * (7.5625 * (current_time -= (2.625 / 2.75)) * current_time + 0.984375) * 0.5 + change_val * 0.5 + begin_val;



	// ELASTIC EASE IN

	case LW_Animation.ELASTIC_EASE_IN:



		if ( current_time == 0 )

			return begin_val;



		if ( (current_time /= duration) == 1 )

			return begin_val + change_val;



		var p = duration * 0.3;

		var a = change_val;

		var s = p / 4;



		return -(a * Math.pow( 2, 10 * (current_time -= 1) ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p )) + begin_val;



	// ELASTIC EASE OUT

	case LW_Animation.ELASTIC_EASE_OUT:



		if ( current_time == 0 )

			return begin_val;



		if ( (current_time /= duration) == 1 )

			return begin_val + change_val;



		var p = duration * 0.3;

		var a = change_val;

		var s = p / 4;



		return a * Math.pow( 2, -10 * current_time ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p ) + change_val + begin_val;



	// ELASTIC EASE BOTH

	case LW_Animation.ELASTIC_EASE_BOTH:



		if ( current_time == 0 )

			return begin_val;



		if ( (current_time /= duration / 2) == 2 )

			return begin_val + change_val;



		var p = duration * (0.3 * 1.5);

		var a = change_val;

		var s = p / 4;



		if ( current_time < 1 ) return -0.5 * (a * Math.pow( 2, 10 * (current_time -= 1) ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p )) + begin_val;



		return a * Math.pow( 2, -10 * (current_time -= 1) ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p ) * 0.5 + change_val + begin_val;



	}



}





//------------------------------------------------------------------------

// Class: LW_Animation_Sequence

// Stores a sequence of <LW_Animation> objects.

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Static Variables

//------------------------------------------------------------------------

LW_Animation_Sequence.sequences = new Array();





//------------------------------------------------------------------------

// Public Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// Constructor: LW_Animation_Sequence()

// Class constructor.

//

// Parameters:

//     options - An optional object of options for the Animation Sequence.

//------------------------------------------------------------------------

function LW_Animation_Sequence( options )

{



	// Store the default values.

	this.animations               = new Array();

	this.current_animation_index  = 0;

	this.sequence_index           = LW_Animation_Sequence.sequences.length;

	this.status                   = false;

	this.options                  = options;



	// Callbacks.

	this.onStart               = null;

	this.onAdvance             = null;

	this.onLoop                = null;

	this.onFinish              = null;



	// Store this animation sequence in the global sequences array.

	LW_Animation_Sequence.sequences[this.sequence_index] = this;



}





//------------------------------------------------------------------------

// Function: addAnimation()

// Adds an <LW_Animation> object to the animation sequence.

//

// Parameters:

//     animation - An <LW_Animation> object that you would like to set up

//                 to play in the animation. Will add it at the end of the

//                 sequence.

//------------------------------------------------------------------------

LW_Animation_Sequence.prototype.addAnimation = function( animation )

{



	// Store the animation in the sequence.

	this.animations.push( animation );



	// Add the onFinish and onStop functions.

	LW_Events_Handler.addEvent( animation, "onFinish", LW_Animation_Sequence.nextAnimation );

	LW_Events_Handler.addEvent( animation, "onStop", LW_Animation_Sequence.nextAnimation );



	// Store the sequence index in the animation.

	animation.sequence_index = this.sequence_index;



}





//------------------------------------------------------------------------

// Function: start()

// Sets the Animation Sequence to start playing.

//------------------------------------------------------------------------

LW_Animation_Sequence.prototype.start = function()

{



	// Only start if there is at least one animation in the sequence

	// and we are not already playing.

	if ( this.animations.length == 0 || this.status ) return;



	// Set as playing.

	this.status = true;



	// On start callback.

	if ( this.onStart != null && this.onStart( this ) == false )

		return;



	// Start the first animation in the sequence.

	this.animations[0].start();



}





//------------------------------------------------------------------------

// (Exclude)

// Function: reset()

// Cleans up the animation sequence.

//------------------------------------------------------------------------

LW_Animation_Sequence.prototype.reset = function()

{



	// Reset the values.

	this.status                   = false;

	this.current_animation_index  = 0;



}





//------------------------------------------------------------------------

// Public Static Member Functions

//------------------------------------------------------------------------



//------------------------------------------------------------------------

// (Exclude)

// Function: nextAnimation()

// Plays the next animation in the sequence. This is set as the previous

// animation's onFinish function so that a chain forms.

//------------------------------------------------------------------------

LW_Animation_Sequence.nextAnimation = function( animation )

{



	// Get the animation sequence.

	var animation_sequence = LW_Animation_Sequence.sequences[animation.sequence_index];



	// If the animation sequence is stopped, return false.

	if ( !animation_sequence.status )

	  return false;



	// Increment the current animation index.

	animation_sequence.current_animation_index++;



	// Play the next animation if there is one.

	if ( animation_sequence.animations[animation_sequence.current_animation_index] != null )

	{



		// On advance callback.

		if ( animation_sequence.onAdvance != null && animation_sequence.onAdvance( animation_sequence ) == false )

			return;



		// Start the next animation.

		animation_sequence.animations[animation_sequence.current_animation_index].start();



	}  // End if next animation.

	else

	{



		// Should we loop?

		if ( animation_sequence.options && animation_sequence.options.loop == true )

		{



			// On loop callback.

			if ( animation_sequence.onLoop != null && animation_sequence.onLoop( animation_sequence ) == false )

				return;



			// Loop.

			animation_sequence.reset();

			animation_sequence.start();



		}  // End if looping.

		else

		{



			// Finish the animation sequence.

			animation_sequence.reset();



			// On finish callback.

			if ( animation_sequence.onFinish != null )

				animation_sequence.onFinish( animation_sequence );



		}



	}  // End if no more animations.



}





//------------------------------------------------------------------------

// (Exclude)

// Class: LW_Animation_Manager

// Manages each animation. All the animations are incremented through the

// manager.

//------------------------------------------------------------------------

LW_Animation_Manager =

{



	//----------------------------------------------------------------------

	// Public Variables

	//----------------------------------------------------------------------

	increment_speed:     20,           // The speed at which the animation manager will increment each animation.

	playing_animations:  new Array(),  // An array of all the currently playing animations.

	interval_handle:     null,         // The handle that the setInterval() function returns.





	//----------------------------------------------------------------------

	// Public Member Functions

	//----------------------------------------------------------------------

	//----------------------------------------------------------------------

	// (Exclude)

	// Function: addAnimation()

	// This function is used to add an animation to the animation manager

	// for playing.

	//----------------------------------------------------------------------

	addAnimation: function( animation )

	{



		// Loop through each animation being played.

		for ( var i = 0; i < this.playing_animations.length; i++ )

		{



			// Get the animation.

			var playing_animation = this.playing_animations[i];



			// Is the animation we're adding animate the

			// same element than this animation's element?

			if ( animation.element == playing_animation.element )

			{



				// Remove the animation from the playing animations array.

				this.playing_animations.splice( i, 1 );



				// Stop the currently playing animation so that we can play this one.

				playing_animation.stop();



			}  // End if managing the same element.



		}  // Next playing animation.



		// Add the animation to the playing animations array.

		this.playing_animations.push( animation );



		// If we don't have any animations playing,

		// we have to set up the timeout.

		if ( this.interval_handle == null )

		{



			// Set to advanced all animations.

			this.interval_handle = setInterval( LW_Animation_Manager.advanceAnimations, this.increment_speed, null );



		}  // End if no animations currently playing.



	},





	//------------------------------------------------------------------------

	// (Exclude)

	// Function: advanceAnimations()

	// Advances each animation being played.

	//------------------------------------------------------------------------

	advanceAnimations: function()

	{



		// Loop through each animation being played.

		for ( var i = 0; i < LW_Animation_Manager.playing_animations.length; i++ )

		{



			// Get the animation from the array.

			var animation = LW_Animation_Manager.playing_animations[i];



			// Advance the animation.

			var continue_playing = animation.advanceFrame();



			// Is the animation done playing?

			if ( !continue_playing )

			{



				// Remove the animation from the playing animations array.

				LW_Animation_Manager.playing_animations.splice( i, 1 );



				// Finish up the animation.

				animation.finish();



				// If we don't have any more animations to play, stop

				// JavaScript from calling this function again.

				if ( LW_Animation_Manager.playing_animations.length == 0 )

				{

					clearInterval( LW_Animation_Manager.interval_handle );

					LW_Animation_Manager.interval_handle = null;

				}



			}  // End if stop playing this animation.



		}  // Next playing animation.



	}



}







//-----------------------------------------------------------

// Function: toggleSection()

// Toggles whether a section is active or not.

//-----------------------------------------------------------

var toggleSection = function( section )

{



	if ( !toggleSection[section] )

	{



		// Set the section as active.

		toggleSection[section] = true;



		// Get the section's links.

		var section_links = document.getElementById( section + "_links" );



		// Set it as visible.

		LW_DOM_Library.setStyle( section_links, "display", "block" );



		// If we don't have this section's original height, get it.

		if ( !toggleSection.heights[section] )

			toggleSection.heights[section] = LW_DOM_Library.getHeight( section_links );



		// Set the height to nothing so we can expand it with an animation.

		LW_DOM_Library.setStyle( section_links, "height", "0px" );



		var anim = new LW_Animation( section_links, 1000 );

		anim.controller.height.to = toggleSection.heights[section];

		anim.controller.height.ease = LW_Animation.STRONG_EASE_IN;

		anim.start();



		// Let's switch the + to a -

		var switcher = document.getElementById( section + "_switcher" );

		switcher.innerHTML = "-" + switcher.innerHTML.substring( 1 );



	}

	else

	{



		// Set the section as active.

		toggleSection[section] = false;



		var section_links = document.getElementById( section + "_links" );



		// If we don't have this section's original height, get it.

		if ( !toggleSection.heights[section] )

			toggleSection.heights[section] = LW_DOM_Library.getHeight( section_links );



		var anim = new LW_Animation( section_links, 1000 );

		anim.controller.height.to = 1;

		anim.onFinish = function(){ LW_DOM_Library.setStyle( section_links, "height", toggleSection.heights[section] + "px" );

		                            LW_DOM_Library.setStyle( section_links, "display", "none" ); };

		anim.start();



		// Let's switch the - to a +

		var switcher = document.getElementById( section + "_switcher" );

		switcher.innerHTML = "+" + switcher.innerHTML.substring( 1 );



	}



}



toggleSection.education = false;

toggleSection.retirement = false;

toggleSection.heights = {};


//------------------------------------------------------------------------
// Name: showAvatar()
//------------------------------------------------------------------------
function showAvatar()
{

	var elem_html = '<a class="avatar-close" href="" onclick="hideAvatar(); return false;">X CLOSE</a><iframe id="iframe-test" scrolling="no" src="' + LW_SITE_URL + '/avatar.php"></iframe>';

	// Create the enlarged image element.
	var enlarged_elem = $$( 'container' ).create( 'div', { id: 'enlarged' }, true, elem_html );
	$( enlarged_elem ).position( ($( document.body ).dimensions()[0] - 660) * 0.5, $( document.body ).scrollOffset()[1] + 20 );

	var body_dimensions = $( document.body ).dimensions();

	if ( body_dimensions[1] < $( window ).dimensions()[1] )
		body_dimensions[1] = $( window ).dimensions()[1];

	// Create the background element.
	var back_elem = $$( 'container' ).create( 'div', { id: 'background' }, true );
	back_elem.dimensions( body_dimensions[0], body_dimensions[1] );
	back_elem.setStyle( 'opacity', 0.5 );
	back_elem.position( 0, 0 );

}


//------------------------------------------------------------------------
// Name: hideAvatar()
//------------------------------------------------------------------------
function hideAvatar()
{

	// Remove the background and enlarged image elements.
	$$( 'background' ).remove();
	$$( 'enlarged' ).remove();

}





//-----------------------------------------------------------

// Function: onloadHandler()

// Executed when the document is finished loading.

//-----------------------------------------------------------

function onloadHandler()

{



	var inactive_menu = document.getElementById( "inactive_menu" );



	if ( inactive_menu )

		LW_DOM_Library.setStyle( inactive_menu, "opacity", 0.45 );



	var retirement_links = document.getElementById( "retirement_links" );

	var education_links = document.getElementById( "education_links" );



	if ( LW_DOM_Library.getStyle( retirement_links, "display" ) != "none" )

		toggleSection.retirement = true;



	if ( LW_DOM_Library.getStyle( education_links, "display" ) != "none" )

		toggleSection.education = true;



}



LW_Events_Handler.addEvent( window, "onload", onloadHandler );/**

 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/

 *

 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:

 * http://www.opensource.org/licenses/mit-license.php

 *

 */

if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObject.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObject.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObject.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObject;var SWFObject=deconcept.SWFObject;