<?php
/**
 * Colour preset system.
 *
 * A preset is a named set of design tokens that map to CSS custom properties.
 * Three presets ship with the theme; users can create / edit / duplicate /
 * activate / delete their own. The active preset is rendered as inline CSS
 * on the front-end (see nexora_preset_css()).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Token definitions: key => [label, css variable, description].
 *
 * @return array
 */
function nexora_preset_tokens() {
	return array(
		'primary'        => array( __( 'Primary', 'nexora' ), '--theme-primary', __( 'Buttons, highlights, badges, active states.', 'nexora' ) ),
		'primary_hover'  => array( __( 'Primary hover', 'nexora' ), '--theme-primary-hover', __( 'Primary colour on hover / focus.', 'nexora' ) ),
		'on_primary'     => array( __( 'Text on primary', 'nexora' ), '--theme-on-primary', __( 'Text colour placed on primary backgrounds. Keep contrast ≥ 4.5:1.', 'nexora' ) ),
		'secondary'      => array( __( 'Secondary', 'nexora' ), '--theme-secondary', __( 'Page background.', 'nexora' ) ),
		'surface'        => array( __( 'Surface', 'nexora' ), '--theme-surface', __( 'Cards, inputs and light sections.', 'nexora' ) ),
		'accent'         => array( __( 'Accent / dark', 'nexora' ), '--theme-accent', __( 'Dark buttons, category menu, headings.', 'nexora' ) ),
		'text'           => array( __( 'Text', 'nexora' ), '--theme-text', __( 'Body text.', 'nexora' ) ),
		'text_muted'     => array( __( 'Muted text', 'nexora' ), '--theme-text-muted', __( 'Secondary text, meta info.', 'nexora' ) ),
		'border'         => array( __( 'Border', 'nexora' ), '--theme-border', __( 'Dividers and outlines.', 'nexora' ) ),
		'button'         => array( __( 'Button', 'nexora' ), '--theme-button', __( 'Default button background (usually = primary).', 'nexora' ) ),
		'button_text'    => array( __( 'Button text', 'nexora' ), '--theme-button-text', '' ),
		'header_bg'      => array( __( 'Header background', 'nexora' ), '--theme-header-bg', '' ),
		'header_text'    => array( __( 'Header text', 'nexora' ), '--theme-header-text', '' ),
		'topbar_bg'      => array( __( 'Top bar background', 'nexora' ), '--theme-topbar-bg', '' ),
		'footer_bg'      => array( __( 'Footer background', 'nexora' ), '--theme-footer-bg', '' ),
		'footer_text'    => array( __( 'Footer text', 'nexora' ), '--theme-footer-text', '' ),
		'footer_bottom'  => array( __( 'Footer bottom row', 'nexora' ), '--theme-footer-bottom', '' ),
		'success'        => array( __( 'Success', 'nexora' ), '--theme-success', '' ),
		'danger'         => array( __( 'Danger / sale', 'nexora' ), '--theme-danger', '' ),
		'star'           => array( __( 'Star rating', 'nexora' ), '--theme-star', '' ),
	);
}

/**
 * Built-in presets (cannot be deleted, can be duplicated).
 *
 * @return array
 */
