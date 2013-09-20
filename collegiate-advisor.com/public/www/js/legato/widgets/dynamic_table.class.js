/*
	Class: Legato_Widgets_DynamicTable
	Make a table dynamic, with the ability to sort, search, pull data in
	from a data source, etc.
*/


/*
	Group: Functions
*/

/*
	Function: Legato_Widgets_DynamicTable()
	The class constructor.
	
	Syntax:
		object Legato_Widgets_DynamicTable( mixed table_id )
		
	Parameters:
		mixed table_id - Can be either the ID of your table element, or an element reference.
		
	Notes:
		You must call the <Legato_Widgets_DynamicTable::initialize()> function on this
		object to actually initialize it.
						
	Examples:
		> var table = new Legato_Widgets_DynamicTable( 'cool_table' );
	
	See Also:
		- <Legato_Widgets_DynamicTable::initialize()>
*/
function Legato_Widgets_DynamicTable( table_id )
{
	
	this.table_element = $$( table_id );
	this.head_element = null;
	this.body_element = null;
	this.table_data = [];
	this.last_clicked = null;
	this.direction = 'asc';
	this.unsortable = {};
	this.initialized = false;
	this.data_source = '';
	
	this.search_field = null;
	this.search_timeout = null;
	
	// Events.
	this.onPreSort = null;
	this.onPostSort = null;
	this.onPreUpdateData = null;
	this.onPostUpdateData = null;
	this.onInitSearchTimeout = null;
	this.onSendDataSearch = null;

}


/*
	Function: initialize()
	Called to actually set up and initialize the table to be dynamic.
	
	Syntax:
		void initialize( object options = {} )
		
	Parameters:
		object options - *optional* - This is the options object. 
		Defaults to the default options, or a blank options object.
		
	Options:
		These are the options that you are able to pass in to the initialize() function.
	
		- search_field - The ID/element reference of a text input element that you'd like to use
		as a search field.
		
		- sorted_col - If you've pre-sorted the table data, you can use this to pass in the sorted
		column index.
		
		- sorted_dir - If you've pre-sorted the table data, you can use this to pass in the sorted
		column's direction. Can be either 'asc' or 'desc'.
		
		- unsortable - An array of indices for any column's that you wouldn't like to be sortable.
		
		- data_source - A URL that you would like to query when updating the table's data. Note that
		you can define this option if you wouldn't like to sort with JavaScript, but would rather sort/search
		by querying a data source.
						
	Examples:
	(begin code)
		<input type="submit" id="results_search">
	
		<table id="results">
			<thead>
				<tr>
					<th>Name</th>
					<th>Position</th>
				</tr>			
			</thead>
			<tbody>
				<tr>
					<td>David DeCarmine</td>
					<td>Lead Developer</td>
				</tr>
				<tr>
					<td>Trevor Gerhardt</td>
					<td>Programmer</td>
				</tr>
			</tbody>	
		</table>		
	(end)
	
	(begin code)
		var table = new Legato_Widgets_DynamicTable( 'results' );
		table.initialize
		( {		
			search_field: 'results_search',
			sorted_col: 0	
		} );
	(end)
*/
Legato_Widgets_DynamicTable.prototype.initialize = function( options )
{
	
	// Clear out variables.
	this.table_data = [];
	this.last_clicked = null;
	this.direction = 'asc';
	
	// Any search field?
	if ( options && options.search_field != undefined )
		this.search_field = $$( options.search_field );
		
	// Only do this stuff the first time initialization is called.
	if ( !this.initialized )
	{
		
		// Make sure the table is correctly structured.
		this.restructureTable();
		
		// Store the head and body elements for easy access.
		this.head_element = this.table_element.elmsByTag( 'thead' ).first();
		this.body_element = this.table_element.elmsByTag( 'tbody' ).first();
		
		// Add the table's events.
		this.addEvents();
		
	}
	else  // Only do on reinitialization.
	{
		
		// Get the table's headings.
		this.head_element.elmsByTag( 'th' ).each( function()
		{
			$( this ).removeClass( 'sorted_asc' );
			$( this ).removeClass( 'sorted_desc' );
		} );
		
	}
	
	// Initialize the rows.
	this.initRows();
	
	// Any options?
	if ( options )
	{
		
		// Get the table's headings.
		var table_headings = this.table_element.elmsByTag( 'th' );
		
		// Are we already sorted?
		if ( options.sorted_col != undefined )
		{
			if ( options.sorted_dir == undefined )
				options.sorted_dir = 'asc';
			
			this.direction = options.sorted_dir;
			this.last_clicked = options.sorted_col;
			$( table_headings[options.sorted_col] ).addClass( 'sorted_' + this.direction );
		}
		
		// If there's any unsortable columns passed in, make an object out of the
		// unsortable columns so we can test for it quickly.
		if ( options.unsortable != undefined )
			for( var i = 0; i < options.unsortable.length; i++ )
			{
				this.unsortable[options.unsortable[i]] = true;
				$( table_headings[options.unsortable[i]] ).addClass( 'unsortable' );
			}
			
		// Any datasource?
		if ( options.data_source != undefined )
			this.data_source = options.data_source;
		
	}  // End if options passed in.
	
	// Set as initialized.
	this.initialized = true;
	
	// Send the data search.
	if ( this.data_source )
		this.sendDataSearch();
	
}


