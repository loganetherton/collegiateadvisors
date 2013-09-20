<?php

	//------------------------------------------------------------------------
	// Package: Autoloader
	//------------------------------------------------------------------------

	//------------------------------------------------------------------------
	// Function: __autoload()
	//
	// The autoload function that's called when a class is used but not
	// loaded. It will automatically load the file that the class resides in.
	// You can autoload your own classes by storing them in the <LW_Settings>
	// object.
	//
	// Parameters:
	//     $classname - The classes name to load.
	//------------------------------------------------------------------------
	function __autoload( $classname )
	{
		
		// Create the class array.
		$classes = array( 'LW_Settings' => '/settings/settings.class.php',
		                  'LW_Stage'    => '/stage/stage.class.php',
						  'LW_Resource' => '/resource/resource.class.php',
						  'LW_Debug_Debugger' => '/debug/debugger.class.php',
						  'LW_Authentication_Cookie' => '/authentication/cookie.class.php',
						  'LW_DB' => '/db/db.class.php',
						  'LW_DB_MySQL' => '/db/mysql.class.php',
						  'LW_Encryption' => '/encryption/encryption.class.php',
						  'LW_Form' => '/form/form.class.php',
						  'LW_Form_Validator' => '/form/validator.class.php',
						  'LW_Form_Fieldset' => '/form/fieldset.class.php',
						  'LW_Form_Group' => '/form/group.class.php',
						  'LW_Form_Element' => '/form/element.class.php',
						  'LW_Form_Element_Text' => '/form/element_text.class.php',
						  'LW_Form_Element_Password' => '/form/element_password.class.php',
						  'LW_Form_Element_File' => '/form/element_file.class.php',
						  'LW_Form_Element_Textarea' => '/form/element_textarea.class.php',
						  'LW_Form_Element_Checkbox' => '/form/element_checkbox.class.php',
						  'LW_Form_Element_Select' => '/form/element_select.class.php',
						  'LW_Form_Element_Radio' => '/form/element_radio.class.php',
						  'LW_Form_Element_Submit' => '/form/element_submit.class.php',
						  'LW_Form_Element_Reset' => '/form/element_reset.class.php',
						  'LW_Form_Element_Button' => '/form/element_button.class.php',
						  'LW_Form_Element_Hidden' => '/form/element_hidden.class.php',
						  'LW_Controller_Abstract' => '/controller/abstract.class.php',
						  'LW_Controller' => '/controller/controller.class.php',
						  'LW_Controller_Helper' => '/controller/helper.class.php',
						  'LW_Controller_HelperManager' => '/controller/helper.class.php',
						  'LW_Hook' => '/hook/hook.class.php',
						  'LW_Cache' => '/cache/cache.class.php',
						  'LW_iCache_Handler' => '/cache/handler.int.php',
						  'LW_Cache_HandlerAPC' => '/cache/handler_apc.class.php',
						  'LW_Cache_HandlerFile' => '/cache/handler_file.class.php',
						  'LW_Compressor' => '/compressor/compressor.class.php' );

		// The LW Framework class checker.
		// User classes are checked below.
		if ( $classes[$classname] )
		{			
			require( PATH_TO_LW_API . $classes[$classname] );
			return;
		}
		
		// Check for user added classes.
		if ( LW_Settings::get( 'autoloader', $classname ) )
		{			
			require( ROOT . LW_Settings::get( 'autoloader', 'include_folder' ) . LW_Settings::get( 'autoloader', $classname ) );
			return;			
		}
		
		// Check for forms.
		if ( strpos( $classname, 'Form' ) !== false )
		{
			$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( array( '_', 'Form' ), array( '/', '' ), $classname, $count ) ) ) . '.form.php';
			
			if ( file_exists( ROOT . LW_Settings::get( 'stage', 'forms_folder' ) . '/' . $filename ) )
			{
				require( ROOT . LW_Settings::get( 'stage', 'forms_folder' ) . '/' . $filename );
				return;
			}
		}
		
		// Check for a resource.
		$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( '_', '/', $classname, $count ) ) );
		$filename = ($count == 0) ? $filename . '/' . $filename . '.class.php' : $filename . '.class.php';
		
		if ( file_exists( ROOT . LW_Settings::get( 'stage', 'resources_folder' ) . '/' . $filename ) )
		{
			require( ROOT . LW_Settings::get( 'stage', 'resources_folder' ) . '/' . $filename );
			return;
		}

	}

?>