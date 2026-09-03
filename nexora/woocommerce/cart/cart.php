<?php
/**
 * Cart page.
 *
 * @package Nexora
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
$nexora_count = WC()->cart->get_cart_contents_count();
?>
<section class="section section--sm cart-page" data-cart-page>
	<div class="container">
		<div class="page-head__inner page-head__inner--cart">
			<h1 class="h3"><?php nexora_the_icon( 'cart', 'md' ); ?> <?php esc_html_e( 'Shopping cart', 'nexora' ); ?> <span class="text-muted small fw-medium" data-cart-count-label><?php echo esc_html( sprintf( /* translators: %s: count */ _n( '(%s item)', '(%s items)', $nexora_count, 'nexora' ), nexora_num( $nexora_count ) ) ); ?></span></h1>
			<a class="btn btn--ghost btn--sm text-danger" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'empty-cart', '1', wc_get_cart_url() ), 'nexora_empty_cart' ) ); ?>" data-cart-clear><?php nexora_the_icon( 'trash2', 'xs' ); ?><?php esc_html_e( 'Empty cart', 'nexora' ); ?></a>
		</div>
		<?php woocommerce_output_all_notices(); ?>
		<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
			<?php do_action( 'woocommerce_before_cart_table' ); ?>
			<div class="cart-layout">
				<div>
					<?php
					$nexora_savings = (float) WC()->cart->get_discount_total();
					foreach ( WC()->cart->get_cart() as $nexora_ci ) {
						$nexora_pp = $nexora_ci['data'];
						if ( $nexora_pp && $nexora_pp->is_on_sale() && $nexora_pp->get_regular_price() ) {
							$nexora_savings += ( (float) $nexora_pp->get_regular_price() - (float) $nexora_pp->get_price() ) * $nexora_ci['quantity'];
						}
					}
					if ( $nexora_savings > 0 ) :
						?>
						<div class="alert alert--success" data-cart-savings><?php nexora_the_icon( 'percent', 'sm' ); ?><span><?php echo wp_kses_post( sprintf( /* translators: %s: amount */ __( 'You are saving %s on this order.', 'nexora' ), wc_price( $nexora_savings ) ) ); ?></span></div>
					<?php endif; ?>
					<div class="cart-table shop_table cart" data-cart-items>
						<?php do_action( 'woocommerce_before_cart_contents' ); ?>
						<?php
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
							if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								continue;
							}
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							$nexora_max        = $_product->get_max_purchase_quantity();
							?>
							<div class="cart-item woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
								<a class="cart-item__media" href="<?php echo esc_url( $product_permalink ); ?>" tabindex="-1" aria-hidden="true"><?php echo apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'nexora-thumb' ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
								<div class="cart-item__info">
									<h3 class="cart-item__title"><?php echo $product_permalink ? '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $_product->get_name() ) . '</a>' : wp_kses_post( $_product->get_name() ); ?></h3>
									<div class="cart-item__attrs">
										<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php if ( $_product->get_sku() ) : ?><span><?php esc_html_e( 'SKU:', 'nexora' ); ?> <span class="ltr"><?php echo esc_html( $_product->get_sku() ); ?></span></span><?php endif; ?>
									</div>
									<div class="cart-item__unit"><?php esc_html_e( 'Unit price:', 'nexora' ); ?> <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( $_product->is_on_sale() && $_product->get_regular_price() ) : ?> <s><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $_product, array( 'price' => $_product->get_regular_price() ) ) ) ); ?></s><?php endif; ?></div>
									<?php if ( $_product->managing_stock() && null !== $_product->get_stock_quantity() && $_product->get_stock_quantity() <= 3 ) : ?><div class="cart-item__stock text-danger"><?php echo esc_html( sprintf( /* translators: %s: qty */ __( 'Only %s left in stock', 'nexora' ), nexora_num( $_product->get_stock_quantity() ) ) ); ?></div><?php endif; ?>
									<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?><div class="cart-item__stock text-muted"><?php esc_html_e( 'Available on backorder', 'nexora' ); ?></div><?php endif; ?>
									<div class="cart-item__actions">
										<button type="button" data-action="cart-to-wishlist" data-key="<?php echo esc_attr( $cart_item_key ); ?>" data-id="<?php echo (int) $product_id; ?>"><?php nexora_the_icon( 'heart', 'xs' ); ?> <?php esc_html_e( 'Move to wishlist', 'nexora' ); ?></button>
									</div>
								</div>
								<div class="cart-item__mobile-row">
									<div class="cart-item__qty">
										<?php
										if ( $_product->is_sold_individually() ) {
											printf( '<span class="num">1</span><input type="hidden" name="cart[%s][qty]" value="1" />', esc_attr( $cart_item_key ) );
										} else {
											echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'woocommerce_cart_item_quantity',
												woocommerce_quantity_input(
													array(
														'input_name'   => "cart[{$cart_item_key}][qty]",
														'input_value'  => $cart_item['quantity'],
														'max_value'    => $nexora_max,
														'min_value'    => '0',
														'product_name' => $_product->get_name(),
														'classes'      => array( 'qty--sm' ),
													),
													$_product,
													false
												),
												$cart_item_key,
												$cart_item
											);
										}
										?>
									</div>
									<div class="cart-item__total"><div class="price"><span class="price__current"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></div></div>
								</div>
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a href="%s" class="cart-item__remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart-remove="%s">%s</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										/* translators: %s: product name */
										esc_attr( sprintf( __( 'Remove %s from cart', 'nexora' ), wp_strip_all_tags( $_product->get_name() ) ) ),
										esc_attr( $product_id ),
										esc_attr( $_product->get_sku() ),
										esc_attr( $cart_item_key ),
										nexora_icon( 'trash2', 'sm' )
									),
									$cart_item_key
								);
								?>
							</div>
						<?php endforeach; ?>
						<?php do_action( 'woocommerce_cart_contents' ); ?>
						<?php do_action( 'woocommerce_after_cart_contents' ); ?>
					</div>
					<div class="cart-tools">
						<?php if ( wc_coupons_enabled() && nexora_option( 'shop', 'cart_coupon' ) ) : ?>
							<div class="coupon">
								<label class="visually-hidden" for="coupon_code"><?php esc_html_e( 'Coupon code', 'nexora' ); ?></label>
								<div class="input-icon coupon__field"><?php nexora_the_icon( 'tag', 'sm' ); ?><input id="coupon_code" class="form-control input-text" type="text" name="coupon_code" value="" placeholder="<?php esc_attr_e( 'Enter coupon code', 'nexora' ); ?>" autocomplete="off" dir="ltr"></div>
								<button type="submit" class="btn btn--dark" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'nexora' ); ?>"><?php esc_html_e( 'Apply', 'nexora' ); ?></button>
								<?php do_action( 'woocommerce_cart_coupon' ); ?>
							</div>
						<?php endif; ?>
						<a class="btn btn--outline" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', nexora_shop_url() ) ); ?>"><?php nexora_the_icon( 'arrow-right', 'xs', 'icon--flip-ltr' ); ?><?php esc_html_e( 'Continue shopping', 'nexora' ); ?></a>
						<button type="submit" class="btn btn--ghost" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'nexora' ); ?>" data-cart-update><?php nexora_the_icon( 'sync', 'xs' ); ?><?php esc_html_e( 'Update cart', 'nexora' ); ?></button>
						<?php do_action( 'woocommerce_cart_actions' ); ?>
						<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</div>
					<?php
					$nexora_applied = WC()->cart->get_applied_coupons();
					if ( $nexora_applied ) :
						?>
						<p class="form-hint cart-coupons"><?php esc_html_e( 'Applied coupons:', 'nexora' ); ?>
							<?php foreach ( $nexora_applied as $nexora_code ) : ?>
								<a class="chip chip--removable" href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $nexora_code ), wc_get_cart_url() ) ); ?>"><span class="ltr"><?php echo esc_html( wc_format_coupon_code( $nexora_code ) ); ?></span><?php nexora_the_icon( 'cross', 'xs' ); ?></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</div>
				<aside class="cart-layout__aside">
					<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
					<div class="cart-collaterals">
						<?php
						/**
						 * Renders cart/cart-totals.php (theme override) inside .summary.
						 */
						do_action( 'woocommerce_cart_collaterals' );
						?>
					</div>
				</aside>
			</div>
			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>
	</div>
</section>
<?php do_action( 'woocommerce_after_cart' ); ?>
