<?php

	class PagesController extends Legato_Controller
	{
		
		public $level;

		public function __construct()
		{
			
			parent::__construct();
			
			if ( !$GLOBALS['admin'] )
			{				
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;				
			}
				
			$this->level = $GLOBALS['admin']->get( 'level' );
			
			if ( $this->level > 1 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}
			
			$this->assign( 'level', $this->level );
			
		}
		

		public function edit( $file = '' )
		{ 
			
			$this->layout->title = 'Edit Page';
			$this->assign( 'page', 'pages/edit' );
			
			// Get the pages information.
			if ( $file == 'index' )
				$filename = 'index';
			else if ( $file == 'about' )
				$filename = 'about';
			else if ( $file == 'contact' )
				$filename = 'contact';
			else if ( $file == 'signup' )
				$filename = 'signup';
			else
			{
				$page = new Advisor_EditablePage( $file );
				$filename = $page->get( 'filename' );
			}
			
			$filename = ROOT . '/application/views/ext/' . $GLOBALS['admin']->get( 'advisor_id' ) . '/' . $filename . '.phtml';
			
			$file_handle = fopen( $filename, 'a+b' );
			$content = @fread( $file_handle, filesize( $filename ) );
			
			fclose( $file_handle );

			$form = new PageForm( $content );
			
			// Does it validate?
			if ( $form->validate() )
			{

	            $content = $_POST['page_content'];
	            
	            $file_handle = fopen( $filename, 'wb' );
	            fwrite( $file_handle, stripslashes( $content ) );
	            fclose( $file_handle );
	            
	            $this->assign( 'uploaded', true );
				
			}
			
			$this->assign( 'form', $form );	
			$this->assign( 'file', $file );		
			$this->render_view( 'pages/edit' );
			
		}

	}

?>