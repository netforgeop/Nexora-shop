<?php
/**
 * My Account: navigation icons, dashboard stats, login/register tweaks.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon per account endpoint.
 *
 * @param string $endpoint Endpoint.
 * @return string
 */
function nexora_account_icon( $endpoint ) {
	$map = array(
		'dashboard'       => 'home',
		'orders'          => 'cart',
		'downloads'       => 'download',
		'edit-address'    => 'map-marker',
		'payment-methods' => 'credit-card',
		'edit-account'    => 'user',
		'nexora-wishlist' => 'heart',
		'customer-logout' => 'exit',
	);
	return apply_filters( 'nexora_account_icon', $map[ $endpoint ] ?? 'chevron-left', $endpoint );
}

/**
 * Dashboard stats for the account overview cards.
 *
 * @return array
 */
function nexora_account_stats() {
	$user_id = get_current_user_id();
	$orders  = wc_get_orders( array( 'customer_id' => $user_id, 'limit' => -1, 'return' => 'ids' ) );
	$pending = wc_get_orders( array( 'customer_id' => $user_id, 'limit' => -1, 'return' => 'ids', 'status' => array( 'pending', 'processing', 'on-hold' ) ) );
	return array(
		array( 'icon' => 'cart', 'label' => __( 'Orders', 'nexora' ), 'value' => count( $orders ), 'url' => wc_get_account_endpoint_url( 'orders' ) ),
		array( 'icon' => 'clock', 'label' => __( 'In progress', 'nexora' ), 'value' => count( $pending ), 'url' => wc_get_account_endpoint_url( 'orders' ) ),
		array( 'icon' => 'heart', 'label' => __( 'Wishlist', 'nexora' ), 'value' => count( nexora_user_list( 'wishlist' ) ), 'url' => wc_get_account_endpoint_url( 'nexora-wishlist' ) ),
		array( 'icon' => 'star', 'label' => __( 'Reviews', 'nexora' ), 'value' => (int) get_comments( array( 'user_id' => $user_id, 'type' => 'review', 'count' => true ) ), 'url' => wc_get_account_endpoint_url( 'orders' ) ),
	);
}

/**
 * Human-friendly, coloured order status badge.
 *
 * @param WC_Order $order Order.
 */
function nexora_order_status_badge( $order ) {
	$status = $order->get_status();
	$type   = 'muted';
	if ( in_array( $status, array( 'completed' ), true ) ) {
		$type = 'success';
	} elseif ( in_array( $status, array( 'processing', 'on-hold', 'pending' ), true ) ) {
		$type = 'warning';
	} elseif ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
		$type = 'danger';
	}
	printf( '<span class="status status--%1$s">%2$s</span>', esc_attr( $type ), esc_html( wc_get_order_status_name( $status ) ) );
}

/**
 * Redirect to the account page after login when requested from the header.
 */
add_filter(
	'woocommerce_login_redirect',
	static function ( $redirect ) {
		if ( ! empty( $_POST['nexora_redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$target = esc_url_raw( wp_unslash( $_POST['nexora_redirect'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return wp_validate_redirect( $target, $redirect );
		}
		return $redirect;
	}
);

/**
 * Persian-friendly names for endpoints when the site language is fa.
 */
add_filter(
	'woocommerce_account_menu_items',
	static function ( $items ) {
		if ( isset( $items['customer-logout'] ) ) {
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
			$items['customer-logout'] = $logout;
		}
		return $items;
	},
	20
);

/**
 * Show login form on the checkout page for guests (theme styled).
 */
add_filter( 'woocommerce_checkout_show_login_form', '__return_false' );
