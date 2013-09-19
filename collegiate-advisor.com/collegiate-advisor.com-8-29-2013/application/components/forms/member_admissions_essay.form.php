<?php

	class MemberAdmissionsEssayForm extends Legato_Form
	{

		public function __construct()
		{

			// Initialize this form.
			parent::__construct( 'member_admissions_essay_form', array('form_action' => SITE_URL . '/member/admissions/upload') );

			// Add the elements.
			$this->add( new Legato_Form_Fieldset( 'upload_essay' ) );

			$this->add( new Legato_Form_Element_File( 'essay' ) );
			$this->add( new Legato_Form_Element_Submit( 'upload', 'Upload' ) );
			
		}

		public function validate()
		{

			$error = parent::validate();

			if ( !$error )
				return false;
				
			$file_types = array( 'application/msword', 'application/rtf', 'text/plain' );
			$file_info = $this->essay->value;
			
			// Restrict file type.
			if ( !in_array( $file_info['type'], $file_types ) )
			{
				$this->essay->error( 'File must be a .doc, .rtf, or .txt file.' );
				return false;
			}
			
			// Success!
			return true;

		}

	}

?>