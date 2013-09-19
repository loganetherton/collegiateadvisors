<?php

	class RetirementController extends Legato_Controller
	{
		
		private $plugins = array();
		
		public function __construct()
		{
			
			parent::__construct();
			
			$this->plugins = $GLOBALS['advisor']->get( 'plugins' );		
			$this->assign( 'plugins', $plugins );
			
			if ( $this->plugins['retirement'] == false ) {
				header( 'Location: '. SITE_URL .'/' );
				exit();
			}
			
			$this->assign( 'business_name', $GLOBALS['advisor']->get( 'business_name' ) );
			
		}


		public function index()
		{
			
			$this->assign( 'page', 'retirement_index' );
			$this->layout->title = 'Retirement Planning - ';
			$this->render_view( 'retirement/index' );
			
		}


		public function services()
		{
			
			$this->assign( 'page', 'retirement_services' );
			$this->layout->title = 'Retirement Services We Offer - ';
			$this->render_view( 'retirement/services' );
			
		}
		

		public function iras()
		{
					
			$this->assign( 'page', 'retirement_iras' );
			$this->layout->title = 'Tradiational vs. Roth IRAs - ';
			$this->render_view( 'retirement/iras' );
			
		}


		public function life_insurance()
		{
					
			$this->assign( 'page', 'retirement_life_insurance' );
			$this->layout->title = 'Life Insurance - ';
			$this->render_view( 'retirement/life_insurance' );
			
		}
		
		
		public function p401k()
		{
			
			$this->assign( 'page', 'retirement_401k' );
			$this->layout->title = '401k - ';
			$this->render_view( 'retirement/401k' );
			
		}
		
		
		public function p529()
		{			
			
			$this->assign( 'page', 'retirement_529' );
			$this->layout->title = '529 - ';
			$this->render_view( 'retirement/529' );
			
		}

	}

?>