<?php

	/*
		Package: Autoloader
		
		The autoloader automatically loads many types of components that you may want to use
		in your site: classes used in the framework, resources, helpers, forms, etc. You may also
		define your own classes for the autoloader to automatically load.
		
		_Note that you should load this file fairly early on in your site's bootstrapper, as it's
		needed by the framework to automatically load files. The "index.php" file included with the
		site skeleton does this._
		
		Topic: Autoloading Your Classes
		
		To autoload one of your classes, simply place the class to autoload under the [autoloader]
		section of your settings file. The key should be the case-sensitive name of the class you'd
		like to load, and the value should be the path relative to the <Legato_Stage::autoloader_folder> 
		setting.
		
		Here's an example
		
		(begin code)
			[autoloader]
			MyClass = "/classes/my/class.php"
			Project_SEED = "/classes/projects/seed.class.php"
		(end)
		
		That's all there is to it.
	*/

	
	/*
		(Exclude)
		Function: __autoload()
		The autoload function that's called when a class is used but not loaded. It
		will automatically load the file that the class resides in. Note this function
		will autoload many kinds of components in the framework: resources, helpers, forms, etc.
	*/
	function __autoload( $classname )
	{
		
		// Create the class array.
		$classes = array
		( 
			'Legato_ACL' => '/acl/acl.class.php',
			'Legato_Authentication' => '/authentication/authentication.class.php',
			'Legato_Cache' => '/cache/cache.class.php',
			'Legato_Cache_Handler_APC' => '/cache/handler/apc.class.php',
			'Legato_Cache_Handler_File' => '/cache/handler/file.class.php',
			'Legato_iCache_Handler' => '/cache/handler.int.php',
			'Legato_Compressor' => '/compressor/compressor.class.php',
			'Legato_Controller' => '/controller/controller.class.php',
			'Legato_Cookie' => '/cookie/cookie.class.php',
			'Legato_DB' => '/db/db.class.php',
			'Legato_DB_Handler' => '/db/handler.class.php',
			'Legato_DB_Statement' => '/db/statement.class.php',
			'Legato_Debug_Debugger' => '/debug/debugger.class.php',
			'Legato_Encryption' => '/encryption/encryption.class.php',
			'Legato_Filter' => '/filter/filter.class.php',
			'Legato_Form' => '/form/form.class.php',
			'Legato_Form_Element' => '/form/element/element.class.php',
			'Legato_Form_Element_Text' => '/form/element/text.class.php',
			'Legato_Form_Element_Password' => '/form/element/password.class.php',
			'Legato_Form_Element_File' => '/form/element/file.class.php',
			'Legato_Form_Element_Textarea' => '/form/element/textarea.class.php',
			'Legato_Form_Element_Checkbox' => '/form/element/checkbox.class.php',
			'Legato_Form_Element_CheckboxMultiple' => '/form/element/checkbox_multiple.class.php',
			'Legato_Form_Element_Select' => '/form/element/select.class.php',
			'Legato_Form_Element_SelectMultiple' => '/form/element/select_multiple.class.php',
			'Legato_Form_Element_Radio' => '/form/element/radio.class.php',
			'Legato_Form_Element_Submit' => '/form/element/submit.class.php',
			'Legato_Form_Element_Image' => '/form/element/image.class.php',
			'Legato_Form_Element_Reset' => '/form/element/reset.class.php',
			'Legato_Form_Element_Button' => '/form/element/button.class.php',
			'Legato_Form_Element_Hidden' => '/form/element/hidden.class.php',
			'Legato_Form_Fieldset' => '/form/fieldset.class.php',
			'Legato_Form_Group' => '/form/group.class.php',
			'Legato_Form_Validator' => '/form/validator.class.php',
			'Legato_Helper' => '/helper/helper.class.php',
			'Legato_Hook' => '/hook/hook.class.php',
			'Legato_Input' => '/input/input.class.php',
			'Legato_Layout' => '/layout/layout.class.php',
			'Legato_Mail' => '/mail/mail.class.php',
			'Legato_Resource' => '/resource/resource.class.php',
			'Legato_Settings' => '/settings/settings.class.php',
			'Legato_Stage'    => '/stage/stage.class.php',
			'Legato_Validation' => '/validation/validation.class.php',
			'Legato_View' => '/view/view.class.php'
		);

		// The Legato Framework class checker.
		// User classes are checked below.
		if ( $classes[$classname] )
		{
			require( LEGATO . '/' . $classes[$classname] );
			return;
		}
		
		// Check for user added classes.
		if ( Legato_Settings::get( 'autoloader', $classname ) )
		{
			$section = Legato_Settings::get_section( 'autoloader', $classname );
			require( ROOT . Legato_Settings::get( 'stage', 'autoloader_folder', $section ) . Legato_Settings::get( 'autoloader', $classname, $section ) );
			return;
		}
		
		// Check for forms.
		if ( strpos( $classname, 'Form' ) !== false )
		{
			foreach( Legato_Stage::$sections as $section )
			{
				$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( array( '_', 'Form' ), array( '/', '' ), $classname, $count ) ) ) . '.form.php';
				
				if ( file_exists( ROOT . Legato_Settings::get( 'stage', 'forms_folder', $section ) . '/' . $filename ) )
				{
					require( ROOT . Legato_Settings::get( 'stage', 'forms_folder', $section ) . '/' . $filename );
					return;
				}				
			}
		}
		
		// Check for helpers.
		if ( strpos( $classname, 'Helper' ) !== false )
		{
			foreach( Legato_Stage::$sections as $section )
			{
				$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( array( '_', 'Helper' ), array( '/', '' ), $classname, $count ) ) ) . '.help.php';
				
				if ( file_exists( ROOT . Legato_Settings::get( 'stage', 'helpers_folder', $section ) . '/' . $filename ) )
				{
					require( ROOT . Legato_Settings::get( 'stage', 'helpers_folder', $section ) . '/' . $filename );
					return;
				}
			}
		}
		
		// Check for a resource.
		$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', str_replace( '_', '/', $classname, $count ) ) );
		$filename = ($count == 0) ? $filename . '/' . $filename . '.class.php' : $filename . '.class.php';
		
		foreach( Legato_Stage::$sections as $section )
		{			
			if ( file_exists( ROOT . Legato_Settings::get( 'stage', 'resources_folder', $section ) . '/' . $filename ) )
			{
				require( ROOT . Legato_Settings::get( 'stage', 'resources_folder', $section ) . '/' . $filename );
				return;
			}
		}
		
		// Let's see if there's any hooks that want to autoload.
		if ( Legato_Stage::$hooks )
			foreach ( Legato_Stage::$hooks as $hook )
				$hook->autoload( $classname );

	}