//------------------------------------------------------------------------
// Name: LW_Form_Validator
// Desc: Exploses methods to validate the input parameters passed in to a
//       form.
//------------------------------------------------------------------------
//------------------------------------------------------------------------
// Some Global Form_Validator Variables
//------------------------------------------------------------------------
LW_Form_Validator.validators = Object();


//------------------------------------------------------------------------
// Name: Form_Validator
// Desc: Class constructor.
//------------------------------------------------------------------------
function LW_Form_Validator( form, options_object )
{

	// Add this object to a global validator array.
	LW_Form_Validator.validators[form.id] = this;

	this.form                  = form;
	this.input_elements        = new Array();
	this.groups                = new Array();

	// Store the parameters.
	this.submit_button         = options_object.submit_button;
	this.submit_form           = (options_object.submit_form == null) ? false : true;
	this.redirect_url          = options_object.redirect_url;
	this.modified_request_url  = "";
	this.errors                = new Array();

	this.processing            = false;

	// Add on onsubmit event for the form.
	LW_Events_Handler.addEvent( this.form, "onsubmit", new Function( "return LW_Form_Validator.validators['" + this.form.id + "'].validateForm();" ) );

}


//----------------------------------------------------------------------
// Name: validateForm()
// Desc: Start the validation.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.validateForm = function()
{

	var errors = false;

	// Are we processing?
	if ( this.processing == true )
		return false;
	else
		this.processing = true;

	// Disable the submit button.
	if ( this.submit_button != null ) this.submit_button.disabled = true;

	// First make sure the form is clean.
	this.cleanupForm();

	// Call the form's onprevalidate function.
	if ( this.form['onprevalidate'] != null )
		this.form['onprevalidate']();
	/*
	// Were there any errors?
	if ( errors == true )
	{

		this.postError( null, "There were errors while processing the form. Please fix them and try submitting the form again." );

		// Set processing to false.
		this.processing = false;

		// Enable the submit button.
		if ( this.submit_button != null ) this.submit_button.disabled = false;

		// Return. We don't want to process any more.
		return false;

	}

	// If there is a request URL, send the request.
	if ( this.request_url != null )
	{

	  var query_string = this.getQueryString();

		// Send the request.
		LW_RequestManager.makeRequest( this.modified_request_url, new Function( "response", "LW_Form_Validator.validators['" + this.form.id + "'].processResponse( response );" ), query_string );

		// Return. We will continue processing in the processResponse() function.
		return false;

	}
	*/
	// Finish the validation.
	return this.finishValidation();

}


//----------------------------------------------------------------------
// Name: processResponse()
// Desc: Processes the response from the XHR request.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.processResponse = function( response )
{

	var response = response.responseXML;

	// Hide the message container, if there is one.
	if ( this.message_container != null )
		LW_DOM_Library.setStyle( this.message_container, "visibility", "hidden" );

	// Get the errors.
	var errors = response.getElementsByTagName( "error" );

	// Is there any errors?
	if ( errors.length != 0 )
	{

		// Loop through each error.
		for ( var i = 0; i < errors.length; i++ )
		{

			// Get the error details.
			var id = errors[i].getElementsByTagName( "id" );

			if ( id.length == 0 )
				id = null;
			else
				id = id[0].firstChild.data;

			var message = errors[i].getElementsByTagName( "message" )[0].firstChild.data;

			// Post the error.
			this.postError( id, message );

		}  // Next error.

		// Should we alert the errors?
		if ( this.alert_errors )
		{

		  // Show the first error as an alert.
		  alert( this.errors[0] );

		  // Empty out the errors array.
		  this.errors.length = 0;

		}  // End if alerting errors.

	}  // End if errors.
	else
	{

		// Finish validation.
		LW_Form_Validator.validators[this.form.id].finishValidation();

		// Return.
		return;

	}  // End if no errors.

	// If there was errors, show the generic error message.
	if ( errors.length > 0 && !this.alert_errors ) this.postError( null, "There were errors while processing the form. Please fix them and try submitting the form again." );

	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) this.submit_button.disabled = false;

}


//----------------------------------------------------------------------
// Name: finishValidation()
// Desc: Finishes the validation.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.finishValidation = function()
{
	
	// Set processing to false.
	this.processing = false;

	// Enable the submit button.
	if ( this.submit_button != null ) this.submit_button.disabled = false;

	// Check for a redirect URL and if there is one redirect them.
//	if ( this.redirect_url != null )
	//	window.location = this.redirect_url;
	
	// If the submit form flag is set, submit the form.
	if ( this.submit_form )
		return true;
	else
		return false;

}


//----------------------------------------------------------------------
// Name: postError()
// Desc: Posts an error to the form with information about what went
//       wrong and why it went wrong.
// Note: If null is passed in for element_id, the error will be placed
//       at the end of the form.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.postError = function( element_id, error )
{

	// Create the error node.
	var error_node = document.createElement( "p" );
	error_node.className = "error";

	// Stick the text into the error node.
	error_node.innerHTML = error;

	// What type of placement?
	if ( element_id == null )
	{

		// Do we have a submit button?
		if ( this.submit_button != null )
		{

			this.submit_button.parentNode.parentNode.insertBefore( error_node, this.submit_button.parentNode.parentNode.firstChild );

		}  // End if submit button.
		else
		{

			document.getElementById( this.form.id ).appendChild( error_node );

		}  // End if no submit button.

	}  // End if general error message.
	else
	{

		var html_element = document.getElementById( element_id );

		// Group or normal?
		if ( html_element.parentNode.parentNode.className == "group" )
		{

			// Insert the error node before the input element.
			html_element.parentNode.parentNode.insertBefore( error_node, html_element.parentNode.parentNode.firstChild );

		}  // End if group.
		else
		{

			// Insert the error node before the input element.
			html_element.parentNode.insertBefore( error_node, html_element.parentNode.firstChild );

		}  // End if normal.

	}  // End if normal/group error message.

}


//----------------------------------------------------------------------
// Name: cleanupForm()
// Desc: Cleans up the form to make it ready for form validation.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.cleanupForm = function()
{

	// Get all the forms error's.
	var errors = this.form.getElementsByTagName( "p" );

	// Loop through each error.
	// We get the length before hand, because we take elements away from the
	// array in the loop.
	for ( var i = 0, length = errors.length, index = 0; i < length; i++ )
	{

		// Get the element. We retrieve the 0th element because we remove
		// the child below, and the next one will fall in this place.
		var error_element = errors[index];

		// Is this an error element?
		if ( error_element.getAttribute( "class" ) == "error" )
			error_element.parentNode.removeChild( error_element );  // Remove the element.
		else
			index++; // Increment the index if we have skipped an element.


	}  // Next error node.

}


//----------------------------------------------------------------------
// Name: getQueryString()
// Desc: Concatenates all the managed input elements in to a query
//       string suitable for appending to a URL.
//----------------------------------------------------------------------
LW_Form_Validator.prototype.getQueryString = function()
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

}


