<?php
/**
 * Registers the core flightdeck settings.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Helper function for use in validating password settings.
 *
 * @param string $password Password to check.
 *
 * @return true|WP_Error True if passes, WP_Error or errors on failure.
 */
function password_is_valid_or_wp_errors( $password ) {
	if ( is_password_valid( $password ) ) {
		return true;
	}

	return Rule_Message::all_to_wp_errors( get_password_validity_messages( $password ) );
}

/**
 * Registers all core Flightdeck settings.
 *
 * Runs on the flightdeck/get_settings hook.
 *
 * @param Flightdeck_Setting[] $settings Array of current settings.
 *
 * @return Flightdeck_Settings[] The new array of settings.
 */
function register_all_flightdeck_settings( $settings ) {
	$settings[] = new Flightdeck_Setting(
		'flightdeck_allow_connections',
		array(
			'type'    => 'bool',
			'default' => false,
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_foreign_address',
		array(
			'validate_callback' => function( $value ) {
				delete_transient( 'foreign_address_thumbnail_url' );
				delete_transient( 'foreign_address_favicon_url' );

				return true;
			},
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_foreign_password',
		array(
			'send_in_rest' => false,
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_local_password',
		array(
			'send_in_rest'      => false,
			'validate_callback' => __NAMESPACE__ . '\\password_is_valid_or_wp_errors',
			'sanitize_callback' => function( $pass ) {
				return password_hash( $pass, PASSWORD_DEFAULT );
			},
		)
	);

	return $settings;
}
add_filter( 'flightdeck/get_settings', __NAMESPACE__ . '\\register_all_flightdeck_settings' );
