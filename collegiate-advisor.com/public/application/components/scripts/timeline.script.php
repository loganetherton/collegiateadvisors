<?php

	// Include the config file and Framework Autoloader
	include( dirname( dirname( dirname( __FILE__ ) ) ) . '/settings/config.conf.php' );
	include( LEGATO . '/autoloader/autoloader.fns.php' );
	
	// Initialize the Settings
	Legato_Stage::initialize();
	
	// Get all the users.
	$users = User::fetch( 'User' );
	
	// Get the date information.
	$year = date( 'Y' );
	$month = date( 'n' );
	
	// Get a mailer object.
	$mail = new Legato_Mail();
	$mail->view = new Legato_View( 'emails/timeline/main' );
	
	// Try to get keith's message.
	$date = strtotime( date( 'm/1/Y', strtotime( '-1 month' ) ) );
	$mail->view->keith_news = Legato_Resource::order_by( 'date', 'desc' )->limit( 1 )->fetch( 'Newsletter_News', array( 'date >=' => $date ) );
	
	// Loop through each user.
	foreach ( $users as $user )
	{
		
		// Get the offset between this year and their graduation year.
		$offset = (int)$user->graduation_year - $year;
		
		// They must have an email address, and a graduation year that's in the future.
		if ( !$user->email_address || !$user->graduation_year || $user->graduation_year == 1 || $offset < 0 )
			continue;
		
		// Make sure we have information for this date.
		if
		( 
			!file_exists( ROOT . Legato_Settings::get( 'stage', 'views_folder' ) . '/emails/timeline/' . $offset . '/' ) ||
			!file_exists( ROOT . Legato_Settings::get( 'stage', 'views_folder' ) . '/emails/timeline/' . $offset . '/' . $month . '.phtml' )
		)
		{ continue; }
		
		// To the user.
		$mail->to( array( $user->email_address => ($user->first_name . ' ' . $user->last_name) ) );
		
		// From the advisor.
		$mail->from( $user->advisor->contact_email_address, $user->advisor->business_name );
		
		// Email data.
		$mail->subject = 'Reminder from ' . $user->advisor->business_name;
		
		// Set up the view.
		$mail->view->user = $user;
		$mail->view->timeline_view = new Legato_View( 'emails/timeline/' . $offset . '/' . $month );
		
		// Only for the senior year.
		if ( ($offset == 0 || ($offset == 1 && $month > 8)) && !$mail->view->common_view )
			$mail->view->common_view = new Legato_View( 'emails/timeline/senior_common' );
		else
			$mail->view->common_view = null;
		
		// Send the email.
		$mail->send();
		$mail->reset();
		
	}  // Next user.
	
?>	