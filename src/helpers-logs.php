<?php
/**
 * Contains helpers for manipulating logs.
 *
 * @package flightdeck
 */

namespace flightdeck;

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
