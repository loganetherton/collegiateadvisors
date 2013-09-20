//------------------------------------------------------------------------
// Name: Legato_Widgets_PopupContainer
// Desc: Manages a container that pops up on the page next to a certain
//       element when clicked.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Class Constants
//------------------------------------------------------------------------
Legato_Widgets_PopupContainer.PLACE_TOP     = 0;
Legato_Widgets_PopupContainer.PLACE_BOTTOM  = 1;


//------------------------------------------------------------------------
// Static Variables
//------------------------------------------------------------------------
Legato_Widgets_PopupContainer.popups = new Array();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Widgets_PopupContainer()
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Widgets_PopupContainer( element, container, placement )
{
	
	// Store the values.
	this.element    = element;
	this.container  = container;
	this.index      = Legato_Widgets_PopupContainer.popups.length;
	this.active     = false;
	
	Legato_Widgets_PopupContainer.popups[this.index] = this;

	// Get the necessary variables for convenience.
	var scroll_Y_offset   = Legato_DOM_Library.getScrollYOffset();
	var client_height     = Legato_DOM_Library.getViewportHeight();
	var element_pos       = Legato_DOM_Library.getXY( element );
	var element_height    = Legato_DOM_Library.getHeight( element );
	var container_height  = Legato_DOM_Library.getHeight( container );

	// Get the correct position.
	var position = new Legato_Structure_Point( element_pos.X, null );

	// Only do this if no placement was passed in.
	if ( placement == null )
	{

		// Decide whether we should position the popup on the top or bottom of the element.
		if ( (element_pos.Y - scroll_Y_offset) >= (client_height * 0.5) )
		{

			// Top.
			placement = Legato_Widgets_PopupContainer.PLACE_TOP;

		}  // End if top.
		else if ( ((element_pos.Y + element_height) - scroll_Y_offset) < (client_height * 0.5) )
		{

			// Bottom.
			placement = Legato_Widgets_PopupContainer.PLACE_BOTTOM;

		}  // End if bottom.

	}  // End no placement.

	// Where are we placing the popup?
	if ( placement == Legato_Widgets_PopupContainer.PLACE_TOP )
	{

		position.Y = (element_pos.Y - container_height);

	}  // End top.
	else if ( placement == Legato_Widgets_PopupContainer.PLACE_BOTTOM )
	{

		position.Y = (element_pos.Y + element_height);

	}  // End bottom.

	// Set the container's position.
	Legato_DOM_Library.setXY( container, position );

	// Hide the container.
	Legato_DOM_Library.setStyle( container, "visibility", "hidden" );

	// Register the event handlers.
	Legato_Events_Handler.addEvent( element, "onclick",     new Function( "Legato_Widgets_PopupContainer.showContainer( " + this.index + " );" ) );
	Legato_Events_Handler.addEvent( element, "onmouseout",  new Function( "Legato_Widgets_PopupContainer.popups[" + this.index +"].active = false;" ) );
	Legato_Events_Handler.addEvent( element, "onmouseover", new Function( "Legato_Widgets_PopupContainer.popups[" + this.index +"].active = true;" ) );

	Legato_Events_Handler.addEvent( container, "onmouseout",  new Function( "Legato_Widgets_PopupContainer.popups[" + this.index +"].active = false;" ) );
	Legato_Events_Handler.addEvent( container, "onmouseover", new Function( "Legato_Widgets_PopupContainer.popups[" + this.index +"].active = true;" ) );

}


//------------------------------------------------------------------------
// Public Static Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: showContainer()
// Desc: Shows the popup container with the index specified.
//------------------------------------------------------------------------
Legato_Widgets_PopupContainer.showContainer = function( index )
{

	// Retrieve the container.
	container = Legato_Widgets_PopupContainer.popups[index];
	
	// Set the container as visible.
	Legato_DOM_Library.setStyle( container.container, "visibility", "visible" );

}


//------------------------------------------------------------------------
// Name: hideContainers()
// Desc: Hides the popup containers that aren't active.
//------------------------------------------------------------------------
Legato_Widgets_PopupContainer.hideContainers = function()
{

	// Loop through each popup container.
	for ( var i = 0; i < Legato_Widgets_PopupContainer.popups.length; i++ )
	{

		// Retrieve the container.
		container = Legato_Widgets_PopupContainer.popups[i];

		// If this container is not active, set as invisible.
		if ( !container.active )
     Legato_DOM_Library.setStyle( container.container, "visibility", "hidden" );

	}  // Next popup container.

}

// Set the hideContainers function as a document.onclick event.
Legato_Events_Handler.addEvent( document, "onclick", Legato_Widgets_PopupContainer.hideContainers );
