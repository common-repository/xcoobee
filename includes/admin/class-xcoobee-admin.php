<?php
/**
 * The XcooBee_Admin class.
 *
 * @package XcooBee/Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controls setting pages.
 *
 * @since 1.0.0
 */
class XcooBee_Admin {
	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'settings' ] );
		add_action( 'wp_ajax_xbee_test_keys', [ $this, 'test_keys' ] );
		add_filter( 'custom_menu_order', [ $this, 'submenu_order' ] );
	}

	/**
	 * Tests API keys.
	 *
	 * @since 1.0.0
	 */
	public function test_keys() {
		// Config array.
		$config = [
			'apiKey'    => $_POST['apiKey'],
			'apiSecret' => $_POST['apiSecret'],
		];

		// Test keys and get result.
		$result = xbee_test_keys( false, $config );
		
		// Send response, and die.
		wp_send_json( json_encode( $result ) );
	}

	/**
	 * Registers setting pages.
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		add_menu_page(
			__( 'XcooBee', 'xcoobee' ),
			__( 'XcooBee', 'xcoobee' ),
			'manage_options',
			'xcoobee',
			[ $this, 'render' ],
			XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee.svg',
			30
		);

		add_submenu_page(
			'xcoobee',
			__( 'General', 'xcoobee' ),
			__( 'General', 'xcoobee' ),
			'manage_options',
			'xcoobee',
			[ $this, 'render' ]
		);

		add_submenu_page(
			'xcoobee',
			__( 'Addons', 'xcoobee' ),
			__( 'Addons', 'xcoobee' ),
			'manage_options',
			'admin.php?page=xcoobee&tab=addons'
		);
	}

	/**
	 * Re-orders submenu items.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function submenu_order() {
		global $submenu;

		if ( ! isset( $submenu['xcoobee'] ) ) {
			return;
		}

		$menu_items = [];
		foreach ( $submenu['xcoobee'] as $item ) {
			$menu_items[ $item[2] ] = $item;
		}

		// Re-order submenu items
		$submenu['xcoobee'] = [];

		if ( isset( $menu_items[ 'xcoobee' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['xcoobee'] );
		}

		if ( isset( $menu_items[ 'admin.php?page=xcoobee&tab=cookie' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['admin.php?page=xcoobee&tab=cookie'] );
		}

		if ( isset( $menu_items[ 'admin.php?page=xcoobee&tab=document' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['admin.php?page=xcoobee&tab=document'] );
		}

		if ( isset( $menu_items[ 'admin.php?page=xcoobee&tab=form' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['admin.php?page=xcoobee&tab=form'] );
		}

		if ( isset( $menu_items[ 'admin.php?page=xcoobee&tab=sar' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['admin.php?page=xcoobee&tab=sar'] );
		}

		if ( isset( $menu_items[ 'admin.php?page=xcoobee&tab=addons' ] ) ) {
			array_push( $submenu['xcoobee'], $menu_items['admin.php?page=xcoobee&tab=addons'] );
		}

		return $submenu;
	}

	/**
	 * Registers the general setting fields.
	 *
	 * @since 1.0.0
	 */
	public function settings() {
		// General settings.
		register_setting( 'xbee_general', 'xbee_api_key' );
		register_setting( 'xbee_general', 'xbee_api_secret' );
		register_setting( 'xbee_general', 'xbee_pgp_secret' );
		register_setting( 'xbee_general', 'xbee_pgp_password' );
		register_setting( 'xbee_general', 'xbee_enable_login_privacy' );
		register_setting( 'xbee_general', 'xbee_enable_endpoint' );
		register_setting( 'xbee_general', 'xbee_endpoint' );
	}

	/**
	 * Renders setting pages.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		require_once XBEE_ABSPATH . '/includes/admin/views/settings.php';
	}
}

new XcooBee_Admin;