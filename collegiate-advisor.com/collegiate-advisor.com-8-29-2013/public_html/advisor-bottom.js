// JavaScript Document

//------------------------------------------------------------------------
// Package: Structures
// The various structures used by the system.
// 
// Topic: Dependencies
// - <DOM Library>
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Class: Legato_Structure_Color
// Handles a single color.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Structure_Color()
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
function Legato_Structure_Color()
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
Legato_Structure_Color.prototype.toHexString = function()
{

	var R = (this.R < 16) ? "0" + this.R.toString( 16 ) : this.R.toString( 16 );
	var G = (this.G < 16) ? "0" + this.G.toString( 16 ) : this.G.toString( 16 );
	var B = (this.B < 16) ? "0" + this.B.toString( 16 ) : this.B.toString( 16 );

	return R + G + B;

}


//------------------------------------------------------------------------
// Class: Legato_Structure_Dimensions
// Handles a single set of dimensions.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Structure_Dimensions()
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
function Legato_Structure_Dimensions()
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
		
		var element = $( arguments[0] );

		// Get the element's dimensions.
		var dimensions = element.dimensions();
		this.width = dimensions[0];
		this.height = dimensions[1];
		
	}  // End if element passed in.

}


//------------------------------------------------------------------------
// Class: Legato_Structure_Position
// Handles a single position. For positioning objects relative to its
// containing element.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Structure_Position()
// Class constructor.
//
// Parameters:
//     top - The value from the top of the containing element.
//     right - The value from the right of the containing element.
//     bottom - The value from the bottom of the containing element.
//     left - The value from the left of the containing element.
//------------------------------------------------------------------------
function Legato_Structure_Position( top, right, bottom, left )
{

	// Store the passed in parameters.
	this.top     = top;
	this.right   = right;
	this.bottom  = bottom;
	this.left    = left;

}


//------------------------------------------------------------------------
// Class: Legato_Structure_Point
// Handles a single point.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Structure_Point()
// Class constructor.
//
// Parameters:
//     X - The X value of the point.
//     Y - The Y value of the point.
//------------------------------------------------------------------------
function Legato_Structure_Point( X, Y )
{

	// Store the passed in parameters.
	this.X = X;
	this.Y = Y;

}


//------------------------------------------------------------------------
// Class: Legato_Structure_Region
// Handles a single region.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Structure_Region()
// Class constructor.
//
// Parameters:
//     min_point - The <Legato_Structure_Point> of the top left corner.
//     max_point - The <Legato_Structure_Point> of the bottom right corner.
//
//     OR
//
//     element - A DOM element, in which case the region will be the
//               containing region of the element.
//------------------------------------------------------------------------
function Legato_Structure_Region()
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

		var element = $( arguments[0] );

		// Get the element's position for the region's min point.
		this.min_point = this.element.position();
		this.min_point = new Legato_Structure_Point( this.min_point[0], this.min_point[1] );

		// Get the max point for the region.
		var dimensions = element.dimensions();
		this.max_point = new Legato_Structure_Point( (this.min_point.X + dimensions[0]), (this.min_point.Y + dimensions[1]) );

	}  // End if HTML element passed in.

}


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Function: intersectRegion()
// Used to test if two <Legato_Structure_Region> objects are intersecting
// each other.
//
// Parameters:
//     region - The <Legato_Structure_Region> that you'd like to test for
//              intersection against this object.
//     in_test - (Optional) If this is set to true, the region passed in
//               must be fully contained by this object.
//     epsilon - (Optional) This is the allowable mistake that can happen
//               between the tests, eg: if this is 1, then if the region
//               is 1 pixel away from intersecting, this function will
//               still return true.
//------------------------------------------------------------------------
Legato_Structure_Region.prototype.intersectRegion = function( region, in_test, epsilon )
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
// Used to test if there is an <Legato_Structure_Point> object within the
// bounds of this region.
//
// Parameters:
//     point - The <Legato_Structure_Point> object.
//------------------------------------------------------------------------
Legato_Structure_Region.prototype.containsPoint = function( point )
{

	var contained = true;

	// Is the point completely contained in this region?
	if ( this.min_point.X > point.X || this.max_point.X < point.X ) contained = false;
	if ( this.min_point.Y > point.Y || this.max_point.Y < point.Y ) contained = false;

	// Return.
	return contained;

}


