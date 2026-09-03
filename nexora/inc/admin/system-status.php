<?php
/**
 * System status + WooCommerce configuration checker.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce configuration checks.
 *
 * @return array [ id => [label, state(ok|warn|bad|muted), detail, fix_url] ]
 */
function nexora_wc_checks() {
	$checks = array();
	if ( ! class_exists( 'WooCommerce' ) ) {
		$checks['woo'] = array( __( 'WooCommerce', 'nexora' ), 'bad', __( 'Not active — install and activate WooCommerce.', 'nexora' ), admin_url( 'admin.php?page=nexora-plugins' ) );
		return $checks;
	}
	$settings = admin_url( 'admin.php?page=wc-settings' );

	$currency         = get_woocommerce_currency();
	$checks['currency'] = array( __( 'Currency', 'nexora' ), $currency ? 'ok' : 'bad', $currency ? sprintf( '%s (%s)', $currency, get_woocommerce_currency_symbol() ) : __( 'Not set', 'nexora' ), $settings . '&tab=general' );

	$country  = WC()->countries->get_base_country();
	$city     = WC()->countries->get_base_city();
	$checks['address'] = array( __( 'Store address', 'nexora' ), $country && $city ? 'ok' : 'warn', $country ? WC()->countries->countries[ $country ] . ( $city ? ', ' . $city : '' ) : __( 'Not set', 'nexora' ), $settings . '&tab=general' );

	$zones     = class_exists( 'WC_Shipping_Zones' ) ? WC_Shipping_Zones::get_zones() : array();
	$has_ship  = false;
	foreach ( $zones as $z ) {
		if ( ! empty( $z['shipping_methods'] ) ) {
			$has_ship = true;
			break;
		}
	}
	if ( ! $has_ship ) {
		$rest = WC_Shipping_Zones::get_zone( 0 );
		$has_ship = $rest && count( $rest->get_shipping_methods( true ) ) > 0;
	}
	$checks['shipping'] = array( __( 'Shipping methods', 'nexora' ), $has_ship ? 'ok' : 'warn', $has_ship ? sprintf( /* translators: %d: zones */ _n( '%d zone configured', '%d zones configured', count( $zones ), 'nexora' ), count( $zones ) ) : __( 'No shipping method in any zone', 'nexora' ), $settings . '&tab=shipping' );

	$gateways = WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
	$names    = array_map( static function ( $g ) { return $g->get_title(); }, $gateways );
	$checks['payment'] = array( __( 'Payment gateways', 'nexora' ), $gateways ? 'ok' : 'bad', $gateways ? implode( ', ', $names ) : __( 'No enabled gateway — customers cannot pay', 'nexora' ), $settings . '&tab=checkout' );

	$pages = array( 'shop' => __( 'Shop', 'nexora' ), 'cart' => __( 'Cart', 'nexora' ), 'checkout' => __( 'Checkout', 'nexora' ), 'myaccount' => __( 'My Account', 'nexora' ) );
	$missing = array();
	foreach ( $pages as $k => $label ) {
		$id = wc_get_page_id( $k );
		if ( $id <= 0 || 'publish' !== get_post_status( $id ) ) {
			$missing[] = $label;
		}
	}
	$checks['pages'] = array( __( 'WooCommerce pages', 'nexora' ), $missing ? 'bad' : 'ok', $missing ? sprintf( /* translators: %s: page list */ __( 'Missing: %s', 'nexora' ), implode( ', ', $missing ) ) : __( 'All core pages exist', 'nexora' ), $settings . '&tab=advanced' );

	$terms = wc_terms_and_conditions_page_id();
	$checks['terms'] = array( __( 'Terms & conditions page', 'nexora' ), $terms ? 'ok' : 'warn', $terms ? get_the_title( $terms ) : __( 'Not set (recommended for legal compliance)', 'nexora' ), $settings . '&tab=advanced' );

	$count = (int) wp_count_posts( 'product' )->publish;
	$checks['products'] = array( __( 'Published products', 'nexora' ), $count ? 'ok' : 'warn', nexora_num( $count ), admin_url( 'edit.php?post_type=product' ) );

	$tax = wc_tax_enabled();
	$checks['tax'] = array( __( 'Taxes', 'nexora' ), 'muted', $tax ? __( 'Enabled', 'nexora' ) : __( 'Disabled', 'nexora' ), $settings . '&tab=general' );

	$guest = 'yes' === get_option( 'woocommerce_enable_guest_checkout' );
	$checks['guest'] = array( __( 'Guest checkout', 'nexora' ), 'muted', $guest ? __( 'Allowed', 'nexora' ) : __( 'Account required', 'nexora' ), $settings . '&tab=account' );

	$checks['ssl'] = array( __( 'HTTPS', 'nexora' ), is_ssl() ? 'ok' : 'bad', is_ssl() ? __( 'Secure connection active', 'nexora' ) : __( 'Store is not served over HTTPS — required for payments', 'nexora' ), admin_url( 'options-general.php' ) );

	return apply_filters( 'nexora_wc_checks', $checks );
}

