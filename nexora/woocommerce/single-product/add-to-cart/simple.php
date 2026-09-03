<?php
/**
 * Simple product add to cart.
 *
 * @package Nexora
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}
if ( $product->is_in_stock() ) :
	do_action( 'woocommerce_before_add_to_cart_form' );
	?>
	<form class="cart buy-box__form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" data-add-to-cart-form>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
		<div class="buy-box__row">
			<?php
			do_action( 'woocommerce_before_add_to_cart_quantity' );
			woocommerce_quantity_input(
				array(
					'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
					'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
					'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing
				)
			);
			do_action( 'woocommerce_after_add_to_cart_quantity' );
			?>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button btn btn--primary btn--lg" data-add-to-cart-submit><?php nexora_the_icon( 'cart-add', 'sm' ); ?><span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span></button>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="btn btn--dark btn--lg" data-buy-now formaction="<?php echo esc_url( add_query_arg( 'nexora_buy_now', '1', $product->get_permalink() ) ); ?>"><?php nexora_the_icon( 'cash-dollar', 'sm' ); ?><span><?php esc_html_e( 'Buy now', 'nexora' ); ?></span></button>
		</div>
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>
	<?php
	do_action( 'woocommerce_after_add_to_cart_form' );
else :
	?>
	<div class="buy-box__row">
		<a class="btn btn--outline btn--lg" href="#tab-reviews" data-action="notify" data-id="<?php echo (int) $product->get_id(); ?>"><?php nexora_the_icon( 'alarm', 'sm' ); ?><span><?php esc_html_e( 'Notify me when available', 'nexora' ); ?></span></a>
	</div>
	<?php
endif;
