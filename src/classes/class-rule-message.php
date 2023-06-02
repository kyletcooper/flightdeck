<?php
/**
 * Contains the Rule_Message class.
 *
 * @version 1.0.0
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * A helper class to contain a message about whether a rule is passed or not.
 *
 * @since 1.0.0
 */
class Rule_Message {
	/**
	 * The code of the rule.
	 *
	 * @var string|int $code
	 */
	public $code;

	/**
	 * The human readable message.
	 *
	 * @var string $message
	 */
	public $message;

	/**
	 * If the rule is passed.
	 *
	 * @var boolean $success
	 */
	public $success;

	/**
	 * Any additional data attached to the message.
	 *
	 * @var mixed $data
	 */
	public $data;

	/**
	 * Initializes the message.
	 *
	 * @param string|int $code The code of the rule.
	 *
	 * @param boolean    $success If the rule is passed.
	 *
	 * @param mixed      $opts Additional opts. 'pass_message', 'fail_message' and 'data' are accepted.
	 */
	public function __construct( $code = '', $success = false, $opts = null ) {
		$opts = wp_parse_args(
			$opts,
			array(
				'pass_message' => __( 'Success!', 'flightdeck' ),
				'fail_message' => __( 'An error occured.', 'flightdeck' ),
				'data'         => array(),
			)
		);

		$this->code    = $code;
		$this->success = $success;
		$this->message = $success ? $opts['pass_message'] : $opts['fail_message'];
		$this->data    = $opts['data'];
	}

	/**
	 * Returns the success status.
	 *
	 * @return bool If the rule is passed.
	 */
	public function is_success() {
		return boolval( $this->success );
	}

	/**
	 * Converts the object to a WP_Error.
	 *
	 * @return WP_Error|null The WP_Error or null if a success.
	 */
	public function to_wp_error() {
		if ( $this->is_success() ) {
			return null;
		}

		return new \WP_Error( $this->code, $this->message, $this->data );
	}

	/**
	 * Returns true if all rules are met.
	 *
	 * @param Rule_Message[] $rules The array of rules.
	 *
	 * @return bool If all rules are met.
	 */
	public static function all( $rules ) {
		foreach ( $rules as $rule ) {
			if ( ! $rule instanceof Rule_Message ) {
				return false;
			}

			if ( ! $rule->is_success() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true if any rules are met.
	 *
	 * @param Rule_Message[] $rules The array of rules.
	 *
	 * @return bool If any rules are met.
	 */
	public static function any( $rules ) {
		foreach ( $rules as $rule ) {
			if ( ! $rule instanceof Rule_Message ) {
				continue;
			}

			if ( $rule->is_success() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Converts all rules to a WP_Error.
	 *
	 * @param Rule_Message[] $rules The array of rules.
	 *
	 * @return WP_Error|null WP_Error with multiple causes or null if all successfull.
	 */
	public static function all_to_wp_errors( $rules ) {
		if ( static::all( $rules ) ) {
			return null;
		}

		$error = new \WP_Error();

		foreach ( $rules as $rule ) {
			if ( ! $rule instanceof Rule_Message ) {
				continue;
			}

			if ( $rule->is_success() ) {
				continue;
			}

			$error->add( $rule->code, $rule->message, $rule->data );
		}

		return $error;
	}
}
