<?php
/**
 * Contains password related helpers.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Encrypts a string using an encryption key.
 *
 * @param string $key The encryption key. Use a random key.
 *
 * @param string $string The string to encrypt.
 *
 * @return string The encrypted string.
 */
function encrypt_string( $key, $string ) {
	$iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-gcm' ) );
	$encrypted = openssl_encrypt( $string, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );
	return base64_encode( $iv . $tag . $encrypted ); // phpcs:ignore -- Not used to hide code.
}

/**
 * Decrypts a string using an encryption key.
 *
 * @param string $key The encryption key. Use a random key.
 *
 * @param string $string The string to decrypt.
 *
 * @return string The decrypted string.
 */
function decrypt_string( $key, $string ) {
	$c              = base64_decode( $string ); // phpcs:ignore -- Not used to hide code.
	$cipher         = 'AES-256-GCM';
	$ivlen          = openssl_cipher_iv_length( $cipher );
	$iv             = substr( $c, 0, $ivlen );
	$tag            = substr( $c, $ivlen, $taglen = 16 );
	$ciphertext_raw = substr( $c, $ivlen + $taglen );
	return openssl_decrypt( $ciphertext_raw, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );
}

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
function password_is_valid_or_wp_errors( $password ) {
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
		return new \WP_Error( 'arrivals_disallowed', __( 'Arrivals are not allowed.', 'flightdeck' ) );
	}

	if ( ! verify_local_password( $request->get_header( 'X-Flightdeck-Password' ) ) ) {
		return new \WP_Error(
			'PASSWORD_INCORRECT',
			__( 'Password incorrect.', 'flightdeck' ),
			array(
				$request->get_header( 'X-Flightdeck-Password' ),
				get_flightdeck_setting( 'flightdeck_local_password', false ),
			)
		);
	}

	return true;
}
