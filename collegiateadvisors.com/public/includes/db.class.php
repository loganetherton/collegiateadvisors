<?php

	class DB
	{

		private static $dbh = null;

		private static function connect()
		{

			// server, user, pass
			$dbh = mysql_connect( 'localhost', 'collegia_legato', 'cad4ldo79' );
			mysql_select_db( 'collegia_legato' );

		}

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