//------------------------------------------------------------------------
// Name: Legato_Widgets_Menu
// Desc: Manages a menu that pops up when you click a certain element.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Class Constants
//------------------------------------------------------------------------
Legato_Widgets_Menu.PLACE_TOP_LEFT      = 0;
Legato_Widgets_Menu.PLACE_TOP_RIGHT     = 1;
Legato_Widgets_Menu.PLACE_BOTTOM_LEFT   = 2;
Legato_Widgets_Menu.PLACE_BOTTOM_RIGHT  = 3;
Legato_Widgets_Menu.PLACE_RIGHT_TOP     = 4;
Legato_Widgets_Menu.PLACE_RIGHT_BOTTOM  = 5;
Legato_Widgets_Menu.PLACE_LEFT_TOP      = 6;
Legato_Widgets_Menu.PLACE_LEFT_BOTTOM   = 7;


//------------------------------------------------------------------------
// Static Variables
//------------------------------------------------------------------------
Legato_Widgets_Menu.menus = new Array();


//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Widgets_Menu()
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Widgets_Menu( element, menu_items, options_object )
{

	if ( options_object == null ) options_object = {};

	// Store the values.
	this.index              = Legato_Widgets_Menu.menus.length;
	this.active             = false;
	this.menus              = new Array();
	this.active_menu_stack  = new Array();

	// Store this menu in the global array.
	Legato_Widgets_Menu.menus[this.index] = this;

	// Create the top menu and all the menus beneath it.
	this.top_menu  = new Menu( this.index, null, element, menu_items, options_object );


	//----------------------------------------------------------------------
  // Private Class Declarations (for use in this class only)
  //----------------------------------------------------------------------
	//----------------------------------------------------------------------
  // Name: Menu
  // Desc: Manages a menu object for use in the Legato_Widgets_Menu class.
  //----------------------------------------------------------------------
	function Menu( main_menu_index, parent_menu, element, menu_items, options_object )
	{

		if ( options_object == null ) options_object = {};

		// Get the menu's index for below.
		var menu_index = Legato_Widgets_Menu.menus[main_menu_index].menus.length;

		// Store this menu in the main menu's menu array.
	  Legato_Widgets_Menu.menus[main_menu_index].menus.push( this );

		// Store the values.
		this.index            = menu_index;
		this.main_menu_index  = main_menu_index;
		this.element          = element;
		this.menu_container   = Legato_DOM_Library.createContainer( Legato_DOM_Library.CREATE_HTML, getMenuHTML() );
		this.items            = new Array();
		this.parent_menu      = parent_menu;
		this.sub_menus        = new Array();

		// Hide the menu.
		Legato_DOM_Library.setStyle( this.menu_container, "visibility", "hidden" );

		// Set the css class.
		if ( options_object.css_class != null ) this.menu_container.className = options_object.css_class;

		// If this is a first sub menu, set the container's placement.
		if ( parent_menu != null && parent_menu.parent_menu == null )
		{

			switch ( options_object.placement )
			{

			case Legato_Widgets_Menu.PLACE_TOP_LEFT:

			  options_object.placement = Legato_Widgets_Menu.PLACE_LEFT_TOP;
				break;

			case Legato_Widgets_Menu.PLACE_TOP_RIGHT:

			  options_object.placement = Legato_Widgets_Menu.PLACE_RIGHT_TOP;
				break;

			case Legato_Widgets_Menu.PLACE_BOTTOM_LEFT:

			  options_object.placement = Legato_Widgets_Menu.PLACE_LEFT_BOTTOM;
				break;

			case Legato_Widgets_Menu.PLACE_BOTTOM_RIGHT:

			  options_object.placement = Legato_Widgets_Menu.PLACE_RIGHT_BOTTOM;
				break;

			}

		}

		// Get the required values.
		var element_pos       = Legato_DOM_Library.getXY( element );
		var element_width     = Legato_DOM_Library.getWidth( element );
		var element_height    = Legato_DOM_Library.getHeight( element );
		var container_width   = Legato_DOM_Library.getWidth( this.menu_container );
		var container_height  = Legato_DOM_Library.getHeight( this.menu_container );

		// Menu placement switch.
		switch ( options_object.placement )
		{

		// Place on top-right.
		case Legato_Widgets_Menu.PLACE_TOP_RIGHT:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X, Y: element_pos.Y - container_height } );
			break;

		// Place on top-left.
		case Legato_Widgets_Menu.PLACE_TOP_LEFT:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X + element_width - container_width, Y: element_pos.Y - container_height } );
			break;

		// Place to the right-top.
		case Legato_Widgets_Menu.PLACE_RIGHT_TOP:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X + element_width, Y: element_pos.Y + element_height - container_height } );
			break;

		// Place to the right-bottom.
		case Legato_Widgets_Menu.PLACE_RIGHT_BOTTOM:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X + element_width, Y: element_pos.Y } );
			break;

		// Place to the left-top.
		case Legato_Widgets_Menu.PLACE_LEFT_TOP:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X - container_width, Y: element_pos.Y + element_height - container_height } );
			break;

		// Place to the left-top.
		case Legato_Widgets_Menu.PLACE_LEFT_BOTTOM:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X - container_width, Y: element_pos.Y } );
			break;

		// Place to the bottom-left.
		case Legato_Widgets_Menu.PLACE_BOTTOM_LEFT:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X + element_width - container_width, Y: element_pos.Y + element_height } );
			break;

		// Place on bottom-right (also the default).
		case Legato_Widgets_Menu.PLACE_BOTTOM_RIGHT:
		default:

			Legato_DOM_Library.setXY( this.menu_container, { X: element_pos.X, Y: element_pos.Y + element_height } );
			break;

		}

		// Loop through each menu item group.
		for ( var i = 0; i < menu_items.length; i++ )
		{

			// Get the item group.
			var item_group = menu_items[i];

			// Loop through each menu item.
			for ( var n = 0; n < item_group.items.length; n++ )
			{

				var sub_menu      = null;
				var menu_item     = item_group.items[n];
				var item_element  = document.getElementById( "menu_item_" + main_menu_index + "_" + menu_index + "_" + + i + "_" + n );

				// What type of menu is this?
				if ( menu_item.sub_menu != null )
				{

					// Create the sub menu.
					sub_menu = new Menu( main_menu_index, this, item_element, menu_item.sub_menu, options_object );

					// Add the sub menu to this menu's sub menus array.
					this.sub_menus.push( sub_menu );

					// Set the item's CSS class.
					item_element.className = "sub_menu";

				}  // End if sub menu.
				else if ( menu_item.disabled == true )
				{

					// Set the item's CSS class.
					item_element.className = "disabled";

				}  // End if disabled item.

				// Add the event handlers.
				Legato_Events_Handler.addEvent( item_element, "onclick", new Function( "Legato_Widgets_Menu.itemClick( " + main_menu_index + ", " + menu_index + ", " + this.items.length + " );" ) );
				Legato_Events_Handler.addEvent( item_element, "onmouseover", new Function( "Legato_Widgets_Menu.itemHoverOn( " + main_menu_index + ", " + menu_index + ", " + this.items.length + " );" ) );
				Legato_Events_Handler.addEvent( item_element, "onmouseout", new Function( "Legato_Widgets_Menu.itemHoverOff( " + main_menu_index + ", " + menu_index + ", " + this.items.length + " );" ) );

				// Add the item to the menu object.
				this.items.push( { menu: this, index: this.items.length, title: menu_item.title, action: menu_item.action, element: item_element, sub_menu: sub_menu, disabled: menu_item.disabled } );

			}  // Next menu item.

		}  // Next menu item group.

		// Is this a top menu?
		if ( this.parent_menu == null )
		{

			// Register the event handlers.
			Legato_Events_Handler.addEvent( element, "onclick", new Function( "Legato_Widgets_Menu.showMainMenu( " + main_menu_index + " );" ) );
			Legato_Events_Handler.addEvent( element, "onmouseover", function(){ Legato_Widgets_Menu.menus[main_menu_index].active = true; } );
			Legato_Events_Handler.addEvent( element, "onmouseout", function(){ Legato_Widgets_Menu.menus[main_menu_index].active = false; } );

		}  // End if top menu.

		// Register the event handlers.
		Legato_Events_Handler.addEvent( this.menu_container, "onmouseover", function(){ Legato_Widgets_Menu.menus[main_menu_index].active = true; } );
		Legato_Events_Handler.addEvent( this.menu_container, "onmouseout", function(){ Legato_Widgets_Menu.menus[main_menu_index].active = false; } );


		//--------------------------------------------------------------------
	  // Private Member Functions
	  //--------------------------------------------------------------------
		//--------------------------------------------------------------------
		// Name: getMenuHTML()
		// Desc: Formats the menu array into HTML to be placed into the menu
		//       container.
		//--------------------------------------------------------------------
		function getMenuHTML()
		{

			var menu_HTML = "";

			// Loop through each menu item group.
			for ( var i = 0; i < menu_items.length; i++ )
			{

				// Get the item group.
				var item_group = menu_items[i];

				// If this isn't the first item group, post a seperator.
				if ( i > 0 ) menu_HTML += "<div class='seperator'></div>";

				// Put the title, if there is one.
				if ( item_group.title != null )
					menu_HTML += "<h1>" + item_group.title + "</h1>";

				menu_HTML += "<ul>";

				// Loop through each menu item.
				for ( var n = 0; n < item_group.items.length; n++ )
				{

					// Get the menu item.
					var menu_item = item_group.items[n];

					// Add the item.
					menu_HTML += "<li id='menu_item_" + main_menu_index + "_" + menu_index + "_" + + i + "_" + n + "'>" + menu_item.title + "</li>";

				}  // Next menu item.

				menu_HTML += "</ul>";

			}  // Next menu item group.

			return menu_HTML;

	  }

	}

}


