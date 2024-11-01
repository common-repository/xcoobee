<?php
/**
 * The XcooBee_Login class.
 *
 * @package XcooBee/Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use XcooBee\Core\Validation as Sdk_Validation;

/**
 * Adds XcooBee features to the login screens.
 *
 * @since 1.0.0
 */
class XcooBee_Login {
	/**
	 * The constructor.
	 *
	 * XcooBee login features will be available only if the XcooBee login privacy option is enabled.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( 'on' === get_option( 'xbee_enable_login_privacy', '' ) ) {
			add_action( 'login_enqueue_scripts', [ $this, 'login_styles' ] );
			//add_action( 'xbee_endpoint_webhook', [ $this, 'message_logs' ], 10, 2 );
			add_filter( 'shake_error_codes', [ $this, 'shake_error_codes' ], 10, 1 );

			// Registration.
			add_action( 'register_form', [ $this, 'register_form' ] );
			add_action( 'user_register', [ $this, 'user_register' ] );
			add_filter( 'registration_errors', [ $this, 'registration_errors' ], 10, 3 );
			add_filter( 'wp_new_user_notification_email', [ $this, 'new_user_notification' ], 9999, 3 );
			add_filter( 'wp_login_errors', [ $this, 'login_errors' ], 10, 2 );

			// Login.
			add_filter( 'authenticate', [ $this, 'authenticate_xid_password' ], 50, 3 );

			// Password recovery.
			add_action( 'login_form_lostpassword', [ $this, 'login_form_lostpassword' ] );
		}
	}

	/**
	 * Loads styles and scripts for login screens.
	 *
	 * @since 1.0.0
	 */
	public function login_styles() {
		wp_enqueue_script( 'jquery' ); // jQuery is not loaded by default here.
		wp_enqueue_script( 'xbee-login', XBEE_DIR_URL . 'assets/dist/js/login.min.js', [ 'jquery' ], null, false );
		wp_localize_script( 'xbee-login', 'xbeeLoginParams', [
			'userLogin' => __( 'Username, XcooBee Id or Email Address', 'xcoobee' )
		] );
		wp_enqueue_style( 'xbee-login', XBEE_DIR_URL . 'assets/dist/css/login.min.css', [], false, 'all' );
	}

	/**
	 * Updates message logs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $payload
	 * @param string $response
	 */
	public static function message_logs( $payload ) {
		$payload = json_decode( $payload );

		// Encrypted or invalid payload.
		if ( $payload === null ) {
			return;
		}

		$user_id = self::get_log_info( $payload->userReference );

		if ( ! $user_id ) {
			return;
		}

		$message_logs = get_user_meta( $user_id, 'xbee_message_logs', true );
		$message_logs = empty( $message_logs ) ? [] : $message_logs;
		$status = self::get_message_status( $payload->event );

		if ( isset( $message_logs[ $payload->userReference ] ) ) {
			$message_logs[ $payload->userReference ]['date'] = strtotime( $payload->date );
			$message_logs[ $payload->userReference ]['status'] = $status;

			// Update user logs.
			update_user_meta( $user_id, 'xbee_message_logs', $message_logs );
		}

	}

	/**
	 * Returns the status of a message event.
	 *
	 * @since 1.0.0
	 * @link https://github.com/XcooBee/xcoobee-php-sdk#process-level-events
	 *
	 * @param string $event Event type.
	 * @return string|null Message status or null if the event does not exist.
	 */
	 protected static function get_message_status( $event ) {
		switch ( $event ) {
			case 'error':
				return [ 'failed', __( 'Failed', 'xcoobee' ) ];
			case 'deliver':
				return [ 'delivered', __( 'Delivered', 'xcoobee' ) ];
			case 'present':
				return [ 'seen', __( 'Seen', 'xcoobee' ) ];
			case 'download':
				return [ 'read', __( 'Read', 'xcoobee' ) ];
			default:
				return null;
		}
	}

