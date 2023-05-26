<?php
/**
 * Creates the rest API route getting/setting flightdeck settings.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Registers the flightdeck settings fields.
 *
 * @since 1.0.0
 */
function register_flightdeck_api_settings_route() {
	$settings = get_flightdeck_settings();
	$args_arr = array();

	foreach ( $settings as $setting ) {
		$args_arr[ $setting->name ] = $setting->get_rest_api_schema();
	}

	register_rest_route(
		'flightdeck/v1',
		'/settings',
		array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'callback'            => function() {
					return Flightdeck_Setting::prepare_rest_response();
				},
			),
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'args'                => $args_arr,
				'callback'            => function( $request ) {
					foreach ( $request->get_params() as $setting_name => $setting_value ) {
						$setting = Flightdeck_Setting::get_setting( $setting_name );

						if ( $setting ) {
							$setting->set( $setting_value );
						}
					}

					return Flightdeck_Setting::prepare_rest_response();
				},

			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_settings_route' );
