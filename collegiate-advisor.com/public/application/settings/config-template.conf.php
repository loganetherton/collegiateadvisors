<?php

	// --- SITE CONFIG --- //
	
	define( 'ENVIRONMENT_TYPE', 'production' );  // Development or production.

	// Define the domain name for the site.
	define( 'DOMAIN', 'http://localhost' );
	define( 'MAIN_DOMAIN', 'localhost' );
	
	// Define the url to the site. Whatever comes after the domain name.
	// If it's under a certain directory on the site. You can leave this
	// blank if it's under the top-level domain.
	define( 'SITE_URL', '' );

	// Define the root folder.
	define( 'ROOT', dirname( dirname( dirname( __FILE__ ) ) ) );

	// Define the web-accessible folder.
	define( 'SITE_ROOT', ROOT . '/public_html' );

	// Define the location of Legato
	define( 'LEGATO', ROOT . '/library/legato' );
	
	define( 'ENCRYPTION_KEY', 'hgK#@L84&$h8');