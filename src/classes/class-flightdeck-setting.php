<?php
/**
 * Contains the Flightdeck_Setting class.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Represents a setting used in Flightdeck.
 */
class Flightdeck_Setting {
	/**
	 * Unique slug for an option.
	 *
	 * @var string name
	 */
	public $name;

	/**
	 * Array of args.
	 *
	 * @see __construct() for details.
	 *
	 * @var array args
	 */
	public $args;

	/**
	 * Creates a settings.
	 *
	 * @param string $name Unique slug for an option.
	 *
	 * @param array  $args Array of args.
	 */
	public function __construct( $name, $args = array() ) {
		$this->name = $name;
		$this->args = wp_parse_args(
			$args,
			array(
				'type'              => 'string',
				'default'           => null,
				'validate_callback' => null,
				'sanitize_callback' => null,
				'send_in_rest'      => true,
				'view_capability'   => 'manage_options',
				'edit_capability'   => 'manage_options',
				'allow_export'      => false,
			)
		);
	}

	/**
	 * Sets the value of the settings.
	 *
	 * Will fail for permission errors.
	 *
	 * @param mixed $new_value The new value to set.
	 *
	 * @param bool  $check_permissions If true, user capability will be set before setting the value. If false, this is skipped.
	 *
	 * @return WP_Error|bool True on success, WP_Error or false on error.
	 */
	public function set( $new_value, $check_permissions = true ) {
		$validator = $this->args['validate_callback'];
		$sanitizer = $this->args['sanitize_callback'];

		if ( $check_permissions && ! current_user_can( $this->args['edit_capability'] ) ) {
			return new \WP_Error( 'INSUFFICIENT_PERMISSIONS', __( 'You do not have permission to perform this action.', 'flightdeck' ) );
		}

		if ( is_callable( $validator ) ) {
			$validation_passes = call_user_func( $validator, $new_value );

			if ( is_wp_error( $validation_passes ) || false === $validation_passes ) {
				return $validation_passes;
			}
		}

		settype( $new_value, $this->args['type'] );

		if ( is_callable( $sanitizer ) ) {
			$new_value = call_user_func( $sanitizer, $new_value );
		}

		if ( ! $this->has_value( $check_permissions ) ) {
			// We cannot update an option with the value boolean false if it has not been created first.
			add_option( $this->name, $new_value );
		}

		return update_option( $this->name, $new_value );
	}

	/**
	 * Checks if an option has been set.
	 *
	 * @param bool $check_permissions If true, the user capability will be checked before checking.
	 *
	 * @return bool|WP_Error If the option has been set. WP_Error if the user has no permissions.
	 */
	public function has_value( $check_permissions = true ) {
		if ( ! current_user_can( $this->args['edit_capability'] ) && ! $check_permissions ) {
			return new \WP_Error( 'INSUFFICIENT_PERMISSIONS', __( 'You do not have permission to perform this action.', 'flightdeck' ) );
		}

		$temp = uniqid( 'not-exists-' );
		return get_option( $this->name, $temp ) !== $temp;
	}

	/**
	 * Gets the current value of the setting.
	 *
	 * Will return null if the current user doesn't have permission to view it.
	 *
	 * @param bool $check_permissions Whether to check if the current user can view the option's value. Defaults to true.
	 *
	 * @return mixed The value of the setting. Coerced to the args type.
	 */
	public function get( $check_permissions = true ) {
		if ( $check_permissions && ! current_user_can( $this->args['view_capability'] ) ) {
			$value = null;
			settype( $value, $this->args['type'] );
			return $value;
		}

		$value = get_option( $this->name, $this->args['default'] );

		settype( $value, $this->args['type'] );

		return $value;
	}

	/**
	 * Deletes the stored option.
	 *
	 * @return bool True if the option was deleted, false otherwise.
	 */
	public function delete() {
		if ( ! current_user_can( $this->args['edit_capability'] ) ) {
			return new \WP_Error( 'INSUFFICIENT_PERMISSIONS', __( 'You do not have permission to perform this action.', 'flightdeck' ) );
		}

		return delete_option( $this->name );
	}

	/**
	 * Helper function for getting the REST API schema for this setting.
	 *
	 * Useful for register_rest_route() route args. This does not register a sanitize callback to prevent double sanitization.
	 *
	 * @return array The array of settings.
	 */
	public function get_rest_api_schema() {
		return array(
			'type'              => $this->args['type'],
			'validate_callback' => $this->args['validate_callback'],
		);
	}

	/**
	 * Gets a setting object by it's name.
	 *
	 * @param string $name The unique slug of the setting.
	 *
	 * @return Flightdeck_Setting|null The setting or null if not found.
	 */
	public static function get_setting( $name ) {
		$settings = get_flightdeck_settings();

		foreach ( $settings as $setting ) {
			if ( $name === $setting->name ) {
				return $setting;
			}
		}

		return null;
	}

	/**
	 * Returns the array of settings for a REST API response.
	 *
	 * @return array Key value pair array of all settings, respecting the send_in_rest arg.
	 */
	public static function prepare_rest_response() {
		$settings = get_flightdeck_settings();
		$ret      = array();

		foreach ( $settings as $setting ) {
			if ( $setting->args['send_in_rest'] ) {
				$ret[ $setting->name ] = $setting->get();
			} else {
				$ret[ $setting->name ] = '';
			}
		}

		return $ret;
	}
}

/**
 * Returns all Flightdeck_Settings.
 *
 * @return Flightdeck_Setting[] The array of settings
 */
function get_flightdeck_settings() {
	return apply_filters( 'flightdeck/get_settings', array() ); // phpcs:ignore -- Namespaced hook.
}

/**
 * Gets the value of a setting registered with a Flightdeck_Settings object.
 *
 * @param string $name Name of the setting.
 *
 * @param bool   $check_permissions Whether to check if the current user can view the option's value. Defaults to true.
 *
 * @return mixed Value of the setting
 */
function get_flightdeck_setting( $name, $check_permissions = true ) {
	$setting = Flightdeck_Setting::get_setting( $name );

	if ( ! $setting ) {
		return null;
	}

	return $setting->get( $check_permissions );
}
