//------------------------------------------------------------------------
// Name: Legato_Widgets_TabbedView
// Desc: Creates a tabbed view.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Widgets_TabbedView()
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Widgets_TabbedView( container, options )
{
	
	this.container = $( container );
	this.tabs = new Array();
	this.active_tab = null;
	
	// Add the HTML to the container.
	this.tabs_element = $( this.container.create( 'ul', { className: 'tabs' }, true ) );
	
	var window_html = '<div class="top_left"></div><div class="top"></div><div class="top_right"></div><div class="right"></div><div class="bottom_right"></div><div class="bottom"></div><div class="bottom_left"></div><div class="left"></div>';
	
	this.window_element = $( this.container.create( 'div', { className: 'window' }, true, window_html ) );
	
	// Options?
	if ( options.height )
		this.window_element.setStyle( 'height', options.height );
	
}


//------------------------------------------------------------------------
// Name: addTab()
// Desc: Adds a tab to the tabbed view and returns it.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.addTab = function( options )
{

	// Create the tab.
	var tab = new Legato_Widgets_TabbedView_Tab( this, options );
	
	// Stick the DOM elements in.
	this.tabs_element.addContent( tab.tab_element );
	this.window_element.addContent( tab.content_element );
	
	// Hide the tabs content.
	tab.content_element.setStyle( 'display', 'none' );
	
	// Store this tab.
	tab.index = this.tabs.length;
	this.tabs[tab.index] = tab;
	
	// Add the events.
	var tabbed_view = this;
	Legato_Events_Handler.addEvent( tab.tab_element, "onclick", function(){ tabbed_view.activate( tab ); } );
	Legato_Events_Handler.addEvent( tab.tab_element, "onmouseover", function(){ tabbed_view.focus( tab ); } );
	Legato_Events_Handler.addEvent( tab.tab_element, "onmouseout", function(){ tabbed_view.unfocus( tab ); } );
	
	// Any options we should do?
	if ( options.activated || this.tabs.length == 1 )
		this.activate( tab );
	else if ( options.disabled )
		this.disable( tab );
		
	// Return the newly created tab.
	return tab;

}


//------------------------------------------------------------------------
// Name: activate()
// Desc: Activates the tab passed in.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.activate = function( tab )
{
	
	// Disabled?
	if ( tab.disabled )
		return;
		
	// Call the callback function and return if false.
	if ( tab.onActivate != null && tab.onActivate( tab ) == false )
		return;

	// Hide the active tab.
	if ( this.active_tab )
	{
		
		// Call the callback function and return if false.
		if ( tab.onDeactivate != null && tab.onDeactivate( tab ) == false )
			return;
		
		this.active_tab.content_element.setStyle( 'display', 'none' );
		this.active_tab.tab_element.removeClass( 'active' );
		
	}

	// Display the activated tab.
	tab.content_element.setStyle( 'display', 'block' );
	
	// Set the class.
	tab.tab_element.addClass( 'active' );
	
	// Set as active.
	this.active_tab = tab;

}


//------------------------------------------------------------------------
// Name: disable()
// Desc: Disables a tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.disable = function( tab )
{
	
	// Call the callback function and return if false.
	if ( tab.onDisable != null && tab.onDisable( tab ) == false )
		return;

	// Hide the content.
	tab.content_element.setStyle( 'display', 'none' );
	
	// Set as disabled.
	tab.tab_element.addClass( 'disabled' );
	tab.disabled = true;
	
	// Was it the active tab?
	if ( tab == this.active_tab )
		this.active_tab == null;

}


//------------------------------------------------------------------------
// Name: enable()
// Desc: Enables a tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.enable = function( tab )
{
	
	// Call the callback function and return if false.
	if ( tab.onEnable != null && tab.onEnable( tab ) == false )
		return;
	
	// Set as enabled.
	tab.tab_element.removeClass( 'disabled' );
	tab.disabled = false;

}


//------------------------------------------------------------------------
// Name: focus()
// Desc: Focuses a tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.focus = function( tab )
{
	
	if ( tab.disabled || tab == this.active_tab )
		return;
		
	// Call the callback function and return if false.
	if ( tab.onFocus != null && tab.onFocus( tab ) == false )
		return;
	
	// Set the class.
	tab.tab_element.addClass( 'focused' );

}


//------------------------------------------------------------------------
// Name: unfocus()
// Desc: Unfocuses a tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView.prototype.unfocus = function( tab )
{
	
	if ( !tab.tab_element.hasClass( 'focused' ) )
		return;
		
	// Call the callback function and return if false.
	if ( tab.onUnfocus != null && tab.onUnfocus( tab ) == false )
		return;
	
	// Set the class.
	tab.tab_element.removeClass( 'focused' );

}


//------------------------------------------------------------------------
// Name: Legato_Widgets_TabbedView_Tab
// Desc: A tab for use in a tabbed view.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Widgets_TabbedView_Tab()
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Widgets_TabbedView_Tab( tabbed_view, options )
{

	// Store this tabs options.
	this.tabbed_view = tabbed_view;
	this.title = options.title;
	this.content = options.content;
	this.index = null;
	this.disabled = false;
	
	// Callbacks.
	this.onActivate = options.onActivate;
	this.onDeactivate = options.onDeactivate;
	this.onFocus = options.onFocus;
	this.onUnfocus = options.onUnfocus;
	this.onDisable = options.onDisable;
	this.onEnable = options.onEnable;
	
	// Create the tab element for this tab.
	this.tab_element = $( document.body ).create( 'li', {}, false, '<div class="left"></div><div class="title">' + this.title + '</div><div class="right"></div>' );
	
	// Create the content element for this tab.
	this.content_element = $( document.body ).create( 'div', { className: 'content' }, false, this.content );

}


//------------------------------------------------------------------------
// Name: activate()
// Desc: Activates the tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView_Tab.prototype.activate = function()
{
	
	this.tabbed_view.activate( this );

}


//------------------------------------------------------------------------
// Name: disable()
// Desc: Disables the tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView_Tab.prototype.disable = function()
{
	
	this.tabbed_view.disable( this );

}


//------------------------------------------------------------------------
// Name: enable()
// Desc: Enables the tab.
//------------------------------------------------------------------------
Legato_Widgets_TabbedView_Tab.prototype.enable = function()
{
	
	this.tabbed_view.enable( this );

}