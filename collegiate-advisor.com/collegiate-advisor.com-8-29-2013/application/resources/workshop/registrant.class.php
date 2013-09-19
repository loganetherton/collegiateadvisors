<?php
  
	//--------------------------------------------------------------------------
	// Name: Workshop_Registrant
	// Desc: A class used to encapsulate all the common operations done with a
	//       workshop registrant.
	//--------------------------------------------------------------------------
	class Workshop_Registrant extends Legato_Resource
	{
	
		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------ 
		//------------------------------------------------------------------------
		// Name: get_num_registrants()
		// Desc: Returns the number of all the registrants for a particular
		//       workshop.
		//------------------------------------------------------------------------ 
		public static function get_num_registrants( $registrants )
		{

			if ( $registrants == NULL )
				return 0;
				
			$total = count( $registrants );

			foreach ( $registrants as $registrant )
				$total += $registrant->get( 'guests' ) ? count( $registrant->get('guests') ) : 0;

			return $total;
			
		}

	}

?>