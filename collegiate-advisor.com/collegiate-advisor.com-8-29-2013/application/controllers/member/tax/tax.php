<?php

	/* Tax Section Controller */
	class TaxController extends Legato_Controller
	{

		public function __construct()
		{

			parent::__construct();

			if ( $GLOBALS['user'] == false)
			{
				header( 'Location: '. SITE_URL .'/login' );
				return;
			}

			$this->assign( 'page', 'member_tax' );

		}


		public function index()
		{

			$this->layout->title = 'Tax Information - ';
			$this->assign( 'sub_page', 'member_tax_index' );

			$this->render_view( 'member/tax/menu' );
			$this->render_view( 'member/tax/index' );

		}


		public function opportunity()
		{

			$this->layout->title = 'The American Opportunity Tax Credit - ';
			$this->assign( 'sub_page', 'member_tax_opportunity' );

			$this->render_view( 'member/tax/menu' );
			$this->render_view( 'member/tax/opportunity' );

		}


		public function lifetime_learning()
		{

			$this->layout->title = 'Lifetime Learning Tax Credit - ';
			$this->assign( 'sub_page', 'member_tax_lifetime_learning' );

			$this->render_view( 'member/tax/menu' );
			$this->render_view( 'member/tax/lifetime_learning' );

		}


		public function tuition_fees()
		{

			$this->layout->title = 'Tuition and Fees Tax Deduction - ';
			$this->assign( 'sub_page', 'member_tax_tuition_fees' );

			$this->render_view( 'member/tax/menu' );
			$this->render_view( 'member/tax/tuition_fees' );

		}

	}

?>