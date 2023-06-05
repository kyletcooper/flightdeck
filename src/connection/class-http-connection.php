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
		return apply_filters( 'flightdeck/http_connection_warnings', array(), $this ); // phpcs:ignore -- Namespaced hook
	}

	/**
	 * Returns an array containing any messages about if the connection is allowed.
	 *
	 * All rules are run on filters.
	 *
	 * @return Rule_Message[] The array of connection rule messages.
	 */
	public function get_allowed_messages() {
		return apply_filters( 'flightdeck/http_connection_is_allowed', array(), $this ); // phpcs:ignore -- Namespaced hook
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
	 * @return HTTP_Response The response data.
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
		$http_response                            = new HTTP_Response( $resp );

		$log->func_end( __FUNCTION__, $http_response );

		return $http_response;
	}

	/**
	 * Transfers a connection item.
	 *
	 * @param IConnection_Item $connection_item The item to send.
	 */
	public function transfer( $connection_item ) {
		foreach ( $connection_item->get_dependency_items() as $dependency_item ) {
			$this->transfer( $dependency_item );
		}

		if ( ! $connection_item->can_send_self() ) {
			return false;
		}

		$response = $this->send_request(
			'/flightdeck/v1/transfer',
			array(
				'method'  => 'PUT',
				'body'    => $connection_item->get_body(),
				'headers' => array(
					...$connection_item->get_headers(),
					'X-Flightdeck-Transfer-ID' => -1,
					'X-Flightdeck-Item-Type'   => Connection_Item_Factory::get_type( $connection_item ),
				),
			)
		);

		return $response->to_wp_error();
	}

	/**
	 * Closes the connection.
	 */
	public function close(){}
}
