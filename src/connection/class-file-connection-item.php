<?php

namespace flightdeck;

class File_Connection_Item implements IConnection_Item {
	public $rel_path;

	public $abs_path;

	public function __construct( $wp_content_relative_path ) {
		$this->rel_path = $wp_content_relative_path;
		$this->abs_path = trailingslashit( WP_CONTENT_DIR ) . unleadingslashit( $wp_content_relative_path );
	}

	public function can_send_self() {
		$filesystem = Filesystem::get_instance();
		return $filesystem->exists( $this->abs_path ) && ! $filesystem->is_dir( $this->abs_path );
	}

	public function get_name() {
		return $this->rel_path;
	}

	public function get_headers() {
		 return array(
			 'X-Flightdeck-Path' => $this->rel_path,
		 );
	}

	public function get_body() {
		$filesystem = Filesystem::get_instance();
		$filesystem->file_get( $this->abs_path );
	}

	public function get_dependency_items() {
		$filesystem = Filesystem::get_instance();

		if ( ! $filesystem->is_dir( $this->abs_path ) ) {
			return array();
		}

		$dependency_items = array();
		$sub_files        = $filesystem->get_dir_files( $this->abs_path );

		foreach ( $sub_files as $sub_file ) {
			$dependency_items[] = new File_Connection_Item( get_path_wp_content_relative( $sub_file ) );
		}

		return $dependency_items;
	}

	public static function import( $request ) {
		$path       = unleadingslashit( WP_CONTENT_DIR ) . leadingslashit( $request->get_header( 'X-Flightdeck-Path' ) );
		$contents   = $request->get_body();
		$filesystem = Filesystem::get_instance();

		if ( $filesystem->file_create_path( $path, $contents ) ) {
			return true;
		} else {
			return new \WP_Error( 'WRITE_FAILED', __( 'Writing file failed', 'flightdeck' ), array( 'status' => 500 ) );
		}
	}
}

Connection_Item_Factory::register( 'file', __NAMESPACE__ . '\\File_Connection_Item' );
