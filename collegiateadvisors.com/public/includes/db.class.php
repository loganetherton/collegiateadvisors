<?php

	class DB
	{

		// Set a private static database handler
		private static $dbh = null;

		// Connect to the database
		private static function connect()
		{

			$dbh = mysql_connect( 'localhost', 'collegia_legato', 'cad4ldo79' );
			mysql_select_db( 'collegia_legato' );

		}

		// Return an array of news items from the database
		public static function get_news()
		{

			if ( !self::$dbh )
				self::connect();

   			$query = 'SELECT * FROM news ORDER BY date DESC LIMIT 0, 4';
   			$result = mysql_query( $query );

   			$rows = array();
			for ( $data = mysql_fetch_assoc( $result ); $data; $data = mysql_fetch_assoc( $result ) )
				$rows[] = $data;

   			return $rows;

		}

	}