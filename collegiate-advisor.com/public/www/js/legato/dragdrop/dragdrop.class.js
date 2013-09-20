//------------------------------------------------------------------------
// Name: Legato_DragDrop_Handle
// Desc: Contains all the information for one Drag&Drop Handle.
//------------------------------------------------------------------------
function Legato_DragDrop_Handle( element )
{

	// Set variables to default values.
	this.element            = $( element );  // The HTML element.
	this.group              = null;     // The handle's group, if there is one.
	this.constrainX         = false;    // Constrain to the X axis?
	this.constrainY         = false;    // Constrain to the Y axis?
	this.movable_region     = null;     // Is there a certain region this handle can move in?
	this.snap_threshold     = 0;        // The threshold in pixels before the element snaps back into place.
	this.targets            = Array();  // The targets array.

	// Store the intersection information.
	this.intersect_test     = Legato_DragDrop.INTERSECT_THRESHOLD;
	this.on_intersect       = Legato_DragDrop.ON_INTERSECT_NOTHING;

	// Private variables used by the system.
	this.index              = null;     // The handle's index.
	this.mouse_pos          = null;     // The mouse position last time this was updated.
	this.initial_pos        = null;     // The initial position of the element, before dragging.
	this.on_target          = false;    // Is the handle currently on a target?
	this.last_target        = null;     // The target this handle was last on.

	// The element's Z index, in case we change it.
	this.zindex             = element ? element.getStyle( 'z-index' ) : 1;

	//////////////////////////////////////
	// Functions
	this.onStartDrag        = null;  // Called when the handle starts being dragged.
	this.onDrag             = null;  // Called when the handle is being dragged.
	this.onIntersect        = null;  // Called when the handle is intersected with a target.
	this.onStopDrag         = null;  // Called when the handle stops being dragged.

}

//////////////////////////////////////////
// Adds a drop target to a drag handle.
Legato_DragDrop_Handle.prototype.addDropTarget = function( target )
{

	// Store the target in the handle's array.
	this.targets.push( target );

}


//------------------------------------------------------------------------
// Name: Legato_DragDrop_Target
// Desc: Contains all the information for storing drop targets.
//------------------------------------------------------------------------
function Legato_DragDrop_Target( element )
{

	// Set variables to default values.
	this.element     = element;  // The HTML element.

	// Private variables used by the system.
	this.index       = null;     // The handle's index.

	//////////////////////////////////////
	// Functions
	this.onDragOver  = null;   // Called when a handle is dragged over this target.
	this.onDragOut   = null;   // Called when a handle is dragged off of this target.
	this.onDrop      = null;   // Called when a handle is dropped on to this target.

}

/*
//------------------------------------------------------------------------
// Name: Legato_DragDrop_Group
// Desc: Contains all the information for storing information about a
//       Drag&Drop Group.
//------------------------------------------------------------------------
function Legato_DragDrop_Group( element )
{

	// Set variables to default values.
	this.element         = element;                   // The HTML element.
	this.model_handle    = new Legato_DragDrop_Handle();  // The handle to model all group handles by.

  // Store the intersection information.
	this.model_handle.intersect_test  = Legato_DragDrop.INTERSECT_HALF_DIMENSIONS;
	this.model_handle.on_intersect    = Legato_DragDrop.ON_INTERSECT_SWITCH_POSITIONS;

	// Private variables used by the system.
	this.handles         = Array();                // The array of the groups's handle IDs.

}

//////////////////////////////////////////
// Adds a drag handle to the group.
Legato_DragDrop_Group.prototype.addElement = function( element )
{

	// Create a drag handle for this element.
	var drag_handle = new Legato_DragDrop_Handle( element );

	// Store it in the group's handles array.
	this.handles.push( drag_handle );

}
*/

//------------------------------------------------------------------------
// Name: Legato_DragDrop
// Desc: The Drag&Drop manager. Contains methods for the dragging and
//       dropping of handles.
//------------------------------------------------------------------------
// Add an onmouseup event to the document.
Legato_Events_Handler.addEvent( document, "onmouseup", function(){ return Legato_DragDrop.stopDragging(); } );

