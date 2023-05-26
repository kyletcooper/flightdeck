<?php
/**
 * Helpers for interacting with the database tables.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Returns an array of all table names.
 *
 * @return string[] Array of table names.
 */
function get_tables_for_rest_api() {
	global $wpdb;

	$cache_key = 'flightdeck_tables';
	$tables    = wp_cache_get( $cache_key );

	if ( false === $tables ) {
		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N  ); // phpcs:ignore -- No params
		wp_cache_set( $cache_key, $tables );
	}

	$tables = array_merge( ...$tables );

	$ret = array();
	foreach ( $tables as $table ) {
		$table               = esc_sql( $table );
		$row_count_cache_key = 'flightdeck_row_count_' . $table;
		$row_count           = wp_cache_get( $row_count_cache_key );

		if ( false === $row_count ) {
			$row_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" ); // phpcs:ignore -- No params
		}

		$ret[] = array(
			'name'  => $table,
			'count' => $row_count,
		);
	}

	return $ret;
}

/**
 * Creates an SQL transaction to upsert table records into a new database.
 *
 * @param string $table_name The name of the table to export.
 *
 * @param string $find A string to be replaced.
 *
 * @param string $replace What to replace the find target with.
 *
 * @return string The SQL transaction command.
 */
function export_table( $table_name, $find = '', $replace = '' ) {
	global $wpdb;
	$table_name = esc_sql( $table_name );
	$ret        = '';

	$ret .= "DROP TABLE $table_name;\n\n";

	// Table schema.
	$ret .= $wpdb->get_var( "SHOW CREATE TABLE $table_name", 1, 0 ) . ";\n";

	// Get rows.
	$rows = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

	foreach ( $rows as $row ) {
		$values = array();
		foreach ( $row as $col_name => $col_value ) {
			// Find and replace.
			if ( $find && $replace ) {
				$col_value = str_replace( $find, $replace, $col_value );
			}

			$values[] = "'" . esc_sql( $col_value ) . "'";
		}

		$values = join( ', ', $values );
		$ret   .= "\nINSERT INTO `$table_name` VALUES ($values);\n";
	}

	return $ret;
}

/**
 * Runs a export_table SQL command. If it fails at all, it is rolled back.
 *
 * FlightDeck settings are saved so they cannot be overwritten.
 *
 * @param string $table_export_data The result from export_table.
 *
 * @return true|\WP_Error True on success, WP_Error on failure.
 */
function import_table( $table_export_data ) {
	global $wpdb;

	// Save the plugin settings in case they get overwritten!
	$flightdeck_settings = array();
	foreach ( get_flightdeck_settings() as $setting ) {
		$flightdeck_settings[ $setting->name ] = $setting->get();
	}

	$wpdb->query( 'START TRANSACTION;' );

	$res = $wpdb->query( $table_export_data );

	if ( false === $res ) {
		$wpdb->query( 'ROLLBACK;' );
	} else {
		$wpdb->query( 'COMMIT;' );
	}

	foreach ( $flightdeck_settings as $setting_name => $setting_value ) {
		$setting = Flightdeck_Setting::get_setting( $setting_name );
		$setting->set( $setting_value, true );
	}

	if ( false === $res ) {
		return new \WP_Error( 'SQL_FAILED', $wpdb->last_error );
	}

	return true;
}
