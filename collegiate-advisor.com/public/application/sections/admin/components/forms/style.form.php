<?php

	class StyleForm extends Legato_Form
	{
		
		public function __construct( $act, $style = null )
		{
			// Set up work...
			$url = ($style != NULL) ? $act . '/' . $style->get( 'id' ) : $act;
			
			// Initialize this form.
			parent::__construct( 'admin_' . $act . '_style', array( 'form_action' => SITE_URL . '/admin/styles/' . $url ) );

			//------------------------------------
			// Style Information
			//------------------------------------
			$this->add( new Legato_Form_Fieldset( 'style', 'Style Colors' ) );

			// Add the elements.
			$this->add( new Legato_Form_Element_Text( 'background_color' ) );
			$this->add( new Legato_Form_Element_Text( 'menu_color' ) );
			$this->add( new Legato_Form_Element_Text( 'menu_font_color' ) );
			$this->add( new Legato_Form_Element_Text( 'content_color' ) );
			$this->add( new Legato_Form_Element_Text( 'font_color' ) );
			$this->add( new Legato_Form_Element_Text( 'accent_color' ) );
			$this->add( new Legato_Form_Element_Text( 'heading_color' ) );
			$this->add( new Legato_Form_Element_Text( 'link_color' ) );
			$this->add( new Legato_Form_Element_Text( 'footer_color' ) );
			$this->add( new Legato_Form_Element_Text( 'footer_font_color' ) );
			$this->add( new Legato_Form_Element_Text( 'border_color' ) );
			
			if ( $style != null ) 
				$this->default_values( $style->get() );
			
			$this->border_color->rule( 'required', false );
			
			$this->add( new Legato_Form_Element_Submit( 'add_style', 'Submit' ) );
			
		}
		
		
		public function validate( $act )
		{
			$error = parent::validate();
			
			if ( !$error )
				return false;

			return $error;
		}
		
	}

?>