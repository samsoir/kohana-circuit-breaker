<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	'default'   => array(
		'fail_threshold'  => 5,     // Number of failed attempts before circuit is opened
		'ttl'             => 300,   // Number of seconds to elapse before a circuit is closed after failure
	),
);