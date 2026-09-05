<?php
/**
 * Admin core: menu, capability, assets, shared UI helpers, notices.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Capability required for every theme admin screen / action.
 *
 * @return string
 */
function nexora_admin_cap() {
	return apply_filters( 'nexora_admin_cap', 'manage_options' );
}

/**
 * Registered admin pages: slug => [title, menu title, callback, order].
 *
 * @return array
 */
function nexora_admin_pages() {
	$pages = array(
		'nexora'           => array( __( 'Nexora Dashboard', 'nexora' ), __( 'Dashboard', 'nexora' ), 'nexora_render_dashboard' ),
		'nexora-settings'  => array( __( 'Theme Settings', 'nexora' ), __( 'Theme Settings', 'nexora' ), 'nexora_render_settings' ),
		'nexora-presets'   => array( __( 'Colors & Presets', 'nexora' ), __( 'Colors & Presets', 'nexora' ), 'nexora_render_presets' ),
		'nexora-plugins'   => array( __( 'Plugin Manager', 'nexora' ), __( 'Plugins', 'nexora' ), 'nexora_render_plugins' ),
		'nexora-demo'      => array( __( 'Demo Import', 'nexora' ), __( 'Demo Import', 'nexora' ), 'nexora_render_demo_import' ),
		'nexora-tutorials' => array( __( 'Tutorials', 'nexora' ), __( 'Tutorials', 'nexora' ), 'nexora_render_tutorials' ),
		'nexora-status'    => array( __( 'System Status', 'nexora' ), __( 'System Status', 'nexora' ), 'nexora_render_system_status' ),
		'nexora-wizard'    => array( __( 'Setup Wizard', 'nexora' ), __( 'Setup Wizard', 'nexora' ), 'nexora_render_wizard' ),
	);
	return apply_filters( 'nexora_admin_pages', $pages );
}

/**
 * Register menu.
 */
function nexora_admin_menu() {
	$cap   = nexora_admin_cap();
	$icon  = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	$badge = nexora_admin_attention_count();
	$label = nexora_is_fa() ? 'فروشگاه من' : __( 'My Store', 'nexora' );
	if ( $badge ) {
		$label .= ' <span class="awaiting-mod">' . (int) $badge . '</span>';
	}
	add_menu_page( __( 'Nexora Dashboard', 'nexora' ), $label, $cap, 'nexora', 'nexora_render_dashboard', $icon, 2 );
	foreach ( nexora_admin_pages() as $slug => $page ) {
		add_submenu_page( 'nexora', $page[0], $page[1], $cap, $slug, $page[2] );
	}
}
add_action( 'admin_menu', 'nexora_admin_menu' );

/**
 * Things that need the admin's attention (shown as a badge in the menu).
 *
 * @return int
 */
function nexora_admin_attention_count() {
	$count = 0;
	if ( ! class_exists( 'WooCommerce' ) ) {
		$count++;
	}
	if ( ! nexora_get_state( 'wizard_done' ) ) {
		$count++;
	}
	return (int) apply_filters( 'nexora_admin_attention_count', $count );
}

/**
 * Is the current admin screen one of ours?
 *
 * @return bool
 */
function nexora_is_theme_screen() {
	$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return $page && array_key_exists( $page, nexora_admin_pages() );
}

/**
 * Admin assets — only on theme screens (plus tiny help-box CSS elsewhere).
 *
 * @param string $hook Hook suffix.
 */
