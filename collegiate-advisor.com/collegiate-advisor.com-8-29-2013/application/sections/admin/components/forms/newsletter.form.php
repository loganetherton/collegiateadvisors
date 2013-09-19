<?php

	class NewsletterForm extends Legato_Form
	{
		
		public function __construct( $news = NULL )
		{
			
			// Initialize this form.
			parent::__construct( 'admin_newsletter' );

			//------------------------------------
			// Basic Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'newsletter' ) );

			// Add the elements.
			$this->add( new Legato_Form_Element_Textarea( 'news' ) )
			     ->filter( 'html', array( 'popoon' ) );	;
			
			$this->add( new Legato_Form_Element_Submit( 'submit', 'Submit' ) );
			
			// Set the defaults.
			if ( $news != NULL )
				$this->default_values( $news->get() );
			
		}
		
	}

?>