//------------------------------------------------------------------------
// Name: showMenu()
// Desc: Shows this menu.
//------------------------------------------------------------------------
Legato_Widgets_Menu.prototype.showMenu = function()
{

	// Redirect to the static showMainMenu function.
	Legato_Widgets_Menu.showMainMenu( this.index );

}


//------------------------------------------------------------------------
// Public Static Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: showMainMenu()
// Desc: Shows the main menu with the index specified.
//------------------------------------------------------------------------
Legato_Widgets_Menu.showMainMenu = function( index )
{

	// Retrieve the menu.
	var menu = Legato_Widgets_Menu.menus[index];

	// Add the menu to the stack.
	Legato_Widgets_Menu.addMenuToStack( menu.index, menu.top_menu.index );

	// Set the menu as visible.
	Legato_DOM_Library.setStyle( menu.top_menu.menu_container, "visibility", "visible" );

}


//------------------------------------------------------------------------
// Name: showSubMenu()
// Desc: Shows a certain sub menu.
//------------------------------------------------------------------------
Legato_Widgets_Menu.showSubMenu = function( sub_menu )
{

	// Add the menu to the stack.
	Legato_Widgets_Menu.addMenuToStack( sub_menu.main_menu_index, sub_menu.index );

	// Set the menu as visible.
	Legato_DOM_Library.setStyle( sub_menu.menu_container, "visibility", "visible" );

}


