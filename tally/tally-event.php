<?php

class TALLY_Event extends Tally_Base {

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// SHARED PROPERTIES & METHODS
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	protected static $table = TALLY_EVENTS_TABLE;

	protected static $key = 'post_id';

	protected static $defaults = array(
		'post_id'                  => 0,     // the id of the post that is the event
		'enabled'                  => true,  // whether or not registration is enabled on this event
		'open'                     => true,  // whether or not registration is open on this event
		'start_date'               => null,  // the start date for registration, null = immediately available
		'end_date'                 => null,  // the end date for registration, null = registration does not end
		'capture_salutation'       => false, // whether or not to capture the contact's salutation (Mr, Mrs, Ms)
		'capture_shipping'         => false, // whether or not to capture shipping address information
		'capture_organization'     => false, // whether or not to capture the organization/company of the contact
		'capture_registrants'      => false, // whether or not to capture individual registrant information
		'max_registrants'          => 0,     // max number of total individuals attending event, 0 = unlimited
		'custom_fields'            => null,  // custom fields to be applied to the registration form 
		'registrant_custom_fields' => null,  // custom fields to be applied to the registrant information fields
		'payment_method'           => 0      // which payment method the event should use, 0 = free, 1 = billed later, etc...
	);

	protected static $default_types = array(
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

	protected static $default_mysql = array(
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// EVENT RETRIEVAL
//   
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public static function with_post_id($post_id) {
		$results = static::search(array('post_id' => $post_id));
		return count($results) ? $results[0] : false;
	}

}
