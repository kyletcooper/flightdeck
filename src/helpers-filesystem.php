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
