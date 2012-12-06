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
			`start_date` DATETIME NOT NULL,
			`end_date` DATETIME NOT NULL,
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

}
