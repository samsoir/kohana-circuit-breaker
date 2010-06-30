<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_CircuitBreaker_Memcache extends CircuitBreaker_Native {

	/**
	 * Constructor for this driver
	 */
	public function __construct()
	{
		// Load the Kohana Cache library
		$this->_cache = Cache::instance('memcache');
	}
}