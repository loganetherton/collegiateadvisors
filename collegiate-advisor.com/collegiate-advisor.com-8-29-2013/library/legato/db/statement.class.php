<?php
	
	//--------------------------------------------------------------------------
	// Name: Legato_DB_Statement
	// Desc: A simple wrapper class around a SQL query. Used to interface
	//       with a query.
	//--------------------------------------------------------------------------
	class Legato_DB_Statement
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $query;     		// The query to be executed.
		public $dbh;       		// The handle to the DB that owns this query.

		//------------------------------------------------------------------------
		// Protected Variables
		//------------------------------------------------------------------------
		protected $_stmt = NULL;   	// The query statement.

		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $dbh, $query )
		{

			// Assign the variables to those passed in / default values.
			$this->query    = $query;
			$this->dbh      = $dbh;

		}


		//------------------------------------------------------------------------
		// Name: execute()
		// Desc: Executes the query.
		//------------------------------------------------------------------------
		public function execute()
		{
			
			$reporting_on = Legato_Settings::get( 'debugger', 'enable_reporting' );
			$time = 0;

			// Get the function arguments.
			$call_args = func_get_args();

			// Was an array passed in?
			if ( is_array( $call_args[0] ) )
				$args = $call_args[0];
			else
				$args = $call_args;
			
			// Make sure we're connected.
			if ( !$this->dbh->dbh )
				$this->dbh->connect();
				
			// Start timing how long this query takes.
			if ( $reporting_on )
				$start_time = microtime( true );
				
			// Prepare the statement.
			$this->_stmt = $this->dbh->dbh->prepare( $this->query );
				
			// Loop through the arguments.
			for ( $i = 1; $i <= count( $args ); $i++ )
			{
				
				$this->_stmt->bindValue( $i, $args[($i - 1)] );
				
			}  // Next argument.
			
			// Execute the query.
			$ret = $this->_stmt->execute();
			
			// Make sure the result is valid.
			if ( !$ret )
			{
				$error_info = $this->_stmt->errorInfo();
				return Legato_Debug_Debugger::add_item( 'The query could not be executed: <br />' . $this->query . 'Database gave error message: "' . $error_info[2] . '"' );
			}
			
			// Stop timing how long this query takes.
			if ( $reporting_on )
			{
				$end_time = microtime( true );
				$time = sprintf( '%f', $end_time - $start_time );
			}
			
			// Increment the query counter in the DB manager.
			Legato_Debug_Debugger::add_query( $this->query, $time );

			// Success!
			return $this;

		}


		//------------------------------------------------------------------------
		// Name: fetch_array()
		// Desc: Returns an array of the next row's data.
		//------------------------------------------------------------------------
		public function fetch_array( $input_array = false )
		{

			// Fetch the array.
			$row_data = $this->_stmt->fetch( PDO::FETCH_ASSOC );

			// Input array passed in?
			if ( $input_array != false )
			{

				$temp_data = array();

				// Loop through each field.
				$i = 0;
				foreach ( $row_data as $value )
				{

					// Add to the result array.
					$temp_data[$input_array[$i]] = $value;

					$i++;

				}  // Next field.

				$row_data = $temp_data;

			}  // End if an input array was passed in.

			// Return the data.
			return $row_data;

		}


		//------------------------------------------------------------------------
		// Name: fetch_all_array()
		// Desc: Returns an array of all the query data.
		//------------------------------------------------------------------------
		public function fetch_all_array()
		{
			
			// Return the data.
			return $this->_stmt->fetchAll( PDO::FETCH_ASSOC );

		}
		
		
		//------------------------------------------------------------------------
		// Name: num_rows()
		// Desc: Returns the number of rows returned.
		//------------------------------------------------------------------------
		public function num_rows()
		{

			// Return the number of rows.
			return $this->_stmt->rowCount();

		}


		//------------------------------------------------------------------------
		// Name: affected_rows()
		// Desc: Returns the number of affected rows.
		//------------------------------------------------------------------------
		public function affected_rows()
		{

			// Return the number of affected rows.
			return $this->_stmt->rowCount();

		}


		//------------------------------------------------------------------------
		// Name: insert_id()
		// Desc: Returns the insert ID.
		//------------------------------------------------------------------------
		public function insert_id()
		{

			// Return the last auto increment value.
			return $this->dbh->dbh->lastInsertId();

		}

	}