<?php
/**
 * Contains helpers for manipulating logs.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Adds to a flightdeck log file.
 *
 * Log is created if it does not exist.
 *
 * @param string $name Name of the log file.
 *
 * @param string $content The content to add to the file.
 *
 * @return bool True on success, false on failure.
 */
function add_to_log( $name, $content ) {
	file_put_contents( FLIGHTDECK_LOGS_DIR . "/$name.log", $content, FILE_APPEND ); // phpcs:ignore -- WP_Filesystem_Direct does not support appending.
}

/**
 * Returns an array of log files data.
 *
 * @return array[] Array of arrays, each with a name, url, mtime and size keys.
 */
function get_logs() {
	$filesystem = get_filesystem();
	$logs       = $filesystem->dirlist( FLIGHTDECK_LOGS_DIR, false, false );

	// Sort by name in reverse.
	usort(
		$logs,
		function ( $a, $b ) {
			return $b['name'] <=> $a['name'];
		}
	);

	$ret = array();

	foreach ( $logs as $log ) {
		$file = $log['name'];

		$ret[] = array(
			'name'    => basename( $file ),
			'url'     => FLIGHTDECK_LOGS_URL . '/' . get_path_wp_content_relative( $file ),
			'lastmod' => $log['lastmodunix'],
			'size'    => $log['size'],
		);
	}

	return $ret;
}
