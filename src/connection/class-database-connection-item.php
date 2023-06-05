<?php

namespace flightdeck;

class Database_Connection_Item implements IConnection_Item {
	public $table;

	public $rows;

	public function __construct( $item ) {
		$this->table = $item['table'];
		$this->rows  = $item['rows'];
	}

	public function can_send_self() {
		return true;
	}

	public function get_name() {
		return $this->table;
	}

	public function get_headers() {
		global $wpdb;

		return array(
			'X-Flightdeck-Prefix' => $wpdb->prefix,
			'X-Flightdeck-Table'  => $this->table,
		);
	}

	public function get_body() {
		return export_table( $this->table );
	}

	public function get_dependency_items() {
		return array();
	}

	public static function import( $request ) {
		$res = import_table( $request->get_body(), $request->get_header( 'X-Flightdeck-Prefix' ) );

		if ( is_wp_error( $res ) ) {
			$res->add_data(
				array(
					'status' => 500,
				)
			);

		}

		return $res;
	}
}

Connection_Item_Factory::register( 'database', __NAMESPACE__ . '\\Database_Connection_Item' );
