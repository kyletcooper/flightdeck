<?php
/**
 * Contains the Zip_Connection class.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * A connection which adds the transfers to a zip and downloads it.
 */
class Zip_Connection implements IConnection {
	/**
	 * The ZIP file.
	 *
	 * @var ZIP $zip
	 */
	public $zip;

	/**
	 * Creates the ZIP connection.
	 *
	 * @param string $name Name of the ZIP file.
	 */
	public function __construct( $name ) {
		include_once FLIGHTDECK_PLUGIN_DIR . '/src/classes/class-zip.php';
		$this->zip = new ZIP( $name );
	}

	/**
	 * Returns an array containing any messages about if the connection is allowed.
	 *
	 * @return Rule_Message[] The array of connection rule messages.
	 */
	public function get_allowed_messages() {
		return array();
	}

	/**
	 * Checks if the connection is allowed.
	 *
	 * @return bool Whether the connection is allowed.
	 */
	public function is_allowed() {
		return true;
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

		return $this->zip->add_file_from_string( $connection_item->get_name() . '.sql', $connection_item->get_body() );
	}

	/**
	 * Closes the connection and downloads the zip.
	 */
	public function close() {
		$this->zip->download();
	}
}
