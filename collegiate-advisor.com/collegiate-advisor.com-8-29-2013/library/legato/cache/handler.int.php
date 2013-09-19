<?php

	/*
		(Exclude)
		Interface: Legato_iCache_Handler
		The interface for cache handlers. All cache handlers must implement this functionality.
	*/
	interface Legato_iCache_Handler
	{
		
		public function __construct( $cache );
		public function set( $key, $value, $namespace, $ttl );
		public function get( $key, $namespace );
		public function start( $key, $namespace, $ttl );
		public function stop();
		public function delete( $key, $namespace );
		public function invalidate( $namespace );
		public function clear();

	}