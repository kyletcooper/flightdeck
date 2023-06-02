<?php
/**
 * Creates the rest API route for checking the connection.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Registers rest API route for checking the connection.
 *
 * @since 1.0.0
 */
function register_flightdeck_api_check_connection_route() {
	register_rest_route(
		'flightdeck/v1',
		'/connection',
		array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'permission_callback' => __NAMESPACE__ . '\\check_flightdeck_foreign_api_request',
				'callback'            => function() {
					global $wp_version;
					global $wpdb;

					return array(
						'php_version'        => PHP_VERSION,
						'wordpress_version'  => $wp_version,
						'flightdeck_version' => FLIGHTDECK_VERSION,
						'is_multisite'       => is_multisite(),
						'table_prefix'       => $wpdb->prefix,
					);
				},
			),
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => __NAMESPACE__ . '\\current_user_can_use_flightdeck',
				'callback'            => function( $request ) {
					// Gets connection details.
					$address = get_flightdeck_setting( 'flightdeck_foreign_address' );
					$password = get_flightdeck_setting( 'flightdeck_foreign_password' );

					$connection = new HTTP_Connection( $address, $password );

					$thumbnail_url = get_transient( 'foreign_address_thumbnail_url' );
					$favicon_url = get_transient( 'foreign_address_favicon_url' );

					if ( false === $thumbnail_url || false === $favicon_url ) {
						if ( filter_var( $address, FILTER_VALIDATE_URL ) ) {
							include_once FLIGHTDECK_PLUGIN_DIR . '/src/classes/class-site-scraper.php';

							$scraper = new Site_Scraper( $address );
							$scraper->retrieve();

							$thumbnail_url = $scraper->get_thumbnail_image_url();
							$favicon_url = $scraper->get_favicon_url();

							set_transient( 'foreign_address_thumbnail_url', $thumbnail_url );
							set_transient( 'foreign_address_favicon_url', $favicon_url );
						}
					}

					return array(
						'address'  => $address,
						'allowed'  => $connection->is_allowed(),
						'name'     => wp_parse_url( $address, PHP_URL_HOST ),
						'image'    => $thumbnail_url,
						'favicon'  => $favicon_url,
						'errors'   => $connection->get_allowed_messages(),
						'warnings' => $connection->get_warning_messages(),
					);
				},
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\register_flightdeck_api_check_connection_route' );
