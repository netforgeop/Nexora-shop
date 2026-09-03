<?php
/**
 * WooCommerce support declaration & global tweaks.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_wc_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 640,
			'single_image_width'    => 1280,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'max_rows'        => 12,
				'default_columns' => (int) nexora_option( 'shop', 'columns', 4 ),
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-lightbox' );
	// We use Swiper + PhotoSwipe 5 ourselves; drop the legacy zoom/slider scripts.
	remove_theme_support( 'wc-product-gallery-zoom' );
	remove_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'nexora_wc_setup' );

/**
 * Declare HPOS / cart-checkout blocks compatibility.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', NEXORA_DIR . 'functions.php', true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', NEXORA_DIR . 'functions.php', false );
		}
	}
);

/**
 * Products per page from theme options.
 */
add_filter( 'loop_shop_per_page', static function () { return (int) nexora_option( 'shop', 'per_page', 12 ); }, 20 );
add_filter( 'loop_shop_columns', static function () { return (int) nexora_option( 'shop', 'columns', 4 ); }, 20 );

/**
 * Disable WooCommerce's default stylesheets; the theme ships its own design system.
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Mini-cart fragments: header badge + total + mobile bar badge.
 */
function nexora_wc_fragments( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	ob_start();
	get_template_part( 'template-parts/header/mini-cart' );
	$fragments['div.mini-cart']                 = ob_get_clean();
	$fragments['span[data-count="cart"]']       = '<span class="icon-btn__badge" data-count="cart">' . esc_html( nexora_num( $count ) ) . '</span>';
	$fragments['span[data-cart-total]']         = '<span class="header-action__value" data-cart-total>' . ( $count ? wp_kses_post( WC()->cart->get_cart_subtotal() ) : esc_html__( 'Empty', 'nexora' ) ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'nexora_wc_fragments' );

/**
 * Prices: Persian digits on Persian sites.
 */
add_filter(
	'wc_price',
	static function ( $return ) {
		return nexora_is_fa() ? nexora_num( $return ) : $return;
	}
);

/**
 * Sort options: rename + add "discount".
 */
function nexora_wc_orderby( $options ) {
	$options = array(
		'popularity' => __( 'Most popular', 'nexora' ),
		'date'       => __( 'Newest', 'nexora' ),
		'rating'     => __( 'Top rated', 'nexora' ),
		'price'      => __( 'Price: low to high', 'nexora' ),
		'price-desc' => __( 'Price: high to low', 'nexora' ),
		'discount'   => __( 'Biggest discount', 'nexora' ),
		'menu_order' => __( 'Default', 'nexora' ),
	);
	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'nexora_wc_orderby' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'nexora_wc_orderby' );

function nexora_wc_orderby_discount( $args ) {
	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'discount' === $orderby ) {
		// Sale products first, cheapest sale last modified first. Simple, index-friendly.
		$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		$args['orderby']  = 'post__in modified';
	}
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'nexora_wc_orderby_discount' );

/**
 * Query flags used by the theme's filters (?on_sale=1, ?in_stock=1, ?rating=4).
 */
function nexora_wc_filter_query( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) ) ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$meta = (array) $q->get( 'meta_query' );
	$tax  = (array) $q->get( 'tax_query' );
	$list = static function ( $v ) {
		$v = is_array( $v ) ? $v : explode( ',', (string) $v );
		return array_filter( array_map( 'sanitize_title', array_map( 'sanitize_text_field', array_map( 'wp_unslash', $v ) ) ) );
	};
	if ( ! empty( $_GET['product_cat'] ) && is_array( $_GET['product_cat'] ) ) {
		// Checkbox filter posts product_cat[]; WooCommerce only understands the comma string.
		$tax[] = array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $list( $_GET['product_cat'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	}
	if ( ! empty( $_GET['on_sale'] ) ) {
		$q->set( 'post__in', array_merge( array( 0 ), wc_get_product_ids_on_sale() ) );
	}
	if ( ! empty( $_GET['in_stock'] ) ) {
		$meta[] = array( 'key' => '_stock_status', 'value' => 'instock' );
	}
	if ( ! empty( $_GET['rating'] ) ) {
		$meta[] = array( 'key' => '_wc_average_rating', 'value' => (float) $_GET['rating'], 'compare' => '>=', 'type' => 'DECIMAL' );
	}
	if ( ! empty( $_GET['brand'] ) ) {
		$brand_tax = taxonomy_exists( 'product_brand' ) ? 'product_brand' : 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_brand_attr', 'brand' ) );
		if ( taxonomy_exists( $brand_tax ) ) {
			$tax[] = array( 'taxonomy' => $brand_tax, 'field' => 'slug', 'terms' => $list( $_GET['brand'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
	}
	if ( ! empty( $_GET['color'] ) ) {
		$color_tax = 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) );
		if ( taxonomy_exists( $color_tax ) ) {
			$tax[] = array( 'taxonomy' => $color_tax, 'field' => 'slug', 'terms' => $list( $_GET['color'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}
	}
	// phpcs:enable
	if ( $meta ) {
		$q->set( 'meta_query', $meta ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	}
	if ( $tax ) {
		$q->set( 'tax_query', $tax ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	}
}
add_action( 'pre_get_posts', 'nexora_wc_filter_query' );

/**
 * Search: when searching from the header, restrict to products.
 */
add_action(
	'pre_get_posts',
	static function ( $q ) {
		if ( ! is_admin() && $q->is_main_query() && $q->is_search() && 'product' === $q->get( 'post_type' ) ) {
			$q->set( 'post_status', 'publish' );
		}
	}
);

/**
 * Body class helpers for WC pages.
 */
add_filter(
	'body_class',
	static function ( $classes ) {
		if ( is_shop() || is_product_taxonomy() ) {
			$classes[] = 'sidebar-' . nexora_option( 'shop', 'sidebar', 'start' );
		}
		return $classes;
	}
);

/**
 * Number of related products.
 */
add_filter(
	'woocommerce_output_related_products_args',
	static function ( $args ) {
		$args['posts_per_page'] = (int) nexora_option( 'product', 'related_count', 8 );
		$args['columns']        = 4;
		return $args;
	}
);
add_filter( 'woocommerce_upsell_display_args', static function ( $args ) { $args['posts_per_page'] = (int) nexora_option( 'product', 'upsells_count', 4 ); $args['columns'] = 4; return $args; } );

/**
 * Placeholder image from the theme when none is set.
 */
add_filter(
	'woocommerce_placeholder_img_src',
	static function ( $src ) {
		return $src ?: NEXORA_URI . 'assets/img/placeholder.svg';
	}
);
