<?php
/**
 * Mini cart dropdown (also returned as a cart fragment).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
	return;
}
$nexora_items = WC()->cart->get_cart();
$nexora_count = WC()->cart->get_cart_contents_count();
?>
<div class="mini-cart" id="mini-cart" data-mini-cart aria-label="<?php esc_attr_e( 'Shopping cart', 'nexora' ); ?>">
	<div class="mini-cart__head"><span><?php esc_html_e( 'Shopping cart', 'nexora' ); ?></span><span class="text-muted small" data-mini-cart-count><?php echo $nexora_count ? esc_html( sprintf( /* translators: %s: count */ _n( '%s item', '%s items', $nexora_count, 'nexora' ), nexora_num( $nexora_count ) ) ) : ''; ?></span></div>
	<?php if ( $nexora_items ) : ?>
		<div class="mini-cart__list scroll-thin" data-mini-cart-list>
			<?php
			foreach ( $nexora_items as $nexora_key => $nexora_item ) :
				$nexora_p = $nexora_item['data'];
				if ( ! $nexora_p || ! $nexora_p->exists() || $nexora_item['quantity'] <= 0 ) {
					continue;
				}
				$nexora_link = $nexora_p->is_visible() ? $nexora_p->get_permalink( $nexora_item ) : '';
				?>
				<div class="mini-cart__item" data-cart-key="<?php echo esc_attr( $nexora_key ); ?>">
					<a class="mini-cart__thumb" href="<?php echo esc_url( $nexora_link ); ?>"><?php echo $nexora_p->get_image( 'nexora-thumb' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<div class="mini-cart__body">
						<a class="mini-cart__title truncate-2" href="<?php echo esc_url( $nexora_link ); ?>"><?php echo esc_html( $nexora_p->get_name() ); ?></a>
						<?php echo wc_get_formatted_cart_item_data( $nexora_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div class="mini-cart__meta">
							<div class="qty qty--sm" data-qty>
								<button type="button" class="qty__btn" data-qty-dec aria-label="<?php esc_attr_e( 'Decrease', 'nexora' ); ?>"><?php nexora_the_icon( 'minus', 'xs' ); ?></button>
								<input class="qty__input" type="number" inputmode="numeric" min="0" max="<?php echo esc_attr( $nexora_p->get_max_purchase_quantity() > 0 ? $nexora_p->get_max_purchase_quantity() : 99 ); ?>" value="<?php echo esc_attr( $nexora_item['quantity'] ); ?>" data-cart-qty="<?php echo esc_attr( $nexora_key ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'nexora' ); ?>">
								<button type="button" class="qty__btn" data-qty-inc aria-label="<?php esc_attr_e( 'Increase', 'nexora' ); ?>"><?php nexora_the_icon( 'plus', 'xs' ); ?></button>
							</div>
							<span class="mini-cart__price num"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $nexora_p, $nexora_item['quantity'] ) ); ?></span>
						</div>
					</div>
					<button type="button" class="icon-btn icon-btn--sm mini-cart__remove" data-cart-remove="<?php echo esc_attr( $nexora_key ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'nexora' ); ?>"><?php nexora_the_icon( 'trash2', 'xs' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="mini-cart__foot" data-mini-cart-foot>
			<?php
			$nexora_threshold = (float) nexora_option( 'shop', 'free_shipping_min', 0 );
			if ( $nexora_threshold > 0 ) :
				$nexora_sub = (float) WC()->cart->get_subtotal();
				$nexora_pct = min( 100, (int) round( $nexora_sub / $nexora_threshold * 100 ) );
				?>
				<div class="free-ship" data-free-ship>
					<p class="small"><?php echo $nexora_pct >= 100 ? esc_html__( 'You qualify for free shipping.', 'nexora' ) : wp_kses_post( sprintf( /* translators: %s: amount */ __( 'Add %s more for free shipping', 'nexora' ), wc_price( $nexora_threshold - $nexora_sub ) ) ); ?></p>
					<div class="progress"><span style="width:<?php echo esc_attr( $nexora_pct ); ?>%"></span></div>
				</div>
			<?php endif; ?>
			<div class="mini-cart__total"><span><?php esc_html_e( 'Subtotal', 'nexora' ); ?></span><span class="num" data-mini-cart-subtotal><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span></div>
			<div class="mini-cart__actions"><a class="btn btn--outline btn--sm" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'nexora' ); ?></a><a class="btn btn--primary btn--sm" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'nexora' ); ?></a></div>
		</div>
	<?php else : ?>
		<div class="mini-cart__empty" data-mini-cart-empty><?php nexora_the_icon( 'cart-empty', 'xl' ); ?><span><?php esc_html_e( 'Your cart is empty', 'nexora' ); ?></span><a class="btn btn--dark btn--sm" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php esc_html_e( 'Go to shop', 'nexora' ); ?></a></div>
	<?php endif; ?>
</div>
