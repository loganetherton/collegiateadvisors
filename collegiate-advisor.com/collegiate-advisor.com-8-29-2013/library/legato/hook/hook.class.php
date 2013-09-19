<?php	

	//--------------------------------------------------------------------------
	// Class: Legato_Hook
	// Used to hook user-defined methods into the system.
	//--------------------------------------------------------------------------
	abstract class Legato_Hook
	{
		
		public function pre_system() {}
		public function pre_controller() {}
		public function display( $output ) { return $output; }
		public function post_controller() {}
		public function post_system() {}
		public function autoload( $classname ) {}
		
	}