<?php
/**
 * Contains all customs exceptions.
 *
 * @since 1.0.0
 *
 * @package flightdeck
 */

// phpcs:ignoreFile Generic.Files.OneObjectStructurePerFile.MultipleFound -- This file contains empty class definitions for new exception.s

namespace flightdeck;

/**
 * Exception type for when a connection is not allowed.
 *
 * @since 1.0.0
 */
class ConnectionBlockedException extends \Exception {}

/**
 * Exception type for when a feature has not been implemented yet.
 *
 * @since 1.0.0
 */
class NotImplementedException extends \Exception {}