//------------------------------------------------------------------------
// Package: Events Handler
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Class: Legato_Events_Handler
// Used to handle events used by DOM elements.
//------------------------------------------------------------------------
Legato_Events_Handler =
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
		if ( !event_handler.Legato_event_id && event_id == null ) 
			event_handler.Legato_event_id = "Legato_EVENT_" + Legato_Events_Handler.id_counter++;
		else if ( event_id != null )
			event_handler.Legato_event_id = event_id;
		
		// If no events array, create one.
		if ( !element.Legato_events ) element.Legato_events = {};
		
		// Get the element's event handlers for this type.
		var handlers = element.Legato_events[event_type];

		// If no handlers yet,create the array.
		if ( !handlers )
		{
			// Create the array.
			handlers = element.Legato_events[event_type] = {};
			
			// If there already is an event assigned, add it as the first handler.
			if ( element[event_type] )
				Legato_Events_Handler.addEvent( element, event_type, element[event_type], "Legato_BASE_EVENT" );
		}
		
		// Store the event handler.
		handlers[event_handler.Legato_event_id] = event_handler;
		
		// Store it in our system.
		if ( !Legato_Events_Handler.events[event_handler.Legato_event_id] ) 
			Legato_Events_Handler.events[event_handler.Legato_event_id] = [];
			
		Legato_Events_Handler.events[event_handler.Legato_event_id].push( { "element": element, "type": event_type, "handler": event_handler } );
		
		// Set the element's "event_type" event to be handled by us.
		element[event_type] = function( e ){ return Legato_Events_Handler.handleEvent( this, e, event_type ); };
		
		// Return the event's ID.
		return event_handler.Legato_event_id;

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
		
		if ( !element.Legato_events && !element.Legato_events[event_type] )
			return;
				
		// Deleting one handler or the whole event?
		delete element.Legato_events[event_type][event_handler.Legato_event_id];
		
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
		
		if ( !Legato_Events_Handler.events[event_id] )
			return;
		
		// Loop through all the event handlers attached to this event ID.
		for ( var i = 0; i < Legato_Events_Handler.events[event_id].length; i++ )
		{
			
			var event = Legato_Events_Handler.events[event_id][i];	
			
			// Remove the event.
			Legato_Events_Handler.removeEvent( event.element, event.type, event.handler );			
			delete Legato_Events_Handler.events[event_id][i];
			
		}
		
		// Delete from global array.
		delete Legato_Events_Handler.events[event_id];
		
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
		
		if ( !element.Legato_events && !element.Legato_events[event_type] )
			return;
			
		// Loop through.
		for ( var i in element.Legato_events[event_type] )
			delete element.Legato_events[event_type][i];
			
		delete element.Legato_events[event_type];
		
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
		var handlers = element.Legato_events[event_type];
		
		// Loop through and call each handler.
		for ( var i in handlers )
		{
			
			if ( !event )
				event = window.event;
			
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
		for ( var id in Legato_Events_Handler.events )
		{
			if ( !Legato_Events_Handler.events[id] )
				Legato_Events_Handler.removeEventByID( id );
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

		var pos = new Legato_Structure_Point();

		if ( !event )
			event = window.event;

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

	},
	
	
	//--------------------------------------------------------------------
	// Function: getTarget()
	// Returns the correct target that this event fired on.
	//
	// Parameters:
	//     event - The event that was fired.
	//--------------------------------------------------------------------
	getTarget: function( event )
	{

		// Make sure we have a correct event object.
		if ( !event ) 
			event = window.event;

		// Return the correct target.
		if ( event.target )
			return event.target;
		else if ( event.srcElement )
			return event.srcElement;
		else
			return false;

	}

};

// Make sure we unload everything when the page unloads.
Legato_Events_Handler.addEvent( window, "onunload", Legato_Events_Handler.unloadEvents );




// Developed by Robert Nyman/DOMAssistant team
// code/licensing: http://code.google.com/p/domassistant/ 
// documentation: http://www.domassistant.com/documentation
// version 2.7.1.1
Legato_Events_Handler.DOMLoad = function () {
	var DOMLoaded = false;
	var DOMLoadTimer = null;
	var functionsToCall = [];
	var addedStrings = {};
	var errorHandling = null;
	var execFunctions = function () {
		for (var i=0, il=functionsToCall.length; i<il; i++) {
			try {
				functionsToCall[i]();
			}
			catch (e) {
				if (errorHandling && typeof errorHandling === "function") {
					errorHandling(e);
				}
			}
		}
		functionsToCall = [];
	};
	var DOMHasLoaded = function () {
		if (DOMLoaded) {
			return;
		}
		DOMLoaded = true;
		execFunctions();
	};
	/* Internet Explorer */
	/*@cc_on
	@if (@_win32 || @_win64)
		if (document.getElementById) {
			document.write("<script id=\"ieScriptLoad\" defer src=\"//:\"><\/script>");
			document.getElementById("ieScriptLoad").onreadystatechange = function() {
				if (this.readyState === "complete") {
					DOMHasLoaded();
				}
			};
		}
	@end @*/
	/* Mozilla/Opera 9 */
	if (document.addEventListener) {
		document.addEventListener("DOMContentLoaded", DOMHasLoaded, false);
	}
	/* Safari, iCab, Konqueror */
	if (/KHTML|WebKit|iCab/i.test(navigator.userAgent)) {
		DOMLoadTimer = setInterval(function () {
			if (/loaded|complete/i.test(document.readyState)) {
				DOMHasLoaded();
				clearInterval(DOMLoadTimer);
			}
		}, 10);
	}
	/* Other web browsers */
	window.onload = DOMHasLoaded;
	
	return {
		DOMReady : function () {
			for (var i=0, il=arguments.length, funcRef; i<il; i++) {
				funcRef = arguments[i];
				if (!funcRef.DOMReady && !addedStrings[funcRef]) {
					if (typeof funcRef === "string") {
						addedStrings[funcRef] = true;
						funcRef = new Function(funcRef);
					}
					funcRef.DOMReady = true;
					functionsToCall.push(funcRef);
				}
			}
			if (DOMLoaded) {
				execFunctions();
			}
		},
		
		setErrorHandling : function (funcRef) {
			errorHandling = funcRef;
		}
	};
};

Legato_Events_Handler.DOMReady = ( new Legato_Events_Handler.DOMLoad ).DOMReady;