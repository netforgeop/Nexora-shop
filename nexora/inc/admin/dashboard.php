<?php
/**
 * Theme Dashboard ("My Store").
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Store KPIs (WooCommerce).
 *
 * @return array
 */
function nexora_dashboard_kpis() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}
	$cached = get_transient( 'nexora_dash_kpis' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$since   = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
	$orders  = wc_get_orders( array( 'limit' => -1, 'return' => 'ids', 'date_created' => '>=' . $since, 'status' => array( 'completed', 'processing', 'on-hold' ) ) );
	$revenue = 0;
	foreach ( array_slice( $orders, 0, 500 ) as $oid ) {
		$o = wc_get_order( $oid );
		if ( $o ) {
			$revenue += (float) $o->get_total();
		}
	}
	$pending  = wc_get_orders( array( 'limit' => -1, 'return' => 'ids', 'status' => array( 'processing', 'on-hold' ) ) );
	$products = wp_count_posts( 'product' );
	$low      = 0;
	if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
		$low = count( wc_get_products( array( 'limit' => 50, 'return' => 'ids', 'stock_status' => 'outofstock' ) ) );
	}
	$kpis = array(
		array( 'icon' => 'cart', 'label' => __( 'Orders (30 days)', 'nexora' ), 'value' => nexora_num( count( $orders ) ), 'url' => admin_url( 'edit.php?post_type=shop_order' ) ),
		array( 'icon' => 'credit', 'label' => __( 'Revenue (30 days)', 'nexora' ), 'value' => wp_strip_all_tags( wc_price( $revenue ) ), 'url' => admin_url( 'admin.php?page=wc-reports' ) ),
		array( 'icon' => 'truck', 'label' => __( 'Orders to fulfil', 'nexora' ), 'value' => nexora_num( count( $pending ) ), 'url' => admin_url( 'edit.php?post_status=wc-processing&post_type=shop_order' ) ),
		array( 'icon' => 'tag', 'label' => __( 'Published products', 'nexora' ), 'value' => nexora_num( (int) $products->publish ), 'url' => admin_url( 'edit.php?post_type=product' ) ),
		array( 'icon' => 'alert', 'label' => __( 'Out of stock', 'nexora' ), 'value' => nexora_num( $low ), 'url' => admin_url( 'edit.php?post_type=product&stock_status=outofstock' ) ),
		array( 'icon' => 'users', 'label' => __( 'Customers', 'nexora' ), 'value' => nexora_num( (int) count_users()['avail_roles']['customer'] ?? 0 ), 'url' => admin_url( 'users.php?role=customer' ) ),
	);
	set_transient( 'nexora_dash_kpis', $kpis, 10 * MINUTE_IN_SECONDS );
	return $kpis;
}

/**
 * Render dashboard.
 */
