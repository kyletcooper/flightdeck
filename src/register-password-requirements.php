<?php
/**
 * Contains all password requirements.
 *
 * @since 1.0.0
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Checks the min length is 10 characters.
 *
 * @param Rule_Message[] $messages The array of messages.
 *
 * @param string         $password The password to check.
 *
 * @return Rule_Message[] The new array of messages.
 *
 * @since 1.0.0
 */
function pswd_min_length( $messages, $password ) {
	$passes     = strlen( $password ) > 10;
	$messages[] = new Rule_Message(
		'MIN_LEN',
		$passes,
		array(
			'pass_message' => __( 'Minimum length of 10 characters.', 'flightdeck' ),
			'fail_message' => __( 'Minimum length of 10 characters.', 'flightdeck' ),
		)
	);

	return $messages;
}
add_filter( 'flightdeck/password_valid', __NAMESPACE__ . '\\pswd_min_length', 10, 2 );

/**
 * Checks password contains a special character.
 *
 * @param Rule_Message[] $messages The array of messages.
 *
 * @param string         $password The password to check.
 *
 * @return Rule_Message[] The new array of messages.
 *
 * @since 1.0.0
 */
function pswd_special_chars( $messages, $password ) {
	$passes = boolval( preg_match( '/[\'^!£$%&*()}{@#~?><>,|=_+¬-]/', $password ) );

	$messages[] = new Rule_Message(
		'SPECIAL_CHARS',
		$passes,
		array(
			'pass_message' => __( 'Must contain a special character.', 'flightdeck' ),
			'fail_message' => __( 'Must contain a special character.', 'flightdeck' ),
		)
	);

	return $messages;
}
add_filter( 'flightdeck/password_valid', __NAMESPACE__ . '\\pswd_special_chars', 10, 2 );

/**
 * Checks password contains upper & lowercase letters.
 *
 * @param Rule_Message[] $messages The array of messages.
 *
 * @param string         $password The password to check.
 *
 * @return Rule_Message[] The new array of messages.
 *
 * @since 1.0.0
 */
function pswd_mixed_case( $messages, $password ) {
	$passes = strtolower( $password ) !== $password && strtoupper( $password ) !== $password;

	$messages[] = new Rule_Message(
		'MIXED_CASE',
		$passes,
		array(
			'pass_message' => __( 'Must contain both upper & lowercase characters.', 'flightdeck' ),
			'fail_message' => __( 'Must contain both upper & lowercase characters.', 'flightdeck' ),
		)
	);

	return $messages;
}
add_filter( 'flightdeck/password_valid', __NAMESPACE__ . '\\pswd_mixed_case', 10, 2 );

/**
 * Checks password contains a number.
 *
 * @param Rule_Message[] $messages The array of messages.
 *
 * @param string         $password The password to check.
 *
 * @return Rule_Message[] The new array of messages.
 *
 * @since 1.0.0
 */
function pswd_has_number( $messages, $password ) {
	$passes = boolval( preg_match( '~[0-9]+~', $password ) );

	$messages[] = new Rule_Message(
		'HAS_NUMBER',
		$passes,
		array(
			'pass_message' => __( 'Must contain a numeric digit.', 'flightdeck' ),
			'fail_message' => __( 'Must contain a numeric digit.', 'flightdeck' ),
		)
	);

	return $messages;
}
add_filter( 'flightdeck/password_valid', __NAMESPACE__ . '\\pswd_has_number', 10, 2 );
