<?php
  
	//--------------------------------------------------------------------------
	// Name: User
	// Desc: A class used to encapsulate all the common operations done with a
	//       user.
	//--------------------------------------------------------------------------
	class User extends Legato_Resource
	{		
		
		//------------------------------------------------------------------------
		// Name: get_recovery_info()
		// Desc: Returns the Username and Password recovery information.
		//------------------------------------------------------------------------ 
		public function get_recovery_info()
		{
			
			// Get a database handle.
			$dbh = Legato_DB::get( 'Main' );
			
			// Get the user if he exists.
			$query = 'select * from users_login_recovery
			          where user_id = ?';
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $this->get( 'id' ) );
			
			$row = $stmt->fetch_array();
			
			return $row;
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: set_last_logged()
		// Desc: Sets the last time the user was logged in.
		//------------------------------------------------------------------------ 
		public function set_last_login( $time = false )
		{
			
			if ( !$time ) $time = time();
			
			// Update.
			$data['last_login'] = $time;
			$this->update( $data );
			
		}
		
		
		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------ 
		public static function my_create( $data )
		{
			
			// Create the encryption object.
			$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $data['username'] ), 'twofish' );

			// Set the passwords.
			$data['password'] = md5( $data['password'] );
			
			if ( $data['mycareer_password'] != '' )
				$data['mycareer_password'] = $encryption->encrypt( $data['mycareer_password'] );
			
			if ( $data['testgear_password'] != '' )
				$data['testgear_password'] = $encryption->encrypt( $data['testgear_password'] );

			// Create the user.
			Legato_Resource::create( 'User', $data );
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: authenticate()
		// Desc: Checks to see if there is a user in the DB with the username
		//       and password passed in.
		//------------------------------------------------------------------------ 
		public static function authenticate( $username, $password )
		{
			
			$user = new User( array( 'username' => $username, 
			                         'password' => md5( $password ), 
									 'advisor_id' => $GLOBALS['advisor']->get( 'id' ) ) );
			
			// Return the ID of the user, or fail.
			if ( $user->get( 'id' ) )
			{
				return $user->get( 'id' );
			}
			else
			{
				Legato_Debug_Debugger::add_item( 'Wrong username or password.' );
				return false;
			}
		
		}
		
		
		//------------------------------------------------------------------------
		// Name: username_available()
		// Desc: Checks to see if the username passed in is available.
		//------------------------------------------------------------------------ 
		public static function username_available( $username )
		{
		
			// Get a database handle.
			$dbh = Legato_DB::get( 'Main' );
			
			// Check if the username exists.
			$query = 'select users.id from users, admins
			          where users.username = ? or admins.username = ?';
			$stmt = $dbh->prepare( $query );
			$stmt->execute( $username, $username );
			
			// Return whether the username is available.
			if ( $stmt->num_rows() == 0 )
				return true;
			else
				return false;
		
		}
	  
	}

?>