<?php
/**
 * Creates the rest API route for recieving a file.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Registers rest API route for recieving a file.
 *
 * @since 1.0.0
 */
function register_flightdeck_api_file_reciever_route() {
	register_rest_route(
		'flightdeck/v1',
		'/files',
		array(
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'permission_callback' => __NAMESPACE__ . '\\check_flightdeck_foreign_api_request',
				'callback'            => function( $request ) {
					// Recieve a file from a foreign server.

					$path     = WP_CONTENT_DIR . $request->get_header( 'X-Flightdeck-Path' );
					$contents = $request->get_body();

					if ( create_file_path( $path, $contents ) ) {
						return true;
					} else {
						return new \WP_Error( 'WRITE_FAILED', __( 'Writing file failed', 'flightdeck' ), array( 'status' => 500 ) );
					}
				},
			),

			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'args'                => array(
					'path' => array(
						'default' => '/',
						'type'    => 'string',
					),
				),
				'callback'            => function( $request ) {
					$full_path = trailingslashit( WP_CONTENT_DIR . $request->get_param( 'path' ) );
					$files     = array_merge( glob( $full_path . '*' ), glob( $full_path . '.*' ) );
					$resp      = array();

					foreach ( $files as $file ) {
						if ( '.' === basename( $file ) || '..' === basename( $file ) ) {
							continue;
						}

						$resp[] = array(
							'path'         => get_path_wp_content_relative( $file ),
							'type'         => is_dir( $file ) ? 'dir' : 'file',
							'name'         => basename( $file ),
							'parent'       => get_path_wp_content_relative( dirname( $file ) ),
							'lastmodified' => relative_date( filemtime( $file ) ),
						);
					}

					return $resp;
				},
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_file_reciever_route' );
