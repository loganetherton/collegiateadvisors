//------------------------------------------------------------------------
// Package: Events Handler
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Class: LW_Events_Handler
// Used to handle events used by DOM elements.
//------------------------------------------------------------------------
LW_Events_Handler =
{
	
	id_counter: 1,
	events: {},

	//--------------------------------------------------------------------
	// Function: addEvent()
	// Adds an event to an element. If there was one already assigned it
	// will chain them so that as many as the user wants can be assigned
	// without problems.
	//
	// Parameters:
	//     element - The element that you'd like to assign an event to.
	//     event_type - The type of event, eg: "onclick" "onmouseover"
	//     event_handler - The function that you would like to handle this
	//                     event.
	//     event_id - (Optional) The ID that you'd like to assign to this
	//                event. If you assign an ID, it can be used later to
	//                manage the event.
	//--------------------------------------------------------------------
	addEvent: function( element, event_type, event_handler, event_id )
	{
		
		// If we don't have an ID for this handler, add one.
		if ( !event_handler.LW_event_id && event_id == null ) 
			event_handler.LW_event_id = "LW_EVENT_" + LW_Events_Handler.id_counter++;
		else if ( event_id != null )
			event_handler.LW_event_id = event_id;
		
		// If no events array, create one.
		if ( !element.LW_events ) element.LW_events = {};
		
		// Get the element's event handlers for this type.
		var handlers = element.LW_events[event_type];

		// If no handlers yet,create the array.
		if ( !handlers )
		{
			// Create the array.
			handlers = element.LW_events[event_type] = {};
			
			// If there already is an event assigned, add it as the first handler.
			if ( element[event_type] )
				LW_Events_Handler.addEvent( element, event_type, element[event_type], "LW_BASE_EVENT" );
		}
		
		// Store the event handler.
		handlers[event_handler.LW_event_id] = event_handler;
		
		// Store it in our system.
		if ( !LW_Events_Handler.events[event_handler.LW_event_id] ) 
			LW_Events_Handler.events[event_handler.LW_event_id] = [];
			
		LW_Events_Handler.events[event_handler.LW_event_id].push( { "element": element, "type": event_type, "handler": event_handler } );
		
		// Set the element's "event_type" event to be handled by us.
		element[event_type] = function( e ){ return LW_Events_Handler.handleEvent( this, e, event_type ); };
		
		// Return the event's ID.
		return event_handler.LW_event_id;

	},
	
	
	//--------------------------------------------------------------------
	// Function: removeEvent()
	// Removes an event from a particular element.
	// Note that you can usually just call this function with the exact
	// same parameters you used to add the event.
	//
	// Parameters:
	//     element - The element that the event handler was assigned to.
	//     event_type - The type of event, eg: "onclick" "onmouseover"
	//     event_handler - The function that's currently handling this
	//                     event.
	//--------------------------------------------------------------------
	removeEvent: function( element, event_type, event_handler )
	{
		
		if ( !element.LW_events && !element.LW_events[event_type] )
			return;
		
		// Deleting one handler or the whole event?
		delete element.LW_events[event_type][event_handler.event_id];
		
	},
	
	
	//--------------------------------------------------------------------
	// Function: removeEventByID()
	// Removes the particular event(s) for the particular event ID.
	//
	// Parameters:
	//     event_id - The event ID that you'd like to remove. Will remove
	//                all events assigned to this ID used by any element.
	//--------------------------------------------------------------------
	removeEventByID: function( event_id )
	{
		
		if ( !LW_Events_Handler.events[event_id] )
			return;
			
		// Loop through all the event handlers attached to this event ID.
		for ( var i in LW_Events_Handler.events[event_id] )
		{
			
			var event = LW_Events_Handler.events[event_id][i];	
			
			// Remove the event.
			LW_Events_Handler.removeEvent( event.element, event.type, event.handler );			
			delete LW_Events_Handler.events[event_id][i];
			
		}
		
		// Delete from global array.
		delete LW_Events_Handler.events[event_id];
		
	},
	
	
	//--------------------------------------------------------------------
	// Function: removeEventType()
	// Removes all the event handlers for a particular event type.
	// Note that if you've assigned multiple handlers to a particular
	// event, this will remove all of them.
	//
	// Parameters:
	//     element - The element that the event handlers were assigned to.
	//     event_type - The type of event that you'd like to completely
	//                  remove, eg: "onclick" "onmouseover"
	//--------------------------------------------------------------------
	removeEventType: function( element, event_type )
	{
		
		if ( !element.LW_events && !element.LW_events[event_type] )
			return;
			
		// Loop through.
		for ( var i in element.LW_events[event_type] )
			delete element.LW_events[event_type][i];
			
		delete element.LW_events[event_type];
		
	},
	
	
	//--------------------------------------------------------------------
	// (Exclude)
	// Function: handleEvent()
	// Handles a particular event for a particular element.
	//--------------------------------------------------------------------
	handleEvent: function( element, event, event_type )
	{
		
		var ret = true;
		
		// Get the event's handlers.
		var handlers = element.LW_events[event_type];
		
		// Loop through and call each handler.
		for ( var i in handlers )
		{
			
			if ( !event )
				event = null;
			
			if ( handlers[i]( event ) === false )
				ret = false;
				
		}
		
		return ret;
		
	},
	
	
	//--------------------------------------------------------------------
	// (Exclude)
	// Function: unloadEvents()
	// Safely unloads all the events used by the system. Called when the
	// page unloads.
	//--------------------------------------------------------------------
	unloadEvents: function()
	{
		
		// Loop through all the events and remove them.
		for ( var id in LW_Events_Handler.events )
		{
			if ( !LW_Events_Handler.events[id] )
				LW_Events_Handler.removeEventByID( id );
		}
		
	},


	//--------------------------------------------------------------------
	// Function: getCursorPos()
	// Returns the position of the cursor at the time a particular event
	// fired in page coordinates.
	//
	// Parameters:
	//     event - The event that was fired.
	//--------------------------------------------------------------------
	getCursorPos: function( event )
	{

		var pos = new LW_Structure_Point();

		if ( !event ) var event = window.event;

		if ( event.pageX || event.pageY )
		{

			pos.X = event.pageX;
			pos.Y = event.pageY;

		}
		else if ( event.clientX || event.clientY )
		{

			pos.X = event.clientX + document.body.scrollLeft;
			pos.Y = event.clientY + document.body.scrollTop;

		}

		// Finally, return the cursor position.
		return pos;

	}

}

// Make sure we unload everything when the page unloads.
LW_Events_Handler.addEvent( window, "onunload", LW_Events_Handler.unloadEvents );