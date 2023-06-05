<?php
/**
 * Creates the rest API route for sending transfers.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Registers rest API route for sending a transfer.
 *
 * @since 1.0.0
 */
function register_flightdeck_api_transfer_route() {
	register_rest_route(
		'flightdeck/v1',
		'/transfer',
		array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'args'                => array(
					'type'        => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array(
							'files',
							'database',
						),
					),
					'items'       => array(
						'required'          => true,
						'type'              => 'array',
						'minItems'          => 1,
						'sanitize_callback' => function( $value ) {
							return json_decode( $value, true );
						},
					),
					'connection'  => array(
						'type'    => 'string',
						'default' => 'http',
						'enum'    => array(
							'http',
							'zip',
						),
					),
					'output_logs' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'callback'            => function( $request ) {
					$type = $request->get_param( 'type' );
					$items = $request->get_param( 'items' );
					$connection_mode = $request->get_param( 'connection' );
					$output_logs = $request->get_param( 'output_logs' );

					$connection = null;

					switch ( $connection_mode ) {
						case 'http':
							$connection = new HTTP_Connection( get_flightdeck_setting( 'flightdeck_foreign_address' ), get_flightdeck_setting( 'flightdeck_foreign_password' ) );
							break;

						case 'zip':
							$connection = new Zip_Connection( "flightdeck-backup-$type-" . gmdate( 'Y-m-d-H-i-s' ) );
							break;
					}

					if ( ! $connection->is_allowed() ) {
						return new \WP_REST_Response( Rule_Message::all_to_wp_errors( $connection->get_allowed_messages() ), 400 );
					}

					$log = Log::get_instance();
					$log->name( "flightdeck-departure-$type-" . gmdate( 'Y-m-d-H-i' ) );
					$log->output( $output_logs );
					$log->save();

					$log->func_start( __FUNCTION__, func_get_args() );

					set_time_limit( FLIGHTDECK_TIME_LIMIT );

					foreach ( $items as $i => $item ) {
						$allow_item = apply_filters( "flightdeck/allow_export_$type", true, $item, $connection ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Namespaced plugin filter.

						if ( ! $allow_item ) {
							unset( $items[ $i ] );
						}
					}

					switch ( $type ) {
						case 'files':
							$connection->transfer_files( $items );
							break;

						case 'database':
							$connection->transfer_tables( $items );
							break;
					}

					$connection->close();

					$log->func_end( __FUNCTION__, Log::STATUS_FINISHED );
				},
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_transfer_route' );
