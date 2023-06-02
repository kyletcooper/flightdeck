<?php
/**
 * Plugin Name:       FlightDeck
 * Description:       Sync WordPress content across two sites.
 * Version:           1.0.0
 * Requires at least: 6.2.0
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
 * 900 seconds = 15 minutes.
 *
 * @var int FLIGHTDECK_TIME_LIMIT
 */
define( 'FLIGHTDECK_TIME_LIMIT', 900 );

/**
 * The minimum required version of PHP.
 *
 * @var string FLIGHTDECK_REQUIRED_PHP_VERSION
 */
define( 'FLIGHTDECK_REQUIRED_PHP_VERSION', '8.0.0' );

/**
 * The minimum required version of PHP.
 *
 * 6.2.0 is required the wpdb ability to escape table names. @see https://core.trac.wordpress.org/ticket/52506 & https://make.wordpress.org/core/2022/10/08/escaping-table-and-field-names-with-wpdbprepare-in-wordpress-6-1/
 *
 * @var string FLIGHTDECK_REQUIRED_PHP_VERSION
 */
define( 'FLIGHTDECK_REQUIRED_WP_VERSION', '6.2.0' );

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
		wp_enqueue_style( 'material-icons-rounded' );
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
 * Adds the scripts/styles for the back and front end.
 *
 * @since 1.0.0
 */
function enqueue_both_assets() {
	wp_register_style( 'material-icons-rounded', 'https://fonts.googleapis.com/css2?family=Material+Icons+Round&display=block', array(), FLIGHTDECK_VERSION );
}
add_action( 'wp_loaded', __NAMESPACE__ . '\\enqueue_both_assets' );

/**
 * Displays the admin notice to let the user know the site is locked.
 */
function display_indicator_bar() {
	if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
		return false;
	}

	if ( is_admin() && ! get_flightdeck_setting( 'flightdeck_lock_show_indicator_bar_backend' ) ) {
		return false;
	}

	if ( ! is_admin() && ! get_flightdeck_setting( 'flightdeck_lock_show_indicator_bar_frontend' ) ) {
		return false;
	}

	wp_enqueue_style( 'material-icons-rounded' );

	$is_locked   = get_flightdeck_setting( 'flightdeck_lock_local_changes' );
	$foreign_url = get_flightdeck_setting( 'flightdeck_foreign_address' );
	$local_url   = site_url();

	$colors      = array(
		'#ef4444',
		'#f97316',
		'#f59e0b',
		'#eab308',
		'#84cc16',
		'#22c55e',
		'#10b981',
		'#14b8a6',
		'#06b6d4',
		'#0ea5e9',
		'#3b82f6',
		'#6366f1',
		'#8b5cf6',
		'#a855f7',
		'#d946ef',
		'#ec4899',
		'#f43f5e',
	);
	$color_index = hexdec( substr( sha1( $local_url ), 0, 10 ) ) % count( $colors );
	$bg_color    = $colors[ $color_index ];

	?>

	<div class="flightdeck_notice">
		<?php

		// translators: %s is the URL of the current site.
		echo esc_html( sprintf( __( 'Working on %s', 'flightdeck' ), $local_url ) );

		?>

		<span class="material-icons-round" aria-hidden="true" title="<?php echo esc_attr( $is_locked ? __( 'Changes locked', 'flightdeck' ) : __( 'Changes allowed', 'flightdeck' ) ); ?>" aria-label="<?php echo esc_attr( $is_locked ? __( 'Changes locked', 'flightdeck' ) : __( 'Changes allowed', 'flightdeck' ) ); ?>">
			<?php echo esc_html( $is_locked ? 'lock' : 'lock_open' ); ?>
		</span>

		<?php if ( $foreign_url ) : ?>
			<a href="<?php echo esc_url( $foreign_url ); ?>" class="material-icons-round" title="<?php esc_attr_e( 'Go to connected site', 'flightdeck' ); ?>" aria-label="<?php esc_attr_e( 'Go to connected site', 'flightdeck' ); ?>">
				<span aria-hidden="true">
					swap_horiz
				</span>	
			</a>
		<?php endif; ?>
	</div>

	<style>
		.flightdeck_notice{
			display: flex !important;
			align-items: center;
			justify-content: center;
			gap: 16px;
			background-color: rgb(59 130 246);
			background-color: <?php echo esc_html( $bg_color ); ?>;
			color: #fff;
			text-align: center;
			line-height: 32px;
			position: fixed;
			height: 32px;
			top: 0;
			right: 0;
			left: 0;
			z-index: 99999;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size: 13px;
		}

		.flightdeck_notice .material-icons-round{
			font-size: 18px;
		}

		.flightdeck_notice a{
			display: block;
			color: #fff;
			text-decoration: none;
			border-radius: 999px;
			padding: 4px;
		}

		.flightdeck_notice a:hover,
		.flightdeck_notice a:focus{
			background: rgba(255, 255, 255, 0.3);
		}

		#wpadminbar{
			top: 32px;
		}

		html.wp-toolbar{
			padding-top: calc(32px + 32px);
		}

		@media screen and (max-width: 782px){
			html.wp-toolbar {
				padding-top: calc(46px + 32px);
			}
		}

		@media screen and (max-width: 600px){
			html.wp-toolbar {
				padding-top: calc(0px + 32px);
			}

			#wpadminbar{
				top: 0px;
			}
		}

		<?php if ( $is_locked ) : ?>
			.page-title-action,
			.row-actions,
			.edit-tags-php #submit,
			.edit-tag-actions,
			.plupload-upload-ui,
			.upload-php .page-title-action,
			.edit-post-header__settings,
			#major-publishing-actions{
				opacity: 0.5 !important;
				filter: grayscale(1) !important;
				pointer-events: none !important;
			}
		<?php endif; ?>
	</style>

	<?php
}
add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\display_indicator_bar' );

/**
 * Helper function that checks if the user can edit/view Flightdeck.
 *
 * @return bool True if allowed, false if not.
 */
function current_user_can_use_flightdeck() {
	return current_user_can( 'manage_options' );
}

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
