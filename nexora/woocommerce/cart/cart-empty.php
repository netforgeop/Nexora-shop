<?php
/**
 * Empty cart.
 *
 * @package Nexora
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
?>
<section class="section section--sm cart-page">
	<div class="container">
		<?php woocommerce_output_all_notices(); ?>
		<?php nexora_empty_state( array( 'icon' => 'cart-empty', 'title' => __( 'Your cart is empty', 'nexora' ), 'text' => __( 'Looks like you have not added anything yet. Browse the shop and find something you love.', 'nexora' ), 'cta' => __( 'Go to shop', 'nexora' ), 'href' => apply_filters( 'woocommerce_return_to_shop_redirect', nexora_shop_url() ) ) ); ?>
	</div>
</section>
