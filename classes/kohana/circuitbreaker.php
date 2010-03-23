<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Circuit Breaker records and reports on external requests
 * to ensure that they are available. This is useful in SOA
 * environments where services that are unavailable should
 * be handled gracefully.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_CircuitBreaker {

	protected static $_instances = array();

	public static function instance($name, $config = NULL)
	{
		// If an instance exists, return it
		if (isset(CircuitBreaker::$_instances[$uri]))
			return CircuitBreaker::$_instances[$uri];

		CircuitBreaker::$_instances[$name] = new CircuitBreaker($config);
	}

	public $name;

	protected $_config;

	protected function __construct($name, $config = NULL)
	{
		// Apply name of circuit
		$this->name = $name;

		// Setup configuration depending on config supplied
		if (NULL === $config)
			$config = Config::instance()->load('circuitbreaker.default');
		elseif (is_array($config))
			$config = Config::instance()->load('circuitbreaker', $config);

		$this->_config = $config;
	}
}