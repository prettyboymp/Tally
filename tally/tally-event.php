<?php

class TALLY_Event {

	private $data;
	private $new;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// SHARED PROPERTIES & METHODS
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private static $defaults = array(
		'post_id'                  => 0,     //the id of the post that is the event
		'enabled'                  => true,  //whether or not registration is enabled on this event
		'open'                     => true,  //whether or not registration is open on this event
		'start_date'               => null,  //the start date for registration, null = immediately available
		'end_date'                 => null,  //the end date for registration, null = registration does not end
		'capture_salutation'       => false, //whether or not to capture the contact's salutation (Mr, Mrs, Ms)
		'capture_shipping'         => false, //whether or not to capture shipping address information
		'capture_organization'     => false, //whether or not to capture the organization/company of the contact
		'capture_registrants'      => false, //whether or not to capture individual registrant information
		'max_registrants'          => 0,     //max number of total individuals attending event, 0 = unlimited
		'custom_fields'            => null,  //custom fields to be applied to the registration form 
		'registrant_custom_fields' => null,  //custom fields to be applied to the registrant information fields
		'payment_method'           => 0      //which payment method the event should use, 0 = free, 1 = billed later, etc...
	);

	private static $default_types = array(
		'post_id'                  => 'int',    
		'enabled'                  => 'bool', 
		'open'                     => 'bool', 
		'start_date'               => 'datetime', 
		'end_date'                 => 'datetime', 
		'capture_salutation'       => 'bool',
		'capture_shipping'         => 'bool',
		'capture_organization'     => 'bool',
		'capture_registrants'      => 'bool',
		'max_registrants'          => 'int',    
		'custom_fields'            => 'array', 
		'registrant_custom_fields' => 'array', 
		'payment_method'           => 'int'     
	);

	private static $default_mysql = array(
		'post_id'                  => '%d',
		'enabled'                  => '%d',
		'open'                     => '%d',
		'start_date'               => '%s',
		'end_date'                 => '%s',
		'capture_salutation'       => '%d',
		'capture_shipping'         => '%d',
		'capture_organization'     => '%d',
		'capture_registrants'      => '%d',
		'max_registrants'          => '%d',
		'custom_fields'            => '%s',
		'registrant_custom_fields' => '%s',
		'payment_method'           => '%d'
	);

	private static $post_types = array(
		'page',
		'post',
		'tz-event'
	);

	public static function post_types() {
		return apply_filters('tally_post_types', self::$post_types);
	}

	private static function sanitize_args(&$args) {
		foreach($args as $key => $arg) {
			$type = array_key_exists($key, self::$default_types) 
				? self::$default_types[$key] 
				: 'string';

			$new = null;
			switch($type) {
				case 'int':
					$new = (int)$arg;
					break;
				case 'bool':
					$new = (bool)$arg;
					break;
				case 'array':
					$new = empty($arg) ? null : unserialize($arg);
					break;
				case 'datetime':
					$new = ($arg instanceof DateTime || null === $arg) ? $arg : new DateTime($arg);
					break;
				case 'string':
					//fall through
				default:
					$new = is_null($arg) ? null : (string)$arg;
			}

			$args[$key] = $new;
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// EVENT RETRIEVAL
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function __construct(array $args = array(), $new = false) {
		$this->data = wp_parse_args($args, self::$defaults);
		self::sanitize_args($this->data);
		$this->new = $new;
	}

	public static function search(array $args = array(), $format = OBJECT) {
		global $wpdb;

		if (!array_key_exists('enabled', $args)) 
			$args['enabled'] = true;

		$clauses = array();
		$params  = array();
		foreach ($args as $key => $val) {
			if (!array_key_exists($key, self::$defaults)) continue;
			if ('start_date' === $key) continue;
			if ('end_date' === $key) continue;
			$clauses[] = sprintf(
				'%s = %s', 
				$key, 
				(is_numeric($val) || is_bool($val)) && !is_string($val) ? '%d' : '%s'
			);
			$params[] = is_array($val) || is_object($val) ? serialize($val) : $val;
		}

		if (array_key_exists('start_date', $args)) {
			$date = $args['start_date'];
			if ($date instanceof DateTime) 
				$date = $date->setTime(23,59,59)->format('Y-m-d H:i:s');
			$clauses[] = '(end_date IS NULL OR end_date >= %s)';
			$params[] = $date;
		}

		if (array_key_exists('end_date', $args)) {
			$date = $args['end_date'];
			if ($date instanceof DateTime)
				$date = $date->setTime(0,0,0)->format('Y-m-d H:i:s');
			$clauses[] = '(start_date IS NULL OR start_date <= %s)';
			$params[] = $date;
		}

		if (array_key_exists('q', $args)) {
			$types = "'".implode("','", self::post_types())."'";
			$clauses[] = "post_id IN (SELECT post_id 
				FROM $wpdb->posts 
				WHERE post_status = 'publish'
				AND post_type IN ($types)
				AND (
					post_title LIKE %s
					OR post_content LIKE %s
				))";
			$params[] = $params[] = '%'.trim($args['q']).'%';
		}

		$qry = sprintf(
			'SELECT * FROM %s WHERE %s ORDER BY end_date',
			$wpdb->prefix.TALLY_EVENTS_TABLE,
			implode(' AND ', $clauses)
		);

		$results = $wpdb->get_results($wpdb->prepare($qry, $params), ARRAY_N === $format ? ARRAY_N : ARRAY_A);
		$output = array();

		switch($format) {
			case OBJECT:
				foreach($results as $result) 
					$output[] = new self($result);
				break;
			case OBJECT_K:
				foreach($results as $result)
					$output[$result['post_id']] = new self($result);
				break;
			case ARRAY_A: 
				//fall through
			case ARRAY_N: 
				//fall through
			default:
				$output = $results;
				break;
		}

		return $output;
	}

	public static function with_post_id($post_id) {
		$results = self::search(array('post_id' => $post_id));
		return count($results) ? $results[0] : false;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// GETTERS AND SETTERS
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __get($name) {
		return $this->data[$name];
	}

	public function data() {
		return $this->data;
	}

	public function __set($name, $value) {
		if ('post_id' === $name) return;
		$this->data[$name] = $value;
		self::sanitize_args($this->data);
	}

	public function set(array $args) {
		$this->data = wp_parse_args($args, $this->data);
		self::sanitize_args($this->data);
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// CRUD
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public static function create($post_id) {
		$existing = self::with_post_id($post_id);
		if ($existing) return $existing;
		return new self(array('post_id' => $post_id), true);
	}

	public function save() {
		global $wpdb;

		$data = array();
		$formats = array();
		$post_id = $this->data['post_id'];
		foreach($this->data as $key => $arg) {
			if (!array_key_exists($key, self::$defaults)) continue;
			if ('post_id' === $key) continue; 
			$data[$key] = ($arg instanceof DateTime) ? $arg->format('Y-m-d H:i:s') : $arg;
			$formats[$key] = self::$default_mysql[$key];
		}

		$success = false;
		if ($this->new) {
			$data['post_id'] = $post_id;
			$formats[] = self::$default_mysql['post_id'];
			$success = false !== $wpdb->insert(
				$wpdb->prefix.TALLY_EVENTS_TABLE,
				$data,
				$formats
			);
		} else {
			$where = array('post_id' => $post_id);
			$success = false !== $wpdb->update(
				$wpdb->prefix.TALLY_EVENTS_TABLE,
				$data,
				$where,
				$formats,
				'%d'
			);
		}

		$this->new = false;
		return (bool)$success;
	}


}
