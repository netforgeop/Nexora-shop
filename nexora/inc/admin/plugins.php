<?php
/**
 * Plugin Manager — required / recommended plugins with status.
 *
 * Only WordPress.org plugins are ever installed, and only through the core
 * installer with the user's explicit click (install_plugins capability).
 * Third-party plugins (e.g. payment gateways) are linked, never downloaded.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin list.
 *
 * @return array
 */
function nexora_plugin_list() {
	$list = array(
		'woocommerce'            => array(
			'name'     => 'WooCommerce',
			'file'     => 'woocommerce/woocommerce.php',
			'slug'     => 'woocommerce',
			'source'   => 'wporg',
			'required' => true,
			'desc'     => __( 'Products, cart, checkout, orders and customer accounts. The theme cannot function as a store without it.', 'nexora' ),
		),
		'secure-custom-fields'   => array(
			'name'     => 'Secure Custom Fields (ACF-compatible)',
			'file'     => array( 'secure-custom-fields/secure-custom-fields.php', 'advanced-custom-fields/acf.php', 'advanced-custom-fields-pro/acf.php' ),
			'slug'     => 'secure-custom-fields',
			'source'   => 'wporg',
			'required' => false,
			'desc'     => __( 'Adds extra editing fields on products, pages and posts (highlights, size guide, page banners). Advanced Custom Fields / ACF Pro are detected too. Theme options themselves do not need it.', 'nexora' ),
		),
		'seo-by-rank-math'       => array(
			'name'     => 'Rank Math SEO',
			'file'     => 'seo-by-rank-math/rank-math.php',
			'slug'     => 'seo-by-rank-math',
			'source'   => 'wporg',
			'required' => false,
			'desc'     => __( 'Sitemaps, schema, meta tags. The theme’s built-in SEO output steps aside automatically when an SEO plugin is active.', 'nexora' ),
		),
		'litespeed-cache'        => array(
			'name'     => 'LiteSpeed Cache / WP Super Cache',
			'file'     => array( 'litespeed-cache/litespeed-cache.php', 'wp-super-cache/wp-cache.php', 'w3-total-cache/w3-total-cache.php', 'wp-rocket/wp-rocket.php' ),
			'slug'     => 'litespeed-cache',
			'source'   => 'wporg',
			'required' => false,
			'desc'     => __( 'Page caching for faster load times. Any major caching plugin is recognised.', 'nexora' ),
		),
		'wordfence'              => array(
			'name'     => 'Wordfence Security',
			'file'     => array( 'wordfence/wordfence.php', 'better-wp-security/better-wp-security.php', 'sucuri-scanner/sucuri.php' ),
			'slug'     => 'wordfence',
			'source'   => 'wporg',
			'required' => false,
			'desc'     => __( 'Firewall, login protection and malware scanning.', 'nexora' ),
		),
		'persian-woocommerce'    => array(
			'name'     => __( 'Persian WooCommerce', 'nexora' ),
			'file'     => 'persian-woocommerce/woocommerce-persian.php',
			'slug'     => 'persian-woocommerce',
			'source'   => 'wporg',
			'required' => false,
			'desc'     => __( 'Iranian provinces, Toman/Rial formatting and Persian date helpers. Recommended for Iranian stores.', 'nexora' ),
			'locale'   => 'fa',
		),
		'payment-gateway'        => array(
			'name'     => __( 'Payment gateway (ZarinPal / IDPay / your bank)', 'nexora' ),
			'file'     => array( 'zarinpal-woocommerce-payment-gateway/zarinpal-woocommerce.php', 'woo-zarinpal-gateway/woo-zarinpal-gateway.php', 'idpay-woocommerce/idpay-woocommerce.php' ),
			'slug'     => '',
			'source'   => 'external',
			'url'      => 'https://www.zarinpal.com/lab/',
			'required' => false,
			'desc'     => __( 'Install the official gateway plugin from your payment provider and configure it under WooCommerce → Settings → Payments. The theme never handles payment data itself.', 'nexora' ),
		),
	);
	return apply_filters( 'nexora_plugin_list', $list );
}

/**
 * Resolve status for a plugin definition.
 *
 * @param array $plugin Definition.
 * @return array status(active|installed|missing), version, file
 */
function nexora_plugin_status( array $plugin ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$installed = get_plugins();
	foreach ( (array) $plugin['file'] as $file ) {
		if ( isset( $installed[ $file ] ) ) {
			return array(
				'status'  => is_plugin_active( $file ) ? 'active' : 'installed',
				'version' => $installed[ $file ]['Version'],
				'file'    => $file,
				'name'    => $installed[ $file ]['Name'],
			);
		}
	}
	return array( 'status' => 'missing', 'version' => '', 'file' => is_array( $plugin['file'] ) ? $plugin['file'][0] : $plugin['file'], 'name' => $plugin['name'] );
}

