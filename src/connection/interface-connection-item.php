<?php
/**
 * Contains the Connection_Item interface.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Represents an item to be sent through a connection.
 */
interface IConnection_Item {
	/**
	 * Checks if this item can be transferred. Does not prevent dependency items being transferred.
	 *
	 * @return bool True if can be sent, false otherwise.
	 */
	public function can_send_self();

	/**
	 * Returns a descriptive name of an item.
	 *
	 * @return string The item's name.
	 */
	public function get_name();

	/**
	 * Gets header metadata for an item.
	 *
	 * @return array Associative array of headers.
	 */
	public function get_headers();

	/**
	 * Returns the body of an item.
	 *
	 * @return string The body of the item.
	 */
	public function get_body();

	/**
	 * Returns an array of items that should also be transferred with this item.
	 *
	 * @return IConnection_Item[] Array of items.
	 */
	public function get_dependency_items();

	/**
	 * Imports a connection item from a rest request.
	 *
	 * @param \WP_Rest_Request $request The request sent with the item.
	 *
	 * @return mixed The response to the rest request.
	 */
	public static function import( $request );
}
