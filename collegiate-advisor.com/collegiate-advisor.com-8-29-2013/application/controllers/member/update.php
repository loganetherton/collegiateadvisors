<?php

	class UpdateController extends Legato_Controller
	{
		
		public function __construct()
		{
			
			parent::__construct();
			
			if ( $GLOBALS['user'] == false )
			{
				$redirect_url = ( trim(SITE_URL) == '' ) ? $_SERVER['REQUEST_URI'] : substr( $_SERVER['REQUEST_URI'], 0, count(SITE_URL) );

				header( 'Location: ' . SITE_URL . '/login' . $redirect_url );
				return;
			}
			
		}
		

		public function index()
		{
			
			$form = new UserInformationForm();
			
			if ( $form->validate() )
			{
				
				$form_data = $form->values();
				
				if ( !trim( $form_data['password'] ) )
					unset( $form_data['password'] );
				else
					$form_data['password'] = md5( $form_data['password'] );
					
				$form_data['birth_date'] = strtotime( $form_data['birth_date_month'] . '/' . $form_data['birth_date_day'] . '/' . $form_data['birth_date_year'] );
				
				Legato_Resource::update( $GLOBALS['user'], $form_data );
				
				$data['updated'] = true;
				
			}
			
			$this->layout->title = 'Update Your Information - ';
			
			$data['form'] = $form;
			
			$this->render_view( 'member/update/menu' );
			$this->render_view( 'member/update/index', $data );
			
		}

	}

?>