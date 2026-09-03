<?php
/**
 * Shipping methods row (cart + checkout review).
 *
 * @package Nexora
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

$formatted_destination    = isset( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
$has_calculated_shipping  = ! empty( $has_calculated_shipping );
$show_shipping_calculator = ! empty( $show_shipping_calculator );
$calculator_text          = '';
?>
<div class="summary__row summary__row--shipping woocommerce-shipping-totals shipping">
	<span><?php echo wp_kses_post( $package_name ); ?></span>
	<div class="summary__shipping" data-title="<?php echo esc_attr( $package_name ); ?>">
		<?php if ( ! empty( $available_methods ) && is_array( $available_methods ) ) : ?>
			<ul id="shipping_method" class="woocommerce-shipping-methods option-cards option-cards--compact">
				<?php foreach ( $available_methods as $method ) : ?>
					<li class="option-card">
						<?php
						if ( 1 < count( $available_methods ) ) {
							printf( '<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ), checked( $method->id, $chosen_method, false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							printf( '<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr( sanitize_title( $method->id ) ), esc_attr( $method->id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
						<label class="option-card__body" for="shipping_method_<?php echo esc_attr( $index ); ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>">
							<span class="option-card__icon"><?php nexora_the_icon( false !== strpos( $method->id, 'local_pickup' ) ? 'store' : ( false !== strpos( $method->id, 'free' ) ? 'gift' : 'truck' ), 'sm' ); ?></span>
							<span class="option-card__content"><span class="option-card__title"><?php echo wp_kses_post( $method->get_label() ); ?></span></span>
							<span class="option-card__price"><?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) === $method->get_label() ? esc_html__( 'Free', 'nexora' ) : str_replace( $method->get_label() . ': ', '', wc_cart_totals_shipping_method_label( $method ) ) ); ?></span>
						</label>
						<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( is_cart() ) : ?>
				<p class="woocommerce-shipping-destination small text-muted">
					<?php
					if ( $formatted_destination ) {
						/* translators: %s: shipping destination */
						printf( esc_html__( 'Shipping to %s.', 'nexora' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' );
						$calculator_text = esc_html__( 'Change address', 'nexora' );
					} else {
						echo wp_kses_post( apply_filters( 'woocommerce_shipping_estimate_html', __( 'Shipping options will be updated during checkout.', 'nexora' ) ) );
					}
					?>
				</p>
			<?php endif; ?>
		<?php elseif ( ! $has_calculated_shipping || ! $formatted_destination ) : ?>
			<?php
			if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
				echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'nexora' ) ) );
			} else {
				echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'nexora' ) ) );
			}
			?>
		<?php elseif ( ! is_cart() ) : ?>
			<?php echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'nexora' ) ) ); ?>
		<?php else : ?>
			<?php
			/* translators: %s: shipping destination */
			echo wp_kses_post( apply_filters( 'woocommerce_cart_no_shipping_available_html', sprintf( esc_html__( 'No shipping options were found for %s.', 'nexora' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ), $formatted_destination ) );
			$calculator_text = esc_html__( 'Enter a different address', 'nexora' );
			?>
		<?php endif; ?>
		<?php if ( $show_package_details ) : ?>
			<p class="woocommerce-shipping-contents small text-muted"><?php echo esc_html( $package_details ); ?></p>
		<?php endif; ?>
		<?php if ( $show_shipping_calculator ) : ?>
			<?php woocommerce_shipping_calculator( $calculator_text ); ?>
		<?php endif; ?>
	</div>
</div>