	/**
	 * Returns the correspond user Id of a given log reference.
	 *
	 * @since 1.0.0
	 *
	 * @param $log_ref string Log reference.
	 * @return int|false User Id or false if log not found.
	 */
	protected static function get_log_info( $log_ref ) {
		return (int) strtok( $log_ref, '.' );
	}

	/**
	 * Shake the login form on the new custom errors.
	 *
	 * @param array $shake_error_codes Error codes that shake the login form.
	 * @return array The updated error codes array.
	 */
	public function shake_error_codes( $shake_error_codes ) {
		return array_merge( [ 'invalid_xid', 'xid_exists' ], $shake_error_codes );
	}

	/**
	 * Modifies the registration form.
	 *
	 * Adds the following fields:
	 *  - XcooBee Id (text)
	 *  - Enable XcooBee Secure Messaging (checkbox)
	 *
	 * @since 1.0.0
	 */
	public function register_form() {
		$xid = ! empty( $_POST['xbee_xid'] ) ? sanitize_text_field( $_POST['xbee_xid'] ) : '';
		$xbee_message = ! empty( $_POST['xbee_message'] );
		?>
		<p>
			<label for="xbee_xid"><?php _e( 'XcooBee Id (optional)', 'xcoobee' ); ?><br />
				<input type="text" name="xbee_xid" id="xbee_xid" value="<?php echo esc_attr(  $xid  ); ?>" />
			</label>
		</p>
		<p class="xbee-message">
			<input type="checkbox" name="xbee_message" value="on" id="xbee_message" <?php checked( $xbee_message ); ?> /><label for="xbee_message"><?php _e( 'Use XcooBee Secure Messaging', 'xcoobee' ); ?></label>
		</p>
		<p id="reg_xbee"><?php _e( 'Registration confirmation will be securely messaged to you via XcooBee.', 'xcoobee' ); ?></p>
		<?php
	}

	/**
	 * Updates user meta upon a sccuessfull registration.
	 *
	 * @param int $user_id
	 * @return void
	 */
	public function user_register( $user_id ) {
		if ( ! empty( $_POST['xbee_xid'] ) ) {
			update_user_meta( $user_id, 'xbee_xid', sanitize_text_field( $_POST['xbee_xid'] ) );
		}
	}

	/**
	 * Validates registration fields.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $errors
	 * @param string $sanitized_user_login
	 * @param string $user_email
	 * @return WP_Error
	 */
	public function registration_errors( $errors, $sanitized_user_login, $user_email ) {
		if ( ! empty( $_POST['xbee_xid'] ) && ! xbee_validate_xid( $_POST['xbee_xid'] ) ) {
			$errors->add( 'invalid_xid', __( '<strong>ERROR</strong>: Please enter a valid XcooBee Id (begins with a telda ~).', 'xcoobee' ) );
		}

		if ( xbee_get_user_by_xid( $_POST['xbee_xid'] ) ) {
			$errors->add( 'xid_exists', __( '<strong>ERROR</strong>: This XcooBee Id is already registered. Please choose another one.', 'xcoobee' ) );
		}

		return $errors;
	}

