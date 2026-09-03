<?php
/**
 * Product card (shop loop, carousels, grids).
 *
 * @package Nexora
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}
$nexora_args = wp_parse_args( (array) get_query_var( 'nexora_card_args' ), array( 'view' => 'grid', 'flash' => false, 'priority' => false ) );
set_query_var( 'nexora_card_args', array() );
$nexora_id       = $product->get_id();
$nexora_url      = $product->get_permalink();
$nexora_in_stock = $product->is_in_stock();
$nexora_gallery  = $product->get_gallery_image_ids();
$nexora_hover    = nexora_option( 'shop', 'card_hover' ) && ! empty( $nexora_gallery ) ? (int) $nexora_gallery[0] : 0;
$nexora_swatches = nexora_option( 'shop', 'card_swatches' ) ? nexora_product_swatches( $product ) : array();
$nexora_cats     = get_the_terms( $nexora_id, 'product_cat' );
$nexora_cat      = $nexora_cats && ! is_wp_error( $nexora_cats ) ? end( $nexora_cats ) : null;
$nexora_simple   = $product->is_type( 'simple' ) && $product->is_purchasable() && $nexora_in_stock;
$nexora_img_attr = $nexora_args['priority'] ? array( 'fetchpriority' => 'high', 'decoding' => 'async' ) : array( 'loading' => 'lazy', 'decoding' => 'async' );
$nexora_classes  = array( 'product-card' );
if ( 'list' === $nexora_args['view'] ) {
	$nexora_classes[] = 'product-card--list';
}
if ( ! $nexora_in_stock ) {
	$nexora_classes[] = 'is-out-of-stock';
}
?>
<article <?php wc_product_class( $nexora_classes, $product ); ?> data-product-card data-id="<?php echo (int) $nexora_id; ?>">
	<div class="product-card__media">
		<a href="<?php echo esc_url( $nexora_url ); ?>" tabindex="-1" aria-hidden="true" class="product-card__link">
			<?php echo $product->get_image( 'woocommerce_thumbnail', array_merge( $nexora_img_attr, array( 'class' => 'product-card__img product-card__img--main', 'alt' => $product->get_name() ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $nexora_hover ) : ?>
				<?php echo wp_get_attachment_image( $nexora_hover, 'woocommerce_thumbnail', false, array( 'class' => 'product-card__img product-card__img--hover', 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
			<?php endif; ?>
		</a>
		<div class="product-card__badges"><?php echo nexora_product_badges( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="product-card__actions">
			<?php if ( nexora_option( 'shop', 'card_wishlist' ) ) : ?>
				<button type="button" class="product-card__action" data-action="wishlist" data-id="<?php echo (int) $nexora_id; ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'nexora' ); ?>" aria-pressed="false" title="<?php esc_attr_e( 'Add to wishlist', 'nexora' ); ?>"><?php nexora_the_icon( 'heart' ); ?></button>
			<?php endif; ?>
			<?php if ( nexora_option( 'shop', 'card_quickview' ) ) : ?>
				<button type="button" class="product-card__action" data-action="quick-view" data-id="<?php echo (int) $nexora_id; ?>" aria-label="<?php esc_attr_e( 'Quick view', 'nexora' ); ?>" title="<?php esc_attr_e( 'Quick view', 'nexora' ); ?>"><?php nexora_the_icon( 'eye' ); ?></button>
			<?php endif; ?>
			<?php if ( nexora_option( 'shop', 'card_compare' ) ) : ?>
				<button type="button" class="product-card__action" data-action="compare" data-id="<?php echo (int) $nexora_id; ?>" aria-label="<?php esc_attr_e( 'Add to compare', 'nexora' ); ?>" aria-pressed="false" title="<?php esc_attr_e( 'Add to compare', 'nexora' ); ?>"><?php nexora_the_icon( 'compare' ); ?></button>
			<?php endif; ?>
		</div>
		<?php if ( ! $nexora_in_stock ) : ?><span class="product-card__out"><?php esc_html_e( 'Out of stock', 'nexora' ); ?></span><?php endif; ?>
	</div>
	<div class="product-card__body">
		<?php if ( $nexora_cat && nexora_option( 'shop', 'card_category' ) ) : ?>
			<div class="product-card__category"><a href="<?php echo esc_url( get_term_link( $nexora_cat ) ); ?>"><?php echo esc_html( $nexora_cat->name ); ?></a></div>
		<?php endif; ?>
		<h3 class="product-card__title"><a href="<?php echo esc_url( $nexora_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<?php if ( nexora_option( 'shop', 'card_rating' ) && wc_review_ratings_enabled() ) { echo nexora_rating_html( $product->get_average_rating(), $product->get_review_count() ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( 'list' === $nexora_args['view'] && $product->get_short_description() ) : ?>
			<p class="product-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 28 ) ); ?></p>
		<?php endif; ?>
		<?php
		if ( $nexora_args['flash'] && $product->managing_stock() && $product->get_stock_quantity() !== null ) :
			$nexora_sold = (int) $product->get_total_sales();
			$nexora_pct  = min( 96, (int) round( $nexora_sold / max( 1, $nexora_sold + $product->get_stock_quantity() ) * 100 ) );
			/* translators: %s: number */
			$nexora_sold_label = sprintf( __( '%s sold', 'nexora' ), nexora_num( $nexora_sold ) );
			?>
			<div class="product-card__progress" role="progressbar" aria-valuenow="<?php echo (int) $nexora_pct; ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo esc_attr( $nexora_sold_label ); ?>"><div class="product-card__progress-fill" style="inline-size:<?php echo (int) $nexora_pct; ?>%"></div></div>
			<div class="product-card__progress-text"><?php echo esc_html( $nexora_sold_label ); ?></div>
		<?php endif; ?>
		<div class="product-card__footer">
			<div>
				<?php echo nexora_price_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $nexora_swatches ) : ?>
					<div class="product-card__variants" aria-label="<?php esc_attr_e( 'Colours', 'nexora' ); ?>">
						<?php foreach ( array_slice( $nexora_swatches, 0, 4 ) as $nexora_sw ) : ?><span class="product-card__swatch" style="background:<?php echo esc_attr( $nexora_sw[1] ); ?>" title="<?php echo esc_attr( $nexora_sw[0] ); ?>"></span><?php endforeach; ?>
						<?php if ( count( $nexora_swatches ) > 4 ) : ?><span class="product-card__swatch product-card__swatch--more">+<?php echo esc_html( nexora_num( count( $nexora_swatches ) - 4 ) ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( $nexora_simple ) : ?>
				<button type="button" class="product-card__add" data-action="add-to-cart" data-id="<?php echo (int) $nexora_id; ?>" aria-label="<?php esc_attr_e( 'Add to cart', 'nexora' ); ?>"><?php nexora_the_icon( 'cart-add' ); ?><span class="product-card__add-text"><?php esc_html_e( 'Add to cart', 'nexora' ); ?></span></button>
			<?php elseif ( $nexora_in_stock && $product->is_purchasable() ) : ?>
				<a class="product-card__add" href="<?php echo esc_url( $nexora_url ); ?>" aria-label="<?php echo esc_attr( $product->add_to_cart_text() ); ?>"><?php nexora_the_icon( 'eye' ); ?><span class="product-card__add-text"><?php echo esc_html( $product->add_to_cart_text() ); ?></span></a>
			<?php else : ?>
				<button type="button" class="product-card__add" disabled aria-label="<?php esc_attr_e( 'Out of stock', 'nexora' ); ?>"><?php nexora_the_icon( 'cart-add' ); ?><span class="product-card__add-text"><?php esc_html_e( 'Out of stock', 'nexora' ); ?></span></button>
			<?php endif; ?>
		</div>
	</div>
</article>
