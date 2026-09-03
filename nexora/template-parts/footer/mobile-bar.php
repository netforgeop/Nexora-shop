<?php
/**
 * Bottom navigation bar (mobile).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

if ( ! nexora_option( 'header', 'mobile_bar', true ) ) {
	return;
}
$nexora_woo = class_exists( 'WooCommerce' );
?>
<nav class="mobile-bar" aria-label="<?php esc_attr_e( 'Quick access', 'nexora' ); ?>">
	<a class="mobile-bar__item<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php nexora_the_icon( 'home', 'sm' ); ?><span><?php esc_html_e( 'Home', 'nexora' ); ?></span></a>
	<?php if ( $nexora_woo ) : ?>
		<a class="mobile-bar__item<?php echo is_shop() || is_product_taxonomy() ? ' is-active' : ''; ?>" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'grid', 'sm' ); ?><span><?php esc_html_e( 'Shop', 'nexora' ); ?></span></a>
		<a class="mobile-bar__item<?php echo is_cart() ? ' is-active' : ''; ?>" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php nexora_the_icon( 'cart', 'sm' ); ?><span class="icon-btn__badge" data-count="cart"><?php echo esc_html( nexora_num( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?></span><span><?php esc_html_e( 'Cart', 'nexora' ); ?></span></a>
		<a class="mobile-bar__item<?php echo is_page( nexora_get_state( 'page_wishlist' ) ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( nexora_wishlist_url() ); ?>"><?php nexora_the_icon( 'heart', 'sm' ); ?><span class="icon-btn__badge" data-count="wishlist">0</span><span><?php esc_html_e( 'Wishlist', 'nexora' ); ?></span></a>
		<a class="mobile-bar__item<?php echo is_account_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" data-auth-link><?php nexora_the_icon( 'user', 'sm' ); ?><span><?php esc_html_e( 'Account', 'nexora' ); ?></span></a>
	<?php else : ?>
		<?php if ( get_option( 'page_for_posts' ) ) : ?>
			<a class="mobile-bar__item<?php echo is_home() ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php nexora_the_icon( 'book', 'sm' ); ?><span><?php esc_html_e( 'Blog', 'nexora' ); ?></span></a>
		<?php endif; ?>
		<button type="button" class="mobile-bar__item" data-drawer-open="drawer-menu"><?php nexora_the_icon( 'menu', 'sm' ); ?><span><?php esc_html_e( 'Menu', 'nexora' ); ?></span></button>
	<?php endif; ?>
</nav>