	/**
	 * Disables the regular new user notification email and sends it through XcooBee.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $wp_new_user_notification_email
	 * @param int    $user
	 * @param string $blogname
	 * @return string
	 */
	public function new_user_notification( $wp_new_user_notification_email, $user, $blogname ) {
		if ( ! empty( $_POST['xbee_message'] ) ) {
			$user_id = $user->ID;

			// Remove `<` and `>` that enclose the registration URL.
			$message = preg_replace_callback( '/<(.*?)\>/s', function( $matches ) {
				return str_replace( [ '<', '>' ], '', $matches[0] );
			}, $message );

			// Wrap links.
			$message = xbee_wrap_all_links( $wp_new_user_notification_email['message'] );

			// Remove `\r` from the message.
			$message = str_replace( "\r", '', $message );

			// If a XcooBee Id is not provided during registration, use the email address instead.
			$destination = empty( $_POST['xbee_xid'] ) ? $wp_new_user_notification_email['to'] : $_POST['xbee_xid'];

			$recipient = [ Sdk_Validation::isValidEmail( $destination ) ? 'email' : 'xcoobee_id' => $destination ];
			$user_reference = "{$user_id}." . md5( time() + rand( 1111, 9999 ) );

			// Send the notification email.
			try {
				$xcoobee = XcooBee::get_xcoobee( true );
				$response = $xcoobee->bees->takeOff(
					[
						'xcoobee_message' => [
							'xcoobee_simple_message' => [ 'message' => $message ],
							'recipient' => $recipient,
						]
					],
					[
						'process' => [
							'userReference' => $user_reference,
							'destinations'  => [ $destination ],
						]
					],
					[
						'target' => XcooBee::get_endpoint(),
						'events' => ['error', 'success', 'deliver', 'present', 'download', 'delete', 'reroute'],
						'handler' => 'XcooBee_Login::message_logs',
					]
				);
			} catch ( Exception $e ) {
				wp_die( __( 'The message could not be sent.' , 'xcoobee' ) );
			}

			if ( 200 === $response->code ) {
				$message_logs = get_user_meta( $user_id, 'xbee_message_logs', true );
				$message_logs = empty( $message_logs ) ? [] : $message_logs ;
				$message_logs[$user_reference] = [
					'type' => [ 'registration', __( 'Registration', 'xcoobee' ) ],
					'recipient' => $destination,
					'date' => strtotime( $response->time ),
					'status' => [ 'sent', __( 'Sent', 'xcoobee' ) ],
				];
				update_user_meta( $user_id, 'xbee_message_logs', $message_logs );
			} else {
				wp_die( __( 'The message could not be sent.' , 'xcoobee' ) );
			}

			// Disable the regular new user notification email.
			$wp_new_user_notification_email['to'] = '';

			// Update `$_POST['redirect_to]` to display the correct notification.
			$_POST['redirect_to'] = 'wp-login.php?checkemail=registered&xcoobee=1';

			return $wp_new_user_notification_email;
		}
	}

	/**
	 * Displays login form notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $errors
	 * @return WP_Error
	 */
	public function login_errors( $errors ) {
		if ( isset( $_GET['checkemail'] ) && isset( $_GET['xcoobee'] ) && '1' === $_GET['xcoobee'] ) {
			if ( 'confirm' === $_GET['checkemail'] ) {
				$errors->remove( 'confirm' );
				$errors->add( 'confirm_xbee', __( 'Check your XcooBee account for the confirmation link.' ), 'message' );
			}

			if ( 'registered' === $_GET['checkemail'] ) {
				$errors->remove( 'registered' );
				$errors->add( 'registered_xbee', __( 'Registration complete. Please check your XcooBee inbox.' ), 'message' );
			}
		}

		return $errors;
	}

	/**
	 * Authenticates a user using XcooBee Id.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback (default null).
	 * @param string                $xid      XcooBee Id for authentication.
	 * @param string                $password Password for authentication.
	 * @return WP_User|WP_Error WP_User on success, WP_Error on failure.
	 */
	function authenticate_xid_password( $user, $xid, $password ) {
		if ( $user instanceof WP_User ) {
			return $user;
		}

		if( ! xbee_validate_xid( $xid ) ) {
			return $user;
		}

		if ( empty($xid) || empty($password) ) {
			if ( is_wp_error( $user ) )
				return $user;

			$error = new WP_Error();

			if ( empty($xid) )
				$error->add('empty_username', __('<strong>ERROR</strong>: The XcooBee Id field is empty.'));

			if ( empty($password) )
				$error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

			return $error;
		}

		// Get user by XcooBee Id.
		$user_id = xbee_get_user_by_xid( $xid );
		$user = get_user_by( 'id', $user_id );
		
		if ( ! $user ) {
			return new WP_Error(
				'invalid_xid',
				__( '<strong>ERROR</strong>: Invalid XcooBee Id.' ) .
				' <a href="' . wp_lostpassword_url() . '">' .
				__( 'Lost your password?' ) .
				'</a>'
			);
		}

		/** This filter is documented in wp-includes/user.php */
		$user = apply_filters( 'wp_authenticate_user', $user, $password );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			return new WP_Error(
				'incorrect_password',
				sprintf(
					/* translators: %s: user name */
					__( '<strong>ERROR</strong>: The password you entered for the XcooBee Id %s is incorrect.' ),
					'<strong>' . $xid . '</strong>'
				) .
				' <a href="' . wp_lostpassword_url() . '">' .
				__( 'Lost your password?' ) .
				'</a>'
			);
		}

