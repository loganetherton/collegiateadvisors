//------------------------------------------------------------------------
// Name: Legato_Form
// Desc: A helper class to help with the PHP Form class.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// Public Member Functions
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Name: Legato_Form
// Desc: Class constructor.
//------------------------------------------------------------------------
function Legato_Form( form, options_object )
{

	this.form                  = $$( form );
	this.input_elements        = [];
	this.groups                = [];

	// Store the parameters.
	this.submit_button         = options_object.submit_button;
	this.submit_form           = (options_object.submit_form == null) ? false : true;
	this.redirect_url          = options_object.redirect_url;
	this.request_url           = options_object.request_url;
	this.modified_request_url  = '';
	this.errors                = [];

	this.processing            = false;
	
	// Add on onsubmit event for the form.
	var form = this;
	Legato_Events_Handler.addEvent( this.form, 'onsubmit', function(){ return form.validateForm(); } );

}


//----------------------------------------------------------------------
// Name: validateForm()
// Desc: Start the validation.
//----------------------------------------------------------------------
Legato_Form.prototype.validateForm = function()
{
	
	var errors = false;

	// Are we processing?
	if ( this.processing == true )
		return false;
	else
		this.processing = true;

	// Disable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = true;

	// First make sure the form is clean.
	this.cleanupForm();
	
	// If there is a request URL, send the request.
	if ( this.request_url != null )
	{
		
		var query_string = this.getQueryString();

		// Send the request.
		var form = this;
		Legato_RequestManager.makeRequest( this.modified_request_url, function( response ){ form.processResponse( response ); }, query_string );

		// Return. We will continue processing in the processResponse() function.
		return false;

	}
	
	// Finish the validation.
	return this.finishValidation();

};


//----------------------------------------------------------------------
// Name: processResponse()
// Desc: Processes the response from the XHR request.
//----------------------------------------------------------------------
Legato_Form.prototype.processResponse = function( response )
{
	
	var any_errors = false;
	
	// JSON or XML?
	if ( !response.responseXML || response.responseXML.getElementsByTagName( 'errors' ).length == 0 )
	{

		var errors = eval( '(' + response.responseText + ')' );
		
		// Any errors?
		if ( errors.length != 0 )
		{
			
			// Set errors on.
			any_errors = true;
			
			// Loop through each error.
			for ( var id in errors )
			{
				
				// Get the message.
				var message = errors[id];
	
				// Post the error.
				this.postError( id, message );
	
			}  // Next error.
			
		}
		
	}
	else
	{
		
		response = response.responseXML;
		
		// Get the errors.
		var errors = response.getElementsByTagName( 'error' );
		
		// Is there any errors?
		if ( errors.length != 0 )
		{
			
			// Set errors on.
			any_errors = true;
	
			// Loop through each error.
			for ( var i = 0; i < errors.length; i++ )
			{
	
				// Get the error details.
				var id = errors[i].getElementsByTagName( "id" );
	
				if ( id.length == 0 )
					id = null;
				else
					id = id[0].firstChild.data;
	
				// Get the message.
				var message = errors[i].getElementsByTagName( "message" )[0].firstChild.data;
	
				// Post the error.
				this.postError( id, message );
	
			}  // Next error.
			
		}
		
	}
	
	// Any errors?
	if ( !any_errors )
	{
		
		// Finish validation.
		this.finishValidation();

		// Return.
		return;
		
	}

	// Show the generic error message.
	this.postError( null, 'There were errors while processing the form. Please fix them and try submitting the form again.' );

	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = false;

};


//----------------------------------------------------------------------
// Name: finishValidation()
// Desc: Finishes the validation.
//----------------------------------------------------------------------
Legato_Form.prototype.finishValidation = function()
{
	
	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) 
		this.submit_button.disabled = false;

	// Check for a redirect URL and if there is one redirect them.
	if ( this.redirect_url != null )
		window.location = this.redirect_url;
	
	// If the submit form flag is set, submit the form.
	if ( this.submit_form )
		return true;
	else
		return false;

};


