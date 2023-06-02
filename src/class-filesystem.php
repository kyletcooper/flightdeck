<?php
/**
 * Contains the Filesystem class.
 *
 * @package flightdeck
 *
 * @since 1.0.0
 */

namespace flightdeck;

/**
 * Handles writing & reading files in a consistent way.
 */
class Filesystem {
	/**
	 * The instance of the Filesystem singleton.
	 *
	 * @var Filesystem The singleton instance.
	 */
	private static $instance = null;

	/**
	 * The built-in filesystem provided by WordPress.
	 *
	 * @var \WP_Filesystem_Base The built-in filesystem.
	 */
	public $wp_filesystem;

	/**
	 * Creates the singleton.
	 */
	private function __construct() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		global $wp_filesystem;
		WP_Filesystem();

		$this->wp_filesystem = $wp_filesystem;
	}

	/**
	 * Returns the singleton instance of the Filesystem class.
	 *
	 * @return Filesystem The filesystem instance.
	 */
	public static function get_instance() {
		if ( null !== static::$instance ) {
			return static::$instance;
		}

		return new self();
	}

	/**
	 * Checks if a file is within a directory (or is a descendent of it in any way).
	 *
	 * @param string $file Path of the file to search for.
	 *
	 * @param string $dir Path of the directory to search in.
	 *
	 * @return bool True if within, false if not within (or not found).
	 */
	function file_within_directory( $file, $dir ) {
		$base     = realpath( $dir );
		$filename = realpath( $file );

		if ( false === $filename || strncmp( $filename, $base, strlen( $base ) ) !== 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the contents of a file as a string.
	 *
	 * @param string $file The path of the file.
	 *
	 * @return string|false Read data on success, false on failure.
	 */
	public function file_get( $file ) {
		return $this->wp_filesystem->get_contents( $file );
	}

	/**
	 * Read the contents of a file straight to the output.
	 *
	 * @param string $file The path of the file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function file_read( $file ) {
		$res = readfile( $file );

		return false !== $res && $res > 0;
	}

	/**
	 * Stream the contents of a file to the output.
	 *
	 * @param string $file The path of the file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function file_stream( $file ) {
		$handle = fopen( $file, 'r' );
		$res    = fpassthru( $handle );

		return false !== $res && $res > 0;
	}

	/**
	 * Write to a file.
	 *
	 * @param string $file The path of the file.
	 *
	 * @param string $contents The content to set for the file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function file_put( $file, $contents ) {
		$this->wp_filesystem->put_contents( $file, $contents );
	}

	/**
	 * Append to a file.
	 *
	 * @param string $file The path of the file.
	 *
	 * @param string $contents The content to add to the end of the file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function file_put_append( $file, $contents ) {
		$result = file_put_contents( $file, $contents, FILE_APPEND );

		return false !== $result && $result > 0;
	}

	/**
	 * Creates a file and all directories leading to it (if they do not exist).
	 *
	 * @param string $path Absolute path to file destination.
	 *
	 * @param string $content The content of the file.
	 *
	 * @return bool True on success, false on error.
	 */
	function file_create_path( $path, $content ) {
		$dir = dirname( $path );
		$this->dir_create_path( $dir );
		return $this->put_contents( $path, $content );
	}

	/**
	 * Checks if a file is a directory.
	 *
	 * @param string $path The path of the file.
	 *
	 * @return bool True if the file is a directory, otherwise false.
	 */
	public function is_dir( $path ) {
		return $this->wp_filesystem->is_dir( $path );
	}

	/**
	 * Recursivly create directories based on full path.
	 *
	 * @param string $dir Full path to attempt to create.
	 *
	 * @return bool Whether the path was created. True if path already exists.
	 */
	public function dir_create_path( $dir ) {
		return wp_mkdir_p( $dir );
	}

	/**
	 * Returns an array of file paths within a directory.
	 *
	 * The file paths return are absolute.
	 *
	 * @param string $dir The directory to scan.
	 *
	 * @return string[] Array of file paths within the directory.
	 */
	public function get_dir_files( $dir, $absolute_paths = true ) {
		$files = array_diff( scandir( $dir ), array( '..', '.' ) );

		if ( $absolute_paths ) {
			foreach ( $files as $i => $file ) {
				$files[ $i ] = trailingslashit( $dir ) . $file;
			}
		}

		return $files;
	}

	public function get_dir_files_info( $dir ) {
		return $this->wp_filesystem->dirlist( $dir, false, false );
	}

	/**
	 * Deletes a file or directory.
	 *
	 * @param string       $file Path to the file or directory.
	 *
	 * @param bool         $recursive Optional. If set to true, deletes files and folders recursively. Default false.
	 *
	 * @param string|false $type Type of resource. 'f' for file, 'd' for directory. Default false.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function delete( $file, $recursive = false, $type = false ) {
		return $this->wp_filesystem->delete( $file, $recursive, $type );
	}

	/**
	 * Checks if a file or directory exists.
	 *
	 * @param string $file Path to file or directory.
	 *
	 * @return bool Whether $file exists or not.
	 */
	public function exists( $file ) {
		return $this->wp_filesystem->exists( $file );
	}
}
