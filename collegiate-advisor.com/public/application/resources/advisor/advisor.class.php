<?php
  
	//--------------------------------------------------------------------------
	// Name: Advisor
	// Desc: A class used to encapsulate all the common operations done with an
	//       advisor.
	//--------------------------------------------------------------------------
	class Advisor extends Legato_Resource
	{
		
		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $hooks = false;
																				
	
		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------		
		//------------------------------------------------------------------------
		// Name: get_hooks()
		// Returns the hooks for this advisor.
		//------------------------------------------------------------------------ 
		public function get_hooks( $type )
		{
			
			if ( $this->hooks === false )
				$this->hooks = $this->get_sub_resources( 'Hook' );
				
			$hooks = array();
			foreach ( $this->hooks as $hook )
				if ( $hook->get( 'type' ) == $type )
					$hooks[] = $hook;
					
			return $hooks;
			
		}
		
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------ 
		//------------------------------------------------------------------------
		// Name: get_user_count()
		// Desc: Returns the number of users for each advisor.
		//------------------------------------------------------------------------ 
		public static function get_user_count()
		{
			
			// Get a DB handle.
			$dbh = Legato_DB::get( 'Main' );
			
			// Form the query.
			$query = 'SELECT advisor_id, COUNT(*) as "num_users"
			          FROM users
			          GROUP BY advisor_id';
			
			// Get the result.
			$rows = $dbh->prepare( $query )->execute()->fetch_all_array();
			
			// Loop through each row returned and format it into an array.
			foreach ( $rows as $row )
				$user_count[$row['advisor_id']] = $row['num_users'];
			
			// Return the user count.
			return $user_count;
				
		}
		
		public function get_data()
		{
			//echo '<pre>' . print_r( $this->data, 1 ) . '</pre>';
			return 'this';
			return $this->data;
		}
	  
	}

?>