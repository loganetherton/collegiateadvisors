<?php

	/*
 		Class: Legato_Filter
		This class manages filtering of data. It provides a wrapper for htmlpurifier (http://htmlpurifier.org) and 
		popoon (http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php) to ensure secure XSS filtering 
		of your data. All methods can be called statically.
	*/
	class Legato_Filter
	{
		
		/* 
			Group: Constants
		*/

		/*
			Const: DEFAULT_METHOD
			*string* The default filtering method. Defaults to 'popoon'.
  		*/
		const DEFAULT_METHOD = 'popoon'; // The Default Filtering method


		/*
			(Exclude)
			
			Var: $_htmlpurifier
			The HTMLPurifier filtering object, stored so that it does not need to be instantiated each time you filter.
		*/
		protected static $_htmlpurifier = null; // Store the htmlpurifier object for multiple uses
		
		
		/*
			Group: Functions
		*/
		
		/*
			(Exclude)
			Constructor: __construct()
			Does nothing.
		*/
		private function __construct()
		{

			// Do Nothing

		}


		/*
  			Function: clean()
  			Takes in a string that needs to be filtered, the method to use for filtering, and the configuration array for htmlpurifier.
  				
  			Syntax:
  				mixed clean( mixed $dirty [, string $method = Legato_Filter::DEFAULT_METHOD [, array $config = array() ] ] )
  				
  			Parameters:
  				mixed $dirty - The string or array of strings to be filtered.
  				string $method - *optional* - The filtering method to use (currently htmlpurifier or popoon).
				array $config - *optional* - The HTMLPurifier configuration array that will be used to configure HTMLPurifier. 
											 Format - array( 'Namespace' => array( 'Directive' => 'Value' ) ) 
  				
  			Returns:
  				Filtered string or array of filtered strings.
  										
  			Examples:
  			(begin code)
  				$clean = Legato_Filter::clean( $dirty );
  				$clean = Legato_Filter::clean( $dirty, 'htmlpurifier', array( 'HTML' => array( 'Allowed' => 'a,p,strong,em' ) ) );
  				
  				// Cleaning multiple data strings.
  				$data = array
  				(
  					'filter this',
  					'filter this also!'
  				);
  				
  				$clean_data = Legato_Filter::clean( $data );
  			(end)
  		*/		
		public static function clean( $dirty, $method = Legato_Filter::DEFAULT_METHOD, $config = array() )
		{
			
			// If an array as passed in, call clean on each element in the array
			if ( is_array( $dirty ) )
			{
			
				foreach ( $dirty as $key => $value )
					$clean[$key] = self::clean( $value, $method, $config );	
			
				return $clean;
					
			}
			
			// If the method is set to default, try and access the Settings filter method
			$method = ( $method == Legato_Filter::DEFAULT_METHOD ) ? Legato_Settings::get( 'filter', 'method' ) : $method;
			 
			// Call the htmlpurifier clean method 
			if ( $method == 'htmlpurifier' )
			 	return self::htmlpurifier( $dirty, $config );
			else if ( $method == 'popoon' )
				return self::popoon( $dirty );
			
			
			
		}
		
		
		/*
			(Exclude)
  			Function: htmlpurifier()
  			A wrapper function for the htmlpurifier filter.
  		*/
		private static function htmlpurifier( $dirty, $config = array() )
		{
			
			// License located at ../../packages/htmlpurifier/LICENSE
			// http://htmlpurifier.org
		
			// Include HTMLPurifier
			require_once( dirname( dirname( __FILE__ ) ) . '/packages/htmlpurifier/HTMLPurifier.standalone.php' );
			
			// Get the HTMLPurifier Configuration
			$purifier_config = HTMLPurifier_Config::createDefault();
			
			// Set HTMLPurifier Tidy Default to None
			$purifier_config->set( 'HTML', 'TidyLevel', 'none' );
			
			// Set the Defaults
			if ( count( $config ) > 0 )
				foreach ( $config as $namespace => $options )
					foreach ( $options as $directive => $value )
						$purifier_config->set( $namespace, $directive, $value );
			
			// Get the HTMLPurifier Object
			if ( self::$_htmlpurifier == null )
				self::$_htmlpurifier = new HTMLPurifier();
			
			// Add the config to the HTMLPurifier object
			self::$_htmlpurifier->config = $purifier_config;
			
			// Purify
			return self::$_htmlpurifier->purify( $dirty );		
			
		}
		
		
		/*
			(Exclude)
  			Function: popoon()
  			A wrapper function for the popoon filter.
  		*/
		private static function popoon( $dirty )
		{
			
			// +----------------------------------------------------------------------+
			// | popoon                                                               |
			// +----------------------------------------------------------------------+
			// | Copyright (c) 2001-2006 Bitflux GmbH                                 |
			// +----------------------------------------------------------------------+
			// | Licensed under the Apache License, Version 2.0 (the "License");      |
			// | you may not use this file except in compliance with the License.     |
			// | You may obtain a copy of the License at                              |
			// | http://www.apache.org/licenses/LICENSE-2.0                           |
			// | Unless required by applicable law or agreed to in writing, software  |
			// | distributed under the License is distributed on an "AS IS" BASIS,    |
			// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
			// | implied. See the License for the specific language governing         |
			// | permissions and limitations under the License.                       |
			// +----------------------------------------------------------------------+
			// | Author: Christian Stocker <chregu@bitflux.ch>                        |
			// +----------------------------------------------------------------------+
			// http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
			
			$dirty = str_replace( array( "&amp;", "&lt;", "&gt;" ), array( "&amp;amp;", "&amp;lt;", "&amp;gt;", ), $dirty );
	        // fix &entitiy\n;
	        
	        $dirty = preg_replace( '#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $dirty );
	        $dirty = preg_replace( '#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $dirty );
	        $dirty = html_entity_decode( $dirty, ENT_COMPAT, "UTF-8" );
	        
	        // remove any attribute starting with "on" or xmlns
	        $dirty = preg_replace( '#(<[^>]+[\x00-\x20\"\'])(on|xmlns)[^>]*>#iUu', "$1>", $dirty);
	        
			// remove javascript: and vbscript: protocol
	        $dirty = preg_replace( '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $dirty );
	        $dirty = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $dirty );
	        $dirty = preg_replace( '#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $dirty );
	        
			//<span style="width: expression(alert('Ping!'));"></span> 
	        // only works in ie...
	        $dirty = preg_replace( '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^>]*>#iU', "$1>", $dirty );
	        $dirty = preg_replace( '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^>]*>#iU', "$1>", $dirty );
	        $dirty = preg_replace( '#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu', "$1>", $dirty );
	        
			//remove namespaced elements (we do not need them...)
	        $dirty = preg_replace( '#</*\w+:\w[^>]*>#i', "", $dirty );
	        
			//remove really unwanted tags
	        do {
	            $oldstring = $dirty;
	            $dirty = preg_replace( '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $dirty );
	        } while ( $oldstring != $dirty );

			return $dirty;
			
		}

	}