/*
	(Exclude)
	Function: initRows()
	Stores all the rows' data so that we can sort through it later.
	This should be called any time the table's data is changed manually.
*/
Legato_Widgets_DynamicTable.prototype.initRows = function()
{
	
	this.table_data = [];
	
	// Loop through the table's rows.
	var table_rows = this.body_element.elmsByTag( 'tr' );
	
	for ( var i = 0; i < table_rows.length; i++ )
	{
		
		// Get the columns.
		var table_cols = $( table_rows[i] ).elmsByTag( 'td' );
		
		// Skip empty rows.
		if ( table_cols.length == 0 )
			continue;
		
		// Loop through the cols and store them.
		for ( n = 0; n < table_cols.length; n++ )
		{
			
			// Make sure we have an array for this table data index.
			if ( typeof( this.table_data[n] ) != "object" )
				this.table_data[n] = [];
			
			// Add the column.
			this.table_data[n].push( table_cols[n] );
			
			
		}  // Next col.
		
	}  // Next row.
	
}


/*
	(Exclude)
	Function: initSort()
	Called to sort the table data.
	This will set up the system to sort, either with JavaScript or through
	a data source.
*/
Legato_Widgets_DynamicTable.prototype.initSort = function( col_index )
{
	
	// If this is an unsortable column, don't process.
	if ( this.unsortable[col_index] )
		return;
	
	// Call the onPreSort event.
	if ( this.onPreSort != null && this.onPreSort( col_index ) == false )
		return;
	
	// Get the correct direction that the clicked column is facing.
	if ( (col_index != this.last_clicked) || (col_index == this.last_clicked && this.direction == 'desc') )
		this.direction = 'asc';	
	else
		this.direction = 'desc';
	
	// Are we using a data source?
	if ( this.data_source )
	{
		
		// Set last clicked.
		this.last_clicked = col_index;
		
		// Send off the search request now.
		this.sendDataSearch();
		
	}
	else
	{
		
		// Is this the last clicked column, or a new column to sort?
		if ( col_index != this.last_clicked )
		{
			// Set last clicked and sort.
			this.last_clicked = col_index;			
			this.sort( col_index );
		}
		else
			this.reverse( col_index );
			
		// Update the table with the new information.
		this.updateTable( col_index );
		
	}
	
	// Call the onPostSort event.
	if ( this.onPostSort != null )
		this.onPostSort( col_index );
	
}


/*
	(Exclude)
	Function: sort()
	Called to sort the table data.
	This will call updateTable to update the actual table's rows once the data
	is sorted.
*/
Legato_Widgets_DynamicTable.prototype.sort = function( col_index )
{
	
	// Sort the table data.
	if ( this.table_data[col_index][0].innerText.charAt( 0 ) == "$" )
	{
		
		// The function to use in comparing.
		var sort_func = function ( a, b )
		{
			
			a = a.innerText.replace( /[,\\s]/g, "" );
			b = b.innerText.replace( /[,\\s]/g, "" );
			
			a = new Number( a.substring( 1 ) );
			b = new Number( b.substring( 1 ) );
			
			return (b < a) - (a < b);
		
		}
		
	}  // Money sort.
	else
	{
		
		// The function to use in comparing.
		var sort_func = function ( a, b )
		{
			
			a = a.innerText;
			b = b.innerText;
			
			return (b.charAt( 0 ).toLowerCase() < a.charAt( 0 ).toLowerCase()) - (a.charAt( 0 ).toLowerCase() < b.charAt( 0 ).toLowerCase());
		
		}
		
	}  // Normal sort.
	
	// Sort the data.
	this.table_data[col_index].sort( sort_func );
	
}


