<?php
/**
 * Contains functions for sending emails.
 *
 * @package flightdeck
 */

namespace flightdeck;

/**
 * Sends a notification email to the user and/or admin when a transfer is complete.
 *
 * @param int    $userid ID of the user who started the transfer.
 *
 * @param string $notify Who to email. Accepts 'user', 'admin' or 'both'.
 */
function send_transfer_complete_email( $userid, $notify = 'both' ) {
	if ( ! in_array( $notify, array( 'user', 'admin', 'both' ), true ) ) {
		return;
	}

	// translators: %s is the blog name.
	$subject  = sprintf( __( 'FlightDeck: Transfer sent from %s' ), get_bloginfo( 'name' ) );
	$userdata = get_userdata( $userid );
	$to       = array();
	$log      = Log::get_instance();

	if ( 'admin' === $notify || 'both' === $notify ) {
		$to[] = get_option( 'admin_email' );
	}

	if ( 'user' === $notify || 'both' === $notify ) {
		$to[] = $userdata->user_email;
	}

	// translators: %s is the username.
	$message = sprintf( __( 'Hello %s', 'flightdeck' ), $userdata->user_login ) . "\r\n\r\n";
	// translators: %s is the blog url.
	$message .= sprintf( __( 'Your FlightDeck transfer from %s is now complete. To view the log of this transfer visit the following address:', 'flightdeck' ), site_url() ) . "\r\n\r\n";
	$message .= $log->get_file_url() . "\r\n\r\n";

	wp_mail(
		$to,
		$subject,
		$message
	);
}
