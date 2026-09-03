<?php
/**
 * Activation: safe auto-setup (idempotent, never touches secrets or payments).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages the theme needs. Key => [title, template, content].
 *
 * @return array
 */
function nexora_required_pages() {
	return array(
		'page_wishlist' => array( __( 'Wishlist', 'nexora' ), 'page-templates/wishlist.php', '' ),
		'page_compare'  => array( __( 'Compare', 'nexora' ), 'page-templates/compare.php', '' ),
		'page_contact'  => array( __( 'Contact us', 'nexora' ), 'page-templates/contact.php', '' ),
		'page_faq'      => array( __( 'FAQ', 'nexora' ), 'page-templates/faq.php', '' ),
		'page_home'     => array( __( 'Home', 'nexora' ), '', '' ),
		'page_blog'     => array( __( 'Blog', 'nexora' ), '', '' ),
	);
}

/**
 * Create missing pages (only if not already created & existing).
 */
function nexora_ensure_pages() {
	foreach ( nexora_required_pages() as $key => $def ) {
		$id = (int) nexora_get_state( $key );
		if ( $id && get_post( $id ) && 'trash' !== get_post_status( $id ) ) {
			continue;
		}
		// Reuse an existing page with the same template/slug if present.
		$existing = get_page_by_path( sanitize_title( $def[0] ) );
		if ( $existing && 'page' === $existing->post_type && 'trash' !== $existing->post_status ) {
			$id = $existing->ID;
		} else {
			$id = wp_insert_post(
				array(
					'post_title'   => $def[0],
					'post_name'    => sanitize_title( $def[0] ),
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => $def[2],
					'comment_status' => 'closed',
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				continue;
			}
		}
		if ( $def[1] ) {
			update_post_meta( $id, '_wp_page_template', $def[1] );
		}
		nexora_set_state( $key, (int) $id );
	}

	// Front page / posts page: only if the site is still on "latest posts".
	if ( 'posts' === get_option( 'show_on_front' ) && nexora_get_state( 'page_home' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) nexora_get_state( 'page_home' ) );
		if ( nexora_get_state( 'page_blog' ) ) {
			update_option( 'page_for_posts', (int) nexora_get_state( 'page_blog' ) );
		}
	}
}

/**
 * Create a primary menu with sensible links when no menu is assigned.
 */
function nexora_ensure_menus() {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) && wp_get_nav_menu_object( $locations['primary'] ) ) {
		return;
	}
	$menu = wp_get_nav_menu_object( 'Nexora Main' );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Nexora Main' );
	if ( is_wp_error( $menu_id ) ) {
		return;
	}
	if ( ! $menu ) {
		$items = array( array( __( 'Home', 'nexora' ), home_url( '/' ) ) );
		if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) {
			$items[] = array( __( 'Shop', 'nexora' ), get_permalink( wc_get_page_id( 'shop' ) ) );
		}
		foreach ( array( 'page_blog', 'page_faq', 'page_contact' ) as $k ) {
			if ( nexora_get_state( $k ) ) {
				$items[] = array( get_the_title( nexora_get_state( $k ) ), get_permalink( nexora_get_state( $k ) ) );
			}
		}
		foreach ( $items as $i => $item ) {
			wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $item[0], 'menu-item-url' => $item[1], 'menu-item-status' => 'publish', 'menu-item-position' => $i + 1 ) );
		}
	}
	$locations['primary'] = $menu_id;
	if ( empty( $locations['mobile'] ) ) {
		$locations['mobile'] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Default WordPress settings that a store expects (only when untouched).
 */
function nexora_ensure_defaults() {
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	if ( ! get_option( 'nexora_active_preset' ) ) {
		update_option( 'nexora_active_preset', 'classic-red' );
	}
	if ( ! get_option( 'thumbnail_crop' ) ) {
		update_option( 'thumbnail_crop', 1 );
	}
	// Timezone hint for Persian sites (never override an explicit choice).
	if ( nexora_is_fa() && ! get_option( 'timezone_string' ) && ! get_option( 'gmt_offset' ) ) {
		update_option( 'timezone_string', 'Asia/Tehran' );
	}
}

/**
 * Run on theme switch.
 */
function nexora_on_activation() {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	nexora_ensure_defaults();
	nexora_ensure_pages();
	nexora_ensure_menus();
	nexora_set_state( 'installed_at', nexora_get_state( 'installed_at', time() ) );
	nexora_set_state( 'version', NEXORA_VERSION );
	set_transient( 'nexora_activation_redirect', 1, 60 );
	flush_rewrite_rules();
	do_action( 'nexora_activated' );
}
add_action( 'after_switch_theme', 'nexora_on_activation' );

/**
 * Redirect to the wizard once after activation (not on bulk/network activations).
 */
add_action(
	'admin_init',
	static function () {
		if ( ! get_transient( 'nexora_activation_redirect' ) || wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		delete_transient( 'nexora_activation_redirect' );
		if ( current_user_can( nexora_admin_cap() ) && ! nexora_get_state( 'wizard_done' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=nexora-wizard' ) );
			exit;
		}
	}
);

/**
 * Version upgrade routine.
 */
add_action(
	'admin_init',
	static function () {
		$stored = nexora_get_state( 'version', '' );
		if ( $stored === NEXORA_VERSION ) {
			return;
		}
		nexora_set_state( 'version', NEXORA_VERSION );
		flush_rewrite_rules();
		do_action( 'nexora_upgraded', $stored, NEXORA_VERSION );
	},
	5
);

/**
 * On deactivation nothing is deleted — the user's content is theirs. Cleanup
 * is opt-in through Dashboard → Tools → "Reset theme options".
 */
add_action(
	'switch_theme',
	static function () {
		delete_transient( 'nexora_activation_redirect' );
	}
);
