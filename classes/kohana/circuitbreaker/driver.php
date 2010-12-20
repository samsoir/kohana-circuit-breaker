<?php defined('SYSPATH') or die('No direct script access.');
/**
 * CircuitBreaker Driver 
 *
 * @package    Kohana/CircuitBreaker
 * @category   Driver
 */
interface Kohana_CircuitBreaker_Driver {

	/**
	 * Saves the circuit status to the driver.
	 *
	 * @param   string   name of the circuit
	 * @param   array    status to save to the circuit
	 * @return  boolean
	 */
	public function save($circuit_name, array $status);

	/**
	 * Load a circuit from the driver
	 *
	 * @param   string   circuit to load from the driver
	 * @return  array
	 */
	public function load($circuit_name);
}