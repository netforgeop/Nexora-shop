<?php
/**
 * Onboarding tour (first visit to the theme dashboard) — skippable, per user.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tour steps: selector => title/text.
 *
 * @return array
 */
function nexora_onboarding_steps() {
	return array(
		array( 'target' => '.nx-tabs', 'title' => __( 'Theme navigation', 'nexora' ), 'text' => __( 'These tabs are the whole theme: settings, colours, plugins, demo import, tutorials and system status. Everything else is standard WordPress.', 'nexora' ) ),
		array( 'target' => '.nx-strip', 'title' => __( 'Store health', 'nexora' ), 'text' => __( 'A live summary of WooCommerce and theme checks. Red items block sales; amber ones are recommendations.', 'nexora' ) ),
		array( 'target' => '.nx-quicklinks', 'title' => __( 'Quick actions', 'nexora' ), 'text' => __( 'Jump straight to the things you edit most: homepage sections, colours, menus, products, orders.', 'nexora' ) ),
		array( 'target' => '.nx-tutlist', 'title' => __( 'Tutorials', 'nexora' ), 'text' => __( 'Step-by-step guides for store owners. Start with "Your first 10 minutes".', 'nexora' ) ),
		array( 'target' => '.nx-tools', 'title' => __( 'Tools', 'nexora' ), 'text' => __( 'Clear caches, export subscribers and options, or replay this tour later.', 'nexora' ) ),
	);
}

/**
 * Print tour data on the dashboard for users who have not dismissed it.
 */
add_action(
	'admin_footer',
	static function () {
		if ( ! nexora_is_theme_screen() || 'nexora' !== ( isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'nexora_onboarding_done', true ) ) {
			return;
		}
		$data = array(
			'steps' => nexora_onboarding_steps(),
			'i18n'  => array(
				'skip'   => __( 'Skip tour', 'nexora' ),
				'next'   => __( 'Next', 'nexora' ),
				'back'   => __( 'Back', 'nexora' ),
				'finish' => __( 'Finish', 'nexora' ),
				'of'     => __( 'of', 'nexora' ),
			),
		);
		echo '<script type="application/json" id="nexora-onboarding">' . wp_json_encode( $data ) . '</script>';
	}
);

/**
 * AJAX: dismiss.
 */
add_action(
	'wp_ajax_nexora_onboarding_done',
	static function () {
		nexora_admin_ajax_check();
		update_user_meta( get_current_user_id(), 'nexora_onboarding_done', time() );
		wp_send_json_success();
	}
);
