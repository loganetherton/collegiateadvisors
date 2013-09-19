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


