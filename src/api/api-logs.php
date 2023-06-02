<?php
/**
 * Creates the rest API route getting flightdeck logs.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Registers the flightdeck logs endpoint.
 *
 * @since 1.0.0
 */
function register_flightdeck_api_logs_route() {
	register_rest_route(
		'flightdeck/v1',
		'/logs',
		array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'args'                => array(
					'page'     => array(
						'description'       => 'Current page of the collection.',
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page' => array(
						'description'       => 'Maximum number of items to be returned in result set.',
						'type'              => 'integer',
						'default'           => 10,
						'sanitize_callback' => 'absint',
					),
				),
				'callback'            => function( $request ) {
					$page = $request->get_param( 'page' );
					$per_page = $request->get_param( 'per_page' );

					$logs = Log::get_logs();

					$max_page = ceil( count( $logs ) / $per_page );
					$page = max( min( $page, $max_page ), 1 );
					$paged_logs = array_slice( $logs, $per_page * ( $page - 1 ), $per_page );

					$response = new \WP_REST_Response( $paged_logs, 200 );
					$response->header( 'X-WP-Total', count( $logs ) );
					$response->header( 'X-WP-TotalPages', $max_page );

					return $response;
				},
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_logs_route' );