/**
 * Action URL for a plugin card (install / activate) — core URLs, nonce'd by core.
 *
 * @param array $plugin Definition.
 * @param array $status Status.
 * @return array [label, url, primary]
 */
function nexora_plugin_action( array $plugin, array $status ) {
	if ( 'active' === $status['status'] ) {
		return array();
	}
	if ( 'installed' === $status['status'] && current_user_can( 'activate_plugins' ) ) {
		return array(
			__( 'Activate', 'nexora' ),
			wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $status['file'] ) ), 'activate-plugin_' . $status['file'] ),
			true,
		);
	}
	if ( 'missing' === $status['status'] ) {
		if ( 'wporg' === $plugin['source'] && current_user_can( 'install_plugins' ) ) {
			return array(
				__( 'Install from WordPress.org', 'nexora' ),
				wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . rawurlencode( $plugin['slug'] ) ), 'install-plugin_' . $plugin['slug'] ),
				true,
			);
		}
		if ( ! empty( $plugin['url'] ) ) {
			return array( __( 'Get it from the provider', 'nexora' ), $plugin['url'], false );
		}
	}
	return array();
}

/**
 * Summary counts for dashboard.
 *
 * @return array
 */
function nexora_plugin_summary() {
	$out = array( 'required_missing' => 0, 'recommended_missing' => 0, 'active' => 0 );
	foreach ( nexora_plugin_list() as $plugin ) {
		$st = nexora_plugin_status( $plugin );
		if ( 'active' === $st['status'] ) {
			$out['active']++;
		} elseif ( $plugin['required'] ) {
			$out['required_missing']++;
		} else {
			$out['recommended_missing']++;
		}
	}
	return $out;
}

/**
 * Render page.
 */
function nexora_render_plugins() {
	nexora_admin_header( 'nexora-plugins', __( 'Plugin Manager', 'nexora' ), __( 'The theme depends on a few well-known plugins. Nothing is installed automatically — every install goes through the standard WordPress installer from WordPress.org, and payment gateways always come from your provider.', 'nexora' ) );

	foreach ( array( true => __( 'Required', 'nexora' ), false => __( 'Recommended', 'nexora' ) ) as $required => $heading ) {
		echo '<h2 class="nx-section-title">' . esc_html( $heading ) . '</h2><div class="nx-grid nx-grid--2">';
		foreach ( nexora_plugin_list() as $plugin ) {
			if ( (bool) $plugin['required'] !== (bool) $required ) {
				continue;
			}
			if ( ! empty( $plugin['locale'] ) && strpos( get_locale(), $plugin['locale'] ) !== 0 ) {
				continue;
			}
			$st     = nexora_plugin_status( $plugin );
			$pill   = array( 'active' => array( 'ok', __( 'Active', 'nexora' ) ), 'installed' => array( 'warn', __( 'Installed, not active', 'nexora' ) ), 'missing' => array( $required ? 'bad' : 'muted', __( 'Not installed', 'nexora' ) ) );
			$action = nexora_plugin_action( $plugin, $st );
			ob_start();
			echo '<p>' . esc_html( $plugin['desc'] ) . '</p><div class="nx-plugin__meta">';
			echo nexora_admin_pill( $pill[ $st['status'] ][0], $pill[ $st['status'] ][1] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $st['version'] ) {
				echo '<span class="nx-muted">' . esc_html( $st['name'] ) . ' v' . esc_html( $st['version'] ) . '</span>';
			}
			echo '<span class="nx-muted">' . ( 'wporg' === $plugin['source'] ? esc_html__( 'Source: WordPress.org', 'nexora' ) : esc_html__( 'Source: provider website', 'nexora' ) ) . '</span></div>';
			if ( $action ) {
				printf( '<p><a class="button %1$s" href="%2$s"%3$s>%4$s</a></p>', $action[2] ? 'button-primary' : '', esc_url( $action[1] ), $action[2] ? '' : ' target="_blank" rel="noopener"', esc_html( $action[0] ) );
			}
			nexora_admin_card( $plugin['name'], ob_get_clean(), array( 'icon' => 'plug', 'class' => 'nx-plugin nx-plugin--' . $st['status'] ) );
		}
		echo '</div>';
	}

	nexora_admin_card(
		__( 'Security note', 'nexora' ),
		'<p>' . esc_html__( 'Never upload plugins from untrusted sources. Nulled or repackaged plugins are the number one cause of hacked stores. Keep WordPress, WooCommerce and this theme up to date, and use a strong admin password with two-factor authentication.', 'nexora' ) . '</p>',
		array( 'icon' => 'shield', 'class' => 'nx-card--info' )
	);
	nexora_admin_footer();
}
