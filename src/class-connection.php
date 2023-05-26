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
class Connection {
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
	 *
	 * @param bool  $die_on_abort Whether or not to die if the user aborts the connection.
	 */
	public function stream_files_recursive( $files, $die_on_abort = true ) {
		$log = Log::get_instance();
		$log->func_start( __FUNCTION__, func_get_args() );

		foreach ( $files as $file ) {
			if ( $die_on_abort && 0 !== connection_aborted() ) {
				die();
			}

			if ( ! file_exists( $file ) ) {
				continue;
			}

			$allow_file = apply_filters( 'flightdeck/allow_export_file', true, $file, $this );

			if ( ! $allow_file ) {
				continue;
			}

			if ( is_dir( $file ) ) {
				$log->add(
					'dir',
					Log::STATUS_STARTED,
					array(
						'name' => $file,
					)
				);

				$sub_files = array_diff( scandir( $file ), array( '..', '.' ) );

				foreach ( $sub_files as $i => $sub_file ) {
					$sub_files[ $i ] = trailingslashit( $file ) . $sub_file;
				}

				$this->stream_files_recursive( $sub_files );

				$log->add(
					'dir',
					Log::STATUS_FINISHED,
					array(
						'name' => $file,
					)
				);
			} else {
				$log->add(
					'file',
					Log::STATUS_STARTED,
					array(
						'name' => $file,
					)
				);

				$response = $this->send_request(
					'/flightdeck/v1/files',
					array(
						'body'    => file_get_contents( $file ),
						'headers' => array(
							'X-Flightdeck-Path' => get_path_wp_content_relative( $file ),
						),
					)
				);

				$log->add(
					'file',
					$response->ok ? Log::STATUS_SUCCESS : Log::STATUS_FAILED,
					array(
						'name' => $file,
					)
				);
			}
		}

		$log->func_end( __FUNCTION__ );
	}

	/**
	 * Streams an array of table exports.
	 *
	 * @param string[] $tables Array of table names.
	 *
	 * @param bool     $die_on_abort Whether or not to die if the user aborts the connection.
	 */
	public function stream_tables( $tables, $die_on_abort = true ) {
		$log = Log::get_instance();
		$log->func_start( __FUNCTION__, func_get_args() );

		foreach ( $tables as $table ) {
			if ( $die_on_abort && 0 !== connection_aborted() ) {
				die();
			}

			$log->add(
				'table',
				Log::STATUS_STARTED,
				array(
					'name' => $table,
				)
			);

			$response = $this->send_request(
				'/flightdeck/v1/tables',
				array(
					'body' => export_table( $table ),
				)
			);

			$log->add(
				'table',
				$response->ok ? Log::STATUS_SUCCESS : Log::STATUS_FAILED,
				array(
					'name' => $table,
				)
			);
		}

		$log->func_end( __FUNCTION__ );
	}
}