//------------------------------------------------------------------------
// Name: itemClick()
// Desc: This function is called when a menu's item is clicked.
//------------------------------------------------------------------------
Legato_Widgets_Menu.itemClick = function( main_menu_index, menu_index, item_index )
{

	// Retrieve the menu and item.
	var main_menu  = Legato_Widgets_Menu.menus[main_menu_index];
	var menu_item  = main_menu.menus[menu_index].items[item_index];

	// What type of item?
	if ( menu_item.sub_menu != null )
	{

		// Show the sub menu.
		Legato_Widgets_Menu.showSubMenu( menu_item.sub_menu );

	}  // End if sub menu.
	else if ( menu_item.disabled == true )
	{

		// Return without doing anything.
		return;

	}  // End if disabled item.
	else
	{

		// Do the item's action.
	  menu_item.action();

		// Reduce the stack to nothing.
		Legato_Widgets_Menu.reduceStackTo( main_menu_index, null );

	}  // End if normal item.

}


//------------------------------------------------------------------------
// Name: itemHoverOn()
// Desc: This function is called when a menu's item is hovered on to.
//------------------------------------------------------------------------
Legato_Widgets_Menu.itemHoverOn = function( main_menu_index, menu_index, item_index )
{

	// Retrieve the menu and item.
	var main_menu  = Legato_Widgets_Menu.menus[main_menu_index];
	var menu_item  = main_menu.menus[menu_index].items[item_index];

	// Reduce the stack to this item's menu.
	Legato_Widgets_Menu.reduceStackTo( main_menu_index, menu_index );

	// Is this item a sub menu?
	if ( menu_item.sub_menu != null )
	{

		// Set the menu item's CSS class.
		menu_item.element.className = "sub_menu_hover";

		// Show the sub menu.
		Legato_Widgets_Menu.showSubMenu( menu_item.sub_menu );

	}  // End if sub menu.
	else if ( menu_item.disabled == true )
	{

		// Set the menu item's CSS class.
		menu_item.element.className = "disabled_hover";

	}  // End if disabled.
	else
	{

		// Set the menu item's CSS class.
		menu_item.element.className = "item_hover";

	}  // End if normal item.

}


