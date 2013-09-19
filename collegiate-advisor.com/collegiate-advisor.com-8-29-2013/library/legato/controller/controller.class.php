<?php

	/*
		Class: Legato_Controller
		A base controller class for the MVC system.
		This is what you should extend your controllers from.
	*/
	class Legato_Controller
	{

		/* 
			Group: Variables
			
			Var: $request
			*array* An array filled with information about the request made.
			
			Var: $layout
			*object* An instance of the <Legato_Layout> object.
		*/

		public $request = array();
		public $layout = null;
		
		
		/* 
			(Exclude)
			
			Var: $_data
			The data used in the view.
		*/
		
		private $_data = array();
		
		
		/*
			Group: Functions
		*/

		/*
			(Exclude)
			Constructor: __construct()
			The classes constructor. Does nothing yet.
		*/
		public function __construct()
		{
			
			// Get an instance of the layout.
			$this->layout = Legato_Layout::instance();
			
		}
		
		
		/*
			Function: _layout()
			The default delegation for setting up and rendering the layout.
			Note that if you're extending this class for a section index controller, you can override this
			function and it will be called to set up and render the layout.
			
			Notes:
				This is called automatically by the <Legato_Stage> class if the <Legato_Stage::show_layout> setting
				is set to true.
				
			Examples:
			(begin code)
				class IndexController extends Legato_Controller
				{
					public function _layout()
					{
						
						// This delegation will be called automatically if the
						// Legato_Stage::show_layout setting is set to true.
						
						$this->layout->title = $this->layout->title . ' | Legato';
						$data = array( 'some_data' => 'some_value' );
						$this->render_view( 'special_layout', $data );
						
					}
					
					public function index()
					{
						// Do something.
					}				
				}
			(end)
				
			See Also:
				- <Controllers-Delegations>
				- <Legato_Stage::delegate()>
		*/
		public function _layout()
		{
			
			$this->render_view( Legato_Settings::get( 'stage', 'layout_view' ) );
			
		}
		

		/*
			Function: render_view()
			Sets up a <Legato_View> object and renders it. This is a convenience method so you
			don't have to take care of setting up a view and rendering it yourself.
			
			Syntax:
				void render_view( string $view_filename [, array $data = array()] )
				
			Parameters:
				string $view_filename - The filename of the view to be rendered, without the .phtml file extension.
				array $data - *optional* - The array of data to be passed in to the view.
								
			Examples:
			(begin code)
				class IndexController extends Legato_Controller
				{
					public function index()
					{
						$data = array
						(
							'var1' => 'value1',
							'var2' => 'value2'
						);
						
						$this->render_view( 'index', $data );
					}					
				}
			(end)
			
			See Also:
				- <Legato_View>
		*/
		protected function render_view( $view_filename, $data = array() )
		{
			
			// Set up a new view.
			$view = new Legato_View( $view_filename, array_merge( $this->_data, $data ) );
			$view->render();

		}
		
		
		/*
			Function: assign()
			Assigns a variable to be used in any view rendered by the controller. Any variables
			assigned with this function will be passed in to all views rendered by this controller.
			
			Syntax:
				void assign( string $variable, mixed $value )
				
				void assign( array $data )
				
			Parameters:
				string $key - The variable that will be passed in to the view.
				mixed $value - The value that the passed in variable will have.
				
				OR
				
				array $data - An array of data in which the keys are the variables to assign and the values are the values of the variables.
								
			Examples:
			(begin code)
				class IndexController extends Legato_Controller
				{
					public function index()
					{
						// We can assign a single variable.
						$this->assign( 'var1', 'value1' );
						
						$data = array
						(
							'var2' => 'value2',
							'var3' => 'value3'
						);
						
						// Or we can assign an array of variables.
						$this->assign( $data );
						
						// The data that was assigned will be accessible to
						// both views rendered.
						$this->render_view( 'index' );
						$this->render_view( 'comments' );
					}					
				}
			(end)
			
			See Also:
				- <Legato_Controller::render_view()>
				- <Legato_View>
		*/
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
				$this->_data[$key] = $value;

		}

	}
