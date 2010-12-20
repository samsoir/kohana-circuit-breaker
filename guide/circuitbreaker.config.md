# Circuit Breaker Configuration

Circuit Breaker provides a few configuration options to control how each instance behaves. All configuration within Circuit Breaker conforms to the standard Kohana pattern of using class member properties for standard configuration. If required, a tradition configuration file can also be used. The configuration file supports groups allowing multiple configurations for situations where multi-faceted circuit breakers are required.

## Configuration settings

## Standard configuration

If a single configuration of Circuit Breaker is required, it is advisable to use the class member property configuration as it is ultimately faster than the configuration file alternative. The Circuit Breaker class should be configured in the application `bootstrap.php` file, after modules are loaded and before the main [Request] is executed.

### Bootstrap configuration example

    // Setup Circuit Breaker for Memcache
    CircuitBreaker::$driver = 'memcache';

    // Set the Time To Live to 30 seconds
    CircuitBreaker::$ttl = 30;

    // Set the fail threshold to 2 requests
    CircuitBreaker::$fail_threshold = 2;