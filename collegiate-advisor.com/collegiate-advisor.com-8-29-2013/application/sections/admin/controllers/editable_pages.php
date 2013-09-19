<?php

	class EditablePagesController extends Legato_Controller
	{

		public $level;

		public function __construct()
		{

			parent::__construct();

			if ( !$GLOBALS['admin'] )
			{
				header( 'Location: ' . SITE_URL . '/admin/login' );
				return;
			}

			$this->level = $GLOBALS['admin']->get( 'level' );

			if ( $this->level < 3 )
			{
				header( 'Location: ' . SITE_URL . '/admin' );
				return;
			}

			$this->assign( 'level', $this->level );

		}

		public function index( $message = '' )
		{

			$this->layout->title = 'Manage Editable Pages';
			$data['page'] = 'editable_pages_index';

			$data['editable_pages'] = Legato_Resource::fetch( 'Advisor_EditablePage' );

			// Did we perform an action?
			if ( $message != '' )
				$data['action'] = $message;

			$this->assign( $data );
			$this->render_view( 'editable_pages/index' );

		}

		public function view( $page_id )
		{

			$this->layout->title = 'View Editable Page';
			$data['page'] = 'editable_pages_view';

			$data['editable_page'] = new Advisor_EditablePage( $page_id );

			$filename = ROOT . '/application/views/ext/' . $data['editable_page']->get( 'advisor_id' ) . '/' . $data['editable_page']->get( 'filename' ) . '.phtml';

			$file_handle = fopen( $filename, 'a+b' );
			$data['page_content'] = @fread( $file_handle, filesize( $filename ) );
			fclose( $file_handle );

			$this->assign( $data );
			$this->render_view( 'editable_pages/view' );

		}

		public function add()
		{

			$this->layout->title = 'Add Editable Page';
			$data['page'] = 'editable_pages_add';

			$form = new EditablePageForm( 'add' );

			// Does it validate?
			if ( !$form->validate() )
			{

				$data['form'] = $form;

			}
			else
			{

				// Get All the Form Values
				$editable_page_data = $form->values();

				// Create the Page.
				$editable_page_id = Legato_Resource::create( 'Advisor_EditablePage', $editable_page_data );

				// Create the Hook
				Legato_Resource::create( 'Hook', array( 'advisor_id' => $editable_page_data['advisor_id'],
													'type' => $editable_page_data['type'],
													'editable_page_id' => $editable_page_id,
													'data' => $editable_page_data['title'] . '|' . '/ext/' . $editable_page_data['advisor_id'] . '/' . $editable_page_data['filename'] ) );

				$filename = ROOT . '/application/views/ext/' . $editable_page_data['advisor_id'] . '/' . $editable_page_data['filename'] . '.phtml';
				$file_handle = fopen( $filename, 'wb' );

	            fwrite( $file_handle, stripslashes( $editable_page_data['page_content'] ) );
	            fclose( $file_handle );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/editable_pages/added' );
				return;

			}

			$this->assign( $data );
			$this->render_view( 'editable_pages/add' );

		}

		public function edit( $page_id )
		{

			$this->layout->title = 'Edit Editable Page';
			$data['page'] = 'editable_pages_edit';

			$editable_page = new Advisor_EditablePage( $page_id );
			$hook = new Hook( array( 'editable_page_id' => $editable_page->get( 'id' ) ) );

			$form = new EditablePageForm( 'edit', $editable_page, $hook );

			// Does it validate?
			if ( !$form->validate() )
			{

				$data['form'] = $form;

			}
			else
			{

				// Get All the Form Values
				$editable_page_data = $form->values();

				// If the filename or advisor has changed, delete the old file
				if ( ( $editable_page_data['filename'] != $editable_page->get( 'filename' ) ||
					 ( $editable_page_data['advisor_id'] != $editable_page->get( 'advisor_id' ) ) ) )
				{
					$delete_file = ROOT . '/application/views/ext/';
					$delete_file.= ( $editable_page_data['advisor_id'] != $editable_page->get( 'advisor_id' ) ) ? $editable_page->get( 'advisor_id' ) : $editable_page_data['advisor_id'];
					$delete_file.= '/';
					$delete_file.= ( $editable_page_data['filename'] != $editable_page->get( 'filename' ) ) ? $editable_page->get( 'filename' ) : $editable_page_data['filename'];
					$delete_file.= '.phtml';

					if ( file_exists( $delete_file ) )
						unlink( $delete_file );
				}

				// Create the Page.
				$editable_page->update( $editable_page_data );

				$hook->update( array( 'advisor_id' => $editable_page_data['advisor_id'],
									  'type' => $editable_page_data['type'],
									  'data' => $editable_page_data['title'] . '|' . '/ext/' . $editable_page_data['advisor_id'] . '/' . $editable_page_data['filename'] ) );

				$filename = ROOT . '/application/views/ext/' . $editable_page_data['advisor_id'] . '/' . $editable_page_data['filename'] . '.phtml';

				$file_handle = fopen( $filename, 'wb' );

	            fwrite( $file_handle, stripslashes( $editable_page_data['page_content'] ) );
	            fclose( $file_handle );

				// Redirect the user to the appropriate page.
				header( 'Location: ' . SITE_URL . '/admin/editable_pages/edited' );
				return;

			}

			$this->assign( $data );
			$this->render_view( 'editable_pages/edit' );

		}

		public function delete()
		{

			Legato_Settings::set( 'stage', 'show_header_footer', false );

			$ep = new Advisor_EditablePage( $_POST['id'] );

			// Delete the File
			$filename = ROOT . '/application/views/ext/' . $ep->get( 'advisor_id' ) . '/' . $ep->get( 'filename' ) . '.phtml';

			unlink( $filename );

			// Delete the Hooks
			$hooks = Legato_Resource::fetch( 'Hook', array( 'editable_page_id' => $_POST['id'] ) );
			if ( $hooks ) Legato_Resource::delete( 'Hook', $hooks );

			// Delete the Editable Page
			if ( $_POST['id'] )
				Legato_Resource::delete( 'Advisor_EditablePage', $_POST['id'] );

		}

	}

?>