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
	/**
	 * @var  string   the default driver to use
	 */
	public static $driver = 'native';

	/**
	 * @var  integer  number of failed attempts before circuit opening
	 */
	public static $fail_threshold = 5;

	/**
	 * @var  integer  number of seconds to hold circuit open
	 */
	public static $ttl = 300;

	/**
	 * @var  array
	 */
	protected static $_instances = array();

	/**
	 * Creates a new singleton of the CircuitBreaker class
	 *
	 * @param   string   name 
	 * @param   string|array config 
	 * @return  Kohana_CircuitBreaker
	 */
	public static function instance($name, $config = array())
	{
		// Create an instance hash
		$hash = sha1($name.serialize($config));

		if (isset(CircuitBreaker::$_instances[$hash]))
		{
			// If an instance exists, return it
			return CircuitBreaker::$_instances[$hash];
		}

		// Instantiate a new class
		CircuitBreaker::$_instances[$hash] = new CircuitBreaker($config);

		// Return the new instance
		return CircuitBreaker::$_instances[$hash];
	}

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  array
	 */
	public $state = array();

	/**
	 * @var  Kohana_Config
	 */
	protected $_config;

	/**
	 * @var  Kohana_CircuitBreaker_Driver
	 */
	protected $_driver;

	/**
	 * Constructor for the class, maintaining the singleton
	 * pattern.
	 *
	 * @param   string   name 
	 * @param   string|array  config  [Optional]
	 */
	protected function __construct($name, $config = array())
	{
		// Apply name of circuit
		$this->name = $name;

		// If config is a string
		if (is_string($config))
		{
			// Load the configuration setting
			$config = Kohana::config('circuitbreaker.'.$config);
		}

		// Setup the configuration, supplimenting missing fields
		$config += array(
			'driver'         => CircuitBreaker::$driver,
			'fail_threshold' => CircuitBreaker::$fail_threshold,
			'ttl'            => CircuitBreaker::$ttl,
		);

		// Apply the configuration
		$this->_config = $config;

		// Initialise the driver
		$this->_init();
	}

	/**
	 * Returns the open state of this circuit. If the circuit
	 * is open, it has failed more than the total allowed.
	 * 
	 *      // Check the state of the circuit
	 *      if ($circuit->is_open())
	 *      {
	 *           // Handle failed circuit logic here
	 *      }
	 *
	 * @return  boolean
	 */
	public function is_open()
	{
		return ! $this->state['connection'];
	}

	/**
	 * Returns whether the circuit is ready for retesting. This simply asks the circuit
	 * if the time now is beyond the cooling off period set by the open circuit. Returns
	 * `TRUE` if the circuit is ready for reuse or is already closed. `FALSE` otherwise.
	 * 
	 *     // Load circuit breaker
	 *     $circuit = CircuitBreaker::instance('http://foo.bar/test');
	 *
	 *     // Test the circuit for readyness
	 *     if ($circuit->ready())
	 *     {
	 *          $data = Remote::get('http://foo.bar/test');
	 *
	 *          // If connection was ok
	 *          if ($data->status == 200)
	 *          {
	 *               // Reopen the circuit
	 *               $circuit->success();
	 *          }
	 *          else
	 *          {
	 *               // Close the circuit
	 *               $circuit->failure();
	 *          }
	 *     }
	 *
	 * @return  boolean
	 */
	public function ready()
	{
		return time() > $this->state['ttl'];
	}

	/**
	 * Sets a successful connection message to the circuit
	 * breaker. This tells the breaker to close the circuit
	 * for other users. Success messages should only be sent
	 * when required for efficiency.
	 * 
	 *      // Load circuit breaker
	 *      $circuit = CircuitBreaker::instance('http://foo.bar/test');
	 * 
	 *      // Try a connection
	 *      $data = Remote::get('http://foo.bar/test');
	 * 
	 *      if ($data->status === 200 and $circuit->is_open())
	 *      {
	 *           // Mark the circuit successfully open
	 *           $circuit->success();
	 *      }
	 *
	 * @return  self
	 */
	public function success()
	{
		$this->state = array(
			'connection'       => TRUE,
			'failed_connects'  => 0,
			'failed_total'     => $this->state['failed_total'],
			'ttl'              => 0,
		);

		// Save the state
		$this->_driver->save($this->name, $this->state);

		return $this;
	}

	/**
	 * Tells the circuit breaker that a connection has failed. This should be called
	 * every time a named circuit failed, the breaker itself will decide whether or
	 * not to open the circuit.
	 * 
	 *      // Load circuit breaker
	 *      $circuit = CircuitBreaker::instance('http://foo.bar/test');
	 * 
	 *      // Try a connection
	 *      $data = Remote::get('http://foo.bar/test');
	 * 
	 *      // If connection failed
	 *      if ($data->status == 500)
	 *      {
	 *           // Mark failure
	 *           $circuit->failure();
	 *      }
	 *
	 * @return  self
	 */
	public function failure()
	{
		$this->state['failed_total']++;

		if ($this->is_open())
		{
			$this->_driver->save($this->name, $this->state);
			return $this;
		}

		$this->state['failed_circuits']++;

		if ($this->state['failed_circuits'] >= $this->_config['fail_threshold'])
		{
			$this->state['connection'] = FALSE;
			$this->state['ttl'] = time() + $this->_config['ttl'];
		}

		return $this;
	}

	/**
	 * Force reset the circuit. This will close the circuit and zero the failure
	 * count and total failures. Only use this if you really need to hard reset
	 * the circuit.
	 * 
	 * It is possible to reset the circuit to a custom state for testing purposes
	 * by passing an array
	 * 
	 *      // Load circuit breaker
	 *      $circuit = CircuitBreaker::instance('http://foo.bar/test');
	 *
	 *      // Reset the circuit
	 *      $circuit->reset();
	 * 
	 *      // Reset the circuit to state
	 *      $circuit->reset(array('connection' => FALSE, 'ttl' => time() + 300));
	 *
	 * @param   array    Optional state to pass to the reset command
	 * @return  self
	 */
	public function reset(array $state = array())
	{
		$state += array(
			'connection'      => TRUE,
			'failed_circuits' => 0,
			'failed_total'    => 0,
			'ttl'             => 0,
		);

		$this->state = $state;

		// Save the state
		$this->_driver->save($this->name, $this->state);

		return $this;
	}

	/**
	 * Returns the status of this circuit
	 *
	 * @param   string   [Optional] the status field to return
	 * @return  string|array|void
	 */
	public function status($field = NULL)
	{
		return ($field === NULL) ? $this->state : Arr::get($this->state, $field);
	}

	/**
	 * Initialises the circuit breaker
	 *
	 * @return  void
	 */
	protected function _init()
	{
		// Load the driver
		$this->_load_driver($this->config['driver']);

		// Setup the state
		$this->state = $this->_driver->load($this->name);

		// If there is no state
		if ($this->state === NULL)
		{
			// Reset this circuit
			$this->reset();
		}
		return;
	}

	/**
	 * Loads the driver based on the driver defined in
	 * the configuration
	 *
	 * @return  void
	 * @throws  Kohana_CircuitBreaker_Exception
	 */
	protected function _load_driver($driver)
	{
		$driver = 'CircuitBreaker_'.ucfirst($driver);

		// Check for the class existence
		if ( ! Kohana::auto_load($driver))
		{
			// Throw an exception
			throw new Kohana_CircuitBreaker_Exception('Unable to load the CircuitBreaker driver supplied : :driver', array(':driver' => $driver));
		}

		// Initialise the driver
		$this->_driver = new $driver;

		return;
	}
}