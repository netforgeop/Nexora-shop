<?php
/**
 * Order received.
 *
 * @package Nexora
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--sm checkout-page woocommerce-order">
	<div class="container">
		<?php if ( $order ) : ?>
			<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>
			<?php if ( $order->has_status( 'failed' ) ) : ?>
				<div class="success-box success-box--failed card-surface">
					<span class="success-box__icon success-box__icon--danger"><?php nexora_the_icon( 'warning', 'xl' ); ?></span>
					<h1 class="h3"><?php esc_html_e( 'Payment failed', 'nexora' ); ?></h1>
					<p class="text-muted"><?php esc_html_e( 'Unfortunately your order cannot be processed as the payment was declined. Please attempt your purchase again.', 'nexora' ); ?></p>
					<div class="cluster">
						<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn--primary"><?php esc_html_e( 'Try again', 'nexora' ); ?></a>
						<?php if ( is_user_logged_in() ) : ?><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'My account', 'nexora' ); ?></a><?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<ol class="steps" aria-label="<?php esc_attr_e( 'Checkout steps', 'nexora' ); ?>">
					<li class="steps__item is-done"><span class="steps__num"><?php nexora_the_icon( 'check', 'xs' ); ?></span><span class="steps__label"><?php esc_html_e( 'Cart', 'nexora' ); ?></span></li><li class="steps__line is-done" aria-hidden="true"></li>
					<li class="steps__item is-done"><span class="steps__num"><?php nexora_the_icon( 'check', 'xs' ); ?></span><span class="steps__label"><?php esc_html_e( 'Details', 'nexora' ); ?></span></li><li class="steps__line is-done" aria-hidden="true"></li>
					<li class="steps__item is-done"><span class="steps__num"><?php nexora_the_icon( 'check', 'xs' ); ?></span><span class="steps__label"><?php esc_html_e( 'Payment', 'nexora' ); ?></span></li><li class="steps__line is-done" aria-hidden="true"></li>
					<li class="steps__item is-active" aria-current="step"><span class="steps__num"><?php echo esc_html( nexora_num( 4 ) ); ?></span><span class="steps__label"><?php esc_html_e( 'Done', 'nexora' ); ?></span></li>
				</ol>
				<div class="success-box card-surface">
					<span class="success-box__icon"><?php nexora_the_icon( 'checkmark-circle', 'xl' ); ?></span>
					<h1 class="h3"><?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'nexora' ), $order ) ); ?></h1>
					<p class="text-muted success-box__text"><?php echo $order->has_status( array( 'on-hold', 'pending' ) ) ? esc_html__( 'We will start processing it as soon as the payment is confirmed. A confirmation has been sent to your email.', 'nexora' ) : esc_html__( 'A confirmation has been sent to your email. You can track the order from your account.', 'nexora' ); ?></p>
					<div><span class="small text-muted"><?php esc_html_e( 'Order number:', 'nexora' ); ?></span> <span class="success-box__code ltr"><?php echo esc_html( $order->get_order_number() ); ?></span></div>
					<dl class="success-box__meta">
						<dt><?php esc_html_e( 'Date', 'nexora' ); ?></dt><dd><?php echo esc_html( nexora_num( wc_format_datetime( $order->get_date_created() ) ) ); ?></dd>
						<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?><dt><?php esc_html_e( 'Email', 'nexora' ); ?></dt><dd class="ltr"><?php echo esc_html( $order->get_billing_email() ); ?></dd><?php endif; ?>
						<dt><?php esc_html_e( 'Total', 'nexora' ); ?></dt><dd class="num"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd>
						<?php if ( $order->get_payment_method_title() ) : ?><dt><?php esc_html_e( 'Payment method', 'nexora' ); ?></dt><dd><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></dd><?php endif; ?>
					</dl>
					<div class="cluster">
						<?php if ( is_user_logged_in() ) : ?><a class="btn btn--primary" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'Track order', 'nexora' ); ?></a><?php endif; ?>
						<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'nexora' ); ?></a>
					</div>
				</div>
			<?php endif; ?>
			<div class="order-received-details">
				<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
				<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
			</div>
		<?php else : ?>
			<div class="success-box card-surface">
				<span class="success-box__icon"><?php nexora_the_icon( 'checkmark-circle', 'xl' ); ?></span>
				<h1 class="h3"><?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'nexora' ), null ) ); ?></h1>
				<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'nexora' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
