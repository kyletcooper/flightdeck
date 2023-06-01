<?php
/**
 * Helpers for interacting with the database tables.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Creates an SQL transaction to upsert table records into a new database.
 *
 * @param array[] $table An array containing information about the table to export.
 *      @field string    $table The name of the table to export.
 *      @field int[]|int $rows  The rows to export by ID. Array of IDs or -1 for all rows.
 *
 * @param string  $find A string to be replaced.
 *
 * @param string  $replace What to replace the find target with.
 *
 * @return string The SQL transaction command.
 */
function export_table( $table, $find = '', $replace = '' ) {
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );

	$table_name      = $table['table'];
	$table_rows      = $table['rows'];
	$export_all_rows = -1 === $table_rows;

	$allow_table = apply_filters( 'flightdeck/allow_export_table', true, $table_name );

	if ( ! $allow_table ) {
		return '';
	}

	global $wpdb;
	$table_name = esc_sql( $table_name );
	$ret        = '';

	if($export_all_rows){
		$ret .= "DROP TABLE `$table_name`;\n\n";
		
		// Table schema.
		$ret .= $wpdb->get_var( $wpdb->prepare( 'SHOW CREATE TABLE %i', $table_name ), 1, 0 ) . ";\n";
	}

	// Get rows.
	$rows = array();

	if ( $export_all_rows ) {
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i', $table_name ), ARRAY_A );
	} else {
		$primary_key = get_table_primary_key( $table_name );
		$rows_sql    = 'SELECT * FROM %i WHERE %i IN (' . implode( ', ', array_fill( 0, count( $table_rows ), '%s' ) ) . ')';
		$rows        = $wpdb->get_results( $wpdb->prepare( $rows_sql, $table_name, $primary_key, ...$table_rows ), ARRAY_A );
	}

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

		$allow_row = apply_filters( 'flightdeck/allow_export_table_row', true, $row, $table_name );

		if ( $allow_row ) {
			$ret .= "\nINSERT INTO `$table_name` VALUES ($values);\n";
		}
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
 * @param string $original_tables_prefix The prefix for all table names. Will be replaced with our own prefix.
 *
 * @return true|\WP_Error True on success, WP_Error on failure.
 */
function import_table( $table_export_data, $original_tables_prefix ) {
	global $wpdb;

	// Save the plugin settings in case they get overwritten!
	$flightdeck_settings = array();
	foreach ( get_flightdeck_settings() as $setting ) {
		$flightdeck_settings[ $setting->name ] = $setting->get();
	}

	$wpdb->query( 'START TRANSACTION;' );

	$sql = change_sql_tables_prefix( $table_export_data, $original_tables_prefix, $wpdb->prefix );
	$res = $wpdb->query( $sql );

	if ( false === $res ) {
		$wpdb->query( 'ROLLBACK;' );
	} else {
		$wpdb->query( 'COMMIT;' );
	}

	foreach ( $flightdeck_settings as $setting_name => $setting_value ) {
		$setting = Flightdeck_Setting::get_setting( $setting_name );
		$setting->set( $setting_value, false );
	}

	if ( false === $res ) {
		return new \WP_Error( 'SQL_FAILED', $wpdb->last_error );
	}

	return true;
}

/**
 * Swaps the table name prefix in a SQL command.
 *
 * WARNING! This function only currently supports DROP, CREATE TABLE and INSERT commands.
 * It expects SQL to be formated as the result of the flightdeck\export_table function.
 *
 * @param string $sql The SQL to be updated.
 *
 * @param string $old_prefix The prefix to find and replace.
 *
 * @param string $new_prefix The prefix to swap in.
 *
 * @return string The updated SQL.
 */
function change_sql_tables_prefix( $sql, $old_prefix, $new_prefix ) {
	// Drop commands - e.i. DROP TABLE `wp_posts`.
	$drop_command_old = "DROP TABLE `$old_prefix";
	$drop_command_new = "DROP TABLE `$new_prefix";
	$sql              = str_replace( $drop_command_old, $drop_command_new, $sql );

	// Create table commands - e.i. CREATE TABLE `wp_posts`.
	$create_command_old = "CREATE TABLE `$old_prefix";
	$create_command_new = "CREATE TABLE `$new_prefix";
	$sql                = str_replace( $create_command_old, $create_command_new, $sql );

	// Insert commands - e.i. INSERT INTO `wp_posts`.
	$insert_command_old = "INSERT INTO `$old_prefix";
	$insert_command_new = "INSERT INTO `$new_prefix";
	$sql                = str_replace( $insert_command_old, $insert_command_new, $sql );

	return $sql;
}

/**
 * Returns an array of all table names.
 *
 * @return string[] Array of table names.
 */
function get_all_table_names() {
	global $wpdb;

	$cache_key = 'flightdeck_tables';
	$tables    = wp_cache_get( $cache_key );

	if ( false === $tables ) {
		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N  ); // phpcs:ignore -- No params

		wp_cache_set( $cache_key, $tables );
	}

	$tables = array_merge( ...$tables );

	return $tables;
}

/**
 * Returns the rows of a table.
 *
 * @param string $table Name of table.
 *
 * @param int    $limit Max number of rows.
 *
 * @param int    $offset Starting row.
 *
 * @return object[] Array of rows as objects.
 */
function get_table_rows( $table, $limit, $offset ) {
	global $wpdb;

	$table     = esc_sql( $table );
	$cache_key = 'flightdeck_rows_' . $table . '_' . $offset . '_' . $limit;
	$rows      = wp_cache_get( $cache_key );
	$rows      = false;

	if ( false === $rows ) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i LIMIT %d OFFSET %d',
				$table,
				$limit,
				$offset
			)
		);

		wp_cache_set( $cache_key, $rows );
	}

	return $rows;
}

/**
 * Returns the number of rows in a table.
 *
 * @param string $table Name of the table.
 *
 * @return int Number of rows.
 */
function get_table_row_count( $table ) {
	global $wpdb;

	$table     = esc_sql( $table );
	$cache_key = 'flightdeck_row_count_' . $table;
	$row_count = wp_cache_get( $cache_key );

	if ( false === $row_count ) {
		$row_count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i',
				$table
			)
		);

		wp_cache_set( $cache_key, $row_count );
	}

	return $row_count;
}

function get_table_primary_key( $table ) {
	global $wpdb;

	$table       = esc_sql( $table );
	$cache_key   = 'flightdeck_table_primary_key_' . $table;
	$primary_key = wp_cache_get( $cache_key );

	if ( false === $primary_key ) {
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW KEYS FROM %i WHERE Key_name = 'PRIMARY'",
				$table
			)
		);

		$primary_key = $res[0]->Column_name;
	}

	return $primary_key;
}
