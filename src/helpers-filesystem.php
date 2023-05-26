<?php
/**
 * Helpers
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Returns a relative string time stamp.
 *
 * @param int $time Timestamp to be relative to.
 *
 * @return string Short relative string.
 */
function relative_date( $time ) {
	$diff     = time() - $time;
	$day_diff = intval( floor( $diff / 86400 ) );

	if ( 0 === $day_diff ) {
		if ( $diff < 60 ) {
			return 'just now';
		}
		if ( $diff < 120 ) {
			return '1 min ago';
		}
		if ( $diff < 3600 ) {
			return floor( $diff / 60 ) . ' mins ago';
		}
		if ( $diff < 7200 ) {
			return '1 hr ago';
		}
		if ( $diff < 86400 ) {
			return floor( $diff / 3600 ) . ' hrs ago';
		}
	}
	if ( $day_diff < 7 ) {
		return $day_diff . ' days ago';
	}
	if ( $day_diff < 31 ) {
		return ceil( $day_diff / 7 ) . ' wks ago';
	}
	if ( $day_diff < 60 ) {
		return 'last month';
	}
		return strtolower( gmdate( 'M Y', $time ) );
}

/**
 * Helper function that checks if the user can edit/view Flightdeck.
 *
 * @return bool True if allowed, false if not.
 */
function current_user_can_use_flightdeck() {
	return current_user_can( 'manage_options' );
}

/**
 * Creates a file and all directories leading to it (if they do not exist).
 *
 * @param string $path Absolute path to file destination.
 *
 * @param string $content The content of the file.
 *
 * @return bool True on success, false on error.
 */
function create_file_path( $path, $content ) {
	$dir = dirname( $path );

	// Recursively creates dirs to the path.
	wp_mkdir_p( $dir );
	return file_put_contents( $path, $content ) > 0;
}

/**
 * Converts an absolute path to being relative to the local WP Content directory.
 *
 * @param string $path The absolute path.
 *
 * @return string The relative path.
 */
function get_path_wp_content_relative( $path ) {
	return str_replace( WP_CONTENT_DIR, '', $path );
}

/**
 * Converts an absolute path to being relative to the local WP install.
 *
 * @param string $path The absolute path.
 *
 * @return string The relative path.
 */
function get_path_wp_relative( $path ) {
	return str_replace( ABSPATH, '', $path );
}

/**
 * Initializes the WordPress filesystem and returns it.
 *
 * @return \WP_Filesystem_Direct The filesystem.
 */
function get_filesystem() {
	global $wp_filesystem;
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	return $wp_filesystem;
}

/**
 * Checks if a file is within a directory (or is a descendent of it in any way).
 *
 * @param string $file Path of the file to search for.
 *
 * @param string $dir Path of the directory to search in.
 *
 * @return bool True if within, false if not within (or not found).
 */
function file_within_directory( $file, $dir ) {
	$base     = realpath( $dir );
	$filename = realpath( $file );

	if ( false === $filename || strncmp( $filename, $base, strlen( $base ) ) !== 0 ) {
		return false;
	}

	return true;
}


/**
 * Prepends a leading slash.
 *
 * Will remove leading forward and backslashes if it exists already before adding
 * a leading forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * Opposite of {@see WordPress\trailingslashit()}.
 *
 * @param string $string What to add the leading slash to.
 * @return string String with leading slash added.
 */
function leadingslashit( $string ) {
	return '/' . unleadingslashit( $string );
}

/**
 * Removes leading forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * Opposite of {@see WordPress\untrailingslashit()}.
 *
 * @param string $string What to remove the leading slashes from.
 * @return string String without the leading slashes.
 */
function unleadingslashit( $string ) {
	return ltrim( $string, '/\\' );
}
