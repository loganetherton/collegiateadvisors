<?php

	/*
		Class: Legato_Layout
		Allows you to store information that you can use in the layout of
		your site.
		
		Notes:
			- This function is attached to every <Legato_Controller> object and every <Legato_View> object
			for easy access to it.
			- You can attach as much data as you'd like to this object. Simply set a variable on the object.
			
		Examples:
			You may want to assign some extra data for the layout.
			
			(begin code)
				class IndexController extends Legato_Controller
				{
					public function index()
					{
						
						$this->layout->title = 'Index Page';
						
						// Maybe Render Some Views
						
					}
					
					public function _layout()
					{
						
						$this->layout->page = str_replace
						(
							'/', '_', $this->request['generated_uri']
						);
						
						$this->render_view( 'layout' );
						
					}	
				}
			(end)
			
			And you may have a layout.phtml file to render out the layout.
			
			(begin code)
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

				<html xmlns="http://www.w3.org/1999/xhtml">
				
					<head>
					
						<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />	
						<title><?php echo $this->layout->title; ?></title>
						
					</head>
				
					<body id="<?php echo $this->layout->page; ?>">
					
						<?php echo $this->layout->content; ?>
					
					</body>
					
				</html>
			(end)
	*/
	class Legato_Layout
	{
		
		/*
			Group: Variables
			
			Var: $title
			*string* You can store a title for your pages in this variable.
			
			Var: $content
			*string* This is populated by the <Legato_Stage> component right before calling the <Legato_Controller::_layout()>
			delegation with the content that was outputted from the requested action. This allows you to then render the content
			output in the layout view.		
		*/	
		
		public $title = '';		
		public $content = '';
		
		
		/*
			(Exclude)
			
			Var: $_data
			Used to hold any extra data. Populated with the overloaded __set() method.
			
			Var: $_instance
			This is a singleton class, so this is the one single instance of the class.
		*/
				
		private $_data = array();
		private static $_instance = null;
		
		
		/*
			Group: Functions
		*/
		
		/*
			(Exclude)
			Function: __construct()
			The class constructor.
			This is a singleton, so this constructor is called once to store the one single instance.
		*/
		private function __construct()
		{
			
			if ( empty( self::$_instance ) )
				self::$_instance = $this;
			
		}
		
		
		/*
			Function: instance()
			Since this is a singleton class, you can use this function to get an instance
			of the Legato_Layout singleton.
			
			Syntax:
				object instance()
				
			Returns:
				The singleton instance of this class.
								
			Examples:
			(begin code)
				$layout = Legato_Layout::instance();
				echo $layout->title;
			(end)
			
			See Also:
				- <Legato_Controller::$layout>
				- <Legato_View::$layout>
		*/
		public static function instance()
		{
		
			if ( empty( self::$_instance ) )
				new Legato_Layout();
				
			return self::$_instance;
			
		}
		
		
		/*
			(Exclude)
			Function: __get()
			Allows the user to retrieve the data they've stored.
		*/
		public function __get( $property )
		{
			
			return $this->_data[$property];
			
		}
		
		
		/*
			(Exclude)
			Function: __set()
			Allows the user to store their own data in this container.
		*/
		public function __set( $property, $value )
		{
			
			$this->_data[$property] = $value;
			
		}
		
	}