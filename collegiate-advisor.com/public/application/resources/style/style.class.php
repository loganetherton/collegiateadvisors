<?php
  
	//--------------------------------------------------------------------------
	// Name: Style
	// Desc: A class used to encapsulate all the common operations done with a
	//       style.
	//--------------------------------------------------------------------------
	class Style extends Legato_Resource
	{
	
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------ 
		//------------------------------------------------------------------------
		// Name: get_advisors()
		// Desc: Returns the advisors that use this style.
		//------------------------------------------------------------------------ 
		public static function get_advisors()
		{
			
			// Get a DB handle.
			$dbh = Legato_DB::get( 'Main' );
			
			// Form the query.
			$query = 'SELECT id, CONCAT(first_name, last_name) AS name
			          FROM advisors
			          WHERE style_id = '. $this->get( 'id' );
			
			// Get the result.
			$rows = $dbh->prepare( $query )->execute()->fetch_all_array();
			
			// Loop through each row returned and format it into an array.
			foreach ( $rows as $row )
				$advisors[$row['id']] = $row['name'];
			
			// Return the user count.
			return $advisors;
				
		}
	  
	}

?>