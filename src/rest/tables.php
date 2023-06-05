<?php
/**
 * Creates the rest API route getting/setting flightdeck settings.
 *
 * @package flightdeck
 */

namespace flightdeck;

use WP_Error;

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
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'callback'            => function() {
					global $wpdb;

					if ( ! $wpdb->has_cap( 'identifier_placeholders' ) ) {
						return new WP_Error( 'CANNOT_ESCAPE_IDENFITIERS', __( 'You must use version 6.2.0 or greater of WordPress.' ) );
					}

					$tables = get_all_table_names();

					$ret = array();
					foreach ( $tables as $table ) {
						$ret[] = array(
							'name'        => $table,
							'count'       => get_table_row_count( $table ),
							'primary_key' => get_table_primary_key( $table ),
						);
					}

					return $ret;
				},
			),
		)
	);

	register_rest_route(
		'flightdeck/v1',
		'/tables/(?P<table>[\w]+)',
		array(
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
			'args'                => array(
				'table'    => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'esc_sql',
				),
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
					'validate_callback' => function( $value ) {
						return $value <= 100;
					},
				),
			),
			'callback'            => function( $request ) {
				global $wpdb;

				if ( ! $wpdb->has_cap( 'identifier_placeholders' ) ) {
					return new WP_Error( 'CANNOT_ESCAPE_IDENFITIERS', __( 'You must use version 6.2.0 or greater of WordPress.' ) );
				}

				$table = $request->get_param( 'table' );
				$page = $request->get_param( 'page' );
				$per_page = $request->get_param( 'per_page' );

				$row_count = get_table_row_count( $table );
				$max_page = ceil( $row_count / $per_page );

				$page = min( max( 1, $page ), $max_page );
				$offset = ( $page - 1 ) * $per_page;

				$rows = get_table_rows( $table, $per_page, $offset );

				$response = new \WP_REST_Response( $rows );
				$response->header( 'X-WP-Total', $row_count );
				$response->header( 'X-WP-TotalPages', $max_page );

				return $response;
			},
		),
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_tables_route' );
