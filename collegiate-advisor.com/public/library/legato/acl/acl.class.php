<?php

	/*
		Class: Legato_ACL
	 	This class manages an Access Control List.
	*/
	class Legato_ACL
	{
		
		/* 
			Group: Constants
		*/

		/*
			Const: SPACER
			*string* Determines what is used to split the Permission/Inherits lists. Defaults to a comma: ",".
		*/
		const SPACER = ',';


		/*
			(Exclude)
			
			Var: $_acl
			Stores the ACL.
			
			Var: $_user
			Stores the default user, so you do not need to pass it in to every function.			
		*/
		protected $_acl = array();
		protected $_user = '';
		
		
		/*
			Group: Functions
		*/

		/*
			Constructor: __construct()
			Takes in a configuration array and initializes the ACL.
		
			Syntax:
				void __construct( [ array $acl = array() ] )
		
			Parameters:
				array $config - *optional* - The ACL to be stored.
			
			Examples:
			(begin code)				
				$list = array
				(
					'User1' => array
					( 
						'Inherit' => 'UserA,UserB...',
						'Allow' => 'PermissionA,PermissionB',
						'Deny' => 'PermissionC,PermissionD...' 
					),
					'User2' => ...
				);
				
				$acl = new Legato_ACL( $list );				
			(end)
		*/
		public function __construct( $acl = array() )
		{

			if ( count( $acl ) > 0 )
				$this->add( $acl );

		}
		
		
		/*
  			Function: set_default()
  			Sets the default user to use when checking Permissions.
  				
  			Syntax:
  				void set_default( string $user )
  				
  			Parameters:
  				string $user - The default user to check permissions on.
  										
  			Examples:
  			(begin code)
  				$acl->allow( 'Kirby', 'Eat Food' );
  				
  				$acl->set_default( 'Kirby' );
  				
  				if ( $acl->check( 'Eat Food' ) && $acl->check( 'Eat Bubbles' ) )
  					$kirby->eat_bubbles();
			(end)
  		*/
		public function set_default( $user )
		{

			$this->_user = $user;

		}
		
		
		/*
  			Function: add()
  			Takes in a configuration array and merges the array with the ACL.
  				
  			Syntax:
  				void add( array $param )
  				
  			Parameters:
  				array $acl - Adds the ACL array to the existing ACL array.
  										
  			Examples:
  			(begin code)
  				$acl_array = array
				(
					'Teacher' => array
					( 
						'Inherit' => 'User1',
						'Allow' => 'Update Teacher Info, Update Student Info'
					)
				);
				
				$acl->add( $acl_array );  				
  			(end)
  			
  			See Also:
  				- <Legato_ACL::remove()>
  		*/
		public function add( $acl )
		{

			$this->_acl = array_merge( $this->_acl, $acl );

		}


		/*
  			Function: remove()
  			Removes a user from the ACL. 
  				
  			Syntax:
  				void remove( string $user )
  				
  			Parameters:
  				string $user - The user to remove from the ACL.
  										
  			Examples:
  				>	$acl->remove( 'Teacher' );
  				
  			See Also:
  				- <Legato_ACL::add()>
  		*/
		public function remove( $user )
		{

			// If it is an array of users recursively call remove.
			if ( is_array( $user ) )
				foreach ( $user as $value )
					unset( $this->_acl[$value] );
			else
				unset( $this->_acl[$user] );

		}
		
		
		/*
  			Function: allow()
  			Allow the user(s) the given permission(s). Can be called without a user passed in and gets set to the default user.
  				
  			Syntax:
  				void allow( string $permission )
  				
  				void allow( mixed $user, mixed $permission )
  				
  			Parameters:
  				*With Default User Defined*
  				
  				string $permission - The permission to allow the default user.
  				
  				*On Non-Default User*
				    				
  				mixed $user - Either a string or an array of users to allow the given permission(s).
  				mixed $permission - Either a string or an array of permissions to allow the given user(s).
  										
  			Examples:
  			(begin code)
  				// Using allow with a default user.
  				$acl->set_default( 'Teacher' );
  				$acl->allow( 'Modify Student' );
  				
  				// Allowing on a single user and single permssion.
  				$acl->allow( 'Student', 'Modify Answers' );
  				
  				// Allowing multiple users a single permission.
  				$acl->allow( array( 'Teacher', 'Student' ), 'Add Articles' );
  				
  				// Allowing multiple permissions on a single user.
  				$acl->allow( 'Teacher', array
				( 
					'Update Student Permissions', 
					'Update Teacher Information' ) 
				);
  				
  				// Allowing multiple permissions on multiple users.
  				$acl->allow
				( 
					array( 'Teacher', 'Student' ), 
					array( 'Change Password', 'Change Email Address' ) 
				);
  			(end)
  			
  			See Also:
  				- <Legato_ACL::deny()>
  				- <Legato_ACL::check()>
  		*/
		public function allow()
		{

			$args = func_get_args();

			// If no user is passed in call allow using the default user
			if ( count( $args ) == 1 )
				$this->allow( $this->_user, $args[0] );
				
			list( $user, $permission ) = $args;

			// If it is an array of users call allow for each user
			if ( is_array( $user ) )
			{
			
				foreach ( $user as $value )
					$this->allow( $value, $permission );
					
				return;
			
			}		
			// If it is an array of permissions call allow for each permission
			elseif ( is_array( $permission ) )
			{
				
				foreach ( $permission as $value )
					$this->allow( $user, $value );
					
				return;
			
			}
			
			// Process.
			if ( $this->_acl[$user]['Allow'] )
				$this->_process( $user, 'Allow' );			
			
			// Skip if the user is already allowed the permission.
			if ( $this->_acl[$user]['_Allow'][$permission] === true )
			{
					
				// Allow the user the permission.
				$this->_acl[$user]['_Allow'][$permission] = true;

			}

		}
		
		
		/*
			Function: deny()
  			Deny the user(s) the given permission(s). Can be called without a user passed in and denies for the default user.
  				
  			Syntax:
  				void deny( mixed $permission )
  				
  				void deny( mixed $user, mixed $permission )
  				
  			Parameters:
  				*With Default User Defined*
  				
  				mixed $permission - The permission(s) to deny the default user.
  				
  				*On Non-Default User*
  				
  				mixed $user - Either a string or an array of users to deny the given permission(s).
  				mixed $permission - Either a string or an array of permissions to deny the given user(s).
  										
  			Examples:
  			(begin code)
  				// Using deny with a default user.
  				$acl->set_default( 'Teacher' );
  				$acl->deny( 'Modify Administrator' );
  				
  				// Denying on a single user and single permssion.
  				$acl->deny( 'Student', 'Modify Questions' );
  				
  				// Denying multiple users a single permission.
  				$acl->deny( array( 'Teacher', 'Student' ), 'Add School News' );
  				
  				// Denying multiple permissions on a single user.
  				$acl->deny( 'Guest', array
				( 
					'Update Account Information', 
					'Remove Messages' ) 
				);
  				
  				// Denying multiple permissions on multiple users.
  				$acl->deny
				( 
					array( 'Teacher', 'Student' ), 
					array( 'Remove School Slogan', 'Update School Information' ) 
				);
  			(end)
  				- <Legato_ACL::allow()>
  				- <Legato_ACL::check()>
  		*/
		public function deny()
		{
		
			$args = func_get_args();
			
			// If no user passed in, call the function by passing in the default user
			if ( count( $args ) == 1 )
				$this->deny( $this->_user, $args[0] );
				
			list( $user, $permission ) = $args;
			
			// If it is an array of users, call deny for each user
			if ( is_array( $user ) )
			{
			
				foreach ( $user as $value )
					$this->deny( $value, $permission );
					
				return;
			
			}		
			// If it is an array of permissions, call deny for each permission
			elseif ( is_array( $permission ) )
			{
				
				foreach ( $permission as $value )
					$this->deny( $user, $value );
					
				return;
			
			}
			
			// Process.
			if ( $this->_acl[$user]['Deny'] )
				$this->_process( $user, 'Deny' );
				
			// Skip if the user is already denied the permission.
			if ( $this->_acl[$user]['_Deny'][$permission] === true )
			{
				
				// Process.
				if ( $this->_acl[$user]['Allow'] )
					$this->_process( $user, 'Allow' );
				
				// Remove the permission from the user's allow list.
				if ( $this->_acl[$user]['_Allow'][$permission] === true )
					unset( $this->_acl[$user]['_Allow'][$permission] );
					
				// Deny the user the permission.
				$this->_acl[$user]['_Deny'][$permission] = true;

			}

		}
		
		
		/*

			Function: inherit()
			The user(s) passed in now inherit from the parent(s) passed in. 
			Can be called without a user passed in and gets set to the default user.
  				
  			Syntax:
  				void inherit( mixed $parent )
  				
  				void inherit( mixed $user, mixed $parent )
  				
  			Parameters:
  				*With Default User Defined*
  				
  				mixed $parent - The parent(s) for the default user to inherit from.
  				
  				*On Non-Default User*
  				
  				mixed $user - The user(s) to inherit from the parent(s).
  				mixed $parent - The parent(s) to inherit from.
  										
  			Examples:  			
  			(begin code)
  				// Using inherit with a default user.
  				$acl->set_default( 'Teacher' );
  				$acl->inherit( 'User' );
  				$acl->inherit( array( 'Guest', 'Teacher' ) );
  				
  				// Inheriting a single user.
  				$acl->inherit( 'Administrator', 'User' );
  				
  				// Multiple users inheriting one parent user.
  				$acl->inherit( array( 'Student', 'Teacher' ), 'Basic User' );
  				
  				// One user inheriting multiple parent users.
  				$acl->inherit
				( 
					'Administrator', 
					array( 'User', 'Student', 'Teacher' ) 
				);
				
				// Multiple users inheriting from multiple parent users.
  				$acl->inherit
				( 
					array( 'Administrator', 'Teacher', 'Student' ), 
					array( 'Guest', 'Basic User' ) 
				);
			(end)
			
			See Also:
				- <Legato_ACL::uninherit()>
  		*/
		public function inherit()
		{

			$args = func_get_args();
			
			// If no user is passed in, call inherit with the default user
			if ( count( $args ) == 1 )
				$this->inherit( $this->_user, $args[0] );
				
			list( $user, $parent ) = $args;

			// If it is an array of users, call inherit for each user
			if ( is_array( $user ) )
			{
			
				foreach ( $user as $value )
					$this->inherit( $value, $parent );
					
				return;
			
			}
			// If it is an array of parents, call inherit for each parent
			elseif ( is_array( $parent ) )
			{
				
				foreach ( $parent as $value )
					$this->inherit( $user, $value );
					
				return;
			
			}
			
			// Process.
			if ( $this->_acl[$user]['Inherit'] )
				$this->_process( $user, 'Inherit' );
							
			// Skip if the user already inherits from the parent
			if ( $this->_acl[$user]['_Inherit'][$parent] !== true )
				$this->_acl[$user]['_Inherit'][$parent] = true;
			
		}
		
		
		/*
  			Function: uninherit()
  			The user(s) passed in get their uninheritance removed from the parent(s) passed in. 
			Can be called without a user passed in and gets set to the default user.
  				
  			Syntax:
  				void uninherit( mixed $parent )
  				
  				void uninherit( mixed $user, mixed $parent )
  				
  			Parameters:
  				*With Default User Defined*
  				
  				mixed $parent - The parent(s) for the default user to uninherit from.
  				
  				*On Non-Default User*
  				
  				mixed $user - The user(s) to uninherit from the parent(s).
  				mixed $parent - The parent(s) to uninherit from.
  										
  			Examples:
  			(begin code)
  				// Using uninherit with a default user.
  				$acl->set_default( 'Teacher' );
  				$acl->uninherit( 'User' );
  				$acl->uninherit( array( 'Guest', 'Teacher' ) );
  				
  				// Uninheriting a single user.
  				$acl->uninherit( 'Administrator', 'User' );
  				
  				// Multiple users uninheriting one parent user.
  				$acl->uninherit( array( 'Student', 'Teacher' ), 'Basic User' );
  				
  				// One user uninheriting multiple parent users.
  				$acl->uninherit
				( 
					'Administrator', 
					array( 'User', 'Student', 'Teacher' ) 
				);
				
				// Multiple users uninheriting from multiple parent users.
  				$acl->uninherit
				( 
					array( 'Administrator', 'Teacher', 'Student' ), 
					array( 'Guest', 'Basic User' ) 
				);
  			(end)
  			
  			See Also:
  				- <Legato_ACL::inherit()>
  		*/
		public function uninherit()
		{

			$args = func_get_args();
			
			// If no user is passed in, call uninherit with the default user
			if ( count( $args ) == 1 )
			{
				$this->uninherit( $this->_user, $args[0] );
				return;
			}
				
			list( $user, $parent ) = $args;

			// If an array of users is passed in, call uninherit with each user
			if ( is_array( $user ) )
			{
			
				foreach ( $user as $value )
					$this->uninherit( $value, $parent );
					
				return;
			
			}
			// If an array of parents is passed in, call uninherit with each parent
			else if ( is_array( $parent ) )
			{
				
				foreach ( $parent as $value )
					$this->uninherit( $user, $value );
					
				return;
			
			}
			
			// Process.
			if ( $this->_acl[$user]['Inherit'] )
				$this->_process( $user, 'Inherit' );
				
			// Skip if this user does not currently inherit from the parent.
			if ( $this->_acl[$user]['_Inherit'][$parent] === true )
				unset( $this->_acl[$user]['_Inherit'][$parent] );
			
		}
		
		
		/*
  			Function: check()
  			Takes in a user and a permission and returns true if the user is allowed that permission or 
			false if the user is denied or is undefined. If no user is passed in, it checks against 
			the default user.
  				
  			Syntax:
  				bool check( string $permission )
  				
  				bool check( string $user, string $permission )
  				
  			Parameters:
  				*With Default User Defined*
  				
  				string $permission - The permission to check to see if the default user has.
  				
  				*On Non-Default User*
  				
  				string $user - The user to check the permission on. 
  				string $permission - The permission to check to see if the user has.
  				
  			Returns:
  				False if the user does not have that permission, true if the user does.
  										
  			Examples:
  			(begin code)
				$acl->set_default( 'Teacher' );
				
				// Check a permission on the default user.
				if ( !$acl->check( 'Upload School Logo' ) )
					echo 'User is denied permission!';
				
				// Check permission on a user.
				if ( $acl->check( 'Student', 'Upload Student Information' ) )
					echo 'User has permission!';
			(end)
			
			See Also:
				- <Legato_ACL::allow()>
				- <Legato_ACL::deny()>
  		*/
		public function check()
		{

			$args = func_get_args();

			// If no user is passed in, call check() with the default user
			if ( count( $args ) == 1 )
				return $this->check( $this->_user, $args[0] );
				
			list( $user, $permission ) = $args;
			
			// Process.
			if ( !$this->_acl[$user]['_Allow'][$permission] && $this->_acl[$user]['Allow'] )
				$this->_process( $user, 'Allow' );
				
			// Does the permission exist in the allow list for the user?
			if ( $this->_acl[$user]['_Allow'][$permission] === true )
				return true;
				
			// Process.
			if ( !$this->_acl[$user]['_Deny'][$permission] && $this->_acl[$user]['Deny'] )
				$this->_process( $user, 'Deny' );
				
			// Does the permission exist in the deny list for the user?
			if ( $this->_acl[$user]['_Deny'][$permission] === true )
				return false;
				
			// Process.
			if ( $this->_acl[$user]['Inherit'] )
				$this->_process( $user, 'Inherit' );
			
			// Call check() on each parent, and if they do not return null, return that value
			foreach ( $this->_acl[$user]['_Inherit'] as $parent => $none )
			{
				
				$value = $this->check( $parent, $permission );

				if ( $value === true )
					return true;

				if ( $value === false )
					return false;

			}
			
			return null;

		}
		
		
		/*
  			Function: _process()
  			Processes the input given. Explodes the string into an array and stores it.
  		*/
		protected function _process( $user, $type )
		{
			
			// Explode the string.
			$this->_acl[$user][('_' . $type)] = explode( Legato_ACL::SPACER, $this->_acl[$user][$type] );
			
			// Fill the keys with the values.
			$this->_acl[$user][('_' . $type)] = array_fill_keys( $this->_acl[$user][('_' . $type)], true );
			
			// Unset the string.
			unset( $this->_acl[$user][$type] );

		}

	}