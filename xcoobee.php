<?php
/**
 * Plugin Name: XcooBee for WordPress
 * Plugin URI:  https://wordpress.org/plugins/xcoobee/
 * Author URI:  https://www.xcoobee.com/
 * Description: Connects your website to the XcooBee privacy network and enables secure password recovery and login. Base library for XcooBee GDPR and CCPA Add-ons.
 * Version:     1.7.0
 * Author:      XcooBee
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Text Domain: xcoobee
 * Domain Path: /languages
 *
 * Requires at least: 4.4.0
 * Tested up to: 5.3.2
 *
 * @package XcooBee
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Globals constants.
define( 'XBEE_MIN_PHP_VER', '5.6.0' );
define( 'XBEE_MIN_WP_VER', '4.4.0' );
define( 'XBEE_ABSPATH', plugin_dir_path( __FILE__ ) );      // With trailing slash.
define( 'XBEE_DIR_URL', plugin_dir_url( __FILE__ ) );       // With trailing slash.
define( 'XBEE_CONFIG_HOME', untrailingslashit( ABSPATH ) ); // ABSPATH without trailing slash.
define( 'XBEE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( defined( 'PHP_WINDOWS_VERSION_BUILD' ) ) {
	define( 'XBEE_CONFIG_FILE', str_replace( '/', '\\', ABSPATH ) . '.xcoobee\config' );
} else {
	define( 'XBEE_CONFIG_FILE', ABSPATH . '.xcoobee/config' );
}

// Use production API.
putenv( 'XBEE_STATE=prod' );

// Load XcooBee PHP SDK.
require_once XBEE_ABSPATH . 'includes/sdk/vendor/autoload.php';
use XcooBee\XcooBee as Sdk_XcooBee;
use XcooBee\Models\ConfigModel as Sdk_ConfigModel;
use XcooBee\Exception\XcooBeeException as Sdk_XcooBeeException;

/**
 * The main class.
 *
 * @since 1.0.0
 */
class XcooBee {
	/**
	 * The single instance of XcooBee.
	 *
	 * @since 1.0.0
	 * @var XcooBee
	 */
	private static $instance = null;

	/**
	 * The single instance of Sdk_XcooBee.
	 *
	 * @since 1.0.0
	 * @var XcooBee
	 */
	private static $xcoobee = null;

	/**
	 * The single instance of XcooBee_API.
	 *
	 * @since 1.0.0
	 * @var XcooBee_API
	 */
	private static $xcoobee_api;

	/**
	 * Returns the singleton instance of XcooBee.
	 *
	 * Ensures only one instance of XcooBee is/can be loaded.
	 *
	 * @since 1.0.0
	 * @return XcooBee
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns the Sdk_XcooBee instance.
	 *
	 * @since 1.0.0
	 *
	 * @param bool  $set_config Optional. Whether to set configuration data (default: false).
	 * @param array $config     Optional. Configuration data (default: []).
	 * @return XcooBee\XcooBee
	 */
	public static function get_xcoobee( $set_config = false, $config = [] ) {
		if ( null === self::$xcoobee ) {
			self::$xcoobee = new Sdk_XcooBee();

			// Set configuration data.
			if ( $set_config ) {
				self::set_xcoobee_config( self::$xcoobee, $config );
			}
		}

		return self::$xcoobee;
	}

	/**
	 * Returns the XcooBee_API instance.
	 *
	 * @since 1.0.0
	 * @return XcooBee_API
	 */
	public static function get_xcoobee_api() {
		if ( null === self::$xcoobee_api ) {
			$xcoobee = self::get_xcoobee();
			self::$xcoobee_api = new XcooBee_API( $xcoobee );
		}

		return self::$xcoobee_api;
	}

