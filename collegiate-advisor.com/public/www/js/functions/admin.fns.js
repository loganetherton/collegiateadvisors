//------------------------------------------------------------------------
// Name: delete_resource()
// Desc: Sends off a request to delete a resource.
//------------------------------------------------------------------------
function delete_resource( resource_type, id, redirect )
{
	
	if ( resource_type == "workshop" )
		var url = "/admin/workshops/delete";
	else if ( resource_type == "user" )
		var url = "/admin/users/delete";
	else if ( resource_type == "essay" )
		var url = "/admin/essays/delete";
	else if ( resource_type == "style" )
		var url = "/admin/styles/delete";
	else if ( resource_type == "news" )
		var url = "/admin/news/delete";
	else if ( resource_type == "editable_page" )
		var url = "/admin/editable_pages/delete";
	
	if ( confirm( "Are you sure you want to delete this " + resource_type + "?" ) )
	{
		
		// Send off a request.
		Legato_RequestManager.makeRequest( SITE_URL + url, false, 'id=' + id );
		
		if ( redirect == null )
		{
			
			// Remove the element of the resource in it's table.
			var row_element = $$( resource_type + id );
			
			// Remove the table row.
			row_element.parentNode.removeChild( row_element );
			
			// Inform the user.
			show_alert( "Successfully Deleted " + resource_type[0].toUpperCase() + resource_type.slice( 1 ) );
			
		}
		else
		{
			
			window.location = redirect;
			
		}
		
	}
	
}


//------------------------------------------------------------------------
// Name: delete_essay()
// Desc: Sends off a request to delete an essay.
//------------------------------------------------------------------------
function delete_essay( advisor_id, filename )
{
	
	if ( confirm( "Are you sure you want to delete this essay?" ) )
	{
		
		// Send off a request.
		Legato_RequestManager.makeRequest( SITE_URL + "/admin/essays/delete", false, "advisor_id=" + advisor_id + "&filename=" + filename );
		
		// Remove the element of the resource in it's table.
		var row_element = $$( "essay" + filename );
		
		// Remove the table row.
		row_element.parentNode.removeChild( row_element );
		
		// Inform the user.
		show_alert( "Successfully Deleted Essay" );
		
	}
	
}


//------------------------------------------------------------------------
// Name: show_alert()
// Desc: Pops up an alert box.
//------------------------------------------------------------------------
function show_alert( message )
{
	
	var container = $$( 'container' ).create( 'div', { className: 'alert_container' }, true, message );
	container.opacity( 0.9 );
	
	var alert_container = new Legato_Widgets_AlertContainer
	( 
		container, 
		3000, 
		{ 
			Y_offset: 37, 
			X_offset: ($( window ).dimensions()[0] / 2 - $( container ).dimensions()[0] / 2) 
		} 
	);
	
}