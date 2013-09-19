<?php

	/*
		Class: Legato_View
		A base view class for the MVC system.
	*/
	class Legato_View
	{
		
		/* 
			Group: Variables
			
			Var: $filename
			*string* The filename of the view template to render.
			
			Var: $layout
			*object* An instance of the <Legato_Layout> object.
		*/
		
		public $filename = '';		
		protected $layout = null;
		
		
		/* 
			(Exclude)
			
			Var: $_data
			The data to use in the view.
		*/
		
		protected $_data = array();
		
		
		/*
			Group: Functions
		*/		
						
		/*
			Constructor: __construct()
			Class constructor.
		
			Syntax:
				void __construct( [ string $filename = '' ] [, array $data = array() ] ) )
		
			Parameters:
				string $filename - *optional* - The filename to render without .phtml extension.
				array $data - *optional* - An array of data that will be passed in to the view to be used by the view. 'key' becomes $key inside the view.
								
			Examples:
			(begin code)
				// Get a new view object and set some variables on it.
				$v = new Legato_View( '/path/to/view', array( 'key' => 'value' ) );
				$v->new_key = 'value';
				
				// Render out the view with some more data.
				$v->render( array( 'more_data' => 'var' );
			(end)
			
			See Also:
				<Legato_Controller>
		*/
		public function __construct( $filename = '', $data = array() )
		{
			
			// Set up the view.
			$this->layout = Legato_Layout::instance();
			$this->filename = $filename;
			$this->_data = $data;
			
		}
		
		
		/*
  			Function: render()
  			Renders a view to the screen passing in the given data as variables.
  				
  			Syntax:
  				void render( [ array $data = array() ] )
  				
  			Parameters:
  				array $data - *optional* - The data to pass into the view. Array is extracted for the variables in the view.
  				
  			Notes:
  				Note that any data passed directly in to the render() function will only be available during that particular 
				render, and not if you render the view again later on.
  										
  			Examples:
  				>	$v->render( array( 'varname' => 'value' ) );
  		*/
		public function render( $data = array() )
		{
			
			// Merge the data arrays.
			$data = array_merge( $this->_data, $data );
			
			// Transform the data array into variables.
			extract( $data );

			// Include the view.
			include ( ROOT . Legato_Settings::get( 'stage', 'views_folder' ) . '/' . $this->filename . '.phtml' );

		}
		
		
		/*
			(Exclude)
			Function: __set()
			Lets the user add data to the view.
		*/
		public function __set( $key, $value )
		{
			
			if ( !isset( $this->$key ) )
				$this->_data[$key] = $value;
			
		}
		
		
		/*
			(Exclude)
			Function: __get()
			Lets the user get data from the view.
		*/
		public function __get( $key )
		{
			
			if ( isset( $this->_data[$key] ) )
				return $this->_data[$key];
			
		}

	}