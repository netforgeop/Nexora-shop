<?php
/**
 * Quick view modal body.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? $GLOBALS['product']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
if ( ! $product instanceof WC_Product ) {
	return;
}
$nexora_brand = nexora_product_brand( $product );
$nexora_cats  = wc_get_product_category_list( $product->get_id(), ' · ' );
$nexora_max   = $product->get_max_purchase_quantity();
?>
<div class="quick-view" data-product-page data-id="<?php echo (int) $product->get_id(); ?>" data-max="<?php echo (int) ( $nexora_max > 0 ? $nexora_max : 99 ); ?>">
	<div class="quick-view__media"><?php echo $product->get_image( 'woocommerce_single', array( 'alt' => $product->get_name() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<div class="quick-view__body">
		<div class="product-card__category"><?php echo wp_kses_post( wp_strip_all_tags( $nexora_cats ) ); ?><?php echo $nexora_brand ? ' · <span class="ltr">' . esc_html( $nexora_brand[0] ) . '</span>' : ''; ?></div>
		<h2 class="h4"><?php echo esc_html( $product->get_name() ); ?></h2>
		<?php if ( wc_review_ratings_enabled() ) { echo nexora_rating_html( $product->get_average_rating(), $product->get_review_count() ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $product->get_short_description() ) : ?><p class="quick-view__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 40 ) ); ?></p><?php endif; ?>
		<div class="product-info__price"><?php echo nexora_price_html( $product, 'lg', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php get_template_part( 'template-parts/products/stock', null, array( 'product' => $product ) ); ?>
		<div class="quick-view__form">
			<?php
			// WooCommerce's own add-to-cart form (handles variations, grouped, external).
			woocommerce_template_single_add_to_cart();
			?>
		</div>
		<div class="buy-box__secondary">
			<button type="button" data-action="wishlist" data-id="<?php echo (int) $product->get_id(); ?>" aria-pressed="false"><?php nexora_the_icon( 'heart', 'xs' ); ?><span><?php esc_html_e( 'Wishlist', 'nexora' ); ?></span></button>
			<button type="button" data-action="compare" data-id="<?php echo (int) $product->get_id(); ?>" aria-pressed="false"><?php nexora_the_icon( 'compare', 'xs' ); ?><span><?php esc_html_e( 'Compare', 'nexora' ); ?></span></button>
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php nexora_the_icon( 'link', 'xs' ); ?><span><?php esc_html_e( 'Full details', 'nexora' ); ?></span></a>
		</div>
	</div>
</div>
