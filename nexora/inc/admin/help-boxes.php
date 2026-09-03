<?php
/**
 * Contextual help: WP help tabs on theme screens + light hints on core screens
 * that matter for the theme (menus, widgets, products, reading settings).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Help content per screen id fragment.
 *
 * @return array
 */
function nexora_help_map() {
	return array(
		'nexora'           => array( __( 'Dashboard', 'nexora' ), __( 'This screen summarises the health of your store. Use the tabs at the top to move between theme areas. Nothing here changes the front end by itself.', 'nexora' ) ),
		'nexora-settings'  => array( __( 'Theme settings', 'nexora' ), __( 'Each tab is saved separately. Toggles that are off hide the element entirely (no empty space is left). Fields marked with a note depend on another field and appear only when relevant.', 'nexora' ) ),
		'nexora-presets'   => array( __( 'Colour presets', 'nexora' ), __( 'Colours are CSS custom properties. Anything you add in a child theme can use var(--theme-primary) and will follow the active preset.', 'nexora' ) ),
		'nexora-plugins'   => array( __( 'Plugins', 'nexora' ), __( 'Installs are performed by WordPress itself from the official directory. The theme never downloads code from elsewhere.', 'nexora' ) ),
		'nexora-demo'      => array( __( 'Demo import', 'nexora' ), __( 'Demo products, categories, posts and settings are tagged so they can be removed completely. Your existing content is never modified.', 'nexora' ) ),
		'nexora-status'    => array( __( 'System status', 'nexora' ), __( 'Copy the support report when asking for help. It contains versions and active plugins only — no credentials.', 'nexora' ) ),
		'nav-menus'        => array( __( 'Nexora menu locations', 'nexora' ), __( 'Primary = main navigation bar. Mobile = drawer (falls back to Primary). Top bar start/end = small utility links. Footer 1/2 = link columns. Footer bottom = legal links. Account = user dropdown.', 'nexora' ) ),
		'widgets'          => array( __( 'Nexora widget areas', 'nexora' ), __( 'Shop sidebar shows next to the product grid (filters are built in — add extra widgets only if needed). Blog sidebar and Page sidebar appear on posts/pages with a sidebar layout. Footer extra sits above the copyright row.', 'nexora' ) ),
		'options-reading'  => array( __( 'Nexora homepage', 'nexora' ), __( 'Set "Your homepage displays" to a static page and pick the "Home" page — the theme then renders the homepage sections configured in Theme Settings → Homepage.', 'nexora' ) ),
		'product'          => array( __( 'Nexora product tips', 'nexora' ), __( 'Featured image: square 800 px+. Attributes marked visible appear in the Specifications tab and compare table. Sale price triggers the discount badge. Highlights (ACF) show next to the price.', 'nexora' ) ),
	);
}

add_action(
	'current_screen',
	static function ( $screen ) {
		if ( ! $screen || ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$map  = nexora_help_map();
		$key  = '';
		if ( $page && isset( $map[ $page ] ) ) {
			$key = $page;
		} elseif ( isset( $map[ $screen->id ] ) ) {
			$key = $screen->id;
		} elseif ( 'product' === $screen->post_type && 'post' === $screen->base ) {
			$key = 'product';
		}
		if ( ! $key ) {
			return;
		}
		$screen->add_help_tab(
			array(
				'id'      => 'nexora-help',
				'title'   => 'Nexora',
				'content' => '<h3>' . esc_html( $map[ $key ][0] ) . '</h3><p>' . esc_html( $map[ $key ][1] ) . '</p><p><a href="' . esc_url( admin_url( 'admin.php?page=nexora-tutorials' ) ) . '">' . esc_html__( 'Open theme tutorials', 'nexora' ) . ' &rarr;</a></p>',
			)
		);
		$screen->set_help_sidebar( '<p><strong>Nexora</strong></p><p><a href="' . esc_url( admin_url( 'admin.php?page=nexora' ) ) . '">' . esc_html__( 'Theme dashboard', 'nexora' ) . '</a></p><p><a href="' . esc_url( admin_url( 'admin.php?page=nexora-status' ) ) . '">' . esc_html__( 'System status', 'nexora' ) . '</a></p>' );
	}
);

/**
 * Inline hint box on core screens that are essential for the theme (dismissible).
 */
add_action(
	'admin_notices',
	static function () {
		$screen = get_current_screen();
		if ( ! $screen || ! current_user_can( nexora_admin_cap() ) ) {
			return;
		}
		$hints = array(
			'nav-menus'       => 'nav-menus',
			'widgets'         => 'widgets',
			'options-reading' => 'options-reading',
		);
		if ( ! isset( $hints[ $screen->id ] ) ) {
			return;
		}
		$dismissed = (array) get_user_meta( get_current_user_id(), 'nexora_hints_dismissed', true );
		if ( in_array( $screen->id, $dismissed, true ) ) {
			return;
		}
		$h = nexora_help_map()[ $hints[ $screen->id ] ];
		printf(
			'<div class="notice notice-info is-dismissible nexora-hint" data-hint="%1$s"><p><strong>%2$s</strong> — %3$s <a href="%4$s">%5$s</a></p></div>',
			esc_attr( $screen->id ),
			esc_html( $h[0] ),
			esc_html( $h[1] ),
			esc_url( admin_url( 'admin.php?page=nexora-tutorials' ) ),
			esc_html__( 'Learn more', 'nexora' )
		);
		wp_enqueue_script( 'nexora-hints', NEXORA_URI . 'assets/js/admin-hints.js', array( 'jquery' ), NEXORA_VERSION, true );
		wp_localize_script( 'nexora-hints', 'NEXORA_HINTS', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'nexora_admin' ) ) );
	}
);

add_action(
	'wp_ajax_nexora_dismiss_hint',
	static function () {
		nexora_admin_ajax_check();
		$id = isset( $_POST['hint'] ) ? sanitize_key( $_POST['hint'] ) : '';
		$d  = array_filter( (array) get_user_meta( get_current_user_id(), 'nexora_hints_dismissed', true ) );
		$d[] = $id;
		update_user_meta( get_current_user_id(), 'nexora_hints_dismissed', array_values( array_unique( $d ) ) );
		wp_send_json_success();
	}
);
