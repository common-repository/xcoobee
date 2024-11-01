<?php
/**
 * The XcooBee_Endpoint class.
 *
 * @package XcooBee
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XcooBee_User
 *
 * @since 1.0.0
 */
class XcooBee_Endpoint {
	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( 'on' === get_option( 'xbee_enable_endpoint', '' ) ) {
			add_filter( 'query_vars', [ $this, 'add_query_vars' ], 0 );
			add_action( 'init', [ $this, 'endpoint' ] , 0);
			add_action( 'parse_request', [ $this, 'handle_endpoint_requests' ], 0 );
		}
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'xbee';
		return $vars;
	}

	public function endpoint() {
		add_rewrite_endpoint( 'xbee', EP_ALL );
	}

	public function handle_endpoint_requests() {
		global $wp;

		if ( ! empty( $_GET['xbee'] ) ) {
			$wp->query_vars['xbee'] = sanitize_key( wp_unslash( $_GET['xbee'] ) );
		}

		if ( ! empty( $wp->query_vars['xbee'] ) ) {
			// Buffer, we won't want any output here.
			ob_start();

			// Execute XcooBee\Core\Api\System::handleEvents() to handle XcooBee events.
			$xcoobee = XcooBee::get_xcoobee( true );
			$xcoobee->system->handleEvents();

			// HTTP headers.
			$headers = xbee_getallheaders();

			// Endpoint request.
			$request = strtolower( $wp->query_vars['xbee'] );

			// Response body.
			$response = file_get_contents( 'php://input', true );

			// Trigger an action which can be hooked into to fulfill the request.
			do_action( 'xbee_endpoint_' . $request, $headers, $response );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}
}

new XcooBee_Endpoint;