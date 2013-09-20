<?php	

	//--------------------------------------------------------------------------
	// Class: LW_Hook
	// Used to hook user-defined methods into the system.
	//--------------------------------------------------------------------------
	abstract class LW_Hook
	{
		
		public function pre_system() {}
		public function pre_controller() {}
		public function display( $output ) { return $output; }
		public function post_controller() {}
		public function post_system() {}
		
	}

?>