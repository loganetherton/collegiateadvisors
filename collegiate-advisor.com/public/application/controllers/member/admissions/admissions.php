<?php

	class AdmissionsController extends Legato_Controller
	{

		function __construct()
		{
			
			parent::__construct();
			
			if ( $GLOBALS['user'] == false )
			{
				header( 'Location: ' . SITE_URL . '/login' );
				return;
			}

			$this->assign( 'page', 'member_admissions' );
			
		}


		function assistance()
		{
			
			$this->layout->title = 'Admissions Assistance - ';
			$this->assign( 'sub_page', 'member_admissions_assistance' );

			$this->render_view( 'member/admissions/menu' );
			$this->render_view( 'member/admissions/assistance' );

		}


		public function essay()
		{
			
			$this->layout->title = 'How to Write an Admissions Essay - ';
			$this->assign( 'sub_page', 'member_admissions_essay' );

			$this->render_view( 'member/admissions/menu' );
			$this->render_view( 'member/admissions/essay' );
			
		}


		public function interview_prep()
		{
			
			$this->layout->title = 'Preparing for Your Interview - ';
			$this->assign( 'sub_page', 'member_admissions_interview_prep' );

			$this->render_view( 'member/admissions/menu' );
			$this->render_view( 'member/admissions/interview_prep' );
			
		}


		public function upload()
		{
			
			$this->layout->title = 'Upload an Essay - ';
			$this->assign( 'sub_page', 'member_admissions_upload' );
			$this->render_view( 'member/admissions/menu' );
			
			$form = new MemberAdmissionsEssayForm();

			// Does it validate?
			if ( !$form->validate() )
			{
				$this->assign( 'form', $form );
			}
			else
			{
				$essay = $form->essay->value;

				$destination = ROOT . '/private_files/advisors/' . $GLOBALS['advisor']->get( 'id' );
				
				// If the target directory does not exist, then create it
				if ( !is_dir( $destination ) ) 
					mkdir( $destination );
					
				$destination .= '/essays/';
					
				// If the target directory does not exist, then create it
				if ( !is_dir( $destination ) ) 
					mkdir( $destination );
				
				$filename = $GLOBALS['user']->get( 'id' ) . '_' . time() . '_' . $essay['name'];

				move_uploaded_file( $essay['tmp_name'], $destination . $filename );
				
				EmailHelper::send_essay_email( $destination . $filename );

				$this->render_view( 'member/admissions/upload_confirmation' );

				return;
			}
			
			$this->render_view( 'member/admissions/upload' );
		}

	}

?>