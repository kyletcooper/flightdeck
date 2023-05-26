<?php
/**
 * Plugin Name:       FlightDeck
 * Description:       Sync WordPress content across two sites.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4.0
 * Author:            Web Results Direct
 * Author URI:        https://wrd.studio
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       flightdeck
 * Domain Path:       /languages
 *
 * Initizialises the Flightdeck plugin.
 *
 * @since 1.0.0
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * The version of the plugin
 *
 * @var string FLIGHTDECK_VERSION
 */
define( 'FLIGHTDECK_VERSION', '1.0.0' );

/**
 * The max execution time used when syncing files.
 *
 * @var int FLIGHTDECK_TIME_LIMIT
 */
define( 'FLIGHTDECK_TIME_LIMIT', 500 );

/**
 * The minimum required version of PHP.
 *
 * @var string FLIGHTDECK_REQUIRED_PHP_VERSION
 */
define( 'FLIGHTDECK_REQUIRED_PHP_VERSION', '8.0.0' );

/**
 * The minimum required version of PHP.
 *
 * @var string FLIGHTDECK_REQUIRED_PHP_VERSION
 */
define( 'FLIGHTDECK_REQUIRED_WP_VERSION', '5.0.0' );

/**
 * The base plugin file.
 *
 * @var string FLIGHTDECK_PLUGIN_FILE
 */
define( 'FLIGHTDECK_PLUGIN_FILE', __FILE__ );

/**
 * The base plugin directory.
 *
 * @var string FLIGHTDECK_PLUGIN_DIR
 */
define( 'FLIGHTDECK_PLUGIN_DIR', __DIR__ );

/**
 * The directory logs are stored in.
 *
 * @var string FLIGHTDECK_LOGS_DIR
 */
define( 'FLIGHTDECK_LOGS_DIR', WP_CONTENT_DIR . '/flightdeck/logs' );

/**
 * The URL of the directory logs are stored in.
 *
 * @var string FLIGHTDECK_LOGS_URL
 */
define( 'FLIGHTDECK_LOGS_URL', WP_CONTENT_URL . '/flightdeck/logs' );

/**
 * Includes all the needed source files.
 *
 * @since 1.0.0
 */
function include_flightdeck() {
	include_once __DIR__ . '/src/class-flightdeck-setting.php';
	include_once __DIR__ . '/src/class-connection-response.php';
	include_once __DIR__ . '/src/class-connection.php';
	include_once __DIR__ . '/src/class-rule-message.php';
	include_once __DIR__ . '/src/class-log.php';
	include_once __DIR__ . '/src/class-exceptions.php';

	include_once __DIR__ . '/src/helpers-filesystem.php';
	include_once __DIR__ . '/src/helpers-passwords.php';
	include_once __DIR__ . '/src/helpers-tables.php';
	include_once __DIR__ . '/src/helpers-logs.php';

	include_once __DIR__ . '/src/api-settings.php';
	include_once __DIR__ . '/src/api-connection.php';
	include_once __DIR__ . '/src/api-files.php';
	include_once __DIR__ . '/src/api-tables.php';
	include_once __DIR__ . '/src/api-logs.php';
	include_once __DIR__ . '/src/ajax.php';

	include_once __DIR__ . '/src/register-settings.php';
	include_once __DIR__ . '/src/register-connection-restrictions.php';
	include_once __DIR__ . '/src/register-password-requirements.php';
}
include_flightdeck();

/**
 * Adds the admin page.
 *
 * @since 1.0.0
 */
function add_flightdeck_admin_area() {
	add_menu_page(
		__( 'FlightDeck', 'flightdeck' ),
		__( 'FlightDeck', 'flightdeck' ),
		'manage_options',
		'flightdeck',
		function() {
			echo '<div id="flightdeck"></div>';
		},
		'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI1NTUiIGhlaWdodD0iNTU1IiB2aWV3Qm94PSIwIDAgNTU1IDU1NSI+CiAgPGcgaWQ9Ikdyb3VwXzMwMjUiIGRhdGEtbmFtZT0iR3JvdXAgMzAyNSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEzNzYgMTEzKSI+CiAgICA8cmVjdCBpZD0iUmVjdGFuZ2xlXzEwNDYiIGRhdGEtbmFtZT0iUmVjdGFuZ2xlIDEwNDYiIHdpZHRoPSI1NTUiIGhlaWdodD0iNTU1IiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMzc2IC0xMTMpIiBvcGFjaXR5PSIwIi8+CiAgICA8cGF0aCBpZD0iSW50ZXJzZWN0aW9uXzIxIiBkYXRhLW5hbWU9IkludGVyc2VjdGlvbiAyMSIgZD0iTTIwOS4wMjYsNDEwLjI1NiwzNzUuNjkyLDE5Mi4zMDgsMTU3Ljc0NCwzNTguOTc0LDY4LjEyOCwyNjkuMSw0NTIuNjE1LDExNS4zODUsMjk4LjksNDk5Ljg3MloiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDEzOTMuMTI4IC0xMTMuMTI4KSIgZmlsbD0iI2ZmZiIvPgogIDwvZz4KPC9zdmc+Cg==',
		75
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\add_flightdeck_admin_area' );

/**
 * Hides admin notices on the flightdeck page.
 *
 * @since 1.0.0
 */
function hide_notices_on_flightdeck() {
	global $hook_suffix;

	if ( 'toplevel_page_flightdeck' === $hook_suffix ) {
		echo '<style>.notice { display: none !important; }</style>';
	}
}
add_action( 'admin_print_styles', __NAMESPACE__ . '\\hide_notices_on_flightdeck', 99 );

/**
 * Adds the scripts/styles to the admin page.
 *
 * @param string $hook The current admin page.
 *
 * @since 1.0.0
 */
function enqueue_admin_assets( $hook ) {
	if ( 'toplevel_page_flightdeck' === $hook ) {
		wp_enqueue_style( 'material-icons-rounded', 'https://fonts.googleapis.com/css2?family=Material+Icons+Round&display=block', array(), FLIGHTDECK_VERSION );
		wp_enqueue_script( 'flightdeck', plugins_url( '/admin/dist/bundle.js', __FILE__ ), array(), FLIGHTDECK_VERSION, true );

		wp_localize_script(
			'flightdeck',
			'flightdeck',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'rest_url'   => get_rest_url(),
				'home_url'   => get_home_url(),
				'nonce'      => wp_create_nonce( 'flightdeck_nonce' ),
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets' );

/**
 * Runs on activation.
 *
 * @since 1.0.0
 */
function flightdeck_activation() {
	// Create logs folder.
	wp_mkdir_p( FLIGHTDECK_LOGS_DIR );
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\flightdeck_activation' );

/**
 * Runs on uninstall.
 *
 * @since 1.0.0
 */
function flightdeck_uninstall() {
	// Delete all options.
	$settings = get_flightdeck_settings();

	foreach ( $settings as $setting ) {
		$setting->delete();
	}

	// Delete all logs.
	$filesystem = get_filesystem();
	$filesystem->delete( WP_CONTENT_DIR . '/flightdeck' );
}
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\\flightdeck_uninstall' );
