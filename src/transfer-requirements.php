<?php
/**
 * Contains filters for blacklisting import & exporting of specific files/directories/tables.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Prevents files from within the Flightdeck plugin or log directories from being exported.
 *
 * @param bool             $allow_file If the row should be exported.
 *
 * @param IConnection_Item $file The item being exported.
 *
 * @param Connection       $connection The connection being sent to.
 *
 * @return bool True if the file can be exported, false otherwise.
 */
function prevent_export_flightdeck_dirs( $allow_file, $file, $connection ) {
	$filesystem = Filesystem::get_instance();

	if ( $filesystem->file_within_directory( $file, FLIGHTDECK_LOGS_DIR ) ) {
		return false;
	}

	if ( $filesystem->file_within_directory( $file, FLIGHTDECK_PLUGIN_DIR ) ) {
		return false;
	}

	return $allow_file;
}
add_filter( 'flightdeck/allow_export_file', __NAMESPACE__ . '\\prevent_export_flightdeck_dirs', 10, 4 );

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
