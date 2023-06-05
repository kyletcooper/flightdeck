<?php
/**
 * Contains filters for blacklisting import & exporting of specific files/directories/tables.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Prevents files the user has blacklisted from being imported into.
 *
 * @param bool|WP_Error $allowed The current allowed state.
 *
 * @param string        $file The path of the file to import.
 *
 * @return bool|WP_Error Error or false on failure, true if allowed.
 */
function prevent_import_blacklisted_folders( $allowed, $file ) {
	$filesystem     = Filesystem::get_instance();
	$blacklist_dirs = get_flightdeck_setting( 'flightdeck_blacklist_import_folders', false );

	foreach ( $blacklist_dirs as $blacklist_dir ) {
		if ( $filesystem->file_within_directory( $blacklist_dir, FLIGHTDECK_PLUGIN_DIR ) ) {
			return new \WP_Error( 'DIRECTORY_NOT_ALLOWED', __( 'The file is within a blacklisted directory', 'flightdeck' ), array( 'status' => 500 ) );
		}
	}

	return $allowed;
}
add_filter( 'flightdeck/allow_import_file', __NAMESPACE__ . '\\prevent_import_blacklisted_folders', 10, 4 );

/**
 * Prevents files from within the Flightdeck plugin or log directories from being exported.
 *
 * @param bool       $allow_file If the row should be exported.
 *
 * @param array      $file The file being exported.
 *
 * @param Connection $connection The connection being sent to.
 *
 * @return bool True if the file can be exported, false otherwise.
 */
function prevent_export_flightdeck_dirs( $allow_file, $file, $connection ) {
	$filesystem = Filesystem::get_instance();

	if ( $filesystem->file_within_directory( $file, FLIGHTDECK_LOGS_DIR ) ) {
		Log::get_instance()->add(
			'file',
			Log::STATUS_FAILED,
			array(
				'error' => 'File is within the FlightDeck log directory.',
				'name'  => $file,
			)
		);

		return false;
	}

	if ( $filesystem->file_within_directory( $file, FLIGHTDECK_PLUGIN_DIR ) ) {
		Log::get_instance()->add(
			'file',
			Log::STATUS_FAILED,
			array(
				'error' => 'File is within the FlightDeck plugin directory.',
				'name'  => $file,
			)
		);

		return false;
	}

	return $allow_file;
}
add_filter( 'flightdeck/allow_export_files', __NAMESPACE__ . '\\prevent_export_flightdeck_dirs', 10, 4 );

/**
 * Filters the rows exported by export_table() to prevent Flightdeck settings being exported.
 *
 * @param bool   $allow_row If the row should be exported.
 *
 * @param array  $row The row from the database.
 *
 * @param string $table The table being exported.
 *
 * @return bool True if the row can be exported, false otherwise.
 */
function prevent_export_flightdeck_settings( $allow_row, $row, $table ) {
	global $wpdb;

	if ( $wpdb->options !== $table ) {
		return $allow_row;
	}

	$settings = get_flightdeck_settings();

	foreach ( $settings as $setting ) {
		if ( ! $setting->args['allow_export'] && $setting->name === $row['option_name'] ) {
			Log::get_instance()->add(
				'database',
				Log::STATUS_FAILED,
				array(
					'error' => 'Row is a FlightDeck setting which cannot be synced.',
					'name'  => $table,
				)
			);

			return false;
		}
	}

	return true;
}
add_filter( 'flightdeck/allow_export_table_row', __NAMESPACE__ . '\\prevent_export_flightdeck_settings', 10, 4 );
