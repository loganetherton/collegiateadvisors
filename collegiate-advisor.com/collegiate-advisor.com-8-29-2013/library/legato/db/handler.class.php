<?php

	//--------------------------------------------------------------------------
	// Name: Legato_DB_Handler
	// Desc: A simple wrapper class for PDO. Used for connecting to a DB and
	//       preparing queries for execution.
	//--------------------------------------------------------------------------
	class Legato_DB_Handler
	{

		//------------------------------------------------------------------------
		// Protected Variables
		//------------------------------------------------------------------------
		protected $_type;		// The type of database (MySQL, PostgreSQL, SQLite)
		protected $_data;		// The array of data
		
		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $dbh;        // The handle to the database.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $type, $data = array() )
		{

			// Assign the DB Type
			$this->_type	= strtolower( $type );
			$this->_data	= $data; 

		}


		//------------------------------------------------------------------------
		// Name: prepare()
		// Desc: Prepares a query for execution.
		//------------------------------------------------------------------------
		public function prepare( $query )
		{

			// Instantiate a new statement and return it.
			return new Legato_DB_Statement( $this, $query );

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
				switch( $this->_type )
				{
					
					case 'mysql':
						$this->dbh = new PDO( 'mysql:host=' . $this->_data['host'] . ';dbname=' . $this->_data['dbname'], $this->_data['user'], $this->_data['pass'], array( PDO::ATTR_PERSISTENT => true ) );
						$this->dbh->setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
						break;
					case 'postgresql':
					case 'pgsql':
						$this->dbh = new PDO( 'pgsql:host=' . $this->_data['host'] . ' port=' . $this->_data['port'] . ' dbname=' . $this->_data['dbname'], $this->_data['user'], $this->_data['pass'], array( PDO::ATTR_PERSISTENT => true ) );
						break;
					case 'sqlite':
					case 'sqlite2':	
						if ( $this->_data['memory'] )
							$this->dbh = new PDO( $this->_type . ':memory:' );
						else
							$this->dbh = new PDO( $this->_type . ':' . $this->_data['path'] );
						break;
						
				}
				
			}
			catch( PDOException $e )
			{
				return Legato_Debug_Debugger::add_item( 'Could not connect to the database: <br />' . $e->getMessage() );
			}

		}

	}