function nexora_builtin_presets() {
	$base = array(
		'secondary'     => '#ffffff',
		'surface'       => '#f8f8f8',
		'text'          => '#333333',
		'text_muted'    => '#6b6b6b',
		'border'        => '#e6e6e8',
		'header_bg'     => '#ffffff',
		'header_text'   => '#333333',
		'success'       => '#1f9d55',
		'danger'        => '#d64545',
		'star'          => '#f5a623',
	);

	return array(
		'classic-red'  => array(
			'name'    => __( 'Classic Red', 'nexora' ),
			'builtin' => true,
			'colors'  => array_merge(
				$base,
				array(
					'primary'       => '#d62828',
					'primary_hover' => '#b81f1f',
					'on_primary'    => '#ffffff',
					'accent'        => '#111111',
					'button'        => '#d62828',
					'button_text'   => '#ffffff',
					'topbar_bg'     => '#151515',
					'footer_bg'     => '#151515',
					'footer_text'   => '#c7c7c7',
					'footer_bottom' => '#0d0d0d',
				)
			),
		),
		'modern-blue'  => array(
			'name'    => __( 'Modern Blue', 'nexora' ),
			'builtin' => true,
			'colors'  => array_merge(
				$base,
				array(
					'primary'       => '#2563eb',
					'primary_hover' => '#1d4ed8',
					'on_primary'    => '#ffffff',
					'accent'        => '#0f172a',
					'button'        => '#2563eb',
					'button_text'   => '#ffffff',
					'topbar_bg'     => '#0f172a',
					'footer_bg'     => '#0f172a',
					'footer_text'   => '#cbd5e1',
					'footer_bottom' => '#0b1120',
					'text'          => '#1e293b',
					'text_muted'    => '#64748b',
					'border'        => '#e2e8f0',
					'surface'       => '#f8fafc',
				)
			),
		),
		'luxury-green' => array(
			'name'    => __( 'Luxury Green', 'nexora' ),
			'builtin' => true,
			'colors'  => array_merge(
				$base,
				array(
					'primary'       => '#1b5e3f',
					'primary_hover' => '#154a32',
					'on_primary'    => '#ffffff',
					'accent'        => '#121a16',
					'button'        => '#1b5e3f',
					'button_text'   => '#ffffff',
					'topbar_bg'     => '#121a16',
					'footer_bg'     => '#121a16',
					'footer_text'   => '#c9d3ce',
					'footer_bottom' => '#0b100d',
					'surface'       => '#f6f8f7',
					'border'        => '#e1e8e4',
					'star'          => '#c9a227',
				)
			),
		),
		'nexora-gold'  => array(
			'name'    => __( 'Nexora Gold (original)', 'nexora' ),
			'builtin' => true,
			'colors'  => array_merge(
				$base,
				array(
					'primary'       => '#f6d014',
					'primary_hover' => '#e3be09',
					'on_primary'    => '#111111',
					'accent'        => '#111111',
					'button'        => '#f6d014',
					'button_text'   => '#111111',
					'topbar_bg'     => '#151515',
					'footer_bg'     => '#151515',
					'footer_text'   => '#c7c7c7',
					'footer_bottom' => '#111111',
				)
			),
		),
	);
}

/**
 * All presets: built-in + user-created.
 *
 * @return array
 */
function nexora_presets() {
	$custom = get_option( 'nexora_presets', array() );
	$custom = is_array( $custom ) ? $custom : array();
	return array_merge( nexora_builtin_presets(), $custom );
}

/**
 * Active preset id.
 *
 * @return string
 */
function nexora_active_preset_id() {
	$id      = get_option( 'nexora_active_preset', 'classic-red' );
	$presets = nexora_presets();
	return isset( $presets[ $id ] ) ? $id : 'classic-red';
}

/**
 * Active preset definition.
 *
 * @return array
 */
function nexora_active_preset() {
	$presets = nexora_presets();
	return $presets[ nexora_active_preset_id() ];
}

/**
 * Save a user preset.
 *
 * @param string $id     Slug.
 * @param string $name   Display name.
 * @param array  $colors Sanitised colours.
 * @return string Final id.
 */
function nexora_save_preset( $id, $name, array $colors ) {
	$custom  = get_option( 'nexora_presets', array() );
	$custom  = is_array( $custom ) ? $custom : array();
	$builtin = nexora_builtin_presets();
	$id      = sanitize_key( $id );
	if ( '' === $id || isset( $builtin[ $id ] ) ) {
		$id = sanitize_key( sanitize_title( $name ) );
		if ( '' === $id ) {
			$id = 'preset';
		}
		$base = $id;
		$i    = 2;
		while ( isset( $builtin[ $id ] ) || isset( $custom[ $id ] ) ) {
			$id = $base . '-' . $i++;
		}
	}
	$custom[ $id ] = array(
		'name'    => sanitize_text_field( $name ),
		'builtin' => false,
		'colors'  => nexora_sanitize_preset_colors( $colors ),
	);
	update_option( 'nexora_presets', $custom, false );
	return $id;
}

/**
 * Delete a user preset.
 *
 * @param string $id Slug.
 * @return bool
 */
function nexora_delete_preset( $id ) {
	$custom = get_option( 'nexora_presets', array() );
	if ( ! is_array( $custom ) || ! isset( $custom[ $id ] ) ) {
		return false;
	}
	unset( $custom[ $id ] );
	update_option( 'nexora_presets', $custom, false );
	if ( nexora_active_preset_id() === $id ) {
		update_option( 'nexora_active_preset', 'classic-red' );
	}
	return true;
}