		return $user;
	}

	/**
	 * Overrides the lostpassword form in wp-login.php.
	 *
	 * @since 1.0.0
	 * @see wp-login.php
	 */
	public function login_form_lostpassword() {
		$xbee_message = ! empty( $_POST['xbee_message'] );
		$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
		$interim_login = isset( $_REQUEST['interim-login'] );
		$login_link_separator = apply_filters( 'login_link_separator', ' | ' );
		$errors = new WP_Error();

		if ( $http_post ) {
			$errors = $this->retrieve_password( $xbee_message );
			if ( ! is_wp_error($errors) ) {
				$redirect_checkemail = $xbee_message ? 'wp-login.php?checkemail=confirm&xcoobee=1' : 'wp-login.php?checkemail=confirm';
				$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : $redirect_checkemail;
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}
	
		if ( isset( $_GET['error'] ) ) {
			if ( 'invalidkey' == $_GET['error'] ) {
				$errors->add( 'invalidkey', __( 'Your password reset link appears to be invalid. Please request a new link below.' ) );
			} elseif ( 'expiredkey' == $_GET['error'] ) {
				$errors->add( 'expiredkey', __( 'Your password reset link has expired. Please request a new link below.' ) );
			}
		}
	
		$lostpassword_redirect = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

		$redirect_to = apply_filters( 'lostpassword_redirect', $lostpassword_redirect );

		do_action( 'lost_password' );
	
		login_header( __('Lost Password'), '<p class="message">' . __( 'Please enter your username, XcooBee Id or email address. You will receive a link to create a new password via email or XcooBee.', 'xcoobee' ) . '</p>', $errors );
	
		$user_login = '';
	
		if ( isset( $_POST['user_login'] ) && is_string( $_POST['user_login'] ) ) {
			$user_login = wp_unslash( $_POST['user_login'] );
		}
		?>
		
		<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( network_site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
			<p>
				<label for="user_login" ><?php _e( 'Username, XcooBee Id or Email Address' ); ?><br />
				<input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" /></label>
			</p>
			<p class="xbee-message">
				<input type="checkbox" name="xbee_message" value="on" id="xbee_message" <?php checked( $xbee_message ); ?> /><label for="xbee_message"><?php _e( 'Use XcooBee Secure Messaging', 'xcoobee' ); ?></label>
			</p>
			<?php
			do_action( 'lostpassword_form' ); ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Get New Password' ); ?>" /></p>
		</form>
		
		<p id="nav">
		<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e('Log in') ?></a>
		<?php
		if ( get_option( 'users_can_register' ) ) :
			$registration_url = sprintf( '<a href="%s">%s</a>', esc_url( wp_registration_url() ), __( 'Register' ) );
		
			echo esc_html( $login_link_separator );
		
			/** This filter is documented in wp-includes/general-template.php */
			echo apply_filters( 'register', $registration_url );
		endif;
		?>
		</p>
		
		<?php
		login_footer('user_login');

		// Stop here and don't go through the rest of wp-login.php.
		exit();
	}

	/**
	 * This method overwrites `retrieve_password()` in wp-login.php.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $xbee_message Whether to send the recovery password message through XcooBee.
	 * @return bool|WP_Error True: when finish. WP_Error on error.
	 */
	protected function retrieve_password( $xbee_message = false ) {
		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username, XcooBee Id or email address.', 'xcoobee' ) );
		} elseif ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
			
			if ( empty( $user_data ) ) {
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.', 'xcoobee' ) );
			}
		} elseif ( xbee_validate_xid( $_POST['user_login'] ) ) {
			$login = trim($_POST['user_login']);
			$user_data = get_user_by( 'id', xbee_get_user_by_xid( $login ) );

			if ( ! $user_data ) {
				$errors->add( 'invalid_xid', __( '<strong>ERROR</strong>: There is no user registered with that XcooBee Id.', 'xcoobee' ) );
			}
		} else {
			$login = trim($_POST['user_login']);
			$user_data = get_user_by( 'login', $login );
		}

		/** This filter is documented in wp-login.php */
		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			return $errors;
		}

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username, XcooBee Id or email.', 'xcoobee' ) );
			return $errors;
		}

		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$user_id = $user_data->ID;
		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Site Name: %s'), $site_name ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s'), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		/** This filter is documented in wp-login.php. */
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
	
		/** This filter is documented in wp-login.php. */
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

		// Send the recovery message via XcooBee.
		if ( $xbee_message ) {
			// Remove `<` and `>` that enclose the registration URL .
			$message = preg_replace_callback( '/<(.*?)\>/s', function( $matches ) {
				return str_replace( [ '<', '>' ], '', $matches[0] );
			}, $message );

			// Wrap links.
			$message = xbee_wrap_all_links( $message );

			// Remove `\r` from the message.
			$message = str_replace( "\r", '', $message );

			// If no XcooBee Id found, use email to send the message.
			$xid = xbee_get_xid( $user_id );
			$destination = is_null( $xid ) ? $user_email : $xid;

			$recipient = [ Sdk_Validation::isValidEmail( $destination ) ? 'email' : 'xcoobee_id' => $destination ];
			$user_reference = "{$user_id}." . md5( time() + rand( 1111, 9999 ) );

			// Send the notification email.
			try {
				$xcoobee = XcooBee::get_xcoobee( true );
				$response = $xcoobee->bees->takeOff(
					[
						'xcoobee_message' => [
							'xcoobee_simple_message' => [ 'message' => $message ],
							'recipient' => $recipient,
						]
					],
					[
						'process' => [
							'userReference' => $user_reference,
							'destinations'  => [ $destination ],
						]
					],
					[
						'target' => XcooBee::get_endpoint(),
						'events' => ['error', 'success', 'deliver', 'present', 'download', 'delete', 'reroute'],
						'handler' => 'XcooBee_Login::message_logs',
					]
				);
			} catch ( Exception $e ) {
				wp_die( __( 'The message could not be sent.' , 'xcoobee' ) );
			}

			if ( 200 === $response->code ) {
				$message_logs = get_user_meta( $user_id, 'xbee_message_logs', true );
				$message_logs = empty( $message_logs ) ? [] : $message_logs ;
				$message_logs[$user_reference] = [
					'type' => [ 'password_recovery', __( 'Password Recovery', 'xcoobee' ) ],
					'recipient' => $destination,
					'date' => strtotime( $response->time ),
					'status' => [ 'sent', __( 'Sent', 'xcoobee' ) ],
				];
				update_user_meta( $user_id, 'xbee_message_logs', $message_logs );
			} else {
				wp_die( __( 'The message could not be sent.' , 'xcoobee' ) );
			}

			return true;
		}

		// Or, send the recovery message via email.
		if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
			wp_die( __( 'The email could not be sent.', 'xcoobee' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function.', 'xcoobee' ) );
		}
	
		return true;
	}
}

new XcooBee_Login;