<?php
/**
 * Nexora theme bootstrap.
 *
 * Every feature lives in its own file under /inc so the theme stays modular.
 * Nothing here depends on a plugin: WooCommerce and ACF integrations are
 * loaded conditionally and the theme degrades gracefully without them.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

define( 'NEXORA_VERSION', '1.0.0' );
define( 'NEXORA_DIR', trailingslashit( get_template_directory() ) );
define( 'NEXORA_URI', trailingslashit( get_template_directory_uri() ) );
define( 'NEXORA_MIN_PHP', '8.0' );
define( 'NEXORA_MIN_WP', '6.4' );

/**
 * Compatibility guard: never white-screen an old server.
 */
if ( version_compare( PHP_VERSION, NEXORA_MIN_PHP, '<' ) || version_compare( get_bloginfo( 'version' ), NEXORA_MIN_WP, '<' ) ) {
	require_once NEXORA_DIR . 'inc/compat.php';
	return;
}

/**
 * Core (always loaded).
 */
require_once NEXORA_DIR . 'inc/helpers/schema.php';
require_once NEXORA_DIR . 'inc/helpers/options.php';
require_once NEXORA_DIR . 'inc/helpers/presets.php';
require_once NEXORA_DIR . 'inc/helpers/icons.php';
require_once NEXORA_DIR . 'inc/helpers/template-tags.php';
require_once NEXORA_DIR . 'inc/helpers/menus.php';
require_once NEXORA_DIR . 'inc/helpers/breadcrumb.php';
require_once NEXORA_DIR . 'inc/helpers/catalog.php';
require_once NEXORA_DIR . 'inc/security/sanitize.php';
require_once NEXORA_DIR . 'inc/setup.php';
require_once NEXORA_DIR . 'inc/enqueue.php';
require_once NEXORA_DIR . 'inc/customizer.php';
require_once NEXORA_DIR . 'inc/widgets.php';
require_once NEXORA_DIR . 'inc/ajax/front.php';
require_once NEXORA_DIR . 'inc/seo.php';
require_once NEXORA_DIR . 'inc/performance.php';

/**
 * WooCommerce (conditional).
 */
if ( class_exists( 'WooCommerce' ) ) {
	require_once NEXORA_DIR . 'inc/woocommerce/setup.php';
	require_once NEXORA_DIR . 'inc/woocommerce/hooks.php';
	require_once NEXORA_DIR . 'inc/woocommerce/product-card.php';
	require_once NEXORA_DIR . 'inc/woocommerce/single-product.php';
	require_once NEXORA_DIR . 'inc/woocommerce/wishlist.php';
	require_once NEXORA_DIR . 'inc/woocommerce/ajax.php';
	require_once NEXORA_DIR . 'inc/woocommerce/account.php';
}

/**
 * ACF (optional enhancement – extra per-product / per-page fields).
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	require_once NEXORA_DIR . 'inc/acf.php';
}

/**
 * Admin (only in the dashboard).
 */
if ( is_admin() ) {
	require_once NEXORA_DIR . 'inc/admin/core.php';
	require_once NEXORA_DIR . 'inc/admin/dashboard.php';
	require_once NEXORA_DIR . 'inc/admin/settings.php';
	require_once NEXORA_DIR . 'inc/admin/presets.php';
	require_once NEXORA_DIR . 'inc/admin/plugins.php';
	require_once NEXORA_DIR . 'inc/admin/system-status.php';
	require_once NEXORA_DIR . 'inc/admin/wizard.php';
	require_once NEXORA_DIR . 'inc/admin/tutorials.php';
	require_once NEXORA_DIR . 'inc/admin/demo-import.php';
	require_once NEXORA_DIR . 'inc/admin/activation.php';
	require_once NEXORA_DIR . 'inc/admin/onboarding.php';
	require_once NEXORA_DIR . 'inc/admin/help-boxes.php';
}
