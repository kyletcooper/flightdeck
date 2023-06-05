<?php

namespace flightdeck;

interface IConnection_Item {
	public function can_send_self();

	public function get_name();

	public function get_headers();

	public function get_body();

	public function get_dependency_items();

	public static function import( $request );
}
