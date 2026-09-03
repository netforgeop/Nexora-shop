<?php
/**
 * Front-end asset loading. Only what a page needs is enqueued.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request is a WooCommerce-related page.
 *
 * @return bool
 */
function nexora_is_woo_page() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page() || is_page_template( array( 'page-templates/wishlist.php', 'page-templates/compare.php' ) );
}

/**
 * Whether the page uses a carousel.
 *
 * @return bool
 */
function nexora_needs_swiper() {
	return is_front_page() || ( class_exists( 'WooCommerce' ) && ( is_product() || is_cart() ) ) || apply_filters( 'nexora_force_swiper', false );
}

function nexora_enqueue() {
	$ver = NEXORA_VERSION;
	$rtl = is_rtl();

	// Vendors (local, no CDN).
	wp_enqueue_style( 'nexora-bootstrap-grid', NEXORA_URI . 'assets/vendor/bootstrap/' . ( $rtl ? 'bootstrap-grid.rtl.min.css' : 'bootstrap-grid.min.css' ), array(), '5.3.3' );
	if ( nexora_needs_swiper() ) {
		wp_enqueue_style( 'swiper', NEXORA_URI . 'assets/vendor/swiper/swiper-bundle.min.css', array(), '11.1.0' );
		wp_enqueue_script( 'swiper', NEXORA_URI . 'assets/vendor/swiper/swiper-bundle.min.js', array(), '11.1.0', true );
	}
	if ( class_exists( 'WooCommerce' ) && is_product() ) {
		wp_enqueue_style( 'photoswipe', NEXORA_URI . 'assets/vendor/photoswipe/photoswipe.css', array(), '5.4.4' );
		wp_enqueue_script( 'nexora-photoswipe', NEXORA_URI . 'assets/vendor/photoswipe/photoswipe.iife.min.js', array(), '5.4.4', true );
	}

	// Design system (order matters).
	wp_enqueue_style( 'nexora-fonts', NEXORA_URI . 'assets/css/fonts.css', array(), $ver );
	wp_enqueue_style( 'nexora-tokens', NEXORA_URI . 'assets/css/tokens.css', array( 'nexora-fonts' ), $ver );
	wp_enqueue_style( 'nexora-icons', NEXORA_URI . 'assets/css/icons.css', array( 'nexora-tokens' ), $ver );
	wp_enqueue_style( 'nexora-base', NEXORA_URI . 'assets/css/base.css', array( 'nexora-icons' ), $ver );
	wp_enqueue_style( 'nexora-layout', NEXORA_URI . 'assets/css/layout.css', array( 'nexora-base' ), $ver );
	wp_enqueue_style( 'nexora-components', NEXORA_URI . 'assets/css/components.css', array( 'nexora-layout' ), $ver );
	wp_enqueue_style( 'nexora-pages', NEXORA_URI . 'assets/css/pages.css', array( 'nexora-components' ), $ver );
	wp_enqueue_style( 'nexora-theme', NEXORA_URI . 'assets/css/theme.css', array( 'nexora-pages' ), $ver );
	if ( is_singular() || is_home() || is_archive() || is_search() ) {
		wp_enqueue_style( 'nexora-wp', NEXORA_URI . 'assets/css/wp-overrides.css', array( 'nexora-theme' ), $ver );
	}
	if ( class_exists( 'WooCommerce' ) && nexora_is_woo_page() ) {
		wp_enqueue_style( 'nexora-woocommerce', NEXORA_URI . 'assets/css/woocommerce.css', array( 'nexora-theme' ), $ver );
	}
	wp_add_inline_style( 'nexora-theme', nexora_preset_css() );

	// Runtime.
	wp_enqueue_script( 'nexora-app', NEXORA_URI . 'assets/js/app.js', array(), $ver, array( 'in_footer' => true, 'strategy' => nexora_option( 'performance', 'defer_js' ) ? 'defer' : false ) );
	wp_localize_script( 'nexora-app', 'NEXORA', nexora_js_config() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Never load dashicons / block library for logged-out visitors when not needed.
	if ( ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'nexora_enqueue', 20 );

/**
 * Configuration handed to the JS runtime.
 *
 * @return array
 */
function nexora_js_config() {
	$config = array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'nexora_front' ),
		'restUrl'      => esc_url_raw( rest_url() ),
		'homeUrl'      => home_url( '/' ),
		'lang'         => substr( get_locale(), 0, 2 ),
		'rtl'          => is_rtl(),
		'fa'           => nexora_is_fa(),
		'shopUrl'      => nexora_shop_url(),
		'wishlistUrl'  => nexora_wishlist_url(),
		'compareUrl'   => nexora_compare_url(),
		'compareLimit' => 4,
		'heroDelay'    => (int) nexora_option( 'home', 'hero_autoplay' ) * 1000,
		'sticky'       => (bool) nexora_option( 'header', 'sticky' ),
		'woo'          => class_exists( 'WooCommerce' ),
		'loggedIn'     => is_user_logged_in(),
		'i18n'         => array(
			'close'           => __( 'Close', 'nexora' ),
			'undo'            => __( 'Undo', 'nexora' ),
			'addedToCart'     => __( 'Added to cart', 'nexora' ),
			'viewCart'        => __( 'View cart', 'nexora' ),
			'cartError'       => __( 'Could not add to cart.', 'nexora' ),
			'addedToWishlist' => __( 'Added to wishlist', 'nexora' ),
			'removedFromWishlist' => __( 'Removed from wishlist', 'nexora' ),
			'wishlist'        => __( 'Wishlist', 'nexora' ),
			'addedToCompare'  => __( 'Added to compare', 'nexora' ),
			'removedFromCompare' => __( 'Removed from compare', 'nexora' ),
			'compareLimit'    => __( 'You can compare up to 4 products.', 'nexora' ),
			'compareNow'      => __( 'Compare now', 'nexora' ),
			'compareCount'    => __( '%s products selected', 'nexora' ),
			'copied'          => __( 'Link copied', 'nexora' ),
			'loading'         => __( 'Loading…', 'nexora' ),
			'selectOptions'   => __( 'Please select the product options first.', 'nexora' ),
			'newsletterOk'    => __( 'You are subscribed. Thank you!', 'nexora' ),
			'newsletterErr'   => __( 'Please enter a valid email.', 'nexora' ),
			'noSuggestion'    => __( 'No matching products', 'nexora' ),
			'suggested'       => __( 'Suggested products', 'nexora' ),
			'popular'         => __( 'Popular searches', 'nexora' ),
			'searchFor'       => __( 'Search results for', 'nexora' ),
			'page'            => __( 'Page', 'nexora' ),
			'hours'           => __( 'hours', 'nexora' ),
			'minutes'         => __( 'minutes', 'nexora' ),
			'seconds'         => __( 'seconds', 'nexora' ),
			'maxQty'          => __( 'Maximum quantity is %s', 'nexora' ),
			'remove'          => __( 'Remove', 'nexora' ),
			'popularTerms'    => array_filter( array_map( 'trim', explode( ',', (string) get_theme_mod( 'nexora_popular_searches', '' ) ) ) ),
		),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$config['cartUrl']     = wc_get_cart_url();
		$config['checkoutUrl'] = wc_get_checkout_url();
		$config['currency']    = array(
			'symbol'   => html_entity_decode( get_woocommerce_currency_symbol() ),
			'position' => get_option( 'woocommerce_currency_pos' ),
			'decimals' => wc_get_price_decimals(),
			'thousand' => wc_get_price_thousand_separator(),
			'decimal'  => wc_get_price_decimal_separator(),
		);
	}
	return apply_filters( 'nexora_js_config', $config );
}

