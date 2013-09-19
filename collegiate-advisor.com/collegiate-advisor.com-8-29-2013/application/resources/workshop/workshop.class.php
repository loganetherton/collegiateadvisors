<?php

	//--------------------------------------------------------------------------
	// Name: Workshop
	// Desc: A class used to encapsulate all the common operations done with an
	//       workshop.
	//--------------------------------------------------------------------------
	class Workshop extends Legato_Resource
	{

		//------------------------------------------------------------------------
		// Public Static Variables
		//------------------------------------------------------------------------
		public static $current_workshops  = array();


		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: get_current_workshops()
		// Desc: Returns the current workshops.
		//------------------------------------------------------------------------
		public static function get_current_workshops()
		{
			
			if ( !self::$current_workshops )
			{
			
				// Get the current workshops.
				self::$current_workshops = Legato_Resource::order_by( 'time', 'asc' )->
				                                        fetch( 'Workshop', array( 'advisor_id' => $GLOBALS['advisor']->get( 'id' ),
														                          'time >' => time() ) );														                          
			}

			// Return the current workshops.
			return self::$current_workshops;

		}

	}

?>