//-----------------------------------------------------------
// Function: toggleSection()
// Toggles whether a section is active or not.
//-----------------------------------------------------------
var toggleSection = function( section )
{

	if ( !toggleSection[section] )
	{

		// Set the section as active.
		toggleSection[section] = true;

		// Get the section's links.
		var section_links = $$( section + '_links' );

		// Set it as visible.
		section_links.setStyle( 'display', 'block' );

		// If we don't have this section's original height, get it.
		if ( !toggleSection.heights[section] )
			toggleSection.heights[section] = section_links.dimensions()[1];

		// Set the height to nothing so we can expand it with an animation.
		section_links.dimensions( null, 0 );

		var anim = new Legato_Animation( section_links, 1000 );
		anim.controller.height.to = toggleSection.heights[section];
		anim.controller.height.ease = Legato_Animation.STRONG_EASE_IN;
		anim.start();

		// Let's switch the + to a -
		var switcher = document.getElementById( section + "_switcher" );
		switcher.innerHTML = "-" + switcher.innerHTML.substring( 1 );

	}
	else
	{

		// Set the section as active.
		toggleSection[section] = false;

		var section_links = $$( section + '_links' );

		// If we don't have this section's original height, get it.
		if ( !toggleSection.heights[section] )
			toggleSection.heights[section] = section_links.dimensions()[1];

		var anim = new Legato_Animation( section_links, 1000 );
		anim.controller.height.to = 1;
		anim.onFinish = function(){ section_links.dimensions( null, toggleSection.heights[section] );
		                            section_links.setStyle( 'display', 'none' ); };
		anim.start();

		// Let's switch the - to a +
		var switcher = $$( section + "_switcher" );
		switcher.innerHTML = "+" + switcher.innerHTML.substring( 1 );

	}

}

toggleSection.education = false;
toggleSection.retirement = false;
toggleSection.heights = {};


//------------------------------------------------------------------------
// Name: showAvatar()
//------------------------------------------------------------------------
function showAvatar()
{

	var elem_html = '<a class="avatar-close" href="" onclick="hideAvatar(); return false;">X CLOSE</a><iframe id="iframe-test" scrolling="no" src="' + SITE_URL + '/avatar/"></iframe>';

	// Create the enlarged image element.
	var enlarged_elem = $$( 'container' ).create( 'div', { id: 'enlarged' }, true, elem_html );
	$( enlarged_elem ).position( ($( document.body ).dimensions()[0] - 660) * 0.5, $( document.body ).scrollOffset()[1] + 20 );

	var body_dimensions = $( document.body ).dimensions();

	if ( body_dimensions[1] < $( window ).dimensions()[1] )
		body_dimensions[1] = $( window ).dimensions()[1];

	// Create the background element.
	var back_elem = $$( 'container' ).create( 'div', { id: 'background' }, true );
	back_elem.dimensions( body_dimensions[0], body_dimensions[1] );
	back_elem.setStyle( 'opacity', 0.5 );
	back_elem.position( 0, 0 );

}


//------------------------------------------------------------------------
// Name: hideAvatar()
//------------------------------------------------------------------------
function hideAvatar()
{

	// Remove the background and enlarged image elements.
	$$( 'background' ).remove();
	$$( 'enlarged' ).remove();

}

//-----------------------------------------------------------
// Function: onloadHandler()
// Executed when the document is finished loading.
//-----------------------------------------------------------
function onloadHandler()
{

	var inactive_menu = $$( 'inactive_menu' );

	if ( inactive_menu )
		inactive_menu.opacity( 0.45 );

	var retirement_links = $$( 'retirement_links' );
	var education_links = $$( 'education_links' );

	if ( retirement_links.getStyle( 'display' ) != "none" )
		toggleSection.retirement = true;

	if ( education_links.getStyle( 'display' ) != "none" )
		toggleSection.education = true;

}

Legato_Events_Handler.DOMReady( onloadHandler );