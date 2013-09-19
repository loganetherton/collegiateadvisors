<?php

	//--------------------------------------------------------------------------
	// Name: LW_Controller
	// Desc: A base controller class for the MVC system.
	//--------------------------------------------------------------------------
	class LW_Controller extends LW_Controller_Abstract
	{

		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $query_values = array();  // The query values passed in.
		
		
		//------------------------------------------------------------------------
		// Protected Variables
		//------------------------------------------------------------------------
		protected $helpers = null;


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: Class constructor.
		//------------------------------------------------------------------------
		public function __construct()
		{
			
			// Call the parent constructor.
			parent::__construct();
			
			// Set up the controller.
			$this->helpers = new LW_Controller_HelperManager();
			
		}

	}

?>