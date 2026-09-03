<?php
/**
 * Re-wire WooCommerce template hooks to the theme's layout.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Wrappers ---------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/* ---------- Archive header/toolbar handled by archive-product.php ---------- */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

/* ---------- Product card: our content-product.php renders everything ---------- */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

/* ---------- Single product ---------- */
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

/* ---------- Cart / checkout ---------- */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'nexora_wc_cart_suggestions', 20 );

/**
 * "You may also like" carousel under the cart.
 */
function nexora_wc_cart_suggestions() {
	$cross = WC()->cart ? WC()->cart->get_cross_sells() : array();
	if ( empty( $cross ) ) {
		$cross = wc_get_products( array( 'limit' => 8, 'orderby' => 'popularity', 'status' => 'publish', 'return' => 'ids' ) );
	}
	if ( empty( $cross ) ) {
		return;
	}
	echo '<section class="section bg-surface" aria-labelledby="sec-cart-suggest"><div class="container">';
	nexora_section_head( array( 'title' => __( 'You may also like', 'nexora' ), 'id' => 'sec-cart-suggest', 'carousel' => 'suggest', 'reveal' => false ) );
	nexora_product_carousel( $cross, 'suggest' );
	echo '</div></section>';
}

/**
 * Notices wrapper with theme classes.
 */
add_filter(
	'woocommerce_add_message',
	static function ( $message ) {
		return $message;
	}
);

/**
 * Account menu: add wishlist endpoint label & icon mapping.
 */
add_filter(
	'woocommerce_account_menu_items',
	static function ( $items ) {
		$new = array();
		foreach ( $items as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'orders' === $key ) {
				$new['nexora-wishlist'] = __( 'Wishlist', 'nexora' );
			}
		}
		return $new;
	}
);

/**
 * Register the wishlist endpoint in My Account.
 */
add_action(
	'init',
	static function () {
		add_rewrite_endpoint( 'nexora-wishlist', EP_ROOT | EP_PAGES );
	}
);
add_filter(
	'woocommerce_get_query_vars',
	static function ( $vars ) {
		$vars['nexora-wishlist'] = 'nexora-wishlist';
		return $vars;
	}
);
add_action(
	'woocommerce_account_nexora-wishlist_endpoint',
	static function () {
		get_template_part( 'template-parts/products/wishlist-table' );
	}
);

/**
 * Checkout: field classes so WooCommerce forms match the design system.
 */
add_filter(
	'woocommerce_form_field_args',
	static function ( $args ) {
		$args['input_class'][] = 'form-control';
		$args['label_class'][] = 'form-label';
		$args['class'][]       = 'form-group';
		return $args;
	}
);

/**
 * Buttons.
 */
add_filter( 'woocommerce_product_single_add_to_cart_text', static function ( $text ) { return $text; } );

/**
 * Remove "Shop" heading duplication in archive titles.
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );

/**
 * Stock text with theme icon.
 */
add_filter(
	'woocommerce_get_availability_class',
	static function ( $class, $product ) {
		if ( ! $product->is_in_stock() ) {
			return 'stock stock--out';
		}
		if ( $product->managing_stock() && $product->get_stock_quantity() <= wc_get_low_stock_amount( $product ) ) {
			return 'stock stock--low';
		}
		return 'stock stock--in';
	},
	10,
	2
);

/**
 * Sale badge text: percentage for simple products.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function nexora_discount_percent( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}
	if ( $product->is_type( 'variable' ) ) {
		$max = 0;
		foreach ( $product->get_available_variations( 'objects' ) as $v ) {
			$r = (float) $v->get_regular_price();
			$s = (float) $v->get_sale_price();
			if ( $r > 0 && $s > 0 && $s < $r ) {
				$max = max( $max, round( ( $r - $s ) / $r * 100 ) );
			}
		}
		return (int) $max;
	}
	$r = (float) $product->get_regular_price();
	$s = (float) $product->get_sale_price();
	if ( $r > 0 && $s > 0 && $s < $r ) {
		return (int) round( ( $r - $s ) / $r * 100 );
	}
	return 0;
}

/**
 * Is the product "new"?
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function nexora_product_is_new( $product ) {
	$days = (int) nexora_option( 'shop', 'new_days', 14 );
	if ( $days <= 0 ) {
		return false;
	}
	$created = $product->get_date_created();
	return $created && ( time() - $created->getTimestamp() ) < $days * DAY_IN_SECONDS;
}

/**
 * Load the theme's WC template files from /woocommerce.
 */
add_filter( 'wc_get_template_part', static function ( $template, $slug, $name ) { return $template; }, 10, 3 );

/**
 * Empty-cart link on the cart page (nonce-protected).
 */
add_action(
	'template_redirect',
	static function () {
		if ( isset( $_GET['empty-cart'], $_GET['_wpnonce'] ) && is_cart() && wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'nexora_empty_cart' ) && WC()->cart ) {
			WC()->cart->empty_cart();
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	}
);

/**
 * Shipping rows in cart/checkout totals use the theme's option-card layout.
 */
add_filter( 'woocommerce_cart_shipping_method_full_label', static function ( $label ) { return $label; } );
