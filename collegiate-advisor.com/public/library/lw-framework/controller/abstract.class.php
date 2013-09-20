<?php

	//--------------------------------------------------------------------------
	// Name: LW_Controller_Abstract
	// Desc: An abstract base class for a controller and controller helper.
	//--------------------------------------------------------------------------
	class LW_Controller_Abstract
	{
		
		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $data = array();  // The data used in the view.


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: Class constructor.
		//------------------------------------------------------------------------
		public function __construct()
		{
			
			/* Nothing Yet */
			
		}
		
		
		//------------------------------------------------------------------------
		// Name: __destruct()
		// Desc: Class destructor.
		//------------------------------------------------------------------------
		public function __destruct()
		{
			
			/* Nothing Yet */
			
		}
		
			
		//------------------------------------------------------------------------
		// Name: render_view()
		// Desc: Renders a view on to the screen.
		//------------------------------------------------------------------------
		public function render_view( $view, $data = array() )
		{
			
			// Merge the data arrays.
			$data = array_merge( $data, $this->data );
			
			// Transform the data array into variables.
			foreach ( $data as $data_item_key => $data_item_value )
			{

				$$data_item_key = $data_item_value;

			}  // Next data item.

			// Include the view.
			include ( ROOT . LW_Settings::get( 'stage', 'views_folder' ) . '/' . $view . '.phtml' );

		}
		
		
		//------------------------------------------------------------------------
		// Name: assign()
		// Desc: Assigns a variable that can be used in the view.
		//------------------------------------------------------------------------
		public function assign( $key, $value = NULL )
		{

			// Was a string passed in for the key, or an array?
			if ( is_array( $key ) )
			{
				
				// Loop through all the data and store it.
				foreach ( $key as $item_key => $item_value )
					$this->assign( $item_key, $item_value );
					
			}
			else if ( is_string( $key ) )
				$this->data[$key] = $value;

		}

	}

?>