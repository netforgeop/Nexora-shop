<?php
/**
 * Central sanitisation for every option type in the schema.
 *
 * Nothing user-supplied is stored without going through nexora_sanitize_field().
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitise one value according to its field definition.
 *
 * @param mixed $value Raw value.
 * @param array $field Field definition.
 * @return mixed
 */
function nexora_sanitize_field( $value, array $field ) {
	switch ( $field['type'] ) {
		case 'text':
		case 'tel':
			return sanitize_text_field( (string) $value );

		case 'textarea':
			return sanitize_textarea_field( (string) $value );

		case 'richtext':
			return wp_kses_post( (string) $value );

		case 'url':
			return esc_url_raw( trim( (string) $value ) );

		case 'email':
			$email = sanitize_email( (string) $value );
			return is_email( $email ) ? $email : '';

		case 'number':
			$n = is_numeric( $value ) ? (float) $value : (float) nexora_field_default( $field );
			if ( isset( $field['min'] ) ) {
				$n = max( (float) $field['min'], $n );
			}
			if ( isset( $field['max'] ) ) {
				$n = min( (float) $field['max'], $n );
			}
			return ( (int) $n == $n ) ? (int) $n : $n; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual

		case 'toggle':
			return in_array( $value, array( 1, '1', true, 'true', 'on' ), true );

		case 'select':
			$options = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
			if ( ! empty( $field['multiple'] ) ) {
				$value = is_array( $value ) ? $value : array();
				return array_values( array_intersect( array_map( 'sanitize_key', $value ), $options ) );
			}
			$value = sanitize_key( (string) $value );
			return in_array( $value, $options, true ) ? $value : nexora_field_default( $field );

		case 'color':
			$hex = sanitize_hex_color( (string) $value );
			return $hex ? $hex : nexora_field_default( $field );

		case 'image':
			return absint( $value );

		case 'icon':
			$icon = sanitize_key( (string) $value );
			return nexora_icon_exists( $icon ) ? $icon : nexora_field_default( $field );

		case 'link':
			$value = is_array( $value ) ? $value : array();
			return array(
				'text'   => sanitize_text_field( $value['text'] ?? '' ),
				'url'    => esc_url_raw( trim( (string) ( $value['url'] ?? '' ) ) ),
				'target' => ! empty( $value['target'] ) ? '_blank' : '',
			);

		case 'term':
			if ( ! empty( $field['multiple'] ) ) {
				$value = is_array( $value ) ? $value : array_filter( explode( ',', (string) $value ) );
				return array_values( array_filter( array_map( 'absint', $value ) ) );
			}
			return absint( $value );

		case 'products':
			$value = is_array( $value ) ? $value : array_filter( explode( ',', (string) $value ) );
			return array_values( array_filter( array_map( 'absint', $value ) ) );

		case 'sortable':
			$allowed = isset( $field['options'] ) ? array_keys( $field['options'] ) : array();
			$value   = is_array( $value ) ? $value : array_filter( explode( ',', (string) $value ) );
			$value   = array_values( array_intersect( array_map( 'sanitize_key', $value ), $allowed ) );
			foreach ( $allowed as $key ) {
				if ( ! in_array( $key, $value, true ) ) {
					$value[] = $key;
				}
			}
			return $value;

		case 'repeater':
			$rows = is_array( $value ) ? array_values( $value ) : array();
			$max  = isset( $field['max'] ) ? (int) $field['max'] : 50;
			$rows = array_slice( $rows, 0, $max );
			$out  = array();
			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$clean = array();
				foreach ( $field['fields'] as $sub_key => $sub_field ) {
					$clean[ $sub_key ] = nexora_sanitize_field( $row[ $sub_key ] ?? nexora_field_default( $sub_field ), $sub_field );
				}
				$out[] = $clean;
			}
			return $out;

		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Sanitise a whole group from a raw request array.
 *
 * @param string $group Group id.
 * @param array  $raw   Raw values (already unslashed).
 * @return array
 */
function nexora_sanitize_group( $group, array $raw ) {
	$clean = array();
	foreach ( nexora_group_fields( $group ) as $key => $field ) {
		if ( 'toggle' === $field['type'] ) {
			// Unchecked checkboxes are absent from POST.
			$clean[ $key ] = isset( $raw[ $key ] ) ? nexora_sanitize_field( $raw[ $key ], $field ) : false;
			continue;
		}
		if ( array_key_exists( $key, $raw ) ) {
			$clean[ $key ] = nexora_sanitize_field( $raw[ $key ], $field );
		} else {
			$clean[ $key ] = nexora_field_default( $field );
		}
	}
	return $clean;
}

/**
 * Allowed HTML for small inline strings (announcements, notes).
 *
 * @return array
 */
function nexora_kses_inline() {
	return array(
		'a'      => array( 'href' => true, 'target' => true, 'rel' => true, 'class' => true ),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array( 'class' => true ),
		'span'   => array( 'class' => true ),
		'mark'   => array( 'class' => true ),
		'br'     => array(),
	);
}

/**
 * Allowed HTML for trust badges (eNAMAD etc.) — images + links only, no scripts.
 *
 * @return array
 */
function nexora_kses_badges() {
	return array(
		'a'   => array( 'href' => true, 'target' => true, 'rel' => true, 'referrerpolicy' => true, 'id' => true, 'class' => true, 'style' => true ),
		'img' => array( 'src' => true, 'alt' => true, 'width' => true, 'height' => true, 'style' => true, 'referrerpolicy' => true, 'id' => true, 'class' => true, 'loading' => true, 'onclick' => false ),
		'div' => array( 'class' => true, 'id' => true, 'style' => true ),
		'span' => array( 'class' => true, 'id' => true, 'style' => true ),
	);
}

/**
 * Whitelist of allowed SVG attributes for inline sprite output.
 *
 * @return array
 */
function nexora_kses_svg() {
	return array(
		'svg'  => array( 'class' => true, 'aria-hidden' => true, 'focusable' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'role' => true, 'xmlns' => true ),
		'use'  => array( 'href' => true, 'xlink:href' => true ),
		'path' => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'fill-rule' => true, 'clip-rule' => true, 'opacity' => true ),
		'g'    => array( 'fill' => true, 'stroke' => true, 'transform' => true ),
		'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ),
		'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true ),
		'symbol' => array( 'id' => true, 'viewbox' => true, 'fill' => true ),
		'defs'   => array(),
		'title'  => array(),
	);
}

/**
 * Allow only a single map iframe from trusted map providers (Google, OpenStreetMap, Neshan, Balad).
 *
 * @param string $html Raw embed code.
 * @return string Safe HTML or empty string.
 */
function nexora_kses_iframe( $html ) {
	if ( ! preg_match( '/<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>/i', (string) $html, $m ) ) {
		return '';
	}
	$src  = esc_url_raw( $m[1] );
	$host = wp_parse_url( $src, PHP_URL_HOST );
	$ok   = array( 'www.google.com', 'maps.google.com', 'www.openstreetmap.org', 'neshan.org', 'balad.ir', 'map.ir' );
	$ok   = apply_filters( 'nexora_map_hosts', $ok );
	$safe = false;
	foreach ( $ok as $h ) {
		if ( $host === $h || ( $host && substr( $host, -strlen( '.' . $h ) ) === '.' . $h ) ) {
			$safe = true;
		}
	}
	if ( ! $safe || 0 !== strpos( $src, 'https://' ) ) {
		return '';
	}
	return '<iframe src="' . esc_url( $src ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="' . esc_attr__( 'Map', 'nexora' ) . '"></iframe>';
}
