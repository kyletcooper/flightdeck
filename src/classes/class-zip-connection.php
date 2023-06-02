<?php
/**
 * Contains the Zip_Connection class.
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
	 * Transfers files.
	 *
	 * @param string[] $files Array of files.
	 */
	public function transfer_files( $files ) {
		foreach ( $files as $file ) {
			$this->zip->add_file( $file, trailingslashit( WP_CONTENT_DIR ) . unleadingslashit( $file ) );
		}
	}

	/**
	 * Streams an array of table exports.
	 *
	 * @param array[] $tables Array of tables. Each sub-array should have a 'table' and 'rows' key. @see export_table.
	 */
	public function transfer_tables( $tables ) {
		foreach ( $tables as $table ) {
			$this->zip->add_file_from_string( $table['table'] . '.sql', export_table( $table ) );
		}
	}

	/**
	 * Closes the connection and downloads the zip.
	 */
	public function close() {
		$this->zip->download();
	}
}
