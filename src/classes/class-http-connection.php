<?php
/**
 * Contains functions for connecting to a remote server.
 *
 * @since 1.0.0
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Connects to a remote FlightDeck plugin so data can be sent to it.
 *
 * @since 1.0.0
 */
class HTTP_Connection implements IConnection {
	/**
	 * The address to connect to.
	 *
	 * @var string $address
	 */
	public $address;

	/**
	 * The password the address will require.
	 *
	 * @var string $password
	 */
	public $password;

	/**
	 * Initializes the connection.
	 *
	 * @param string $address The address to connect to.
	 *
	 * @param string $password The password the address will require.
	 */
	public function __construct( $address, $password ) {
		$rest_address = trailingslashit( $address ) . rest_get_url_prefix();

		$this->address  = $rest_address;
		$this->password = $password;
	}

	/**
	 * Returns an array containing any messages about warnings for the connection.
	 *
	 * All rules are run on filters.
	 *
	 * @return Rule_Message[] The array of connection rule messages.
	 */
	public function get_warning_messages() {
		return apply_filters( 'flightdeck/connection_warnings', array(), $this ); // phpcs:ignore -- Namespaced hook
	}

	/**
	 * Returns an array containing any messages about if the connection is allowed.
	 *
	 * All rules are run on filters.
	 *
	 * @return Rule_Message[] The array of connection rule messages.
	 */
	public function get_allowed_messages() {
		return apply_filters( 'flightdeck/connection_is_allowed', array(), $this ); // phpcs:ignore -- Namespaced hook
	}

	/**
	 * Checks all of the connection rules to see if the connection is allowed.
	 *
	 * @return bool If the connection is allowed.
	 */
	public function is_allowed() {
		return Rule_Message::all( $this->get_allowed_messages() );
	}

	/**
	 * Returns the user agent for requests.
	 *
	 * @return string The user agent string.
	 *
	 * @since 1.0.0
	 */
	public function get_user_agent() {
		return 'Flightdeck/' . FLIGHTDECK_VERSION;
	}


	/**
	 * Sends a request. Does not authenticate if the request is allowed!
	 *
	 * @param string $endpoint The rest endpoint to send to.
	 *
	 * @param array  $args Request args.
	 *
	 * @return Connection_Response The response data.
	 *
	 * @see https://developer.wordpress.org/reference/classes/WP_Http/request/
	 */
	public function send_request( $endpoint, $args = array() ) {
		$log = Log::get_instance();
		$log->func_start( __FUNCTION__, array( $endpoint, array_diff_key( $args, array( 'body' => 0 ) ) ) );
		// The above removes the body from the arguments sent to the log to save space.

		$args = wp_parse_args(
			$args,
			array(
				'method'             => 'POST',
				'headers'            => array(),
				'timeout'            => FLIGHTDECK_TIME_LIMIT,
				'user-agent'         => $this->get_user_agent(),
				'reject_unsafe_urls' => true,
				'sslverify'          => true,
				'body'               => '',
			)
		);

		$args['headers']['X-Flightdeck-Password'] = $this->password;
		$resp                                     = wp_remote_request( $this->address . $endpoint, $args );
		$connection_response                      = new Connection_Response( $resp );

		$log->func_end( __FUNCTION__, $connection_response );

		return $connection_response;
	}

	/**
	 * Recursively streams files and subfiles.
	 *
	 * @param array $files Array of file paths.
	 */
	public function transfer_files( $files ) {
		$filesystem = Filesystem::get_instance();

		$log = Log::get_instance();
		$log->func_start( __FUNCTION__, func_get_args() );

		foreach ( $files as $file ) {
			if ( 0 !== connection_aborted() ) {
				die();
			}

			$file = trailingslashit( WP_CONTENT_DIR ) . unleadingslashit( $file );

			if ( ! file_exists( $file ) ) {
				continue;
			}

			if ( $filesystem->is_dir( $file ) ) {
				$log->add_transfer_item_status( 'dir', $file, Log::STATUS_STARTED );

				$sub_files = $filesystem->get_dir_files( $file );
				$this->transfer_files( $sub_files );

				$log->add_transfer_item_status( 'dir', $file, Log::STATUS_FINISHED );
			} else {
				$log->add_transfer_item_status( 'file', $file, Log::STATUS_STARTED );

				$response = $this->send_request(
					'/flightdeck/v1/files',
					array(
						'body'    => $filesystem->file_get( $file ),
						'headers' => array(
							'X-Flightdeck-Path' => get_path_wp_content_relative( $file ),
						),
					)
				);

				$log->add_transfer_item_status( 'file', $file, $response );
			}
		}

		$log->func_end( __FUNCTION__ );
	}

	/**
	 * Streams an array of table exports.
	 *
	 * @param array[] $tables Array of tables. Each sub-array should have a 'table' and 'rows' key. @see export_table.
	 */
	public function transfer_tables( $tables ) {
		global $wpdb;

		$log = Log::get_instance();
		$log->func_start( __FUNCTION__, func_get_args() );

		foreach ( $tables as $table ) {
			if ( 0 !== connection_aborted() ) {
				die();
			}

			$log->add_transfer_item_status( 'table', $table, Log::STATUS_STARTED );

			$response = $this->send_request(
				'/flightdeck/v1/tables',
				array(
					'body'    => export_table( $table ),
					'headers' => array(
						'X-Flightdeck-Prefix' => $wpdb->prefix,
						'X-Flightdeck-Table'  => get_path_wp_content_relative( $table ),
					),
				)
			);

			$log->add_transfer_item_status( 'table', $table, $response );
		}

		$log->func_end( __FUNCTION__ );
	}

	/**
	 * Closes the connection.
	 */
	public function close(){}
}
