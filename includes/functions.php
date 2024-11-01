<?php
/**
 * General-purpose and helper functions
 *
 * @package XcooBee
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns text strings.
 *
 * Add-ons can register their text strings using `add_filter()`. Once a text
 * string is being registered, this helper function can be used to retrieve the text.
 *
 * @since 1.0.0
 *
 * @param string $key String key.
 * @return string Text string or '' if the key is not found.
 */
function xbee_get_text( $key ) {
	$strings = apply_filters( 'xbee_text_strings', [] );
	return isset( $strings[ $key ] ) ? $strings[ $key ] : '';
}

/**
 * Returns all the available add-ons.
 *
 * @since 1.0.0
 * @return array List of addons and their information.
 */
function xbee_get_addons() {
	return [
		[
			'slug'         => 'xcoobee-cookie',
			'name'         => __( 'XcooBee GDPR Cookie Manager', 'xcoobee' ),
			'description'  => __( 'Easy and transparent GDPR and EU E-Directive cookie life-cycle consent management for your site.', 'xcoobee' ),
			'icon'         => XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee-cookie.svg',
			'action_links' => xbee_get_addon_action_links( 'xcoobee-cookie' ),
		],
		[
			'slug'         => 'xcoobee-document',
			'name'         => __( 'XcooBee Document', 'xcoobee' ),
			'description'  => __( 'Send your documents and files securly through the XcooBee network.', 'xcoobee' ),
			'icon'         => XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee-document.svg',
			'action_links' => xbee_get_addon_action_links( 'xcoobee-document' ),
		],
		[
			'slug'         => 'xcoobee-form',
			'name'         => __( 'XcooBee Form', 'xcoobee' ),
			'description'  => __( 'Produce XcooBee consent pop-up windows for collection of information.', 'xcoobee' ),
			'icon'         => XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee-form.svg',
			'action_links' => xbee_get_addon_action_links( 'xcoobee-form' ),
		],
		[
			'slug'         => 'xcoobee-sar',
			'name'         => __( 'XcooBee SAR', 'xcoobee' ),
			'description'  => __( 'Enable XcooBee automation for the full Data Export Lifecycle.', 'xcoobee' ),
			'icon'         => XBEE_DIR_URL . 'assets/dist/images/icon-xcoobee-sar.svg',
			'action_links' => xbee_get_addon_action_links( 'xcoobee-sar' ),
		],
	];
}

/**
 * Checks whether an add-on is installed.
 *
 * @since 1.0.0
 *
 * @param string $name Add-on name.
 * @return bool Whether the add-on is installed or not.
 */
function xbee_is_addon_installed( $name ) {
	if ( file_exists( WP_PLUGIN_DIR . '/' . $name . '/' . $name . '.php' ) ) {
		return true;
	}

	return false;
}

/**
 * Checks whether there is a readable config file on the root directory.
 *
 * @since 1.0.0
 * @return bool Whether a readable config file exists.
 */
function xbee_config_file_exists() {
	if ( file_exists( XBEE_CONFIG_FILE ) ) {
		return true;
	}

	return false;
}

/**
 * Returns the current env mode.
 *
 * @since 1.0.0
 * @return string Either 'test' or 'prod'.
 */
function xbee_get_env() {
	return 'test' === getenv('XBEE_STATE') ? 'test' : 'prod';
}

/**
 * Prints out CSS classes if a condition is met.
 *
 * @since 1.0.0
 *
 * @param boolean $condition     The condition to check against (default: false).
 * @param string  $class         Classes to print out (default: '').
 * @param bool    $leading_space Whether to add a leading space (default: false).
 * @param bool    $echo          Whether to echo or return the output (default: false).
 */
function xbee_add_css_class( $condition = false, $class = '', $leading_space = false, $echo = false ) {
	if ( $condition ) {
		$output = ( $leading_space ? ' ' : '' ) . $class;
		
		if ( $echo ) {
			echo $output;
		} else {
			return  $output;
		}
	}
}