//----------------------------------------------------------------------
// Name: postError()
// Desc: Posts an error to the form with information about what went
//       wrong and why it went wrong.
// Note: If null is passed in for element_id, the error will be placed
//       at the end of the form.
//----------------------------------------------------------------------
Legato_Form.prototype.postError = function( element_id, error )
{
	
	// Create the error node.
	var error_node = $( document.body ).create( 'p', { className: 'error' }, false, error );
	
	// What type of placement?
	if ( element_id == null )
	{

		// Do we have a submit button?
		if ( this.submit_button != null )
		{
			
			// A group or not?
			if ( $( this.submit_button.parentNode.parentNode ).hasClass( 'group_elements' ) )			
				this.submit_button.parentNode.parentNode.parentNode.insertBefore( error_node, this.submit_button.parentNode.parentNode.parentNode.firstChild );
			else
				this.submit_button.parentNode.insertBefore( error_node, this.submit_button.parentNode.firstChild );
		}
		else
			this.form.addContent( error_node );

	}  // End if general error message.
	else
	{

		var html_element = $$( element_id );

		// Group or normal?
		if ( $( html_element.parentNode.parentNode ).hasClass( 'group' ) )
			html_element.parentNode.parentNode.insertBefore( error_node, html_element.parentNode.parentNode.firstChild );
		else
			html_element.parentNode.insertBefore( error_node, html_element.parentNode.firstChild );

	}  // End if normal/group error message.
	
};


//----------------------------------------------------------------------
// Name: cleanupForm()
// Desc: Cleans up the form to make it ready for form validation.
//----------------------------------------------------------------------
Legato_Form.prototype.cleanupForm = function()
{

	// Get all the forms error's.
	var errors = this.form.cssSelect( 'p.error' );

	// Loop through each error.
	// We get the length before hand, because we take elements away from the
	// array in the loop.
	for ( var i = 0; i < errors.length; i++ )
	{

		// Get the element. We retrieve the 0th element because we remove
		// the child below, and the next one will fall in this place.
		var error_element = errors[i];

		// Is this an error element?
		error_element.remove();  // Remove the element.


	}  // Next error node.

};


//----------------------------------------------------------------------
// Name: getQueryString()
// Desc: Concatenates all the managed input elements in to a query
//       string suitable for appending to a URL.
//----------------------------------------------------------------------
Legato_Form.prototype.getQueryString = function()
{

	var values = Array();

	// Loop through each input element in the form.
	for ( var i = 0; i < this.form.elements.length; i++ )
	{

		var element = this.form.elements[i];

		// What type of element?
		switch ( element.type )
		{

		// Simple elements.
		case 'text':
		case 'password':
		case 'file':
		case 'textarea':
		case 'hidden':
		case 'select-one':

			values.push( (element.name + "=" + encodeURIComponent( element.value )) );
			break;

		// Checkboxes and radio buttons.
		case 'checkbox':
		case 'radio':

			// Only add if checked.
			if ( element.checked )
				values.push( (element.name + "=" + encodeURIComponent( element.value )) );

			break;

		// Select multiples.
		case 'select-multiple':

			// Loop through each option.
			for ( var n = 0; n < element.options.length; n++ )
			{

				// Only add if option is selected.
				if ( element.options[n].selected )
					values.push( (element.name + "=" + encodeURIComponent( element.options[n].value )) );

			}  // Next option.

			break;

		}  // End what type of elements.

	}  // Next input element.

	// Put the values together into a string.
	var query_string = values.join( "&" );

	// Get the pieces of the request URL.
	var pieces = this.request_url.match( /(\S*)\?(\S*)/ );

	// If the request_url does not have a ? in it, get rid of the query values from
	// the request URL, and add it to the query string.
	if ( pieces != null )
	{

		this.modified_request_url = pieces[1];
		query_string              = pieces[2] + "&" + query_string;

	}
	else
	{

		this.modified_request_url = this.request_url;

	}

	// Return the query string.
	return query_string;

};

