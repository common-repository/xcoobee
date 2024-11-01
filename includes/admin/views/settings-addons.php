<?php
/**
 * The addons tab
 *
 * @package XcooBee/Admin/Views
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php foreach( xbee_get_addons() as $addon ) : ?>
	<div class="addon">
		<img class="icon" src="<?php echo $addon['icon']; ?>" />
		<h2 class="name"><?php echo $addon['name'] ?></h2>
		<p class="description"><?php echo $addon['description']; ?></p>
		<?php if ( $addon['action_links'] ) : ?>
			<ul class="action-links"><li><?php echo implode( '</li><li>', $addon['action_links'] ); ?></li></ul>
		<?php endif; ?>
	</div>
<?php endforeach; ?>