//------------------------------------------------------------------------
// Package: Ajax
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// (Exclude)
// Class: Legato_XHR
// This class is managed by the Legato_RequestManager and holds a single
// XML HTTP Request.
//------------------------------------------------------------------------

//------------------------------------------------------------------------
// (Exclude)
// Constructor: Legato_XHR()
// The class constructor.
//------------------------------------------------------------------------
function Legato_XHR()
{

	// The user function to be called after the request has been completed.
	this.user_func = null;

	// The actual http request object.
	this.xml_http_request = null;

	// Time to create the XMLHttpRequest object.
	if ( window.XMLHttpRequest )
	{

		// This is for Opera, Mozilla, Safari, and all the smart browsers...
		this.xml_http_request = new XMLHttpRequest();

		// Try to override the mime type.
		if ( this.xml_http_request.overrideMimeType )
			this.xml_http_request.overrideMimeType( "text/xml" );

	}
	else if ( window.ActiveXObject )
	{

		// This is for IE.
		try
		{
			this.xml_http_request = new ActiveXObject( "Msxml2.XMLHTTP" );
		}
		catch ( e )
		{
			try { this.xml_http_request = new ActiveXObject( "Microsoft.XMLHTTP" ); }
			catch ( e ) {}
		}

	}

}


//------------------------------------------------------------------------
// (Exclude)
// Function: inProgress()
// Called to check if the request is still processing.
//------------------------------------------------------------------------
Legato_XHR.prototype.inProgress = function()
{

	return (this.xml_http_request.readyState != 0 && this.xml_http_request.readyState != 4);

};


//------------------------------------------------------------------------
// (Exclude)
// Function: clear()
// Called to clear out the object.
//------------------------------------------------------------------------
Legato_XHR.prototype.clear = function()
{

	// Clear out the object.
	this.xml_http_request.onreadystatechange = function(){};
	this.user_func = null;
	
	// Push onto the stack of available XHR objects.
	Legato_RequestManager.stack.push( this );

};


//------------------------------------------------------------------------
// Class: Legato_RequestManager
// This class manages all the request objects. You use this class to send
// out an Ajax request.
//------------------------------------------------------------------------
Legato_RequestManager =
{

	num_requests: 0,     // How many requests have been created.
	stack: new Array(),  // The stack of available XHR objects.
	stack_size: 10,      // The initial size of the stack.


	//--------------------------------------------------------------------
	// Function: makeRequest()
	// Sends out an HTTP request.
	//
	// Parameters:
	//     url - The URL that you would like to request.
	//     user_func - (Optional) A function that you would like to
	//                 execute once the response is returned.
	//     query_string - (Optional) The query string that you would like
	//                    to pass in to the request. If a query string is
	//                    passed in, the request will revert to a POS, if
	//                    left blank it will send as a GET.
	//--------------------------------------------------------------------
	makeRequest: function( url, user_func, query_string )
	{
		
		var http_request = null;	

		// Get the method.
		if ( query_string != null )
			method = "POST";
		else
			method = "GET";

		// Increment the number of requests.
		++Legato_RequestManager.num_requests;

		// Create the request object.
		if ( Legato_RequestManager.stack.length < 1 )
			http_request = new Legato_XHR();
		else
			http_request = Legato_RequestManager.stack.pop();

		// Store the arguments.
		http_request.user_func = user_func;

		// Set up the request.
		http_request.xml_http_request.open( method, url, true );
		http_request.xml_http_request.onreadystatechange = function() { Legato_RequestManager.handleResponse( http_request ) };

		// If we are posting...
		if ( query_string != null )
		{

			// Set the appropriate values for posting.
			http_request.xml_http_request.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded" );
			http_request.xml_http_request.setRequestHeader( "Content-length", query_string.length );
			http_request.xml_http_request.setRequestHeader( "Connection", "close" );

			// Send the request.
			http_request.xml_http_request.send( query_string );

		}
		else
		{

			// Send the request.
			http_request.xml_http_request.send( null );

		}

	},


	//--------------------------------------------------------------------
	// (Exclude)
	// Function: handleResponse()
	// This function is called when the response status changes.
	//--------------------------------------------------------------------
	handleResponse: function( http_request )
	{

		// Are we completed?
		if ( http_request.inProgress() )
			return;

		// Did it pass through?
		if ( http_request.xml_http_request.status == 200 )
		{

			// Pass on to the response user function.
			if ( http_request.user_func )
			{
				http_request.user_func( http_request.xml_http_request );

			}  // End user func.

		}  // End if passed through.

		// Release the XHR object.
		Legato_RequestManager.releaseRequest( http_request );

	},


	//--------------------------------------------------------------------
	// (Exclude)
	// Function: releaseRequest()
	// Called to release the request object.
	//--------------------------------------------------------------------
	releaseRequest: function( request_object )
	{
		
		// Clear out the object.
		request_object.clear();

	}

};

// Set up the stack.
for ( var i = 0; i < Legato_RequestManager.stack_size; i++ )
	Legato_RequestManager.stack.push( new Legato_XHR() );
