<?php
/**
 * Wishlist & compare storage.
 *
 * Guests: localStorage (handled by JS). Logged-in users: user meta, synced via
 * AJAX so lists follow them across devices. No custom tables, no plugin needed.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a list for the current user.
 *
 * @param string $type wishlist|compare.
 * @return int[]
 */
function nexora_user_list( $type ) {
	if ( ! is_user_logged_in() ) {
		return array();
	}
	$list = get_user_meta( get_current_user_id(), '_nexora_' . sanitize_key( $type ), true );
	return is_array( $list ) ? array_values( array_map( 'absint', $list ) ) : array();
}

/**
 * Persist a list for the current user.
 *
 * @param string $type wishlist|compare.
 * @param int[]  $ids  Product ids.
 * @return int[]
 */
function nexora_set_user_list( $type, array $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	$ids = array_slice( $ids, 0, 'compare' === $type ? 4 : 200 );
	// Keep only real products.
	$ids = array_values( array_filter( $ids, static function ( $id ) { return 'product' === get_post_type( $id ); } ) );
	update_user_meta( get_current_user_id(), '_nexora_' . sanitize_key( $type ), $ids );
	return $ids;
}

/**
 * Wishlist / compare pages: expose the list to templates (ids come from JS for guests).
 *
 * @param string $type Type.
 * @return int[]
 */
function nexora_list_for_template( $type ) {
	if ( is_user_logged_in() ) {
		return nexora_user_list( $type );
	}
	// Guests: JS injects the ids via ?ids= after reading localStorage (progressive enhancement).
	$ids = isset( $_GET['ids'] ) ? array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['ids'] ) ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return array_slice( array_filter( $ids ), 0, 'compare' === $type ? 4 : 100 );
}

/**
 * Fields shown in the compare table.
 *
 * @param WC_Product $product Product.
 * @return array label => html
 */
function nexora_compare_rows( $product ) {
	$rows = array(
		__( 'Price', 'nexora' )        => nexora_price_html( $product ),
		__( 'Rating', 'nexora' )       => nexora_rating_html( $product->get_average_rating(), $product->get_review_count() ),
		__( 'Availability', 'nexora' ) => wc_get_stock_html( $product ) ?: ( $product->is_in_stock() ? '<span class="status status--success">' . esc_html__( 'In stock', 'nexora' ) . '</span>' : '<span class="status status--danger">' . esc_html__( 'Out of stock', 'nexora' ) . '</span>' ),
		__( 'SKU', 'nexora' )          => esc_html( $product->get_sku() ?: '—' ),
	);
	$brand = nexora_product_brand( $product );
	if ( $brand ) {
		$rows[ __( 'Brand', 'nexora' ) ] = esc_html( $brand[0] );
	}
	foreach ( nexora_product_specs( $product ) as $group ) {
		foreach ( $group['rows'] as $row ) {
			$rows[ $row[0] ] = esc_html( $row[1] );
		}
	}
	$rows[ __( 'Description', 'nexora' ) ] = wp_kses_post( wp_trim_words( $product->get_short_description(), 30 ) );
	return $rows;
}