/*
	(Exclude)
	Function: reverse()
	Called to simply reverse all the rows in the table.
	This is here so that we can quickly reverse if the user clicks on the same
	column twice. So that when the direction changes, all we do is reverse.
*/
Legato_Widgets_DynamicTable.prototype.reverse = function( col_index )
{
	
	// Reverse the active column.
	this.table_data[col_index].reverse();
	
	// Update the table with the new information.
	this.updateTable( col_index );
	
}


/*
	(Exclude)
	Function: updateTable()
	This is called to reorder the table's rows to match the data modified
	by the sort function.
*/
Legato_Widgets_DynamicTable.prototype.updateTable = function( col_index )
{
	
	var th = this.table_element.elmsByTag( 'th' );
	
	th.each( function()
	{
		$( this ).removeClass( 'sorted_asc' );
		$( this ).removeClass( 'sorted_desc' );
	} );
	
	// Set the class for the column heading.
	th[col_index].addClass( 'sorted_' + this.direction );
	
	// If there is no data for this column, just return.
	if ( !this.table_data[col_index] )
		return;
	
	// Loop through all the rows in the table data and update the table.
	for ( var i = 0; i < this.table_data[col_index].length; i++ )
		this.body_element.appendChild( this.table_data[col_index][i].parentNode );
		
	// Zebra stripe the table.
	this.body_element.cssSelect( 'tr:nth-child(odd)' ).each( function(){ this.addClass( 'row1' ); } );				
	this.body_element.cssSelect( 'tr:nth-child(even)' ).each( function(){ this.addClass( 'row2' ); } );
	
}


/*
	(Exclude)
	Function: sendDataSearch()
	Sends a request out to get data to populate the table.
	Queries the data source passed in when the table was constructed.
	It should receive a JSON request.
*/
Legato_Widgets_DynamicTable.prototype.sendDataSearch = function()
{
	
	// Make sure a data source was passed in.
	if ( !this.data_source )
		return false;
		
	var th = this.head_element.elmsByTag( 'th' );
		
	var params = {};		
	params['query'] = '';
	params['page'] = 1;
	params['column'] = th[this.last_clicked].id;
	params['direction'] = this.direction;
	
	// If there is a search field, try to get the value of it.
	if ( this.search_field )
	{
		this.search_timeout = null;
		params['query'] = this.search_field.value;
	}
	
	// Call the onSendDataSearch event.
	if ( this.onSendDataSearch != null )
		params = this.onSendDataSearch( params );
	
	// Create the query string from the parameters.
	var query_string = '';
	for ( var key in params )
		query_string = query_string + key + '=' + params[key] + '&';
	
	// Send off the request.
	var table = this;
	Legato_RequestManager.makeRequest( this.data_source, function( response ){ table.updateTableData( response ); }, query_string );

}


/*
	(Exclude)
	Function: updateTableData()
	Updates the table with data received from the data source.
*/
Legato_Widgets_DynamicTable.prototype.updateTableData = function( response )
{
	
	var response = eval( '(' + response.responseText + ')' );
	
	// Call the onPreUpdateData event.
	if ( this.onPreUpdateData != null && this.onPreUpdateData( response ) == false )
		return false;
	
	// Remove all the rows.
	this.body_element.elmsByTag( 'tr' ).each( function(){ this.remove(); } );
	
	// Loop through all the rows returned.
	if ( response.rows && response.total_count > 0 )
	{
		for ( var row_id in response.rows )
		{
			
			var row_data = response.rows[row_id];
			var row = this.body_element.create( 'tr', {}, true );
			var new_cells = [];
			
			// Add all the new cells before we populate them with data.
			for ( var i in row_data )
			{
				if ( i == 'class_name' )
					continue;
					
				new_cells.push( row.create( 'td', {}, true ) );
			}
				
			// Now run through the data and populate the correct cells with the data.
			var headings = this.head_element.elmsByTag( 'th' );
			for ( var i in row_data )
			{
				if ( i == 'class_name' )
					continue;
				
				var th = $$( i );	
				new_cells[th.cellIndex].innerHTML = row_data[i];
			}
			
			// Check for a class name.
			if ( row_data['class_name'] )
				$( row ).addClass( row_data['class_name'] );
			
		}
	}
	else
	{
			
		var td_elem = document.createElement( 'td' );
		td_elem.colSpan = this.head_element.elmsByTag( 'th' ).length;
		td_elem.className = 'no_results';
		td_elem.innerHTML = 'No results to show.';
		
		var row_elem = this.body_element.create( 'tr', {}, true ).addContent( td_elem );
	
	}
	
	// Reinitialize the dynamic table.
	this.initRows();
		
	// Call the onPostUpdateData event.
	if ( this.onPostUpdateData != null )
		this.onPostUpdateData( response );
		
	this.updateTable( this.last_clicked );
	
}


