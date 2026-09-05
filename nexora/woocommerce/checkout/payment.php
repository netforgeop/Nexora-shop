<?php
/**
 * Payment methods + place order.
 *
 * @package Nexora
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment checkout-payment">
	<h3 class="checkout-section__title checkout-section__title--sm"><span class="step-badge"><?php echo esc_html( nexora_num( 3 ) ); ?></span><?php esc_html_e( 'Payment', 'nexora' ); ?></h3>
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods option-cards option-cards--compact">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info alert alert--warning">' . wp_kses_post( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance.', 'nexora' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'nexora' ) ) ) . '</li>';
			}
			?>
		</ul>
	<?php endif; ?>
	<div class="form-row place-order">
		<noscript>
			<?php
			/* translators: $1 and $2 opening and closing emphasis tags respectively */
			printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'nexora' ), '<em>', '</em>' );
			?>
			<br/><button type="submit" class="btn btn--outline btn--sm" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'nexora' ); ?>"><?php esc_html_e( 'Update totals', 'nexora' ); ?></button>
		</noscript>
		<?php wc_get_template( 'checkout/terms.php' ); ?>
		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
		<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="btn btn--primary btn--lg btn--block" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . nexora_icon( 'lock', 'xs' ) . esc_html( $order_button_text ) . '</button>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
	<div class="summary__secure"><?php nexora_the_icon( 'shield-check', 'xs' ); ?><?php esc_html_e( 'Your information is encrypted and secure', 'nexora' ); ?></div>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