/**
 * Validate a colour map against the token list.
 *
 * @param array $colors Raw colours.
 * @return array
 */
function nexora_sanitize_preset_colors( array $colors ) {
	$defaults = nexora_builtin_presets()['classic-red']['colors'];
	$clean    = array();
	foreach ( nexora_preset_tokens() as $key => $meta ) {
		$hex = isset( $colors[ $key ] ) ? sanitize_hex_color( $colors[ $key ] ) : null;
		$clean[ $key ] = $hex ? $hex : ( $defaults[ $key ] ?? '#000000' );
	}
	return $clean;
}

/**
 * Convert #rrggbb to "r, g, b".
 *
 * @param string $hex Hex colour.
 * @return string
 */
function nexora_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '0, 0, 0';
	}
	return hexdec( substr( $hex, 0, 2 ) ) . ', ' . hexdec( substr( $hex, 2, 2 ) ) . ', ' . hexdec( substr( $hex, 4, 2 ) );
}

/**
 * Darken a hex colour by a percentage (used for derived tokens).
 *
 * @param string $hex Hex colour.
 * @param int    $pct Percentage 0-100.
 * @return string
 */
function nexora_shade( $hex, $pct ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 6 !== strlen( $hex ) ) {
		return '#' . $hex;
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$c    = hexdec( substr( $hex, $i * 2, 2 ) );
		$c    = max( 0, min( 255, (int) round( $c * ( 100 - $pct ) / 100 ) ) );
		$out .= str_pad( dechex( $c ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}

/**
 * Build CSS custom properties for a colour map.
 *
 * @param array $colors Sanitised colours.
 * @return string
 */
function nexora_preset_css_vars( array $colors ) {
	$vars = array();
	foreach ( nexora_preset_tokens() as $key => $meta ) {
		if ( isset( $colors[ $key ] ) ) {
			$vars[] = $meta[1] . ':' . $colors[ $key ];
		}
	}
	$vars[] = '--theme-primary-rgb:' . nexora_hex_to_rgb( $colors['primary'] );
	$vars[] = '--theme-primary-active:' . nexora_shade( $colors['primary'], 22 );
	$vars[] = '--theme-primary-text:' . nexora_shade( $colors['primary'], 35 );
	$vars[] = '--theme-danger-rgb:' . nexora_hex_to_rgb( $colors['danger'] );
	$vars[] = '--theme-success-rgb:' . nexora_hex_to_rgb( $colors['success'] );
	return ':root{' . implode( ';', $vars ) . '}';
}

/**
 * Inline CSS for the active preset + typography choices.
 *
 * @return string
 */
function nexora_preset_css() {
	$preset = nexora_active_preset();
	$css    = nexora_preset_css_vars( $preset['colors'] );

	$fonts = array(
		'iranyekan' => '"primary-font", "IRANYekan", Tahoma, system-ui, sans-serif',
		'pinar'     => '"secondary-font", "primary-font", Tahoma, system-ui, sans-serif',
		'inter'     => '"Inter", "Segoe UI", system-ui, -apple-system, Roboto, sans-serif',
		'system'    => 'system-ui, -apple-system, "Segoe UI", Roboto, Tahoma, sans-serif',
	);
	$body    = nexora_option( 'typography', 'body_font', 'iranyekan' );
	$heading = nexora_option( 'typography', 'heading_font', 'pinar' );
	$size    = (int) nexora_option( 'typography', 'base_size', 14 );
	$radius  = nexora_option( 'typography', 'radius', 'sharp' );
	$radii   = array(
		'sharp' => array( 0, '3px', '4px', '8px' ),
		'soft'  => array( '4px', '6px', '8px', '12px' ),
		'round' => array( '999px', '10px', '14px', '18px' ),
	);
	$r = $radii[ $radius ] ?? $radii['sharp'];

	$css .= ':root{--font-body:' . ( $fonts[ $body ] ?? $fonts['iranyekan'] ) . ';--font-heading:' . ( $fonts[ $heading ] ?? $fonts['pinar'] ) . ';';
	$css .= '--fs-base:' . ( $size / 16 ) . 'rem;--radius-none:' . $r[0] . ';--radius-sm:' . $r[1] . ';--radius-md:' . $r[2] . ';--radius-lg:' . $r[3] . '}';

	return apply_filters( 'nexora_preset_css', $css );
}
