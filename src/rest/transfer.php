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
				'methods'             => 'PUT',
				'permission_callback' => __NAMESPACE__ . '\\check_flightdeck_foreign_api_request',
				'callback'            => function( $request ) {
					$item_type = $request->get_header( 'X-Flightdeck-Item-Type' );
					$class_name = Connection_Item_Factory::get_class( $item_type );

					// Possible filter out the item. Allows returning false or a WP_Error to blacklist the item from being imported.
					$allow = apply_filters( "flightdeck/allow_import_$item_type", true, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Namespaced plugin hook.

					if ( false === $allow || is_wp_error( $allow ) ) {
						if ( is_wp_error( $allow ) ) {
							return $allow;
						}

						return new \WP_Error( 'IMPORT_FILTERED_OUT', __( 'Import was blocked.', 'flightdeck' ), array( 'status' => 500 ) );
					}

					return $class_name::import( $request );
				},
			),

			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'args'                => array(
					'type'        => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => Connection_Item_Factory::get_all_types(),
					),
					'items'       => array(
						'required' => true,
						'type'     => 'array',
						'minItems' => 1,
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
					$item_type = $request->get_param( 'type' );
					$items = $request->get_param( 'items' );
					$connection_mode = $request->get_param( 'connection' );

					$log = Log::get_instance();
					$log->name( 'xyz' );
					$log->save();
					$log->output( $request->get_param( 'output_logs' ) );

					switch ( $connection_mode ) {
						case 'http':
							$connection = new HTTP_Connection( get_flightdeck_setting( 'flightdeck_foreign_address' ), get_flightdeck_setting( 'flightdeck_foreign_password' ) );
							break;

						case 'zip':
							$connection = new Zip_Connection( "flightdeck-backup-$item_type-" . gmdate( 'Y-m-d-H-i-s' ) );
							break;

						default:
							return new \WP_Error( 'UNKNOWN_CONNECTION_TYPE', __( 'Unknown connection type', 'flightdeck' ) );
					}

					if ( ! $connection->is_allowed() ) {
						return new \WP_REST_Response( Rule_Message::all_to_wp_errors( $connection->get_allowed_messages() ), 400 );
					}

					set_time_limit( FLIGHTDECK_TIME_LIMIT );

					foreach ( $items as $item ) {
						$connection_item = Connection_Item_Factory::make( $item_type, $item );
						$log->add_connection_item_status( $connection_item, Log::STATUS_STARTED );

						// Possible filter out the item. Allows returning false or a WP_Error to blacklist the item from being exported.
						$allow = apply_filters( "allow_export_$item_type", true, $connection_item, $connection );

						if ( ! $allow || is_wp_error( $allow ) ) {
							if ( is_wp_error( $allow ) ) {
								$log->add_connection_item_status( $connection_item, $allow );
							} else {
								$log->add_connection_item_status( $connection_item, new \WP_Error( 'EXPORT_FILTERED_OUT', __( 'Item removed by filter.' ) ) );
							}

							continue;
						}

						$success = $connection->transfer( $connection_item );
						$log->add_connection_item_status( $connection_item, $success );
					}

					$connection->close();

					send_transfer_complete_email( get_current_user_id(), 'both' );
				},
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_transfer_route' );
