<?php

	//----------------------------------------------------------------------------
	// Class: Legato_Resource
	// The base class used for extending a new resource class.
	//----------------------------------------------------------------------------
	class Legato_Resource
	{

		private static $resource_info        = array( 'Legato_Resource' => true );
		private static $resource_data        = array( 'Legato_Resource' => array( true ) );
		private static $populated_info       = array();
		private static $modifiers            = array();
		private static $resource             = null;
		private static $cache                = null;
		private static $query_cache          = array();

		private $name = '';
		private $id = 0;
		private $info = NULL;
		private $data = NULL;
		private $populated = NULL;

		// For sub-resources.
		private $_sub_resource_info = false;


		//------------------------------------------------------------------------
		// Events
		//------------------------------------------------------------------------
		/*
		protected static function post_fetch( &$data ){}
		protected static function pre_create( &$data ){}
		protected static function post_create( $ids ){}
		protected static function pre_update( &$data ){}
		*/

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

			// If a conditional was passed in, fetch the data.
			if ( is_array( $input ) )
			{

				// Get the data and populate this resource.
				$data = reset( self::static_fetch( $this->name, $input ) );
				$resource = reset( self::populate( $this->name, array( $data ) ) );
				$input = $resource->id;


			}  // End if array passed in.
			else
			{
				$this->populated['Legato_main'] = false;
			}  // End if ID.

			// Set up the resource's properties.
			$this->id = $input;
			$this->data = &self::$resource_data[$this->name][$this->id];
			$this->populated = &self::$populated_info[$this->name][$this->id];

			// Already stored?
			if ( !$this->data && $input != 0 )
			{

				// Loop through each table and store the ID.
				foreach ( $this->info['tables'] as $table_info )
					$this->data[$table_info['id']] = $this->id;

			}

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: __get()
		// Used for returning either the resource's data or sub-resource.
		//------------------------------------------------------------------------
		public function __get( $arg )
		{

			if ( $this->info['sub_resources'][$arg] )
			{

				$sub_resource = new $arg();

				$sub_resource->_sub_resource_info = array();
				$sub_resource->_sub_resource_info['key'] = $this->info['sub_resources'][$arg];
				$sub_resource->_sub_resource_info['id'] = $this->id;
				$sub_resource->_sub_resource_info['lookup'] = $this->info['many_to_many'][$arg] ? $this->info['many_to_many'][$arg] : false;

				return $sub_resource;

			}
			else
			{

				return $this->get( $arg );

			}

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: __set()
		// Used for setting the data of the resource.
		//------------------------------------------------------------------------
		public function __set( $property, $value )
		{

			$this->set( $property, $value );

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
				if ( !$this->populated['Legato_main'] )
					$this->populate_resource_data( $property );

				// Return this resource's data.
				return $this->data;

			}
			else
			{

				// Populate if resource has not been populated.
				if ( (!$this->data[$property] && !$this->populated['Legato_main']) ||  // Empty data field and not populated.
					 ($this->info['deferred'][$property] && !$this->populated[$property]) ||  // Deferred property and the property isn't populated.
					 ($this->info['foreign_keys'][$property . '_id'] && !$this->populated[$property]) )  // Foreign key that isn't populated.
				{

					$this->populate_resource_data( $property );

				}

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
		//     Legato_Resource::get_sub_resources( $users, 'User_Comment' );
		//
		//     // The same thing as above can be accomplished on one line.
		//     Legato_Resource::get_sub_resources( 'User', array( 1, 2 ), 'User_Comment' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function get_sub_resources()
		{

			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			if ( $isStatic )
				return call_user_func_array( array( 'Legato_Resource', 'static_get_sub_resources' ), $args );
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
		//     Legato_Resource::create( 'User', array( 'first_name' => 'David', 'last_name' => 'DeCarmine' ) );
		//
		//     // And if we want to create 10 copies.
		//     Legato_Resource::create( 'User', array( 'first_name' => 'Copy', 'last_name' => 'Cat' ), 10 );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function create()
		{

			$extra_info = array();
			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			if ( $isStatic || ($this && $this->_sub_resource_info) )
			{

				// Get the default data for a static call.
				$resource_name = $args[0];
				$data = $args[1];
				$count = $args[2];

				// If this is being called from a sub-resource, pass on the information.
				if ( $this && $this->_sub_resource_info )
				{
					$resource_name = $this->name;
					$data = $args[0];
					$count = $args[1];
					$extra_info['sub_resource_info'] = $this->_sub_resource_info;
				}

				return self::static_create( $resource_name, $data, $count, $extra_info );

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
		//     Legato_Resource::update( 'User', 1, array( 'first_name' => 'Dan' ) );
		//
		//     // Updating multiple resources.
		//     $users[] = new User( 1 );
		//     $users[] = new User( 2 );
		//     Legato_Resource::update( $users, array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     Legato_Resource::update( 'User', array( 1, 2 ), array( 'first_name' => 'Dan' ) );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function update()
		{

			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			$extra_info = array();

			// If this is being called from a sub-resource, pass on the information.
			if ( $this && $this->_sub_resource_info )
			{
				$extra_info['sub_resource_info'] = $this->_sub_resource_info;
				$input = $args[0];        // We go through the hassle of assigning to $input first so we don't mess up the indexes.
				$update_data = $args[1];  // Don't mess with this ordering unless you know what you're doing.
				$args[0] = $this->name;   // Got that?!
				$args[1] = $input;
				$args[2] = $update_data;
				$isStatic = true;
			}

			$args[] = $extra_info;

			// The second part  of the conditional is to check if they had set some modifiers before calling this function statically.
			if ( $isStatic || ($this && $this->name == 'Legato_Resource') )
				return call_user_func_array( array( 'Legato_Resource', 'static_update' ), $args );
			else
				return Legato_Resource::static_update( $this, $args[0] );

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
		//     Legato_Resource::update( 'User', 1, array( 'first_name' => 'Dan' ) );
		//
		//     // Updating multiple resources.
		//     $users[] = new User( 1 );
		//     $users[] = new User( 2 );
		//     Legato_Resource::update( $users, array( 'first_name' => 'Dan' ) );
		//
		//     // The same can be accomplished in one line.
		//     Legato_Resource::update( 'User', array( 1, 2 ), array( 'first_name' => 'Dan' ) );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function delete()
		{

			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			$extra_info = array();

			// If this is being called from a sub-resource, pass on the information.
			if ( $this && $this->_sub_resource_info )
			{
				$extra_info['sub_resource_info'] = $this->_sub_resource_info;
				$input = $args[0];       // We go through the hassle of assigning to $input first so we don't mess up the indexes.
				$args[0] = $this->name;  // Don't mess with this ordering unless you know what you're doing.
				$args[1] = $input;       // Got that?!
				$isStatic = true;
			}

			$args[] = $extra_info;

			// The second part  of the conditional is to check if they had set some modifiers before calling this function statically.
			if ( $isStatic || ($this && $this->name == 'Legato_Resource') )
				return call_user_func_array( array( 'Legato_Resource', 'static_delete' ), $args );
			else
				return self::static_delete( $this, $extra_info );

		}


		//------------------------------------------------------------------------
		// Function: attach()
		// Attaches a sub-resource to a resource.
		//------------------------------------------------------------------------
		public function attach( $input )
		{

			if ( $this && !$this->_sub_resource_info )
			{
				Legato_Debug_Debugger::add_item( 'attach() can only be called on a sub-resource.' );
				return false;
			}

			// If we are not attaching multiple sub-resources, restructure the ID array.
			if ( !is_array( $input ) )
				$input = array( $input );

			// Lookup attach or a normal sub-resource attach.
			if ( $this && !$this->_sub_resource_info['lookup'] )
			{

				// Loop through the input IDs and convert them into resources.
				if ( is_numeric( reset( $input ) ) )
				{
					foreach( $input as $index => $resource )
					{
						$resource = new $this->name( $resource );
						$input[$index] = $resource;
					}
				}

				// Do the update.
				Legato_Resource::static_update( $input, array( $this->_sub_resource_info['key'] => $this->_sub_resource_info['id'] ) );


			}  // End if normal sub-resource.
			else
			{

				$lookup = $this->_sub_resource_info['lookup'];
				$query_values = array();

				// Get a database handle.
				$dbh = Legato_DB::get( $this->info['db'] );

				// Start creating the query.
				$query = 'INSERT INTO ' . $lookup['name'] . ' (' . $lookup['primary_keys'][0] . ', ' . $lookup['primary_keys'][1] . ') VALUES ';

				// Loop through each input item.
				$query_entries = array();
				foreach ( $input as $resource )
				{

					// Get the ID.
					if ( is_numeric( $resource ) )
						$id = $resource;
					else
						$id = $resource->id;

					// Add the row and data.
					$query_entries[] = '( ?, ? )';
					$query_values[] = $this->_sub_resource_info['id'];
					$query_values[] = $id;

				}  // Next row.

				// Implode the query entries.
				$query .= implode( ', ', $query_entries );

				// Prepare and execute the query.
				$stmt = $dbh->prepare( $query );
				$stmt->execute( $query_values );

			}  // End if many-to-many.

			// Success.
			return true;

		}


		//------------------------------------------------------------------------
		// Function: detach()
		// Detaches a sub-resource from a resource.
		//------------------------------------------------------------------------
		public function detach( $input = '' )
		{

			if ( $this && !$this->_sub_resource_info )
			{
				Legato_Debug_Debugger::add_item( 'detach() can only be called on a sub-resource.' );
				return false;
			}

			// If we are not detaching multiple sub-resources, restructure the ID array.
			if ( $input != '' && !is_array( $input ) )
				$input = array( $input );

			// Lookup detach or a normal sub-resource detach.
			if ( $this && !$this->_sub_resource_info['lookup'] )
			{

				// Was any input passed in?
				if ( $input )
				{

					// Loop through the input IDs and convert them into resources.
					if ( is_numeric( reset( $input ) ) )
					{
						foreach( $input as $index => $resource )
						{
							$resource = new $this->name( $resource );
							$input[$index] = $resource;
						}
					}

					// Do the update.
					Legato_Resource::static_update( $input, array( $this->_sub_resource_info['key'] => 0 ) );

				}  // End if IDs passed in.
				else
				{

					// Set the conditional to detach all sub-resources attached to this resource.
					$input = array(	$this->_sub_resource_info['key'] => $this->_sub_resource_info['id']	);

					// Do the update.
					Legato_Resource::static_update( $this->name, $input, array( $this->_sub_resource_info['key'] => 0 ) );

				}  // End if we should detach all sub-resources.


			}  // End if normal sub-resource.
			else
			{

				$lookup = $this->_sub_resource_info['lookup'];
				$query_values = array();

				// Get a database handle.
				$dbh = Legato_DB::get( $this->info['db'] );

				// Are we detaching all sub-resources from this resource, or just
				// the ones passed in?
				if ( !$input )
				{

					// Add the conditional and query values for detaching all.
					$where_string = $lookup['name'] . '.' . $lookup['primary_keys'][0] . ' = ?';
					$query_values[] = $this->_sub_resource_info['id'];

				}  // End if detaching all sub-resources.
				else
				{

					$query_entries = array();

					// Loop through each input item.
					$i = 1;
					foreach ( $input as $index => $resource )
					{

						// Get the ID.
						if ( is_numeric( $resource ) )
							$id = $resource;
						else
							$id = $resource->id;

						// Add to the where string.
						$query_entries[] = '(' . $lookup['name'] . '.' . $lookup['primary_keys'][0] . ' = ? AND ' . $lookup['name'] . '.' . $lookup['primary_keys'][1] . ' = ?' . ')';

						// Add the query values (IDs) that go with the conditional above.
						$query_values[] = $this->_sub_resource_info['id'];
						$query_values[] = $id;

						$i++;

					}  // Next ID.

					// Implode the query entries.
					$where_string = implode( ' OR ', $query_entries );

				}  // End if detaching passed in sub-resources.

				// Compile the query.
				$query = 'DELETE FROM ' . $lookup['name'] . ' USING ' . $lookup['name'] . ' WHERE ' . $where_string;

				// Prepare and execute the query.
				$stmt = $dbh->prepare( $query );
				$stmt->execute( $query_values );

			}  // End if many-to-many.

			// Success.
			return true;

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
		public function fetch()
		{

			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			$return_raw = self::$modifiers['raw_data'];
			$extra_info = array();
			$resource_data = array();

			// If this is being called from a sub-resource, pass on the information.
			if ( $this && $this->_sub_resource_info )
				$extra_info['sub_resource_info'] = $this->_sub_resource_info;

			// Get the resource data.
			// The second part  of the conditional is to check if they had set some modifiers before calling this function statically.
			if ( $isStatic || ($this && $this->name == 'Legato_Resource') )
			{
				$resource_name = $args[0];
				$resource_data = self::static_fetch( $resource_name, $args[1], $extra_info );
			}
			else
			{
				$resource_name = $this->name;
				$resource_data = self::static_fetch( $resource_name, $args[0], $extra_info );
			}

			// Populate all the resources fetched.
			// Notice that we pass in the raw data modifier value.
			$resource_data = self::populate( $resource_name, $resource_data, $return_raw );

			// Return the data.
			return $resource_data;

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
		//     Legato_Resource::order_by( 'first_name', 'desc' )::fetch( 'User' );
		//
		//     // Ordering by more than one field.
		//     Legato_Resource::order_by( array( 'first_name', 'desc' ), array( 'last_name' ) )::fetch( 'User' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function order_by()
		{

			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new Legato_Resource();

			// Carry over the sub resources info, or cancel it out.
			if ( $this && $this->_sub_resource_info )
			{
				self::$resource->name = $this->name;
				self::$resource->_sub_resource_info = $this->_sub_resource_info;
			}
			else
			{
				self::$resource->name = 'Legato_Resource';
				self::$resource->_sub_resource_info = NULL;
			}

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
		//     $users = Legato_Resource::limit( 10 )->fetch( 'User' );
		//
		//     // Getting 10 users after an offset of 5.
		//     $users = Legato_Resource::limit( 5, 10 )-> fetch( 'User' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function limit( $offset, $count = false )
		{

			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new Legato_Resource();

			// Carry over the sub resources info, or cancel it out.
			if ( $this && $this->_sub_resource_info )
			{
				self::$resource->name = $this->name;
				self::$resource->_sub_resource_info = $this->_sub_resource_info;
			}
			else
			{
				self::$resource->name = 'Legato_Resource';
				self::$resource->_sub_resource_info = NULL;
			}

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
		// Function: fields()
		// Sets a "fields" modifier.
		// The next query will only return the fields requested.
		//
		// Parameters:
		//     $field - The field to get.
		//     ...    - Pass in as many fields as you'd like to get.
		//
		// Examples:
		//     (begin code)
		//
		//     // When getting the Users, only get the username field.
		//     $users = Legato_Resource::fields( 'username' )->fetch( 'User' );
		//
		//     (end)
		//------------------------------------------------------------------------
		public function fields()
		{

			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new Legato_Resource();

			// Carry over the sub resources info, or cancel it out.
			if ( $this && $this->_sub_resource_info )
			{
				self::$resource->name = $this->name;
				self::$resource->_sub_resource_info = $this->_sub_resource_info;
			}
			else
			{
				self::$resource->name = 'Legato_Resource';
				self::$resource->_sub_resource_info = NULL;
			}

			$args = func_get_args();

			// Store the fields.
			self::$modifiers['fields'] = $args;

			// Return the fake resource.
			return self::$resource;

		}


		/*
			Function: raw_data()
			Sets up the next query to return any fetched data as an array instead of a resource object.

			Syntax:
				object raw_data( bool $return_raw = true )

			Parameters:
				bool $return_raw - *optional* - Whether or not to return the data as an array. Defaults to true.

			Returns:
				A resource object so that you can link this method call.

			Examples:
			(begin code)
				// Return an array of users instead of objects.
				$users = Legato_Resource::raw_data()->fetch( 'User' );
			(end)
		*/
		public function raw_data( $return_raw = true )
		{

			// To use the modifiers, we have to create a fake resource
			// so that we can return it and use it to link methods.
			if ( !self::$resource )
				self::$resource = new Legato_Resource();

			// Carry over the sub resources info, or cancel it out.
			if ( $this && $this->_sub_resource_info )
			{
				self::$resource->name = $this->name;
				self::$resource->_sub_resource_info = $this->_sub_resource_info;
			}
			else
			{
				self::$resource->name = 'Legato_Resource';
				self::$resource->_sub_resource_info = NULL;
			}

			// Store the raw data modifier.
			self::$modifiers['raw_data'] = $return_raw;

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
				self::$resource = new Legato_Resource();

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
		//     $raw_data - Whether or not to return raw data, or an array of resource objects.
		//------------------------------------------------------------------------
		public static function populate( $resource_name, $data, $raw_data = false )
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
				if ( !self::$populated_info[$resource_name][$resource_id]['Legato_main'] || !is_array( self::$resource_data[$resource_name][$resource_id] ) )
				{
					self::$resource_data[$resource_name][$resource_id] = $resource_data;
				}
				else if ( is_array( $resource_data ) )
				{
					self::$resource_data[$resource_name][$resource_id] = array_merge( self::$resource_data[$resource_name][$resource_id], $resource_data );
				}

				// Call the post_fetch() event.
				if ( method_exists( $resource_name, 'post_fetch' ) )
					call_user_func_array( array( $resource_name, 'post_fetch' ), array( &self::$resource_data[$resource_name][$resource_id] ) );

				// Should we return just an array, or should we return resource objects?
				if ( !$raw_data )
					$return_data[$resource_id] = new $resource_name( $resource_id );
				else
					$return_data[$resource_id] = self::$resource_data[$resource_name][$resource_id];

				// Set as populated.
				self::$populated_info[$resource_name][$resource_id]['Legato_main'] = true;

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
		public function count()
		{

			$args = func_get_args();
			$isStatic = !isset($this) || !is_a( $this, __CLASS__ );

			$query_values = array();
			$extra_info = array();

			// If this is being called from a sub-resource, pass on the information.
			if ( $this && $this->_sub_resource_info )
				$extra_info['sub_resource_info'] = $this->_sub_resource_info;

			// The second part  of the conditional is to check if they had set some modifiers before calling this function statically.
			if ( $isStatic || ($this && $this->name == 'Legato_Resource') )
				return self::static_count( $args[0], $args[1], $extra_info );
			else
				return self::static_count( $this->name, $args[0], $extra_info );

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: get_debug_info()
		// Returns the debugging information for resources.
		//------------------------------------------------------------------------
		public static function get_debug_info()
		{

			return self::$resource_data;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: get_resource_debug_info()
		// Returns the debugging information for the resource this was called on.
		//------------------------------------------------------------------------
		public function get_resource_debug_info()
		{

			return array( $this->name, $this->id );

		}


		//------------------------------------------------------------------------
		// Private Static Member Functions
		//------------------------------------------------------------------------

		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_fetch()
		// Retrieves the required information from the database to be used to
		// populate resources.
		//------------------------------------------------------------------------
		private static function static_fetch( $resource_name, $input, $options = array() )
		{

			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] )
				new $resource_name();

			// Check if the class exists.
			if ( !class_exists( $resource_name ) )
				return Legato_Debug_Debugger::add_item( 'The resource, ' . $resource_name . ', does not exist. Make sure you typed it in correctly.' );

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// If this is a many-to-many relationship, set up the
			// first table as the lookup table.
			if ( $options['sub_resource_info'] && $options['sub_resource_info']['lookup'] )
			{

				$lookup = $options['sub_resource_info']['lookup'];

				// We set up a fake table in the resource info so that it will loop through and
				// add all the look up table information.
				// We do the array merge so that the lookup table is the first table to be passed through
				// and will act as the main table of the resource.
				$lookup_table_info['id'] = $lookup['id'];
				$lookup_table_info['fields'] = $lookup['fetch_fields'];
				$lookup_table_info['lookup'] = true;
				$resource_info['tables'] = array_merge( array( $lookup['name'] => $lookup_table_info ), $resource_info['tables'] );

				// We do this so that below when it's constructing the WHERE clause, it will
				// find the correct table name for the key.
				$resource_info['fields'][$options['sub_resource_info']['key']] = $lookup['name'];

			}

			// If we are doing a normal fetch.
			if ( !$options['deferred_field'] || $options['fetch_all'] )
			{

				$fields = NULL;

				// Any fields modifier set?
				if ( self::$modifiers && $options['modifiers_on'] !== false && self::$modifiers['fields'] )
				{
					$fields = self::$modifiers['fields'];

					// Make sure the ID field is in the fields.
					$first_table = reset( $resource_info['tables'] );
					if ( !in_array( $first_table['id'], $fields ) )
						$fields[] = $first_table['id'];
				}

				// Generate the pieces of the query.
				$table_join = self::_generate_tables_inner( $resource_info['tables'] );
				$select_string = self::_generate_fields_select( $resource_name, $fields, array( 'deferred' => $options['deferred_field'] ) );

			}
			else  // If getting a deferred key.
			{

				// Generate the pieces of the query.
				$table_join = self::_generate_tables( array( $resource_info['fields'][$options['deferred_field']] ) );
				$select_string = self::_generate_fields_select( $resource_name, array( $options['deferred_field'] ) );

				// We do this so that we can have the logic below process it
				// like the user passed in a key and value. This is done so that
				// a huge if statement doesn't have to be placed around everything.
				$input = array( $resource_info['tables'][$resource_info['fields'][$options['deferred_field']]]['id'] => $input );

			}

			// Any foreign keys we have to add?
			$key_mapping = array();
			if ( $resource_info['foreign_keys'] && !($options['deferred_field'] && !$options['fetch_all']) )
			{

				$table_join .= self::_generate_foreign_tables( $resource_info, $key_mapping );
				$select_string .= self::_generate_foreign_select( $key_mapping );

			}  // End if foreign keys.

			// Set up the conditional.
			$query_values = array();
			$conditional_string .= self::_generate_conditional
			(
				array
				(
					'resource_name' => $resource_name,
					'resource_info' => $resource_info,
					'input' => $input,
					'sub_resource_info' => $options['sub_resource_info'],
					'foreign_key_mapping' => $key_mapping
				),
				$query_values
			);

			// Add to the conditional.
			if ( $conditional_string ) $conditional_string = ' WHERE ' . $conditional_string;

			// Compile the query.
			$query = 'SELECT ' . $select_string . ' FROM ' . $table_join . $conditional_string;

			// Any modifiers set?
			if ( self::$modifiers && $options['modifiers_on'] !== false )
			{

				// Order by?
				if ( self::$modifiers['order_by'] )
				{

					$ordering = array();

					// Loop through each order by.
					foreach ( self::$modifiers['order_by'] as $order_options )
						$ordering[] = $order_options[0] . ' ' . $order_options[1];

					$query .= ' ORDER BY ' . implode( ', ', $ordering );

				}

				// Limit?
				if ( self::$modifiers['limit'] )
					$query .= (self::$modifiers['limit']['offset'] !== false) ? ' LIMIT ' . self::$modifiers['limit']['offset'] . ', ' . self::$modifiers['limit']['count'] : ' LIMIT ' . self::$modifiers['limit']['count'];

			}  // End if modifiers set.

			// Now that we have the query, let's see if it's stored in the query cache.
			$query_cache_found = true;
			if ( Legato_Settings::get( 'resource', 'enable_internal_query_cache' ) )
			{
				$query_cache_key = self::_get_query_cache_key( $query, $query_values );
				$return_data = self::_check_query_cache( $query_cache_key );
			}

			if ( !$return_data && !is_array( $return_data ) )
			{

				$query_cache_found = false;

				// Get a database handle.
				$dbh = Legato_DB::get( $resource_info['db'] );

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

			}  // End if no data in the query cache.
			else
				$query_data = null;

			// Loop through each input element.
			while ( $query_data != NULL )
			{

				$resource_index = 0;

				// Loop through each table.
				foreach ( $resource_info['tables'] as $table_name => $table_info )
				{

					// If this is a lookup table that was added up above, skip it.
					if ( $table_info['lookup'] )
						continue;

					// Get the resource's ID.
					if ( $resource_index == 0 )
						$resource_index = $query_data[$table_info['id']];

					// Store the query data.
					$data[$resource_index] = $query_data;

					// Unserialize fields.
					if ( $resource_info['serialized'] )
						foreach ( $resource_info['serialized'] as $field => $value )
							$data[$resource_index][$field] = unserialize( $data[$resource_index][$field] );

				}  // Next table.

				// Any foreign keys we have to populate?
				if ( $resource_info['foreign_keys'] )
				{

					// Loop through each key.
					foreach ( $key_mapping as $key => $key_info )
					{

						$foreign_key_data = array();

						// Make sure that there is foreign key data.
						if ( $data[$resource_index][$key] )
						{

							// Loop through each field of this resource.
							foreach ( $key_info['fields'] as $field => $field_index )
							{

								// Unserialize if this is a serialized field.
								if ( self::$resource_info[$key_info['reference']]['serialized'][$field] )
									$foreign_key_data[$field] = unserialize( $query_data[$field_index] );
								else
									$foreign_key_data[$field] = $query_data[$field_index];

							}  // Next field.

							// Now let's create an actual resource from the key.
							$property_key = str_replace( '_id', '', $key );

							// Notice that we pass in the raw data modifier value to populate().
							$populated_reference = self::populate( $key_info['reference'], array( $foreign_key_data ), self::$modifiers['raw_data'] );
							$data[$resource_index][$property_key] = reset( $populated_reference );
							self::$populated_info[$resource_name][$resource_index][$property_key] = true;

						}
						else
						{

							// If no data, just put as NULL.
							$property_key = str_replace( '_id', '', $key );
							$data[$resource_index][$property_key] = NULL;
							self::$populated_info[$resource_name][$resource_index][$property_key] = true;

						}

					}  // Next key.

				}  // End if foreign keys.

				// Grab the query data.
				$query_data = $stmt->fetch_array();

			}  // Next data element.

			// Store in the query cache.
			if ( !$query_cache_found )
				self::_store_query_cache( $query_cache_key, $return_data );

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
					Legato_Debug_Debugger::add_item( 'The resource, ' . $resource_name . ', doesn\'t manage the sub-resource, ' . $sub_resource_name );
					continue;
				}

			}  // Next resource.

			// Get the key that refers to the resource.
			$key = self::$resource_info[$resource_name]['sub_resources'][$sub_resource_name];

			// Get the resource's data.
			$data = self::static_fetch( $sub_resource_name, array( $key => $id_array ) );

			// Loop through the resource data.
			$i = 0;
			foreach ( $data as $sub_resource_id => $row )
			{

				// Create a new resource.
				$sub_resource = new $sub_resource_name( $sub_resource_id );
				$sub_resource->populated['Legato_main'] = true;

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
		private static function static_create( $resource_name, $creation_data = array(), $count = 1, $extra_info = array() )
		{

			if ( !self::$resource_info[$resource_name] )
				new $resource_name();

			// Make sure it's in an array.
			if ( $creation_data[0] == '' )
				$creation_data = array( $creation_data );

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Get a database handle.
			$dbh = Legato_DB::get( $resource_info['db'] );

			// If a sub-resource, add it to the data entry.
			if ( $extra_info['sub_resource_info'] && !$extra_info['sub_resource_info']['lookup'] )
			{

				// Add the key and value to the data for the sub-resource.
				foreach ( $creation_data as &$data )
					$data[$extra_info['sub_resource_info']['key']] = $extra_info['sub_resource_info']['id'];

				unset( $data );  // Since we created a reference in the foreach.

			}  // End if sub-resource.
			else if ( $extra_info['sub_resource_info'] && $extra_info['sub_resource_info']['lookup'] )
			{

				$lookup = $extra_info['sub_resource_info']['lookup'];

				// Add the lookup table into this resource's info.
				$lookup_table_info['id'] = $lookup['id'];
				$lookup_table_info['fields'] = $lookup['fields'];
				$lookup_table_info['lookup'] = true;
				$resource_info['tables'] = array_merge( $resource_info['tables'], array( $lookup['name'] => $lookup_table_info ) );

				// We do this so that below when it's constructing the WHERE clause, it will
				// find the correct table name for the key.
				$resource_info['fields'][$extra_info['sub_resource_info']['key']] = $lookup['name'];

				// Add the key and value to the data for the lookup table.
				foreach ( $creation_data as &$data )
					$data[$extra_info['sub_resource_info']['key']] = $extra_info['sub_resource_info']['id'];

				unset( $data );  // Since we created a reference in the foreach.

			}  // End if many-to-many sub-resource.

			// Call the pre_create() event.
			if ( method_exists( $resource_name, 'pre_create' ) )
				foreach( $creation_data as &$data )
					call_user_func_array( array( $resource_name, 'pre_create' ), array( &$data ) );

			unset( $data );  // Since we created a reference in the foreach.

			// Loop through each table.
			$i = 0;
			$id = 0;
			$id_array = array();
			foreach ( $resource_info['tables'] as $table_name => $table_info )
			{

				$query_values = array();

				// If there is an ID already, add it to all the rows.
				if ( $id && $i == 1 )
					foreach ( $creation_data as &$data )
						$data[$table_info['id']] = $id;

				unset( $data );  // Since we created a reference in the foreach.

				$data_entry = self::_generate_data_entry( $resource_name, $table_info['fields'], $creation_data, $query_values, $count );

				// Start creating the query.
				$query = 'INSERT INTO ' . $table_name . ' VALUES ' . $data_entry;

				// Prepare and execute the query.
				$stmt = $dbh->prepare( $query );
				$stmt->execute( $query_values );

				// If this is the first iteration, get the ID.
				if ( $i == 0 )
					$id = $stmt->insert_id();

				// Increment.
				++$i;

			}  // Next table.

			// Loop through each row that we are inserting.
			$entry_count = ($count > 1) ? $count : count( $creation_data );

			// Return the new resource ID or array of IDs.
			if ( $entry_count > 1 )
			{
				// Get the ID array.
				for ( $i = 0; $i < $entry_count; $i++ )
					$id_array[] = $id + $i;

				$return_data = $id_array;

			}
			else
				$return_data = $id;

			// Call the post_create() event.
			if ( method_exists( $resource_name, 'post_create' ) )
				call_user_func_array( array( $resource_name, 'post_create' ), array( (is_array( $return_data ) ? $return_data : array( $return_data )) ) );

			// Return the new resource ID or array of IDs.
			return $return_data;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_update()
		//------------------------------------------------------------------------
		private static function static_update()
		{

			$args = func_get_args();

			// Get the correct input.
			if ( is_string( $args[0] ) )
			{
				$resource_name = $args[0];
				$input = ($args[1] && !is_array( $args[1] )) ? array( $args[1] ) : $args[1];
				$update_data = $args[2];
				$extra_info = $args[3];
			}
			else
			{
				$input = is_array( $args[0] ) ? $args[0] : array( $args[0] );
				$resource_name = get_class( reset( $input ) );
				$update_data = $args[1];
				$extra_info = $args[2];
			}

			if ( !self::$resource_info[$resource_name] )
				new $resource_name();

			// Loop through all the input and make sure it's a resource.
			if ( $type == 1 )
			{
				foreach ( $input as $index => $resource )
				{
					if ( is_numeric( $resource ) )
					{
						$resource = new $resource_name( $resource );
						$input[$index] = $resource;
					}
				}
			}

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Many-to-many sub-resource?
			if ( $extra_info['sub_resource_info'] && $extra_info['sub_resource_info']['lookup'] )
			{

				$lookup = $extra_info['sub_resource_info']['lookup'];

				// Add the lookup table into this resource's info.
				$lookup_table_info['id'] = $lookup['id'];
				$lookup_table_info['fields'] = $lookup['fetch_fields'];
				$lookup_table_info['lookup'] = true;
				$resource_info['tables'] = array_merge( array( $lookup['name'] => $lookup_table_info ), $resource_info['tables'] );

				// We do this so that below when it's constructing the WHERE clause, it will
				// find the correct table name for the key.
				$resource_info['fields'][$extra_info['sub_resource_info']['key']] = $lookup['name'];

			}

			// Get a database handle.
			$dbh = Legato_DB::get( $resource_info['db'] );

			// Call the pre_update() event.
			if ( method_exists( $resource_name, 'pre_update' ) )
				call_user_func_array( array( $resource_name, 'pre_update' ), array( &$update_data ) );

			// Generate the query parts.
			$tables = self::_generate_tables_inner( $resource_info['tables'] );

			$set = self::_generate_set( $resource_name, $update_data, $query_values );

			$conditional = self::_generate_conditional
			(
				array
				(
					'resource_name' => $resource_name,
					'resource_info' => $resource_info,
					'input' => $input,
					'sub_resource_info' => $extra_info['sub_resource_info'],
				),
				$query_values
			);

			// Compile the query.
			$query = 'UPDATE ' . $tables . ' SET ' . $set . ' WHERE '. $conditional;

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
			$type = 1;  // 1 = normal update from IDs/Resources, 2 = update from conditional

			// Get the correct input.
			if ( is_string( $args[0] ) )
			{
				$resource_name = $args[0];
				$input = ($args[1] && !is_array( $args[1] )) ? array( $args[1] ) : $args[1];
				$extra_info = $args[2];
			}
			else
			{
				$input = is_array( $args[0] ) ? $args[0] : array( $args[0] );
				$resource_name = get_class( reset( $input ) );
				$extra_info = $args[1];
			}

			if ( is_array( $input ) && empty( $input ) )
				return false;

			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] )
				new $resource_name();

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Many-to-many sub-resource?
			if ( $extra_info['sub_resource_info'] && $extra_info['sub_resource_info']['lookup'] )
			{

				$lookup = $extra_info['sub_resource_info']['lookup'];

				// Add the lookup table into this resource's info.
				$lookup_table_info['id'] = $lookup['id'];
				$lookup_table_info['fields'] = $lookup['fetch_fields'];
				$lookup_table_info['lookup'] = true;
				$resource_info['tables'] = array_merge( array( $lookup['name'] => $lookup_table_info ), $resource_info['tables'] );

				// We do this so that below when it's constructing the WHERE clause, it will
				// find the correct table name for the key.
				$resource_info['fields'][$extra_info['sub_resource_info']['key']] = $lookup['name'];

			}

			// Get a database handle.
			$dbh = Legato_DB::get( $resource_info['db'] );

			// Generate the query parts.
			$tables = self::_generate_tables( array_keys( $resource_info['tables'] ) );

			$tables_join = self::_generate_tables_inner( $resource_info['tables'] );

			// Get the conditional string.
			$conditional = self::_generate_conditional
			(
				array
				(
					'resource_name' => $resource_name,
					'resource_info' => $resource_info,
					'input' => $input,
					'sub_resource_info' => $extra_info['sub_resource_info'],
				),
				$query_values
			);

			if ( $conditional ) $conditional = ' WHERE ' . $conditional;

			// Compile the query.
			$query .= 'DELETE ' . $tables . ' FROM ' . $tables_join . $conditional;

			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $query_values );

			// Success!
			return true;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: static_count()
		//------------------------------------------------------------------------
		private static function static_count( $resource_name, $conditional = array(), $extra_info = array() )
		{

			$query_values = array();

			// Instantiate the class if it's not added yet. We have to give the
			// constructor the ability to add itself.
			if ( !self::$resource_info[$resource_name] )
				new $resource_name();

			// Get the resource info.
			$resource_info = self::$resource_info[$resource_name];

			// Get a database handle.
			$dbh = Legato_DB::get( $resource_info['db'] );

			// Many-to-many sub-resource.
			if ( $extra_info['sub_resource_info'] && $extra_info['sub_resource_info']['lookup'] )
			{

				$lookup = $extra_info['sub_resource_info']['lookup'];

				// Add the lookup table into this resource's info.
				$lookup_table_info['id'] = $lookup['id'];
				$lookup_table_info['fields'] = $lookup['fetch_fields'];
				$lookup_table_info['lookup'] = true;
				$resource_info['tables'] = array_merge( array( $lookup['name'] => $lookup_table_info ), $resource_info['tables'] );

				// We do this so that below when it's constructing the WHERE clause, it will
				// find the correct table name for the key.
				$resource_info['fields'][$extra_info['sub_resource_info']['key']] = $lookup['name'];

			}

			// Get the tables string.
			$tables = self::_generate_tables_inner( $resource_info['tables'] );

			// Any foreign keys we have to add?
			$key_mapping = array();
			if ( $resource_info['foreign_keys'] )
			{

				$tables .= self::_generate_foreign_tables( $resource_info, $key_mapping );
				self::_generate_foreign_select( $key_mapping );  // We do this to populate the key mapping.

			}  // End if foreign keys.

			// Get the conditional string.
			$conditional = self::_generate_conditional
			(
				array
				(
					'resource_name' => $resource_name,
					'resource_info' => $resource_info,
					'input' => $conditional,
					'foreign_key_mapping' => $key_mapping,
					'sub_resource_info' => $extra_info['sub_resource_info']
				),
				$query_values
			);

			if ( $conditional ) $conditional = 'WHERE ' . $conditional;

			// Create the query.
			$query = 'SELECT COUNT(*) AS "num" FROM ' . $tables . ' ' . $conditional;

			// Prepare and execute the query.
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $query_values );

			$row = $stmt->fetch_array();

			// Return the number of rows.
			return $row['num'];

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
			if ( !self::$cache && Legato_Settings::get( 'resource', 'cache_schemas' ) )
			{

				self::$cache = new Legato_Cache( array( 'handler' => Legato_Settings::get( 'resource', 'cache_handler' ),
					                                'namespace' => 'Legato_Resource',
										            'ttl' => Legato_Settings::get( 'resource', 'cache_ttl' ) ) );

			}  // End if no cache handler.

			if ( Legato_Settings::get( 'resource', 'cache_schemas' ) )
			{

				// Get the cached resource info.
				$cached_info = self::$cache->get( $this->name );
				if ( $cached_info )
					$resource_info = unserialize( $cached_info );

				// Let's check the version info to make sure it matches,
				// if not delete the cached version and set it up to create a new one.
				if ( $resource_info['version'] != Legato_Settings::get( 'resource', 'version' ) )
				{
					self::$cache->delete( $this->name );
					$resource_info = false;
				}

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
					Legato_Debug_Debugger::add_item( 'An error occured while attempting to load the resource schema.' );
					return false;
				}

				// Get the database connector.
				$resource_info['db'] = (string)$xml->db;

				// Get the database handle.
				$dbh = Legato_DB::get( $resource_info['db'] );

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
							$key = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', (string)$reference ) . '_id' );

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
							$key = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2',  $this->name ) . '_id' );

						// Check for many-to-many relationship.
						if ( $sub_resource['lookup'] != '' )
						{

							$lower_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2',  $this->name ) );
							$lower_sub_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2',  (string)$sub_resource ) );

							// Get the columns in the table.
							$query = 'SHOW COLUMNS FROM ' . $sub_resource['lookup'];
							$columns = $dbh->prepare( $query )->execute()->fetch_all_array();

							// Loop through each column and store it.
							foreach ( $columns as $column )
							{

								$fields[] = $column['Field'];

								// Filter out any columns that aren't part of this sub-resource's data.
								if ( strpos( $column['Field'], $lower_name ) === 0 )
									continue;

								$fetch_fields[] = $column['Field'];

							}

							// Store the lookup table information.
							$resource_info['many_to_many'][(string)$sub_resource] = array( 'name' => (string)$sub_resource['lookup'],
																							'id' => $lower_sub_name . '_id',
																							'fields' => $fields,
																							'fetch_fields' => $fetch_fields,
																							'primary_keys' => array( $lower_name . '_id', $lower_sub_name . '_id' ) );

						}  // End if many-to-many.

						$resource_info['sub_resources'][(string)$sub_resource] = $key;

					}
				}

				// Store the version.
				$resource_info['version'] = Legato_Settings::get( 'resource', 'version' );

				// Store the retrieved information in the cache.
				if ( Legato_Settings::get( 'resource', 'cache_schemas' ) )
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
				$fetch_options['fetch_all'] = !$this->populated['Legato_main'];
				$this->populated[$property] = true;

			}

			if ( ($this->info['foreign_keys'][$property . '_id'] && !$this->populated[$property]) )
			{

				// We have to make sure we've populated this resource before we
				// can populate the reference. We'll do that by attemping to get this reference's
				// key on the resource.
				$this->get( ($property . '_id') );

				$reference = $property . '_id';
				$foreign_key = $this->info['foreign_keys'][$reference];

				$this->data[$property] = new $foreign_key( $this->data[$reference] );
				return;

			}

			// Set not to use modifiers.
			$fetch_options['modifiers_on'] = false;

			// Get the resource data.
			$data = reset( self::static_fetch( $this->name, $this->id, $fetch_options ) );

			if ( !is_array( $this->data ) ) $this->data = array();
			if ( !is_array( $data ) ) $data = array();

			$this->data = array_merge( $this->data, $data );

			// Call the post_fetch() event.
			if ( method_exists( $this, 'post_fetch' ) )
				call_user_func_array( array( $this->name, 'post_fetch' ), array( &$this->data ) );

			// Set us as populated.
			$this->populated['Legato_main'] = true;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_tables()
		// Generates a table string from an array of tables.
		//------------------------------------------------------------------------
		private static function _generate_tables( $tables )
		{

			// Implode all the tables together.
			$tables_string = implode( ', ', $tables );

			// Return the imploded string.
			return $tables_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_tables_inner()
		// Generates an inner table join string from an array of tables.
		//------------------------------------------------------------------------
		private static function _generate_tables_inner( $tables_info )
		{

			$tables_string = '';

			// Loop through each table.
			$first_table_name      = '';
			$first_table_id_field  = 0;
			$i = 0;
			foreach ( $tables_info as $table_name => $table_info )
			{

				$i++;

				// Add to the table join.
				if ( $i == 1 )
				{
					$first_table_name = $table_name;
					$first_table_id_field = $table_info['id'];
					$tables_string .= $table_name;
				}
				else
					$tables_string .= ' INNER JOIN ' . $table_name . ' ON ' . $table_name . '.' . $table_info['id'] . ' = ' . $first_table_name . '.' . $first_table_id_field;

			}  // Next table.

			// Return the imploded string.
			return $tables_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_foreign_tables()
		// Generates the table join for the foreign keys of a resource.
		//------------------------------------------------------------------------
		private static function _generate_foreign_tables( $resource_info, &$key_mapping )
		{

			// Loop through each key.
			$key_index = 1;
			$table_join = '';
			foreach ( $resource_info['foreign_keys'] as $key => $reference )
			{

				// Make sure the information has been populated for the referenced resource.
				if ( !self::$resource_info[$reference] )
					new $reference();

				// Find out which table this key belongs to.
				$key_table = $resource_info['fields'][$key];

				// Add to the key mapping array.
				$key_mapping[$key] = array( 'reference' => $reference );

				// Loop through each table for the referenced resource.
				foreach ( self::$resource_info[$reference]['tables'] as $table_name => $table_info )
				{

					// Add to the table join.
					$table_join .= ' LEFT JOIN ' . $table_name . ' AS f' . $key_index . ' ON f' . $key_index . '.' . $table_info['id'] . ' = ' . $key_table . '.' . $key;

					// Add to the key mapping array.
					$key_mapping[$key]['tables'][$table_name] = $key_index;
					++$key_index;

				}  // Next table.

			}  // Next key.

			return $table_join;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_foreign_select()
		// Generates the select string for the foreign keys.
		//------------------------------------------------------------------------
		private static function _generate_foreign_select( &$key_mapping )
		{

			$select_string = '';

			// Loop through each key.
			foreach ( $key_mapping as $key => $key_info )
			{

				$field_index = 1;

				// Loop through each table for the referenced resource.
				foreach ( self::$resource_info[$key_info['reference']]['tables'] as $table_name => $table_info )
				{

					// Loop through each field and store it in the select string.
					foreach ( $table_info['fields'] as $field )
						if ( !self::$resource_info[$key_info['reference']]['deferred'][$field] )
						{
							$select_string .= ', f' . $key_info['tables'][$table_name] . '.' . $field . ' AS f' . $key_info['tables'][$table_name] . '_' . $field_index;
							$key_mapping[$key]['fields'][$field] = 'f' . $key_info['tables'][$table_name] . '_' . $field_index;
							$key_mapping[$key]['fields_tables'][$field] = 'f' . $key_info['tables'][$table_name];
							++$field_index;
						}

				}  // Next table.

			}  // Next key.

			return $select_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_conditional()
		// Generates a conditional string from some input data.
		//------------------------------------------------------------------------
		private static function _generate_conditional( $setup_array, &$query_values )
		{

			if ( !function_exists( 'Legato_get_operation' ) )
			{

				function Legato_get_operation( &$key, $position )
				{

					$subtraction = 0;

					// Any special syntax?
					if ( $position == 'end' )
					{
						$length = strlen( $key );
						$char1 = $key[($length - 2)];
						$char2 = $key[($length - 1)];
					}
					else if ( $position == 'front' )
					{
						$char1 = $key[0];
						$char2 = $key[1];
					}

					// Find which syntax.
					if ( $char1 == '>' && $char2 == '=' )
					{
						//$key = substr( $key, 0, -3 );
						$subtraction = 3;
						$operation = '>=';

					}  // End if >=
					else if ( $char1 == '<' && $char2 == '=' )
					{
						$subtraction = 3;
						$operation = '<=';

					}  // End if <=
					else if ( $char1 == '!' && $char2 == '=' )
					{
						$subtraction = 3;
						$operation = '!=';

					}	// End if !=
					else if ( $char2 == '>' )
					{
						$subtraction = 2;
						$operation = '>';

					}  // End if >
					else if ( $char2 == '<' )
					{
						$subtraction = 2;
						$operation = '<';

					}  // End if <
					else if ( $char2 == '!' )
					{
						$subtraction = 2;
						$operation = 'NOT LIKE';

					}	// End if NOT LIKE
					else
						$operation = '';

					// Remove the special syntax.
					if ( $operation != '' )
					{
						if ( $position == 'end' )
							$key = substr( $key, 0, -($subtraction) );
						else if ( $position == 'front' )
							$key = substr( $key, $subtraction );
					}

					// Return the operator being used.
					return $operation;

				}

			}

			$resource_name = $setup_array['resource_name'];
			$resource_info = $setup_array['resource_info'];
			$input = $setup_array['input'];
			$foreign_key_mapping = $setup_array['foreign_key_mapping'];
			$sub_resource_info = $setup_array['sub_resource_info'];

			$numeric_conditionals = array();
			$key_conditionals = array();

			$numeric_query_values = array();
			$key_query_values = array();

			if ( !$resource_info )
				$resource_info = self::$resource_info[$resource_name];

			// If no input was passed in, we don't need to do anything.
			if ( !$input ) $input = array();
			if ( !is_array( $input ) ) $input = array( $input );

			// Lookup from IDs/Resources.
			// We loop through all the input and see if the elements are either IDs or
			// resource objects. If they are we store them in the $numeric_conditionals array.
			// We do this so that we can have both IDs, resources, and key conditionals passed in at once.
			foreach ( $input as $key => $value )
			{

				// Make sure it's either an ID or resource.
				if ( (is_numeric( $key ) && is_numeric( $value )) || ($value instanceof $resource_name) )
				{

					// If a resource, get the ID.
					if ( $value instanceof $resource_name )
						$value = $value->id;

					// The value should be the ID.
					$id = $value;

					// Use the first table.
					$table_info = reset( $resource_info['tables'] );
					$table_name = key( $resource_info['tables'] );

					// Add to the conditional.
					$numeric_conditionals[] = $table_name . '.' . $table_info['id'] . ' = ?';
					$numeric_query_values[] = $id;

				}  // End if numeric conditional.

			}  // End if numeric key lookup.

			// We'll now be checking for any key conditionals, so make
			// sure the input is in the correct format.
			if ( !is_array( $input[0] ) )
				$input = array( $input );

			// Lookup from key conditionals.
			// Loop through all the elements passed in and skip over any numeric or resource objects.
			// We do this so that we can have both IDs, resources, and key conditionals passed in at once.
			foreach ( $input as $first_conditional )
			{

				if ( !is_array( $first_conditional ) )
					$first_conditional = array( $first_conditional );

				$second_conditionals = array();

				foreach ( $first_conditional as $key => $values )
				{

					// Don't count in numeric IDs or resource objects.
					if ( (is_numeric( $key ) && is_numeric( $values )) || ($values instanceof $resource_name) )
						continue;

					$third_conditionals = array();
					$operation = 'LIKE';

					$new_operation = Legato_get_operation( $key, 'end' );
					if ( $new_operation ) $operation = $new_operation;

					// Get the full key.
					if ( strpos( $key, '.' ) )
					{
						list( $reference, $key ) = explode( '.', $key );
						$key = $foreign_key_mapping[($reference . '_id')]['fields_tables'][$key] . '.' . $key;
					}
					else
						$key = $resource_info['fields'][$key] . '.' . $key;

					if ( !is_array( $values ) )
						$values = array( $values );

					foreach ( $values as $value )
					{

						if ( !is_array( $value ) )
						{
							$new_operation = Legato_get_operation( $value, 'front' );
							if ( $new_operation ) $operation = $new_operation;

							$third_conditionals[] = $key . ' ' . $operation . ' ?';
							$key_query_values[] = $value;
						}
						else
						{

							$fourth_conditionals = array();
							foreach ( $value as $nested_value )
							{
								$new_operation = Legato_get_operation( $nested_value, 'front' );
								if ( $new_operation ) $operation = $new_operation;

								$fourth_conditionals[] = $key . ' ' . $operation . ' ?';
								$key_query_values[] = $nested_value;
							}

							$third_conditionals[] = '(' . implode( ' AND ', $fourth_conditionals ) . ')';
						}

					}

					$second_conditionals[] .= '(' . implode( ' OR ', $third_conditionals ) . ')';

				}

				if ( $second_conditionals )
					$key_conditionals[] = '(' . implode( ' AND ', $second_conditionals ) . ')';

			}

			// Implode the conditional arrays.
			$numeric_conditionals = implode( ' OR ', $numeric_conditionals );
			$key_conditionals = implode( ' OR ', $key_conditionals );

			// Combine it all together.
			$conditionals_string = $key_conditionals;
			if ( $numeric_conditionals )
				if ( $key_conditionals )
					$conditionals_string = '(' . $conditionals_string . ') AND (' . $numeric_conditionals . ')';
				else
					$conditionals_string = $numeric_conditionals;

			// Combine the query value arrays.
			foreach ( $key_query_values as $key_query_value )
		   		$query_values[] = $key_query_value;

		   	foreach ( $numeric_query_values as $numeric_query_value )
		   		$query_values[] = $numeric_query_value;

		   	// Any sub-resources?
			if ( $sub_resource_info )
			{

				if ( $conditionals_string )
					$conditionals_string = '(' . $conditionals_string . ') AND ';

				// Add the relationship to the conditional.
				$conditionals_string .= '(' . $resource_info['fields'][$sub_resource_info['key']] . '.' . $sub_resource_info['key'] . ' = ?)';
				$query_values[] = $sub_resource_info['id'];

			}  // End if sub-resources.

			// Return the conditionals string.
			return $conditionals_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_fields_select()
		// Generates a fields select from the input given.
		//------------------------------------------------------------------------
		private static function _generate_fields_select( $resource_name, $input = array(), $options = array() )
		{

			$fields = array();
			$resource_info = self::$resource_info[$resource_name];

			// Getting fields from resource or from input.
			if ( !$input )
			{

				// Loop through all the fields.
				foreach ( $resource_info['fields'] as $field => $table_name )
					if ( !$resource_info['deferred'][$field] )
						$fields[] = $table_name . '.' . $field;

				// Any deferred field?
				if ( $options['deferred'] )
					$fields[] = $resource_info['fields'][$options['deferred']] . '.' . $options['deferred'];

			}  // End if getting fields by resource.
			else if ( $input )
			{

				// Loop through all the fields.
				foreach ( $input as $field )
					if ( $resource_info['fields'][$field] )
						$fields[] = $resource_info['fields'][$field] . '.' . $field;

			}  // End if getting fields by input.

			// Implode the fields array.
			$fields_string = implode( ', ', $fields );

			// Return the fields string.
			return $fields_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_data_entry()
		// Generates a data entry string from some input data.
		//------------------------------------------------------------------------
		private static function _generate_data_entry( $resource_name, $fields, $data, &$query_values, $count = 1 )
		{

			$data_entries = array();
			$new_query_values = array();
			$fields_string = '';
			$resource_info = self::$resource_info[$resource_name];

			// Make sure it's in an array so we can loop through the rows.
			if ( $data[0] == '' )
				$data = array( $data );

			// Get the fields.
			$fields = array_fill_keys( $fields, '' );

			// Loop through all the fields to set up the field string.
			$fields_string = implode( ', ', array_fill( 0, count( $fields ), '?' ) );

			// Loop through all the rows to set up.
			foreach ( $data as $row_data )
			{

				// Add to the data entries array.
				$data_entries[] = '( ' . $fields_string . ' )';

				// Get the correct data input array.
				$row_data = array_merge( $fields, array_intersect_key( $row_data, $fields ) );

				// Loop through all the field and add it.
				foreach ( $row_data as $field => $field_data )
				{

					if ( $resource_info['serialized'] && $resource_info['serialized'][$field] )
						$field_data = serialize( $field_data );

					// We do two query values in case we have a count more than one for later on.
					$query_values[] = $field_data;
					$new_query_values[] = $field_data;

				}  // Next field data.

			}  // Next row data.

			// If count is greater than one, multiply the data entries and query values.
			if ( $count > 1 )
			{

				$data_entries = array_fill( 0, $count, $data_entries[0] );

				$field_count = count( $fields );
				for ( $i = 1; $i < $count; $i++ )
					for( $n = 0; $n < $field_count; $n++ )
						$query_values[] = $new_query_values[$n];

			}

			// Implode the data entries array.
			$data_entries_string = implode( ', ', $data_entries );

			// Return the data entries string.
			return $data_entries_string;

		}


		//------------------------------------------------------------------------
		// (Exclude)
		// Function: _generate_set()
		// Generates a set string from some input data.
		//------------------------------------------------------------------------
		private static function _generate_set( $resource_name, $update_data, &$query_values )
		{

			$sets = array();
			$resource_info = self::$resource_info[$resource_name];

			// Loop through all the update data.
			foreach ( $update_data as $field => $data )
			{

				// Only add if there is update data for this field.
				if ( !array_key_exists( $field, $resource_info['fields'] ) )
					continue;

				// Get the table for this field.
				$table = $resource_info['fields'][$field];

				// Add to the sets array.
				$sets[] = $table . '.' . $field . ' = ?';

				// Should we serialize the data?
				if ( $resource_info['serialized'][$field] )
					$data = serialize( $data );

				// Add the data to the query values array.
				$query_values[] = $data;

			}  // Next field data.

			// Implode the sets array.
			$sets_string = implode( ', ', $sets );

			// Return the sets string.
			return $sets_string;

		}


		private static function _get_query_cache_key( $query, $query_values )
		{

			$query_values_string = '';
			foreach ( $query_values as $value )
				$query_values_string .= '|' . $value . '|';

			//!!!!!!!!!!!!!!!!!!!!!
			// We still have to take in to account some more variables,
			// like the raw data modifier.
			//!!!!!!!!!!!!!!!!!!!!!

			$key = md5( $query . '|' . $query_values );

			return $key;

		}


		private static function _check_query_cache( $key )
		{

			return self::$query_cache[$key];

		}

		private static function _store_query_cache( $key, $data )
		{

			self::$query_cache[$key] = $data;

		}

	}