//------------------------------------------------------------------------
// Name: Legato_Debug_Debugger
// Desc: Works with the PHP Legato_Debug_Debugger class to display debugging
//       information.
//------------------------------------------------------------------------
Legato_Debug_Debugger =
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
		Legato_Debug_Debugger.system_settings[category].push( { setting: setting,
		                                                    value:   value } );

	},
	

	//----------------------------------------------------------------------
	// Name: addItem()
	// Desc: Adds a debug item.
	//----------------------------------------------------------------------
	addItem: function( message, error_code, file, line, php_class, php_function, type )
	{

		// Add the debug item.
		Legato_Debug_Debugger.debug_items.push( { message:       message,
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
		var debug_item = Legato_Debug_Debugger.debug_items[id];

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
		Legato_Debug_Debugger.queries.push( { query:  query,
																	    file:   file,
																	    line:   line } );

	},


	//----------------------------------------------------------------------
	// Name: addDB()
	// Desc: Adds a database resource.
	//----------------------------------------------------------------------
	addDB: function( host, name )
	{

		// Add the database resource.
		Legato_Debug_Debugger.database_resources.push( { host:  host,
																	               name:  name } );

	},


	//----------------------------------------------------------------------
	// Name: addResource()
	// Desc: Adds a resource.
	//----------------------------------------------------------------------
	addResource: function( class_name, id, data )
	{
		
		// Add the resource.
		Legato_Debug_Debugger.resources.push( { class_name: class_name, id: id, data: data } );

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
		window_html += "<html>";
		window_html += "<head>";

		window_html += "<title>Debug Information</title>";

		window_html += "<style>";
		window_html += "html { color: #000; background-color: #FFF; font-family: Verdana, Helvetica, sans-serif; font-size: 11px; }";
		window_html += "body { margin: 0; padding: 0; }";
		window_html += "h1, h2, h3, h4, h5, h6 { font-family: Trebuchet MS, verdana, sans-serif; margin: 0px; }";
		window_html += "h1 { font-size: 17px; color: #3870A9; border-bottom: 1px solid #CCC; padding-left: 10px; margin-top: 15px; }";
		window_html += "h2 { font-size: 14px; color: #3870A9; border-bottom: 1px solid #CCC; margin-top: 15px; padding: 0px 0px 2px 50px; }";
		window_html += ".item { background-color: #EEE; margin: 0px 10px 10px 10px; padding: 5px; }";
		window_html += "table, th, td, tr { font-family: Verdana, Helvetica, sans-serif; font-size: 11px; vertical-align: top; }";
		window_html += "table { width: 100%; }";
		window_html += "td { width: 46%; padding: 0% 2%; }";
		window_html += "</style>";

		window_html += "</head>";
		window_html += "<body>";

		////////////////////////////////////////
		// Execution Information
		window_html += "<h1>Execution Information</h1>";

		window_html += "<div class='item'>"
					 + "<strong>Page Execution Time: </strong> <br /> <em>" + Legato_Debug_Debugger.execution_time + " Seconds</em> <br /><br />"
					 + "<strong>Number of Debug Items: </strong> <em>" + Legato_Debug_Debugger.debug_items.length + "</em> <br />"
					 + "<strong>Number of Queries: </strong> <em>" + Legato_Debug_Debugger.queries.length + "</em> <br />"
					 + "<strong>Number of Resources: </strong> <em>" + Legato_Debug_Debugger.resources.length + "</em> <br />"
					 + "</div>";

		////////////////////////////////////////
		// Debug items.
		if ( Legato_Debug_Debugger.debug_items.length != 0 )
		{
			
			window_html += "<h1>Debug Information</h1>";
	
			// Loop through each debug item.
			formatted_debug_items = Legato_Debug_Debugger.formatDebugItems();
	
			for ( var i = 0; i < formatted_debug_items.length; i++ )
			{
	
				// Get the debug item.
				var debug_item = formatted_debug_items[i];
	
				window_html += "<h2>Item #" + (i + 1) + "</h2>"
							 + "<div class='item'>"
							 + debug_item
							 + "</div>";
	
			}  // Next debug item.
		
		}

		////////////////////////////////////////
		// Database queries.
		window_html += "<h1>Database Queries (" + Legato_Debug_Debugger.queries.length + ")</h1>";

		// Loop through each query.
		for ( var i = 0; i < Legato_Debug_Debugger.queries.length; i++ )
		{

			// Get the query.
			var query = Legato_Debug_Debugger.queries[i];

			// Show the query.
			window_html += "<h2>Query #" + (i + 1) + "</h2>"
						 + "<div class='item'>"
						 + '"' + query.query + '" <br />'
						 + "<strong>File:</strong> <em>" + query.file + "</em> <br />"
						 + "<strong>Line:</strong> <em>" + query.line + "</em> <br />"
						 + "</div>";

		}  // Next query.

		////////////////////////////////////////
		// Resource information.
		window_html += "<h1>Resource Information (" + Legato_Debug_Debugger.resources.length + ")</h1>";

		// Loop through each DB resource.
		for ( var i = 0; i < Legato_Debug_Debugger.database_resources.length; i++ )
		{

			// Get the DB resource.
			var resource = Legato_Debug_Debugger.database_resources[i];

			// Show the DB resource.
			window_html += "<h2>DB #" + (i + 1) + "</h2>"
						 + "<div class='item'>"
						 + "<strong>DB Host:</strong> <em>" + resource.host + "</em> <br />"
						 + "<strong>DB Name:</strong> <em>" + resource.name + "</em> <br />"
						 + "</div>";

		}  // Next DB resource.

		// Loop through each resource.
		for ( var i = 0; i < Legato_Debug_Debugger.resources.length; i++ )
		{

			// Get the resource.
			var resource = Legato_Debug_Debugger.resources[i];
			
			var resource_data = '';
			for ( n in resource.data )
				resource_data += "<strong>" + n + ":</strong> " + resource.data[n] + "<br />";

			// Show the resource.
			window_html += "<h2>" + resource.class_name + " (" + (resource.id) + ")</h2>"
						 + "<div class='item'>"
						 + "<table><tr>"
						 + "<td><h3>Resource Data</h3><br /><div>" + resource_data + "</div></td>"
						 + "<td><h3>Resource Info</h3><div></div></td>"
						 + "</tr></table>"
						 + "</div>";

		}  // Next resource.
		
		window += "</body>";
		window += "</html>";

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
		for ( var i = 0; i < Legato_Debug_Debugger.debug_items.length; i++ )
		{

			var debug_item = Legato_Debug_Debugger.debug_items[i];

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

// Developed by Robert Nyman/DOMAssistant team
// code/licensing: http://code.google.com/p/domassistant/ 
// documentation: http://www.domassistant.com/documentation
// version 2.7.1.1
var DOMAssistant = function () {
	var HTMLArray = function () {
		// Constructor
	};
	var isIE = /*@cc_on!@*/false;
	var cachedElms = [];
	var camel = {
		"accesskey": "accessKey",
		"class": "className",
		"colspan": "colSpan",
		"for": "htmlFor",
		"maxlength": "maxLength",
		"readonly": "readOnly",
		"rowspan": "rowSpan",
		"tabindex": "tabIndex",
		"valign": "vAlign",
		"cellspacing": "cellSpacing",
		"cellpadding": "cellPadding"
	};
	var pushAll = function (set1, set2) {
		for (var j=0, jL=set2.length; j<jL; j++) {
			set1.push(set2[j]);
		}
		return set1;
	};
	if (isIE) {
		pushAll = function (set1, set2) {
			if (set2.slice) {
				return set1.concat(set2);
			}
			for (var i=0, iL=set2.length; i<iL; i++) {
				set1[set1.length] = set2[i];
			}
			return set1;
		};
	}
	return {
		isIE : isIE,
		camel : camel,
		allMethods : [],
		publicMethods : [
			"cssSelect",
			"elmsByClass",
			"elmsByAttribute",
			"elmsByTag"
		],
		
		initCore : function () {
			this.applyMethod.call(window, "$", this.$);
			this.applyMethod.call(window, "$$", this.$$);
			window.DOMAssistant = this;
			if (isIE) {
				HTMLArray = Array;
			}
			HTMLArray.prototype = [];
			HTMLArray.prototype.each = function (functionCall) {
				for (var i=0, il=this.length; i<il; i++) {
					functionCall.call(this[i]);
				}
				return this;
			};
			HTMLArray.prototype.first = function () {
				return (typeof this[0] !== "undefined")? DOMAssistant.addMethodsToElm(this[0]) : null;
			};
			HTMLArray.prototype.end = function () {
				return this.previousSet;
			};
			this.attach(this);
		},
		
		addMethods : function (name, method) {
			if (typeof this.allMethods[name] === "undefined") {
				this.allMethods[name] = method;
				this.addHTMLArrayPrototype(name, method);
			}
		},
		
		addMethodsToElm : function (elm) {
			for (var method in this.allMethods) {
				if (typeof this.allMethods[method] !== "undefined") {
					this.applyMethod.call(elm, method, this.allMethods[method]);
				}
			}
			return elm;
		},
		
		applyMethod : function (method, func) {
			if (typeof this[method] !== "function") {
				this[method] = func;
			}
		},
		
		attach : function (plugin) {
			var publicMethods = plugin.publicMethods;
			if (typeof publicMethods === "undefined") {
				for (var method in plugin) {
					if (method !== "init" && typeof plugin[method] !== "undefined") {
						this.addMethods(method, plugin[method]);
					}
				}
			}
			else if (publicMethods.constructor === Array) {
				for (var i=0, current; (current=publicMethods[i]); i++) {
					this.addMethods(current, plugin[current]);
				}
			}
			if (typeof plugin.init === "function") {
				plugin.init();
			}
		},
		
		addHTMLArrayPrototype : function (name, method) {
			HTMLArray.prototype[name] = function () {
				var elmsToReturn = new HTMLArray();
				elmsToReturn.previousSet = this;
				var elms;
				for (var i=0, il=this.length; i<il; i++) {
					elms = method.apply(this[i], arguments);
					if (typeof elms !== "undefined" && elms !== null && elms.constructor === Array) {
						elmsToReturn = pushAll(elmsToReturn, elms);
					}
					else {
						elmsToReturn.push(elms);
					}	
				}
				return elmsToReturn;
			};
		},
		
		$ : function () {
			var elm = new HTMLArray();
			if (document.getElementById) {
				var arg = arguments[0];
				if (typeof arg === "string") {
					arg = arg.replace(/^[^#]*(#)/, "$1");
					if (/^#[\w\u00C0-\uFFFF\-\_]+$/.test(arg)) {
						var idMatch = DOMAssistant.$$(arg.substr(1), false);
						if (idMatch) {
							elm.push(idMatch);
						}
					}
					else {
						elm = DOMAssistant.cssSelection.call(document, arg);
					}
				}
				else if ((typeof arg === "object") || (typeof arg === "function" && typeof arg.nodeName !== "undefined")) {
					elm = (arguments.length === 1)? DOMAssistant.$$(arg) : pushAll(elm, arguments);
				}
			}
			return elm;
		},
	
		$$ : function (id, addMethods) {
			var elm = ((typeof id === "object") || (typeof id === "function" && typeof id.nodeName !== "undefined"))? id : document.getElementById(id);
			var applyMethods = addMethods || true;
			if (typeof id === "string" && elm && elm.id !== id) {
				elm = null;
				for (var i=0, item; (item=document.all[i]); i++) {
					if (item.id === id) {
						elm = item;
						break;
					}
				}
			}
			if (elm && applyMethods) {
				DOMAssistant.addMethodsToElm(elm);
			}
			return elm;
		},
	
		cssSelection : function (cssRule) {
			var getSequence = function (expression) {
				var start, add = 2, max = -1, modVal = -1;
				var expressionRegExp = /^((odd|even)|([1-9]\d*)|((([1-9]\d*)?)n([\+\-]\d+)?)|(\-(([1-9]\d*)?)n\+(\d+)))$/;
				var pseudoValue = expressionRegExp.exec(expression);
				if (!pseudoValue) {
					return null;
				}
				else {
					if (pseudoValue[2]) {	// odd or even
						start = (pseudoValue[2] === "odd")? 1 : 2;
						modVal = (start === 1)? 1 : 0;
					}
					else if (pseudoValue[3]) {	// single digit
						start = parseInt(pseudoValue[3], 10);
						add = 0;
						max = start;
					}
					else if (pseudoValue[4]) {	// an+b
						add = pseudoValue[6]? parseInt(pseudoValue[6], 10) : 1;
						start = pseudoValue[7]? parseInt(pseudoValue[7], 10) : 0;
						while (start < 1) {
							start += add;
						}
						modVal = (start > add)? (start - add) % add : ((start === add)? 0 : start);
					}
					else if (pseudoValue[8]) {	// -an+b
						add = pseudoValue[10]? parseInt(pseudoValue[10], 10) : 1;
						start = max = parseInt(pseudoValue[11], 10);
						while (start > add) {
							start -= add;
						}
						modVal = (max > add)? (max - add) % add : ((max === add)? 0 : max);
					}
				}
				return { start: start, add: add, max: max, modVal: modVal };
			};
			if (document.evaluate) {
				var ns = { xhtml: "http://www.w3.org/1999/xhtml" };
				var prefix = (document.documentElement.namespaceURI === ns.xhtml)? "xhtml:" : "";
				var nsResolver = function lookupNamespaceURI (prefix) {
					return ns[prefix] || null;
				};
				DOMAssistant.cssSelection = function (cssRule) {
					var cssRules = cssRule.replace(/\s*(,)\s*/g, "$1").split(",");
					var elm = new HTMLArray();
					var currentRule, identical, cssSelectors, xPathExpression, cssSelector, splitRule, sequence;
					var cssSelectorRegExp = /^(\w+)?(#[\w\u00C0-\uFFFF\-\_]+|(\*))?((\.[\w\u00C0-\uFFFF\-_]+)*)?((\[\w+(\^|\$|\*|\||~)?(=([\w\u00C0-\uFFFF\s\-\_\.]+|"[^"]*"|'[^']*'))?\]+)*)?(((:\w+[\w\-]*)(\((odd|even|\-?\d*n?((\+|\-)\d+)?|[\w\u00C0-\uFFFF\-_\.]+|"[^"]*"|'[^']*'|((\w*\.[\w\u00C0-\uFFFF\-_]+)*)?|(\[#?\w+(\^|\$|\*|\||~)?=?[\w\u00C0-\uFFFF\s\-\_\.]+\]+)|(:\w+[\w\-]*))\))?)*)?(>|\+|~)?/;
					var selectorSplitRegExp = new RegExp("(?:\\[[^\\[]*\\]|\\(.*\\)|[^\\s\\+>~\\[\\(])+|[\\+>~]", "g");
					function attrToXPath (match, p1, p2, p3) {
						p3 = p3.replace(/^["'](.*)["']$/, "$1");
						switch (p2) {
							case "^": return "starts-with(@" + p1 + ", \"" + p3 + "\")";
							case "$": return "substring(@" + p1 + ", (string-length(@" + p1 + ") - " + (p3.length - 1) + "), " + p3.length + ") = \"" + p3 + "\"";
							case "*": return "contains(concat(\" \", @" + p1 + ", \" \"), \"" + p3 + "\")";
							case "|": return "(@" + p1 + "=\"" + p3 + "\" or starts-with(@" + p1 + ", \"" + p3 + "-\"))";
							case "~": return "contains(concat(\" \", @" + p1 + ", \" \"), \" " + p3 + " \")";
							default: return "@" + p1 + (p3? "=\"" + p3 + "\"" : "");
						}
					}
					function pseudoToXPath (tag, pseudoClass, pseudoValue) {
						tag = (/\-child$/.test(pseudoClass))? "*" : tag;
						var xpath = "", pseudo = pseudoClass.split("-");
						switch (pseudo[0]) {
							case "first":
								xpath = "not(preceding-sibling::" + tag + ")";
								break;
							case "last":
								xpath = "not(following-sibling::" + tag + ")";
								break;
							case "only":
								xpath = "not(preceding-sibling::" + tag + " or following-sibling::" + tag + ")";
								break;		
							case "nth":
								if (!/^n$/.test(pseudoValue)) {
									var position = ((pseudo[1] === "last")? "(count(following-sibling::" : "(count(preceding-sibling::") + tag + ") + 1)";
									sequence = getSequence(pseudoValue);
									if (sequence) {
										if (sequence.start === sequence.max) {
											xpath = position + " = " + sequence.start;
										}
										else {
											xpath = position + " mod " + sequence.add + " = " + sequence.modVal + ((sequence.start > 1)? " and " + position + " >= " + sequence.start : "") + ((sequence.max > 0)? " and " + position + " <= " + sequence.max: "");
										}
									}
								}
								break;	
							case "empty":
								xpath = "count(child::*) = 0 and string-length(text()) = 0";
								break;
							case "contains":
								xpath = "contains(., \"" + pseudoValue.replace(/^["'](.*)["']$/, "$1") + "\")";
								break;	
							case "enabled":
								xpath = "not(@disabled)";
								break;
							case "disabled":
								xpath = "@disabled";
								break;
							case "checked":
								xpath = "@checked=\"checked\""; // Doesn't work in Opera 9.24
								break;
							case "target":
								var hash = document.location.hash.slice(1);
								xpath = "@name=\"" + hash + "\" or @id=\"" + hash + "\"";
								break;
							case "not":
								if (/^(:\w+[\w\-]*)$/.test(pseudoValue)) {
									xpath = "not(" + pseudoToXPath(tag, pseudoValue.slice(1)) + ")";
								}
								else {
									pseudoValue = pseudoValue.replace(/^\[#([\w\u00C0-\uFFFF\-\_]+)\]$/, "[id=$1]");
									var notSelector = pseudoValue.replace(/^(\w+)/, "self::$1");
									notSelector = notSelector.replace(/^\.([\w\u00C0-\uFFFF\-_]+)/g, "contains(concat(\" \", @class, \" \"), \" $1 \")");
									notSelector = notSelector.replace(/\[(\w+)(\^|\$|\*|\||~)?=?([\w\u00C0-\uFFFF\s\-_\.]+)?\]/g, attrToXPath);
									xpath = "not(" + notSelector + ")";
								}
								break;
							default:
								xpath = "@" + pseudoClass + "=\"" + pseudoValue + "\"";
								break;
						}
						return xpath;
					}
					for (var i=0; (currentRule=cssRules[i]); i++) {
						if (i > 0) {
							identical = false;
							for (var x=0, xl=i; x<xl; x++) {
								if (cssRules[i] === cssRules[x]) {
									identical = true;
									break;
								}
							}
							if (identical) {
								continue;
							}
						}
						cssSelectors = currentRule.match(selectorSplitRegExp);
						xPathExpression = ".";
						for (var j=0, jl=cssSelectors.length; j<jl; j++) {
							cssSelector = cssSelectorRegExp.exec(cssSelectors[j]);
							splitRule = {
								tag : prefix + ((!cssSelector[1] || cssSelector[3] === "*")? "*" : cssSelector[1]),
								id : (cssSelector[3] !== "*")? cssSelector[2] : null,
								allClasses : cssSelector[4],
								allAttr : cssSelector[6],
								allPseudos : cssSelector[11],
								tagRelation : cssSelector[23]
							};
							if (splitRule.tagRelation) {
								switch (splitRule.tagRelation) {
									case ">":
										xPathExpression += "/child::";
										break;
									case "+":
										xPathExpression += "/following-sibling::*[1]/self::";
										break;
									case "~":
										xPathExpression += "/following-sibling::";
										break;
								}
							}
							else {
								xPathExpression += (j > 0 && /(>|\+|~)/.test(cssSelectors[j-1]))? splitRule.tag : ("/descendant::" + splitRule.tag);
							}
							if (splitRule.id) {
								xPathExpression += "[@id = \"" + splitRule.id.replace(/^#/, "") + "\"]";
							}
							if (splitRule.allClasses) {
								xPathExpression += splitRule.allClasses.replace(/\.([\w\u00C0-\uFFFF\-_]+)/g, "[contains(concat(\" \", @class, \" \"), \" $1 \")]");
							}
							if (splitRule.allAttr) {
								xPathExpression += splitRule.allAttr.replace(/(\w+)(\^|\$|\*|\||~)?=?([\w\u00C0-\uFFFF\s\-_\.]+|"[^"]*"|'[^']*')?/g, attrToXPath);
							}
							if (splitRule.allPseudos) {
								var pseudoSplitRegExp = /:(\w[\w\-]*)(\(([^\)]+)\))?/;
								splitRule.allPseudos = splitRule.allPseudos.match(/(:\w+[\w\-]*)(\([^\)]+\))?/g);
								for (var k=0, kl=splitRule.allPseudos.length; k<kl; k++) {
									var pseudo = splitRule.allPseudos[k].match(pseudoSplitRegExp);
									var pseudoClass = pseudo[1]? pseudo[1].toLowerCase() : null;
									var pseudoValue = pseudo[3]? pseudo[3] : null;
									var xpath = pseudoToXPath(splitRule.tag, pseudoClass, pseudoValue);
									if (xpath.length) {
										xPathExpression += "[" + xpath + "]";
									}
								}
							}
						}
						var xPathNodes = document.evaluate(xPathExpression, this, nsResolver, 0, null), node;
						while ((node = xPathNodes.iterateNext())) {
							elm.push(node);
						}
					}
					return elm;
				};
			}
			else {
				DOMAssistant.cssSelection = function (cssRule) {
					var cssRules = cssRule.replace(/\s*(,)\s*/g, "$1").split(",");
					var elm = new HTMLArray();
					var prevElm = [], matchingElms = [];
					var prevParents, currentRule, identical, cssSelectors, childOrSiblingRef, nextTag, nextRegExp, regExpClassNames, matchingClassElms, regExpAttributes, matchingAttributeElms, attributeMatchRegExp, current, previous, prevParent, addElm, iteratorNext, childCount, childElm, sequence;
					var childOrSiblingRefRegExp = /^(>|\+|~)$/;
					var cssSelectorRegExp = /^(\w+)?(#[\w\u00C0-\uFFFF\-\_]+|(\*))?((\.[\w\u00C0-\uFFFF\-_]+)*)?((\[\w+(\^|\$|\*|\||~)?(=([\w\u00C0-\uFFFF\s\-\_\.]+|"[^"]*"|'[^']*'))?\]+)*)?(((:\w+[\w\-]*)(\((odd|even|\-?\d*n?((\+|\-)\d+)?|[\w\u00C0-\uFFFF\-_]+|"[^"]*"|'[^']*'|((\w*\.[\w\u00C0-\uFFFF\-_]+)*)?|(\[#?\w+(\^|\$|\*|\||~)?=?[\w\u00C0-\uFFFF\s\-\_\.]+\]+)|(:\w+[\w\-]*))\))?)*)?/;
					var selectorSplitRegExp;
					try {
						selectorSplitRegExp = new RegExp("(?:\\[[^\\[]*\\]|\\(.*\\)|[^\\s\\+>~\\[\\(])+|[\\+>~]", "g");
					}
					catch (e) {
						selectorSplitRegExp = /[^\s]+/g;
					}
					function clearAdded (elm) {
						elm = elm || prevElm;
						for (var n=0, nl=elm.length; n<nl; n++) {
							elm[n].added = null;
						}
					}
					function clearChildElms () {
						for (var n=0, nl=prevParents.length; n<nl; n++) {
							prevParents[n].childElms = null;
						}
					}
					function subtractArray (arr1, arr2) {
						for (var i=0, src1; (src1=arr1[i]); i++) {
							var found = false;
							for (var j=0, src2; (src2=arr2[j]); j++) {
								if (src2 === src1) {
									found = true;
									break;
								}
							}
							if (found) {
								arr1.splice(i--, 1);
							}
						}
						return arr1;
					}
					function getAttr (elm, attr) {
						return isIE? elm[camel[attr.toLowerCase()] || attr] : elm.getAttribute(attr, 2);
					}
					function attrToRegExp (attrVal, substrOperator) {
						attrVal = attrVal? attrVal.replace(/^["'](.*)["']$/, "$1").replace(/\./g, "\\.") : null;
						switch (substrOperator) {
							case "^": return "^" + attrVal;
							case "$": return attrVal + "$";
							case "*": return attrVal;
							case "|": return "(^" + attrVal + "(\\-\\w+)*$)";
							case "~": return "\\b" + attrVal + "\\b";
							default: return attrVal? "^" + attrVal + "$" : null;
						}
					}
					function getElementsByTagName (tag, parent) {
						tag = tag || "*";
						parent = parent || document;
						if (parent === document || parent.lastModified) {
							if (!cachedElms[tag]) {
								cachedElms[tag] = isIE? ((tag === "*")? document.all : document.all.tags(tag)) : document.getElementsByTagName(tag);
							}
							return cachedElms[tag];
						}
						return isIE? ((tag === "*")? parent.all : parent.all.tags(tag)) : parent.getElementsByTagName(tag);
					}
					function getElementsByPseudo (previousMatch, pseudoClass, pseudoValue) {
						prevParents = [];
						var pseudo = pseudoClass.split("-"), matchingElms = [], checkNodeName;
						var prop = (checkNodeName = /\-of\-type$/.test(pseudoClass))? "nodeName" : "nodeType";
						function getPrevElm(elm) {
							var val = checkNodeName? elm.nodeName : 1;
							while ((elm = elm.previousSibling) && elm[prop] !== val) {}
							return elm;
						}
						function getNextElm(elm) {
							var val = checkNodeName? elm.nodeName : 1;
							while ((elm = elm.nextSibling) && elm[prop] !== val) {}
							return elm;
						}
						switch (pseudo[0]) {
							case "first":
								for (var i=0; (previous=previousMatch[i]); i++) {
									if (!getPrevElm(previous)) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "last":
								for (var j=0; (previous=previousMatch[j]); j++) {
									if (!getNextElm(previous)) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "only":
								for (var k=0, kParent; (previous=previousMatch[k]); k++) {
									prevParent = previous.parentNode;
									if (prevParent !== kParent) {
										if (!getPrevElm(previous) && !getNextElm(previous)) {
											matchingElms[matchingElms.length] = previous;
										}
										kParent = prevParent;
									}
								}
								break;
							case "nth":
								if (/^n$/.test(pseudoValue)) {
									matchingElms = previousMatch;
								}
								else {
									var direction = (pseudo[1] === "last")? ["lastChild", "previousSibling"] : ["firstChild", "nextSibling"];
									sequence = getSequence(pseudoValue);
									if (sequence) {
										for (var l=0; (previous=previousMatch[l]); l++) {
											prevParent = previous.parentNode;
											if (!prevParent.childElms) {
												iteratorNext = sequence.start;
												childCount = 0;
												childElm = prevParent[direction[0]];
												while (childElm && (sequence.max < 0 || iteratorNext <= sequence.max)) {
													if (checkNodeName) {
														if (childElm.nodeName === previous.nodeName) {
															if (++childCount === iteratorNext) {
																matchingElms[matchingElms.length] = childElm;
																iteratorNext += sequence.add;
															}
														}
													}
													else {
														if (childElm.nodeType === 1) {
															if (++childCount === iteratorNext) {
																if (childElm.nodeName === previous.nodeName) {
																	matchingElms[matchingElms.length] = childElm;
																}
																iteratorNext += sequence.add;
															}
														}
													}
													childElm = childElm[direction[1]];
												}
												prevParent.childElms = true;
												prevParents[prevParents.length] = prevParent;
											}
										}
										clearChildElms();
									}
								}
								break;
							case "empty":
								for (var m=0; (previous=previousMatch[m]); m++) {
									if (!previous.childNodes.length) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "enabled":
								for (var n=0; (previous=previousMatch[n]); n++) {
									if (!previous.disabled) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "disabled":
								for (var o=0; (previous=previousMatch[o]); o++) {
									if (previous.disabled) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "checked":
								for (var p=0; (previous=previousMatch[p]); p++) {
									if (previous.checked) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
							case "contains":
								pseudoValue = pseudoValue.replace(/^["'](.*)["']$/, "$1");
								for (var q=0; (previous=previousMatch[q]); q++) {
									if (!previous.added) {
										if (previous.innerText.indexOf(pseudoValue) !== -1) {
											previous.added = true;
											matchingElms[matchingElms.length] = previous;
										}
									}
								}
								break;
							case "target":
								var hash = document.location.hash.slice(1);
								if (hash) {
									for (var r=0; (previous=previousMatch[r]); r++) {
										if (getAttr(previous, "name") === hash || getAttr(previous, "id") === hash) {
											matchingElms[matchingElms.length] = previous;
											break;
										}
									}
								}
								break;
							case "not":
								if (/^(:\w+[\w\-]*)$/.test(pseudoValue)) {
									matchingElms = subtractArray(previousMatch, getElementsByPseudo(previousMatch, pseudoValue.slice(1)));
								}
								else {
									pseudoValue = pseudoValue.replace(/^\[#([\w\u00C0-\uFFFF\-\_]+)\]$/, "[id=$1]");
									var notTag = /^(\w+)/.exec(pseudoValue);
									var notClass = /^\.([\w\u00C0-\uFFFF\-_]+)/.exec(pseudoValue);
									var notAttr = /\[(\w+)(\^|\$|\*|\||~)?=?([\w\u00C0-\uFFFF\s\-_\.]+)?\]/.exec(pseudoValue);
									var notRegExp = new RegExp("(^|\\s)" + (notTag? notTag[1] : notClass? notClass[1] : "") + "(\\s|$)", "i");
									if (notAttr) {
										var notMatchingAttrVal = attrToRegExp(notAttr[3], notAttr[2]);
										notRegExp = new RegExp(notMatchingAttrVal, "i");
									}
									for (var s=0, notElm; (notElm=previousMatch[s]); s++) {
										addElm = null;
										if (notTag && !notRegExp.test(notElm.nodeName)) {
											addElm = notElm;
										}		
										else if (notClass && !notRegExp.test(notElm.className)) {
											addElm = notElm;
										}
										else if (notAttr) {
											var att = getAttr(notElm, notAttr[1]);
											if (!att || !notRegExp.test(att)) {
												addElm = notElm;
											}
										}
										if (addElm && !addElm.added) {
											addElm.added = true;
											matchingElms[matchingElms.length] = addElm;
										}
									}
								}
								break;
							default:
								for (var t=0; (previous=previousMatch[t]); t++) {
									if (getAttr(previous, pseudoClass) === pseudoValue) {
										matchingElms[matchingElms.length] = previous;
									}
								}
								break;
						}
						return matchingElms;
					}
					for (var a=0; (currentRule=cssRules[a]); a++) {
						if (a > 0) {
							identical = false;
							for (var b=0, bl=a; b<bl; b++) {
								if (cssRules[a] === cssRules[b]) {
									identical = true;
									break;
								}
							}
							if (identical) {
								continue;
							}
						}
						cssSelectors = currentRule.match(selectorSplitRegExp);
						prevElm = [this];
						for (var i=0, rule; (rule=cssSelectors[i]); i++) {
							matchingElms = [];
							if (i > 0 && childOrSiblingRefRegExp.test(rule)) {
								childOrSiblingRef = childOrSiblingRefRegExp.exec(rule);
								if (childOrSiblingRef) {
									nextTag = /^\w+/.exec(cssSelectors[i+1]);
									if (nextTag) {
										nextTag = nextTag[0];
										nextRegExp = new RegExp("(^|\\s)" + nextTag + "(\\s|$)", "i");
									}
									for (var j=0, prevRef; (prevRef=prevElm[j]); j++) {
										switch (childOrSiblingRef[0]) {
											case ">":
												var children = getElementsByTagName(nextTag, prevRef);
												for (var k=0, child; (child=children[k]); k++) {
													if (child.parentNode === prevRef) {
														matchingElms[matchingElms.length] = child;
													}
												}
												break;
											case "+":
												while ((prevRef = prevRef.nextSibling) && prevRef.nodeType !== 1) {}
												if (prevRef) {
													if (!nextTag || nextRegExp.test(prevRef.nodeName)) {
														matchingElms[matchingElms.length] = prevRef;
													}
												}
												break;
											case "~":
												while ((prevRef = prevRef.nextSibling) && !prevRef.added) {
													if (!nextTag || nextRegExp.test(prevRef.nodeName)) {
														prevRef.added = true;
														matchingElms[matchingElms.length] = prevRef;
													}
												}
												break;
										}
									}
									prevElm = matchingElms;
									clearAdded();
									rule = cssSelectors[++i];
									if (/^\w+$/.test(rule)) {
										continue;
									}
									prevElm.skipTag = true;
								}
							}
							var cssSelector = cssSelectorRegExp.exec(rule);
							var splitRule = {
								tag : (!cssSelector[1] || cssSelector[3] === "*")? "*" : cssSelector[1],
								id : (cssSelector[3] !== "*")? cssSelector[2] : null,
								allClasses : cssSelector[4],
								allAttr : cssSelector[6],
								allPseudos : cssSelector[11]
							};
							if (splitRule.id) {
								var DOMElm = document.getElementById(splitRule.id.replace(/#/, ""));
								if (DOMElm) {
									matchingElms = [DOMElm];
								}
								prevElm = matchingElms;
							}
							else if (splitRule.tag && !prevElm.skipTag) {
								if (i===0 && !matchingElms.length && prevElm.length === 1) {
									prevElm = matchingElms = pushAll([], getElementsByTagName(splitRule.tag, prevElm[0]));
								}
								else {
									for (var l=0, ll=prevElm.length, tagCollectionMatches, tagMatch; l<ll; l++) {
										tagCollectionMatches = getElementsByTagName(splitRule.tag, prevElm[l]);
										for (var m=0; (tagMatch=tagCollectionMatches[m]); m++) {
											if (!tagMatch.added) {
												tagMatch.added = true;
												matchingElms[matchingElms.length] = tagMatch;
											}
										}
									}
									prevElm = matchingElms;
									clearAdded();
								}
							}
							if (!matchingElms.length) {
								break;
							}
							prevElm.skipTag = false;
							if (splitRule.allClasses) {
								splitRule.allClasses = splitRule.allClasses.replace(/^\./, "").split(".");
								regExpClassNames = [];
								for (var n=0, nl=splitRule.allClasses.length; n<nl; n++) {
									regExpClassNames[regExpClassNames.length] = new RegExp("(^|\\s)" + splitRule.allClasses[n] + "(\\s|$)");
								}
								matchingClassElms = [];
								for (var o=0, elmClass; (current=prevElm[o]); o++) {
									elmClass = current.className;
									if (elmClass && !current.added) {
										addElm = false;
										for (var p=0, pl=regExpClassNames.length; p<pl; p++) {
											addElm = regExpClassNames[p].test(elmClass);
											if (!addElm) {
												break;
											}
										}
										if (addElm) {
											current.added = true;
											matchingClassElms[matchingClassElms.length] = current;
										}
									}
								}
								clearAdded();
								prevElm = matchingElms = matchingClassElms;
							}
							if (splitRule.allAttr) {
								splitRule.allAttr = splitRule.allAttr.match(/\[[^\]]+\]/g);
								regExpAttributes = [];
								attributeMatchRegExp = /(\w+)(\^|\$|\*|\||~)?=?([\w\u00C0-\uFFFF\s\-_\.]+|"[^"]*"|'[^']*')?/;
								for (var q=0, ql=splitRule.allAttr.length, attributeMatch, attrVal; q<ql; q++) {
									attributeMatch = attributeMatchRegExp.exec(splitRule.allAttr[q]);
									attrVal = attrToRegExp(attributeMatch[3], (attributeMatch[2] || null));
									regExpAttributes[regExpAttributes.length] = [(attrVal? new RegExp(attrVal) : null), attributeMatch[1]];
								}
								matchingAttributeElms = [];
								for (var r=0, currentAttr; (current=matchingElms[r]); r++) {
									for (var s=0, sl=regExpAttributes.length, attributeRegExp; s<sl; s++) {
										addElm = false;
										attributeRegExp = regExpAttributes[s][0];
										currentAttr = getAttr(current, regExpAttributes[s][1]);
										if (typeof currentAttr === "string" && currentAttr.length) {
											if (!attributeRegExp || typeof attributeRegExp === "undefined" || (attributeRegExp && attributeRegExp.test(currentAttr))) {
												addElm = true;
											}
										}
										if (!addElm) {
											break;
										} 
									}
									if (addElm) {
										matchingAttributeElms[matchingAttributeElms.length] = current;
									}
								}
								prevElm = matchingElms = matchingAttributeElms;
							}
							if (splitRule.allPseudos) {
								var pseudoSplitRegExp = /:(\w[\w\-]*)(\(([^\)]+)\))?/;
								splitRule.allPseudos = splitRule.allPseudos.match(/(:\w+[\w\-]*)(\([^\)]+\))?/g);
								for (var t=0, tl=splitRule.allPseudos.length; t<tl; t++) {
									var pseudo = splitRule.allPseudos[t].match(pseudoSplitRegExp);
									var pseudoClass = pseudo[1]? pseudo[1].toLowerCase() : null;
									var pseudoValue = pseudo[3]? pseudo[3] : null;
									matchingElms = getElementsByPseudo(matchingElms, pseudoClass, pseudoValue);
									clearAdded(matchingElms);
								}
								prevElm = matchingElms;
							}
						}
						elm = pushAll(elm, prevElm);
					}
					return elm;	
				};
			}
			if (document.querySelectorAll) {
				var cssSelectionBackup = DOMAssistant.cssSelection;
				DOMAssistant.cssSelection = function (cssRule) {
					try {
						var elm = new HTMLArray();
						return pushAll(elm, this.querySelectorAll(cssRule));
					}
					catch (e) {
						return cssSelectionBackup.call(this, cssRule);
					}
				};
			}
			return DOMAssistant.cssSelection.call(this, cssRule); 
		},
		
		cssSelect : function (cssRule) {
			return DOMAssistant.cssSelection.call(this, cssRule);
		},
	
		elmsByClass : function (className, tag) {
			var cssRule = (tag || "") + "." + className;
			return DOMAssistant.cssSelection.call(this, cssRule);
		},
	
		elmsByAttribute : function (attr, attrVal, tag, substrMatchSelector) {
			var cssRule = (tag || "") + "[" + attr + ((attrVal && attrVal !== "*")? ((substrMatchSelector || "") + "=" + attrVal + "]") : "]");
			return DOMAssistant.cssSelection.call(this, cssRule);
		},
	
		elmsByTag : function (tag) {
			return DOMAssistant.cssSelection.call(this, tag);
		}
	};	
}();
DOMAssistant.initCore();



DOMAssistant.CSS = function () {
	return {
		addClass : function (className) {
			var currentClass = this.className;
			if (!new RegExp(("(^|\\s)" + className + "(\\s|$)"), "i").test(currentClass)) {
				this.className = currentClass + (currentClass.length? " " : "") + className;
			}
			return this;
		},

		removeClass : function (className) {
			var classToRemove = new RegExp(("(^|\\s)" + className + "(\\s|$)"), "i");
			this.className = this.className.replace(classToRemove, function (match) {
				var retVal = "";
				if (new RegExp("^\\s+.*\\s+$").test(match)) {
					retVal = match.replace(/(\s+).+/, "$1");
				}
				return retVal;
			}).replace(/^\s+|\s+$/g, "");
			return this;
		},
		
		replaceClass : function (className, newClass) {
			var classToRemove = new RegExp(("(^|\\s)" + className + "(\\s|$)"), "i");
			this.className = this.className.replace(classToRemove, function (match, p1, p2) {
				var retVal = p1 + newClass + p2;
				if (new RegExp("^\\s+.*\\s+$").test(match)) {
					retVal = match.replace(/(\s+).+/, "$1");
				}
				return retVal;
			}).replace(/^\s+|\s+$/g, "");
			return this;
		},

		hasClass : function (className) {
			return new RegExp(("(^|\\s)" + className + "(\\s|$)"), "i").test(this.className);
		},
		
		setStyle : function (style, value) {
			if (typeof this.style.cssText !== "undefined") {
				var styleToSet = this.style.cssText;
				if (typeof style === "object") {
					for (var i in style) {
						if (typeof i === "string") {
							styleToSet += ";" + i + ":" + style[i];
						}
					}
				}
				else {                    
					styleToSet += ";" + style + ":" + value;
				}
				this.style.cssText = styleToSet;
			}
			return this;
		},

		getStyle : function (cssRule) {
			var cssVal = "";
			if (document.defaultView && document.defaultView.getComputedStyle) {
				cssVal = document.defaultView.getComputedStyle(this, "").getPropertyValue(cssRule);
			}
			else if (this.currentStyle) {
				cssVal = cssRule.replace(/\-(\w)/g, function (match, p1) {
					return p1.toUpperCase();
				});
				cssVal = this.currentStyle[cssVal];
			}
			return cssVal;
		}
	};
}();
DOMAssistant.attach(DOMAssistant.CSS);



DOMAssistant.Content = function () {
	return {
		prev : function () {
			var prevSib = this;
			while ((prevSib = prevSib.previousSibling) && prevSib.nodeType !== 1) {}
			return DOMAssistant.$(prevSib);
		},

		next : function () {
			var nextSib = this;
			while ((nextSib = nextSib.nextSibling) && nextSib.nodeType !== 1) {}
			return DOMAssistant.$(nextSib);
		},

		create : function (name, attr, append, content) {
			var elm = DOMAssistant.$(document.createElement(name));
			if (attr) {
				elm = elm.setAttributes(attr);
			}
			if (typeof content !== "undefined") {
				elm.addContent(content);
			}
			if (append) {
				DOMAssistant.Content.addContent.call(this, elm);
			}
			return elm;
		},

		setAttributes : function (attr) {
			if (DOMAssistant.isIE) {
				var setAttr = function (elm, att, val) {
					var attLower = att.toLowerCase();
					switch (attLower) {
						case "name":
						case "type":
							return document.createElement(elm.outerHTML.replace(new RegExp(attLower + "=[a-zA-Z]+"), " ").replace(">", " " + attLower + "=" + val + ">"));
						case "style":
							elm.style.cssText = val;
							return elm;
						default:
							elm[DOMAssistant.camel[attLower] || att] = val;
							return elm;
					}
				};
				DOMAssistant.Content.setAttributes = function (attr) {
					var elem = this;
					var parent = this.parentNode;
					for (var i in attr) {
						if (typeof attr[i] === "string" || typeof attr[i] === "number") {
							var newElem = setAttr(elem, i, attr[i]);
							if (parent && /(name|type)/i.test(i)) {
								if (elem.innerHTML) {
									newElem.innerHTML = elem.innerHTML;
								}
								parent.replaceChild(newElem, elem);
							}
							elem = newElem;
						}
					}
					return DOMAssistant.$(elem);
				};
			}
			else {
				DOMAssistant.Content.setAttributes = function (attr) {
					for (var i in attr) {
						if (/class/i.test(i)) {
							this.className = attr[i];
						}
						else {
							this.setAttribute(i, attr[i]);
						}	
					}
					return this;
				};
			}
			return DOMAssistant.Content.setAttributes.call(this, attr); 
		},

		addContent : function (content) {
			if (typeof content === "string" || typeof content === "number") {
				this.innerHTML += content;
			}
			else if ((typeof content === "object") || (typeof content === "function" && typeof content.nodeName !== "undefined")) {
				this.appendChild(content);
			}
			return this;
		},

		replaceContent : function (newContent) {
			var children = this.all || this.getElementsByTagName("*");
			for (var i=0, child, attr; (child=children[i]); i++) {
				attr = child.attributes;
				if (attr) {
					for (var j=0, jl=attr.length, att; j<jl; j++) {
						att = attr[j].nodeName.toLowerCase();
						if (typeof child[att] === "function") {
							child[att] = null;
						}
					}
				}
			}
			while (this.hasChildNodes()) {
				this.removeChild(this.firstChild);
			}
			DOMAssistant.$(this).addContent(newContent);
			return this;
		},

		remove : function () {
			this.parentNode.removeChild(this);
			return null;
		}
	};
}();
DOMAssistant.attach(DOMAssistant.Content);


/*
	Class: Legato_DOM_Library
	Provides a plugin to DOMAssistant to allow extra features for working with the DOM.
*/
Legato_DOM_Library = {};

Legato_DOM_Library.DOMAssistantPlugIn = function () 
{
	
	return {
				
		/*
			Function: dimensions()
			Sets/gets the dimension's of the element.
			If no dimensions passed in, will return the element's dimensions.
			
			Syntax:
				*Getting Dimensions*
				
				array dimensions()
				
				*Setting Dimensions*
				
				object dimensions( int width, int height )
				
			Parameters:				
				*Setting Dimensions*
				
				int width - The new width you'd like the element to have. Pass in null if you would like the width to stay the same.
				int height - The new height you'd like the element to have. Pass in null if you would like the height to stay the same.
				
			Returns:
				*Getting Dimensions*
				
				Returns an array of the dimensions, with the first item being the width and the second item being the height.
				
				*Setting Dimensions*
				
				Returns the element the dimensions were set on.
								
			Examples:
			(begin code)
				var dimensions = $$( 'container' ).dimensions();
				alert( dimensions[0] )  // Show the width of the container.
			(end)
			
			(begin code)
				// Set the height of the container to 300 pixels.
				$$( 'container' ).dimensions( null, 300 );
			(end)
		*/
		dimensions: function()
		{
			
			if ( this.window == window )
			{
				
				var width = window.innerWidth || (window.document.documentElement.clientWidth || window.document.body.clientWidth);
		        var height = window.innerHeight || (window.document.documentElement.clientHeight || window.document.body.clientHeight);
		        
		        return [ width, height ];
		        
			}
			else if ( arguments.length == 0 )
			{
				
				return [ this.offsetWidth, this.offsetHeight ];
				
			}	
			else
			{
				
				if ( arguments[0] !== null ) this.setStyle( 'width', arguments[0] + 'px' );
				if ( arguments[1] !== null ) this.setStyle( 'height', arguments[1] + 'px' );
				return this;
				
			}
			
		},
		
		
		/*
			Function: position()
			Sets/gets the position of an element.
			If no position passed in, will return the current position of the element.
			
			Syntax:
				*Getting Position*
				
				array position()
				
				*Setting Position*
				
				object position( int X, int Y )
				
			Parameters:
				*Setting Position*
				
				int X - The new X value that you'd like the element to have. Pass in null if you would like the X position to stay the same.
				int Y - The new Y value that you'd like the element to have. Pass in null if you would like the Y position to stay the same.
				
			Returns:
				*Getting Position*
				
				Returns an array of the position, with the first item being the X value and the second item being the Y value.
				
				*Setting Position*
				
				Returns the element the position was set on.
				
			Notes:
				This function works off of the page grid and not the containing element. So, setting an X value of 50 would put the element
				50 pixels from the top of the page.
								
			Examples:
			(begin code)
				// Show the position of the container element.
				var pos = $$( 'container' ).position();
				alert( pos[0] + ' | ' + pos[1] );
				
				// Set the Y position to 50 pixels.
				$$( 'container' ).position( null, 50 );
			(end)
		*/
		position: function()
		{
			
			if ( arguments.length == 0 )
			{
				
				var offsetLeft = offsetTop = 0;
				var elem = this;
				
				if ( elem.offsetParent )
				{		
					do
					{
						offsetLeft += elem.offsetLeft;
						offsetTop += elem.offsetTop;
					}	
					while ( elem = elem.offsetParent );				
				}
				
				return [ offsetLeft, offsetTop ];
				
			}
			else
			{
				
				// Get the positioning of this element.
				var positioning = this.getStyle( 'position' );
				
				// If it's statically positioned, we change it to relative positioning.
				// If it's absolute, we leave it.
                if ( positioning == 'static' ) 
				{
					positioning = 'relative';
                    this.setStyle( 'position', 'relative' );
                }
                
                // Try to get the offset value.
                var offset = 
				[
                    parseInt( this.getStyle( 'left' ), 10 ),
                    parseInt( this.getStyle( 'top' ), 10 )
                ];
            
            	// If auto was returned, retrieve the correct offset.
                if ( isNaN( offset[0] ) )
                    offset[0] = (positioning == 'relative') ? 0 : this.offsetLeft;
                    
                // If auto was returned, retrieve the correct offset.
                if ( isNaN( offset[1] ) )
                    offset[1] = (positioning == 'relative') ? 0 : this.offsetTop;
                    
                // Get the page XY position of the element.
                var posXY = this.position();
                
                // If a new X or Y value was passed in, set it.
                if ( arguments[0] !== null ) this.setStyle( 'left', arguments[0] - posXY[0] + offset[0] + 'px' );
                if ( arguments[1] !== null ) this.setStyle( 'top', arguments[1] - posXY[1] + offset[1] + 'px' );
                
                return this;
				
			}
			
		},
		

		/*
			Function: opacity()
			Sets/gets the opacity of an element.
			If no opacity passed in, will return the current opacity of the element.
			
			Syntax:
				*Getting Opacity*
				
				float opacity()
				
				*Setting Opacity*
				
				object opacity( float opacity )
				
			Parameters:
				*Setting Opacity*
				
				float opacity - The new opacity you'd like the element to have. This parameter should be a value between 0 and 1.
				
			Returns:
				*Getting Opacity*
				
				Returns the current opacity of the element as a value between 0 and 1.
				
				*Setting Opacity*
				
				Returns the element the opacity was set on.
								
			Examples:
			(begin code)
				// Show the opacity of the container element.
				alert( $$( 'container' ).opacity() );
				
				// Set the opacity of the container element to 50%.
				$$( 'container' ).opacity( 0.5 );
			(end)
		*/
		opacity: function()
		{
			
			if ( arguments.length == 0 )
			{
				
				// For all browsers besides IE.
				if ( !document.all )
					return this.getStyle( 'opacity' );
				
				// Below is for just IE.
				var value = 100;
								
                try { value = this.filters['DXImageTransform.Microsoft.Alpha'].opacity; } 
				catch( e ) 
				{
                    try { value = this.filters( 'alpha' ).opacity; } 
					catch( e ){}
                }
                
                return value / 100;
                
			}						
			else
			{	
			
				this.setStyle( 'opacity', arguments[0] );
				this.style.filter = 'alpha(opacity=' + arguments[0] * 100 + ')';  // For Internet Explorer.
				
				return this;
				
			}
			
		},
		
		
		/*
			Function: scrollOffset()
			Sets/gets the scroll offset of an element.
			If no offset passed in, will return the current offset of the element.
			
			Syntax:
				*Getting Offset*
				
				array scrollOffset()
				
				*Setting Offset*
				
				object scrollOffset( int X, int Y )
				
			Parameters:
				*Setting Offset*
				
				int X - The new X value that you'd like the element's scroll offset to be. Pass in null if you would like the X offset to stay the same.
				int Y - The new Y value that you'd like the element's scroll offset to be. Pass in null if you would like the Y offset to stay the same.
				
			Returns:
				*Getting Offset*
				
				Returns an array of the scroll offset, with the first item being the X offset and the second item being the Y offset.
				
				*Setting Offset*
				
				Returns the element the scroll offset was set on.
								
			Examples:
			(begin code)
				// Show the Y offset of the container element.
				alert( $$( 'container' ).position() );
				
				// Set the X offset to 75 pixels.
				$$( 'container' ).scrollOffset( 75, null );
			(end)
		*/
		scrollOffset: function()
		{
			
			if ( this.window == window || this == document.body )
			{
				
				var X = Y = 0;
				
				if( typeof( window.pageXOffset ) == 'number' ) 
				{					
					X = window.pageXOffset;
					Y = window.pageYOffset;					
				}  // Netscape.
				else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) 
				{					
					X = document.body.scrollLeft;
					Y = document.body.scrollTop;					
				}  // Standards compliant.
				else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) 
				{					
					X = document.documentElement.scrollLeft;
					Y = document.documentElement.scrollTop;					
				}  // IE 6 standards mode.
				
				return [ X, Y ];
		        
			}
			else if ( arguments.length == 0 )
			{
				
				return [ this.scrollLeft, this.scrollTop ];
				
			}	
			else
			{
				
				if ( arguments[0] !== null ) this.scrollLeft = arguments[0];
				if ( arguments[1] !== null ) this.scrollTop = arguments[1];
				return this;
				
			}
			
		}
		
	};
	
}();

DOMAssistant.attach( Legato_DOM_Library.DOMAssistantPlugIn );
//------------------------------------------------------------------------
// Name: Legato_Form
// Desc: A helper class to help with the PHP Form class.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Form
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Form( form, options_object )
{

	this.form                  = $$( form );
	this.input_elements        = [];
	this.groups                = [];

	// Store the parameters.
	this.submit_button         = options_object.submit_button;
	this.submit_form           = (options_object.submit_form == null) ? false : true;
	this.redirect_url          = options_object.redirect_url;
	this.request_url           = options_object.request_url;
	this.modified_request_url  = '';
	this.errors                = [];

	this.processing            = false;
	
	// Add on onsubmit event for the form.
	var form = this;
	Legato_Events_Handler.addEvent( this.form, 'onsubmit', function(){ return form.validateForm(); } );

}


//----------------------------------------------------------------------
// Name: validateForm()
// Desc: Start the validation.
//----------------------------------------------------------------------
Legato_Form.prototype.validateForm = function()
{
	
	var errors = false;

	// Are we processing?
	if ( this.processing == true )
		return false;
	else
		this.processing = true;

	// Disable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = true;

	// First make sure the form is clean.
	this.cleanupForm();
	
	// If there is a request URL, send the request.
	if ( this.request_url != null )
	{
		
		var query_string = this.getQueryString();

		// Send the request.
		var form = this;
		Legato_RequestManager.makeRequest( this.modified_request_url, function( response ){ form.processResponse( response ); }, query_string );

		// Return. We will continue processing in the processResponse() function.
		return false;

	}
	
	// Finish the validation.
	return this.finishValidation();

};


//----------------------------------------------------------------------
// Name: processResponse()
// Desc: Processes the response from the XHR request.
//----------------------------------------------------------------------
Legato_Form.prototype.processResponse = function( response )
{
	
	var any_errors = false;
	
	// JSON or XML?
	if ( !response.responseXML || response.responseXML.getElementsByTagName( 'errors' ).length == 0 )
	{

		var errors = eval( '(' + response.responseText + ')' );
		
		// Any errors?
		if ( errors.length != 0 )
		{
			
			// Set errors on.
			any_errors = true;
			
			// Loop through each error.
			for ( var id in errors )
			{
				
				// Get the message.
				var message = errors[id];
	
				// Post the error.
				this.postError( id, message );
	
			}  // Next error.
			
		}
		
	}
	else
	{
		
		response = response.responseXML;
		
		// Get the errors.
		var errors = response.getElementsByTagName( 'error' );
		
		// Is there any errors?
		if ( errors.length != 0 )
		{
			
			// Set errors on.
			any_errors = true;
	
			// Loop through each error.
			for ( var i = 0; i < errors.length; i++ )
			{
	
				// Get the error details.
				var id = errors[i].getElementsByTagName( "id" );
	
				if ( id.length == 0 )
					id = null;
				else
					id = id[0].firstChild.data;
	
				// Get the message.
				var message = errors[i].getElementsByTagName( "message" )[0].firstChild.data;
	
				// Post the error.
				this.postError( id, message );
	
			}  // Next error.
			
		}
		
	}
	
	// Any errors?
	if ( !any_errors )
	{
		
		// Finish validation.
		this.finishValidation();

		// Return.
		return;
		
	}

	// Show the generic error message.
	this.postError( null, 'There were errors while processing the form. Please fix them and try submitting the form again.' );

	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = false;

};


//----------------------------------------------------------------------
// Name: finishValidation()
// Desc: Finishes the validation.
//----------------------------------------------------------------------
Legato_Form.prototype.finishValidation = function()
{
	
	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = false;

	// Check for a redirect URL and if there is one redirect them.
	if ( this.redirect_url != null )
		window.location = this.redirect_url;
	
	// If the submit form flag is set, submit the form.
	if ( this.submit_form )
		return true;
	else
		return false;

};


//----------------------------------------------------------------------
// Name: postError()
// Desc: Posts an error to the form with information about what went
//       wrong and why it went wrong.
// Note: If null is passed in for element_id, the error will be placed
//       at the end of the form.
//----------------------------------------------------------------------
Legato_Form.prototype.postError = function( element_id, error )
{
	
	// Create the error node.
	var error_node = $( document.body ).create( 'p', { className: 'error' }, false, error );
	
	// What type of placement?
	if ( element_id == null )
	{

		// Do we have a submit button?
		if ( this.submit_button != null )
		{
			
			// A group or not?
			if ( $( this.submit_button.parentNode.parentNode ).hasClass( 'group_elements' ) )			
				this.submit_button.parentNode.parentNode.parentNode.insertBefore( error_node, this.submit_button.parentNode.parentNode.parentNode.firstChild );
			else
				this.submit_button.parentNode.insertBefore( error_node, this.submit_button.parentNode.firstChild );
		}
		else
			this.form.addContent( error_node );

	}  // End if general error message.
	else
	{

		var html_element = $$( element_id );

		// Group or normal?
		if ( $( html_element.parentNode.parentNode ).hasClass( 'group' ) )
			html_element.parentNode.parentNode.insertBefore( error_node, html_element.parentNode.parentNode.firstChild );
		else
			html_element.parentNode.insertBefore( error_node, html_element.parentNode.firstChild );

	}  // End if normal/group error message.
	
};


//----------------------------------------------------------------------
// Name: cleanupForm()
// Desc: Cleans up the form to make it ready for form validation.
//----------------------------------------------------------------------
Legato_Form.prototype.cleanupForm = function()
{

	// Get all the forms error's.
	var errors = this.form.cssSelect( 'p.error' );

	// Loop through each error.
	// We get the length before hand, because we take elements away from the
	// array in the loop.
	for ( var i = 0; i < errors.length; i++ )
	{

		// Get the element. We retrieve the 0th element because we remove
		// the child below, and the next one will fall in this place.
		var error_element = errors[i];

		// Is this an error element?
		error_element.remove();  // Remove the element.


	}  // Next error node.

};


//----------------------------------------------------------------------
// Name: getQueryString()
// Desc: Concatenates all the managed input elements in to a query
//       string suitable for appending to a URL.
//----------------------------------------------------------------------
Legato_Form.prototype.getQueryString = function()
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

};


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
// Class: Legato_Animation_Controller
// Stores the animations parameters.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Animation_Controller()
// Class constructor. You don't need to instantiate this as it is used by
// the system.
//------------------------------------------------------------------------
function Legato_Animation_Controller()
{

	// Store the default values.
	this.move              = { to:    new Legato_Structure_Point(),
	                           by:    new Legato_Structure_Point(),
							   ease:  Legato_Animation.EASE_NONE };

	this.width             = { to:    null,
	                           by:    null,
							   ease:  Legato_Animation.EASE_NONE };

	this.height            = { to:    null,
	                           by:    null,
							   ease:  Legato_Animation.EASE_NONE };

	this.opacity           = { to:    null,
	                           by:    null,
							   ease:  Legato_Animation.EASE_NONE };

	this.background_color  = { to:    new Legato_Structure_Color(),
	                           by:    new Legato_Structure_Color() };

	this.border_color      = { to:    new Legato_Structure_Color(),
	                           by:    new Legato_Structure_Color() };

	this.text_color        = { to:    new Legato_Structure_Color(),
	                           by:    new Legato_Structure_Color() };

	this.delay             = 0;

}


//------------------------------------------------------------------------
// Class: Legato_Animation
// Holds a single animation for an element and the necessary methods to
// handle it.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Class Constants
//------------------------------------------------------------------------
Legato_Animation.EASE_NONE          = 0;
Legato_Animation.EASE_IN            = 1;
Legato_Animation.EASE_OUT           = 2;
Legato_Animation.EASE_BOTH          = 3;
Legato_Animation.STRONG_EASE_IN     = 4;
Legato_Animation.STRONG_EASE_OUT    = 5;
Legato_Animation.STRONG_EASE_BOTH   = 6;
Legato_Animation.BACK_EASE_IN       = 7;
Legato_Animation.BACK_EASE_OUT      = 8;
Legato_Animation.BACK_EASE_BOTH     = 9;
Legato_Animation.BOUNCE_EASE_IN     = 10;
Legato_Animation.BOUNCE_EASE_OUT    = 11;
Legato_Animation.BOUNCE_EASE_BOTH   = 12;
Legato_Animation.ELASTIC_EASE_IN    = 13;
Legato_Animation.ELASTIC_EASE_OUT   = 14;
Legato_Animation.ELASTIC_EASE_BOTH  = 15;


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Animation()
// Class constructor.
//
// Parameters:
//     element - The DOM element that you'd like to animate.
//     run_time - The length that you'd like the animation to run.
//------------------------------------------------------------------------
function Legato_Animation( element, run_time )
{

	// Store the values.
	this.element               = $( element );
	this.element_properties    = Object();
	this.run_time              = run_time;
	this.controller            = new Legato_Animation_Controller();

	this.onStart               = null;
	this.onInterval            = null;
	this.onAdvance             = null;
	this.onEventFrame          = null;
	this.onStop                = null;
	this.onFinish              = null;

	// These values are used by the Legato_Animation system internally.
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
	this.offset_pos            = new Legato_Structure_Point();
	this.offset_back_color     = new Legato_Structure_Color();
	this.offset_border_color   = new Legato_Structure_Color();
	this.offset_text_color     = new Legato_Structure_Color();
	this.offset_opacity        = null;

	this.desired_width         = null;
	this.desired_height        = null;
	this.desired_pos           = new Legato_Structure_Point();
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
Legato_Animation.prototype.addEventFrame = function( time_offset, event_func )
{

  // Add the new event frame to the animation.
	this.event_frames.push( { time_offset: time_offset, event_func: event_func, triggered: false } );

}


//------------------------------------------------------------------------
// Function: start()
// Sets the animation to start playing.
//------------------------------------------------------------------------
Legato_Animation.prototype.start = function()
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
		this.begin_width = this.element.dimensions()[0];

		// Get the desired width and offset width.
		this.desired_width  = (this.controller.width.to != null) ? (this.controller.width.to) : (this.begin_width + this.controller.width.by);
		this.offset_width   = this.desired_width - this.begin_width;

	}

	// Height.
	if ( this.controller.height.to != null || this.controller.height.by != null )
	{

		// Get the element's height.
		this.begin_height = this.element.dimensions()[1];

		// Get the desired height and offset height.
		this.desired_height  = (this.controller.height.to != null) ? (this.controller.height.to) : (this.begin_height + this.controller.height.by);
		this.offset_height   = this.desired_height - this.begin_height;

	}

	// Position.
	if ( this.controller.move.to.X != null || this.controller.move.to.Y != null || this.controller.move.by.X != null || this.controller.move.by.Y != null )
	{

		// Get the element's position.
		this.begin_pos = this.element.position();
		this.begin_pos = new Legato_Structure_Point( this.begin_pos[0], this.begin_pos[1] );

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
		this.begin_opacity = this.element.opacity();
		
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
		this.begin_back_color   = new Legato_Structure_Color( this.element.getStyle( 'background-color' ).substring( 1 ) );
		this.desired_back_color = new Legato_Structure_Color( this.begin_back_color.toHexString() );

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
		this.begin_border_color   = new Legato_Structure_Color( this.element.getStyle( 'border-color' ).substring( 1 ) );
		this.desired_border_color = new Legato_Structure_Color( this.begin_border_color.toHexString() );

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
		this.begin_text_color   = new Legato_Structure_Color( this.element.getStyle( 'color' ).substring( 1 ) );
		this.desired_text_color = new Legato_Structure_Color( this.begin_text_color.toHexString() );
		
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
	Legato_Animation_Manager.addAnimation( this );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceWidth()
// Advances the width.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceWidth = function()
{

	// Get the new width.
	var new_width = Legato_Animation.tweenValue( this.controller.width.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_width, this.offset_width );

	// Bounds.
	new_width = Math.max( new_width, 0 );

	// Set the new width on the element.
	this.element.dimensions( Math.ceil( new_width ), null );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceHeight()
// Advances the height.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceHeight = function()
{

	// Get the new height.
	var new_height = Legato_Animation.tweenValue( this.controller.height.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_height, this.offset_height );
	
	// Bounds.
	new_height = Math.max( new_height, 0 );
	
	// Set the new height on the element.
	this.element.dimensions( null, Math.ceil( new_height ) );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advancePosition()
// Advances the position.
//------------------------------------------------------------------------
Legato_Animation.prototype.advancePosition = function()
{

	// Updating X position?
	if ( this.offset_pos.X != null )
	{

		// Get the new X position.
		var new_X_pos = Legato_Animation.tweenValue( this.controller.move.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_pos.X, this.offset_pos.X );
		
		// Bounds.
		new_X_pos = Math.max( new_X_pos, 0 );

		// Set the new X position on the element.
		this.element.position( Math.ceil( new_X_pos ), null );

	}  // End if updating X position.

	// Updating Y position?
	if ( this.offset_pos.Y != null )
	{

		// Get the new Y position.
		var new_Y_pos = Legato_Animation.tweenValue( this.controller.move.ease, (this.current_time - this.controller.delay), this.run_time, this.begin_pos.Y, this.offset_pos.Y );
		
		// Bounds.
		new_Y_pos = Math.max( new_Y_pos, 0 );

		// Set the new Y position on the element.
		this.element.position( null, Math.ceil( new_Y_pos ) );

	}  // End if updating Y position.

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceOpacity()
// Advances the opacity.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceOpacity = function()
{

	// Get the new opacity.
	var new_opacity = (Legato_Animation.tweenValue( this.controller.opacity.ease, (this.current_time - this.controller.delay), this.run_time, (this.begin_opacity * 100), (this.offset_opacity * 100) ) / 100);
	
	// Bounds.
	new_opacity = Math.min( Math.max( new_opacity, 0 ), 1 );
	
	// Set the new opacity on the element.
	this.element.opacity( new_opacity );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceBackgroundColor()
// Advances the background color.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceBackgroundColor = function()
{

	// Set the new back color as the beginning color.
	var new_back_color = new Legato_Structure_Color( this.begin_back_color.toHexString() );

	// Updating red value?
	if ( this.offset_back_color.R != null )
	{

		// Get the new background color.
		new_back_color.R = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.R, this.offset_back_color.R ) );
	
	}  // End if updating red value.

	// Updating green value?
	if ( this.offset_back_color.G != null )
	{

		// Get the new background color.
		new_back_color.G = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.G, this.offset_back_color.G ) );

	}  // End if updating red value.

	// Updating blue value?
	if ( this.offset_back_color.B != null )
	{

		// Get the new background color.
		new_back_color.B = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_back_color.B, this.offset_back_color.B ) );

	}  // End if updating red value.
	
	// Bounds.
	new_back_color.R = Math.min( Math.max( new_back_color.R, 0 ), 255 );
	new_back_color.G = Math.min( Math.max( new_back_color.G, 0 ), 255 );
	new_back_color.B = Math.min( Math.max( new_back_color.B, 0 ), 255 );
	
	// Set the new background color on the element.
	this.element.setStyle( 'background-color', '#' + new_back_color.toHexString() );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceBorderColor()
// Advances the border color.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceBorderColor = function()
{

	// Set the new border color as the beginning color.
	var new_border_color = new Legato_Structure_Color( this.begin_border_color.toHexString() );

	// Updating red value?
	if ( this.offset_border_color.R != null )
	{

		// Get the new border color.
		new_border_color.R = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.R, this.offset_border_color.R ) );

	}  // End if updating red value.

	// Updating green value?
	if ( this.offset_back_color.G != null )
	{

		// Get the new border color.
		new_border_color.G = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.G, this.offset_border_color.G ) );

	}  // End if updating red value.

	// Updating blue value?
	if ( this.offset_border_color.B != null )
	{

		// Get the new border color.
		new_border_color.B = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_border_color.B, this.offset_border_color.B ) );

	}  // End if updating red value.
	
	// Bounds.
	new_border_color.R = Math.min( Math.max( new_border_color.R, 0 ), 255 );
	new_border_color.G = Math.min( Math.max( new_border_color.G, 0 ), 255 );
	new_border_color.B = Math.min( Math.max( new_border_color.B, 0 ), 255 );

	// Set the new border color on the element.
	this.element.setStyle( 'border-color', '#' + new_border_color.toHexString() );
	
}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceTextColor()
// Advances the text color.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceTextColor = function()
{
	
	// Set the new text color as the beginning color.
	var new_text_color = new Legato_Structure_Color( this.begin_text_color.toHexString() );

	// Updating red value?
	if ( this.offset_text_color.R != null )
	{
		
		// Get the new text color.
		new_text_color.R = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.R, this.offset_text_color.R ) );

	}  // End if updating red value.

	// Updating green value?
	if ( this.offset_text_color.G != null )
	{

		// Get the new text color.
		new_text_color.G = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.G, this.offset_text_color.G ) );

	}  // End if updating red value.

	// Updating blue value?
	if ( this.offset_text_color.B != null )
	{

		// Get the new text color.
		new_text_color.B = Math.ceil( Legato_Animation.tweenValue( Legato_Animation.EASE_NONE, (this.current_time - this.controller.delay), this.run_time, this.begin_text_color.B, this.offset_text_color.B ) );

	}  // End if updating red value.
	
	// Bounds.
	new_text_color.R = Math.min( Math.max( new_text_color.R, 0 ), 255 );
	new_text_color.G = Math.min( Math.max( new_text_color.G, 0 ), 255 );
	new_text_color.B = Math.min( Math.max( new_text_color.B, 0 ), 255 );

	// Set the new text color on the element.
	this.element.setStyle( 'color', '#' + new_text_color.toHexString() );

}


//------------------------------------------------------------------------
// (Exclude)
// Function: advanceFrame()
// Carries out the next frame of the animation.
//------------------------------------------------------------------------
Legato_Animation.prototype.advanceFrame = function()
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
Legato_Animation.prototype.stop = function()
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
Legato_Animation.prototype.finish = function()
{

	// Set the animation's status to not playing.
	this.status = false;
	
	// Get rid of any animation errors. Set the desired values on the elements.
	if ( this.desired_width        ) this.element.dimensions( this.desired_width, null );
	if ( this.desired_height       ) this.element.dimensions( null, this.desired_height );

	if ( this.desired_pos.X        ) this.element.position( this.desired_pos.X, null );
	if ( this.desired_pos.Y        ) this.element.position( null, this.desired_pos.Y );

	if ( this.desired_opacity      ) this.element.opacity( this.desired_opacity );

	if ( this.desired_back_color   ) this.element.setStyle( 'background-color', '#' + this.desired_back_color.toHexString() );

	if ( this.desired_border_color ) this.element.setStyle( 'border-color', '#' + this.desired_border_color.toHexString() );

	if ( this.desired_text_color   ) this.element.setStyle( 'color', '#' + this.desired_text_color.toHexString() );

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
Legato_Animation.tweenValue = function( ease_type, current_time, duration, begin_val, change_val )
{
	
	// What easing equation?
	switch( ease_type )
	{
		
	// EASE NONE
	case Legato_Animation.EASE_NONE:	
		return change_val * (current_time / duration) + begin_val;
		
	// EASE IN
	case Legato_Animation.EASE_IN:
		return change_val * (current_time /= duration) * current_time + begin_val;
		
	// EASE OUT
	case Legato_Animation.EASE_OUT:
		return -change_val * (current_time /= duration) * (current_time - 2) + begin_val;
		
	// EASE BOTH
	case Legato_Animation.EASE_BOTH:
		
		if ( (current_time /= duration / 2) < 1 ) 
			return change_val / 2 * current_time * current_time + begin_val;

		return -change_val / 2 * ((--current_time) * (current_time - 2) - 1) + begin_val;
		
	// STRONG EASE IN
	case Legato_Animation.STRONG_EASE_IN:
		return change_val * (current_time /= duration) * current_time * current_time * current_time + begin_val;
	
	// STRONG EASE OUT	
	case Legato_Animation.STRONG_EASE_OUT:
		return -change_val * ((current_time = current_time / duration - 1) * current_time * current_time * current_time - 1) + begin_val;
		
	// STRONG EASE BOTH
	case Legato_Animation.STRONG_EASE_BOTH:
	
		if ( (current_time /= duration / 2) < 1 ) 
			return change_val / 2 * current_time * current_time * current_time * current_time + begin_val;

		return -change_val / 2 * ((current_time -= 2) * current_time * current_time * current_time - 2) + begin_val;
		
	// BACK EASE IN
	case Legato_Animation.BACK_EASE_IN:
		return change_val * (current_time /= duration) * current_time * (2.70158 * current_time - 1.70158) + begin_val;
		
	// BACK EASE OUT
	case Legato_Animation.BACK_EASE_OUT:
		return change_val * ((current_time = current_time / duration - 1) * current_time * (2.70158 * current_time + 1.70158) + 1) + begin_val;
		
	// BACK EASE BOTH
	case Legato_Animation.BACK_EASE_BOTH:
		
		if ( (current_time /= duration / 2) < 1 ) 
			return change_val / 2 * (current_time * current_time * (3.5949095 * current_time - 2.5949095)) + begin_val;

		return change_val / 2 * ((current_time -= 2) * current_time * (3.5949095 * current_time + 2.5949095) + 2) + begin_val;
		
	// BOUNCE EASE IN
	case Legato_Animation.BOUNCE_EASE_IN:
		
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
	case Legato_Animation.BOUNCE_EASE_OUT:
	
		if ( (current_time /= duration) < (1 / 2.75) )
		  return change_val * (7.5625 * current_time * current_time) + begin_val;
		else if ( current_time < (2 / 2.75 ) )
		  return change_val * (7.5625 * (current_time -= (1.5 / 2.75)) * current_time + 0.75) + begin_val;
		else if ( current_time < (2.5 / 2.75) )
		  return change_val * (7.5625 * (current_time -= (2.25 / 2.75)) * current_time + 0.9375) + begin_val;
		else
		  return change_val * (7.5625 * (current_time -= (2.625 / 2.75)) * current_time + 0.984375) + begin_val;
		  
	// BOUNCE EASE BOTH
	case Legato_Animation.BOUNCE_EASE_BOTH:
	
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
	case Legato_Animation.ELASTIC_EASE_IN:
	
		if ( current_time == 0 ) 
			return begin_val;
			
		if ( (current_time /= duration) == 1 ) 
			return begin_val + change_val;

		var p = duration * 0.3;
		var a = change_val;
		var s = p / 4;

		return -(a * Math.pow( 2, 10 * (current_time -= 1) ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p )) + begin_val;
		
	// ELASTIC EASE OUT
	case Legato_Animation.ELASTIC_EASE_OUT:
		
		if ( current_time == 0 ) 
			return begin_val;
			
		if ( (current_time /= duration) == 1 ) 
			return begin_val + change_val;

		var p = duration * 0.3;
		var a = change_val;
		var s = p / 4;

		return a * Math.pow( 2, -10 * current_time ) * Math.sin( (current_time * duration - s) * (2 * Math.PI) / p ) + change_val + begin_val;
		
	// ELASTIC EASE BOTH
	case Legato_Animation.ELASTIC_EASE_BOTH:
	
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
// Class: Legato_Animation_Sequence
// Stores a sequence of <Legato_Animation> objects.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Static Variables
//------------------------------------------------------------------------
Legato_Animation_Sequence.sequences = new Array();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Constructor: Legato_Animation_Sequence()
// Class constructor.
//
// Parameters:
//     options - An optional object of options for the Animation Sequence.
//------------------------------------------------------------------------
function Legato_Animation_Sequence( options )
{

	// Store the default values.
	this.animations               = new Array();
	this.current_animation_index  = 0;
	this.sequence_index           = Legato_Animation_Sequence.sequences.length;
	this.status                   = false;
	this.options                  = options;
	
	// Callbacks.
	this.onStart               = null;
	this.onAdvance             = null;
	this.onLoop                = null;
	this.onFinish              = null;

	// Store this animation sequence in the global sequences array.
	Legato_Animation_Sequence.sequences[this.sequence_index] = this;

}


//------------------------------------------------------------------------
// Function: addAnimation()
// Adds an <Legato_Animation> object to the animation sequence.
//
// Parameters:
//     animation - An <Legato_Animation> object that you would like to set up
//                 to play in the animation. Will add it at the end of the
//                 sequence.
//------------------------------------------------------------------------
Legato_Animation_Sequence.prototype.addAnimation = function( animation )
{

	// Store the animation in the sequence.
	this.animations.push( animation );

	// Add the onFinish and onStop functions.
	Legato_Events_Handler.addEvent( animation, "onFinish", Legato_Animation_Sequence.nextAnimation );
	Legato_Events_Handler.addEvent( animation, "onStop", Legato_Animation_Sequence.nextAnimation );

	// Store the sequence index in the animation.
	animation.sequence_index = this.sequence_index;

}


//------------------------------------------------------------------------
// Function: start()
// Sets the Animation Sequence to start playing.
//------------------------------------------------------------------------
Legato_Animation_Sequence.prototype.start = function()
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
Legato_Animation_Sequence.prototype.reset = function()
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
Legato_Animation_Sequence.nextAnimation = function( animation )
{

	// Get the animation sequence.
	var animation_sequence = Legato_Animation_Sequence.sequences[animation.sequence_index];

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
// Class: Legato_Animation_Manager
// Manages each animation. All the animations are incremented through the
// manager.
//------------------------------------------------------------------------
Legato_Animation_Manager =
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
			this.interval_handle = setInterval( Legato_Animation_Manager.advanceAnimations, this.increment_speed, null );

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
		for ( var i = 0; i < Legato_Animation_Manager.playing_animations.length; i++ )
		{

			// Get the animation from the array.
			var animation = Legato_Animation_Manager.playing_animations[i];

			// Advance the animation.
			var continue_playing = animation.advanceFrame();

			// Is the animation done playing?
			if ( !continue_playing )
			{
				
				// Remove the animation from the playing animations array.
				Legato_Animation_Manager.playing_animations.splice( i, 1 );
				
				// Finish up the animation.
				animation.finish();
				
				// If we don't have any more animations to play, stop
				// JavaScript from calling this function again.
				if ( Legato_Animation_Manager.playing_animations.length == 0 )
				{
					clearInterval( Legato_Animation_Manager.interval_handle );
					Legato_Animation_Manager.interval_handle = null;
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
		var section_links = $$( section + '_links' );

		// Set it as visible.
		section_links.setStyle( 'display', 'block' );

		// If we don't have this section's original height, get it.
		if ( !toggleSection.heights[section] )
			toggleSection.heights[section] = section_links.dimensions()[1];

		// Set the height to nothing so we can expand it with an animation.
		section_links.dimensions( null, 0 );

		var anim = new Legato_Animation( section_links, 1000 );
		anim.controller.height.to = toggleSection.heights[section];
		anim.controller.height.ease = Legato_Animation.STRONG_EASE_IN;
		anim.start();

		// Let's switch the + to a -
		var switcher = document.getElementById( section + "_switcher" );
		switcher.innerHTML = "-" + switcher.innerHTML.substring( 1 );

	}
	else
	{

		// Set the section as active.
		toggleSection[section] = false;

		var section_links = $$( section + '_links' );

		// If we don't have this section's original height, get it.
		if ( !toggleSection.heights[section] )
			toggleSection.heights[section] = section_links.dimensions()[1];

		var anim = new Legato_Animation( section_links, 1000 );
		anim.controller.height.to = 1;
		anim.onFinish = function(){ section_links.dimensions( null, toggleSection.heights[section] );
		                            section_links.setStyle( 'display', 'none' ); };
		anim.start();

		// Let's switch the - to a +
		var switcher = $$( section + "_switcher" );
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

	var elem_html = '<a class="avatar-close" href="" onclick="hideAvatar(); return false;">X CLOSE</a><iframe id="iframe-test" scrolling="no" src="' + SITE_URL + '/avatar/"></iframe>';

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

	var inactive_menu = $$( 'inactive_menu' );

	if ( inactive_menu )
		inactive_menu.opacity( 0.45 );

	var retirement_links = $$( 'retirement_links' );
	var education_links = $$( 'education_links' );

	if ( retirement_links.getStyle( 'display' ) != "none" )
		toggleSection.retirement = true;

	if ( education_links.getStyle( 'display' ) != "none" )
		toggleSection.education = true;

}

Legato_Events_Handler.DOMReady( onloadHandler );
/**
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectUtil=="undefined"){deconcept.SWFObjectUtil=new Object();}deconcept.SWFObject=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObject.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObject.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObject.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObject;var SWFObject=deconcept.SWFObject;