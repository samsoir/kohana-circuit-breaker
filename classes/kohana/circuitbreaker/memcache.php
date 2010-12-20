<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Circuit Breaker native driver that connects to [Cache_Memcache] driver
 *
 * @package    Kohana/CircuitBreaker
 * @category   Driver
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
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