<?php
/**
 * Active filter chips (server-rendered; JS keeps them in sync).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$nexora_g     = wp_unslash( $_GET );
$nexora_chips = array();
$nexora_map   = array(
	'product_cat' => array( __( 'Category', 'nexora' ), 'product_cat' ),
	'brand'       => array( __( 'Brand', 'nexora' ), taxonomy_exists( 'product_brand' ) ? 'product_brand' : 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_brand_attr', 'brand' ) ) ),
	'color'       => array( __( 'Colour', 'nexora' ), 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) ) ),
);
foreach ( $nexora_map as $nexora_k => $nexora_m ) {
	if ( empty( $nexora_g[ $nexora_k ] ) ) {
		continue;
	}
	$nexora_vals = is_array( $nexora_g[ $nexora_k ] ) ? $nexora_g[ $nexora_k ] : explode( ',', sanitize_text_field( $nexora_g[ $nexora_k ] ) );
	foreach ( $nexora_vals as $nexora_v ) {
		$nexora_t = taxonomy_exists( $nexora_m[1] ) ? get_term_by( 'slug', sanitize_title( $nexora_v ), $nexora_m[1] ) : null;
		$nexora_chips[] = array( $nexora_k, sanitize_title( $nexora_v ), $nexora_m[0] . ': ' . ( $nexora_t ? $nexora_t->name : $nexora_v ) );
	}
}
if ( isset( $nexora_g['min_price'] ) || isset( $nexora_g['max_price'] ) ) {
	$nexora_chips[] = array( 'price', '', __( 'Price', 'nexora' ) . ': ' . wp_strip_all_tags( wc_price( (float) ( $nexora_g['min_price'] ?? 0 ) ) ) . ' – ' . wp_strip_all_tags( wc_price( (float) ( $nexora_g['max_price'] ?? 0 ) ) ) );
}
if ( ! empty( $nexora_g['rating'] ) ) {
	/* translators: %s: stars */
	$nexora_chips[] = array( 'rating', '', sprintf( __( '%s stars and up', 'nexora' ), nexora_num( (int) $nexora_g['rating'] ) ) );
}
if ( ! empty( $nexora_g['in_stock'] ) ) {
	$nexora_chips[] = array( 'in_stock', '', __( 'In stock', 'nexora' ) );
}
if ( ! empty( $nexora_g['on_sale'] ) ) {
	$nexora_chips[] = array( 'on_sale', '', __( 'On sale', 'nexora' ) );
}
// phpcs:enable
if ( ! $nexora_chips ) {
	return;
}
foreach ( $nexora_chips as $nexora_chip ) {
	if ( 'price' === $nexora_chip[0] ) {
		$nexora_href = remove_query_arg( array( 'min_price', 'max_price', 'paged' ) );
	} elseif ( $nexora_chip[1] ) {
		$nexora_rest = array_diff( is_array( $nexora_g[ $nexora_chip[0] ] ) ? $nexora_g[ $nexora_chip[0] ] : explode( ',', $nexora_g[ $nexora_chip[0] ] ), array( $nexora_chip[1] ) );
		$nexora_href = $nexora_rest ? add_query_arg( array( $nexora_chip[0] => implode( ',', array_map( 'sanitize_title', $nexora_rest ) ), 'paged' => false ) ) : remove_query_arg( array( $nexora_chip[0], 'paged' ) );
	} else {
		$nexora_href = remove_query_arg( array( $nexora_chip[0], 'paged' ) );
	}
	echo '<a class="chip chip--removable" href="' . esc_url( $nexora_href ) . '" data-filter-remove="' . esc_attr( $nexora_chip[0] ) . '" data-value="' . esc_attr( $nexora_chip[1] ) . '">' . esc_html( $nexora_chip[2] ) . nexora_icon( 'cross', 'xs' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
echo '<a class="chip chip--ghost" href="' . esc_url( is_product_taxonomy() ? get_term_link( get_queried_object() ) : nexora_shop_url() ) . '" data-filters-clear>' . esc_html__( 'Clear all', 'nexora' ) . '</a>';
