<?php
/**
 * Colors & Presets admin screen.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form handlers (activate / save / duplicate / delete / import).
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_preset_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		nexora_admin_post_check( 'nexora_presets' );
		$action  = sanitize_key( $_POST['nexora_preset_action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$id      = isset( $_POST['preset_id'] ) ? sanitize_key( $_POST['preset_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$presets = nexora_presets();
		$edit    = '';

		switch ( $action ) {
			case 'activate':
				if ( isset( $presets[ $id ] ) ) {
					update_option( 'nexora_active_preset', $id );
					/* translators: %s: preset name */
					nexora_admin_flash( sprintf( __( '"%s" is now the active color preset.', 'nexora' ), $presets[ $id ]['name'] ) );
				}
				break;

			case 'save':
				$name   = isset( $_POST['preset_name'] ) ? sanitize_text_field( wp_unslash( $_POST['preset_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$colors = isset( $_POST['colors'] ) && is_array( $_POST['colors'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['colors'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				if ( '' === $name ) {
					$name = __( 'My preset', 'nexora' );
				}
				// Built-in presets are read-only: saving one creates a copy.
				$target = isset( $presets[ $id ] ) && empty( $presets[ $id ]['builtin'] ) ? $id : '';
				$new_id = nexora_save_preset( $target, $name, $colors );
				if ( ! empty( $_POST['activate_after'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					update_option( 'nexora_active_preset', $new_id );
				}
				nexora_admin_flash( __( 'Preset saved.', 'nexora' ) );
				$edit = $new_id;
				break;

			case 'duplicate':
				if ( isset( $presets[ $id ] ) ) {
					/* translators: %s: preset name */
					$edit = nexora_save_preset( '', sprintf( __( '%s (copy)', 'nexora' ), $presets[ $id ]['name'] ), $presets[ $id ]['colors'] );
					nexora_admin_flash( __( 'Preset duplicated — you are now editing the copy.', 'nexora' ) );
				}
				break;

			case 'delete':
				if ( nexora_delete_preset( $id ) ) {
					nexora_admin_flash( __( 'Preset deleted.', 'nexora' ), 'info' );
				} else {
					nexora_admin_flash( __( 'Built-in presets cannot be deleted.', 'nexora' ), 'error' );
				}
				break;

			case 'import':
				$json = isset( $_POST['preset_json'] ) ? wp_unslash( $_POST['preset_json'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$data = json_decode( (string) $json, true );
				if ( is_array( $data ) && ! empty( $data['colors'] ) && is_array( $data['colors'] ) ) {
					$edit = nexora_save_preset( '', sanitize_text_field( $data['name'] ?? __( 'Imported preset', 'nexora' ) ), array_map( 'sanitize_text_field', $data['colors'] ) );
					nexora_admin_flash( __( 'Preset imported.', 'nexora' ) );
				} else {
					nexora_admin_flash( __( 'Invalid preset JSON.', 'nexora' ), 'error' );
				}
				break;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=nexora-presets' . ( $edit ? '&edit=' . $edit : '' ) ) );
		exit;
	}
);

/**
 * Render page.
 */
function nexora_render_presets() {
	$presets = nexora_presets();
	$active  = nexora_active_preset_id();
	$edit_id = isset( $_GET['edit'] ) ? sanitize_key( $_GET['edit'] ) : $active; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$edit    = $presets[ $edit_id ] ?? $presets[ $active ];
	$tokens  = nexora_preset_tokens();

	nexora_admin_header( 'nexora-presets', __( 'Colors & Presets', 'nexora' ), __( 'Pick a ready-made palette or design your own. Every colour is a CSS variable (--theme-*), so the whole site — including WooCommerce pages — updates at once. Built-in presets are read-only; edit them to create your own copy.', 'nexora' ) );

	echo '<div class="nx-presets">';
	echo '<div class="nx-presets__list">';
	echo '<h2 class="nx-section-title">' . esc_html__( 'Presets', 'nexora' ) . '</h2>';
	foreach ( $presets as $id => $preset ) {
		$c = $preset['colors'];
		echo '<article class="nx-preset' . ( $id === $active ? ' is-active' : '' ) . ( $id === $edit_id ? ' is-editing' : '' ) . '">';
		echo '<a class="nx-preset__swatches" href="' . esc_url( admin_url( 'admin.php?page=nexora-presets&edit=' . $id ) ) . '" aria-label="' . esc_attr( $preset['name'] ) . '">';
		foreach ( array( 'primary', 'accent', 'secondary', 'surface', 'text', 'danger' ) as $k ) {
			echo '<span style="background:' . esc_attr( $c[ $k ] ) . '"></span>';
		}
		echo '</a><div class="nx-preset__meta"><strong>' . esc_html( $preset['name'] ) . '</strong>';
		if ( $id === $active ) {
			echo nexora_admin_pill( 'ok', __( 'Active', 'nexora' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( ! empty( $preset['builtin'] ) ) {
			echo '<span class="nx-muted">' . esc_html__( 'Built-in', 'nexora' ) . '</span>';
		}
		echo '</div><div class="nx-preset__actions">';
		echo '<form method="post" class="nx-inline">';
		wp_nonce_field( 'nexora_presets', '_nexora_nonce' );
		echo '<input type="hidden" name="preset_id" value="' . esc_attr( $id ) . '">';
		if ( $id !== $active ) {
			echo '<button class="button button-small" name="nexora_preset_action" value="activate">' . esc_html__( 'Activate', 'nexora' ) . '</button> ';
		}
		echo '<a class="button button-small" href="' . esc_url( admin_url( 'admin.php?page=nexora-presets&edit=' . $id ) ) . '">' . esc_html__( 'Edit', 'nexora' ) . '</a> ';
		echo '<button class="button button-small" name="nexora_preset_action" value="duplicate">' . esc_html__( 'Duplicate', 'nexora' ) . '</button> ';
		if ( empty( $preset['builtin'] ) ) {
			echo '<button class="button-link-delete" name="nexora_preset_action" value="delete" data-confirm>' . esc_html__( 'Delete', 'nexora' ) . '</button>';
		}
		echo '</form></div></article>';
	}
	echo '<details class="nx-card nx-card--flat"><summary>' . esc_html__( 'Import preset from JSON', 'nexora' ) . '</summary><form method="post">';
	wp_nonce_field( 'nexora_presets', '_nexora_nonce' );
	echo '<textarea name="preset_json" rows="4" class="large-text code" placeholder=\'{"name":"…","colors":{"primary":"#…"}}\'></textarea><p><button class="button" name="nexora_preset_action" value="import">' . esc_html__( 'Import', 'nexora' ) . '</button></p></form></details>';
	echo '</div>';

	// Editor.
	echo '<div class="nx-presets__editor">';
	echo '<form method="post" class="nx-card" id="nexora-preset-editor" data-preset-editor>';
	wp_nonce_field( 'nexora_presets', '_nexora_nonce' );
	echo '<input type="hidden" name="preset_id" value="' . esc_attr( $edit_id ) . '">';
	echo '<header class="nx-card__head"><h2>' . nexora_admin_icon( 'palette' ) . ( empty( $edit['builtin'] ) ? esc_html__( 'Edit preset', 'nexora' ) : esc_html__( 'Customise (saves as a new preset)', 'nexora' ) ) . '</h2></header><div class="nx-card__body">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="nx-field"><label class="nx-field__label" for="preset_name">' . esc_html__( 'Preset name', 'nexora' ) . '</label><div class="nx-field__control"><input type="text" id="preset_name" name="preset_name" class="regular-text" value="' . esc_attr( empty( $edit['builtin'] ) ? $edit['name'] : $edit['name'] . ' ' . __( '(custom)', 'nexora' ) ) . '" required></div></div>';
	echo '<div class="nx-colorgrid">';
	foreach ( $tokens as $key => $meta ) {
		printf(
			'<div class="nx-field nx-field--color"><label class="nx-field__label" for="c-%1$s">%2$s <code>%3$s</code></label><div class="nx-field__control"><input type="text" id="c-%1$s" name="colors[%1$s]" class="nx-color" value="%4$s" data-var="%3$s" data-default-color="%4$s"></div>%5$s</div>',
			esc_attr( $key ),
			esc_html( $meta[0] ),
			esc_attr( $meta[1] ),
			esc_attr( $edit['colors'][ $key ] ),
			$meta[2] ? '<p class="nx-field__desc">' . esc_html( $meta[2] ) . '</p>' : ''
		);
	}
	echo '</div>';
	echo '<div class="nx-contrast" data-contrast><strong>' . esc_html__( 'Contrast check', 'nexora' ) . ':</strong> <span data-contrast-out></span></div>';
	echo '<div class="nx-savebar nx-savebar--inline"><button class="button button-primary" name="nexora_preset_action" value="save">' . esc_html__( 'Save preset', 'nexora' ) . '</button> <label class="nx-check"><input type="checkbox" name="activate_after" value="1" checked> ' . esc_html__( 'Activate after saving', 'nexora' ) . '</label> <button type="button" class="button" data-preset-export>' . esc_html__( 'Export JSON', 'nexora' ) . '</button></div>';
	echo '</div></form>';

	// Live preview.
	echo '<div class="nx-card nx-preview" data-preset-preview style="' . esc_attr( trim( str_replace( array( ':root{', '}' ), '', nexora_preset_css_vars( $edit['colors'] ) ) ) ) . '">';
	echo '<header class="nx-card__head"><h2>' . nexora_admin_icon( 'eye' ) . esc_html__( 'Live preview', 'nexora' ) . '</h2></header><div class="nx-card__body nx-preview__body">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="nx-preview__topbar">' . esc_html__( 'Free shipping on orders over 500,000 Toman', 'nexora' ) . '</div>';
	echo '<div class="nx-preview__header"><strong>' . esc_html( nexora_option( 'general', 'logo_text', 'Nexora' ) ) . '</strong><span class="nx-preview__search">' . esc_html__( 'Search products…', 'nexora' ) . '</span><span class="nx-preview__cart">3</span></div>';
	echo '<div class="nx-preview__body-area"><div class="nx-preview__card"><div class="nx-preview__img"></div><span class="nx-preview__badge">-20%</span><h4>' . esc_html__( 'Sample product', 'nexora' ) . '</h4><p class="nx-preview__price">1,250,000</p><button type="button" class="nx-preview__btn">' . esc_html__( 'Add to cart', 'nexora' ) . '</button></div><div class="nx-preview__card"><div class="nx-preview__img"></div><h4>' . esc_html__( 'Another product', 'nexora' ) . '</h4><p class="nx-preview__price">890,000</p><button type="button" class="nx-preview__btn nx-preview__btn--dark">' . esc_html__( 'Details', 'nexora' ) . '</button></div></div>';
	echo '<div class="nx-preview__footer">' . esc_html__( 'Footer text · Links · Newsletter', 'nexora' ) . '<div class="nx-preview__footer-bottom">' . esc_html__( 'All rights reserved.', 'nexora' ) . '</div></div>';
	echo '</div></div>';
	echo '</div></div>';
	nexora_admin_footer();
}
