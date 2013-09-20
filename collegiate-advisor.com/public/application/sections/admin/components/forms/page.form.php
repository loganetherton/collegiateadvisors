<?php

	class PageForm extends Legato_Form
	{
		
		public function __construct( $content )
		{
			
			// Initialize this form.
			parent::__construct( 'page_edit' );
			
			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'edit_page', 'Edit Page' ) );
			
			$this->add( new Legato_Form_Element_Textarea( 'page_content' ) )
			     ->filter( 'html', array( 'popoon' ) );
						
			$this->add( new Legato_Form_Element_Submit( 'edit', 'Upload Changes' ) );
			
			$this->page_content->rule( 'required', false );
			
			// Set the defaults.
			$this->page_content->default_value( $content );

		}
		
	}

?>