/**
 * Theme configuration checks.
 *
 * @return array
 */
function nexora_theme_checks() {
	$c = array();
	$c['front'] = array( __( 'Homepage', 'nexora' ), 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ? 'ok' : 'warn', 'page' === get_option( 'show_on_front' ) ? get_the_title( get_option( 'page_on_front' ) ) : __( 'Set a static front page to use the Nexora homepage builder', 'nexora' ), admin_url( 'options-reading.php' ) );
	$c['logo']  = array( __( 'Logo', 'nexora' ), get_theme_mod( 'custom_logo' ) ? 'ok' : 'warn', get_theme_mod( 'custom_logo' ) ? __( 'Uploaded', 'nexora' ) : __( 'Using text logo', 'nexora' ), admin_url( 'customize.php?autofocus[section]=title_tagline' ) );
	$menus      = get_nav_menu_locations();
	$c['menu']  = array( __( 'Primary menu', 'nexora' ), ! empty( $menus['primary'] ) ? 'ok' : 'warn', ! empty( $menus['primary'] ) ? __( 'Assigned', 'nexora' ) : __( 'No menu assigned to "Primary"', 'nexora' ), admin_url( 'nav-menus.php?action=locations' ) );
	$c['permalinks'] = array( __( 'Permalinks', 'nexora' ), get_option( 'permalink_structure' ) ? 'ok' : 'bad', get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : __( 'Plain permalinks break product URLs', 'nexora' ), admin_url( 'options-permalink.php' ) );
	$c['preset'] = array( __( 'Active color preset', 'nexora' ), 'ok', nexora_active_preset()['name'], admin_url( 'admin.php?page=nexora-presets' ) );
	$c['wizard'] = array( __( 'Setup wizard', 'nexora' ), nexora_get_state( 'wizard_done' ) ? 'ok' : 'warn', nexora_get_state( 'wizard_done' ) ? __( 'Completed', 'nexora' ) : __( 'Not completed', 'nexora' ), admin_url( 'admin.php?page=nexora-wizard' ) );
	$c['wishlist'] = array( __( 'Wishlist & compare pages', 'nexora' ), nexora_get_state( 'page_wishlist' ) && nexora_get_state( 'page_compare' ) ? 'ok' : 'warn', nexora_get_state( 'page_wishlist' ) ? __( 'Created', 'nexora' ) : __( 'Run "Repair pages" below', 'nexora' ), '' );
	return apply_filters( 'nexora_theme_checks', $c );
}

/**
 * Server checks.
 *
 * @return array
 */
function nexora_server_checks() {
	$f = nexora_env_facts();
	$mem = wp_convert_hr_to_bytes( $f['memory'] );
	return array(
		'php'    => array( 'PHP', version_compare( $f['php'], '8.1', '>=' ) ? 'ok' : ( version_compare( $f['php'], NEXORA_MIN_PHP, '>=' ) ? 'warn' : 'bad' ), $f['php'], '' ),
		'wp'     => array( 'WordPress', version_compare( $f['wp'], NEXORA_MIN_WP, '>=' ) ? 'ok' : 'bad', $f['wp'], '' ),
		'mysql'  => array( 'MySQL / MariaDB', 'muted', $f['mysql'], '' ),
		'memory' => array( __( 'Memory limit', 'nexora' ), $mem >= 256 * MB_IN_BYTES ? 'ok' : 'warn', $f['memory'], '' ),
		'upload' => array( __( 'Max upload size', 'nexora' ), 'muted', $f['max_upload'], '' ),
		'time'   => array( __( 'Max execution time', 'nexora' ), $f['exec_time'] >= 60 || 0 === $f['exec_time'] ? 'ok' : 'warn', $f['exec_time'] . 's', '' ),
		'debug'  => array( 'WP_DEBUG', $f['debug'] ? 'warn' : 'ok', $f['debug'] ? __( 'Enabled — turn off in production', 'nexora' ) : __( 'Disabled', 'nexora' ), '' ),
		'gd'     => array( __( 'Image library', 'nexora' ), extension_loaded( 'gd' ) || extension_loaded( 'imagick' ) ? 'ok' : 'bad', extension_loaded( 'imagick' ) ? 'Imagick' : ( extension_loaded( 'gd' ) ? 'GD' : __( 'None', 'nexora' ) ), '' ),
		'mbstring' => array( 'mbstring', extension_loaded( 'mbstring' ) ? 'ok' : 'warn', extension_loaded( 'mbstring' ) ? __( 'Loaded', 'nexora' ) : __( 'Missing — Persian text functions may misbehave', 'nexora' ), '' ),
		'cron'   => array( 'WP-Cron', defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'muted' : 'ok', defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? __( 'Disabled (server cron expected)', 'nexora' ) : __( 'Enabled', 'nexora' ), '' ),
	);
}