/**
 * Preload the primary fonts (LCP).
 */
function nexora_preload_fonts() {
	if ( ! nexora_option( 'performance', 'preload_fonts' ) ) {
		return;
	}
	$font = nexora_is_fa() ? 'iran-yekan-400.woff2' : 'inter-latin-400-normal.woff2';
	echo '<link rel="preload" href="' . esc_url( NEXORA_URI . 'assets/fonts/' . $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<link rel="preload" href="' . esc_url( NEXORA_URI . 'assets/fonts/linearicons-subset.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'nexora_preload_fonts', 2 );

/**
 * Apply persisted preferences before first paint (announcement dismiss) + theme-color meta.
 */
function nexora_head_inline() {
	$preset = nexora_active_preset();
	echo '<meta name="theme-color" content="' . esc_attr( $preset['colors']['primary'] ) . '">' . "\n";
	echo "<script>(function(){try{if(localStorage.getItem('nx:announcement')==='dismissed'){document.documentElement.classList.add('no-announcement');}}catch(e){}})();</script>\n";
}
add_action( 'wp_head', 'nexora_head_inline', 1 );

/**
 * Favicon fallback from bundled icons when no Site Icon is set.
 */
function nexora_favicon_fallback() {
	if ( has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( NEXORA_URI . 'assets/icons/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( NEXORA_URI . 'assets/icons/apple-touch-icon.png' ) . '">' . "\n";
}
add_action( 'wp_head', 'nexora_favicon_fallback', 3 );
