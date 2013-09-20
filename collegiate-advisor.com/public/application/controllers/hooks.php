<?php

	/*
		Note that this file is just a bunch of delegations.
		You can not access any of this from the Web.
	*/
	class HooksController extends Legato_Controller
	{
		
		public function _menu( $type, $page = false )
		{
			
			$hooks = $GLOBALS['advisor']->get_hooks( $type . '_menu' );
				
			// Loop through each hook and retrieve the data.
			foreach( $hooks as $hook )
			{
				$data = explode( '|', $hook->get( 'data' ) );
				$id = $GLOBALS['advisor']->get( 'id' );
				$hook->set( 'data', $data );
				$hook->set( 'page', substr( $data[1], (strpos( $data[1], $id ) + strlen( $id ) + 1) ) );
			}
						
			$this->assign( 'hooks', $hooks );
			$this->assign( 'type', $type );
			$this->assign( 'page', $page );
			$this->render_view( 'hooks/menu' );
			
		}
		

		public function _footer()
		{
			
			$this->assign( 'hooks', $GLOBALS['advisor']->get_hooks( 'footer' ) );
			$this->render_view( 'hooks/footer' );
			
		}
		
		
		public function _index_top()
		{
			
			$hooks = $GLOBALS['advisor']->get_hooks( 'index_top' );
			
			// Loop through each hook and retrieve the data.
			foreach( $hooks as $hook )
				$hook->set( 'data', explode( '|', $hook->get( 'data' ) ) );
						
			$this->assign( 'hooks', $hooks );
			$this->render_view( 'hooks/index_top' );
			
		}
		
		
		public function _index_bottom()
		{
						
			$this->assign( 'hooks', $GLOBALS['advisor']->get_hooks( 'index_bottom' ) );
			$this->render_view( 'hooks/index_bottom' );
			
		}		
		
	}