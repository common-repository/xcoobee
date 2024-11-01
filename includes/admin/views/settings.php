<?php
/**
 * Setting pages
 *
 * @package XcooBee/Admin/Views
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$classes = [ 'general' => '', 'cookie' => '', 'document' => '', 'form' => '', 'sar' => '', 'addons' => '' ];
$tab = isset( $_GET['tab'] ) && '' !== $_GET['tab'] ? $_GET['tab'] : 'general';

if ( isset( $tab ) ) {
	// Addons tab.
	if ( 'addons' === $tab ) {
		$classes['addons'] .= ' nav-tab-active';
		$tab_content = XBEE_ABSPATH . 'includes/admin/views/settings-addons.php';
	}
	// Cookie tab (addon plugin).
	elseif ( 'cookie' === $tab && defined( 'XBEE_COOKIE_ABSPATH' ) ) {
		$classes['cookie'] .= ' nav-tab-active';
		$tab_content = XBEE_COOKIE_ABSPATH . 'includes/admin/views/settings-cookie.php';
	}
	// Document tab (addon plugin).
	elseif ( 'document' === $tab && defined( 'XBEE_DOCUMENT_ABSPATH' ) ) {
		$classes['document'] .= ' nav-tab-active';
		$tab_content = XBEE_DOCUMENT_ABSPATH . 'includes/admin/views/settings-document.php';
	}
	// Form tab (addon plugin).
	elseif ( 'form' === $tab && defined( 'XBEE_FORM_ABSPATH' ) ) {
		$classes['form'] .= ' nav-tab-active';
		$tab_content = XBEE_FORM_ABSPATH . 'includes/admin/views/settings-form.php';
	}
	// SAR tab (addon plugin).
	elseif ( 'sar' === $tab && defined( 'XBEE_SAR_ABSPATH' ) ) {
		$classes['sar'] .= ' nav-tab-active';
		$tab_content = XBEE_SAR_ABSPATH . 'includes/admin/views/settings-sar.php';
	}
	// Else, display the general tab.
	else {
		$classes['general'] .= ' nav-tab-active';
		$tab_content = XBEE_ABSPATH . 'includes/admin/views/settings-general.php';
	}
}

settings_errors();
?>
<div class="wrap xbee">
	<h1><?php _e( 'XcooBee Settings', 'xcoobee' ); ?></h1>
	<form method="post" action="options.php" id="xbee-settings-<?php echo $tab; ?>">
		<nav class="nav-tab-wrapper xbee-nav-tab-wrapper">
			<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=general' ); ?>" class="nav-tab<?php echo $classes['general']; ?>"><?php _e( 'General', 'xcoobee' ); ?></a>
			<?php if ( defined( 'XBEE_COOKIE_ABSPATH' ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=cookie' ); ?>" class="nav-tab<?php echo $classes['cookie']; ?>"><?php _e( 'Cookie', 'xcoobee' ); ?></a>
			<?php endif; ?>
			<?php if ( defined( 'XBEE_DOCUMENT_ABSPATH' ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=document' ); ?>" class="nav-tab<?php echo $classes['document']; ?>"><?php _e( 'Document', 'xcoobee' ); ?></a>
			<?php endif; ?>
			<?php if ( defined( 'XBEE_FORM_ABSPATH' ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=form' ); ?>" class="nav-tab<?php echo $classes['form']; ?>"><?php _e( 'Form', 'xcoobee' ); ?></a>
			<?php endif; ?>
			<?php if ( defined( 'XBEE_SAR_ABSPATH' ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=sar' ); ?>" class="nav-tab<?php echo $classes['sar']; ?>"><?php _e( 'SAR', 'xcoobee' ); ?></a>
			<?php endif; ?>
			<a href="<?php echo admin_url( 'admin.php?page=xcoobee&amp;tab=addons' ); ?>" class="nav-tab<?php echo $classes['addons']; ?>"><?php _e( 'Addons', 'xcoobee' ); ?></a>
		</nav>
		<div class="postbox">
			<div class="inside tab tab-<?php echo $tab; ?>">
				<?php include $tab_content; ?>
			</div>
		</div>
	</form>
</div>