Legato_DragDrop =
{

	////////////////////////////////////////
	// Constants.
	INTERSECT_HALF_DIMENSIONS: 0,
	INTERSECT_CURSOR_POS:      1,
	INTERSECT_THRESHOLD:       2,

	ON_INTERSECT_SWITCH_POSITIONS: 0,
	ON_INTERSECT_SNAP:             1,
	ON_INTERSECT_NOTHING:          2,

	// The variables we need during dragging.
	started_dragging:  false,
	active_handle:     null,
	last_replaced_pos: null,
	new_position:      new Legato_Structure_Point(),
	old_mouse_pos:     new Legato_Structure_Point(),

	// Array of drag handles.
	drag_handles: Array(),

	// Array of drop targets.
	drop_targets: Array(),

	// Array of groups that this DragDrop is managing.
	drag_groups: Array(),


	//----------------------------------------------------------------------
	// Name: addDropTarget()
	// Desc: Adds an a drop target to the system.
	//----------------------------------------------------------------------
	addDropTarget: function( target )
	{

		// Set the target's index.
		target.index = Legato_DragDrop.drop_targets.length;

		// Store the drop target in the global array.
		Legato_DragDrop.drop_targets.push( $( target ) );

	},


	//----------------------------------------------------------------------
	// Name: addDragHandle()
	// Desc: Adds an element that can be dragged around.
	//----------------------------------------------------------------------
	addDragHandle: function( handle )
	{

		// Set the handle's index.
		handle.index = Legato_DragDrop.drag_handles.length;

		// Subtract the drag handle's width and height from the movable region, if there is one.
		if ( handle.movable_region )
		{
			
			handle.movable_region.max_point.X = handle.movable_region.max_point.X - handle.element.offsetWidth;
			handle.movable_region.max_point.Y = handle.movable_region.max_point.Y - handle.element.offsetHeight;
			
		}  // End if movable region.

		// Add an onmousedown event to the element.
		Legato_Events_Handler.addEvent( handle.element, "onmousedown", function( e ){ return Legato_DragDrop.preStart( handle, e ); } );

		// Store the drag handle.
		Legato_DragDrop.drag_handles.push( handle );

	},

	/*
	//----------------------------------------------------------------------
	// Name: addDragGroup()
	// Desc: Adds all the items in the group to be managed by the system.
	//----------------------------------------------------------------------
	addDragGroup: function( group )
	{

		// Set the group's index.
		group.index = Legato_DragDrop.drag_groups.length;

		// Store the group.
		Legato_DragDrop.drag_groups.push( group );

		// Is there a list element?
		if ( group.element )
		{

			// Loop through each list item.
			for ( var i = 0; i < group.element.childNodes.length; i++ )
			{

				var element = group.element.childNodes[i];

				// Only process LI elements.
				if ( !element || element.tagName != "LI" ) continue;

				// Create a handle for this list element.
				var element_handle = new Legato_DragDrop_Handle( element );

				// Add the handle to the group's handle array.
				group.handles.push( element_handle );

			}  // Next list item.

		}  // End if list element.

		// Get the region of the group.
		var movable_region = (group.element) ? new Legato_Structure_Region( group.element ) : group.model_handle.movable_region;

		// Loop through each drag handle.
		for ( var i = 0; i < group.handles.length; i++ )
		{

			var handle = group.handles[i];

			// Set the handle's parameters.
			handle.group           = group;
			handle.constrainX      = group.model_handle.constrainX;
			handle.constrainY      = group.model_handle.constrainY;
			handle.movable_region  = (movable_region) ? new Legato_Structure_Region( new Legato_Structure_Point( movable_region.min_point.X, movable_region.min_point.Y ), new Legato_Structure_Point( movable_region.max_point.X, movable_region.max_point.Y ) ) : null;
			handle.snap_threshold  = group.model_handle.snap_threshold;
			handle.intersect_test  = group.model_handle.intersect_test;
			handle.on_intersect    = group.model_handle.on_intersect;
			handle.show_handle     = group.model_handle.show_handle;
			handle.show_ghost      = group.model_handle.show_ghost;
			handle.onStartDrag     = group.model_handle.onStartDrag;
			handle.onDrag          = group.model_handle.onDrag;
			handle.onIntersect     = group.model_handle.onIntersect;
			handle.onStopDrag      = group.model_handle.onStopDrag;

			// Add the handle to the manager.
			Legato_DragDrop.addDragHandle( handle );

		}  // Next drag handle.

	},
	*/

	//----------------------------------------------------------------------
	// Name: preStart()
	// Desc: Begins the dragging process. Sets the element up ready to be
	//       dragged. If the element is actually dragged, startDrag() is
	//       called.
	//----------------------------------------------------------------------
	preStart: function( drag_handle, e )
	{
	
		// Store the mouse position and the element's initial position.
		drag_handle.mouse_pos = Legato_Events_Handler.getCursorPos( e );
		drag_handle.initial_pos = drag_handle.element.position();

		// Set the required parameters.
		Legato_DragDrop.active_handle      = drag_handle;
		Legato_DragDrop.last_replaced_pos  = drag_handle.initial_pos;

		// Add the onmousemove event to the document.
		Legato_Events_Handler.addEvent( document, "onmousemove", function( e ){ Legato_DragDrop.drag( e ); } );

		return false;

	},


	//----------------------------------------------------------------------
	// Name: startDrag()
	// Desc: Starts the dragging of an element.
	//----------------------------------------------------------------------
	startDrag: function()
	{

		// Retrieve the drag handle.
		var drag_handle = Legato_DragDrop.active_handle;

		// Set the element's z index to a very high number.
		drag_handle.element.setStyle( 'z-index', 10000 );

		// Call the onStartDrag function. Will stop dragging if it returns false.
		if ( drag_handle.onStartDrag && drag_handle.onStartDrag() == false ) 
			Legato_DragDrop.stopDragging();

		// Set the started dragging flag to true.
		Legato_DragDrop.started_dragging = true;

	},


	//----------------------------------------------------------------------
	// Name: drag()
	// Desc: Sets the element to its new position.
	//----------------------------------------------------------------------
	drag: function( e )
	{

		// Retrieve the values we need.
		var drag_handle        = Legato_DragDrop.active_handle;
		var current_mouse_pos  = Legato_Events_Handler.getCursorPos( e );
		var current_position   = drag_handle.element.position();
		var mouse_delta        = {};
		var targets            = Array();
		var old_on_target      = false;

		// Start dragging, if we haven't already.
		if ( !Legato_DragDrop.started_dragging ) 
			Legato_DragDrop.startDrag();

		// Store the old mouse position.
		Legato_DragDrop.old_mouse_pos = drag_handle.mouse_pos;

		// Get the difference in the mouse position since the last update.
		mouse_delta.X = current_mouse_pos.X - Legato_DragDrop.old_mouse_pos.X;
		mouse_delta.Y = current_mouse_pos.Y - Legato_DragDrop.old_mouse_pos.Y;

		// Reset the drag handle's mouse position.
		drag_handle.mouse_pos = current_mouse_pos;

		// Get the new X and Y positions.
		if ( drag_handle.movable_region )
		{

			Legato_DragDrop.new_position.X = Math.max( drag_handle.movable_region.min_point.X, Math.min( drag_handle.movable_region.max_point.X, (current_position[0] + mouse_delta.X) ) );
			Legato_DragDrop.new_position.Y = Math.max( drag_handle.movable_region.min_point.Y, Math.min( drag_handle.movable_region.max_point.Y, (current_position[1] + mouse_delta.Y) ) );

		}
		else
		{

			Legato_DragDrop.new_position.X = current_position[0] + mouse_delta.X;
			Legato_DragDrop.new_position.Y = current_position[1] + mouse_delta.Y;

		}

		// Store the previous on target value and the previous target.
		old_on_target = drag_handle.on_target;
		old_target = drag_handle.last_target;

		// Get the targets for this drag handle.
		if ( drag_handle.group )
			targets = drag_handle.group.handles;
		else if ( drag_handle.targets )
			targets = drag_handle.targets;

		// Loop through each target.
		for ( var i = 0; i < targets.length; i++ )
		{

			// Get the target.
			var target = targets[i];

			// Perform the intersection test, and if we intersected, break.
			if ( Legato_DragDrop.intersectTest( drag_handle, target ) ) break;

		}  // Next target.

		// Call the onDrag function. Won't drag the handle if the function returns false.
		if ( drag_handle.onDrag && drag_handle.onDrag() == false )
		{

			// Return the mouse position back to it's old position.
			drag_handle.mouse_pos = Legato_DragDrop.old_mouse_pos;

			// Return from the function.
			return;

		}  // End if onDrag() returned false.

		// Compare the new on target value to the old one.
		if ( !old_on_target && drag_handle.on_target )
		{

			// If we were previously set as off target, then we have dragged on to the target.
			// In this case, call the target's onDragOver function.
			if ( drag_handle.last_target.onDragOver ) 
				drag_handle.last_target.onDragOver();

		}  // End if dragged over.
		else if ( old_on_target && !drag_handle.on_target )
		{

			// If we were previously set as on target, then we have dragged out of the target.
			// In this case, call the target's onDragOut function.
			if ( drag_handle.last_target.onDragOut ) 
				drag_handle.last_target.onDragOut();

		}  // End if dragged out.
		else if ( old_target && old_on_target && drag_handle.on_target && old_target != drag_handle.last_target )
		{

			// Call the old target's onDragOut function and call the new target's onDragOver function.
			if ( old_target.onDragOut )
				old_target.onDragOut();
				
			if ( drag_handle.last_target.onDragOver )
				drag_handle.last_target.onDragOver();

		}  // End if dragged on to a different target.

		// Set the current element's new position.
		if ( !drag_handle.constrainY ) drag_handle.element.position( Legato_DragDrop.new_position.X, null );
		if ( !drag_handle.constrainX ) drag_handle.element.position( null, Legato_DragDrop.new_position.Y );

	},


	//----------------------------------------------------------------------
	// Name: stopDragging()
	// Desc: Stops the dragging of an element.
	//----------------------------------------------------------------------
	stopDragging: function()
	{

		var on_drop_return  = true;
		var on_stop_return  = true;
		var return_val      = true;
		var drag_handle     = Legato_DragDrop.active_handle;

		if ( drag_handle != null )
		{

			// If we are on a target, and there is an onDrop function for the target, call it.
			if ( drag_handle.on_target && drag_handle.last_target.onDrop )
				on_drop_return = drag_handle.last_target.onDrop();

			// Call the onStopDrag function.
			if ( drag_handle.onStopDrag )
				on_stop_return = drag_handle.onStopDrag();

			// If onDrop or onStopDrag returned false, we must set return val to false.
			if ( on_drop_return == false || on_stop_return == false ) 
				return_val = false;

			// If the onStopDrag function returned false, put the drag handle to its initial position.
			if ( !return_val )
				drag_handle.element.position( drag_handle.initial_pos[0], drag_handle.initial_pos[1] );

			// If there is a last replaced element position and this drag handle has no targets or part of a group, then place the active handle in that position.
			//if ( drag_handle.group && Legato_DragDrop.last_replaced_pos && return_val ) 
			//	Legato_DOM_Library.setXY( DragDrop.active_handle.element, Legato_DragDrop.last_replaced_pos );

			// Return the element's z index to normal.
			drag_handle.element.setStyle( 'z-index', drag_handle.zindex );

			// Null out values.
			drag_handle.on_target = false;
			drag_handle.last_target = null;
			Legato_DragDrop.started_dragging = false;
			Legato_DragDrop.active_handle = null;
			Legato_DragDrop.last_replaced_pos = null;
			document.onmousemove = null;

		}

		return true;

	},


	////////////////////////////////////////////////////////////////////////
  // Helper Functions
  ////////////////////////////////////////////////////////////////////////
	//----------------------------------------------------------------------
  // Name: intersectTest()
  // Desc: This function is called to test for intersection between a drag
	//       handle and its target.
  //----------------------------------------------------------------------
	intersectTest: function( handle, target )
	{

		// Cache the target position.
	  if ( !target.position )
		  target.position = new Legato_DOM_Library.getXY( target.element );

		// Get the target's position.
		var target_pos = target.position;

		// What type of intersection test should we do?
		if ( handle.intersect_test == Legato_DragDrop.INTERSECT_HALF_DIMENSIONS )
		{

			// If we are in a position to switch with the target.
			if ( Math.abs( Legato_DragDrop.new_position.Y - target_pos.Y ) < target.element.offsetHeight * 0.5 )
			{

				Legato_DragDrop.onIntersect( handle, target, "up" );
				return true;

			}  // End moving up.
			else if ( Math.abs( Legato_DragDrop.new_position.Y - (target_pos.Y + (target.element.offsetHeight * 0.5)) ) < 1 )
			{

				Legato_DragDrop.onIntersect( handle, target, "down" );
				return true;

			}	 // End moving down.

		}  // End if half dimensions intersect test.
		else if ( handle.intersect_test == Legato_DragDrop.INTERSECT_CURSOR_POS )
		{

			// Cache the target region.
			if ( !target.region )
			  target.region = new Legato_Structure_Region( target.element );

			// Get the target region and the cursor's position.
			var target_region = target.region;
			var cursor_pos    = handle.mouse_pos;

      if ( target_region.containsPoint( cursor_pos ) )
			{

				Legato_DragDrop.onIntersect( handle, target, null );
				return true;

			}

		}  // End if intersect intersect test.
		else if ( handle.intersect_test == Legato_DragDrop.INTERSECT_THRESHOLD )
		{

			// Only snap if we meet the threshold.
			if ( (Math.abs( target_pos.X - Legato_DragDrop.new_position.X ) <= handle.snap_threshold) && (Math.abs( target_pos.Y - Legato_DragDrop.new_position.Y ) <= handle.snap_threshold) )
			{

				Legato_DragDrop.onIntersect( handle, target, null );
				return true;

			}

		}  // End if threshold intersect test.

		// Set the handle's on target attribute to false.
		handle.on_target = false;

		// Return false if no intersection occured.
		return false;

	},


	//----------------------------------------------------------------------
  // Name: onIntersect()
  // Desc: This function is called when a drag handle has intersected its
	//       target.
  //----------------------------------------------------------------------
	onIntersect: function( handle, target, orientation )
	{

		// Set the handle as being on a target.
		handle.on_target    = true;
		handle.last_target  = target;

		// Call the onIntersect function.
		// We pass in the handle, the target, and the orientation.
		// If onIntersect returns false, we return without processing.
		if ( handle.onIntersect && handle.onIntersect( handle, target, orientation ) == false ) return;

		// Cache the target position.
	  if ( !target.position )
		  target.position = new Legato_DOM_Library.getXY( target.element );

		// Get the target's position.
		var target_pos = target.position;

		// What should we do on intersection?
		if ( handle.on_intersect == Legato_DragDrop.ON_INTERSECT_SWITCH_POSITIONS )
		{

			// Which way are we moving?
			if ( orientation == "up" )
			{

				// Replace the positions of the elements.
				handle.element.parentNode.removeChild( handle.element );
				target.element.parentNode.insertBefore( handle.element, target.element.nextSibling );
				Legato_DragDrop.last_replaced_pos = target_pos;

			}  // End if moving up.
			else if ( orientation == "down" )
			{

				// Replace the positions of the elements.
				handle.element.parentNode.removeChild( handle.element );
				target.element.parentNode.insertBefore( handle.element, target.element );
				Legato_DragDrop.last_replaced_pos = target_pos;

			}  // End if moving down.

		}  // End if switching positions.
		else if ( handle.on_intersect == Legato_DragDrop.ON_INTERSECT_SNAP )
		{

			Legato_DragDrop.new_position.X = target_pos.X;
			Legato_DragDrop.new_position.Y = target_pos.Y;
			handle.mouse_pos           = DragDrop.old_mouse_pos;

		}  // End if snapping.

	}

};

