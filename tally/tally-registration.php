<?php

class TALLY_Registration extends Tally_Base {

	protected static $table = TALLY_REGISTRATIONS_TABLE;

	protected static $key = 'id';

	protected static $defaults = array(
		'id'                   => 0,
		'guid'                 => '00000000-0000-0000-0000-000000000000',
		'post_id'              => 0,
		'ip_address'           => '255.255.255.255',
		'created_on'           => '1970-01-01 00:00:00',
		'modified_on'          => '1970-01-01 00:00:00',
		'contact_salutation'   => '',
		'contact_first_name'   => '',
		'contact_last_name'    => '',
		'contact_email'        => '',
		'contact_phone'        => '',
		'organization'         => '',
		'shipping_address_1'   => '',
		'shipping_address_2'   => '',
		'shipping_city'        => '',
		'shipping_state'       => '',
		'shipping_zip'         => '',
		'custom_fields'        => null,
		'registration_type_id' => 0,
		'registration_qty'     => 0,
		'total_payment'        => 0.00,
		'billing_first_name'   => '',
		'billing_last_name'    => '',
		'billing_address_1'    => '',
		'billing_address_2'    => '',
		'billing_city'         => '',
		'billing_state'        => '',
		'billing_zip'          => '',
		'transaction_raw'      => '',
		'transaction_data'     => null,
		'transaction_status'   => 0,
		'registration_notes'   => '',
		'status'               => 0
	);

	protected static $default_types = array(
		'id'                   => 'int',
		'guid'                 => 'string',
		'post_id'              => 'int',
		'ip_address'           => 'string',
		'created_on'           => 'string',
		'modified_on'          => 'string',
		'contact_salutation'   => 'string',
		'contact_first_name'   => 'string',
		'contact_last_name'    => 'string',
		'contact_email'        => 'string',
		'contact_phone'        => 'string',
		'organization'         => 'string',
		'shipping_address_1'   => 'string',
		'shipping_address_2'   => 'string',
		'shipping_city'        => 'string',
		'shipping_state'       => 'string',
		'shipping_zip'         => 'string',
		'custom_fields'        => 'array',
		'registration_type_id' => 'int',
		'registration_qty'     => 'int',
		'total_payment'        => 'float',
		'billing_first_name'   => 'string',
		'billing_last_name'    => 'string',
		'billing_address_1'    => 'string',
		'billing_address_2'    => 'string',
		'billing_city'         => 'string',
		'billing_state'        => 'string',
		'billing_zip'          => 'string',
		'transaction_raw'      => 'string',
		'transaction_data'     => 'array',
		'transaction_status'   => 'int',
		'registration_notes'   => 'string',
		'status'               => 'int'
	);

	protected static $default_mysql = array(
		'id'                   => '%d',
		'guid'                 => '%s',
		'post_id'              => '%d',
		'ip_address'           => '%s',
		'created_on'           => '%s',
		'modified_on'          => '%s',
		'contact_salutation'   => '%s',
		'contact_first_name'   => '%s',
		'contact_last_name'    => '%s',
		'contact_email'        => '%s',
		'contact_phone'        => '%s',
		'organization'         => '%s',
		'shipping_address_1'   => '%s',
		'shipping_address_2'   => '%s',
		'shipping_city'        => '%s',
		'shipping_state'       => '%s',
		'shipping_zip'         => '%s',
		'custom_fields'        => '%s',
		'registration_type_id' => '%d',
		'registration_qty'     => '%d',
		'total_payment'        => '%f',
		'billing_first_name'   => '%s',
		'billing_last_name'    => '%s',
		'billing_address_1'    => '%s',
		'billing_address_2'    => '%s',
		'billing_city'         => '%s',
		'billing_state'        => '%s',
		'billing_zip'          => '%s',
		'transaction_raw'      => '%s',
		'transaction_data'     => '%s',
		'transaction_status'   => '%d',
		'registration_notes'   => '%s',
		'status'               => '%d'
	);

	public static function with_post_id($post_id) {
		$results = static::search(array('post_id' => $post_id));
		return count($results) ? $results[0] : false;
	}

}
