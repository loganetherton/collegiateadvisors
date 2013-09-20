<?php
	
	/*
		Class: Legato_Validation
		This class manages simple validation functions. 
		Mainly to help you forget Regular Expressions. 
		Every function returns true or false.
	*/
	class Legato_Validation
	{ 
		
		/*
			(Exclude)
			Constructor: __construct()
			Cannot be called.
		*/
		private function __construct()
		{
		
			// Do Nothing

		}
		
		
		/*
  			Function: alpha()
  			Verifies whether the string is only alpha characters.
  				
  			Syntax:
  				bool alpha( string $str )
  				
  			Parameters:
  				string $str - The string to validate.
  				
  			Returns:
  				True if $str contains just alpha characters, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::alpha( 'leet' ) )
  				>		echo 'Do Something';
  		*/
		public static function alpha( $str )
		{
			
			// Matches all Alpha characters, newlines, and tabs
			return (bool)preg_match( '/^[a-zA-Z\s\t\n\r]+$/', $str );

		}
		
		
		/*
  			Function: alpha_numeric()
  			Verifies whether the string is only alpha characters or numbers.
  				
  			Syntax:
  				bool alpha_numeric( string $str )
  				
  			Parameters:
  				string $str - The string to validate.
  				
  			Returns:
  				True if $str contains just alpha characters or numbers, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::alpha_numeric( '1337leet' ) )
  				>		echo 'Do Something';
  		*/
		public static function alpha_numeric( $str )
		{

			return (bool)preg_match( '/^[a-zA-Z\d\s\t\n\r.-]+$/', $str );

		}
		
		
		/*
  			Function: numeric()
  			Verifies whether the string is only numbers.
  				
  			Syntax:
  				bool numeric( string $str )
  				
  			Parameters:
  				string $str - The string to validate.
  				
  			Returns:
  				True if $str contains just numbers, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::numeric( '1337' ) )
  				>		echo 'Do Something';
  		*/
		public static function numeric( $str )
		{

			return (bool)is_numeric( $str );

		}
		
		
		/*
  			Function: nonzero()
  			Verifies whether the string is a numerical value greater than zero.
  				
  			Syntax:
  				bool nonzero( string $str )
  				
  			Parameters:
  				string $str - The string to validate.
  				
  			Returns:
  				True if $str is a numerical value greater than zero, false if not.
  										
  			Examples:
  				>	if ( Legato_Validation::nonzero( 56 ) )
  				>		echo 'Hurray!';
  		*/
		public static function nonzero( $str )
		{

			return (bool)preg_match( '/^[0\D]*$/', $str );

		}
		
		/*
  			Function: email_address()
  			Verifies whether the string is an email address.
  				
  			Syntax:
  				bool email_address( string $addr )
  				
  			Parameters:
  				string $addr - The string to validate.
  				
  			Returns:
  				True if $addr is a valid email address, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::email_address( 'church@newbeginningscma.org' ) )
				>		echo 'Do Something';  					
  		*/
		public static function email_address( $addr )
		{

			// If valid Email Address return true
			return (bool)preg_match( '/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i', $addr );

		}
		
		
		/*
  			Function: phone_number()
  			Verifies whether the string is a valid phone number.
  				
  			Syntax:
  				bool phone_number( string $num )
  				
  			Parameters:
  				string $num - The string to validate.
  				
  			Returns:
  				True if $num is a valid phone number, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::phone_number( '845-454-2580' ) )
				>		echo 'Do Something';  					
  		*/
		public static function phone_number( $num )
		{
			
			// Remove all non numeric characters
			$num = preg_replace( '/([-.\(\) ]|ext)+/', '', $num );
			
			// If you remove all normal non-numeric characters and their are non-numeric left
			// rturn false
			if ( preg_match( '/\D/', $num ) )
				return false;
			
			// Phone Number too long
			if ( strlen( $num ) > 15 )
				return false;
				
			return true;

		}
		
		
		/*
  			Function: url()
  			Verifies whether the string is a valid url.
  				
  			Syntax:
  				bool url( string $url )
  				
  			Parameters:
  				string $url - The string to validate.
  				
  			Returns:
  				True if $url is a valid url, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::url( 'http://google.com' ) )
				>		echo 'Do Something';  					
  		*/
		public static function url( $url )
		{

			return (bool)parse_url( $url );

		}
		
		
		/*
  			Function: ip_address()
  			Verifies whether the string is a valid ip address.
  				
  			Syntax:
  				bool ip_address( string $addr )
  				
  			Parameters:
  				string $addr - The string to validate.
  				
  			Returns:
  				True if $addr is a valid ip address, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::ip_address( '70.15.96.131' ) )
				>		echo 'Do Something';  					
  		*/
		public static function ip_address( $addr )
		{

			// Must be in the format #.#.#.# where # can be between 1-3 digits
			if ( !preg_match( '/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/', $addr ) )
				return false;
			
			$i = 0;
			$pieces = explode( '.', $addr );
			foreach ( $pieces as $octet )
			{
				
				if ( $octet > 255 )
					return false;
				
				if ( $i == 0 && ( $octet > 224 || $octet == 10 ) )
					return false;
					
				$i++;
				
			}
			
			return true;

		}
		
		
		/*
  			Function: credit_card()
  			Verifies whether the string is a valid credit card number.
  				
  			Syntax:
  				bool credit_card( string $num )
  				
  			Parameters:
  				string $num - The string to validate.
  				
  			Returns:
  				True if $num is a valid credit card number, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::credit_card( '49927398716' ) )
				>		echo 'Do Something';  					
  		*/
		public static function credit_card( $num )
		{
			
			// LUHN Formula
			// http://en.wikipedia.org/wiki/Luhn_algorithm
			
			// Remove all non-numeric characters
			$num = preg_replace( '/\D+/', '', $num );
			
			// Get an array of individual numbers
			$num = preg_split( '//', $num, -1, PREG_SPLIT_NO_EMPTY );
			
			$even = true;
			for ( $i = count( $num ) - 1; $i >= 0; $i-- )
			{
				
				if ( $even )
					$total += $num[$i];
				else 
					$total += ( ( $num[$i] * 2 ) > 9  ) ? $num[$i] * 2 - 9 : $num[$i] * 2;
				
				$even = $even ? false : true;
					
			}
			
			return (bool)( $total % 10 == 0 );
						
		}
		
		
		/*
  			Function: length()
  			Verifies whether the string is a certain length.
  				
  			Syntax:
  				bool length( string $str, int $length )
  				
  			Parameters:
  				string $num - The string to validate.
  				int $length - The length to compare.
  				
  			Returns:
  				True if $str is equal to $length, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::length( 'Hello World', 11 ) )
				>		echo 'Do Something';
  		*/
		public static function length( $str, $length )
		{

			return (bool)( strlen( $str ) == $length );

		}
		
		
		/*
  			Function: maxlength()
  			Verifies whether the string is less than a certain length.
  				
  			Syntax:
  				bool maxlength( string $str, int $maxlength )
  				
  			Parameters:
  				string $num - The string to validate.
  				int $maxlength - The length to compare.
  				
  			Returns:
  				True if $str is less than $maxlength, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::maxlength( 'Hello World', 12 ) )
				>		echo 'Do Something';  					
  		*/
		public static function maxlength( $str, $maxlength )
		{

			return (bool)( strlen( $str ) <= $maxlength );

		}
		
		
		/*
  			Function: minlength()
  			Verifies whether the string is greater than a certain length.
  				
  			Syntax:
  				bool minlength( string $str, int $minlength )
  				
  			Parameters:
  				string $num - The string to validate.
  				int $minlength - The length to compare.
  				
  			Returns:
  				True if $str is greater than $minlength, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::minlength( 'Hello World', 10 ) )
				>		echo 'Do Something';  					
  		*/
		public static function minlength( $str, $minlength )
		{

			// If string is greater then minlength return true
			return ( strlen( $str ) >= $minlength );

		}
		
		
		/*
  			Function: rangelength()
  			Verifies whether the string's length is in a certain range.
  				
  			Syntax:
  				bool rangelength( string $str, int $minlength, int $maxlength )
  				
  			Parameters:
  				string $num - The string to validate.
  				int $minlength - The length to compare.
  				int $maxlength - The length to compare.
  				
  			Returns:
  				True if $str is greater than $minlength and greate than $maxlength, else false.
  										
  			Examples:
  				>	if ( Legato_Validation::rangelength( 'Hello World', 10, 12 ) )
				>		echo 'Do Something';  					
  		*/
		public static function rangelength( $str, $minlength, $maxlength )
		{
			
			// If the string is in the range given, then return true
			return ( (strlen( $str ) >= $minlength) && (strlen( $str ) <= $maxlength) );

		}

	}
