<?php
/**
 * Cart totals (summary card).
 *
 * @package Nexora
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;
$nexora_threshold = (float) nexora_option( 'shop', 'free_shipping_min', 0 );
?>
<div class="summary cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">
	<?php do_action( 'woocommerce_before_cart_totals' ); ?>
	<h2 class="summary__title"><?php esc_html_e( 'Order summary', 'nexora' ); ?></h2>
	<?php
	if ( $nexora_threshold > 0 ) :
		$nexora_sub = (float) WC()->cart->get_subtotal();
		$nexora_pct = min( 100, (int) round( $nexora_sub / $nexora_threshold * 100 ) );
		?>
		<div class="summary__shipping-progress" data-shipping-progress>
			<span><?php echo $nexora_pct >= 100 ? esc_html__( 'Congratulations — shipping is free!', 'nexora' ) : wp_kses_post( sprintf( /* translators: %s: amount */ __( 'Add %s more for free shipping', 'nexora' ), wc_price( $nexora_threshold - $nexora_sub ) ) ); ?></span>
			<div class="product-card__progress"><div class="product-card__progress-fill" style="inline-size:<?php echo (int) $nexora_pct; ?>%"></div></div>
		</div>
	<?php endif; ?>
	<div class="summary__rows shop_table shop_table_responsive">
		<div class="summary__row cart-subtotal"><span><?php esc_html_e( 'Subtotal', 'nexora' ); ?> (<?php echo esc_html( nexora_num( WC()->cart->get_cart_contents_count() ) ); ?>)</span><span class="num"><?php wc_cart_totals_subtotal_html(); ?></span></div>
		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="summary__row summary__row--discount cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>"><span><?php wc_cart_totals_coupon_label( $coupon ); ?></span><span class="num"><?php wc_cart_totals_coupon_html( $coupon ); ?></span></div>
		<?php endforeach; ?>
		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<div class="summary__row shipping"><span><?php esc_html_e( 'Shipping', 'nexora' ); ?></span><span><?php woocommerce_shipping_calculator(); ?></span></div>
		<?php endif; ?>
		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="summary__row fee"><span><?php echo esc_html( $fee->name ); ?></span><span class="num"><?php wc_cart_totals_fee_html( $fee ); ?></span></div>
		<?php endforeach; ?>
		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';
			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'nexora' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
					echo '<div class="summary__row tax-rate tax-rate-' . esc_attr( sanitize_title( $code ) ) . '"><span>' . esc_html( $tax->label ) . wp_kses_post( $estimated_text ) . '</span><span class="num">' . wp_kses_post( $tax->formatted_amount ) . '</span></div>';
				}
			} else {
				echo '<div class="summary__row tax-total"><span>' . esc_html( WC()->countries->tax_or_vat() ) . wp_kses_post( $estimated_text ) . '</span><span class="num">' . wp_kses_post( WC()->cart->get_taxes_total() ) . '</span></div>'; // phpcs:ignore
			}
		}
		?>
		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
	</div>
	<div class="summary__total order-total"><span><?php esc_html_e( 'Total', 'nexora' ); ?></span><span class="num"><?php wc_cart_totals_order_total_html(); ?></span></div>
	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>
	<div class="summary__secure"><?php nexora_the_icon( 'lock', 'xs' ); ?><?php esc_html_e( 'Secure payment via bank gateway', 'nexora' ); ?></div>
	<?php $nexora_pay = (array) nexora_option( 'footer', 'payments' ); ?>
	<?php if ( $nexora_pay ) : ?><div class="summary__payments"><?php foreach ( $nexora_pay as $nexora_p ) { echo nexora_svg( $nexora_p ); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
