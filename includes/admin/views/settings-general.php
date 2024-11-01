<?php
/**
 * The general tab
 *
 * @package XcooBee/Admin/Views
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Fields.
$api_key            = get_option( 'xbee_api_key', '' );
$api_secret         = get_option( 'xbee_api_secret', '' );
$pgp_secret         = get_option( 'xbee_pgp_secret', '' );
$pgp_password       = get_option( 'xbee_pgp_password', '' );
$enable_privacy_ids = get_option( 'xbee_enable_login_privacy', '' );
$enable_endpoint    = get_option( 'xbee_enable_endpoint', '' );
$endpoint           = get_option( 'xbee_endpoint', '' );

?>
<?php settings_fields( 'xbee_general' ); ?>

<!-- Section: Account Configuration -->
<div class="section">
	<h2 class="headline"><?php _e( 'Account Configuration', 'xcoobee' ); ?></h2>
	<p class="message<?php xbee_add_css_class( xbee_config_file_exists(), 'hide', true, true ); ?>"><?php echo sprintf( __( 'If you don\'t have your XcooBee API Keys in the config file located at <code>%1$s</code> you can enter them here. Please <a href="%2$s" target="_blank">login</a> to XcooBee and get your keys. You will need to be a developer, professional, business or enterprise subscriber to generate API keys.', 'xcoobee' ), XBEE_CONFIG_FILE, xbee_get_text( 'url_login') ); ?></p>
	<table class="form-table">
		<tbody class="<?php xbee_add_css_class( ! xbee_config_file_exists(), 'hide', false, true ); ?>">
			<tr>
				<td><?php echo sprintf( __( 'Reading from the config file located at <code>%1$s</code>.', 'xoobee' ), XBEE_CONFIG_FILE ); ?></td>
			</tr>
		</tbody>
		<tbody class="<?php xbee_add_css_class( xbee_config_file_exists(), 'hide', false, true ); ?>">
			<tr>
				<th scope="row"><label for="xbee_api_key"><?php _e( 'API Key', 'xcoobee' ); ?></label></th>
				<td><input name="xbee_api_key" type="text" id="xbee_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="xbee_api_secret"><?php _e( 'API Secret', 'xcoobee' ); ?></label></th>
				<td><input name="xbee_api_secret" type="password" id="xbee_api_secret" value="<?php echo esc_attr( $api_secret ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="xbee_pgp_secret"><?php _e( 'PGP Secret', 'xcoobee' ); ?></label></th>
				<td><textarea cols="50" rows="5" name="xbee_pgp_secret" type="text" class="large-text code" id="xbee_pgp_secret"><?php echo esc_attr( $pgp_secret ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="xbee_pgp_password"><?php _e( 'PGP Password', 'xcoobee' ); ?></label></th>
				<td><input name="xbee_pgp_password" type="password" id="xbee_pgp_password" value="<?php echo esc_attr( $pgp_password ); ?>" class="regular-text" /></td>
			</tr>
		</tbody>
	</table>
	<div class="test-keys">
		<input id="test-keys" type="button" class="button button-primary" value="<?php _e( 'Test Keys', 'xcoobee' ); ?>"/>
		<div class="xbee-notification" data-notification="test-api-keys"></div>
	</div>
</div>
<!-- End Section: Account Configuration -->

<!-- Section: Login Privacy -->
<div class="section">
	<h2 class="headline"><?php _e( 'Enable Login Privacy', 'xcoobee' ); ?></h2>
	<p class="message"><?php _e( 'We will add features to your login and password recovery to enable the use of XcooBee Ids and send secure password recovery messages.', 'xcoobee' ); ?></p>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Enable XcooBee Privacy Ids', 'xcoobee' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Enable XcooBee Privacy Ids', 'xcoobee' ); ?></span></legend>
						<label for="xbee_enable_login_privacy">
							<input name="xbee_enable_login_privacy" type="checkbox" id="xbee_enable_login_privacy" <?php checked( $enable_privacy_ids, 'on' ); ?>> <?php _e( 'Enable XcooBee Privacy Ids', 'xcoobee' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- End Section: Login Privacy -->

<!-- Section: Endpoint-->
<div class="section">
	<h2 class="headline"><?php _e( 'Endpoint', 'xcoobee' ); ?></h2>
	<p class="message"><?php echo sprintf( __( 'The plugin will use <code>%1$s</code> to receive data from the XcooBee network. In order to receive notifications, it must be possible to access your website via the Internet directly.', 'xcoobee' ), XcooBee::get_endpoint() ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e( 'Enable Endpoint', 'xcoobee' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Enable Endpoint', 'xcoobee' ); ?></span></legend>
					<label for="xbee_enable_endpoint">
						<input name="xbee_enable_endpoint" type="checkbox" id="xbee_enable_endpoint" <?php checked( $enable_endpoint, 'on' ); ?>> <?php _e( 'Enable Endpoint', 'xcoobee' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>
	</table>
</div>
<!-- End Section: Endpoint -->

<p class="actions"><?php submit_button( 'Save Changes', 'primary', 'submit', false ); ?></p>