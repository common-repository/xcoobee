<?php
/**
 * The XcooBee_Admin_User class.
 *
 * @package XcooBee/Admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XcooBee_Admin_User
 *
 * @since 1.0.0
 */
class XcooBee_Admin_User {
	/**
	 * The constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( 'on' === get_option( 'xbee_enable_login_privacy', '' ) ) {
			add_action( 'show_user_profile', [ $this, 'profile_fields' ] );
			add_action( 'edit_user_profile', [ $this, 'profile_fields' ] );
			add_action( 'personal_options_update', [ $this, 'profile_update' ] );
			add_action( 'edit_user_profile_update', [ $this, 'profile_update' ] );
			add_action( 'user_profile_update_errors', [ $this, 'profile_update_errors' ], 10, 3 );
			add_action( 'wp_ajax_xbee_clear_message_logs', [ $this, 'clear_message_logs' ] );

			add_filter( 'manage_users_columns', [ $this, 'add_user_column_xcoobee_id' ] );
			add_filter( 'manage_users_custom_column', [ $this, 'display_user_column_xcoobee_id' ], 10, 3 );
		}
	}

	/**
	 * Adds a column for XcooBee Id on the manage users screen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $column
	 * @return array
	 */
	public function add_user_column_xcoobee_id( $column ) {
		$new_column = [];
		foreach( $column as $col => $name ) {
			$new_column[ $col ] = $name;

			if ( 'username' === $col ) {
				$new_column['xbee_xid'] = __( 'XcooBee Id', 'xcoobee' );
			}
		}

		return $new_column;
	}
	
	/**
	 * Displays XcooBee Id in front of users on the manage users screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $output
	 * @param string $column_name
	 * @param int    $user_id
	 * @return string
	 */
	public function display_user_column_xcoobee_id( $output, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'xbee_xid' :
				$xid = xbee_get_xid( $user_id );
				return $xid ? $xid : '';
				break;
		}

		return $output;
	}

	/**
	 * Adds custom fields to the profile form.
	 *
	 * @since 1.0.0
	 * @param WP_User $user
	 */
	public function profile_fields( $user ) {
		$user_id = $user->ID;
		$xid = get_user_meta( $user->ID, 'xbee_xid', true );
		$message_logs = xbee_get_message_logs( $user_id );
		?>
		<h3><?php esc_html_e( 'XcooBee Information', 'xcoobee' ); ?></h3>

		<table class="form-table xbee-information">
			<tr>
				<th><label for="xbee_xid"><?php esc_html_e( 'XcooBee Id', 'xcoobee' ); ?></label></th>
				<td>
					<input type="text" id="xbee_xid" name="xbee_xid" value="<?php echo esc_attr( $xid ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Message Logs', 'xcoobee' ); ?></th>
				<td>
					<table id="xbee-message-logs" class="wp-list-table widefat">
						<thead>
							<tr>
								<th scope="col" id="message-type"><?php _e( 'Message Type', 'xcoobee' ); ?></th>
								<th scope="col" id="recipient"><?php _e( 'Recipient', 'xcoobee' ); ?></th>
								<th scope="col" id="last-updated"><?php _e( 'Last Updated', 'xcoobee' ); ?></th>
								<th scope="col" id="status"><?php _e( 'Status', 'xcoobee' ); ?></th>
							</tr>
						</thead>
						<tbody id="the-list">
							<?php if ( empty( $message_logs ) ) : ?>
							<tr>
								<td><?php _e( 'No message logs found.', 'xcoobee' ); ?></td>
							</tr>
							<?php else : ?>
							<?php foreach ( $message_logs as $message ) : ?>
							<tr>
								<td><?php echo $message['type'][1]; ?></td>
								<td><?php echo $message['recipient']; ?></td>
								<td><?php echo xbee_get_timestamp_as_datetime( $message['date'] ); ?></td>
								<td><mark class="status <?php echo $message['status'][0]; ?>"><?php echo $message['status'][1]; ?></mark></td>
							</tr>
							<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
					<?php if ( ! empty( $message_logs ) ) : ?>
					<a href="" class="xbee-clear-message-logs" id="xbee-clear-message-logs" data-user-id="<?php echo $user_id; ?>"><?php _e( 'Clear All Logs' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Updates user profile.
	 *
	 * @since 1.0.0
	 * @param int $user_id
	 */
	public function profile_update( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		// Validations.
		if ( empty( $_POST['xbee_xid'] ) ||
			( xbee_validate_xid( $_POST['xbee_xid'] ) && is_null( xbee_get_user_by_xid( $_POST['xbee_xid'] ) ) )
		) {
			update_user_meta( $user_id, 'xbee_xid', $_POST['xbee_xid'] );
		}
	}

	/**
	 * Validates profile fields.
	 *
	 * @since 1.0.0
	 *
	 * @param object $errors
	 * @param bool $update
	 * @param object $user
	 */
	public function profile_update_errors( $errors, $update, $user ) {
		if ( ! $update ) {
			return;
		}

		if ( ! empty( $_POST['xbee_xid'] ) && ! xbee_validate_xid( $_POST['xbee_xid'] ) ) {
			$errors->add( 'invalid_xid', __( '<strong>ERROR</strong>: Please enter a valid XcooBee Id (begins with a telda ~).', 'xcoobee' ) );
		}

		if ( ! empty( $_POST['xbee_xid'] ) && ( ! is_null( xbee_get_user_by_xid( $_POST['xbee_xid'] ) ) && $_POST['xbee_xid'] !== xbee_get_xid( $user->ID ) ) ) {
			$errors->add( 'invalid_xid', __( '<strong>ERROR</strong>: This XcooBee Id is already used by another user.', 'xcoobee' ) );
		}
	}

	/**
	 * Clears user message logs
	 *
	 * @since 1.0.0
	 */
	public function clear_message_logs() {
		$user_id = $_POST['userId'];

		if ( delete_user_meta( $user_id, 'xbee_message_logs' ) ) {
			$result = ( object ) [
				'result' => true,
				'status' => 'success',
				'code'   => 'success_clear_message_logs',
				'errors' => [],
			];
		} else {
			$result = ( object ) [
				'result' => false,
				'status' => 'error',
				'code'   => 'error_clear_message_logs',
				'errors' => [ xbee_get_text( 'message_error_clear_message_logs' ) ],
			];
		}
		
		// Send response, and die.
		wp_send_json( json_encode( $result ) );
	}
}

new XcooBee_Admin_User;