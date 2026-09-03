<?php
/**
 * Options storage & access.
 *
 * Each schema group is stored in its own option row (`nexora_<group>`) so a
 * save never touches unrelated data. Defaults are resolved from the schema.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default value for a field definition.
 *
 * @param array $field Field definition.
 * @return mixed
 */
function nexora_field_default( array $field ) {
	if ( array_key_exists( 'default', $field ) ) {
		return $field['default'];
	}
	switch ( $field['type'] ) {
		case 'toggle':
			return false;
		case 'number':
			return 0;
		case 'repeater':
		case 'sortable':
			return array();
		case 'link':
			return array( 'text' => '', 'url' => '', 'target' => '' );
		case 'image':
			return 0;
		default:
			return ! empty( $field['multiple'] ) ? array() : '';
	}
}

/**
 * Flat list of fields for a group: key => definition.
 *
 * @param string $group Group id.
 * @return array
 */
function nexora_group_fields( $group ) {
	static $cache = array();
	if ( isset( $cache[ $group ] ) ) {
		return $cache[ $group ];
	}
	$schema = nexora_schema();
	$fields = array();
	if ( isset( $schema[ $group ]['sections'] ) ) {
		foreach ( $schema[ $group ]['sections'] as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				if ( 'notice' === $field['type'] ) {
					continue;
				}
				$fields[ $key ] = $field;
			}
		}
	}
	$cache[ $group ] = $fields;
	return $fields;
}

/**
 * All defaults for a group.
 *
 * @param string $group Group id.
 * @return array
 */
function nexora_group_defaults( $group ) {
	$out = array();
	foreach ( nexora_group_fields( $group ) as $key => $field ) {
		$out[ $key ] = nexora_field_default( $field );
	}
	return $out;
}

/**
 * Saved + default values for a group (memoised per request).
 *
 * @param string $group Group id.
 * @return array
 */
function nexora_options( $group ) {
	static $cache = array();
	if ( isset( $cache[ $group ] ) ) {
		return $cache[ $group ];
	}
	$saved = get_option( 'nexora_' . $group, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$cache[ $group ] = array_merge( nexora_group_defaults( $group ), $saved );
	return $cache[ $group ];
}

/**
 * Read one option.
 *
 * @param string $group   Group id.
 * @param string $key     Field key.
 * @param mixed  $fallback Value when the key is unknown.
 * @return mixed
 */
function nexora_option( $group, $key, $fallback = null ) {
	$options = nexora_options( $group );
	$value   = array_key_exists( $key, $options ) ? $options[ $key ] : $fallback;
	return apply_filters( 'nexora_option', $value, $group, $key );
}

/**
 * Persist a whole group (already sanitised).
 *
 * @param string $group  Group id.
 * @param array  $values Values.
 * @return bool
 */
function nexora_update_options( $group, array $values ) {
	$fields = nexora_group_fields( $group );
	$clean  = array();
	foreach ( $fields as $key => $field ) {
		if ( array_key_exists( $key, $values ) ) {
			$clean[ $key ] = $values[ $key ];
		}
	}
	return update_option( 'nexora_' . $group, $clean, false );
}

/**
 * Convenience: a link field as [text,url,target] with safe fallbacks.
 *
 * @param mixed $value Raw link value.
 * @return array
 */
function nexora_link_value( $value ) {
	$value = is_array( $value ) ? $value : array();
	return array(
		'text'   => isset( $value['text'] ) ? (string) $value['text'] : '',
		'url'    => isset( $value['url'] ) ? (string) $value['url'] : '',
		'target' => ! empty( $value['target'] ) ? '_blank' : '',
	);
}

/**
 * Image field → URL for a given size.
 *
 * @param int|string $value Attachment ID or URL.
 * @param string     $size  Image size.
 * @return string
 */
function nexora_image_url( $value, $size = 'full' ) {
	if ( is_numeric( $value ) && (int) $value > 0 ) {
		$src = wp_get_attachment_image_url( (int) $value, $size );
		return $src ? $src : '';
	}
	if ( is_string( $value ) && '' !== $value ) {
		return esc_url_raw( $value );
	}
	return '';
}

/**
 * Bundled placeholder image URL (theme assets).
 *
 * @param string $path Relative path inside assets/img.
 * @return string
 */
function nexora_asset_img( $path ) {
	return NEXORA_URI . 'assets/img/' . ltrim( $path, '/' );
}

/**
 * Convenience for state flags that must survive across requests.
 *
 * @param string $key   Flag key.
 * @param mixed  $value Value.
 */
function nexora_set_state( $key, $value ) {
	$state         = get_option( 'nexora_state', array() );
	$state         = is_array( $state ) ? $state : array();
	$state[ $key ] = $value;
	update_option( 'nexora_state', $state, false );
}

/**
 * Read a state flag.
 *
 * @param string $key     Flag key.
 * @param mixed  $default Default.
 * @return mixed
 */
function nexora_get_state( $key, $default = null ) {
	$state = get_option( 'nexora_state', array() );
	return is_array( $state ) && array_key_exists( $key, $state ) ? $state[ $key ] : $default;
}
