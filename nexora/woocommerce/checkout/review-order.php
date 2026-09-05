<?php
/**
 * Order review (AJAX-refreshed fragment).
 *
 * @package Nexora
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="shop_table woocommerce-checkout-review-order-table summary__review">
<div class="summary__items scroll-thin">
	<?php
	do_action( 'woocommerce_review_order_before_cart_contents' );
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			?>
			<div class="summary__item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<?php echo $_product->get_image( 'nexora-thumb', array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div><div class="summary__item-title"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></div><div class="summary__item-meta"><span class="num"><?php echo esc_html( nexora_num( $cart_item['quantity'] ) ); ?></span> × <?php echo wp_kses_post( WC()->cart->get_product_price( $_product ) ); ?><?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div>
				<div class="num"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			</div>
			<?php
		}
	}
	do_action( 'woocommerce_review_order_after_cart_contents' );
	?>
</div>
<div class="summary__rows">
	<div class="summary__row cart-subtotal"><span><?php esc_html_e( 'Subtotal', 'nexora' ); ?> (<?php echo esc_html( nexora_num( WC()->cart->get_cart_contents_count() ) ); ?>)</span><span class="num"><?php wc_cart_totals_subtotal_html(); ?></span></div>
	<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<div class="summary__row summary__row--discount cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>"><span><?php wc_cart_totals_coupon_label( $coupon ); ?></span><span class="num"><?php wc_cart_totals_coupon_html( $coupon ); ?></span></div>
	<?php endforeach; ?>
	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		<?php wc_cart_totals_shipping_html(); ?>
		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
	<?php endif; ?>
	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<div class="summary__row fee"><span><?php echo esc_html( $fee->name ); ?></span><span class="num"><?php wc_cart_totals_fee_html( $fee ); ?></span></div>
	<?php endforeach; ?>
	<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
		<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
			<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
				<div class="summary__row tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>"><span><?php echo esc_html( $tax->label ); ?></span><span class="num"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span></div>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="summary__row tax-total"><span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span><span class="num"><?php wc_cart_totals_taxes_total_html(); ?></span></div>
		<?php endif; ?>
	<?php endif; ?>
	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>
</div>
<div class="summary__total order-total"><span><?php esc_html_e( 'Total', 'nexora' ); ?></span><span class="num"><?php wc_cart_totals_order_total_html(); ?></span></div>
<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
</div>
