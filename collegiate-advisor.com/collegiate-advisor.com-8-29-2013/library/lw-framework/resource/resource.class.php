<?php
	 
	//----------------------------------------------------------------------------
	// Class: LW_Resource
	// The base class used for extending a new resource class.
	//----------------------------------------------------------------------------
	class LW_Resource
	{		
		
		private static $resource_info        = array( 'LW_Resource' => true );
		private static $resource_data        = array( 'LW_Resource' => array( true ) );
		private static $populated_info       = array();
		private static $modifiers            = array();
		private static $resource             = null;
		private static $cache                = null;
		
		
		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $name = '';
		private $id = 0;
		private $info = NULL;
		private $data = NULL;
		private $populated = NULL;
		
		
		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------ 
		
		//------------------------------------------------------------------------
		// Constructor: __construct()
		// Takes in a resource's ID and sets up the resource.
		//
		// Parameters:
		//     $id - The ID of the resource you'd like to work with.
		//
		//     OR
		//
		//     $input - An array where the key is the field and the value is the
		//              value the resource has for that field.
		//
		// Examples:
		//     (start code)
		//
		//     // Getting user with an ID of 1.
		//     $user = new User( 1 );
		//
		//     // Getting user with a first name of David.
		//     $user = new User( array( 'first_name' => 'David' ) );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function __construct( $input = 0 )
		{
			
			$this->name = get_class( $this );
			$this->info = &self::$resource_info[$this->name];
			
			// Make sure this resource's information is being managed by the system.
			if ( !$this->info )
				$this->populate_resource_info();
			
			// Array or ID passed in?
			if ( is_array( $input ) )
			{
				
				$data = reset( self::fetch_data( $this->name, $input ) );
				$first_table = reset( $this->info['tables'] );
				$this->id = $data[$first_table['id']];
				
				$this->data = &self::$resource_data[$this->name][$this->id];
				$this->data = $data;
				
				$this->populated = &self::$populated_info[$this->name][$this->id];
				$this->populated['LW_main'] = true;
				
			}  // End if array passed in.
			else
			{
				
				// Was a blank resource created?
				if ( $input == 0 )
					$this->id = 'LW_New_' . count( self::$populated_info[$this->name] );
				else
					$this->id = $input;
				
				$this->data = &self::$resource_data[$this->name][$this->id];
				$this->populated = &self::$populated_info[$this->name][$this->id];
				$this->populated['LW_main'] = false;
				
			}  // End if ID passed in.
			
			// Already stored?
			if ( !$this->data && $input != 0 )
			{
				
				// Loop through each table and store the ID.
				foreach ( $this->info['tables'] as $table_info )
					$this->data[$table_info['id']] = $this->id;
			
			}	
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: get()
		// Retrieve a property from the resource.
		//
		// Parameters:
		//     $property - The property of the resource that you'd like to get.
		//                 Leave blank to get all the data this resource holds.
		//
		// Examples:
		//     (start code)
		//
		//     // Getting first name.
		//     $user->get( 'first_name' );
		//
		//     // Getting all the reosurces data.
		//     $user->get();
		//
		//     (end)
		//------------------------------------------------------------------------
		public function get( $property = '' )
		{
			
			if ( !$property )
			{
				
				// Populate if the resource has not been populated.
				if ( !$this->populated['LW_main'] )
					$this->populate_resource_data( $property );
				
				// Return this resource's data.
				return $this->data;
				
			}
			else
			{
		  
				// Populate if resource has not been populated.
				if ( !$this->data[$property] && !$this->populated['LW_main'] || ($this->info['deferred'][$property] && !$this->populated[$property]) )
					$this->populate_resource_data( $property );
				
				// Return the property.
				return $this->data[$property];
				
			}
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: set()
		// Sets a property in the resource.
		//
		// Parameters:
		//     $property - The property of the resource that you'd like to set.
		//     $value - The new value that you'd like to assign to the property.
		//
		//     OR
		//
		//     $data_array - An array of key/value pairs where the key is the
		//                   property you'd like to set, and the value is the new
		//                   value you'd like it to have.
		//
		// Examples:
		//     (start code)
		//
		//     // Setting first name.
		//     $user->set( 'first_name', 'David' );
		//
		//     // Setting name with a data array.
		//     $data['first_name'] = 'David';
		//     $data['last_name'] = 'DeCarmine';
		//     $user->set( $data );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function set()
		{
			
			$args = func_get_args();
			
			if ( is_array( $args[0] ) )
				$this->data = array_merge( $this->data, $args[0] );
			else
				$this->data[$args[0]] = $args[1];
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: get_sub_resources()
		// Allows you to retrieve sub-resources for one or more resources.
		//
		// Parameters:
		//
		//     _Called on resource object:_
		//
		//     $sub_resource_name - The name of the sub-resource that you'd like
		//                          to fetch.
		//
		//     _Called statically:_
		//
		//     $resource_name - The name of the resource that you'd like to fetch
		//                      sub-resources for.
		//     $input - A single ID or an array of IDs of the resources you'd
		//              like to fetch sub-resources for.
		//     $sub_resource_name - The name of the sub-resource that you'd like
		//                          to fetch.
		//
		//     OR
		// 
		//     $resource - A resource object or an array of resource objects that 
		//                 you'd like to fetch sub-resources for.
		//     $sub_resource_name - The name of the sub-resource that you'd like
		//                          to get.
		//
		// Examples:
		//     (start code)
		//
		//     // Getting the comments for an instantiated user resource.
		//     $user->get_sub_resources( 'User_Comment' );
		//
		//     // Getting the comments for multiple instantiated user resources.
		//     $users[] = new User( 1 );
		//     $users[] = new User( 2 );
		//     LW_Resource::get_sub_resources( $users, 'User_Comment' );
		//
		//     // The same thing as above can be accomplished on one line.
		//     LW_Resource::get_sub_resources( 'User', array( 1, 2 ), 'User_Comment' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function get_sub_resources()
		{
			
			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );
			
			if ( $isStatic )
				return call_user_func_array( array( 'LW_Resource', 'static_get_sub_resources' ), $args );
			else				
				return $this->static_get_sub_resources( $this, $args[0] );
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: create()
		// Inserts one or more resources into a database.
		//
		// Parameters:
		//
		//     _Called on resource object:_
		//
		//     $resource_data - (Optional) The resource's data.
		//
		//     _Called statically:_
		//
		//     $resource_name - The name of the resource that you'd like to create.
		//     $resource_data - (Optional) The resource's data.
		//     $count - (Optional) The number of resources to create. If this is
		//              passed in, $count number of resources will be created with
		//              the data passed in.
		//
		// Examples:
		//     (start code)
		//
		//     // Creating user with first name of David and last name of DeCarmine.
		//     $user = new User();
		//     $user->set( 'first_name', 'David' );
		//     $user->create( array( 'last_name', 'DeCarmine' ) );
		//
		//     // The same can be accomplished in one line.
		//     LW_Resource::create( 'User', array( 'first_name' => 'David', 'last_name' => 'DeCarmine' ) );
		//
		//     // And if we want to create 10 copies.
		//     LW_Resource::create( 'User', array( 'first_name' => 'Copy', 'last_name' => 'Cat' ), 10 );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function create()
		{
			
			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );
			
			if ( $isStatic )
				return call_user_func_array( array( 'LW_Resource', 'static_create' ), $args );
			else
			{
				
				// We can only create new resources.
				if ( strpos( $this->id, 'LW_New_' ) === false )
				{
					LW_Debug_Debugger::add_item( 'You can only call the create function on a newly instantiated resource.' );
					return false;
				}
				
				// Strip out the IDs.
				foreach ( $this->info['tables'] as $table_info )
					unset( $this->data[$table_info['id']] );
					
				// If data was passed in, merge it.
				if ( $args[0] && is_array( $args[0] ) )
					$this->data = array_merge( $this->data, $args[0] );
			
				// Create the new resource.
				$id = $this->static_create( $this->name, $this->data );
				
				// Set the new arrays to point correctly.
				self::$resource_data[$this->name][$id] = &self::$resource_data[$this->name][$this->id];
				self::$populated_info[$this->name][$id] = &self::$populated_info[$this->name][$this->id];
				
				// Set our internal arrays to point correctly.
				$this->id = $id;
				$this->data = &self::$resource_data[$this->name][$this->id];
				$this->populated = &self::$populated_info[$this->name][$this->id];
				$this->populated['LW_main'] = false;
				
				// Add the new IDs back in.
				foreach ( $this->info['tables'] as $table_info )
					$this->data[$table_info['id']] = $this->id;
				
				// Return the new ID.
				return $id;
				
			}
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: update()
		// Updates one or more resources in the database.
		// If a data array is passed in, it will merge it with the current data
		// held in the resource, if there is any.
		//
		// Parameters:
		//
		//     _Called on resource object:_
		//
		//     $resource_data - (Optional) The resource's data.
		//
		//     _Called statically:_
		//
		//     $resource_name - The name of the resource that you'd like to update.
		//     $resource_id - An ID or an array of multiple resources.
		//     $resource_data - (Optional) The data to use in updating.
		//
		//     OR
		//
		//     $resource - A resource object or an array of resource objects that
		//                 you'd like to update.
		//     $resource_data - (Optional) The data to use in updating.
		//
		// Examples:
		//     (start code)
		//
		//     // Changing a user's first name.
		//     $user = new User( 1 );
		//     $user->update( array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     LW_Resource::update( 'User', 1, array( 'first_name' => 'Dan' ) );
		//
		//     // Updating multiple resources.
		//     $users[] = new User( 1 );
		//     $users[] = new User( 2 );
		//     LW_Resource::update( $users, array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     LW_Resource::update( 'User', array( 1, 2 ), array( 'first_name' => 'Dan' ) );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function update()
		{
			
			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );
			
			if ( $isStatic )
				return call_user_func_array( array( 'LW_Resource', 'static_update' ), $args );
			else
			{
				
				// Merge the data arrays if data passed in.
				if ( $args[0] )
					$args[0] = array_merge( $this->data, $args[0] );
				
				return LW_Resource::static_update( $this, $args[0] );
				
			}
		
		}
		
		
		//------------------------------------------------------------------------
		// Function: delete()
		// Deletes one or more resources from the database/system.
		//
		// Parameters:
		//
		//     _Called on resource object:_
		//
		//     No arguments.
		//
		//     _Called statically:_
		//
		//     $resource_name - The name of the resource that you'd like to update.
		//     $resource_id - An ID or an array of multiple resources.
		//     $resource_data - (Optional) The data to use in updating.
		//
		//     OR
		//
		//     $resource - A resource object or an array of resource objects that
		//                 you'd like to update.
		//     $resource_data - (Optional) The data to use in updating.
		//
		// Examples:
		//     (start code)
		//
		//     // Changing a user's first name.
		//     $user = new User( 1 );
		//     $user->update( array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     LW_Resource::update( 'User', 1, array( 'first_name' => 'Dan' ) );
		//
		//     // Updating multiple resources.
		//     $users[] = new User( 1 );
		//     $users[] = new User( 2 );
		//     LW_Resource::update( $users, array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     LW_Resource::update( 'User', array( 1, 2 ), array( 'first_name' => 'Dan' ) );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function delete()
		{
			
			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );
			
			if ( $isStatic )
				return call_user_func_array( array( 'LW_Resource', 'static_delete' ), $args );
			else
				return LW_Resource::static_delete( $this );
		
		}
		
		
		//------------------------------------------------------------------------
		// Static Member Functions
		//------------------------------------------------------------------------ 		
		
		//------------------------------------------------------------------------
		// Function: fetch()
		// Allows you to retrieve multiple resources from the system.
		//
		// Parameters:
		//     $resource_name - The name of the resource that you'd like to get.
		//     $input - (FILL THIS OUT)
		//------------------------------------------------------------------------
		public static function fetch( $resource_name, $input = array() )
		{
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			$return_data = array();

			// Get the resource data.
			$resource_data = self::fetch_data( $resource_name, $input );
			
			// Loop through each resource's data.
			foreach ( $resource_data as $resource_id => $data )
			{
				
				// If this resource already has data stored, merge the data arrays
				// so that we don't lose a field by accident.
				if ( !self::$populated_info[$resource_name][$resource_id] )
					self::$resource_data[$resource_name][$resource_id] = $data;
				else
					self::$resource_data[$resource_name][$resource_id] = array_merge( self::$resource_data[$resource_name][$resource_id], $data );
				
				// Create a new resource for the return data.
				$return_data[$resource_id] = new $resource_name( $resource_id );
				$return_data[$resource_id]->populated['LW_main'] = true;
				
			}
			
			// Return the data.
			return $return_data;
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: order_by()
		// Sets an "order by" modifier.
		// The next query will use this modifier and then clear it.
		//
		// Parameters:
		//     $column - The column that you'd like to sort by.
		//     $direction - (Optional) The direction you'd like to sort: 
		//                  asc or desc.
		//
		//     OR
		//
		//     $order_options - An array where the first value is the column you'd
		//                      like to sort by and the second value is the direction.
		//
		//     ... (Pass in as many arrays for as many fields as your want)
		//
		// Examples:
		//     (begin code)
		//
		//     // Getting all the users and sort by their first name in descending order.
		//     LW_Resource::order_by( 'first_name', 'desc' )::fetch( 'User' );
		//
		//     // Ordering by more than one field.
		//     LW_Resource::order_by( array( 'first_name', 'desc' ), array( 'last_name' ) )::fetch( 'User' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public static function order_by()
		{
			
			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new LW_Resource();
			
			$args = func_get_args();
			
			if ( is_array( $args[0] ) )
			{
				
				// Loop through each order by clause passed in.
				foreach ( $args as $order_options )
				{
					
					// Store.
					if ( !$order_options[1] ) $order_options[1] = 'asc';
					self::$modifiers['order_by'][] = $order_options;
					
				}  // Next order by.
				
			}
			else
			{
				
				// Store.
				if ( !$args[1] ) $args[1] = 'asc';
				self::$modifiers['order_by'][] = $args;
				
			}
			
			// Return the fake resource.
			return self::$resource;
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: limit()
		// Sets a "limit" modifier.
		// The next query will use the modifier and then clear it.
		//
		// Parameters:
		//     $offset - The offset that you'd like to start at.
		//     $count - The number of rows you'd like to retrieve after the
		//              offset.
		//
		//     OR
		//
		//     $count - The number of rows you'd like to retrieve from the start
		//              of the result array.
		//
		// Examples:
		//     (begin code)
		//
		//     // Getting 10 users from the start of the table.
		//     $users = LW_Resource::limit( 10 )->fetch( 'User' );
		//
		//     // Getting 10 users after an offset of 5.
		//     $users = LW_Resource::limit( 5, 10 )-> fetch( 'User' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public static function limit( $offset, $count = false )
		{
			
			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new LW_Resource();
			
			// If only one argument passed in, swap the variables.	
			if ( $count === false )
			{
				$count = $offset;
				$offset = false;
			}
			
			// Store the order by.
			self::$modifiers['limit'] = array( 'offset' => $offset, 'count' => $count );
			
			// Return the fake resource.
			return self::$resource;
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: clear_modifiers()
		// Clears all modifiers set.
		// This is called at the end of each query.
		//------------------------------------------------------------------------
		public static function clear_modifiers()
		{
			
			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new LW_Resource();
			
			// Clear out the modifiers
			self::$modifiers = array();
			
			// Return the fake resource.
			return self::$resource;
			
		}
		
		
		//------------------------------------------------------------------------
		// Function: populate()
		// Takes in the data for multiple resources and populates the data for
		// them and returns the resource objects.
		//
		// Parameters:
		//     $resource_name - The name of the resources that you'd like to
		//                      populate.
		//     $data - The resources' data to use to populate them.
		//------------------------------------------------------------------------
		public static function populate( $resource_name, $data )
		{
			
			$return_data = array();
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];
			$first_table = reset( $resource_info['tables'] );
			
			// Loop through each resource's data.
			foreach ( $data as $resource_data )
			{
				
				$resource_id = $resource_data[$first_table['id']];
				
				// If this resource already has data stored, merge the data arrays
				// so that we don't lose a field by accident.
				if ( !self::$populated_info[$resource_name][$resource_id] )
					self::$resource_data[$resource_name][$resource_id] = $resource_data;
				else
					self::$resource_data[$resource_name][$resource_id] = array_merge( self::$resource_data[$resource_name][$resource_id], $resource_data );
					
				// Create a new resource for the return data.
				$return_data[$resource_id] = new $resource_name( $resource_id );
				$return_data[$resource_id]->populated['LW_main'] = true;
				
			}
			
			// Return the data.
			return $return_data;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: count()
		// Returns the number of resources in the database with that name.
		//
		// Parameters:
		//     $resource_name - The name of the resource.
		//------------------------------------------------------------------------
		public static function count( $resource_name )
		{
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Get a database handle.
			$dbh = LW_DB::get( $resource_info['db'] );

			// Get the first table's name.
			reset( $resource_info['tables'] );
			$table_name = key( $resource_info['tables'] );

			// Create and execute the query.
			$query = 'select count(*) as "num" from ' . $table_name;

			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute();
			
			$row = $stmt->fetch_array();

			// Return the number of rows.
			return $row['num'];

		}
		
		
		//------------------------------------------------------------------------
		// Private Static Member Functions
		//------------------------------------------------------------------------
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: fetch_data()
		// Retrieves the required information from the database to be used to
		// populate resources.
		//------------------------------------------------------------------------
		private static function fetch_data( $resource_name, $input, $options = array() )
		{
			
			// Get the input array.
			$input_array = (is_array( $input )) ? $input : array( $input );

			// Check if the class exists.
			if ( !class_exists( $resource_name ) )
			{
				LW_Debug_Debugger::add_item( 'The resource, ' . $resource_name . ', does not exist. Make sure you typed it in correctly.' );
				return false;
			}

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];
			
			// If we are doing a normal fetch.
			$table_join            = '';
			$select_string         = '';
			$conditional_string    = '';
			if ( !$options['deferred_field'] || $options['fetch_all'] )
			{
			
				// Loop through each table.
				$first_table_name      = '';
				$first_table_id_field  = 0;
				$i = 0;
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{
					
					$i++;
	
					// Add to the table join.
					if ( $i == 1 )
					{
						$first_table_name = $table_name;
						$first_table_id_field = $table_info['id'];
						$table_join .= $table_name;	
					}
					else
						$table_join .= ' INNER JOIN ' . $table_name . ' ON ' . $table_name . '.' . $table_info['id'] . ' = ' . $first_table_name . '.' . $first_table_id_field;
						
					// Let's put down the select string.						
					$n = 1;
					foreach ( $table_info['fields'] as $field )
					{
						if ( !$resource_info['deferred'][$field] || $field == $options['deferred_field'] )
							$select_string .= ($i == 1 && $n == 1) ? (' ' . $table_name . '.' . $field) : (', ' . $table_name . '.' . $field);
							
						$n++;
					}
	
				}  // Next table.
				
			}
			else  // If getting a deferred key.
			{
				
				// Find which table this deferred key goes with.
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{
					
					if ( in_array( $extra_options['deferred_field'], $table_info['fields'] ) )
					{
						
						$select_string .= $table_name . '.' . $extra_options['deferred_field'];
						$table_join .= $table_name;
						
						// We do this so that we can have the logic below process it
						// like the user passed in a key and value. This is done so that
						// a huge if statement doesn't have to be placed around everything.
						$input_array = array( $table_info['id'] => $input_array[0] );
											
					}
				}
				
			}
			
			// If fetching by ID.
			if ( is_numeric( $input_array[0] ) )
			{
				
				// Loop through each input element.
				$conditional_string  = '';
				$query_values        = array();
				$dependant_index     = 1;
				$i                   = 1;
				foreach ( $input_array as $input_element )
				{
	
					// Get the type of input.
					if ( is_a( $input_element, $resource_name ) )
						$input_type = 1;
					else if ( is_numeric( $input_element ) )
						$input_type = 2;
	
					// Start the conditional.
					if ( $i == 1 )
						$conditional_string .= '(';
					else
						$conditional_string .= ' OR (';
	
					// Loop through each table.
					$n = 1;
					foreach ( $resource_info['tables'] as $table_name => $table_info )
					{
	
						// Add to the conditional.
						if ( $n == 1 )
							$conditional_string .= $table_name .'.'. $table_info['id'] .' = ?';
						else
							$conditional_string .= ' AND '. $table_name .'.'. $table_info['id'] .' = ?';
	
						// Don't process if this is a sub-resource and we aren't on our first iteration.
						if ( !($linked_id_field == true && $n != 1) )
						{
	
							// Add the ID.
							if ( $input_type == 1 )
								$query_values[] = $input_element->id;
							else if ( $input_type == 2 )
								$query_values[] = $input_element;
	
						}
						
						$n++;
	
					}  // Next table.
	
					// End the conditional.
					$conditional_string .= ')';
	
					$i++;
	
				}  // Next input element.
				
				$conditional_string = ' WHERE (' . $conditional_string . ')';
				
			}
			else  // Get by conditional.
			{
				
				if ( $conditional_string )
					$conditional_string = ' WHERE (' . $conditional_string . ')';
				
				// Loop through each input element.
				$query_values = array();
				$i = 1;
				foreach ( $input_array as $key => $value )
				{
					
					if ( $conditional_string )
						$conditional_string .= ' AND ';
					else
						$conditional_string .= ' WHERE ';
						
					// Any special syntax?
					$length = strlen( $key );
					$char1 = $key[($length - 2)];
					$char2 = $key[($length - 1)];
					
					if ( $char1 == '>' && $char2 == '=' )
					{
						$key = substr( $key, 0, -3 );
						$operation = '>=';
						
					}  // End if >=
					else if ( $char1 == '<' && $char2 == '=' )
					{
						$key = substr( $key, 0, -3 );
						$operation = '<=';
						
					}  // End if <=
					else if ( $char1 == '!' && $char2 == '=' )
					{
						$key = substr( $key, 0, -3 );
						$operation = '!=';
						
					}	// End if !=			
					else if ( $char2 == '>' )
					{
						$key = substr( $key, 0, -2 );
						$operation = '>';
						
					}  // End if >
					else if ( $char2 == '<' )
					{
						$key = substr( $key, 0, -2 );
						$operation = '<';
						
					}  // End if <
					else if ( $char2 == '!' )
					{
						$key = substr( $key, 0, -2 );
						$operation = 'NOT LIKE';
						
					}	// End if NOT LIKE
					else
						$operation = 'LIKE';
					
					if ( !is_array( $value ) )
						$value = array( $value );
					
					$conditional = '';
					$values = $value;
					foreach ( $values as $value )
					{
						
						// Add the OR if we need to.
						if ( $conditional )
							$conditional .= ' OR ';
							
						$conditional .= $resource_info['fields'][$key] . '.' . $key . ' ' . $operation . ' ?';
						
						// Add the value to the query values array.
						$query_values[] = $value;

					}
										
					$conditional_string .= '(' . $conditional . ')';
					
				}  // Next input element.
				
			}  // End if getting by conditional.
			
			// Any foreign keys we have to add?
			if ( $resource_info['foreign_keys'] )
			{
				
				// Loop through each key.
				$key_index  = 1;
				$table_index = 1;
				$key_table = '';
				foreach ( $resource_info['foreign_keys'] as $key => $reference )
				{
					
					// Make sure the information has been populated for the referenced resource.
					if ( !self::$resource_info[$reference] ) 
						new $reference( 0 );
					
					// Find out which table this key belongs to.
					foreach ( $resource_info['tables'] as $table_name => $table_info )
						foreach ( $table_info['fields'] as $field )
							if ( $field == $key )
							{
								$key_table = $table_name;
								break;
							}
					
					// Loop through each table for the referenced resource.
					foreach ( self::$resource_info[$reference]['tables'] as $table_name => $table_info )
					{
						
						// Loop through each field and store it in the select string.
						foreach ( $table_info['fields'] as $field )
							if ( !self::$resource_info[$reference]['deferred'][$field] )
							{
								$select_string .= ', dt' . $table_index . '.' . $field . ' AS d' . $key_index;
								++$key_index;
							}
						
						// Add to the table join.
						$table_join .= ' LEFT JOIN '. $table_name . ' AS dt' . $table_index . ' ON dt' . $table_index . '.' . $table_info['id'] . ' = ' . $key_table . '.' . $key;
						
						++$table_index;
						
					}  // Next table.

				}  // Next key.

			}  // End if foreign keys.
			
			// Compile the query.
			$query = 'SELECT ' . $select_string . ' FROM ' . $table_join . $conditional_string;
			
			// Any modifiers set?
			if ( self::$modifiers && $options['modifiers_on'] !== false )
			{
				
				// Order by?
				if ( self::$modifiers['order_by'] )
				{
					
					// Loop through each order by.
					foreach ( self::$modifiers['order_by'] as $order_options )
					{
						$ordering .= ($ordering) ? ', ' : '';
						$ordering .= $order_options[0] . ' ' . $order_options[1];
					}
						
					$query .= ' ORDER BY ' . $ordering;
						
				}
					
				// Limit?
				if ( self::$modifiers['limit'] )
					$query .= (self::$modifiers['limit']['offset'] !== false) ? ' LIMIT ' . self::$modifiers['limit']['offset'] . ', ' . self::$modifiers['limit']['count'] : ' LIMIT ' . self::$modifiers['limit']['count'];
			
			}  // End if modifiers set.
			
			// Get a database handle.
			$dbh = LW_DB::get( $resource_info['db'] );
			
			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $query_values );
			
			// Prepare to loop through each input element.
			$return_data      = array();
			$data             = array();
			$query_data       = $stmt->fetch_array();
			
			// We set $data to point to the return data array.
			// If we are populating a sub-resource, we will change this to point to
			// a different area later on.
			$data = &$return_data;

			// Loop through each input element.
			while ( $query_data != NULL )
			{
				
				$resource_index = 0;
				
				// Loop through each table.
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{
					
					$resource_index = $query_data[$table_info['id']];

					// Loop through each table field.
					foreach ( $table_info['fields'] as $field )
					{
						
						if ( !$query_data[$field] )
							continue;
						
						// Unserialize if this is a serialized field.
						if ( $resource_info['serialized'][$field] )
							$data[$resource_index][$field] = unserialize( $query_data[$field] );
						else
							$data[$resource_index][$field] = $query_data[$field];

					}  // Next table field.
					
				}  // Next table.
				
				// Any foreign keys we have to populate?
				if ( $resource_info['foreign_keys'] )
				{
					
					// Loop through each key.
					$key_index = 0;
					$key_id = 0;
					foreach ( $resource_info['foreign_keys'] as $key => $reference )
					{
						
						$data[$resource_index][$key] = array();
						
						// Loop through each table for the referenced resource.
						foreach ( self::$resource_info[$reference]['tables'] as $table_name => $table_info )
						{
							
							// Loop through each field.
							foreach ( $table_info['fields'] as $field )
							{
								
								// Skip over if this is a deferred field.
								if ( self::$resource_info[$reference]['deferred'][$field] )
									continue;
									
								++$key_index;
								
								// Unserialize if this is a serialized field.
								if ( self::$resource_info[$reference]['serialized'][$field] )
									$data[$resource_index][$key][$field] = unserialize( $query_data['d' . $key_index] );
								else
								{
									$data[$resource_index][$key][$field] = $query_data['d' . $key_index];
								}
								
							}  // Next field.
							
							// Get the reference's ID.
							$key_id = $data[$resource_index][$key][$table_info['id']];
							
						}  // Next table.
						
						// Now let's create an actual resource from the key.
						$data[$resource_index][str_replace( '_id', '', $key )] = new $reference( $key_id );								
						self::$resource_data[$reference][$key_id] = $data[$resource_index][$key];
						self::$populated_info[$reference][$key_id]['LW_main'] = true;
						$data[$resource_index][$key] = $key_id;
						
					}  // Next key.
	
				}  // End if foreign keys.

				// Grab the query data.
				$query_data = $stmt->fetch_array();

			}  // Next data element.
			
			// Clear out the modifiers.
			if ( $options['modifiers_on'] !== false )
				self::clear_modifiers();
			
			// Return the data.
			return $return_data;
			
		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_get_sub_resources()
		//------------------------------------------------------------------------
		private static function static_get_sub_resources()
		{
			
			$args = func_get_args();
			
			// Either the resource name is passed in as parameter 1, or a resource.
			if ( count( $args ) == 2 )
			{
				$resource_name = (is_array( $args[0] )) ? get_class( reset( $args[0] ) ) : get_class( $args[0] );
				$input = $args[0];
				$sub_resource_name = $args[1];
			}
			else
			{
				$resource_name = $args[0];
				$input = $args[1];
				$sub_resource_name = $args[2];
			}
			
			// Get the return type: array or resource.
			$return_type = is_array( $input ) ? 2 : 1;
			
			// Make sure input is an array so we can loop through it.
			if ( !is_array( $input ) )
				$input = array( $input );
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$sub_resource_name] ) 
				new $sub_resource_name();
			
			// Get the input array.
			$output_array  = array();
			$id_array      = array();
			
			// Loop through each resource.
			foreach ( $input as $index => $resource )
			{
				
				// An integer ID passed in?
				if ( is_numeric( $resource ) )
				{
					$resource = new $resource_name( $resource );
					$input_array[$index] = $resource;
				}
				
				// Is this sub-resource being managed by this resource?
				if ( $resource->info['sub_resources'][$sub_resource_name] )
					$id_array[] = $resource->id;
				else
				{
					LW_Debug_Debugger::add_item( 'The resource, ' . $resource_name . ', doesn\'t manage the sub-resource, ' . $sub_resource_name );
					continue;
				}

			}  // Next resource.
			
			// Get the key that refers to the resource.
			$key = self::$resource_info[$resource_name]['sub_resources'][$sub_resource_name];
			
			// Get the resource's data.
			$data = self::fetch_data( $sub_resource_name, array( $key => $id_array ) );
			
			// Loop through the resource data.
			$i = 0;
			foreach ( $data as $sub_resource_id => $row )
			{
				
				// Create a new resource.
				$sub_resource = new $sub_resource_name( $sub_resource_id );
				$sub_resource->populated['LW_main'] = true;
				
				// Store the data for the resource.
				self::$resource_data[$sub_resource_name][$sub_resource_id] = array_merge( self::$resource_data[$sub_resource_name][$sub_resource_id], $row );
				
				// Store in the output array.
				$output_array[$sub_resource->get( $key )][$sub_resource_id] = $sub_resource;

			}  // Next row.
			
			// Return the output.
			if ( $return_type == 1 && count( $output_array ) != 0 )
				return reset( $output_array );
			else
				return $output_array;
			
		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_create()
		//------------------------------------------------------------------------
		private static function static_create( $resource_name, $creation_data = array(), $count = 1 )
		{

			$id = 0;
			$id_array = array();
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];
			
			// If the count is more than 1, restructure the creation data array.
			if ( $count > 1 && $creation_data[0] == '' )
			{
				$data = $creation_data;
				$creation_data = array();
				for ( $i = 0; $i < $count; $i++ )
					$creation_data[$i] = $data;
			}

			// If we are not inserting multiple rows, restructure the data array.
			if ( $creation_data[0] == '' )
				$creation_data = array( $creation_data );

			// Get a database handle.
			$dbh = LW_DB::get( $resource_info['db'] );
			
			// Loop through each table.
			$i = 0;
			foreach ( $resource_info['tables'] as $table_name => $table_info )
			{

				$query_values = array();

				// Start creating the query.
				$query = 'INSERT INTO ' . $table_name . ' VALUES ';

				// Loop through each row that we are inserting.
				$row_num   = 1;
				$row_count = count( $creation_data );

				foreach ( $creation_data as $row_info )
				{

					// Add to the ID array.
					if ( $i == 0 )
						$id_array[] = ($row_num - 1);
					
					$query .= '( ';

					// Loop through each table field.
					$n = 1;
					$field_count = count( $table_info['fields'] );

					foreach ( $table_info['fields'] as $field )
					{

						// Add to the query string.
						if ( $n == $field_count )
							$query .= '? ';
						else
							$query .= '?, ';

						// If we have a creation data value for this field,
						// add it to the query values array.
						if ( $n == 1 && $i != 0 )
						{
							$query_values[] = ($row_num - 1) + $id;
						}
						else if ( array_key_exists( $field, $row_info ) )
						{
							
							if ( $row_info[$field] == null )
								$row_info[$field] = '';

							// Serialize if this is a serialized field.
							if ( $resource_info['serialized'][$field] )
								$query_values[] = serialize( $row_info[$field] );
							else
							  $query_values[] = $row_info[$field];

						}
						else
						{
							$query_values[] = '';
						}

						// Increment.
						$n++;
						$field_num++;

					}  // Next field.

					// End the row.
					if ( $row_num != $row_count )
						$query .= '), ';
					else
						$query .= ')';

					// Increment.
					$row_num++;

				}  // Next row.
				
				// Prepare and execute the query.
				$stmt = $dbh->prepare( $query );
				$stmt->execute( $query_values );

				// If this is the first iteration, get the ID.
				if ( $i == 0 )
					$id = $stmt->insert_id();
				
				// Increment.
				$i++;

			}  // Next table.
			
			// If there are variables in the ID array, reformat.
			foreach ( $id_array as $index => $value )
				$id_array[$index] = $id + $value;

			// Return the new resource ID or IDs.
			if ( $row_count > 1 )
				return $id_array;
			else
			  	return $id;
			
		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_update()
		//------------------------------------------------------------------------
		private static function static_update()
		{
			
			$args = func_get_args();
			
			// Either the resource name is passed in as parameter 1, or a resource.
			if ( count( $args ) == 2 )
			{
				$resource_name = (is_array( $args[0] )) ? get_class( reset( $args[0] ) ) : get_class( $args[0] );
				$input = $args[0];
				$update_data = $args[1];
			}
			else
			{
				$resource_name = $args[0];
				$input = $args[1];
				$update_data = $args[2];
			}

			// Get the resource array.
			$resource_array = is_array( $input ) ? $input : array( $input );
			
			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];
			
			// Loop through the input IDs and convert them into resources.
			if ( is_numeric( reset( $resource_array ) ) )
			{
				foreach( $resource_array as $index => $resource )
				{
					$resource = new $resource_name( $resource );
					$resource_array[$index] = $resource;
				}
			}
			
			// Get a database handle.
			$dbh = LW_DB::get( $resource_info['db'] );

			// Loop through each table.
			$tables_array  = array();
			$set_array     = array();
			foreach ( $resource_info['tables'] as $table_name => $table_info )
			{

				// Loop through each table field.
				$i = 1;
				foreach ( $table_info['fields'] as $field )
				{

					// Only add if there is update data for this field.
					if ( array_key_exists( $field, $update_data ) )
					{

						// Add the table names.
						if ( $i == 1 )
							$tables_array[] = $table_name;

						// Add to the query string.
						$set_array[] = $table_name .'.'. $field .' = ?';

						// Store the value.
						if ( $resource_info['serialized'][$field] )
							$query_values[] = serialize( $update_data[$field] );
						else
							$query_values[] = $update_data[$field];
							
						$i++;

					}  // End if update data for this field.

				}  // Next table field.

			}  // Next table.

			// Loop through each resource.
			$i = 1;
			$where_string = '';
			foreach ( $resource_array as $resource )
			{

				// Add to the where string.
				if ( $i == 1 )
				  $where_string .= '(';
				else
				  $where_string .= ' OR (';

				// Loop through each table.
				$n = 1;
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{

					// Only add if there is a value in the table array for this table.
					if ( !in_array( $table_name, $tables_array ) )
						continue;

					// Add to the where string.
					if ( $n == 1 )
						$where_string .= $table_name .'.'. $table_info['id'] .' = ?';
					else
						$where_string .= ' AND '. $table_name .'.'. $table_info['id'] .' = ?';

					// Add to the query values.
					$query_values[] = $resource->data[$table_info['id']];

					$n++;

				}  // Next table.

				// Add to the where string.
				$where_string .= ')';
				
				// Update this resource's data.
				$resource->data = array_merge( $resource->data, $update_data );

				$i++;

			}  // Next resource.

			// Compile the query.
			$query = 'UPDATE '. implode( ', ', $tables_array ) .' SET '. implode( ', ', $set_array ) .' WHERE '. $where_string;
			
			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $query_values );
			
			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_delete()
		//------------------------------------------------------------------------
		private static function static_delete()
		{
			
			$args = func_get_args();
			
			// Either the resource name is passed in as parameter 1,
			// or a resource.
			if ( count( $args ) == 1 )
			{
				$resource_name = (is_array( $args[0] )) ? get_class( reset( $args[0] ) ) : get_class( $args[0] );
				$input = $args[0];
			}
			else
			{
				$resource_name = $args[0];
				$input = $args[1];
			}

			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] ) 
				new $resource_name();
				
			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Get a database handle.
			$dbh = LW_DB::get( $resource_info['db'] );

			// If we are not deleting multiple rows, restructure the ID array.
			if ( !is_array( $input ) )
				$input = array( $input );

			// Loop through each ID.
			$where_string   = '';
			$query_values   = array();
			$tables_string  = '';
			$i = 1;
			foreach ( $input as $index => $resource )
			{
				
				// An integer ID passed in?
				if ( is_numeric( $resource ) )
				{
					$resource = new $resource_name( $resource );
					$input[$index] = $resource;
				}

				if ( $i == 1 )
					$where_string .= '(';
				else
					$where_string .= ' OR (';

				// Loop through each table.
				$n = 1;
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{

					// Add the table names.
					if ( $i == 1 )
					{

						if ( $n == 1 )
							$tables_string .= $table_name;
						else
							$tables_string .= ', '. $table_name;

					}  // End if first ID.

					// Add to the where string.
					if ( $n == 1 )
						$where_string .= $table_name . '.' . $table_info['id'] . ' = ?';
					else
						$where_string .= ' AND ' . $table_name . '.' . $table_info['id'] . ' = ?';

					// Add the id to the query values.
					$query_values[] = $resource->id;

					$n++;

				}  // Next table.

				$where_string .= ')';
				
				// Clear out the resource data.
				$resource->invalidate();

				$i++;

			}  // Next ID.

			// Compile the query.
			$query .= 'DELETE FROM ' . $tables_string . ' USING ' . $tables_string . ' WHERE ' . $where_string;
			
			// Any modifiers set?
			if ( self::$modifiers )
			{
					
				// Limit?
				if ( self::$modifiers['limit'] )
					$query .= (self::$modifiers['limit']['offset'] !== false) ? ' LIMIT ' . self::$modifiers['limit']['offset'] . ', ' . self::$modifiers['limit']['count'] : ' LIMIT ' . self::$modifiers['limit']['count'];
			
			}  // End if modifiers set.
			
			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $query_values );

			// Success!
			return true;

		}
		
		
		//------------------------------------------------------------------------
		// Private Member Functions
		//------------------------------------------------------------------------ 
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: populate_resource_info()
		// This function is called if a particular resource's information has
		// never been stored in the manager yet.
		//------------------------------------------------------------------------
		private function populate_resource_info()
		{
			
			$resource_info = false;
			
			// Make sure we have a cache handler.
			if ( !self::$cache && LW_Settings::get( 'resource', 'cache_schemas' ) )
			{
				
				self::$cache = new LW_Cache( array( 'handler' => LW_Settings::get( 'resource', 'cache_handler' ), 
					                                'namespace' => 'LW_Resource',
										            'ttl' => LW_Settings::get( 'resource', 'cache_ttl' ) ) );
										            
			}  // End if no cache handler.
			
			if ( LW_Settings::get( 'resource', 'cache_schemas' ) )
			{
				
				// Get the cached resource info.				            
				$cached_info = self::$cache->get( $this->name );
				if ( $cached_info )
					$resource_info = unserialize( $cached_info );
										      
			}  // End if no cache handler.
							      
			if ( !$resource_info )
			{
				
				$resource_info = array( 'tables' => array() );
			
				// Let's get the filename of the schema.
				$reflect  = new ReflectionClass( $this->name );
				$filename = str_replace( '.class.php', '.schema.xml', $reflect->getFilename() );		
				
				// Attempt to load the XML.
				$xml = simplexml_load_file( $filename );
	
				// Error occured while loading?
				if ( !$xml )
				{
					LW_Debug_Debugger::add_item( 'An error occured while attempting to load the resource schema.' );
					return false;
				}
				
				// Get the database connector.
				$resource_info['db'] = (string)$xml->db;
				
				// Get the database handle.
				$dbh = LW_DB::get( $resource_info['db'] );
				
				// Loop through all the tables.
				foreach ( $xml->tables->table as $table )
				{
					
					$table_info = array();
					
					// Get the columns in the table.
					$query = 'SHOW COLUMNS FROM ' . (string)$table;				
					$columns = $dbh->prepare( $query )->execute()->fetch_all_array();
					
					// Loop through each column and store it.
					foreach ( $columns as $column )
					{
						
						// If this is the primary key for this table, store it.
						if ( $column['Key'] == 'PRI' )
							$table_info['id'] = $column['Field'];
						
						// Store the field.
						$table_info['fields'][] = $column['Field'];
						$resource_info['fields'][$column['Field']] = (string)$table;
						
					}
					
					// If one was stored in the XML, overwrite with that ID.
					if ( (string)$table['id'] != '' )
						$table_info['id'] = (string)$table['id'];
						
					// Store the table information.
					$resource_info['tables'][(string)$table] = $table_info;
					
				}  // Next table.
				
				// Store all the serialized fields.
				if ( $xml->serialized != '' )
					foreach ( $xml->serialized->field as $serialized_field )
						$resource_info['serialized'][(string)$serialized_field] = true;
					
				// Store all the deferred fields.
				if ( $xml->deferred != '' )
					foreach ( $xml->deferred->field as $deferred_field )
						$resource_info['deferred'][(string)$deferred_field] = true;
						
				// Store all the foreign keys.
				if ( $xml->foreign_keys != '' )
				{
					foreach ( $xml->foreign_keys->reference as $reference )
					{
									
						if ( $reference['key'] != '' )
							$key = (string)$reference['key'];
						else
							$key = strtolower( (string)$reference . '_id' );
						
						$resource_info['foreign_keys'][$key] = (string)$reference;
						
					}
				}
					
				// Store all the sub-resources.
				if ( $xml->sub_resources != '' )
				{
					foreach ( $xml->sub_resources->sub_resource as $sub_resource )
					{						
						if ( $sub_resource['key'] != '' )
							$key = (string)$sub_resource['key'];
						else
							$key = strtolower( $this->name . '_id' );
						
						$resource_info['sub_resources'][(string)$sub_resource] = $key;	
					}
				}
				
				// Store the retrieved information in the cache.
				if ( LW_Settings::get( 'resource', 'cache_schemas' ) )
					self::$cache->set( $this->name, serialize( $resource_info ) );
						
			}  // End if not cached.
				
			// Add this resource's information to be managed.
			$this->info = $resource_info;
			
		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: populate_resource_data()
		// Grabs the resource's data from the database and populates this
		// resource.
		//------------------------------------------------------------------------
		private function populate_resource_data( $property )
		{
			
			$fetch_options = array();
			
			// Are we trying to retrieve a deferred field?
			if ( $this->info['deferred'][$property] )
			{
				
				$fetch_options['deferred_field'] = $property;
				$fetch_options['fetch_all'] = !$this->populated['main'];
				$this->populated[$property] = true;
					
			}
			
			// Set not to use modifiers.
			$fetch_options['modifiers_on'] = false;
			
			// Get the resource data.
			$data = reset( $this->fetch_data( $this->name, $this->id, $fetch_options ) );
			$this->data = array_merge( $this->data, $data );
			
			// Set us as populated.
			$this->populated['LW_main'] = true;
			
		}
		
		
		//------------------------------------------------------------------------
		// (Exclude)
		// Function: invalidate()
		// Completely cleans out this resource.
		//------------------------------------------------------------------------
		private function invalidate()
		{
			
			$this->data = array();
			$this->populated = array();
			
			unset( $this->name );
			unset( $this->id );
			unset( $this->data );
			unset( $this->info );
			unset( $this->populated );
			
		}
	
	}
	
	
	//----------------------------------------------------------------------------
	// Configuration Settings
	//----------------------------------------------------------------------------
	$data['cache_schemas']  = true;
	$data['cache_handler']  = 'file';
	$data['cache_ttl']      = 0;
	
	LW_Settings::set_default( 'resource', $data );

?>
