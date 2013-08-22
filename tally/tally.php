<?php
/*
	Plugin Name: Tally
	Plugin URI: https://github.com/Clark-Nikdel-Powell/Tally
	Version: 0.1
	Description: A WordPress registration plugin
	Author: Chris Roche
	Author URI: http://www.clarknikdelpowell.com

	Copyright 2013  Chris Roche (email : wordpress@clarknikdelpowell.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 3 or later, 
	as published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

////////////////////////////////////////////////////////////////////////////////
// PLUGIN CONSTANT DEFINITIONS
////////////////////////////////////////////////////////////////////////////////

//FILESYSTEM CONSTANTS
define('TALLY_LOCAL',        $_SERVER['SERVER_NAME'] == 'localhost');
define('TALLY_PATH',         plugin_dir_path(__FILE__));
define('TALLY_URL',          TALLY_LOCAL ? plugins_url().'/tally/' : plugin_dir_url(__FILE__));

//DATABASE CONSTANTS
define('TALLY_DB_VERSION', 4);
define('TALLY_DB_VERSION_OPTION', 'tally_db_version');
define('TALLY_EVENTS_TABLE', 'tally_events');
define('TALLY_REGISTRATION_TYPES_TABLE', 'tally_registration_types');
define('TALLY_REGISTRATIONS_TABLE', 'tally_registrations');
define('TALLY_REGISTRANTS_TABLE', 'tally_registrants');

////////////////////////////////////////////////////////////////////////////////
// PLUGIN DEPENDENCIES
////////////////////////////////////////////////////////////////////////////////

require_once TALLY_PATH.'tally-base.php';
require_once TALLY_PATH.'tally-migrations.php';
require_once TALLY_PATH.'tally-event.php';
require_once TALLY_PATH.'tally-registration-type.php';
require_once TALLY_PATH.'tally-registration.php';
require_once TALLY_PATH.'tally-registrant.php';

////////////////////////////////////////////////////////////////////////////////
// ROOT PLUGIN CLASS
////////////////////////////////////////////////////////////////////////////////

final class TALLY_Tally {

	public static function activation() {
		add_option(TALLY_DB_VERSION_OPTION, 0);
		TALLY_Migrations::up();
	}

	public static function deactivation() {
		/* PLUGIN DEACTIVATION LOGIC HERE */
	}

	public static function uninstall() {
		TALLY_Migrations::down();
		delete_option(TALLY_DB_VERSION_OPTION);
	}

	private static $post_types = array(
		'page',
		'post',
		'tz-event'
	);

	public static function post_types() {
		return apply_filters('tally_post_types', self::$post_types);
	}

	/**
	 * Runs the database migration to ensure the database is up-to-date with the
	 * plugin
	 *
	 * @static
	 * @access public
	 * @return null
	 */
	public static function migrate() { TALLY_Migrations::up(); }

	public static function debug() {
		$evt = TALLY_Event::with_post_id(1);
		if (!$evt) $evt = TALLY_Event::create(1);
		$evt->open = true;
		$evt->start_date = new DateTime('yesterday');
		$evt->end_date = new DateTime('tomorrow');
		$evt->save();
	}

	public static function initialize() {
		
		//check to ensure database is current
		//have to wait until init before attempting to manipulate the db
		//results in errors otherwise on the admin side...
		add_action('init', array(__CLASS__, 'migrate'));

		if (TALLY_LOCAL) add_action('init', array(__CLASS__, 'debug'));
	}

}

////////////////////////////////////////////////////////////////////////////////
// PLUGIN INITIALIZATION
////////////////////////////////////////////////////////////////////////////////

register_activation_hook(__FILE__, array('TALLY_Tally', 'activation'));
register_deactivation_hook(__FILE__, array('TALLY_Tally', 'deactivation'));
register_uninstall_hook(__FILE__, array('TALLY_Tally', 'uninstall'));
TALLY_Tally::initialize();
