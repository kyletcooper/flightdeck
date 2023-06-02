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

					$path       = unleadingslashit( WP_CONTENT_DIR ) . leadingslashit( $request->get_header( 'X-Flightdeck-Path' ) );
					$contents   = $request->get_body();
					$filesystem = Filesystem::get_instance();
					$is_allowed = apply_filters( 'flightdeck/allow_import_file', true, $path ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Namespaced plugin hook.

					if ( false === $is_allowed || is_wp_error( $is_allowed ) ) {
						return new \WP_Error( 'FILE_NOT_ALLOWED', __( 'File was blocked from being overwritten', 'flightdeck' ), array( 'status' => 500 ) );
					}

					if ( $filesystem->file_create_path( $path, $contents ) ) {
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
					$filesystem = Filesystem::get_instance();
					$full_path = trailingslashit( WP_CONTENT_DIR . $request->get_param( 'path' ) );
					$files     = $filesystem->get_dir_files( $full_path );
					$resp      = array();

					foreach ( $files as $file ) {
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
