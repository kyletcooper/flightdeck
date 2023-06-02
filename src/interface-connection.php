<?php

namespace flightdeck;

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
	 * Transfers files.
	 *
	 * @param string[] $files Array of files.
	 */
	public function transfer_files( $files );

	/**
	 * Streams an array of table exports.
	 *
	 * @param array[] $tables Array of tables. Each sub-array should have a 'table' and 'rows' key. @see export_table.
	 */
	public function transfer_tables( $tables );

	/**
	 * Closes the connection.
	 */
	public function close();
}
