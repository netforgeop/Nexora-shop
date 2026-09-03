<?php
/**
 * Shipping calculator (compact).
 *
 * @package Nexora
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_shipping_calculator' );
?>
<form class="woocommerce-shipping-calculator shipping-calc" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php printf( '<a href="#" class="shipping-calculator-button link small">%s</a>', esc_html( ! empty( $button_text ) ? $button_text : __( 'Calculate shipping', 'nexora' ) ) ); ?>
	<section class="shipping-calculator-form" style="display:none;">
		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_country', true ) ) : ?>
			<div class="form-group" id="calc_shipping_country_field">
				<label for="calc_shipping_country" class="visually-hidden"><?php esc_html_e( 'Country / region', 'nexora' ); ?></label>
				<select name="calc_shipping_country" id="calc_shipping_country" class="form-control country_to_state country_select" rel="calc_shipping_state">
					<option value="default"><?php esc_html_e( 'Select a country / region…', 'nexora' ); ?></option>
					<?php foreach ( WC()->countries->get_shipping_countries() as $key => $value ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( WC()->customer->get_shipping_country(), esc_attr( $key ) ); ?>><?php echo esc_html( $value ); ?></option><?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>
		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_state', true ) ) : ?>
			<div class="form-group" id="calc_shipping_state_field">
				<?php
				$current_cc = WC()->customer->get_shipping_country();
				$current_r  = WC()->customer->get_shipping_state();
				$states     = WC()->countries->get_states( $current_cc );
				if ( is_array( $states ) && empty( $states ) ) {
					?><input type="hidden" name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php esc_attr_e( 'State / County', 'nexora' ); ?>" /><?php
				} elseif ( is_array( $states ) ) {
					?>
					<span><label for="calc_shipping_state" class="visually-hidden"><?php esc_html_e( 'State / County', 'nexora' ); ?></label>
					<select name="calc_shipping_state" class="form-control state_select" id="calc_shipping_state" data-placeholder="<?php esc_attr_e( 'State / County', 'nexora' ); ?>">
						<option value=""><?php esc_html_e( 'Select an option…', 'nexora' ); ?></option>
						<?php foreach ( $states as $ckey => $cvalue ) : ?><option value="<?php echo esc_attr( $ckey ); ?>" <?php selected( $current_r, $ckey ); ?>><?php echo esc_html( $cvalue ); ?></option><?php endforeach; ?>
					</select></span>
					<?php
				} else {
					?><label for="calc_shipping_state" class="visually-hidden"><?php esc_html_e( 'State / County', 'nexora' ); ?></label><input type="text" class="form-control" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php esc_attr_e( 'State / County', 'nexora' ); ?>" name="calc_shipping_state" id="calc_shipping_state" /><?php
				}
				?>
			</div>
		<?php endif; ?>
		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_city', true ) ) : ?>
			<div class="form-group" id="calc_shipping_city_field"><label for="calc_shipping_city" class="visually-hidden"><?php esc_html_e( 'Town / City', 'nexora' ); ?></label><input type="text" class="form-control" value="<?php echo esc_attr( WC()->customer->get_shipping_city() ); ?>" placeholder="<?php esc_attr_e( 'Town / City', 'nexora' ); ?>" name="calc_shipping_city" id="calc_shipping_city" /></div>
		<?php endif; ?>
		<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) ) : ?>
			<div class="form-group" id="calc_shipping_postcode_field"><label for="calc_shipping_postcode" class="visually-hidden"><?php esc_html_e( 'Postcode / ZIP', 'nexora' ); ?></label><input type="text" class="form-control" value="<?php echo esc_attr( WC()->customer->get_shipping_postcode() ); ?>" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'nexora' ); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" dir="ltr" /></div>
		<?php endif; ?>
		<p><button type="submit" name="calc_shipping" value="1" class="btn btn--dark btn--sm"><?php esc_html_e( 'Update', 'nexora' ); ?></button></p>
		<?php wp_nonce_field( 'woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce' ); ?>
	</section>
</form>
<?php do_action( 'woocommerce_after_shipping_calculator' ); ?>
