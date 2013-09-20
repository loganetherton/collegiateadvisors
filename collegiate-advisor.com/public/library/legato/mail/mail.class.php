<?php 

	/*
		Class: Legato_Mail
		Assists in sending e-mails.
		Allows using views to send the email.
	*/
	class Legato_Mail
	{
		
		/*
			Group: Variables
			
			Var: $view
			*object* A <Legato_View> object to use in rendering the message's body.
			You must set its filename before using it.
			
			Var: $validate
			*bool* Whether or not to validate the email addresses used in the mailer. 
			Defaults to false.
			
			Var: $type
			*string* The mime type to send the mail as text, html, or both. 
			Defaults to both.
			
			Var: $subject
			*string* The subject of the email.
			
			Var: $body
			*string* The body of the email.
			
			Var: $batch
			*bool* Whether or not this should send using SwiftMailer's batchSend() function or not.
			Defaults to false.
		*/	
		
		public $view = null;
		public $validate = false;
		public $type = 'both';
		public $subject = '';
		public $body = '';
		public $batch = false;
	
		/*
			(Exclude)
			
			Var: $_smtp
			Whether or not to send mail using SMTP or PHP's mail() function.
			
			Var: $_host
			The host to connect to.
			
			Var: $_port
			The remote port to connect to.
			
			Var: $_username
			The username for authentication.
			
			Var: $_password
			The password for authentication.
			
			Var: $_smtp
			Whether or not to send mail using SMTP or PHP's mail() function.
			
			Var: $_swift
			The stored instance of Swift Mailer.
			
			Var: $_view
			The view to render for the email.
			
			Var: $_view_data
			The array of data to be passed into the view.
			
			Var: $_from
			The address from which the email will be said to have been sent from.
			
			Var: $_from_name
			The name that appears with the email address.
			
			Var: $_replyto
			The email address that will be sent to when users click Reply.
			
			Var: $_to
			The array of email addresses to send to, as 'email_address' => 'Legato Bluesummers'.
			
			Var: $_cc
			The array of email addresses to be CC'd, as 'email_address' => 'Legato Bluesummers'.
			
			Var: $_bcc
			The array of email addresses to be BCC'd, as 'email_address' => 'Legato Bluesummers'.
			
			Var: $_attachments
			The array of attachements to send with the email.
		*/
		
		protected $_smtp = true;
		protected $_host = '';
		protected $_port = null;
		protected $_username = '';
		protected $_password = '';
		protected $_swift = null;
		protected $_view = '';
		protected $_view_data = array();
		protected $_from = '';
		protected $_from_name = '';
		protected $_replyto = '';
		protected $_to = array();
		protected $_cc = array();
		protected $_bcc = array();
		protected $_attachments = array();
		
		
		/*
			Group: Functions
		*/ 
		
		/*
			Constructor: __construct()
			Class constructor.
		
			Syntax:
				void __construct( [ array $options = array() ] )
		
			Parameters:
				array $options - *optional* - An array of options to set up the object.
				
			Options:			
				string 'host' - The host to connect to. Defaults to the <Legato_Mail::host> setting.
				int 'port' - The remote port to connect to. Defaults to the <Legato_Mail::port> setting.
				string 'username' - The username for authentication. Defaults to the <Legato_Mail::username> setting.
				string 'password' - The password for authentication. Defaults to the <Legato_Mail::password> setting.
				string 'smtp' - Whether or not to send mail using SMTP or PHP's mail() function. Defaults to true.
								
			Examples:
			(begin code)
				$m = new Legato_Mail( array
				( 
					'host' => 'smtphost', 
					'username' => 'billy', 
					'password' => '&%sy9' 
				) );
			(end)
		*/
		public function __construct( $options = array() )
		{
			
			foreach ( $options as $key => $value )
				$this->{'_' . $key} = $value;
				
			// Set defaults.
			if ( $this->_host == '' )
				$this->_host = Legato_Settings::get( 'smtp', 'host' );
				
			// Set defaults.
			if ( $this->_port == null )
				$this->_port = Legato_Settings::get( 'smtp', 'port' );
				
			if ( $this->_username == '' )
				$this->_username = Legato_Settings::get( 'smtp', 'username' );
				
			if ( $this->_password == '' )
				$this->_password = Legato_Settings::get( 'smtp', 'password' );
			
			// Include the Swift files.
			require_once( dirname( dirname( __FILE__ ) ) . '/packages/swift/Swift.php' );
			require_once( dirname( dirname( __FILE__ ) ) . '/packages/swift/Swift/Connection/SMTP.php' );
			
			// Create a new SMTP connection and connect.
			$conn = new Swift_Connection_SMTP( $this->_host, $this->_port );
			$conn->setUsername( $this->_username );
			$conn->setPassword( $this->_password );
			
			$this->_swift = new Swift( $conn );
			
			// Add the view if this is an SMTP connection.
			$this->view = new Legato_View();
			
		}
		
		
		/*
			Destructor: __destruct()
			Class destructor. Will automatically disconnect from the SMTP server for you.
		*/
		public function __destruct()
		{
			
			$this->_swift->disconnect();
					
		}
		
		
		/*
			Function: reset()
			Used to reset the data that is used for each email sent out to their defaults.
			Resets $to, $from, $cc, $bcc, $subject, $body, and $attachments.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to reset attachments. Defaults to true.
								
			Examples:
			>	$m->reset( true );
		*/
		public function reset( $reset_attachments = true )
		{
			
			$this->_to = array();
			$this->_from = '';
			$this->_cc = array();
			$this->_bcc = array();
			$this->subject = '';
			$this->body = '';
			
			if ( $reset_attachments )
				$this->_attachments = array();
			
		}
		
		
		/*
			Function: to()
			Gets passed in an array of all the email addresses to send the email to. 
			Can be used multiple times and the new email addresses will be added to the list.
			
			Syntax:
				bool to( array $to )
				
			Parameters:
				array $to - Array of all the email addresses that will be in the To field of the email. The keys are the email addresses and the values are the names.
				
			Returns:
				True if validation is off, or the email address is valid. 
				False if validation is on and the email address is invalid.
								
			Examples:
			(begin code)
				$m->to( array
				( 
					'example@site.com' => 'Legato Bluesummers',
					'example2@site.com' => 'Nicholas Wolfwood'
				) );
			(end)
			
			See Also:
				- <Legato_Mail::$validate>
		*/
		public function to( $to )
		{
			
			return $this->_set_recipients( $to, 'to' );
			
		}
		
		
		/*
			Function: cc()
			Gets passed in an array of all the email addresses to CC (carbon copy) the email to. 
			Can be used multiple times and the new email addresses will be added to the list.
			
			Syntax:
				bool cc( array $cc )
				
			Parameters:
				array $cc - Array of all the email addresses that will be in the CC field of the email. The keys are the email addresses and the values are the names.
				
			Returns:
				True if validation is off, or the email address is valid. 
				False if validation is on and the email address is invalid.
								
			Examples:
			(begin code)
				$m->cc( array
				( 
					'example@site.com' => 'Legato Bluesummers',
					'example2@site.com' => 'Nicholas Wolfwood'
				) );
			(end)
			
			See Also:
				- <Legato_Mail::$validate>
		*/
		public function cc( $cc )
		{
			
			return $this->_set_recipients( $cc, 'cc' );
			
		}
		
		
		/*
			Function: bcc()
			Gets passed in an array of all the email addresses to BCC (blind carbon copy) the email to. 
			Can be used multiple times and the new email addresses will be added to the list.
			
			Syntax:
				bool bcc( array $bcc )
				
			Parameters:
				array $bcc - Array of all the email addresses that will be in the BCC field of the email. The keys are the email addresses and the values are the names.
				
			Returns:
				True if validation is off, or all of the email addresses are valid. 
				False if validation is on and one of the email addresses is invalid.
								
			Examples:
			(begin code)
				$m->bcc( array
				( 
					'example@site.com' => 'Legato Bluesummers',
					'example2@site.com' => 'Nicholas Wolfwood'
				) );
			(end)
			
			See Also:
				- <Legato_Mail::$validate>
		*/
		public function bcc( $bcc )
		{
			
			return $this->_set_recipients( $bcc, 'bcc' );
			
		}
		
		
		/*
			Function: from()
			Sets the From field in the email. 
			
			Syntax:
				bool from( string $from [, string $from_name = '' ] )
				
			Parameters:
				string $from - The email address to send from.
				string $from_name - *optional* - The name of the person associated with the email address.
				
			Returns:
				True if validation is off, or the email address is valid. 
				False if validation is on and the email address is invalid.
								
			Examples:
			>	$m->from( 'legato@bluesummers.com' );
			>	$m->from( 'vash@stampede.com', 'Vash the Stampede' );
			
			See Also:
				- <Legato_Mail::$validate>
		*/
		public function from( $from, $from_name = '' )
		{
			
			if ( $this->validate )
				if ( !Legato_Validation::email( $from ) )
					return false;
			
			$this->_from = $from;
			$this->_from_name = $from_name; 
			
			return true;
			
		}
		
		
		/*
			Function: replyto()
			Used to reset the data that is normally changed per email.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to re.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize( 'admin' );
		*/
		public function replyto( $replyto = '' )
		{
			
			if ( $this->validate )
			{
				
				if ( !Legato_Validation::email( $replyto ) )
					return false;
					
			}
			
			$this->_replyto = $replyto;
			
		}
		
		
		/*
			Function: reset()
			Used to reset the data that is normally changed per email.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to re.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize( 'admin' );
		*/
		public function attach( $file, $name = '', $mime = '' )
		{
			
			// PECL FileInfo functions used to determine MIME Type
			$this->_attachments[] = array( 'file' => $file, 'name' => $name, 'mime' => $mime );
			
		}
		
		
		/*
			Function: reset()
			Used to reset the data that is normally changed per email.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to re.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize( 'admin' );
		*/
		public function view( $view = '', $view_data = array() )
		{
			
			$this->view = new Legato_View( $view, $view_data );
			
		}
		
		
		/*
			Function: get_mailer()
			Returns a reference to the SwiftMailer class used by this component so you can work directly with it.
			
			Syntax:
				object get_mailer()
				
			Notes:
				This function returns a reference, so any modifications done to the reference will be
				done to the SwiftMailer class used in this component, too.
								
			Examples:
			(begin code)
				// Include the anti-flood class.
				require_once( LEGATO . '/packages/swift/Swift/Plugin/AntiFlood.php' );
			
				// Create a new Mail class.
				$m = new Legato_Mail();
				
				// Get the SwiftMailer object for this Mail object.
				$swift = $m->get_mailer();
				
				// Attach an anti-flood plugin to it.
				$swift->attachPlugin( new Swift_Plugin_AntiFlood( 100 ), 'anti-flood' );
			(end)
		*/
		public function &get_mailer()
		{
			
			return $this->_swift;
			
		}
		
		
		/*
			Function: reset()
			Used to reset the data that is normally changed per email.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to re.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize( 'admin' );
		*/
		public function send()
		{
			
			// If SMTP is set to False, send mail using PHP's mail() function.
			if ( !$this->_smtp )
			{
				
				$to = implode( $this->_to, ', ' );
				$cc = implode( $this->_cc, ', ' );
				$bcc = implode( $this->_bcc, ', ' ); 
				
				$additional_headers = ( $this->_from != '' ) ? 'From: ' . $this->_from : '';
				$additional_headers.= ( $cc != '' ) ? '\r\n Cc: ' . $cc : '';
				$additional_headers.= ( $bcc != '' ) ? '\r\n Bcc: ' . $bcc : '';  
					
				return mail( $to, $this->subject, $this->body, $additional_headers );
			
			}
			
			$swift = $this->_swift;
				
			// Build the Message
			$message = new Swift_Message( $this->subject );
			
			// Add Recipients
			$recipients = new Swift_RecipientList();
			
			// Add To
			foreach ( $this->_to as $addr => $name )
				$recipients->addTo( $addr, $name );
			
			// Add Cc
			foreach ( $this->_cc as $addr => $name )
				$recipients->addCc( $addr, $name );
				
			// Add Bcc
			foreach ( $this->_bcc as $addr => $name )
				$recipients->addBcc( $addr, $name );
				
			// Add Replyto
			if ( $this->_replyto != '' )
				$message->headers->set( 'Replyto', $this->_replyto );
				
			// If a View is set, render the view and capture the data
			if ( $this->view && $this->view->filename )	
			{
				
				ob_start();
			
				$this->view->render();
				
				$html_message = ob_get_clean();
				
				// If the type is both or text
				if ( $this->type == 'both' || $this->type == 'text' )
				{
					
					$text_message = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $html_message );
					$text_message = str_replace( array( '</p>', '</ul>', '</ol>' ), "\r\n\r\n", $text_message );
					$text_message = str_replace( array( '</li>', '<br>', '<br />' ), "\r\n", $text_message );
					$text_message = str_replace( '<li>', '- ', $text_message );
					$text_message = str_replace( array( '<hr>', '<hr />' ), "---------- \r\n\r\n", $text_message );
					$text_message = strip_tags( $text_message );
					
					$message->attach( new Swift_Message_Part( $text_message ) );
				}	
				
				// If the type is both or html
				if ( $this->type == 'both' || $this->type == 'html' )
					$message->attach( new Swift_Message_Part( $html_message, 'text/html' ) );
				
			}
			else 
			{
				
				// If there is no view specified, attach the message anyway
				switch ( $this->type )
				{
					case 'both':
						$message->attach( new Swift_Message_Part( $this->body, 'text/html' ) );
						
						$text_message = str_replace( array( "\r\n", "\r", "\n" ), '', $this->body );
						$text_message = str_replace( '<br />', "\r\n", $text_message );
						$this->body = strip_tags( $text_message );
					case 'text':
						$message->attach( new Swift_Message_Part( $this->body ) );
						break;
					case 'html':
						$message->attach( new Swift_Message_Part( $this->body, 'text/html' ) );
						break;
					
				}
				
			}
			
			// Add Attachments
			foreach ( $this->_attachments as $attachment )
			{
				if ( $attachment['name'] != '' && $attachment['mime'] != '' )	
					$message->attach( new Swift_Message_Attachment( $attachment['file'], $attachment['name'], $attachment['mime'] ) );
				else
					$message->attach( new Swift_Message_Attachment( new Swift_File( $attachment['file'] ) ) );
				
			}
			
			// Send it out.
			if ( !$this->batch )
				return $swift->send( $message, $recipients, new Swift_Address( $this->_from, $this->_from_name ) );
			else
				return $swift->sendBatch( $message, $recipients, new Swift_Address( $this->_from, $this->_from_name ) );
			
		}
		
		
		/*
			(Exclude)
			Function: reset()
			Used to reset the data that is normally changed per email.
			
			Syntax:
				void reset( [ bool $reset_attachments = true] )
				
			Parameters:
				bool $reset_attachments - *optional* - Whether or not to re.
				
			Notes:
				This function is called automatically by <Legato_Stage::run()>, so you don't
				have to call it if you're running the whole stage class.
								
			Examples:
			>	Legato_Stage::initialize( 'admin' );
		*/
		private function _set_recipients( $recipients = array(), $type = 'to' )
		{
					
			if ( $this->validate )
			{
					
				foreach ( $recipients as $addr => $name )
					if ( !Legato_Validation::email( $addr ) )
						return false;
					
			}
			
			$this->{'_' . $type} = array_merge( $this->{'_' . $type}, $recipients );
			
			return true;
			
		}
		
	}
