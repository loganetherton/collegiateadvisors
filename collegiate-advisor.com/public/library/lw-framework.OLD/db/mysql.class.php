<?php

	//--------------------------------------------------------------------------
	// Name: LW_DB_MySQL
	// Desc: A simple wrapper class for MySQL. Used for connecting to a DB and
	//       preparing queries for execution.
	// Note: Used in conjunction with the DB_MySQLStatement class.
	//--------------------------------------------------------------------------
	class LW_DB_MySQL
	{

		//------------------------------------------------------------------------
		// Protected Variables
		//------------------------------------------------------------------------
		public $user;       // Contains the username.
		public $pass;       // Contains the password.
		public $dbhost;     // The database host.
		public $dbname;     // The database name.
		public $dbh;        // The handle to the database.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $user, $pass, $dbhost, $dbname )
		{

			// Assign the DB variables to those passed in.
			$this->user    = $user;
			$this->pass    = $pass;
			$this->dbhost  = $dbhost;
			$this->dbname  = $dbname;

		}


		//------------------------------------------------------------------------
		// Name: prepare()
		// Desc: Prepares a query for execution.
		//------------------------------------------------------------------------
		public function prepare( $query )
		{

			// Instantiate a new statement and return it.
			return new LW_DB_MySQLStatement( $this, $query );

		}
		
		
		//------------------------------------------------------------------------
		// Name: connect()
		// Desc: Connects to the database.
		//------------------------------------------------------------------------
		public function connect()
		{
			
			try
			{

				// Connect to the database.
				$this->dbh = new PDO( 'mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname, $this->user, $this->pass, array( PDO::ATTR_PERSISTENT => true ) );
				$this->dbh->setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
				
			}
			catch( PDOException $e )
			{
				LW_Debug_Debugger::add_item( 'Could not connect to the database: <br />' . $e->getMessage() );
				return false;
			}

		}

	}
	
	
	//--------------------------------------------------------------------------
	// Name: LW_DB_MySQLStatement
	// Desc: A simple wrapper class around a MySQL query. Used to interface
	//       with a query.
	//--------------------------------------------------------------------------
	class LW_DB_MySQLStatement
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $query;     // The query to be executed.
		public $dbh;       // The handle to the DB that owns this query.


		//------------------------------------------------------------------------
		// private Variables
		//------------------------------------------------------------------------
		private $stmt          = NULL;   // The query statement.


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
				
			// Prepare the statement.
			$this->stmt = $this->dbh->dbh->prepare( $this->query );
				
			// Loop through the arguments.
			for ( $i = 1; $i <= count( $args ); $i++ )
			{
				
				$this->stmt->bindValue( $i, $args[($i - 1)] );
				
			}  // Next argument.
			
			// Execute the query.
			$ret = $this->stmt->execute();
			
			// Make sure the result is valid.
			if ( !$ret )
			{
				LW_Debug_Debugger::add_item( 'The query could not be executed: <br />' . $this->query );
				return false;
			}
			
			// Increment the query counter in the DB manager.
			LW_Debug_Debugger::add_query( $this->query );

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
			$row_data = $this->stmt->fetch( PDO::FETCH_ASSOC );

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
			return $this->stmt->fetchAll( PDO::FETCH_ASSOC );

		}
		
		
		//------------------------------------------------------------------------
		// Name: num_rows()
		// Desc: Returns the number of rows returned.
		//------------------------------------------------------------------------
		public function num_rows()
		{

			// Return the number of rows.
			return $this->stmt->rowCount();

		}


		//------------------------------------------------------------------------
		// Name: affected_rows()
		// Desc: Returns the number of affected rows.
		//------------------------------------------------------------------------
		public function affected_rows()
		{

			// Return the number of affected rows.
			return $this->stmt->rowCount();

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

?>