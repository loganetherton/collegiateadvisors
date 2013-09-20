// First, before we do anything, load the Yahoo! UI DOM library.
document.writeln("<script type='text/javascript' src='" + LW_PATH_TO_API + "/yahoo-ui/yahoo-min.js'><" + "/script>");
document.writeln("<script type='text/javascript' src='" + LW_PATH_TO_API + "/yahoo-ui/dom-min.js'><" + "/script>");


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


