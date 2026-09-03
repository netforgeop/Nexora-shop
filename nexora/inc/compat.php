<?php
/**
 * Loaded only when the server does not meet the minimum requirements.
 * Shows a notice, prevents activation side effects and keeps the site alive.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_compat_notice() {
	$message = sprintf(
		/* translators: 1: required PHP version, 2: required WordPress version, 3: current PHP version, 4: current WordPress version */
		esc_html__( 'Nexora requires PHP %1$s and WordPress %2$s or newer. You are running PHP %3$s and WordPress %4$s. The theme has been loaded in a safe mode without its features.', 'nexora' ),
		NEXORA_MIN_PHP,
		NEXORA_MIN_WP,
		PHP_VERSION,
		get_bloginfo( 'version' )
	);
	printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
}
add_action( 'admin_notices', 'nexora_compat_notice' );

function nexora_compat_disable_customizer() {
	wp_die( esc_html__( 'The Customizer is unavailable because Nexora is running in safe mode. Please upgrade PHP / WordPress.', 'nexora' ) );
}
add_action( 'load-customize.php', 'nexora_compat_disable_customizer' );