function nexora_render_dashboard() {
	$totals   = nexora_health_totals();
	$plugins  = nexora_plugin_summary();
	$user     = wp_get_current_user();
	$wizard   = (bool) nexora_get_state( 'wizard_done' );
	$demo     = nexora_get_state( 'demo_imported' );

	nexora_admin_header( 'nexora', sprintf( /* translators: %s: user display name */ __( 'Welcome, %s', 'nexora' ), $user->display_name ), __( 'Everything about your store in one place: health, quick actions, and step-by-step help.', 'nexora' ) );

	// Hero / onboarding banner.
	if ( ! $wizard ) {
		echo '<div class="nx-hero"><div><h2>' . esc_html__( 'Finish setting up your store', 'nexora' ) . '</h2><p>' . esc_html__( 'The setup wizard walks you through logo, colours, homepage, WooCommerce and demo content in about five minutes. You can re-run it anytime.', 'nexora' ) . '</p></div><a class="button button-primary button-hero" href="' . esc_url( admin_url( 'admin.php?page=nexora-wizard' ) ) . '">' . nexora_admin_icon( 'wand' ) . esc_html__( 'Start setup wizard', 'nexora' ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	// Status strip.
	$state = $totals['bad'] ? 'bad' : ( $totals['warn'] ? 'warn' : 'ok' );
	$msg   = array(
		'bad'  => __( 'Some critical items need attention before you can sell.', 'nexora' ),
		'warn' => __( 'The store works, but a few recommended settings are missing.', 'nexora' ),
		'ok'   => __( 'All checks passed — your store is ready.', 'nexora' ),
	);
	echo '<div class="nx-strip nx-strip--' . esc_attr( $state ) . '">' . nexora_admin_icon( 'bad' === $state ? 'alert' : ( 'warn' === $state ? 'info' : 'check' ) ) . '<div><strong>' . esc_html( $msg[ $state ] ) . '</strong> <span class="nx-muted">' . esc_html( sprintf( /* translators: 1: ok 2: warn 3: bad */ __( '%1$d passed · %2$d recommended · %3$d critical', 'nexora' ), $totals['ok'], $totals['warn'], $totals['bad'] ) ) . '</span></div><a class="button" href="' . esc_url( admin_url( 'admin.php?page=nexora-status' ) ) . '">' . esc_html__( 'View details', 'nexora' ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// KPIs.
	$kpis = nexora_dashboard_kpis();
	if ( $kpis ) {
		echo '<div class="nx-kpis">';
		foreach ( $kpis as $k ) {
			printf( '<a class="nx-kpi" href="%1$s">%2$s<span class="nx-kpi__value">%3$s</span><span class="nx-kpi__label">%4$s</span></a>', esc_url( $k['url'] ), nexora_admin_icon( $k['icon'] ), esc_html( $k['value'] ), esc_html( $k['label'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}

	echo '<div class="nx-grid nx-grid--3">';

	// Quick links.
	$links = array(
		array( 'settings', __( 'Theme settings', 'nexora' ), admin_url( 'admin.php?page=nexora-settings' ) ),
		array( 'home', __( 'Homepage sections', 'nexora' ), admin_url( 'admin.php?page=nexora-settings&tab=home' ) ),
		array( 'palette', __( 'Colors & presets', 'nexora' ), admin_url( 'admin.php?page=nexora-presets' ) ),
		array( 'image', __( 'Logo & site identity', 'nexora' ), admin_url( 'customize.php?autofocus[section]=title_tagline' ) ),
		array( 'menu', __( 'Menus', 'nexora' ), admin_url( 'nav-menus.php' ) ),
		array( 'layout', __( 'Widgets', 'nexora' ), admin_url( 'widgets.php' ) ),
	);
	if ( class_exists( 'WooCommerce' ) ) {
		$links[] = array( 'tag', __( 'Add product', 'nexora' ), admin_url( 'post-new.php?post_type=product' ) );
		$links[] = array( 'cart', __( 'Orders', 'nexora' ), admin_url( 'edit.php?post_type=shop_order' ) );
		$links[] = array( 'credit', __( 'Payments', 'nexora' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		$links[] = array( 'truck', __( 'Shipping', 'nexora' ), admin_url( 'admin.php?page=wc-settings&tab=shipping' ) );
	}
	$links[] = array( 'pen', __( 'Write a post', 'nexora' ), admin_url( 'post-new.php' ) );
	$links[] = array( 'download', __( 'Demo import', 'nexora' ), admin_url( 'admin.php?page=nexora-demo' ) );
	ob_start();
	echo '<div class="nx-quicklinks">';
	foreach ( $links as $l ) {
		printf( '<a href="%1$s">%2$s<span>%3$s</span></a>', esc_url( $l[2] ), nexora_admin_icon( $l[0] ), esc_html( $l[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
	nexora_admin_card( __( 'Quick actions', 'nexora' ), ob_get_clean(), array( 'icon' => 'zap', 'class' => 'nx-span-2' ) );

	// Environment.
	$f = nexora_env_facts();
	ob_start();
	echo '<ul class="nx-facts">';
	$facts = array(
		array( __( 'Theme version', 'nexora' ), NEXORA_VERSION ),
		array( 'WordPress', $f['wp'] ),
		array( 'WooCommerce', $f['woo'] ? $f['woo'] : __( 'not active', 'nexora' ) ),
		array( 'ACF / SCF', $f['acf'] ? $f['acf'] : __( 'not active', 'nexora' ) ),
		array( 'PHP', $f['php'] ),
		array( __( 'Language', 'nexora' ), $f['locale'] . ( $f['rtl'] ? ' · RTL' : '' ) ),
		array( __( 'Color preset', 'nexora' ), nexora_active_preset()['name'] ),
		array( __( 'Demo content', 'nexora' ), $demo ? $demo : __( 'none', 'nexora' ) ),
	);
	foreach ( $facts as $fact ) {
		printf( '<li><span>%1$s</span><strong>%2$s</strong></li>', esc_html( $fact[0] ), esc_html( $fact[1] ) );
	}
	echo '</ul>';
	nexora_admin_card( __( 'At a glance', 'nexora' ), ob_get_clean(), array( 'icon' => 'info' ) );

	// Plugins.
	ob_start();
	echo '<p>' . ( $plugins['required_missing'] ? nexora_admin_pill( 'bad', sprintf( /* translators: %d: count */ _n( '%d required plugin missing', '%d required plugins missing', $plugins['required_missing'], 'nexora' ), $plugins['required_missing'] ) ) : nexora_admin_pill( 'ok', __( 'All required plugins active', 'nexora' ) ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p class="nx-muted">' . esc_html( sprintf( /* translators: %d: count */ __( '%d recommended plugins not yet installed.', 'nexora' ), $plugins['recommended_missing'] ) ) . '</p>';
	echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=nexora-plugins' ) ) . '">' . esc_html__( 'Open plugin manager', 'nexora' ) . '</a>';
	nexora_admin_card( __( 'Plugins', 'nexora' ), ob_get_clean(), array( 'icon' => 'plug' ) );

	// WooCommerce checks (compact).
	ob_start();
	$checks = nexora_wc_checks();
	echo '<ul class="nx-checklist">';
	foreach ( array_slice( $checks, 0, 6, true ) as $c ) {
		printf( '<li class="is-%1$s">%2$s<span>%3$s</span><em>%4$s</em></li>', esc_attr( $c[1] ), nexora_admin_icon( 'ok' === $c[1] ? 'check' : ( 'bad' === $c[1] ? 'x' : 'alert' ) ), esc_html( $c[0] ), esc_html( $c[2] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
	nexora_admin_card( __( 'WooCommerce readiness', 'nexora' ), ob_get_clean(), array( 'icon' => 'cart', 'action' => '<a href="' . esc_url( admin_url( 'admin.php?page=nexora-status' ) ) . '">' . esc_html__( 'All checks', 'nexora' ) . '</a>' ) );

	// Tutorials.
	ob_start();
	echo '<ul class="nx-tutlist">';
	foreach ( array_slice( nexora_tutorials(), 0, 5 ) as $t ) {
		printf( '<li><a href="%1$s">%2$s<span>%3$s</span><em>%4$s</em></a></li>', esc_url( admin_url( 'admin.php?page=nexora-tutorials&tutorial=' . $t['id'] ) ), nexora_admin_icon( 'book' ), esc_html( $t['title'] ), esc_html( nexora_tutorial_categories()[ $t['category'] ] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
	nexora_admin_card( __( 'Getting started guides', 'nexora' ), ob_get_clean(), array( 'icon' => 'book', 'action' => '<a href="' . esc_url( admin_url( 'admin.php?page=nexora-tutorials' ) ) . '">' . esc_html__( 'All tutorials', 'nexora' ) . '</a>' ) );

	// Tools.
	ob_start();
	echo '<form method="post" class="nx-tools">';
	wp_nonce_field( 'nexora_tools', '_nexora_nonce' );
	echo '<button class="button" name="nexora_tool" value="flush_cache">' . nexora_admin_icon( 'refresh' ) . esc_html__( 'Clear theme caches', 'nexora' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button class="button" name="nexora_tool" value="export_subscribers">' . nexora_admin_icon( 'download' ) . esc_html__( 'Export newsletter subscribers (CSV)', 'nexora' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button class="button" name="nexora_tool" value="export_options">' . nexora_admin_icon( 'file' ) . esc_html__( 'Export theme options (JSON)', 'nexora' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button class="button" name="nexora_tool" value="rerun_onboarding">' . nexora_admin_icon( 'play' ) . esc_html__( 'Replay onboarding tour', 'nexora' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<button class="button-link-delete" name="nexora_tool" value="reset_options" data-confirm>' . esc_html__( 'Reset all theme options', 'nexora' ) . '</button>';
	echo '</form><p class="nx-muted">' . esc_html__( 'Import options: paste the exported JSON on the System Status page.', 'nexora' ) . '</p>';
	nexora_admin_card( __( 'Tools', 'nexora' ), ob_get_clean(), array( 'icon' => 'settings' ) );

	echo '</div>';
	nexora_admin_footer();
}

/**
 * Tools handler.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_tool'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		nexora_admin_post_check( 'nexora_tools' );
		$tool = sanitize_key( $_POST['nexora_tool'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		switch ( $tool ) {
			case 'flush_cache':
				global $wpdb;
				$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nexora_%' OR option_name LIKE '_transient_timeout_nexora_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				wp_cache_flush();
				nexora_admin_flash( __( 'Theme caches cleared.', 'nexora' ) );
				break;
			case 'export_subscribers':
				$subs = get_option( 'nexora_subscribers', array() );
				nocache_headers();
				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=nexora-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );
				echo "\xEF\xBB\xBF"; // BOM for Excel.
				$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				fputcsv( $out, array( 'email', 'date', 'source' ) );
				foreach ( (array) $subs as $s ) {
					fputcsv( $out, array( $s['email'] ?? '', $s['date'] ?? '', $s['source'] ?? '' ) );
				}
				exit;
			case 'export_options':
				$data = array( 'version' => NEXORA_VERSION, 'options' => array(), 'presets' => get_option( 'nexora_presets', array() ), 'active_preset' => nexora_active_preset_id() );
				foreach ( array_keys( nexora_schema() ) as $g ) {
					$data['options'][ $g ] = nexora_options( $g );
				}
				nocache_headers();
				header( 'Content-Type: application/json; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=nexora-options-' . gmdate( 'Y-m-d' ) . '.json' );
				echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
				exit;
			case 'rerun_onboarding':
				delete_user_meta( get_current_user_id(), 'nexora_onboarding_done' );
				nexora_admin_flash( __( 'The onboarding tour will start now.', 'nexora' ), 'info' );
				break;
			case 'reset_options':
				foreach ( array_keys( nexora_schema() ) as $g ) {
					delete_option( 'nexora_' . $g );
				}
				nexora_admin_flash( __( 'All theme options were reset to defaults. Presets and pages were kept.', 'nexora' ), 'info' );
				break;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=nexora' ) );
		exit;
	}
);

/**
 * Small WP dashboard widget linking here.
 */
add_action(
	'wp_dashboard_setup',
	static function () {
		if ( ! current_user_can( nexora_admin_cap() ) ) {
			return;
		}
		wp_add_dashboard_widget(
			'nexora_widget',
			'Nexora — ' . ( nexora_is_fa() ? 'فروشگاه من' : __( 'My Store', 'nexora' ) ),
			static function () {
				$t = nexora_health_totals();
				echo '<p>' . esc_html( sprintf( /* translators: 1: ok 2: warn 3: bad */ __( 'Store health: %1$d passed, %2$d recommended, %3$d critical.', 'nexora' ), $t['ok'], $t['warn'], $t['bad'] ) ) . '</p>';
				echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=nexora' ) ) . '">' . esc_html__( 'Open theme dashboard', 'nexora' ) . '</a> <a class="button" href="' . esc_url( admin_url( 'admin.php?page=nexora-tutorials' ) ) . '">' . esc_html__( 'Tutorials', 'nexora' ) . '</a></p>';
			}
		);
	}
);
