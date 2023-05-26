<?php
/**
 * Contains the ZIP class.
 *
 * @package flightdeck
 */

namespace flightdeck;

use ZipArchive;

/**
 * Creates and downloads a ZIP file.
 */
class ZIP {
	/**
	 * The name of the string.
	 *
	 * @var string $name The name of the file.
	 */
	public $name;

	/**
	 * The internal ZIP file.
	 *
	 * @var ZipArchive $zip The ZIP archive.
	 */
	private $zip;

	/**
	 * Creates a new ZIP file.
	 *
	 * @param string $name The name of the file.
	 *
	 * @throws \Exception If file creation fails.
	 */
	public function __construct( $name ) {
		$this->name = $name;
		$this->zip  = new ZipArchive();

		if ( $this->zip->open( get_temp_dir() . $this->name, ZipArchive::CREATE ) !== true ) {
			throw new \Exception( 'File could not be created.' );
		}
	}

	/**
	 * Adds an existing file.
	 *
	 * This will recursively get all files in a directory if a directory is passed.
	 *
	 * @param string $file_name The name of the file in the ZIP.
	 *
	 * @param string $file_path The path of the file to add.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function add_file( $file_name, $file_path ) {
		if ( is_dir( $file_path ) ) {
			$sub_files = array_diff( scandir( $file_path ), array( '..', '.' ) );

			if ( count( $sub_files ) ) {
				foreach ( $sub_files as $sub_file ) {
					$sub_path = trailingslashit( $file_path ) . $sub_file;
					$sub_name = get_path_wp_content_relative( $sub_path );

					$this->add_file( $sub_name, $sub_path );
				}
			} else {
				$this->zip->addEmptyDir( get_path_wp_content_relative( $file_path ) );
			}

			return true;
		} else {
			return $this->zip->addFile( $file_path, unleadingslashit( $file_name ) );
		}
	}

	/**
	 * Creates a file from a string and adds it.
	 *
	 * @param string $name The name of the new file.
	 *
	 * @param string $contents The content of the new file.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function add_file_from_string( $name, $contents ) {
		return $this->zip->addFromString( $name, $contents );
	}

	/**
	 * Sets all headers needed to download the ZIP.
	 *
	 * @param bool $stream If true, file is streamed rather than being fulled opened and dumped.
	 */
	public function download( $stream = true ) {
		$this->zip->close();
		$zip_file = get_temp_dir() . $this->name;
		header( 'Content-type: application/zip' );
		header( "Content-Disposition: attachment; filename=$this->name.zip" );
		header( 'Content-length: ' . filesize( $zip_file ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		if ( ! $stream ) {
			readfile( $zip_file );
		} else {
			$handle = fopen( $zip_file, 'r' );
			fpassthru( $handle );
		}
	}
}