/**
 * Overall score for the dashboard.
 *
 * @return array [ ok, warn, bad ]
 */
function nexora_health_totals() {
	$t = array( 'ok' => 0, 'warn' => 0, 'bad' => 0 );
	foreach ( array_merge( nexora_wc_checks(), nexora_theme_checks() ) as $c ) {
		if ( isset( $t[ $c[1] ] ) ) {
			$t[ $c[1] ]++;
		}
	}
	return $t;
}

/**
 * Render a check table.
 *
 * @param array $checks Checks.
 */
function nexora_render_check_table( array $checks ) {
	echo '<table class="nx-table nx-table--checks"><tbody>';
	foreach ( $checks as $c ) {
		echo '<tr><th scope="row">' . esc_html( $c[0] ) . '</th><td>' . nexora_admin_pill( $c[1], $c[2] ) . '</td><td class="nx-table__act">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! empty( $c[3] ) && 'ok' !== $c[1] ) {
			echo '<a class="button button-small" href="' . esc_url( $c[3] ) . '">' . esc_html__( 'Fix', 'nexora' ) . '</a>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Render page.
 */
function nexora_render_system_status() {
	nexora_admin_header( 'nexora-status', __( 'System Status', 'nexora' ), __( 'A single place to verify the store is ready for customers. Green = fine, amber = recommended, red = must fix.', 'nexora' ) );
	echo '<div class="nx-grid nx-grid--2">';
	ob_start();
	nexora_render_check_table( nexora_wc_checks() );
	nexora_admin_card( __( 'WooCommerce configuration', 'nexora' ), ob_get_clean(), array( 'icon' => 'cart' ) );
	ob_start();
	nexora_render_check_table( nexora_theme_checks() );
	echo '<form method="post" class="nx-inline-form">';
	wp_nonce_field( 'nexora_repair', '_nexora_nonce' );
	echo '<input type="hidden" name="nexora_action" value="repair"><button class="button">' . nexora_admin_icon( 'refresh' ) . esc_html__( 'Repair pages, menus & rewrite rules', 'nexora' ) . '</button></form>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	nexora_admin_card( __( 'Theme configuration', 'nexora' ), ob_get_clean(), array( 'icon' => 'settings' ) );
	ob_start();
	nexora_render_check_table( nexora_server_checks() );
	nexora_admin_card( __( 'Server environment', 'nexora' ), ob_get_clean(), array( 'icon' => 'activity' ) );

	ob_start();
	$f = nexora_env_facts();
	$report = array(
		'Theme'     => 'Nexora ' . NEXORA_VERSION . ( $f['child'] ? ' (child: ' . $f['child'] . ')' : '' ),
		'WordPress' => $f['wp'],
		'WooCommerce' => $f['woo'] ?: '-',
		'ACF/SCF'   => $f['acf'] ?: '-',
		'PHP'       => $f['php'],
		'MySQL'     => $f['mysql'],
		'Memory'    => $f['memory'],
		'Locale'    => $f['locale'] . ( $f['rtl'] ? ' (RTL)' : '' ),
		'SSL'       => $f['ssl'] ? 'yes' : 'no',
		'Debug'     => $f['debug'] ? 'on' : 'off',
		'Preset'    => nexora_active_preset_id(),
	);
	$active_plugins = array();
	foreach ( (array) get_option( 'active_plugins', array() ) as $p ) {
		$active_plugins[] = $p;
	}
	$text = "### Nexora system report\n";
	foreach ( $report as $k => $v ) {
		$text .= $k . ': ' . $v . "\n";
	}
	$text .= "Active plugins: " . implode( ', ', $active_plugins ) . "\n";
	echo '<p class="nx-muted">' . esc_html__( 'Copy this block when contacting support.', 'nexora' ) . '</p><textarea class="nx-report" readonly rows="10" data-select-all>' . esc_textarea( $text ) . '</textarea>';
	nexora_admin_card( __( 'Support report', 'nexora' ), ob_get_clean(), array( 'icon' => 'file' ) );
	echo '</div>';
	nexora_admin_footer();
}

/**
 * Handle "repair" action.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_action'] ) || 'repair' !== $_POST['nexora_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		nexora_admin_post_check( 'nexora_repair' );
		nexora_ensure_pages();
		nexora_ensure_menus();
		flush_rewrite_rules();
		nexora_admin_flash( __( 'Pages, menus and rewrite rules were repaired.', 'nexora' ) );
		wp_safe_redirect( admin_url( 'admin.php?page=nexora-status' ) );
		exit;
	}
);
