<?php
/**
 * Registers the core requirements for a connection.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Filters the list of connection rules.
 *
 * @param Rule_Message[] $restrictions Current array of restrictions.
 *
 * @param Connection     $connection The connection being checked.
 *
 * @return Rule_Message[] Filtered array of restrictions.
 */
function register_connection_restrictions( $restrictions, $connection ) {
	$restrictions[] = new Rule_Message(
		'MISSING_PERMISSIONS',
		current_user_can_use_flightdeck(),
		array(
			'fail_message' => __( 'User must have the correct permissions.', 'flightdeck' ),
			'pass_message' => __( 'User permission check passed.', 'flightdeck' ),
		)
	);

	if ( ! current_user_can_use_flightdeck() ) {
		return $restrictions;
	}

	$is_valid_url   = boolval( filter_var( $connection->address, FILTER_VALIDATE_URL ) );
	$restrictions[] = new Rule_Message(
		'URL_INVALID',
		$is_valid_url,
		array(
			'fail_message' => __( 'Connection address must be a valid URL.', 'flightdeck' ),
			'pass_message' => __( 'Connection address is a valid URL.', 'flightdeck' ),
		)
	);

	if ( ! $is_valid_url ) {
		return $restrictions;
	}

	$is_https_url   = 'https' === wp_parse_url( $connection->address, PHP_URL_SCHEME );
	$restrictions[] = new Rule_Message(
		'URL_NOT_HTTPS',
		$is_https_url,
		array(
			'fail_message' => __( 'Connection address must be over HTTPS.', 'flightdeck' ),
			'pass_message' => __( 'Connection address is HTTPS.', 'flightdeck' ),
		)
	);

	if ( ! $is_https_url ) {
		return $restrictions;
	}

	$resp  = $connection->send_request( '/flightdeck/v1/connection', array( 'method' => 'POST' ) );
	$cause = __( 'Unknown', 'flightdeck' );

	if ( ! $resp->ok ) {
		if ( ! $resp->is_body_json() ) {
			$cause = __( 'REST API not found. Check address points to WordPress installation. ', 'flightdeck' );
		} elseif ( 404 === $resp->code ) {
			$cause = __( 'FlightDeck is either not installed or not active.', 'flightdeck' );
		} elseif ( array_key_exists( 'message', $resp->get_body_json() ) ) {
			$cause = __( 'Authentication failed. Ensure arrivals are enabled and the password is correct.', 'flightdeck' );
		} else {
			$cause = __( 'An unknown error occurred.', 'flightdeck' );
		}

		$restrictions[] = new Rule_Message(
			'CONNECTION_REFUSED',
			false,
			array(
				'fail_message' => $cause,
				'data'         => $resp,
			)
		);
	} else {
		$restrictions[] = new Rule_Message(
			'CONNECTION_REFUSED',
			true,
			array(
				'pass_message' => __( 'Connection established!', 'flightdeck' ),
				'data'         => $resp,
			)
		);
	}

	return $restrictions;
}
add_filter( 'flightdeck/http_connection_is_allowed', __NAMESPACE__ . '\\register_connection_restrictions', 10, 2 );

/**
 * Filters the list of connection warnings.
 *
 * @param Rule_Message[] $warnings Current array of warnings.
 *
 * @param Connection     $connection The connection being checked.
 *
 * @return Rule_Message[] Filtered array of warnings.
 */