function nexora_admin_assets( $hook ) {
	if ( ! nexora_is_theme_screen() ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style( 'nexora-admin', NEXORA_URI . 'assets/css/admin.css', array(), NEXORA_VERSION );
	wp_enqueue_script( 'nexora-admin', NEXORA_URI . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'wp-util' ), NEXORA_VERSION, true );
	wp_localize_script(
		'nexora-admin',
		'NEXORA_ADMIN',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'nexora_admin' ),
			'tokens'  => nexora_preset_tokens(),
			'i18n'    => array(
				'select'    => __( 'Select', 'nexora' ),
				'remove'    => __( 'Remove', 'nexora' ),
				'confirm'   => __( 'Are you sure? This cannot be undone.', 'nexora' ),
				'saving'    => __( 'Saving…', 'nexora' ),
				'saved'     => __( 'Saved.', 'nexora' ),
				'error'     => __( 'Something went wrong.', 'nexora' ),
				'importing' => __( 'Importing… please keep this tab open.', 'nexora' ),
				'search'    => __( 'Type to search…', 'nexora' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'nexora_admin_assets' );

/**
 * Safety net: if another plugin dequeues/replaces our admin stylesheet (or an
 * RTL "replace" swap points at a missing file), print the link tag directly so
 * theme screens are never rendered unstyled.
 */
function nexora_admin_assets_fallback() {
	if ( ! nexora_is_theme_screen() ) {
		return;
	}
	if ( ! wp_style_is( 'nexora-admin', 'enqueued' ) && ! wp_style_is( 'nexora-admin', 'done' ) ) {
		printf( '<link rel="stylesheet" id="nexora-admin-fallback" href="%s">' . "\n", esc_url( add_query_arg( 'ver', NEXORA_VERSION, NEXORA_URI . 'assets/css/admin.css' ) ) );
	}
}
add_action( 'admin_print_styles', 'nexora_admin_assets_fallback', 100 );

/**
 * Verify nonce + capability for admin AJAX; dies on failure.
 */
function nexora_admin_ajax_check() {
	if ( ! check_ajax_referer( 'nexora_admin', 'nonce', false ) || ! current_user_can( nexora_admin_cap() ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'nexora' ) ), 403 );
	}
}

/**
 * Verify nonce + capability for a form POST; dies on failure.
 *
 * @param string $action Nonce action.
 */
function nexora_admin_post_check( $action ) {
	if ( ! current_user_can( nexora_admin_cap() ) ) {
		wp_die( esc_html__( 'Permission denied.', 'nexora' ), 403 );
	}
	check_admin_referer( $action, '_nexora_nonce' );
}

/**
 * Store a flash notice for the next theme screen load.
 *
 * @param string $message Message.
 * @param string $type    success|error|warning|info.
 */
function nexora_admin_flash( $message, $type = 'success' ) {
	set_transient( 'nexora_flash_' . get_current_user_id(), array( 'm' => $message, 't' => $type ), 60 );
}

/**
 * Print flash notices.
 */
function nexora_admin_print_flash() {
	$flash = get_transient( 'nexora_flash_' . get_current_user_id() );
	if ( ! $flash ) {
		return;
	}
	delete_transient( 'nexora_flash_' . get_current_user_id() );
	printf( '<div class="nx-notice nx-notice--%1$s">%2$s</div>', esc_attr( $flash['t'] ), wp_kses_post( $flash['m'] ) );
}

/**
 * Admin SVG icon (lucide-style outline set, no emoji anywhere).
 *
 * @param string $name Icon.
 * @param string $class Extra class.
 * @return string
 */