/**
 * Generates HTML tag attributes if their value is not empty.
 *
 * The leading and trailing spaces will not be printed out if all attributes have empty values.
 *
 * @since 1.0.0
 *
 * @param array $atts           Attributes and their values.
 * @param bool  $leading_space  Whether to add a leading space (default: false).
 * @param bool  $trailing_space Whether to add a trailing space (default: false).
 * @param bool  $echo           Whether to echo or return the output (default: false).
 * @return string Tag attributes.
 */
function xbee_generate_html_tag_atts( $atts, $leading_space = false, $trailing_space = false, $echo = false ) {
	$output =  '';
	$atts_count = 0;

	foreach ( $atts as $attr => $value ) {
		if ( ! empty( $value ) ) {
			$atts_count++;
			$output .=  $attr . '="' . $value . '"';
		}
	}

	if ( 0 < $atts_count ) {
		$output = ( $leading_space ? ' ' : '' ) . $output . ( $trailing_space ? ' ' : '' );

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}

/**
 * Generates inline style string.
 *
 * @since 1.0.0
 *
 * @param array $props Array of CSS properties and their values.
 * @param bool  $echo  Whether to echo or return the output (default: false).
 * @return string Inline style string.
 */
function xbee_generate_inline_style( $props, $echo = false ) {
	$output = '';
	foreach( $props as $prop => $value ) {
		if ( ! empty( $value ) ) {
			$output .= "{$prop}:{$value};";
		}
	}

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * Tests API keys.
 *
 * @since 1.0.0
 *
 * @param bool  $cached Optional. Whether to use any cached result (defualt: true).
 * @param array $config Optional. Configuration array (default: []).
 * @return object Result object `{result, status, code, errors}`.
 */
function xbee_test_keys( $cached = true, $config = [] ) {
	/*
	 * Look for any cached result and use it if it has an identical checksum to the 
	 * checksum of the stored data (in the database or the config file).
	 *
	 * Important: if the user updates or removes the keys on the XcooBee site, it would
	 * not be possible to know about this and the cached result will be used (however, it
	 * would not be correct).
	 */
	if ( $cached && empty( $config ) ) {
		// If reading config from a file.
		if ( file_exists( XBEE_CONFIG_FILE ) ) {
			$cached_checksum = get_option( 'xbee_checksum_cached_test_keys_file', '' );
			$calc_checksum = md5( file_get_contents( XBEE_CONFIG_FILE ) );

			$cached_result = get_option( 'xbee_cached_test_keys_file', '' );
		} else {
			$cached_checksum = get_option( 'xbee_checksum_cached_test_keys', '' );
			$api_key = get_option( 'xbee_api_key', '' );
			$api_secret = get_option( 'xbee_api_secret', '' );
			$calc_checksum = md5( $api_key . $api_secret );

			$cached_result = get_option( 'xbee_cached_test_keys', '' );
		}

		if ( $calc_checksum === $cached_checksum ) {
			return $cached_result;
		}
	}

	// Set configuration data.
	$xcoobee = XcooBee::get_xcoobee();
	$set_config = XcooBee::set_xcoobee_config( $xcoobee, $config );

	// Setting configuration data failed.
	if ( -1 === $set_config ) {
		$result = ( object ) [
			'result' => false,
			'status' => 'error',
			'code'   => 'error_set_config',
			'errors' => [
				xbee_get_text( 'message_error_set_config' ),
				xbee_get_text( 'message_error_missing_keys' )
			],
		];
	} else {
		// Test API keys.
		try {
			$campaigns = $xcoobee->consents->listCampaigns();

			if ( 200 === $campaigns->code ) {
				$result = ( object ) [
					'result' => true,
					'status'  => 'success',
					'code'    => 'success_valid_keys',
					'errors'  => [],
				];
			} else {
				$result = ( object ) [
					'result' => true,
					'status'  => 'error',
					'code'    => 'error_test_keys',
					'errors'  => [ xbee_get_text( 'message_error_test_keys' ) ],
				];
			}
		} catch ( Exception $exception ) {
			$result = ( object ) [
				'result' => false,
				'status' => 'error',
				'code'   => 'error_invalid_keys',
				'errors' => [ xbee_get_text( 'message_error_invalid_keys' ) ],
			];
		}
	}

	// Cache the result.
	if ( empty( $config ) ) {
		if ( file_exists( XBEE_CONFIG_FILE ) ) {
			$calc_checksum = md5( file_get_contents( XBEE_CONFIG_FILE ) );
			
			update_option( 'xbee_checksum_cached_test_keys_file', $calc_checksum );
			update_option( 'xbee_cached_test_keys_file', $result );
		} else {
			$api_key = get_option( 'xbee_api_key', '' );
			$api_secret = get_option( 'xbee_api_secret', '' );
			$calc_checksum = md5( $api_key . $api_secret );

			update_option( 'xbee_checksum_cached_test_keys', $calc_checksum );
			update_option( 'xbee_cached_test_keys', $result );
		}
	}

	return $result;
}

/**
 * Retrieves user data by XcooBee Id.
 *
 * @since 1.0.0
 *
 * @param string XcooBee Id.
 * @return int|null User Id or null if no record found.
 */
function xbee_get_user_by_xid( $xid ) {
	global $wpdb;

	$user = $wpdb->get_row( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key='xbee_xid' AND meta_value='$xid'" );
	
	return is_null( $user ) ? null : (int) $user->user_id;
}

/**
 * Retrieves the registered XcooBee Id for a specific user.
 *
 * @since 1.0.0
 *
 * @param int $user_id User Id.
 * @return string|null XcooBee Id or null if no Id found.
 */
function xbee_get_xid( $user_id ) {
	$xid = get_user_meta( $user_id, 'xbee_xid', true );
	
	return empty( $xid ) ? null : $xid;
}

/**
 * Validates a XcooBee Id.
 *
 * Must begin with a telda ~.
 *
 * @since 1.0.0
 *
 * @param $xid
 * @return bool Whether the Id is valid.
 */
function xbee_validate_xid( $xid ) {
	return substr( $xid, 0, 1 ) === '~';
}

/**
 * Wraps a URL to be used in a simple message.
 *
 * Example: `https://example.com` is converted to `{{LINK::https://example.com}}`.
 *
 * @since 1.0.0
 *
 * @param string $url URL.
 * @return string Wrapped URL.
 */
function xbee_wrap_link( $url ) {
	return "{{LINK::{$url}}}";
}

/**
 * Wraps all links in a string.
 *
 * @since 1.0.0
 *
 * @param string $string The string that contains links.
 * @return string The modified string with URLs wrapped.
 */
function xbee_wrap_all_links( $string ) {
	$url_pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';

	return preg_replace_callback( $url_pattern, function( $matches ) {
		foreach( $matches as $match ) {
			return xbee_wrap_link( $match );
		}
	}, $string );
}

/**
 * Returns all the sent HTTP hearders.
 *
 * @since 1.0.0
 * @return array Array of headers.
 */
function xbee_getallheaders() {
	$headers = array();

	foreach ( $_SERVER as $name => $value ) { 
		if ( substr( $name, 0, 5 ) == 'HTTP_' ) { 
			$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value; 
		} 
	}

	return $headers;
}

/**
 * Retrieves message logs of a specific user.
 *
 * @since 1.0.0
 *
 * @param int $user_id User Id.
 * @return array Message logs or an empty array if no logs found.
 */
function xbee_get_message_logs( $user_id ) {
	$message_logs = get_user_meta( $user_id, 'xbee_message_logs', true );

	return empty( $message_logs ) ? [] : $message_logs;
}

/**
 * Returns add-on action links.
 *
 * @since 1.4.0
 *
 * @param string $slug Plugin slug.
 * @return array Action links.
 */
function xbee_get_addon_action_links( $slug ) {
	// Include Plugin Install Administration API.
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

	$plugin = plugins_api( 'plugin_information', [ 'slug' => $slug ] );

	if ( is_wp_error( $plugin ) ) {
		return;
	}

	$action_links = [];
	
	if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
		$status = install_plugin_install_status( $plugin );
		
		switch ( $status['status'] ) {
			case 'install' :
				if ( $status['url'] ) {
					$action_links[] = sprintf(
						'<a class="install-now button button-primary" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
						esc_attr( $plugin->slug ),
						esc_url( $status['url'] ),
						/* translators: %s: plugin name and version */
						esc_attr( sprintf( __( 'Install %s now', 'xcoobee' ), $plugin->name ) ),
						esc_attr( $plugin->name ),
						__( 'Install Now', 'xcoobee' )
					);
				}	
				break;
			case 'update_available' :
				if ( $status['url'] ) {
					$action_links[] = sprintf(
						'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
						esc_attr( $status['file'] ),
						esc_attr( $plugin->slug ),
						esc_url( $status['url'] ),
						/* translators: %s: plugin name and version */
						esc_attr( sprintf( __( 'Update %s now', 'xcoobee' ), $plugin->name ) ),
						esc_attr( $plugin->name ),
						__( 'Update Now', 'xcoobee' )
					);
				}
				break;
			case 'latest_installed' :
			case 'newer_installed' :
				if ( is_plugin_active( $status['file'] ) ) {
					$action_links[] = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Active', 'plugin', 'xcoobee' )
					);
				} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
					$button_text = __( 'Activate', 'xcoobee' );
					/* translators: %s: plugin name */
					$button_label = _x( 'Activate %s', 'plugin', 'xcoobee' );
					$activate_url = add_query_arg( [
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
							'action'   => 'activate',
							'plugin'   => $status['file'],
						],
						network_admin_url( 'plugins.php' )
					);
					
					if ( is_network_admin() ) {
						$button_text = __( 'Network Activate', 'xcoobee' );
						/* translators: %s: plugin name */
						$button_label = _x( 'Network Activate %s', 'plugin', 'xcoobee' );
						$activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
					}
					
					$action_links[] = sprintf(
						'<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
						esc_url( $activate_url ),
						esc_attr( sprintf( $button_label, $plugin->name ) ),
						$button_text
					);
				} else {
					$action_links[] = sprintf(
						'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
						_x( 'Installed', 'plugin', 'xcoobee' )
					);
				}
				break;
		}
	}

	return $action_links;
}