/*
	(Exclude)
	Function: initSearchTimeout()
	Initializes the search timeout.
*/
Legato_Widgets_DynamicTable.prototype.initSearchTimeout = function()
{
	
	// Send off the request.
	var table = this;
	
	// If the timeout already started, clear it out so we can start it again.
	if ( this.search_timeout != null )
		window.clearTimeout( this.search_timeout );
	
	// Set the timeout.
	// Delay one second, then send the search.
	this.search_timeout = window.setTimeout( function(){ table.sendDataSearch(); }, 1000 );
	
	// Call the onInitSearchTimeout event.
	if ( this.onInitSearchTimeout != null )
		this.onInitSearchTimeout();

}


/*
	(Exclude)
	Function: addEvents()
	Adds the correct events to the table.
*/
Legato_Widgets_DynamicTable.prototype.addEvents = function()
{
	
	var table = this;
	
	// Set an onclick event for the heading. We'll get the target of the click
	// in the event handler.
	Legato_Events_Handler.addEvent( this.head_element, 'onclick', function( e )
	{ 
		var col_index = Legato_Events_Handler.getTarget( e ).cellIndex;
		table.initSort( col_index ); 
	} );
	
	// Any search field to worry about?
	if ( this.search_field )
	{
		// Set up the timeout event on key up.
		Legato_Events_Handler.addEvent( this.search_field, 'onkeyup', function(){ table.initSearchTimeout(); } );		
	}

}


/*
	(Exclude)
	Function: restructureTable()
	Restructures the table to make sure it's in a good format to work with.
*/
Legato_Widgets_DynamicTable.prototype.restructureTable = function()
{
	
	// Try to get thead and th elements.
	var thead = this.table_element.elmsByTag( 'thead');
	var th = this.table_element.elmsByTag( 'th' )
	
	// If there are THs, make sure there is a THEAD to enclose them.
	if ( thead.length == 0 && th.length != 0 )
	{
		
		var head_tr = th.first().parentNode;
		
		// Add the TR element to a new THEAD element.
		var thead_elem = $( this.table_element ).create( 'thead' ).addContent( head_tr );
		
		// Insert the THEAD element as the first element in the table.
		this.table_element.insertBefore( thead_elem, this.table_element.firstChild );
		
	}
	
}


/*
	Group: Events

	Event: onPreSort( int col_index )
	Called prior to sorting a column. Is passed in the index of the column that's being sorted.
	If the event handler returns false, the column will not be sorted.
	
	Event: onPostSort( int col_index )
	Called after sorting a column. Is passed in the index of the column that's being sorted.
	
	Event: onSendDataSearch( object params )
	Called when sending a data search to a data source. Is passed in the array of parameters
	that will be sent to the data source. This allows you to modify the parameters before they're
	actually sent. Note that you MUST return the parameter object or no parameters will be sent.
	The parameters set in the params object are as follows:
	
	- query - The search string to be sent.
	- page - The page number. Currently is always equal to 1. This allows you to modify it correctly.
	- column - The currently active column's ID.
	- direction - The direction the currently active column is being sorted.
	
	Event: onPreUpdateData( object response )
	Called prior to updating the data from a retrieval from a data source.
	Is passed the response object returned from the data source.
	If you return false, the table will not be updated with the newly retreived data.
	
	Event: onPostUpdateData( object response )
	Called after updating the data from a retrieval from a data source.
	Is passed the response object returned from the data source.
	
	Event: onInitSearchTimeout()
	Called when someone starts typing into the search box specified. If you specified one.
*/