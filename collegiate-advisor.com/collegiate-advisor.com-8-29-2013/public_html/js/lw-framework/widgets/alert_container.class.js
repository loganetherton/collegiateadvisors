//------------------------------------------------------------------------
// Name: LW_Widgets_AlertContainer
// Desc: Manages a floating container that slides on to the screen and
//       then slides away.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Static Variables
//------------------------------------------------------------------------
LW_Widgets_AlertContainer.alerts = new Array();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: LW_Widgets_AlertContainer()
// Desc: Class constructor.
//------------------------------------------------------------------------
function LW_Widgets_AlertContainer( container, delay, options_object )
{

	// Store the values.
	this.index      = LW_Widgets_AlertContainer.alerts.length;
	this.container  = container;
	this.delay      = delay;
	this.status     = 0;

	// Get the options from the options object, if there is any.
	if ( options_object != null )
	{

		// Store the options.
		this.in_time   = (options_object.in_time != null) ? options_object.in_time : 1500;
		this.out_time  = (options_object.out_time != null) ? options_object.out_time : 800;

		this.in_ease   = (options_object.in_ease != null) ? options_object.in_ease : LW_Animation.BOUNCE_EASE_OUT;
		this.out_ease  = (options_object.out_ease != null) ? options_object.out_ease : LW_Animation.BACK_EASE_IN;

		this.X_offset  = (options_object.X_offset != null) ? options_object.X_offset : 10;
		this.Y_offset  = (options_object.Y_offset != null) ? options_object.Y_offset : 10;
		
		this.auto_start = (options_object.auto_start != null ) ? options_object.auto_start : true;
		this.class_name = (options_object.class_name != null ) ? options_object.class_name : "alert_container";

	}  // End if extra options.
	else
	{

		// Store default options.
		this.in_time   = 1500;
		this.out_time  = 800;

		this.in_ease   = LW_Animation.BOUNCE_EASE_OUT;
		this.out_ease  = LW_Animation.BACK_EASE_IN;

		this.X_offset  = 10;
		this.Y_offset  = 10;
		
		this.auto_start = true;
		this.class_name = "alert_container";

	}  // End if no options passed in.
	
	// Set the class name.
	this.container.className = this.class_name;

	// Store the container.
	LW_Widgets_AlertContainer.alerts[this.index] = this;
	
	// Should we auto start it?
	if ( this.auto_start )
		this.show();

}


//------------------------------------------------------------------------
// Name: show()
// Desc: Slides the container in to view.
//------------------------------------------------------------------------
LW_Widgets_AlertContainer.prototype.show = function()
{

	// Get the container's width and height.
	var width   = LW_DOM_Library.getWidth( this.container );
	var height  = LW_DOM_Library.getHeight( this.container );

	// Set the container's starting position.
	LW_DOM_Library.setXY( this.container, { X: LW_DOM_Library.getViewportWidth() - width - this.X_offset, Y: -height } );

	// Set up the sliding in animation.
	var slide_in_anim = new LW_Animation( this.container, this.in_time );

	slide_in_anim.controller.move.by    = { X: null, Y: height + this.Y_offset };
	slide_in_anim.controller.move.ease  = this.in_ease;

	// Set up the sliding out animation.
	var slide_out_anim = new LW_Animation( this.container, this.out_time );

	slide_out_anim.controller.move.by    = { X: null, Y: -(height + this.Y_offset) };
	slide_out_anim.controller.move.ease  = this.out_ease;
	slide_out_anim.controller.delay      = this.delay;

	slide_out_anim.onFinish              = new Function( "LW_Widgets_AlertContainer.destroyContainer( " + this.index + " );" );

	// Set up the animation sequence.
	var animation_sequence = new LW_Animation_Sequence();

	animation_sequence.addAnimation( slide_in_anim );
	animation_sequence.addAnimation( slide_out_anim );

	// Start the animation sequence.
	animation_sequence.start();

}


//------------------------------------------------------------------------
// Public Static Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: destroyContainer()
// Desc: Destroys the alert container. Performs all the required clean up.
//------------------------------------------------------------------------
LW_Widgets_AlertContainer.destroyContainer = function( container_index )
{

	// Get the alert container.
	var alert_container = LW_Widgets_AlertContainer.alerts[container_index];

	// Remove the container element.
	alert_container.container.parentNode.removeChild( alert_container.container );

	// Remove from the array.
	LW_Widgets_AlertContainer.alerts[container_index] = null;

	// Remove object.
	alert_container.container  = null;
	alert_container            = null;

}