	/**
	 * Configures the SDK instance.
	 *
	 * This method will fail if the API key, secret or both are missing.
	 * However, it does not care if they are invalid.
	 *
	 * @since 1.0.0
	 *
	 * @throws XcooBee\Exception\XcooBeeException
	 *
	 * @param XcooBee\XcooBee $xcoobee The Sdk_XcooBee instance.
	 * @param array           $config  Optional. Configuration data (default: []).
	 * @return int Returns 1 on success and -1 on failure.
	 */
	public static function set_xcoobee_config( $xcoobee, $config = [] ) {
		// If no configuration array passed, get data from the database or the default values.
		$config['apiKey']      = isset( $config['apiKey'] ) ? $config['apiKey'] : get_option( 'xbee_api_key', '' );
		$config['apiSecret']   = isset( $config['apiSecret'] ) ? $config['apiSecret'] : get_option( 'xbee_api_secret', '' );
		$config['pgpSecret']   = isset( $config['pgpSecret'] ) ? $config['pgpSecret'] : get_option( 'xbee_pgp_secret', '' );
		$config['pgpPassword'] = isset( $config['pgpPassword'] ) ? $config['pgpPassword'] : get_option( 'xbee_pgp_password', '' );
		$config['campaignId']  = isset( $config['campaignId'] ) ? $config['campaignId'] : get_option( 'xbee_campaign_id', '' );
		$config['pageSize']    = isset( $config['pageSize'] ) ? $config['pageSize'] : 100;

		// Remove empty elements.
		$config = array_filter( $config );

		// Clear cached config.
		$xcoobee->clearConfig();

		/*
		 * Read from file if exists.
		 * File should be located at `/ABSPATH/.xcoobee/config`.
		 */
		try {
			if ( file_exists( XBEE_CONFIG_FILE ) ) {
				$xcoobee->setConfig( Sdk_ConfigModel::createFromFile( XBEE_CONFIG_HOME ) );
			} else {
				$xcoobee->setConfig( Sdk_ConfigModel::createFromData( $config ) );
			}
		} catch ( Exception $exception ) {
			return -1;
		}

		return 1;
	}

