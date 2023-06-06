<?php
/**
 * Registers the core flightdeck settings.
 *
 * @package flightdeck
 */

namespace flightdeck;

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
		'flightdeck_lock_local_changes',
		array(
			'type'            => 'bool',
			'default'         => false,
			'view_capability' => 'read',
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_lock_show_indicator_bar_backend',
		array(
			'type'            => 'bool',
			'default'         => true,
			'view_capability' => 'read',
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_lock_show_indicator_bar_frontend',
		array(
			'type'            => 'bool',
			'default'         => true,
			'view_capability' => 'read',
		)
	);

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
			'validate_callback' => __NAMESPACE__ . '\\is_password_valid_or_wp_errors',
			'sanitize_callback' => function( $pass ) {
				return password_hash( $pass, PASSWORD_DEFAULT );
			},
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_auth_code_expires',
		array(
			'type'            => 'int',
			'edit_capability' => 'do_not_allow',
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_auth_code',
		array(
			'sanitize_callback' => function( $value ) {
				// If somebody tries to set an auth code, generate a new one randomly.
				$expiry_setting = Flightdeck_Setting::get_setting( 'flightdeck_auth_code_expires' );
				$expiry_setting->set( time() + FLIGHTDECK_AUTH_CODE_DURATION, false );

				return generate_auth_code( 5 );
			},
		)
	);

	$settings[] = new Flightdeck_Setting(
		'flightdeck_blacklist_import_folders',
		array(
			'type'    => 'array',
			'default' => array(),
		)
	);

	return $settings;
}
add_filter( 'flightdeck/get_settings', __NAMESPACE__ . '\\register_all_flightdeck_settings' );
