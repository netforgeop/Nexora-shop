<?php
/**
 * Checkout form.
 *
 * @package Nexora
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo '<section class="section section--sm"><div class="container">';
	nexora_empty_state( array( 'icon' => 'lock', 'title' => __( 'Please log in to checkout', 'nexora' ), 'text' => apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'nexora' ) ), 'cta' => __( 'Log in', 'nexora' ), 'href' => wc_get_page_permalink( 'myaccount' ) ) );
	echo '</div></section>';
	return;
}
?>
<section class="section section--sm checkout-page" data-checkout-page>
	<div class="container">
		<h1 class="visually-hidden"><?php esc_html_e( 'Checkout', 'nexora' ); ?></h1>
		<ol class="steps" aria-label="<?php esc_attr_e( 'Checkout steps', 'nexora' ); ?>">
			<li class="steps__item is-done"><span class="steps__num"><?php nexora_the_icon( 'check', 'xs' ); ?></span><span class="steps__label"><?php esc_html_e( 'Cart', 'nexora' ); ?></span></li>
			<li class="steps__line is-done" aria-hidden="true"></li>
			<li class="steps__item is-active" aria-current="step"><span class="steps__num"><?php echo esc_html( nexora_num( 2 ) ); ?></span><span class="steps__label"><?php esc_html_e( 'Details & shipping', 'nexora' ); ?></span></li>
			<li class="steps__line" aria-hidden="true"></li>
			<li class="steps__item"><span class="steps__num"><?php echo esc_html( nexora_num( 3 ) ); ?></span><span class="steps__label"><?php esc_html_e( 'Payment', 'nexora' ); ?></span></li>
			<li class="steps__line" aria-hidden="true"></li>
			<li class="steps__item"><span class="steps__num"><?php echo esc_html( nexora_num( 4 ) ); ?></span><span class="steps__label"><?php esc_html_e( 'Done', 'nexora' ); ?></span></li>
		</ol>
		<?php woocommerce_output_all_notices(); ?>
		<?php if ( ! is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) : ?>
			<div class="alert alert--info checkout-login-reminder"><?php nexora_the_icon( 'user', 'sm' ); ?><span><?php esc_html_e( 'Already have an account?', 'nexora' ); ?> <a class="link" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'Log in for faster checkout', 'nexora' ); ?></a></span></div>
		<?php endif; ?>
		<?php if ( wc_coupons_enabled() ) : ?>
			<div class="checkout-coupon" data-checkout-coupon>
				<span class="small text-muted"><?php esc_html_e( 'Have a coupon?', 'nexora' ); ?></span>
				<a href="#" class="showcoupon link small"><?php esc_html_e( 'Click here to enter your code', 'nexora' ); ?></a>
				<form class="checkout_coupon woocommerce-form-coupon coupon" method="post" style="display:none">
					<div class="input-icon coupon__field"><?php nexora_the_icon( 'tag', 'sm' ); ?><input type="text" name="coupon_code" class="form-control input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'nexora' ); ?>" id="coupon_code" value="" dir="ltr" /></div>
					<button type="submit" class="btn btn--dark btn--sm" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'nexora' ); ?>"><?php esc_html_e( 'Apply', 'nexora' ); ?></button>
				</form>
			</div>
		<?php endif; ?>

		<form name="checkout" method="post" class="checkout woocommerce-checkout cart-layout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'Checkout', 'nexora' ); ?>">
			<div>
				<?php if ( $checkout->get_checkout_fields() ) : ?>
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
					<div id="customer_details">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
				<?php endif; ?>
				<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
					<section class="checkout-section" aria-labelledby="co-ship">
						<div class="checkout-section__head"><h2 class="checkout-section__title" id="co-ship"><span class="step-badge"><?php echo esc_html( nexora_num( 3 ) ); ?></span><?php esc_html_e( 'Shipping method', 'nexora' ); ?></h2></div>
						<div class="checkout-shipping-methods" data-checkout-shipping>
							<?php
							/**
							 * Shipping rates are part of the review-order fragment so they refresh via AJAX;
							 * this wrapper is filled from review-order.php (see .checkout-shipping-methods--live).
							 */
							?>
							<p class="small text-muted"><?php esc_html_e( 'Shipping options are shown in the order summary and update automatically when your address changes.', 'nexora' ); ?></p>
						</div>
					</section>
				<?php endif; ?>
			</div>
			<aside class="cart-layout__aside">
				<div class="summary" id="order_review_wrap">
					<div class="cluster cluster--between"><h2 class="summary__title summary__title--plain" id="order_review_heading"><?php esc_html_e( 'Your order', 'nexora' ); ?></h2><a class="checkout-section__edit" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Edit cart', 'nexora' ); ?></a></div>
					<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
					<div id="order_review" class="woocommerce-checkout-review-order">
						<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					</div>
					<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				</div>
			</aside>
		</form>
	</div>
</section>
<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
