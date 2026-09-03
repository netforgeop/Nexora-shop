<?php
/**
 * WooCommerce AJAX endpoints (all nonce-checked via nexora_ajax_check()).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add to cart (simple & variation) — returns refreshed fragments.
 */
function nexora_ajax_add_to_cart() {
	nexora_ajax_check();
	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$variation    = array();
	if ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
		foreach ( wp_unslash( $_POST['variation'] ) as $k => $v ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$variation[ sanitize_title( $k ) ] = sanitize_text_field( $v );
		}
	}
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_purchasable() ) {
		wp_send_json_error( array( 'message' => __( 'This product cannot be purchased.', 'nexora' ) ) );
	}
	$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation );
	if ( ! $passed ) {
		wp_send_json_error( array( 'message' => nexora_collect_wc_notices() ) );
	}
	$key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
	if ( ! $key ) {
		wp_send_json_error( array( 'message' => nexora_collect_wc_notices() ) );
	}
	do_action( 'woocommerce_ajax_added_to_cart', $product_id );
	wc_clear_notices();
	wp_send_json_success(
		array(
			/* translators: %s: product name */
			'message'   => sprintf( __( '"%s" was added to your cart.', 'nexora' ), $product->get_name() ),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'count'     => WC()->cart->get_cart_contents_count(),
		)
	);
}
add_action( 'wp_ajax_nexora_add_to_cart', 'nexora_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_nexora_add_to_cart', 'nexora_ajax_add_to_cart' );

/**
 * Update / remove a cart line from the mini-cart or cart drawer.
 */
function nexora_ajax_cart_update() {
	nexora_ajax_check();
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! $key || ! WC()->cart->get_cart_item( $key ) ) {
		wp_send_json_error( array( 'message' => __( 'Item not found in cart.', 'nexora' ) ) );
	}
	if ( $qty <= 0 ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		WC()->cart->set_quantity( $key, $qty, true );
	}
	WC()->cart->calculate_totals();
	wc_clear_notices();
	wp_send_json_success(
		array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'count'     => WC()->cart->get_cart_contents_count(),
		)
	);
}
add_action( 'wp_ajax_nexora_cart_update', 'nexora_ajax_cart_update' );
add_action( 'wp_ajax_nopriv_nexora_cart_update', 'nexora_ajax_cart_update' );

/**
 * Quick view modal content.
 */
function nexora_ajax_quick_view() {
	nexora_ajax_check();
	$id      = isset( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : 0;
	$product = wc_get_product( $id );
	if ( ! $product || 'publish' !== $product->get_status() && ! current_user_can( 'edit_products' ) ) {
		wp_send_json_error( array( 'message' => __( 'Product not found.', 'nexora' ) ) );
	}
	$GLOBALS['product'] = $product;
	$GLOBALS['post']    = get_post( $id );
	setup_postdata( $GLOBALS['post'] );
	ob_start();
	get_template_part( 'template-parts/products/quick-view', null, array( 'product' => $product ) );
	$html = ob_get_clean();
	wp_reset_postdata();
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_nexora_quick_view', 'nexora_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_nexora_quick_view', 'nexora_ajax_quick_view' );

/**
 * Sync wishlist / compare for logged-in users.
 */
function nexora_ajax_list_sync() {
	nexora_ajax_check();
	$type = isset( $_POST['type'] ) && 'compare' === $_POST['type'] ? 'compare' : 'wishlist';
	if ( ! is_user_logged_in() ) {
		wp_send_json_success( array( 'ids' => array(), 'guest' => true ) );
	}
	$ids = nexora_user_list( $type );
	if ( isset( $_POST['ids'] ) ) {
		$incoming = array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$mode     = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'set';
		if ( 'merge' === $mode ) {
			$ids = array_merge( $ids, $incoming );
		} else {
			$ids = $incoming;
		}
		$ids = nexora_set_user_list( $type, $ids );
	}
	wp_send_json_success( array( 'ids' => $ids ) );
}
add_action( 'wp_ajax_nexora_list_sync', 'nexora_ajax_list_sync' );
add_action( 'wp_ajax_nopriv_nexora_list_sync', 'nexora_ajax_list_sync' );

/*
 * Shop filtering is done by fetching the filtered archive URL itself (same query, same
 * templates) and swapping the results region in JS — no separate endpoint to secure/duplicate.
 */

/**
 * Collapse WooCommerce notices into a plain message.
 *
 * @return string
 */
function nexora_collect_wc_notices() {
	$msgs = array();
	foreach ( wc_get_notices() as $type => $list ) {
		foreach ( $list as $n ) {
			$msgs[] = wp_strip_all_tags( is_array( $n ) ? $n['notice'] : $n );
		}
	}
	wc_clear_notices();
	return $msgs ? implode( ' ', $msgs ) : __( 'Something went wrong. Please try again.', 'nexora' );
}

/**
 * Reorder: add all purchasable items of one of the customer's own orders to the cart.
 */
function nexora_ajax_reorder() {
	nexora_ajax_check();
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Please log in first.', 'nexora' ) ), 403 );
	}
	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$order    = wc_get_order( $order_id );
	if ( ! $order || (int) $order->get_customer_id() !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => __( 'Order not found.', 'nexora' ) ), 404 );
	}
	$added = 0;
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			continue;
		}
		$variation_id = $item->get_variation_id();
		$attributes   = array();
		if ( $variation_id && $product->is_type( 'variation' ) ) {
			$attributes = $product->get_variation_attributes();
		}
		if ( WC()->cart->add_to_cart( $item->get_product_id(), $item->get_quantity(), $variation_id, $attributes ) ) {
			$added++;
		}
	}
	wc_clear_notices();
	if ( ! $added ) {
		wp_send_json_error( array( 'message' => __( 'None of the items are available right now.', 'nexora' ) ) );
	}
	wp_send_json_success(
		array(
			/* translators: %s: number of items */
			'message'   => sprintf( _n( '%s item added to your cart.', '%s items added to your cart.', $added, 'nexora' ), nexora_num( $added ) ),
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		)
	);
}
add_action( 'wp_ajax_nexora_reorder', 'nexora_ajax_reorder' );
