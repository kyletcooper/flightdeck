<?php
/**
 * Contains the IConnection interface.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Interface for objects which send files & tables somewhere.
 */
interface IConnection {
	/**
	 * Returns an array containing any messages about if the connection is allowed.
	 *
	 * @return Rule_Message[] The array of connection rule messages.
	 */
	public function get_allowed_messages();

	/**
	 * Checks if the connection is allowed.
	 *
	 * @return bool Whether the connection is allowed.
	 */
	public function is_allowed();

	/**
	 * Transfers a connection item.
	 *
	 * @param IConnection_Item $connection_item The item to send.
	 *
	 * @return bool|WP_Error True on success, WP_Error or false on failure.
	 */
	public function transfer( $connection_item );

	/**
	 * Closes the connection.
	 */
	public function close();
}
