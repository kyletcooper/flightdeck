<?php
/**
 * Contains the Log class.
 *
 * @package flightdeck
 *
 * @since 1.0.0
 */

namespace flightdeck;

use stdClass;

/**
 * Contains the log of information about the current request.
 */
class Log {
	const STATUS_STARTED     = 'started';
	const STATUS_FINISHED    = 'done';
	const STATUS_SUCCESS     = 'success';
	const STATUS_FAILED      = 'failed';
	const STATUS_FATAL       = 'fatal';
	const STATUS_RECOVERABLE = 'recoverable';
	const STATUS_UNKNOWN     = 'unknown';

	/**
	 * The log for this request.
	 *
	 * @var Log $instance The global instance of the log for this request.
	 */
	private static $instance = null;

	/**
	 * The name of the log.
	 *
	 * @var string $log The name of the log.
	 */
	public $name;

	/**
	 * The items in the log.
	 *
	 * @var string[] $items The items in the log.
	 */
	public $items;

	/**
	 * Meta information about the log.
	 *
	 * @var stdClass[] $meta The associative meta information.
	 */
	public $meta;

	/**
	 * If the log should output.
	 *
	 * @var bool $output If the log should output.
	 */
	public $output = false;

	/**
	 * If the log should save to a file.
	 *
	 * @var bool $save If the log should save to a file.
	 */
	public $save = false;

	/**
	 * Creates the log.
	 */
	private function __construct() {
		$this->meta                  = new stdClass();
		$this->meta->schema          = 'meta';
		$this->meta->date            = gmdate( 'c' );
		$this->meta->current_user_id = get_current_user_id();
		$this->meta->type            = $this->get_request_type();
		$this->meta->request         = $this->get_server_value( 'REQUEST_URI' );
		$this->meta->method          = $this->get_server_value( 'REQUEST_METHOD' );
		$this->meta->ip_address      = $this->get_server_value( 'REMOTE_ADDR' );
		$this->meta->user_agent      = $this->get_server_value( 'HTTP_USER_AGENT' );
		$this->meta->post            = $_POST; // phpcs:ignore -- No verification required.
	}

	/**
	 * Creates or returns the existing instance of the log.
	 *
	 * @return Log The existing/new log.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gets the value of a key in the $_SERVER array.
	 *
	 * @param string $key Key of the array.
	 *
	 * @param mixed  $default Value to fallback to. Defaults to null.
	 *
	 * @return mixed The value of the key or the fallback.
	 */
	private function get_server_value( $key, $default = null ) {
		if ( isset( $_SERVER[ $key ] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
		}

		return $default;
	}

	/**
	 * Gets a constant string for the type of request.
	 *
	 * @return string The request type.
	 */
	private function get_request_type() {
		if ( wp_doing_ajax() ) {
			return 'AJAX';
		}

		if ( defined( 'REST_REQUEST' ) ) {
			return 'REST';
		}

		return 'STANDARD';
	}

	/**
	 * Sets the name of the log.
	 *
	 * @param string $name The new name.
	 */
	public function name( $name ) {
		$this->name = $name;
	}

	/**
	 * Sets the output status of the log.
	 *
	 * @param bool $output The new status.
	 */
	public function output( $output = true ) {
		$this->output = $output;
	}

	/**
	 * Sets the save status of the log.
	 *
	 * @param bool $save The new status.
	 */
	public function save( $save = true ) {
		$this->save = $save;

		if ( $this->save && $this->name ) {
			file_put_contents( FLIGHTDECK_LOGS_DIR . "/$this->name.log", wp_json_encode( $this->meta ), FILE_APPEND ); // phpcs:ignore -- WP_Filesystem_Direct does not support appending.
		}
	}


	/**
	 * Adds an item to the log.
	 *
	 * @param string $type Type of data.
	 *
	 * @param string $status The status of the item.
	 *
	 * @param mixed  $data Any additional data.
	 */
	public function add( $type, $status = null, $data = null ) {
		$line = new stdClass();

		$line->schema = 'line';
		$line->time   = time();
		$line->type   = $type;
		$line->status = $status;
		$line->data   = $data;

		$this->items[] = $line;

		$log_line = wp_json_encode( $line ) . PHP_EOL;

		if ( $this->save ) {
			file_put_contents( FLIGHTDECK_LOGS_DIR . "/$this->name.log", $log_line, FILE_APPEND ); // phpcs:ignore -- WP_Filesystem_Direct does not support appending.
		}

		if ( $this->output ) {
			echo $log_line; // phpcs:ignore -- This is only used when streaming the REST response.
		}
	}

	/**
	 * Logs the start of a key function.
	 *
	 * @param string $name Name of the function. Use __FUNCTION__.
	 *
	 * @param array  $arguments The args to the function. Use func_get_args().
	 */
	public function func_start( $name, $arguments ) {
		$this->add( $name, static::STATUS_STARTED, $arguments );
	}

	/**
	 * Logs the end of a key function.
	 *
	 * @param string $name Name of the function. Use __FUNCTION__.
	 *
	 * @param mixed  $data Any data about the success of the function/return value.
	 */
	public function func_end( $name, $data = null ) {
		$this->add( $name, static::STATUS_FINISHED, $data );
	}
}
