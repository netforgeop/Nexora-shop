<?php
/**
 * Front-end performance tweaks (all opt-out via Nexora → Settings → Performance).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_perf_init() {
	if ( is_admin() ) {
		return;
	}

	if ( nexora_option( 'performance', 'disable_emoji' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
	}

	// Housekeeping: things a storefront never needs in <head>.
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'nexora_perf_init' );

/**
 * Drop block library CSS on pages that contain no blocks.
 */
function nexora_perf_block_css() {
	if ( ! nexora_option( 'performance', 'disable_block_css' ) || is_admin() ) {
		return;
	}
	$has_blocks = is_singular() && has_blocks( get_post() );
	if ( ! $has_blocks && ! ( class_exists( 'WooCommerce' ) && ( is_cart() || is_checkout() ) ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'wc-blocks-style' );
	}
}
add_action( 'wp_enqueue_scripts', 'nexora_perf_block_css', 100 );

/**
 * Load WooCommerce front-end assets only where they are needed.
 */
function nexora_perf_woo_assets() {
	if ( ! class_exists( 'WooCommerce' ) || ! nexora_option( 'performance', 'woo_assets_scoped' ) ) {
		return;
	}
	if ( nexora_is_woo_page() || is_singular( 'product' ) ) {
		return;
	}
	// Keep the cart-fragments script (mini cart) but drop heavy styles/scripts elsewhere.
	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-inline', 'wc-blocks-style', 'select2', 'photoswipe', 'photoswipe-default-skin' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
	foreach ( array( 'wc-add-to-cart', 'wc-single-product', 'zoom', 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'selectWoo', 'wc-add-to-cart-variation' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'nexora_perf_woo_assets', 99 );

/**
 * Native lazy loading + async decoding for content images.
 */
function nexora_perf_lazy_attrs( $attr ) {
	if ( nexora_option( 'performance', 'lazy_images' ) && empty( $attr['loading'] ) && empty( $attr['fetchpriority'] ) ) {
		$attr['loading'] = 'lazy';
	}
	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'nexora_perf_lazy_attrs' );

/**
 * Add resource hints for local fonts (same origin) — nothing external is requested.
 */
add_filter(
	'wp_resource_hints',
	static function ( $urls, $relation ) {
		if ( 'dns-prefetch' === $relation ) {
			// WordPress adds s.w.org by default; not needed once emoji is off.
			$urls = array_diff( $urls, array( '//s.w.org' ) );
		}
		return $urls;
	},
	10,
	2
);
