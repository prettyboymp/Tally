<?php

class TALLY_Registration_Type extends Tally_Base {

	protected static $table = TALLY_REGISTRATION_TYPES_TABLE;

	protected static $key = 'id';

	protected static $defaults = array(
		'id'               => 0,         // primary key of this registration type
		'post_id'          => 0,         // post id this registration type is associated with
		'name'             => 'Unnamed', // name of the registration type
		'description'      => '',        // a description of what this type offers
		'registrant_count' => 1,         // number of registrants per reg type
		'price'            => 0.00,      // the price of this reg type
		'max_quantity'     => 0,         // maximum number a person can register for in one go; 0 = unlimited
		'max_allowed'      => 0,         // maximum number of this type allowed per event; 0 = unlimited
		'start_date'       => null,      // start date of this reg type
		'end_date'         => null,      // end date of this reg type
		'active'           => true,      // whether or not this type has been "deleted"
		'open'             => true,      // whether or not this type can be selected
		'order'            => 0          // display order of the reg types on form
	);

	protected static $default_types = array(
		'id'               => 'int',
		'post_id'          => 'int',
		'name'             => 'string',
		'description'      => 'string',
		'registrant_count' => 'int',
		'price'            => 'float',
		'max_quantity'     => 'int',
		'max_allowed'      => 'int',
		'start_date'       => 'datetime',
		'end_date'         => 'datetime',
		'active'           => 'bool',
		'open'             => 'bool',
		'order'            => 'int'
	);

	protected static $default_mysql = array(
		'id'               => '%d',
		'post_id'          => '%d',
		'name'             => '%s',
		'description'      => '%s',
		'registrant_count' => '%d',
		'price'            => '%f',
		'max_quantity'     => '%d',
		'max_allowed'      => '%d',
		'start_date'       => '%s',
		'end_date'         => '%s',
		'active'           => '%d',
		'open'             => '%d',
		'order'            => '%d'
	);

	public static function with_post_id($post_id) 
	{
		return static::search(array('post_id' => $post_id));
	}

}
