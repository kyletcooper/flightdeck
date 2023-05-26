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
function register_flightdeck_api_tables_route() {
	register_rest_route(
		'flightdeck/v1',
		'/tables',
		array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'permission_callback' => __NAMESPACE__ . '\\check_flightdeck_foreign_api_request',
				'callback'            => function( $request ) {
					// Recieve a db table from a foreign server.
					$res = import_table( $request->get_body() );

					if ( is_wp_error( $res ) ) {
						$res->add_data(
							array(
								'status' => 500,
							)
						);

					}

					return $res;
				},
			),

			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'callback'            => __NAMESPACE__ . '\\get_tables_for_rest_api',
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_tables_route' );
