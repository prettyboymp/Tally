<?php

/**
 * Handles database migrations (changes to the database) in a psuedo-version-
 * controlled manner. 
 */
final class TALLY_Migrations {
	
	//////////////////////////////////////////////////////////////////////////////
	// PUBLICLY ACCESSIBLE MIGRATION HANDLERS
	//////////////////////////////////////////////////////////////////////////////

	/**
	 * Updates the database to the current version of the plugin's database
	 * structure. All migrations should be included in this function in ascending
	 * chronological order.
	 * 
	 * @return mixed  True if the database is current; false otherwise.
	 */
	public static function up() {

		$current = TALLY_DB_VERSION;
		$status = (int)get_option(TALLY_DB_VERSION_OPTION, 0);
		if ($status >= $current) return true;

		// 1
		if ($status < 1 && $current >= 1) {
			self::up_1();
			$status = 1;
		}

		// 2
		if ($status < 2 && $current >= 2) {
			self::up_2();
			$status = 2;
		}

		return update_option(TALLY_DB_VERSION_OPTION, $status);
	}

	/**
	 * Reverts all changes to the database created by the plugin in reverse order.
	 * All migrations should be included in this function in descending 
	 * chronological order.
	 * 
	 * @return mixed True if the database has been reverted; false otherwise.
	 */
	public static function down() {

		$current = TALLY_DB_VERSION;
		$status = (int)get_option(TALLY_DB_VERSION_OPTION, 0);
		if ($status <= 0) return true;

		if ($status >= 2) {
			self::down_2();
			$status = 1;
		}

		// 1
		if ($status >= 1) {
			self::down_1();
			$status = 0;
		}

		return update_option(TALLY_DB_VERSION_OPTION, $status);
	}

	//////////////////////////////////////////////////////////////////////////////
	// THE ACTUAL, PRIVATE MIGRATIONS
	//////////////////////////////////////////////////////////////////////////////

	/**
	 * Gets the table name including the WordPress prefix applied for this
	 * site.
	 * 
	 * @param  String  The base of the table to build the table's name.
	 * 
	 * @return String  The name of the table in the database.
	 */
	private static function get_table($table) {
		global $wpdb;
		return $wpdb->prefix.$table;
	}

	/*****************************************************************************
	 * 1 : CREATION OF EVENTS TABLE
	 ****************************************************************************/

	private static function up_1() {
		global $wpdb;
		$table = self::get_table(TALLY_EVENTS_TABLE);

		$sql = "CREATE TABLE $table (
			`post_id` BIGINT(20) UNSIGNED NOT NULL,
			`enabled` TINYINT(1) NOT NULL,
			`open` TINYINT(1) NOT NULL,
			`start_date` DATETIME NULL,
			`end_date` DATETIME NULL,
			`capture_salutation` TINYINT(1) NOT NULL,
			`capture_shipping` TINYINT(1) NOT NULL,
			`capture_organization` TINYINT(1) NOT NULL,
			`capture_registrants` TINYINT(1) NOT NULL,
			`max_registrants` INT(11) NOT NULL,
			`custom_fields` TEXT NULL,
			`registrant_custom_fields` TEXT NULL,
			`payment_method` INT(11) UNSIGNED NOT NULL,
			PRIMARY KEY post_id (post_id)
		);";

		$wpdb->query($sql);
	}

	private static function down_1() {
		global $wpdb;
		$table = self::get_table(TALLY_EVENTS_TABLE);

		$sql = "DROP TABLE $table";

		$wpdb->query($sql);
	}

	/*****************************************************************************
	 * 2 : CREATION OF REGISTRATION TYPES TABLE
	 ****************************************************************************/

	private static function up_2() {
		global $wpdb;
		$table = self::get_table(TALLY_REGISTRATION_TYPES_TABLE);

		$sql = "CREATE TABLE $table (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`post_id` bigint(20) unsigned NOT NULL,
			`name` varchar(256) NOT NULL DEFAULT '',
			`description` varchar(1024) NOT NULL DEFAULT '',
			`registrant_count` int(11) NOT NULL DEFAULT '1',
			`price` decimal(9,2) NOT NULL DEFAULT '0.00',
			`max_quantity` smallint(5) unsigned NOT NULL DEFAULT '0',
			`max_allowed` int(11) unsigned NOT NULL DEFAULT '0',
			`start_date` DATETIME NULL DEFAULT NULL,
			`end_date` DATETIME NULL DEFAULT NULL,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`open` tinyint(1) DEFAULT '1',
			PRIMARY KEY (`id`),
			KEY `post_id` (`post_id`)
		);";

		$wpdb->query($sql);
	}

	private static function down_2() {
		global $wpdb;
		$table = self::get_table(TALLY_REGISTRATION_TYPES_TABLE);

		$sql = "DROP TABLE $table";

		$wpdb->query($sql);
	}

	// EOF -----------------------------------------------------------------------
}	
