///////////////////////////////////////////
// TODO:
// If we just sorted by a column and we click it again, just reverse
// all the rows.
//////////////////////////////////////////


LW_DynamicTable.tables = new Array();

function LW_DynamicTable( table_id )
{
	
	this.table_index = LW_DynamicTable.tables.length;
	this.table_element = document.getElementById( table_id );
	this.head_element = null;
	this.body_element = null;
	this.table_data = [];
	this.last_clicked = null;
	
	// Store this dynamic table in the global dynamic table array.
	LW_DynamicTable.tables[this.table_index] = this;
	
	// Initialize this table.
	this.initialize();

}


LW_DynamicTable.prototype.initialize = function()
{
	
	// Make sure the table is correctly structured.
	this.restructureTable();
	
	// Store the head and body elements for easy access.
	this.head_element = this.table_element.getElementsByTagName( "thead" )[0];
	this.body_element = this.table_element.getElementsByTagName( "tbody" )[0];
	
	// Add the table's events.
	this.addEvents();
	
	// Loop through the table's rows.
	var table_rows = this.body_element.getElementsByTagName( "tr" );
	
	for ( var i = 0; i < table_rows.length; i++ )
	{
		
		// Get the columns.
		var table_cols = table_rows[i].getElementsByTagName( "td" );
		
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


LW_DynamicTable.prototype.sort = function( col_index )
{
	
	// If this is the last clicked column, just reverse.
	if ( col_index == this.last_clicked )
	{
		this.reverse( col_index );
		this.updateTable( col_index );
		return;
	}
	
	// The function to use in comparing.
	var compare = function ( a, b )
	{
		
		a = a.firstChild.data;
		b = b.firstChild.data;
		
		return (b.charAt( 0 ).toLowerCase() < a.charAt( 0 ).toLowerCase()) - (a.charAt( 0 ).toLowerCase() < b.charAt( 0 ).toLowerCase());
	
	}
	
	// Sort the table data.
	this.table_data[col_index].sort( compare );
	
	// Set last clicked.
	this.last_clicked = col_index;
	
	// Update the table with the new information.
	this.updateTable( col_index );
	
}


LW_DynamicTable.prototype.reverse = function( col_index )
{
	
	// Reverse the active column.
	this.table_data[col_index].reverse();
	
	// Update the table with the new information.
	this.updateTable( col_index );
	
}


LW_DynamicTable.prototype.updateTable = function( col_index )
{
	
	// Loop through all the rows in the table data and update the table.
	for ( var i = 0; i < this.table_data[col_index].length; i++ )
		this.body_element.appendChild( this.table_data[col_index][i].parentNode );
	
}


LW_DynamicTable.prototype.restructureTable = function()
{
	
	// Try to get thead and th elements.
	var thead = this.table_element.getElementsByTagName( "thead" );
	var th = this.table_element.getElementsByTagName( "th" );
	
	// If there are THs, make sure there is a THEAD to enclose them.
	if ( thead.length == 0 && th.length != 0 )
	{
		
		var head_tr = th[0].parentNode;
		
		// Add the TR element to a new THEAD element.
		var thead_elem = document.createElement( "thead" );
		thead_elem.appendChild( head_tr );
		
		// Insert the THEAD element as the first element in the table.
		this.table_element.insertBefore( thead_elem, this.table_element.firstChild );
		
	}
	
}


LW_DynamicTable.prototype.addEvents = function()
{
	
	// Get all the table headings.
	var table_headings = this.head_element.getElementsByTagName( "th" );
	
	// Loop through all the table headings and add the onclick event to them.
	for ( i = 0; i < table_headings.length; i++ )
		LW_Events_Handler.addEvent( table_headings[i], "onclick", new Function( "LW_DynamicTable.tables[" + this.table_index + "].sort( " + i + " );" ) );
	
}