/**
 * Validates an array of strings against specific rules and returns the filtered strings.
 *
 * @since 1.4.1
 *
 * @param array $array     Associative array with pairs of keys and values to be filterd.
 * @param array $key_rules Pairs of array keys and filtering rules.
 *
 * @return array The array after filtering.
 */
function xbee_filter_strings( $array, $key_rules ) {
	
	foreach( $key_rules as $key => $rules ) {
		foreach( $rules as $rule => $filter ) {
			/*
			 * max_length
			 * @param integer Maximum length of the string.
			 */
			if ( 'max_length' === $rule ) {
				$max_length = $filter;
				$array[ $key ] = substr( $array[ $key ], 0, $max_length );
			}
		}
	}

	return $array;
}


/**
 * Convert timestamp for display.
 *
 * @since 1.5.0
 *
 * @param int $timestamp Event timestamp.
 * @return string Human readable date/time.
 */
function xbee_get_timestamp_as_datetime( $timestamp ) {
	if ( empty( $timestamp ) ) {
		return '';
	}

	$time_diff = time() - $timestamp;

	if ( $time_diff >= 0 && $time_diff < DAY_IN_SECONDS ) {
		/* translators: human readable timestamp */
		return sprintf( __( '%s ago', 'xcoobee' ), human_time_diff( $timestamp ) );
	}

	$datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' ); 

	return date_i18n( $datetime_format, $timestamp );
}