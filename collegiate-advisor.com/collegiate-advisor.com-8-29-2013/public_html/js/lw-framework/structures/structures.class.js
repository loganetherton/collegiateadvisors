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


