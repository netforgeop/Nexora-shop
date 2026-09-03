<?php
/**
 * Wishlist row.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_p = wc_get_product( (int) ( $args['id'] ?? 0 ) );
if ( ! $nexora_p || ! $nexora_p->is_visible() ) {
	return;
}
$nexora_cats = wc_get_product_category_list( $nexora_p->get_id(), ', ' );
?>
<div class="wish-item" data-id="<?php echo (int) $nexora_p->get_id(); ?>">
	<a class="cart-item__media" href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo $nexora_p->get_image( 'nexora-thumb', array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<div class="cart-item__info">
		<?php if ( $nexora_cats ) : ?><div class="product-card__category"><?php echo wp_kses_post( wp_strip_all_tags( $nexora_cats ) ); ?></div><?php endif; ?>
		<h3 class="cart-item__title"><a href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php echo esc_html( $nexora_p->get_name() ); ?></a></h3>
		<?php echo nexora_rating_html( $nexora_p->get_average_rating(), $nexora_p->get_review_count() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div><?php echo nexora_price_html( $nexora_p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<div><?php echo $nexora_p->is_in_stock() ? '<span class="status status--success">' . esc_html__( 'In stock', 'nexora' ) . '</span>' : '<span class="status status--danger">' . esc_html__( 'Out of stock', 'nexora' ) . '</span>'; ?></div>
	<div class="cluster">
		<?php if ( $nexora_p->is_purchasable() && $nexora_p->is_in_stock() && $nexora_p->is_type( 'simple' ) ) : ?>
			<button type="button" class="btn btn--dark btn--sm" data-action="add-to-cart" data-id="<?php echo (int) $nexora_p->get_id(); ?>"><?php nexora_the_icon( 'cart-add', 'xs' ); ?><?php esc_html_e( 'Add to cart', 'nexora' ); ?></button>
		<?php elseif ( $nexora_p->is_in_stock() ) : ?>
			<a class="btn btn--dark btn--sm" href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php esc_html_e( 'Select options', 'nexora' ); ?></a>
		<?php else : ?>
			<a class="btn btn--outline btn--sm" href="<?php echo esc_url( $nexora_p->get_permalink() ); ?>"><?php nexora_the_icon( 'alarm', 'xs' ); ?><?php esc_html_e( 'Notify me', 'nexora' ); ?></a>
		<?php endif; ?>
	</div>
	<button type="button" class="cart-item__remove" data-action="wishlist" data-id="<?php echo (int) $nexora_p->get_id(); ?>" aria-pressed="true" aria-label="<?php esc_attr_e( 'Remove from wishlist', 'nexora' ); ?>"><?php nexora_the_icon( 'trash2', 'sm' ); ?></button>
</div>