//------------------------------------------------------------------------
// Name: itemHoverOff()
// Desc: This function is called when a menu's item is hovered off of.
//------------------------------------------------------------------------
Legato_Widgets_Menu.itemHoverOff = function( main_menu_index, menu_index, item_index )
{

	// Retrieve the menu and item.
	var main_menu  = Legato_Widgets_Menu.menus[main_menu_index];
	var menu_item  = main_menu.menus[menu_index].items[item_index];

	// What type of item is this?
	if ( menu_item.disabled == true )
	{

		// Set the menu item's CSS class.
		menu_item.element.className = "disabled";

	}  // End if disabled item.
	else if ( menu_item.sub_menu == null )
	{

		// Set the menu item's CSS class.
		menu_item.element.className = "";

	}  // End if normal item.

}


//------------------------------------------------------------------------
// Name: addMenuToStack()
// Desc: Adds a menu to the active menu stack.
//------------------------------------------------------------------------
Legato_Widgets_Menu.addMenuToStack = function( main_menu_index, menu_index )
{

	// Get the main menu.
	var main_menu = Legato_Widgets_Menu.menus[main_menu_index];

	// Add the menu to the stack.
	main_menu.active_menu_stack.push( menu_index );

}


//------------------------------------------------------------------------
// Name: reduceStackTo()
// Desc: Reduces the stack to a certain menu, hiding all the menus removed
//       in the process.
//------------------------------------------------------------------------
Legato_Widgets_Menu.reduceStackTo = function( main_menu_index, menu_index )
{

	// Get the main menu.
	var main_menu = Legato_Widgets_Menu.menus[main_menu_index];

	// Loop until we find the menu that we want.
	for ( var i = main_menu.active_menu_stack.length - 1; i >= 0; i-- )
	{

		// Get the menu.
		var menu = main_menu.menus[main_menu.active_menu_stack[i]];

		// Is this the correct item?
		if ( menu.index != menu_index )
		{

			// Hide the menu and pop it off the stack.
			Legato_DOM_Library.setStyle( menu.menu_container, "visibility", "hidden" );

		  main_menu.active_menu_stack.pop();

			// Loop through each item in this menu.
			for ( var n = 0; n < menu.items.length; n++ )
			{

				var menu_item = menu.items[n];

				// Is this item a sub menu?
				if ( menu_item.sub_menu != null )
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "sub_menu";

				}  // End if sub menu.
				else if ( menu_item.disabled == true )
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "disabled";

				}  // End if disabled.
				else
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "";

				}  // End if normal item.

			}  // Next menu item.

		}  // End if not the correct item.
		else
		{

			// Loop through each item in this menu.
			for ( var n = 0; n < menu.items.length; n++ )
			{

				var menu_item = menu.items[n];

				// Is this item a sub menu?
				if ( menu_item.sub_menu != null )
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "sub_menu";

				}  // End if sub menu.
				else if ( menu_item.disabled == true )
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "disabled";

				}  // End if disabled.
				else
				{

					// Set the menu item's CSS class.
					menu_item.element.className = "";

				}  // End if normal item.

			}  // Next menu item.

			// Break out of the loop.
			break;

		}  // End if item to reduce to.

	}  // Next item in the stack.

}


//------------------------------------------------------------------------
// Name: hideMenus()
// Desc: Hides the menus that aren't active.
//------------------------------------------------------------------------
Legato_Widgets_Menu.hideMenus = function()
{

	// Loop through each menu.
	for ( var i = 0; i < Legato_Widgets_Menu.menus.length; i++ )
	{

		// Retrieve the menu.
		var menu = Legato_Widgets_Menu.menus[i];

		// If this menu is not active, reduce the active menu stack to nothing.
		if ( !menu.active )
      Legato_Widgets_Menu.reduceStackTo( menu.index, null );

	}  // Next popup container.

}

// Set the hideContainers function as a document.onclick event.
Legato_Events_Handler.addEvent( document, "onclick", Legato_Widgets_Menu.hideMenus );


