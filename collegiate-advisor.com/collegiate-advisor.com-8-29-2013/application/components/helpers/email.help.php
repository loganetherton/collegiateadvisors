<?php

	class EmailHelper extends Legato_Helper
	{

		public static function send( $message_data )
		{

			require_once( ROOT . '/library/swift/Swift.php' );
			require_once( ROOT . '/library/swift/Swift/Connection/SMTP.php' );

			// Create a new SMTP connection and connect.
			$conn = new Swift_Connection_SMTP( Legato_Settings::get( 'smtp', 'host' ) );
			$conn->setUsername( Legato_Settings::get( 'smtp', 'username' ) );
			$conn->setPassword( Legato_Settings::get( 'smtp', 'password' ) );

			$swift = new Swift( $conn );

			// Let's build the message.
			$message = new Swift_Message( $message_data['subject'] );

			$email_view = new Legato_View( $message_data['view'], $message_data['data'] );

			ob_start();

			$email_view->render();

			$message_content = ob_get_clean();
			$text_message_content = str_replace( array( "\r\n", "\r", "\n" ), '', $message_content );
			$text_message_content = str_replace( '<br />', "\r\n", $text_message_content );
			$text_message_content = strip_tags( $text_message_content );

			// End rendering of message.

			$message->attach( new Swift_Message_Part( $text_message_content ) );
			$message->attach( new Swift_Message_Part( $message_content, 'text/html' ) );

			// Send it out.
			$swift->send( $message, $message_data['to'], $message_data['from'] );

		}


		public static function send_signup_email( $advisor_id, $data )
		{

			$advisor = new Advisor( $advisor_id );
			$data['advisor'] = $advisor;

			// Create the mail object to be used
			$mailer = new Legato_Mail();
			$mailer->to( array( $data['email_address'] => $data['first_name'] . ' ' . $data['last_name'] ) );
			$mailer->from( $advisor->contact_email_address, $advisor->business_name );

			$mailer->subject = 'Welcome!';
			$mailer->view( 'emails/signup', $data );

			$mailer->send();
			$mailer->reset();

			// Send to the advisor.
			$mailer->to( array( $advisor->email_address => $advisor->first_name . ' ' . $advisor->last_name ) );
			$mailer->from( Legato_Settings::get( 'smtp', 'admin_email' ), 'Keith Landis' );

			$mailer->subject = 'Collegiate Advisors - New User Signed Up';
			$mailer->view( 'emails/signup_advisor', $data );

			$mailer->send();
			$mailer->reset();

			// Send to Keith.
			$mailer->to( array( Legato_Settings::get( 'smtp', 'admin_email' ) => 'Keith Landis' ) );
			$mailer->from( $advisor->contact_email_address, $advisor->business_name );

			$mailer->subject = 'New User Signed Up';
			$mailer->view( 'emails/signup_keith', $data );

			$mailer->send();
			$mailer->reset();

		}


		public static function send_essay_email( $filename )
		{

			// Create the mail object to be used
			$mailer = new Legato_Mail();

			$mailer->to( array( Legato_Settings::get( 'smtp', 'essay_email' ) => 'CA Admin' ) );
			$mailer->from( $GLOBALS['advisor']->contact_email_address, $GLOBALS['advisor']->business_name );

			$mailer->subject = 'Essay Uploaded';
			$mailer->view( 'emails/essay' );

			$mailer->attach( $filename );

			$mailer->send();

			////////////////////////////

			$mailer->reset();

			$mailer->to( array( $GLOBALS['advisor']->contact_email_address => $GLOBALS['advisor']->business_name ) );
			$mailer->from( Legato_Settings::get( 'smtp', 'essay_email' ), 'Collegiate Advisor\'s Admin' );

			$mailer->subject = 'Essay Uploaded';
			$mailer->view( 'emails/essay' );

			$mailer->send();

		}

	}

?>