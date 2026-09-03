<?php
/**
 * Shipping address + order notes.
 *
 * @package Nexora
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="checkout-section" aria-labelledby="co-address">
	<div class="checkout-section__head"><h2 class="checkout-section__title" id="co-address"><span class="step-badge"><?php echo esc_html( nexora_num( 2 ) ); ?></span><?php esc_html_e( 'Shipping address', 'nexora' ); ?></h2></div>
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>
		<div class="woocommerce-shipping-fields">
			<?php if ( ! wc_ship_to_billing_address_only() ) : ?>
				<div class="option-cards checkout-address-mode" id="ship-to-different-address">
					<label class="option-card">
						<input type="radio" name="nexora_ship_mode" value="same" <?php checked( ! $checkout->get_value( 'ship_to_different_address' ) ); ?> data-ship-mode="same">
						<span class="option-card__body"><span class="option-card__icon"><?php nexora_the_icon( 'home', 'sm' ); ?></span><span class="option-card__content"><span class="option-card__title"><?php esc_html_e( 'Same as billing address', 'nexora' ); ?></span><span class="option-card__meta"><?php esc_html_e( 'Deliver to the address entered above', 'nexora' ); ?></span></span></span>
					</label>
					<label class="option-card">
						<input type="radio" name="nexora_ship_mode" value="different" <?php checked( (bool) $checkout->get_value( 'ship_to_different_address' ) ); ?> data-ship-mode="different">
						<span class="option-card__body"><span class="option-card__icon"><?php nexora_the_icon( 'plus', 'sm' ); ?></span><span class="option-card__content"><span class="option-card__title"><?php esc_html_e( 'Ship to a different address', 'nexora' ); ?></span></span></span>
					</label>
					<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox visually-hidden" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" tabindex="-1" aria-hidden="true" />
				</div>
			<?php endif; ?>
			<div class="shipping_address">
				<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>
				<div class="woocommerce-shipping-fields__field-wrapper form-grid">
					<?php foreach ( $checkout->get_checkout_fields( 'shipping' ) as $key => $field ) { woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); } ?>
				</div>
				<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
			</div>
		</div>
	<?php else : ?>
		<p class="small text-muted"><?php esc_html_e( 'This order does not require shipping.', 'nexora' ); ?></p>
	<?php endif; ?>

	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>
		<div class="woocommerce-additional-fields">
			<?php do_action( 'woocommerce_before_order_notes_fields' ); ?>
			<div class="woocommerce-additional-fields__field-wrapper">
				<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) { woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); } ?>
			</div>
		</div>
	<?php endif; ?>
	<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</section>
