<?php
/**
 * Contains AJAX action callbacks.
 *
 * @version 1.0.0
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Helper function for getting a POST value with a fallback/type.
 *
 * @param string $key The key of the array field.
 *
 * @param mixed  $default The fallback value. Defaults to empty string.
 *
 * @param string $type The type to convert to. Defaults to 'string'.
 *
 * @return mixed The value or the default value, in the provided type.
 *
 * @since 1.0.0
 */
function get_post_value( $key, $default = '', $type = 'string' ) {
	$value = $default;

	if ( isset( $_POST[ $key ] ) ) {
		$value = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
	}

	if ( 'array' === $type && 'string' === gettype( $value ) ) {
		$value = json_decode( $value, true );
	}

	settype( $value, $type );

	return $value;
}

/**
 * Checks the ajax referer, nonce and capability.
 *
 * Sends the JSON error on failure.
 *
 * @param string $action The name of the nonce action.
 *
 * @param string $capability The capability required to perform the action.
 *
 * @return void
 *
 * @since 1.0.0
 */
function check_ajax_referer_capability( $action, $capability ) {
	if ( ! check_ajax_referer( $action, false, false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Referrer could not be validated.', 'flightdeck' ),
			)
		);
	}

	if ( ! current_user_can( $capability ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'You are not authorised to complete this action.', 'flightdeck' ),
			)
		);
	}
}

/**
 * Syncs db tables to the connected site.
 */
function ajax_sync_connection() {
	check_ajax_referer_capability( 'flightdeck_nonce', 'manage_options' );

	$items = get_post_value( 'selection', array(), 'array' );
	$type  = get_post_value( 'type' );

	if ( count( $items ) < 1 ) {
		status_header( 400 );
		wp_send_json_error(
			array(
				'messages' => array(
					new Rule_Message(
						'TOO_FEW_ITEMS',
						false,
						array(
							'fail_message' => __( 'You must select at least one item.', 'flightdeck' ),
						)
					),
				),
			)
		);
	}

	$connection = new Connection( get_flightdeck_setting( 'flightdeck_foreign_address' ), get_flightdeck_setting( 'flightdeck_foreign_password' ) );

	if ( ! $connection->is_allowed() ) {
		status_header( 400 );
		wp_send_json_error(
			array(
				'messages' => Rule_Message::all_to_wp_errors( $connection->get_allowed_messages() ),
			)
		);
	}

	$log = Log::get_instance();
	$log->name( "flightdeck-departure-$type-" . gmdate( 'Y-m-d-H-i' ) );
	$log->output();
	$log->save();

	$log->func_start( __FUNCTION__, func_get_args() );

	switch ( $type ) {
		case 'files':
			foreach ( $items as $i => $file ) {
				$items[ $i ] = trailingslashit( WP_CONTENT_DIR ) . unleadingslashit( $file );
			}
			$connection->stream_files_recursive( $items );
			break;

		case 'tables':
			$connection->stream_tables( $items );
			break;

		default:
			status_header( 400 );
			$log->func_end( __FUNCTION__, Log::STATUS_FATAL );

			wp_send_json_error(
				array(
					'messages' => array(
						new Rule_Message(
							'UNKNOWN_SYNC_TYPE',
							false,
							array(
								'fail_message' => __( 'Unknown sync type.', 'flightdeck' ),
							)
						),
					),
				)
			);
			break;
	}

	$log->func_end( __FUNCTION__, Log::STATUS_FINISHED );
	die();
}
add_action( 'wp_ajax_sync_connection', __NAMESPACE__ . '\\ajax_sync_connection' );


/**
 * Syncs db tables to the connected site.
 */
function ajax_download_backup() {
	check_ajax_referer_capability( 'flightdeck_nonce', 'manage_options' );

	$items = get_post_value( 'selection', array(), 'array' );
	$type  = get_post_value( 'type', null, 'string' );

	if ( count( $items ) < 1 ) {
		status_header( 400 );
		wp_send_json_error(
			array(
				'messages' => array(
					new Rule_Message(
						'TOO_FEW_ITEMS',
						false,
						array(
							'fail_message' => __( 'You must select at least one item.', 'flightdeck' ),
						)
					),
				),
			)
		);
	}

	if ( 'tables' !== $type && 'files' !== $type ) {
		status_header( 400 );
		wp_send_json_error(
			array(
				'messages' => array(
					new Rule_Message(
						'UNKNOWN_SYNC_TYPE',
						false,
						array(
							'fail_message' => __( 'Unknown sync type.', 'flightdeck' ),
						)
					),
				),
			)
		);
	}

	include_once FLIGHTDECK_PLUGIN_DIR . '/src/class-zip.php';
	$zip = new ZIP( "flightdeck-backup-$type-" . gmdate( 'Y-m-d-H-i-s' ) );
	set_time_limit( FLIGHTDECK_TIME_LIMIT );

	foreach ( $items as $item ) {
		if ( 'tables' === $type ) {
			$zip->add_file_from_string( $item . '.sql', export_table( $item ) );
		} elseif ( 'files' === $type ) {
			$zip->add_file( $item, trailingslashit( WP_CONTENT_DIR ) . unleadingslashit( $item ) );
		}
	}

	$zip->download();
	die();
}
add_action( 'wp_ajax_download_backup', __NAMESPACE__ . '\\ajax_download_backup' );