	/**
	 * The constructor.
	 *
	 * Private constructor to make sure it cannot be called directly from outside the class.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Create an Sdk_XcooBee instance.
		self::$xcoobee = $this->get_xcoobee( true );

		// Register text strings.
		add_filter( 'xbee_text_strings', [ $this, 'register_text_strings' ], 10, 1 );

		// Include required files.
		$this->includes();

		// Register hooks.
		$this->hooks();

		// Create a XcooBee_API instance.
		self::$xcoobee_api = $this->get_xcoobee_api();

		/**
		 * Fires after the plugin is completely loaded.
		 *
		 * @since 1.0.0
		 */
		do_action( 'xcoobee_loaded' );
	}

	/**
	 * Returns endpoint URL.
	 *
	 * @since 1.0.0
	 * @return string Endpoint URL.
	 */
	public static function get_endpoint() {
		return get_site_url( null, '/?xbee=webhook' );
	}

	/**
	 * Includes plugin files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		// Global includes.
		include_once XBEE_ABSPATH . 'includes/class-xcoobee-api.php';
		include_once XBEE_ABSPATH . 'includes/functions.php';
		include_once XBEE_ABSPATH . 'includes/class-xcoobee-endpoint.php';
		include_once XBEE_ABSPATH . 'includes/class-xcoobee-login.php';

		// Back-end includes.
		if ( is_admin() ) {
			include_once XBEE_ABSPATH . 'includes/admin/class-xcoobee-admin.php';
			include_once XBEE_ABSPATH . 'includes/admin/class-xcoobee-admin-user.php';
		}

		// Front-end includes.
		if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
			// Nothing to include for now.
		}
	}

	/**
	 * Registers plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {
		add_filter( 'plugin_action_links_' . XBEE_PLUGIN_BASENAME, [ $this, 'action_links' ], 10, 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'styles' ] );
	}

	/**
	 * Adds plugin action links.
	 *
	 * @since 1.4.0
	 */
	public function action_links( $links ) {
		$action_links = [
			'settings' => '<a href="' . admin_url( 'admin.php?page=xcoobee' ) . '" aria-label="' . esc_attr__( 'View XcooBee settings', 'xcoobee' ) . '">' . esc_html__( 'Settings', 'xcoobee' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}

	/**
	 * Loads plugin scripts.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {
		// Back-end scripts.
		if ( 'admin_enqueue_scripts' === current_action() ) {
			wp_enqueue_script( 'xbee-admin-scripts', XBEE_DIR_URL . 'assets/dist/js/admin/scripts.min.js', [ 'jquery' ], null, true );
			wp_localize_script( 'xbee-admin-scripts', 'xbeeAdminParams', [
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'images'  => [
					'iconXcooBee' => XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee.svg',
					'loader'      => XBEE_DIR_URL . 'assets/dist/images/loader.svg',
				],
				'messages' => [
					'errorTestKeys'           => xbee_get_text( 'message_error_test_keys' ),
					'successValidKeys'        => xbee_get_text( 'message_success_valid_keys' ),
					'successClearMessageLogs' => xbee_get_text( 'message_success_clear_message_logs' ),
					'errorClearMessageLogs'   => xbee_get_text( 'message_error_clear_message_logs' ),
				]
			] );
		}
		// Front-end scripts.
		else {
			wp_enqueue_script( 'xbee-js-sdk-web', XBEE_DIR_URL . 'assets/dist/vendor/xcoobee-sdk-0.9.6.web.js', [ 'jquery' ] );
			wp_enqueue_script( 'xbee-scripts', XBEE_DIR_URL . 'assets/dist/js/scripts.min.js', [ 'jquery' ], null, false );
			wp_localize_script( 'xbee-scripts', 'xbeeParams', [
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'env' => xbee_get_env(),
				'images'  => [
					'close' => XBEE_DIR_URL . 'assets/dist/images/close.svg',
				],
			] );
		}
	}

	/**
	 * Loads plugin styles.
	 *
	 * @since 1.0.0
	 */
	public function styles() {
		// Back-end styles.
		if ( 'admin_enqueue_scripts' === current_action() ) {
			wp_enqueue_style( 'xbee-admin-styles', XBEE_DIR_URL . 'assets/dist/css/admin/main.min.css', [], false, 'all' );
		}
		// Front-end styles.
		else {
			wp_enqueue_style( 'xbee-styles', XBEE_DIR_URL . 'assets/dist/css/main.min.css', [], false, 'all' );
		}
	}

	/**
	 * Defines and registers text strings.
	 *
	 * Use `url_name_of_the_url` for URL keys and `message_type_the_message` for message keys.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $strings Text strings array.
	 * @return array The updated text strings array.
	 */
	public function register_text_strings( $strings ) {
		return array_merge( $strings, [
			// URLs.
			'url_login'           => 'test' !== xbee_get_env() ? 'https://app.xcoobee.net/' : 'https://testapp.xcoobee.net/',
			'url_campaigns'       => 'test' !== xbee_get_env() ? 'https://app.xcoobee.net/user/consentAdministration/campaigns/' : 'https://testapp.xcoobee.net/user/consentAdministration/campaigns/',
			'url_consent_options' => 'test' !== xbee_get_env() ? 'https://app.xcoobee.net/user/consentAdministration/options' : 'https://testapp.xcoobee.net/user/consentAdministration/options',
			// Messages.
			'message_error_invalid_keys'         => __( 'API keys are invalid.', 'xcoobee' ),
			'message_success_valid_keys'         => __( 'API keys are valid.', 'xcoobee' ),
			'message_error_missing_keys'         => __( 'Missing API key and/or secret.', 'xcoobee' ),
			'message_error_set_config'           => __( 'Could not set configuration data.', 'xcoobee' ),
			'message_error_test_keys'            => __( 'Could not check API keys. Please check your configuration data.', 'xcoobee' ),
			'message_error_try_again'            => __( 'Please try again.', 'xcoobee' ),
			'message_error_try_again_later'      => __( 'Please try again later.', 'xcoobee' ),
			'message_success_clear_message_logs' => __( 'Message logs cleard!', 'xcoobee' ),
			'message_error_clear_message_logs'   => __( 'Could not clear message logs.', 'xcoobee' ),
		] );
	}

	/**
	 * Activation hooks.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Nothing to do for now.
	}
	
	/**
	 * Deactivation hooks.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Nothing to do for now.
	}

	/**
	 * Uninstall hooks.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		include_once XBEE_ABSPATH . 'uninstall.php';
	}
}

/**
 * Inits and returns the XcooBee instance.
 *
 * @since  1.0.0
 * @return XcooBee
 */
function xcoobee() {
	return XcooBee::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['xcoobee'] = xcoobee();

// Plugin hooks.
register_activation_hook( __FILE__, [ 'XcooBee', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'XcooBee', 'deactivate' ] );
register_uninstall_hook( __FILE__, [ 'XcooBee', 'uninstall' ] );
