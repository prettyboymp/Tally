<?php

abstract class Tally_Base {

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SHARED PROPERTIES & METHODS 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// raw data returned from $wpdb
	protected $data;

	// flag indicating if the object exists in the DB yet
	protected $new;

	// the table that stores the data for this class
	protected static $table;

	// the primary key field for this class in the database
	protected static $key;

	// default values for the fields expected from the DB
	protected static $defaults = array();

	// default types for the fields, used to sanitize the fields
	protected static $default_types = array();

	// default wpdb prepared statement types
	protected static $default_mysql = array();

	// sanitizes array of data based on $default_types
	protected static function sanitize_args(&$args) 
	{
		foreach($args as $key => $arg) {
			$type = array_key_exists($key, static::$default_types) 
				? static::$default_types[$key] 
				: 'string';

		$new = null;
			switch($type) {
				case 'int':
					$new = (int)$arg;
					break;
				case 'float':
					$new = (float)$arg;
					break;
				case 'bool':
					$new = (bool)$arg;
					break;
				case 'array':
					$new = empty($arg) ? array() : $arg;
					$new = is_string($new) ? unserialize($new) : $new;
					break;
				case 'datetime':
					$new = ($arg instanceof DateTime || null == $arg) ? $arg : new DateTime($arg);
					if ($arg === '0000-00-00 00:00:00') $new = null;
					break;
				case 'string': // fall through
				default:
					$new = is_null($arg) ? null : (string)$arg;
			}

			$args[$key] = $new;
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DATA ACCESS
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function __get($name) 
	{
		return $this->data[$name];
	}

	public function data() 
	{
		return $this->data;
	}

	public function __set($name, $value) 
	{
		if (!$this->validate_arg($name, $value)) return;
		$this->data[$name] = $value;
		static::sanitize_args($this->data);
	}

	public function set(array $args) 
	{
		$this->data = wp_parse_args($args, $this->data);
		static::sanitize_args($this->data);
	}

	protected function validate_arg($name, $value)
	{
		if (static::$key === $name) return false;
		return true;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CRUD
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// constructs an object with supplied arguements after sanitation
	protected function __construct(array $args = array(), $new = false) 
	{
		$this->data = wp_parse_args($args, static::$defaults);
		static::sanitize_args($this->data);
		$this->new = $new;
	}

	// public interface to create a new object
	public static function create($args = array())
	{
		return new static($args, true);
	}

	// saves the object to the database (either insert or update depending on $new flag)
	public function save() 
	{
		global $wpdb;

		$data = array();
		$formats = array();
		$success = false;

		$this->prepare_save($data, $formats);

		if ($this->new) {
			$data[static::$key] = $this->data[static::$key];
			$formats[] = static::$default_mysql[static::$key];
			$success = false !== $wpdb->insert(
				$wpdb->prefix.static::$table,
				$data,
				$formats
			);
			if ($success) $this->data[static::$key] = $wpdb->insert_id;
		} else {
			$where = array(static::$key => $this->data[static::$key]);
			$success = false !== $wpdb->update(
				$wpdb->prefix.static::$table,
				$data,
				$where,
				$formats,
				static::$default_mysql[static::$key]
			);
		}

		if ($success) $this->new = false;
		return (bool)$success;
	}

	// search database and constructs objects based on provided arguments
	public static function search(array $args = array(), $format = OBJECT) 
	{
		global $wpdb;

		$clauses = array();
		$params = array();
		static::prepare_search_clauses($args, $clauses, $params);

		$qry = static::prepare_search_query($clauses);

		$results = $wpdb->get_results($wpdb->prepare($qry, $params), ARRAY_N === $format ? ARRAY_N : ARRAY_A);
		$output = array();

		switch($format) {
			case OBJECT: //fall through
			case OBJECT_K:
				foreach($results as $result) 
					$output[] = new static($result);
				break;
			case ARRAY_A: //fall through
			case ARRAY_N: //fall through
			default:
				$output = $results;
				break;
		}

		return $output;
	}

	// parses search arguments into where clauses and parameters
	protected static function prepare_search_clauses($args = array(), &$clauses = array(), &$params = array())
	{
		foreach ($args as $key => $val) {
			if (!array_key_exists($key, static::$defaults)) continue;
			$clauses[] = sprintf(
				'%s = %s',
				$key,
				(is_numeric($val) || is_bool($val)) && !is_string($val) ? '%d' : '%s'
			);
			$params[] = is_array($val) || is_object($val) ? serialize($val) : $val;
		}
	}

	// parses clauses into the select query
	protected static function prepare_search_query($clauses = array())
	{
		global $wpdb;

		return sprintf(
			'SELECT * FROM %s WHERE %s',
			$wpdb->prefix.static::$table,
			implode(' AND ', $clauses)
		);
	}

	protected function prepare_save(&$data = array(), &$formats = array())
	{
		foreach($this->data as $key=> $arg) {
			if (!array_key_exists($key, static::$defaults)) continue;
			if (static::$key === $key) continue;

			if ($arg instanceof DateTime) $data[$key] = $arg->format('Y-m-d H:i:s');
			elseif (is_array($arg)) $data[$key] = serialize($arg);
			else $data[$key] = $arg;

			$formats[$key] = static::$default_mysql[$key];
		}
	}

}
