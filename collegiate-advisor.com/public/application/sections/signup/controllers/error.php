<?php

	class ErrorController extends Legato_Controller
	{
		
		public function _error400()
		{
			
			$this->layout->title = 'Bad Request';
			$this->render_view( 'errors/400' );
			
		}
		
		public function _error403()
		{
			
			$this->layout->title = 'Access Denied';
			$this->render_view( 'errors/403' );
			
		}
		
		public function _error404()
		{
			
			$this->layout->title = 'Not Found';
			$this->render_view( 'errors/404' );
			
		}
		
	}