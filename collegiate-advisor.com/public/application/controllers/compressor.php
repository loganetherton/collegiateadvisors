<?php

	class CompressorController extends Legato_Controller
	{	
		
		public function index()
		{
			
			Legato_Compressor::output();
			
		}
		
		
		public function css_common()
		{
			
			Legato_Compressor::output( 'CSS_Common' );
			
			$this->assign( 'style', new Style( $GLOBALS['advisor']->get( 'style_id' ) ) );
			$this->render_view( 'custom_css' );
			
		}

	}

?>