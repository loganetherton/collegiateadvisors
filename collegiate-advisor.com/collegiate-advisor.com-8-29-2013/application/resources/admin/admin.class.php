<?php
  
	//--------------------------------------------------------------------------
	// Name: Admin
	// Desc: A class used to encapsulate all the common operations done with an
	//       admin.
	//--------------------------------------------------------------------------
	class Admin extends Legato_Resource
	{
																				
	
		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------		
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
		//------------------------------------------------------------------------
		// Name: authenticate()
		// Desc: Checks to see if there is a user in the DB with the username
		//       and password passed in.
		//------------------------------------------------------------------------ 
		public static function authenticate( $username, $password )
		{
			
			// Get the user.
			$user = new Admin( array( 'username' => $username, 'password' => md5( $password ) ) );
			
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
	  
	}

?>