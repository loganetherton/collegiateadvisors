<?php

	class EditablePageForm extends Legato_Form
	{
		
		public function __construct( $act, $editable_page = NULL, $hook = NULL )
		{
			
			// Set up work...
			$url = ( $editable_page != NULL ) ? $act . '/' . $editable_page->get( 'id' ) : $act;
			
			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_editable_pages', array( 'form_action' => SITE_URL . '/admin/editable_pages/' . $url ) );
			
			//------------------------------------
			// Basic Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'editable_pages_information', 'Editable Page\'s Information' ) );

			// Add the elements.
			$advisors = Legato_Resource::fetch( 'Advisor' );
			
			foreach ( $advisors as $advisor_id => $advisor )
				$advisor_select[$advisor_id] = $advisor->get( 'first_name' ) . ' ' . $advisor->get( 'last_name' );
			
			$this->add( new Legato_Form_Element_Select( 'advisor_id', 'Advisor:', $advisor_select ) );
			
			$this->add( new Legato_Form_Element_Text( 'filename' ) );
			
			$type_select['footer'] = 'Footer';
			$type_select['public_menu'] = 'Public Menu';
			$type_select['top_menu'] = 'Top Menu';
			$type_select['member_menu'] = 'Member Menu';
			$type_select['public_top_menu'] = 'Public Top Menu';
			$type_select['retirement_menu'] = 'Retirement Menu';
			
			$this->add( new Legato_Form_Element_Select( 'type', 'Type:', $type_select ) );
			
			$this->add( new Legato_Form_Element_Text( 'title' ) );
			
			$this->add( new Legato_Form_Element_Checkbox( 'private' ) )
			     ->rule( 'required', false );

			$this->add( new Legato_Form_Element_Textarea( 'page_content' ) )
			     ->filter( 'html', array( 'popoon' ) );	
					
			$this->add( new Legato_Form_Element_Submit( 'edit', 'Submit' ) );

			// Add the rules.
			$this->page_content->rule( 'required', false );
			
			// Set Defaults	
			if ( $editable_page != NULL )
			{

				$this->advisor_id->default_value( $editable_page->get( 'advisor_id' ) );
				$this->filename->default_value( $editable_page->get( 'filename' ) );
				$this->type->default_value( $hook->get( 'type' ) );
				$this->title->default_value( $editable_page->get( 'title' ) );
				$this->private->default_value( $editable_page->get( 'private' ) );
				
				$filename = ROOT . '/application/views/ext/' . $editable_page->get( 'advisor_id' ) . '/' . $editable_page->get( 'filename' ) . '.phtml';
			
				$file_handle = fopen( $filename, 'rb' );
				$this->page_content->default_value( @fread( $file_handle, filesize( $filename ) ) );
				fclose( $file_handle );	
				
			}
			
		}
		
		
		public function validate()
		{
			$error = parent::validate();
			
			if ( !$error )
				return false;
			
			return $error;
		}
		
	}

?>