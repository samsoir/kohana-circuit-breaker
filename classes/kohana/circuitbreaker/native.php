<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_CircuitBreaker_Native implements CircuitBreaker_Driver {

	/**
	 * @var   string
	 */
	public static $prefix = 'kcbn_';

	/**
	 * @var   integer
	 */
	public static $cache_life = 999999;

	/**
	 * [Kohana_Cache_File] instance
	 *
	 * @var   Kohana_Cache
	 */
	protected $_cache;

	/**
	 * Constructor for this driver
	 */
	public function __construct()
	{
		// Load the Kohana Cache library
		$this->_cache = Cache::instance('file');
	}

	/**
	 * Saves the circuit status to the driver.
	 *
	 * @param   string   name of the circuit
	 * @param   array    status to save to the circuit
	 * @return  boolean
	 */
	public function save($circuit_name, array $status)
	{
		return $this->_cache->set($this->_create_cache_key($circuit_name), $status, CircuitBreaker_Native::$cache_life);
	}

	/**
	 * Load a circuit from the driver
	 *
	 * @param   string   circuit to load from the driver
	 * @return  array
	 */
	public function load($circuit_name)
	{
		return $this->_cache->load($this->_create_cache_key($circuit_name));
	}

	/**
	 * Creates the cache key for storing the circuit breaker
	 *
	 * @param   string   circuit_name 
	 * @return  string
	 */
	protected function _create_cache_key($circuit_name)
	{
		return CircuitBreaker_Native::$prefix.$circuit_name;
	}
}