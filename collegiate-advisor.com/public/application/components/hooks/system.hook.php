<?php

	class SystemHook extends Legato_Hook
	{
		
		public function pre_system()
		{
			
			// Let's get the namespace from the server name.
			preg_match_all( '/([\w-])+./', $_SERVER['SERVER_NAME'], $matches );
			
			$matches = $matches[0];			
			if ( in_array( $matches[(count( $matches ) - 1)], array( 'com', 'org', 'net' ) ) )
				array_pop( $matches );
				
			$namespace = substr( $matches[(count( $matches ) - 2)], 0, -1 );
			
			// Some defines that we need in the pages.
			define( 'DOMAIN', 'http://' . $namespace . '.localhost' );
			define( 'SUBDOMAIN', $namespace );
			define( 'NAMESPACE', $namespace );
			
		}
		
	}