function nexora_admin_icon( $name, $class = '' ) {
	$paths = array(
		'home'      => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
		'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
		'palette'   => '<circle cx="13.5" cy="6.5" r="1"/><circle cx="17.5" cy="10.5" r="1"/><circle cx="8.5" cy="7.5" r="1"/><circle cx="6.5" cy="12.5" r="1"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10a2 2 0 0 0 2-2 2 2 0 0 0-.5-1.3 2 2 0 0 1 1.5-3.2H17a5 5 0 0 0 5-5c0-4.9-4.5-8.5-10-8.5"/>',
		'plug'      => '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a6 6 0 0 1-12 0V8z"/>',
		'download'  => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
		'book'      => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>',
		'activity'  => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
		'wand'      => '<path d="M15 4V2"/><path d="M15 16v-2"/><path d="M8 9h2"/><path d="M20 9h2"/><path d="M17.8 11.8 19 13"/><path d="M15 9h.01"/><path d="M17.8 6.2 19 5"/><path d="m3 21 9-9"/><path d="M12.2 6.2 11 5"/>',
		'check'     => '<path d="M20 6 9 17l-5-5"/>',
		'x'         => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'alert'     => '<path d="m21.7 18-8-14a2 2 0 0 0-3.4 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
		'info'      => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
		'cart'      => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
		'external'  => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
		'plus'      => '<path d="M5 12h14"/><path d="M12 5v14"/>',
		'copy'      => '<rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
		'trash'     => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
		'edit'      => '<path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
		'eye'       => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
		'layout'    => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>',
		'menu'      => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
		'image'     => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>',
		'shield'    => '<path d="M20 13c0 5-3.5 7.5-7.7 9a.6.6 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.7a1 1 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
		'zap'       => '<path d="M4 14a1 1 0 0 1-.8-1.6l9-12a.5.5 0 0 1 .9.4L11.6 10H20a1 1 0 0 1 .8 1.6l-9 12a.5.5 0 0 1-.9-.4L12.4 14z"/>',
		'globe'     => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
		'credit'    => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
		'truck'     => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62L18.3 9.38a1 1 0 0 0-.78-.38H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
		'file'      => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>',
		'arrow'     => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
		'refresh'   => '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
		'star'      => '<path d="M11.5 2.6a.6.6 0 0 1 1 0l2.7 5.6 6.1.9a.6.6 0 0 1 .3 1l-4.4 4.3 1 6.1a.6.6 0 0 1-.8.6L12 18.3l-5.5 2.9a.6.6 0 0 1-.8-.6l1-6.1L2.4 10a.6.6 0 0 1 .3-1l6.1-.9z"/>',
		'help'      => '<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
		'play'      => '<polygon points="6 3 20 12 6 21 6 3"/>',
		'lock'      => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		'type'      => '<polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/>',
		'tag'       => '<path d="M12.6 2.6A2 2 0 0 0 11.2 2H4a2 2 0 0 0-2 2v7.2a2 2 0 0 0 .6 1.4l7.9 7.9a2.4 2.4 0 0 0 3.4 0l6.6-6.6a2.4 2.4 0 0 0 0-3.4z"/><circle cx="7.5" cy="7.5" r=".5"/>',
		'share'     => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" x2="15.4" y1="13.5" y2="17.5"/><line x1="15.4" x2="8.6" y1="6.5" y2="10.5"/>',
		'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'pen'       => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z"/>',
		'store'     => '<path d="m2 7 4.4-4.4A2 2 0 0 1 7.8 2h8.4a2 2 0 0 1 1.4.6L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.6-.6.7.7 0 0 0-.8 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.6-.6.7.7 0 0 0-.8 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.6-.6.7.7 0 0 0-.8 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.6-.6.7.7 0 0 0-.8 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
	);
	$path = $paths[ $name ] ?? $paths['info'];
	return '<svg class="nx-icon ' . esc_attr( $class ) . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

/**
 * Page wrapper start: header bar with title, tabs across theme screens, flash.
 *
 * @param string $slug  Current page slug.
 * @param string $title Title.
 * @param string $intro Intro text.
 */
function nexora_admin_header( $slug, $title, $intro = '' ) {
	$pages = nexora_admin_pages();
	$icons = array( 'nexora' => 'home', 'nexora-settings' => 'settings', 'nexora-presets' => 'palette', 'nexora-plugins' => 'plug', 'nexora-demo' => 'download', 'nexora-tutorials' => 'book', 'nexora-status' => 'activity', 'nexora-wizard' => 'wand' );
	echo '<div class="wrap nx-wrap" id="nexora-admin" data-page="' . esc_attr( $slug ) . '">';
	echo '<div class="nx-topbar"><div class="nx-topbar__brand"><span class="nx-topbar__logo"><img src="' . esc_url( NEXORA_URI . 'assets/img/brand/avirad-mark.svg' ) . '" alt="AVIRAD" width="38" height="38"></span><div><strong>Nexora</strong><span class="nx-topbar__version">v' . esc_html( NEXORA_VERSION ) . '</span></div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<nav class="nx-tabs" aria-label="' . esc_attr__( 'Theme sections', 'nexora' ) . '">';
	foreach ( $pages as $s => $p ) {
		printf( '<a class="nx-tab%1$s" href="%2$s">%3$s<span>%4$s</span></a>', $s === $slug ? ' is-active' : '', esc_url( admin_url( 'admin.php?page=' . $s ) ), nexora_admin_icon( $icons[ $s ] ?? 'info' ), esc_html( $p[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</nav>';
	echo '<a class="nx-topbar__site" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . nexora_admin_icon( 'external' ) . esc_html__( 'View site', 'nexora' ) . '</a></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<header class="nx-page-head"><h1>' . esc_html( $title ) . '</h1>';
	if ( $intro ) {
		echo '<p class="nx-page-head__intro">' . wp_kses_post( $intro ) . '</p>';
	}
	echo '</header>';
	nexora_admin_print_flash();
	settings_errors( 'nexora' );
	echo '<div class="nx-page-body">';
}

/**
 * Page wrapper end.
 */
function nexora_admin_footer() {
	echo '</div>';
	printf( '<footer class="nx-page-foot"><img src="%1$s" alt="" width="18" height="18"> %2$s <a href="https://avirad.ir/" target="_blank" rel="noopener">AVIRAD</a></footer>', esc_url( NEXORA_URI . 'assets/img/brand/avirad-mark.svg' ), esc_html( sprintf( /* translators: %s: version */ __( 'Nexora theme %s — built for WooCommerce by', 'nexora' ), NEXORA_VERSION ) ) );
	echo '</div>';
}

/**
 * Status pill.
 *
 * @param string $state  ok|warn|bad|muted.
 * @param string $label  Text.
 * @return string
 */
function nexora_admin_pill( $state, $label ) {
	$icon = array( 'ok' => 'check', 'warn' => 'alert', 'bad' => 'x', 'muted' => 'info' );
	return '<span class="nx-pill nx-pill--' . esc_attr( $state ) . '">' . nexora_admin_icon( $icon[ $state ] ?? 'info' ) . esc_html( $label ) . '</span>';
}

/**
 * Card wrapper helper.
 *
 * @param string $title Title.
 * @param string $html  Inner HTML (already escaped).
 * @param array  $args  icon, class, action(html).
 */
function nexora_admin_card( $title, $html, array $args = array() ) {
	$icon  = ! empty( $args['icon'] ) ? nexora_admin_icon( $args['icon'] ) : '';
	$class = ! empty( $args['class'] ) ? ' ' . $args['class'] : '';
	echo '<section class="nx-card' . esc_attr( $class ) . '">';
	if ( $title ) {
		echo '<header class="nx-card__head"><h2>' . $icon . esc_html( $title ) . '</h2>' . ( $args['action'] ?? '' ) . '</header>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<div class="nx-card__body">' . $html . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Human "environment" facts used by dashboard + status.
 *
 * @return array
 */
function nexora_env_facts() {
	global $wpdb;
	$theme = wp_get_theme( get_template() );
	return array(
		'wp'        => get_bloginfo( 'version' ),
		'php'       => PHP_VERSION,
		'mysql'     => $wpdb->db_version(),
		'memory'    => WP_MEMORY_LIMIT,
		'max_upload' => size_format( wp_max_upload_size() ),
		'exec_time' => (int) ini_get( 'max_execution_time' ),
		'theme'     => $theme->get( 'Version' ),
		'child'     => is_child_theme() ? wp_get_theme()->get( 'Name' ) : '',
		'woo'       => defined( 'WC_VERSION' ) ? WC_VERSION : '',
		'acf'       => defined( 'ACF_VERSION' ) ? ACF_VERSION : '',
		'ssl'       => is_ssl(),
		'debug'     => defined( 'WP_DEBUG' ) && WP_DEBUG,
		'locale'    => get_locale(),
		'rtl'       => is_rtl(),
		'permalink' => get_option( 'permalink_structure' ),
	);
}

/**
 * Hide the WordPress default admin notices on theme screens so the UI stays clean.
 */
add_action(
	'admin_head',
	static function () {
		if ( nexora_is_theme_screen() ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
			add_action( 'admin_notices', 'nexora_admin_print_flash' );
		}
	},
	1
);

/**
 * "Theme details" links on the Appearance → Themes card and plugin-style row meta.
 */
add_filter(
	'admin_bar_menu',
	static function ( $bar ) {
		if ( ! current_user_can( nexora_admin_cap() ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'nexora',
				'title' => '<span class="ab-icon dashicons dashicons-store"></span>' . esc_html( nexora_is_fa() ? 'فروشگاه من' : __( 'My Store', 'nexora' ) ),
				'href'  => admin_url( 'admin.php?page=nexora' ),
			)
		);
		foreach ( array( 'nexora-settings', 'nexora-presets', 'nexora-status' ) as $slug ) {
			$p = nexora_admin_pages()[ $slug ];
			$bar->add_node( array( 'parent' => 'nexora', 'id' => $slug, 'title' => esc_html( $p[1] ), 'href' => admin_url( 'admin.php?page=' . $slug ) ) );
		}
	},
	90
);
