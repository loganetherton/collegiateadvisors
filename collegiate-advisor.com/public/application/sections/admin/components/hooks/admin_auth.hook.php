<?php

	class AdminAuthHook extends Legato_Hook
	{
		
		public function pre_system()
		{
			
			// Set the advisor global to false initially.
			$GLOBALS['admin'] = false;
			
			// Create new cookie.
			$auth = new Legato_Authentication();
			
			// Validate.
			if ( $auth->validate() )
			{
				
				$id = $auth->get_userid();
				 
				// If everything went okay, then get the advisor.
				$GLOBALS['admin'] = new Admin( $id );
				
				if( $GLOBALS['admin']->advisor_id )
				{
					$GLOBALS['advisor_data'] = new Advisor( $GLOBALS['admin']->advisor_id );
				}
				
				// Set the cookie again.
				$auth->set( $id );
				
			}
			
		}
		
	}