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
	 * The name of the file to log to.
	 *
	 * @var string $log_name
	 */
	private $log_name = '';

	/**
	 * Whether or not to echo log lines out to the output.
	 *
	 * @var bool $output_logs
	 */
	private $output_logs = false;

	const REQUEST_STARTED = 'started';
	const REQUEST_DONE    = 'done';
	const REQUEST_SUCCESS = 'success';
	const REQUEST_FAILED  = 'failed';

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
	 * Sets the log name.
	 *
	 * @param string $name Name of the log file.
	 *
	 * @param bool   $output_logs If true, log lines will additional be ouput.
	 */
	public function set_log_name( $name, $output_logs = false ) {
		$this->log_name    = $name;
		$this->output_logs = $output_logs;
	}

	/**
	 * Adds to the log file & outputs if enabled.
	 *
	 * @param string $type The type of the log. E.g. function name.
	 *
	 * @param string $name Used to indentify down from the type.
	 *
	 * @param string $status Use class constants.
	 *
	 * @param mixed  $data Any additional data to log.
	 */
	public function log( $type, $name, $status, $data = null ) {
		$data = array(
			'timestamp' => time(),
			'type'      => $type,
			'name'      => $name,
			'status'    => $status,
			'data'      => $data,
		);

		$log_line = wp_json_encode( $data ) . PHP_EOL;

		if ( $this->log_name ) {
			add_to_log( $this->log_name, $log_line );
		}

		if ( $this->output_logs ) {
			echo $log_line; // phpcs:ignore -- This is only used when streaming the REST response.
		}
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
		$this->log( 'send_request', $this->address . $endpoint, static::REQUEST_STARTED );

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

		$this->log( 'send_request', $this->address . $endpoint, static::REQUEST_DONE, $connection_response );

		return $connection_response;
	}

	/**
	 * Starts a stream and sends data through it to the connection. Does not authenticate if the request is allowed!
	 *
	 * @param string $endpoint The API endpoint to send the request to.
	 *
	 * @param string $body The content to send. Default empty string.
	 *
	 * @param array  $headers HTTP headers for the request.
	 *
	 * @throws ConnectionBlockedException If the connection is not allowed.
	 *
	 * @return Connection_Response The response data.
	 */
	public function send_endpoint_request( $endpoint, $body = '', $headers = array() ) {
		set_time_limit( FLIGHTDECK_TIME_LIMIT );

		$response = $this->send_request(
			$endpoint,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		return $response;
	}

	/**
	 * Recursively streams files and subfiles.
	 *
	 * @param array $files Array of file paths.
	 *
	 * @param bool  $die_on_abort Whether or not to die if the user aborts the connection.
	 */
	public function stream_files_recursive( $files, $die_on_abort = true ) {
		$this->log( 'stream_files_recursive', get_current_user_id(), static::REQUEST_STARTED );

		foreach ( $files as $file ) {
			if ( $die_on_abort && 0 !== connection_aborted() ) {
				die();
			}

			$wp_content_relative_path = get_path_wp_content_relative( $file );

			if ( ! file_exists( $file ) ) {
				$this->log( 'file', $file, static::REQUEST_FAILED, __( 'File does not exist, skipping.', 'flightdeck' ) );
				continue;
			}

			if ( file_within_directory( $file, FLIGHTDECK_PLUGIN_DIR ) || file_within_directory( $file, FLIGHTDECK_LOGS_DIR ) ) {
				$this->log( 'file', $file, static::REQUEST_FAILED, __( 'File is within the FlightDeck plugin or log directory, skipping.', 'flightdeck' ) );
				continue;
			}

			if ( is_dir( $file ) ) {
				$this->log( 'dir', $wp_content_relative_path, static::REQUEST_STARTED );

				$sub_files = array_diff( scandir( $file ), array( '..', '.' ) );

				foreach ( $sub_files as $i => $sub_file ) {
					$sub_files[ $i ] = trailingslashit( $file ) . $sub_file;
				}

				$this->stream_files_recursive( $sub_files );

				$this->log( 'dir', $wp_content_relative_path, static::REQUEST_DONE );
			} else {
				$this->log( 'file', $wp_content_relative_path, static::REQUEST_STARTED );

				$response = $this->send_endpoint_request(
					'/flightdeck/v1/files',
					file_get_contents( $file ),
					array(
						'X-Flightdeck-Path' => $wp_content_relative_path,
					)
				);

				$this->log( 'file', $wp_content_relative_path, $response->ok ? static::REQUEST_SUCCESS : static::REQUEST_FAILED );
			}
		}

		$this->log( 'stream_files_recursive', get_current_user_id(), static::REQUEST_DONE );
	}

	/**
	 * Streams an array of table exports.
	 *
	 * @param string[] $tables Array of table names.
	 *
	 * @param bool     $die_on_abort Whether or not to die if the user aborts the connection.
	 */
	public function stream_tables( $tables, $die_on_abort = true ) {
		$this->log( 'stream_tables', get_current_user_id(), static::REQUEST_STARTED );

		foreach ( $tables as $table ) {
			if ( $die_on_abort && 0 !== connection_aborted() ) {
				die();
			}

			$this->log( 'table', $table, static::REQUEST_STARTED );

			$response = $this->send_endpoint_request( '/flightdeck/v1/tables', export_table( $table ) );

			$this->log( 'table', $table, $response->ok ? static::REQUEST_SUCCESS : static::REQUEST_FAILED );
		}

		$this->log( 'stream_tables', get_current_user_id(), static::REQUEST_DONE );
	}
}

/**
 * A wrapper class to make is easier to use request responses.
 */
class Connection_Response {
	/**
	 * If the response was successful.
	 *
	 * @var bool $ok
	 */
	public $ok;

	/**
	 * The HTTP status code of the response.
	 *
	 * @var int $code
	 */
	public $code;

	/**
	 * The body of the response.
	 *
	 * @var string $body
	 */
	public $body;

	/**
	 * Creates a response.
	 *
	 * @param array|\WP_Error $response HTTP response.
	 */
	public function __construct( $response ) {
		$this->code = wp_remote_retrieve_response_code( $response );
		$this->body = substr( wp_remote_retrieve_body( $response ), 0, 1024 );
		$this->ok   = 200 === $this->code;
	}

	/**
	 * Checks if the body of the response is valid JSON.
	 *
	 * @return bool True if is valid JSON, false otherwise.
	 */
	public function is_body_json() {
		json_decode( $this->body );
		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Converts the JSON body to an array.
	 *
	 * @return array|false JSON array data or false on error.
	 */
	public function get_body_json() {
		if ( ! $this->is_body_json() ) {
			return false;
		}

		return json_decode( $this->body, true );
	}
}
