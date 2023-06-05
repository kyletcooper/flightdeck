<?php

namespace flightdeck;

class Connection_Item_Factory {
	public static $types = array();

	public static function register( $type, $class ) {
		if ( ! class_exists( $class ) ) {
			throw new \Exception( 'Class not found.' );
		}

		static::$types[ $type ] = $class;
	}

	public static function get_all_types() {
		return array_keys( static::$types );
	}

	public static function get_type( $obj ) {
		$class_name = get_class( $obj );
		return array_search( $class_name, static::$types, true );
	}

	public static function get_class( $type ) {
		return static::$types[ $type ];
	}

	public static function make( $type, ...$args ) {
		$class = static::get_class( $type );
		return new $class( ...$args );
	}
}
