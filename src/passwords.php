<?php
/**
 * Contains password related helpers.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Returns an array of messages regarding rules a password must meet.
 *
 * @param string $password The password to check.
 *
 * @return Rule_Message[] Array of the messages.
 */
function get_password_validity_messages( $password ) {
	return apply_filters( 'flightdeck/password_valid', array(), $password ); // phpcs:ignore -- Hook is namespaced.
}

/**
 * Checks if a password is valid.
 *
 * @param string $password The password to check.
 *
 * @return boolean If the password is valid or not.
 */
function is_password_valid( $password ) {
	return Rule_Message::all( get_password_validity_messages( $password ) );
}

/**
 * Helper function for use in validating password settings.
 *
 * @param string $password Password to check.
 *
 * @return true|WP_Error True if passes, WP_Error or errors on failure.
 */
function is_password_valid_or_wp_errors( $password ) {
	if ( is_password_valid( $password ) ) {
		return true;
	}

	return Rule_Message::all_to_wp_errors( get_password_validity_messages( $password ) );
}

/**
 * Checks if the provided password used to connect to this site is correct.
 *
 * @param string $password The password attempt.
 *
 * @return bool If the password attempt was correct.
 */
function verify_local_password( $password ) {
	$setting = Flightdeck_Setting::get_setting( 'flightdeck_local_password' );

	if ( ! $setting->has_value( false ) ) {
		return false;
	}

	return password_verify( $password, $setting->get( false ) );
}

/**
 * Helper function for REST endpoints that accept foreign requests. Checks the flightdeck password header and returns a bool.
 *
 * Used for permission_callback. This function also checks if arrivals are allowed.
 *
 * @param WP_REST_Request $request The rest request.
 *
 * @return bool If password is correct.
 */
function check_flightdeck_foreign_api_request( $request ) {
	if ( ! get_flightdeck_setting( 'flightdeck_allow_connections', false ) ) {
		return new \WP_Error( 'ARRIVALS_DISALLOWED', __( 'Arrivals are not allowed.', 'flightdeck' ) );
	}

	if ( ! verify_local_password( $request->get_header( 'X-Flightdeck-Password' ) ) ) {
		return new \WP_Error( 'PASSWORD_INCORRECT', __( 'Password incorrect.', 'flightdeck' ) );
	}

	return true;
}