function register_connection_warnings( $warnings, $connection ) {
	$php_version_correct = version_compare( PHP_VERSION, FLIGHTDECK_REQUIRED_PHP_VERSION, '>=' );
	$warnings[]          = new Rule_Message(
		'PHP_VERSION',
		$php_version_correct,
		array(
			'fail_message' => __( 'Your system does not meet the minimum required PHP version.', 'flightdeck' ),
			'pass_message' => __( 'Your system meets the minimum required PHP version.', 'flightdeck' ),
		)
	);

	global $wp_version;
	$wp_version_correct = version_compare( $wp_version, FLIGHTDECK_REQUIRED_WP_VERSION, '>=' );
	$warnings[]         = new Rule_Message(
		'WP_VERSION',
		$wp_version_correct,
		array(
			'fail_message' => __( 'Your installation does not meet the minimum required WordPress version.', 'flightdeck' ),
			'pass_message' => __( 'Your installation has the minimum required WordPress version.', 'flightdeck' ),
		)
	);

	$is_multisite = ! is_multisite();
	$warnings[]   = new Rule_Message(
		'IS_MULTISITE',
		$is_multisite,
		array(
			'fail_message' => __( 'Multisite is not currently supported, you may experience unexpected behaviour.', 'flightdeck' ),
			'pass_message' => __( 'Multisite is not enabled.', 'flightdeck' ),
		)
	);

	$resp = $connection->send_request( '/flightdeck/v1/connection', array( 'method' => 'POST' ) );
	if ( ! $resp->ok || ! $resp->is_body_json() ) {
		$warnings[] = new Rule_Message(
			'CONNECTION_FAILED',
			false,
			array(
				'fail_message' => __( 'Connection could not be established.', 'flightdeck' ),
			)
		);

		return $warnings;
	}

	$foreign_settings = array_merge(
		array(
			'php_version'        => false,
			'wordpress_version'  => false,
			'flightdeck_version' => false,
			'is_multisite'       => false,
			'table_prefix'       => false,
		),
		$resp->get_body_json()
	);

	global $wpdb;
	$table_prefix_match = $wpdb->prefix === $foreign_settings['table_prefix'];
	$warnings[]         = new Rule_Message(
		'TABLE_PREFIX_MATCH',
		$table_prefix_match,
		array(
			'fail_message' => __( 'The table prefix is not the same on the arrival and departure sites.', 'flightdeck' ),
			'pass_message' => __( 'The table prefix matches across the sites.', 'flightdeck' ),
		)
	);

	$php_version_match = version_compare( PHP_VERSION, $foreign_settings['php_version'], '==' );
	$warnings[]        = new Rule_Message(
		'PHP_VERSIONS_MATCH',
		$php_version_match,
		array(
			'fail_message' => __( 'PHP versions on the arrival and departure site do not match.', 'flightdeck' ),
			'pass_message' => __( 'PHP versions on the arrival and departure sites match.', 'flightdeck' ),
		)
	);

	$wp_version_match = version_compare( $wp_version, $foreign_settings['wordpress_version'], '==' );
	$warnings[]       = new Rule_Message(
		'WP_VERSIONS_MATCH',
		$wp_version_match,
		array(
			'fail_message' => __( 'WordPress installation versions on the arrival and departure site do not match.', 'flightdeck' ),
			'pass_message' => __( 'WordPress installation versions on the arrival and departure sites match.', 'flightdeck' ),
		)
	);

	$flightdeck_version_match = version_compare( FLIGHTDECK_VERSION, $foreign_settings['flightdeck_version'], '==' );
	$warnings[]               = new Rule_Message(
		'FLIGHTDECK_VERSIONS_MATCH',
		$flightdeck_version_match,
		array(
			'fail_message' => __( 'FlightDeck plugin versions on the arrival and departure sites do not match.', 'flightdeck' ),
			'pass_message' => __( 'FlightDeck plugin versions on the arrival and departure sites match.', 'flightdeck' ),
		)
	);

	$is_foreign_multisite = ! boolval( $foreign_settings['is_multisite'] );
	$warnings[]           = new Rule_Message(
		'FOREIGN_MULTISITE',
		$is_foreign_multisite,
		array(
			'fail_message' => __( 'The arrival site uses multisite, which is not currently supported.', 'flightdeck' ),
			'pass_message' => __( 'The arrival site does not use multisite.', 'flightdeck' ),
		)
	);

	return $warnings;
}
add_filter( 'flightdeck/http_connection_warnings', __NAMESPACE__ . '\\register_connection_warnings', 10, 2 );
