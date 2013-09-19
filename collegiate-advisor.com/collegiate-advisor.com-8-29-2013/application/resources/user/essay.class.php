<?php

	//--------------------------------------------------------------------------
	// Name: User_Essay
	// Desc: Manages a single resource, as well as functionality to manage
	//       essays.
	//--------------------------------------------------------------------------
	class User_Essay
	{
		
		//------------------------------------------------------------------------
		// Public Variables
		//------------------------------------------------------------------------
		public $advisor_id = '';
		public $user_id = '';
		public $time = '';
		public $filename = '';


		//------------------------------------------------------------------------
		// Public Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: __construct()
		// Desc: The class constructor.
		//------------------------------------------------------------------------
		public function __construct( $advisor_id, $filename )
		{

			
			preg_match( '/^\d+_/', $filename, $user_id );
			preg_match( '/_\d+_/', $filename, $time );
			
			$this->user_id = substr( $user_id[0], 0, -1 );
			$this->time = substr( $time[0], 1, -1 );
			$this->advisor_id = $advisor_id;
			$this->filename = $filename;
			$this->full_filename = ROOT . '/private_files/advisors/' . $advisor_id . '/essays/' . $filename;

		}
		
		
		//------------------------------------------------------------------------
		// Name: display()
		// Desc: Displays the essay.
		//------------------------------------------------------------------------
		public function display()
		{
			
			// Only show it if the file exists.
			if ( file_exists( $this->full_filename ) )
			{

				$buffer = file_get_contents( $this->full_filename );
				
				header( 'Pragma: public' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Length: ' . strlen( $buffer ) );
				header( 'Content-Disposition: attachment; filename="' . $this->filename . '"' );
				
				// Put the correct content type.
				if ( preg_match( '/\.rtf$/', $this->filename ) )
					header( 'Content-Type: application/rtf' );
				else if ( preg_match( '/\.txt$/', $this->filename ) )
					header( 'Content-Type: text/plain' );
				else if ( preg_match( '/\.doc$/', $this->filename ) )
					header( 'Content-Type: application/msword' );
					
				flush();
				
				echo $buffer;

			}

		}
		
		
		//------------------------------------------------------------------------
		// Name: delete()
		// Desc: Deletes the essay.
		//------------------------------------------------------------------------
		public function delete()
		{
			
			// Only delete if the file exists.
			if ( file_exists( $this->full_filename ) )					
				unlink( $this->full_filename );

		}


		//------------------------------------------------------------------------
		// Public Static Member Functions
		//------------------------------------------------------------------------
		//------------------------------------------------------------------------
		// Name: get_essays()
		// Desc: Returns the essays, depending upon the input given.
		//------------------------------------------------------------------------
		public static function get_essays( $advisor_id )
		{
			
			$files = array();
			
			// Get the files in the dir.
			$directory = ROOT . '/private_files/advisors/' . $advisor_id . '/essays/';
			$scan = scandir( $directory );
			
			// Loop through and store them.
			foreach ( $scan as $item )
			{
				if ( is_file( $directory . $item ) )
				{
					
					$files[$item] = new User_Essay( $advisor_id, $item );
				}
			}
			
			// Return the files.
			return $files;

		}

	}

?>