<?php

	//--------------------------------------------------------------------------
	// Name: LW_Controller_HelperManager
	// Desc: Manages the loading of the helpers.
	//--------------------------------------------------------------------------
	class LW_Controller_HelperManager
	{		
		
		//------------------------------------------------------------------------
		// Private Variables
		//------------------------------------------------------------------------
		private $helpers = array();


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
		// Name: __get()
		// Desc: Used to allow the user to try to get a helper when it's not
		//       really loaded yet.
		//------------------------------------------------------------------------
		public function __get( $helper_name )
		{
			
			// First, check to see if this helper is already instantiated.
			if ( $this->helpers[$helper_name] != '' )
				return $this->helpers[$helper_name];
			
			// Get the correct filename and classname for this helper.
			$filename = ROOT . '/application/helpers/' . strtolower( $helper_name ) . '.help.php';
			$classname = ucfirst( $helper_name ) . 'Helper';
			
			// Check to make sure the file exists for this helper.
			if ( !file_exists( $filename ) )
			{
				LW_Debug_Debugger::add_item( 'Could not load the helper. No file could be found with that name.' );
				return false;
			}
			
			// Let's include it and instantiate a new one.
			include( $filename );
			$this->helpers[$helper_name] = new $classname();
			
			// Finally, return it.
			return $this->helpers[$helper_name];
			
		}

	}
	

	//--------------------------------------------------------------------------
	// Name: LW_Controller_Helper
	// Desc: A helper class. Used to create reusable actions (aka helpers).
	//--------------------------------------------------------------------------
	class LW_Controller_Helper extends LW_Controller_Abstract
	{
		
		/* Nothing Yet */
		
	}

?>