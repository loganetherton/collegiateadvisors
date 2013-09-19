//------------------------------------------------------------------------
// Name: LW_Widgets_PopupContainer
// Desc: Manages a container that pops up on the page next to a certain
//       element when clicked.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Class Constants
//------------------------------------------------------------------------
LW_Widgets_PopupContainer.PLACE_TOP     = 0;
LW_Widgets_PopupContainer.PLACE_BOTTOM  = 1;


//------------------------------------------------------------------------
// Static Variables
//------------------------------------------------------------------------
LW_Widgets_PopupContainer.popups = new Array();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: LW_Widgets_PopupContainer()
// Desc: Class constructor.
//------------------------------------------------------------------------
function LW_Widgets_PopupContainer( element, container, placement )
{
	
	// Store the values.
	this.element    = element;
	this.container  = container;
	this.index      = LW_Widgets_PopupContainer.popups.length;
	this.active     = false;
	
	LW_Widgets_PopupContainer.popups[this.index] = this;

	// Get the necessary variables for convenience.
	var scroll_Y_offset   = LW_DOM_Library.getScrollYOffset();
	var client_height     = LW_DOM_Library.getViewportHeight();
	var element_pos       = LW_DOM_Library.getXY( element );
	var element_height    = LW_DOM_Library.getHeight( element );
	var container_height  = LW_DOM_Library.getHeight( container );

	// Get the correct position.
	var position = new LW_Structure_Point( element_pos.X, null );

	// Only do this if no placement was passed in.
	if ( placement == null )
	{

		// Decide whether we should position the popup on the top or bottom of the element.
		if ( (element_pos.Y - scroll_Y_offset) >= (client_height * 0.5) )
		{

			// Top.
			placement = LW_Widgets_PopupContainer.PLACE_TOP;

		}  // End if top.
		else if ( ((element_pos.Y + element_height) - scroll_Y_offset) < (client_height * 0.5) )
		{

			// Bottom.
			placement = LW_Widgets_PopupContainer.PLACE_BOTTOM;

		}  // End if bottom.

	}  // End no placement.

	// Where are we placing the popup?
	if ( placement == LW_Widgets_PopupContainer.PLACE_TOP )
	{

		position.Y = (element_pos.Y - container_height);

	}  // End top.
	else if ( placement == LW_Widgets_PopupContainer.PLACE_BOTTOM )
	{

		position.Y = (element_pos.Y + element_height);

	}  // End bottom.

	// Set the container's position.
	LW_DOM_Library.setXY( container, position );

	// Hide the container.
	LW_DOM_Library.setStyle( container, "visibility", "hidden" );

	// Register the event handlers.
	LW_Events_Handler.addEvent( element, "onclick",     new Function( "LW_Widgets_PopupContainer.showContainer( " + this.index + " );" ) );
	LW_Events_Handler.addEvent( element, "onmouseout",  new Function( "LW_Widgets_PopupContainer.popups[" + this.index +"].active = false;" ) );
	LW_Events_Handler.addEvent( element, "onmouseover", new Function( "LW_Widgets_PopupContainer.popups[" + this.index +"].active = true;" ) );

	LW_Events_Handler.addEvent( container, "onmouseout",  new Function( "LW_Widgets_PopupContainer.popups[" + this.index +"].active = false;" ) );
	LW_Events_Handler.addEvent( container, "onmouseover", new Function( "LW_Widgets_PopupContainer.popups[" + this.index +"].active = true;" ) );

}


//------------------------------------------------------------------------
// Public Static Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: showContainer()
// Desc: Shows the popup container with the index specified.
//------------------------------------------------------------------------
LW_Widgets_PopupContainer.showContainer = function( index )
{

	// Retrieve the container.
	container = LW_Widgets_PopupContainer.popups[index];
	
	// Set the container as visible.
	LW_DOM_Library.setStyle( container.container, "visibility", "visible" );

}


//------------------------------------------------------------------------
// Name: hideContainers()
// Desc: Hides the popup containers that aren't active.
//------------------------------------------------------------------------
LW_Widgets_PopupContainer.hideContainers = function()
{

	// Loop through each popup container.
	for ( var i = 0; i < LW_Widgets_PopupContainer.popups.length; i++ )
	{

		// Retrieve the container.
		container = LW_Widgets_PopupContainer.popups[i];

		// If this container is not active, set as invisible.
		if ( !container.active )
     LW_DOM_Library.setStyle( container.container, "visibility", "hidden" );

	}  // Next popup container.

}

// Set the hideContainers function as a document.onclick event.
LW_Events_Handler.addEvent( document, "onclick", LW_Widgets_PopupContainer.hideContainers );

