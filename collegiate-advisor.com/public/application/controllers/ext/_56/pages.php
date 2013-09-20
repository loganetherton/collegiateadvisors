<?php

	class PagesController extends Legato_Controller
	{

		public function test_prep()
		{

			$page = new Advisor_EditablePage( array( 'advisor_id' => 56, 'filename' => 'test_prep' ) );

			$this->layout->title = $page->title . ' - ';

			$data['page'] = $page;
			$data['content_view'] = new Legato_View( 'ext/56/' . $page->filename );

			$this->render_view( 'ext/56/pages/test_prep', $data );

		}


		public function test_prep_fulfillment()
		{

			$form = new Ext_56_TestPrepFulfillmentForm();

			if ( $form->validate() )
			{

				// Create the email array
				$message['subject'] = 'Test Prep Fulfillment';
				$message['to'] = 'daryl_leake@earthlink.net';
				$message['from'] = $GLOBALS['advisor']->get( 'contact_email_address' );

				$message['data'] = $form->values();

				$message['view'] = 'ext/56/emails/test_prep_fulfillment';

				// Send the Email
				EmailHelper::send( $message );

				header( 'Location: ' . SITE_URL . '/ext/_56/pages/test-prep-fulfillment-complete/' );
				exit();

			}

			$this->layout->title = 'Test Prep Fulfillment - ';

			$data['form'] = $form;

			$this->render_view( 'ext/56/pages/test_prep_fulfillment', $data );

		}


		public function test_prep_fulfillment_complete()
		{

			$this->layout->title = 'Completed Test Prep Fulfillment';

			$this->render_view( 'ext/56/pages/test_prep_fulfillment_complete' );

		}


		public function refer_friend( $complete )
		{

			$form = new Ext_56_ReferFriendForm();

			if ( $form->validate() )
			{

				// Create the email array
				$message['subject'] = 'Refer a Friend';
				$message['to'] = 'daryl_leake@earthlink.net';
				$message['from'] = $GLOBALS['advisor']->get( 'contact_email_address' );

				$message['data'] = $form->values();

				$message['view'] = 'ext/56/emails/refer_friend';

				// Send the Email
				EmailHelper::send( $message );

				header( 'Location: ' . SITE_URL . '/ext/_56/pages/refer-friend/true/' );
				exit();

			}

			$page = new Advisor_EditablePage( array( 'advisor_id' => 56, 'filename' => 'refer_friend' ) );

			$this->layout->title = $page->title . ' - ';

			$data['page'] = $page;
			$data['content_view'] = new Legato_View( 'ext/56/' . $page->filename );
			$data['form'] = $form;
			$data['complete'] = $complete;

			$this->render_view( 'ext/56/pages/refer_friend', $data );

		}


		public function efc_calculator( $complete )
		{

			$form = new Ext_56_EfcCalculatorForm();

			if ( $form->validate() )
			{

				$form_data = $form->values();

				//////////////////////////////////////
				// Create the email array
				$message['subject'] = 'EFC Calculator Info';
				$message['to'] = 'daryl_leake@earthlink.net';
				//$message['to'] = 'david@ydop.com';
				$message['from'] = $GLOBALS['advisor']->get( 'contact_email_address' );

				$message['data'] = $form_data;

				$message['view'] = 'ext/56/emails/efc_calculator_info';

				// Send the Email
				EmailHelper::send( $message );

				//////////////////////////////////////
				// Create the email array
				$message['subject'] = 'EFC Calculator Instructions';
				$message['to'] = $form_data['email'];
				$message['from'] = $GLOBALS['advisor']->get( 'contact_email_address' );

				$message['view'] = 'ext/56/efc_calculator_email';

				// Send the Email
				EmailHelper::send( $message );

				//////////////////////////////////////
				// Redirect
				header( 'Location: ' . SITE_URL . '/ext/_56/pages/efc-calculator/true/' );
				exit();

			}

			$page = new Advisor_EditablePage( array( 'advisor_id' => 56, 'filename' => 'efc_calculator' ) );

			$this->layout->title = $page->title . ' - ';

			$data['page'] = $page;
			$data['content_view'] = new Legato_View( 'ext/56/' . $page->filename );
			$data['form'] = $form;
			$data['complete'] = $complete;

			$this->render_view( 'ext/56/pages/efc_calculator', $data );

		}

	}