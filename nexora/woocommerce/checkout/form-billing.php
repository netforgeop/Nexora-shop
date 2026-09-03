<?php
/**
 * Billing details.
 *
 * @package Nexora
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="checkout-section woocommerce-billing-fields" aria-labelledby="co-contact">
	<div class="checkout-section__head">
		<h2 class="checkout-section__title" id="co-contact"><span class="step-badge"><?php echo esc_html( nexora_num( 1 ) ); ?></span><?php echo WC()->cart->needs_shipping_address() && wc_ship_to_billing_address_only() ? esc_html__( 'Billing & shipping details', 'nexora' ) : esc_html__( 'Contact & billing details', 'nexora' ); ?></h2>
		<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?><span class="small text-muted"><?php esc_html_e( 'Have an account?', 'nexora' ); ?> <a class="link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Log in', 'nexora' ); ?></a></span><?php endif; ?>
	</div>
	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
	<div class="woocommerce-billing-fields__field-wrapper form-grid">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );
		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>
	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</section>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields checkout-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<label class="check woocommerce-form__label-for-checkbox create-account">
				<input class="check__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /><span class="check__box"></span><span class="check__label"><?php esc_html_e( 'Create an account for faster checkout next time', 'nexora' ); ?></span>
			</label>
		<?php endif; ?>
		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>
		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account form-grid">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
