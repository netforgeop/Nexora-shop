<?php
/**
 * Trust bar.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_items = (array) $args['home']['trust_items'];
if ( ! $nexora_items ) {
	return;
}
?>
<section class="section section--sm" aria-label="<?php esc_attr_e( 'Why shop with us', 'nexora' ); ?>">
	<div class="container">
		<div class="trust-bar trust-bar--framed">
			<div class="trust-bar__grid">
				<?php foreach ( $nexora_items as $nexora_item ) : ?>
					<div class="trust-item"><span class="trust-item__icon"><?php nexora_the_icon( $nexora_item['icon'] ); ?></span><div><div class="trust-item__title"><?php echo esc_html( $nexora_item['title'] ); ?></div><div class="trust-item__text"><?php echo esc_html( $nexora_item['text'] ); ?></div></div></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
