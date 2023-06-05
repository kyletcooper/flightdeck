<?php
/**
 * Contains the HTTP_Response class.
 *
 * @package flightdeck
 *
 * @since 1.0.0
 */

namespace flightdeck;

/**
 * A wrapper class to make is easier to use request responses.
 */
class HTTP_Response {
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

	/**
	 * Converts a response to a WP_Error (if it failed and can be converted).
	 *
	 * @return true|WP_Error True if the response was OK, converts to a WP_Error otherwise with as much data as possible.
	 */
	public function to_wp_error() {
		if ( $this->ok ) {
			return true;
		}

		if ( ! $this->is_body_json() ) {
			return new \WP_Error(
				'HTTP_RESPONSE_FAILED',
				__( 'Unknown error.', 'flightdeck' ),
				array(
					'body'   => $this->body,
					'status' => $this->code,
				)
			);
		}

		$json_resp = $this->get_body_json();

		if ( ! isset( $json_resp['message'] ) || ! isset( $json_resp['code'] ) ) {
			return new \WP_Error(
				'HTTP_RESPONSE_FAILED',
				__( 'Unknown JSON error format.', 'flightdeck' ),
				array(
					'body'   => $this->body,
					'status' => $this->code,
				)
			);
		}

		return new \WP_Error(
			$json_resp['code'],
			$json_resp['message'],
			array(
				'status' => $this->code,
			)
		);
	}
}
