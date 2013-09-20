<?php

	class MemberController extends Legato_Controller
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

			$this->layout->title = 'Member Portal - ';
			$this->assign( 'page', 'member_index' );
			$this->render_view( 'member/index' );

		}


		public function private_file()
		{

			$args = func_get_args();

			Legato_Settings::set( 'stage', 'show_layout', false );

			$filename = implode( '/', $args );
			$filename = ROOT . '/private_files/' . $filename . '.pdf';

			// Only show it if the file exists and restrict to only two levels deep.
			if ( file_exists( $filename ) && count( $args ) <= 3 )
			{

				$buffer = file_get_contents( $filename );

				header( 'Pragma: public' );
				header( 'Expires: 0' );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Type: application/pdf' );
				header( 'Content-Length: ' . strlen( $buffer ) );
				header( 'Content-Disposition: attachment; filename="' . $args[(count( $args ) - 1)] . '.pdf"' );

				flush();

				echo $buffer;

			}

		}


		public function career_dimension()
		{

			// Create the encryption object.
			$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $GLOBALS['user']->get( 'username' ) ), 'twofish' );

			// Get the users career dimension username & password.
			$username = $GLOBALS['user']->get( 'mycareer_username' );
			$password = $encryption->decrypt( $GLOBALS['user']->get( 'mycareer_password' ) );

			header( 'Location: http://www.careerdimension.com/login1.cfm?UserName=' . $username . '&Password=' . $password . '&ReturnURL=' . DOMAIN . SITE_URL . '/member&BadLoginURL=http://www.careerdimension.com/register/collegiateadvisors.cfm' );

		}


		public function testgear()
		{

			$this->layout->title = 'Action Needed - ';
			$this->render_view( 'member/testgear' );

		}


		public function testgear_full( $type )
		{

			$this->layout->title = 'Action Needed - ';
			$this->render_view( 'member/testgear-full', array( 'type' => $type ) );

		}


		// This is the action that actually does the login and redirect.
		public function testgear_redirect( $target_url )
		{

			// Turn off the showing of the header and footer.
			Legato_Settings::set( 'stage', 'show_layout', false );

			// Create the encryption object.
			$encryption = new Legato_Encryption( ENCRYPTION_KEY . md5( $GLOBALS['user']->get( 'username' ) ), 'twofish' );

			// Get the users career dimension username & password.
			$username = $GLOBALS['user']->get( 'testgear_username' );
			$password = $encryption->decrypt( $GLOBALS['user']->get( 'testgear_password' ) );
			
			

			// Assign the username and password to the view.
			$this->assign( 'username', $username );
			$this->assign( 'password', $password );
			$this->assign( 'target_url', $target_url );

			$this->render_view( 'member/testgear_redirect' );

		}

		public function best_college_decision()
		{

			$this->layout->title = 'Make the Best College Decision - ';
			$this->assign( 'page', 'member_best_college_decision' );
			$this->render_view( 'member/best_college_decision' );

		}


		public function hs_timeline_sophomore()
		{

			$this->layout->title = 'High School Timeline - Sophomore - ';
			$this->assign( 'page', 'member_hs_timeline' );
			$this->render_view( 'member/hs_timeline_sophomore' );

		}


		public function hs_timeline_junior()
		{

			$this->layout->title = 'High School Timeline - Junior - ';
			$this->assign( 'page', 'member_hs_timeline' );
			$this->render_view( 'member/hs_timeline_junior' );

		}


		public function hs_timeline_senior()
		{

			$this->layout->title = 'High School Timeline - Senior - ';
			$this->assign( 'page', 'member_hs_timeline' );
			$this->render_view( 'member/hs_timeline_senior' );

		}


		public function test_prep()
		{

			$this->layout->title = 'Test Prep - ';
			$this->assign( 'page', 'member_test_prep' );
			$this->render_view( 'member/test_prep' );

		}


		public function tutoring()
		{

			$this->layout->title = 'Tutoring - ';
			$this->assign( 'page', 'member_tutoring' );
			$this->render_view( 'member/tutoring' );

		}


		public function elite_schools()
		{

			$this->layout->title = 'Getting Noticed by Elite Schools - ';
			$this->assign( 'page', 'member_elite_schools' );
			$this->render_view( 'member/elite_schools' );

		}


		public function imperative_financial_aid()
		{

			$this->layout->title = 'Imperative Financial Aid Information - ';
			$this->assign( 'page', 'member_imperative_financial_aid' );
			$this->render_view( 'member/imperative_financial_aid' );

		}


		public function financial_aid_overview()
		{

			$this->layout->title = 'Maixmize Your Financial Aid - ';
			$this->assign( 'page', 'member_financial_aid_overview' );
			$this->render_view( 'member/financial_aid_overview' );

		}


		public function gapping()
		{

			Legato_Settings::set( 'stage', 'show_layout', false );

			$this->layout->title = 'Gapping/Baiting and Switching - ';
			$this->render_view( 'member/gapping' );

		}


		public function test_out()
		{

			$this->layout->title = 'Testing Out of Courses - ';
			$this->assign( 'page', 'member_test_out' );
			$this->render_view( 'member/test_out' );

		}


		public function letters_of_recommendation()
		{

			$this->layout->title = 'Tutoring - ';
			$this->assign( 'page', 'member_letters_of_recommendation' );
			$this->render_view( 'member/letters_of_recommendation' );

		}


		public function sample_recommendation_letter()
		{

			$this->layout->title = 'Letters of Recommendation - ';
			$this->assign( 'page', 'member_letters_of_recommendation' );
			$this->render_view( 'member/sample_recommendation_letter' );

		}


		public function waiting_list()
		{

			$this->layout->title = 'On a School Waiting List? - ';
			$this->assign( 'page', 'member_waiting_list' );
			$this->render_view( 'member/waiting_list' );

		}


		public function student_loans()
		{

			$this->layout->title = 'Student Loans - ';
			$this->assign( 'page', 'member_student_loans' );
			$this->render_view( 'member/student_loans' );

		}


		public function fiscal_timeline()
		{

			$this->layout->title = 'Financial Aid Time Line';
			$this->assign( 'page', 'member_student_loans' );
			$this->render_view( 'member/fiscal_timeline' );

		}

		public function offered_aid()
		{

			$this->layout->title = 'Appealing the Offered Aid Package';
			$this->assign( 'page', 'member_offered_aid' );
			$this->render_view( 'member/offered_aid' );

		}

		public function credit_management()
		{

			$plugins = $GLOBALS['advisor']->get( 'plugins' );

			if ( !$plugins['credit'] )
			{
				header( 'Location:' . SITE_URL . '/member' );
				return;
			}

			$this->layout->title = 'Credit Management - ';
			$this->assign( 'page', 'member_credit_management' );
			$this->render_view( 'member/credit_management' );

		}

	}

?>