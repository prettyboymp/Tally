<?php

class TALLY_Registrant extends Tally_Base {

	protected static $table = TALLY_REGISTRANTS_TABLE;

	protected static $key = 'id';

	protected static $defaults = array(
		'id'              => 0,
		'registration_id' => 0,
		'post_id'         => 0,
		'salutation'      => '',
		'first_name'      => '',
		'last_name'       => '',
		'custom_fields'   => null,
		'status'          => 0
	);

	protected static $default_types = array(
		'id'              => 'int',
		'registration_id' => 'int',
		'post_id'         => 'int',
		'salutation'      => 'string',
		'first_name'      => 'string',
		'last_name'       => 'string',
		'custom_fields'   => 'string',
		'status'          => 'int'
	);

	protected static $default_mysql = array(
		'id'              => '%d',
		'registration_id' => '%d',
		'post_id'         => '%d',
		'salutation'      => '%s',
		'first_name'      => '%s',
		'last_name'       => '%s',
		'custom_fields'   => '%s',
		'status'          => '%d'
	);

	public static function with_registration_id($reg_id) 
	{
		return static::search(array('registration_id' => $reg_id));
	}

	public static function with_post_id($post_id) 
	{
		return static::search(array('post_id' => $post_id));
	}

}
