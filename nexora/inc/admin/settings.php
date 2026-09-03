<?php
/**
 * Theme Settings screen — renders the schema (tabs → sections → fields).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current group from URL.
 *
 * @return string
 */
function nexora_settings_current_group() {
	$schema = nexora_schema();
	$group  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $schema[ $group ] ) ? $group : array_key_first( $schema );
}

/**
 * Save handler.
 */
add_action(
	'admin_init',
	static function () {
		if ( ! isset( $_POST['nexora_settings_group'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		$group = sanitize_key( $_POST['nexora_settings_group'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		nexora_admin_post_check( 'nexora_save_' . $group );
		if ( ! isset( nexora_schema()[ $group ] ) ) {
			wp_die( esc_html__( 'Unknown settings group.', 'nexora' ) );
		}
		if ( isset( $_POST['nexora_reset'] ) ) {
			delete_option( 'nexora_' . $group );
			nexora_admin_flash( __( 'Settings restored to defaults.', 'nexora' ), 'info' );
		} else {
			$raw = isset( $_POST['nexora'] ) && is_array( $_POST['nexora'] ) ? wp_unslash( $_POST['nexora'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			nexora_update_options( $group, nexora_sanitize_group( $group, $raw ) );
			nexora_admin_flash( __( 'Settings saved.', 'nexora' ) );
		}
		do_action( 'nexora_settings_saved', $group );
		wp_safe_redirect( admin_url( 'admin.php?page=nexora-settings&tab=' . $group ) );
		exit;
	}
);

/**
 * Field input name.
 *
 * @param string      $key Key.
 * @param string|null $parent Repeater parent.
 * @param string|null $idx    Row index placeholder.
 * @return string
 */
function nexora_field_name( $key, $parent = null, $idx = null ) {
	return $parent ? 'nexora[' . $parent . '][' . $idx . '][' . $key . ']' : 'nexora[' . $key . ']';
}

/**
 * Render a single field row.
 *
 * @param string $key   Key.
 * @param array  $field Definition.
 * @param mixed  $value Current value.
 * @param array  $ctx   parent/idx for repeater rows.
 */
function nexora_render_field( $key, array $field, $value, array $ctx = array() ) {
	$name = nexora_field_name( $key, $ctx['parent'] ?? null, $ctx['idx'] ?? null );
	$id   = 'nx-' . sanitize_html_class( str_replace( array( '[', ']' ), array( '-', '' ), $name ) );
	$cond = '';
	if ( ! empty( $field['show_if'] ) ) {
		$cond = ' data-show-if="' . esc_attr( $field['show_if'][0] ) . '" data-show-value="' . esc_attr( is_array( $field['show_if'][1] ) ? implode( ',', $field['show_if'][1] ) : $field['show_if'][1] ) . '"';
	}
	if ( 'notice' === $field['type'] ) {
		echo '<div class="nx-field nx-field--notice"' . $cond . '>' . nexora_admin_icon( 'info' ) . '<p>' . wp_kses_post( $field['content'] ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}
	echo '<div class="nx-field nx-field--' . esc_attr( $field['type'] ) . '" data-key="' . esc_attr( $key ) . '"' . $cond . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( 'toggle' !== $field['type'] ) {
		echo '<label class="nx-field__label" for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
	}
	echo '<div class="nx-field__control">';
	switch ( $field['type'] ) {
		case 'text':
		case 'url':
		case 'email':
		case 'tel':
			printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text"%5$s>', esc_attr( 'url' === $field['type'] ? 'url' : $field['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '' );
			break;
		case 'number':
			printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" class="small-text"%4$s%5$s step="%6$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '', isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '', esc_attr( $field['step'] ?? 'any' ) );
			break;
		case 'textarea':
			printf( '<textarea id="%1$s" name="%2$s" rows="%3$d" class="large-text">%4$s</textarea>', esc_attr( $id ), esc_attr( $name ), (int) ( $field['rows'] ?? 3 ), esc_textarea( (string) $value ) );
			break;
		case 'richtext':
			wp_editor( (string) $value, $id, array( 'textarea_name' => $name, 'textarea_rows' => 6, 'media_buttons' => false, 'teeny' => true, 'quicktags' => true ) );
			break;
		case 'toggle':
			printf( '<label class="nx-toggle"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s><span class="nx-toggle__track" aria-hidden="true"></span><span class="nx-toggle__label">%4$s</span></label>', esc_attr( $id ), esc_attr( $name ), checked( (bool) $value, true, false ), esc_html( $field['label'] ) );
			break;
		case 'select':
			$multiple = ! empty( $field['multiple'] );
			printf( '<select id="%1$s" name="%2$s%3$s"%4$s>', esc_attr( $id ), esc_attr( $name ), $multiple ? '[]' : '', $multiple ? ' multiple' : '' );
			foreach ( $field['options'] as $opt => $label ) {
				$selected = $multiple ? in_array( $opt, (array) $value, true ) : (string) $opt === (string) $value;
				printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $opt ), selected( $selected, true, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;
		case 'color':
			printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="nx-color" data-default-color="%4$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), esc_attr( (string) nexora_field_default( $field ) ) );
			break;
		case 'image':
			$value = absint( $value );
			$src   = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
			echo '<div class="nx-image" data-image>';
			printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$d">', esc_attr( $id ), esc_attr( $name ), $value );
			echo '<div class="nx-image__preview' . ( $src ? ' has-image' : '' ) . '">' . ( $src ? '<img src="' . esc_url( $src ) . '" alt="">' : nexora_admin_icon( 'image' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="nx-image__actions"><button type="button" class="button" data-image-select>' . esc_html__( 'Select image', 'nexora' ) . '</button> <button type="button" class="button-link-delete" data-image-remove' . ( $src ? '' : ' hidden' ) . '>' . esc_html__( 'Remove', 'nexora' ) . '</button></div></div>';
			break;
		case 'icon':
			echo '<div class="nx-iconpick" data-iconpick>';
			printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			echo '<button type="button" class="nx-iconpick__btn" data-iconpick-open><span class="icon linear-icon-' . esc_attr( (string) $value ) . '"></span><span class="nx-iconpick__name">' . esc_html( (string) $value ) . '</span></button></div>';
			break;
		case 'link':
			$value = nexora_link_value( $value );
			echo '<div class="nx-link">';
			printf( '<input type="text" name="%1$s[text]" value="%2$s" placeholder="%3$s" class="regular-text">', esc_attr( $name ), esc_attr( $value['text'] ), esc_attr__( 'Label', 'nexora' ) );
			printf( '<input type="url" id="%1$s" name="%2$s[url]" value="%3$s" placeholder="https://" class="regular-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value['url'] ) );
			printf( '<label class="nx-check"><input type="checkbox" name="%1$s[target]" value="1"%2$s> %3$s</label>', esc_attr( $name ), checked( $value['target'], '_blank', false ), esc_html__( 'New tab', 'nexora' ) );
			echo '</div>';
			break;
		case 'term':
			$tax = $field['taxonomy'];
			if ( ! taxonomy_exists( $tax ) ) {
				echo '<p class="nx-muted">' . esc_html__( 'Available once WooCommerce is active.', 'nexora' ) . '</p>';
				printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $name ), esc_attr( is_array( $value ) ? implode( ',', $value ) : (string) $value ) );
				break;
			}
			$terms    = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'number' => 500 ) );
			$multiple = ! empty( $field['multiple'] );
			printf( '<select id="%1$s" name="%2$s%3$s"%4$s class="nx-select"%5$s>', esc_attr( $id ), esc_attr( $name ), $multiple ? '[]' : '', $multiple ? ' multiple' : '', $multiple ? ' data-placeholder="' . esc_attr__( 'Choose…', 'nexora' ) . '"' : '' );
			if ( ! $multiple ) {
				echo '<option value="0">' . esc_html__( '— Select —', 'nexora' ) . '</option>';
			}
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					$sel = $multiple ? in_array( $t->term_id, array_map( 'intval', (array) $value ), true ) : (int) $t->term_id === (int) $value;
					printf( '<option value="%1$d"%2$s>%3$s (%4$s)</option>', $t->term_id, selected( $sel, true, false ), esc_html( $t->name ), esc_html( nexora_num( $t->count ) ) );
				}
			}
			echo '</select>';
			break;
		case 'products':
			$ids = array_filter( array_map( 'absint', (array) $value ) );
			echo '<div class="nx-products" data-products>';
			printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( implode( ',', $ids ) ) );
			echo '<ul class="nx-products__list">';
			foreach ( $ids as $pid ) {
				printf( '<li data-id="%1$d"><span>%2$s</span><button type="button" class="nx-x" data-remove aria-label="%3$s">&times;</button></li>', $pid, esc_html( get_the_title( $pid ) ?: '#' . $pid ), esc_attr__( 'Remove', 'nexora' ) );
			}
			echo '</ul><input type="search" class="regular-text" placeholder="' . esc_attr__( 'Search products by name or SKU…', 'nexora' ) . '" data-products-search autocomplete="off"><ul class="nx-products__results" hidden></ul></div>';
			break;
		case 'sortable':
			$order = array_values( array_unique( array_merge( (array) $value, array_keys( $field['options'] ) ) ) );
			echo '<ul class="nx-sortable" data-sortable>';
			foreach ( $order as $opt ) {
				if ( ! isset( $field['options'][ $opt ] ) ) {
					continue;
				}
				printf( '<li><span class="nx-sortable__handle" aria-hidden="true">%1$s</span>%2$s<input type="hidden" name="%3$s[]" value="%4$s"></li>', nexora_admin_icon( 'menu' ), esc_html( $field['options'][ $opt ] ), esc_attr( $name ), esc_attr( $opt ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</ul>';
			break;
		case 'repeater':
			nexora_render_repeater( $key, $field, is_array( $value ) ? $value : array() );
			break;
	}
	echo '</div>';
	if ( ! empty( $field['description'] ) ) {
		echo '<p class="nx-field__desc">' . wp_kses_post( $field['description'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Repeater UI.
 *
 * @param string $key   Key.
 * @param array  $field Def.
 * @param array  $rows  Rows.
 */
function nexora_render_repeater( $key, array $field, array $rows ) {
	$max = (int) ( $field['max'] ?? 50 );
	echo '<div class="nx-repeater" data-repeater data-max="' . esc_attr( $max ) . '" data-key="' . esc_attr( $key ) . '">';
	echo '<div class="nx-repeater__rows" data-repeater-rows>';
	foreach ( array_values( $rows ) as $i => $row ) {
		nexora_render_repeater_row( $key, $field, $row, (string) $i );
	}
	echo '</div>';
	echo '<script type="text/html" class="nx-repeater__tpl">';
	nexora_render_repeater_row( $key, $field, array(), '__i__' );
	echo '</script>';
	echo '<button type="button" class="button" data-repeater-add>' . nexora_admin_icon( 'plus' ) . esc_html__( 'Add row', 'nexora' ) . '</button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * One repeater row.
 *
 * @param string $key   Parent key.
 * @param array  $field Def.
 * @param array  $row   Values.
 * @param string $idx   Index.
 */
function nexora_render_repeater_row( $key, array $field, array $row, $idx ) {
	$title_key = array_key_first( $field['fields'] );
	foreach ( $field['fields'] as $k => $f ) {
		if ( in_array( $f['type'], array( 'text', 'link' ), true ) ) {
			$title_key = $k;
			break;
		}
	}
	$title = $row[ $title_key ] ?? '';
	if ( is_array( $title ) ) {
		$title = $title['text'] ?? '';
	}
	echo '<div class="nx-repeater__row" data-repeater-row>';
	echo '<div class="nx-repeater__head"><span class="nx-sortable__handle" aria-hidden="true">' . nexora_admin_icon( 'menu' ) . '</span><button type="button" class="nx-repeater__toggle" data-repeater-toggle aria-expanded="false"><span data-row-title>' . esc_html( $title ?: __( 'New row', 'nexora' ) ) . '</span></button><button type="button" class="nx-x" data-repeater-remove aria-label="' . esc_attr__( 'Remove row', 'nexora' ) . '">&times;</button></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="nx-repeater__body" hidden>';
	foreach ( $field['fields'] as $k => $f ) {
		nexora_render_field( $k, $f, $row[ $k ] ?? nexora_field_default( $f ), array( 'parent' => $key, 'idx' => $idx ) );
	}
	echo '</div></div>';
}

/**
 * Render page.
 */
function nexora_render_settings() {
	$schema = nexora_schema();
	$group  = nexora_settings_current_group();
	$def    = $schema[ $group ];
	$values = nexora_options( $group );
	nexora_admin_header( 'nexora-settings', __( 'Theme Settings', 'nexora' ), __( 'Every visible text, image and toggle in the theme lives here. Changes apply instantly on the front end — no cache purge needed unless you run a caching plugin.', 'nexora' ) );
	echo '<div class="nx-settings">';
	echo '<nav class="nx-settings__nav" aria-label="' . esc_attr__( 'Settings groups', 'nexora' ) . '">';
	foreach ( $schema as $slug => $g ) {
		printf( '<a class="nx-settings__navlink%1$s" href="%2$s"><span class="icon linear-icon-%3$s" aria-hidden="true"></span>%4$s</a>', $slug === $group ? ' is-active' : '', esc_url( admin_url( 'admin.php?page=nexora-settings&tab=' . $slug ) ), esc_attr( $g['icon'] ?? 'cog' ), esc_html( $g['title'] ) );
	}
	echo '</nav>';
	echo '<form method="post" class="nx-settings__form" id="nexora-settings-form">';
	wp_nonce_field( 'nexora_save_' . $group, '_nexora_nonce' );
	echo '<input type="hidden" name="nexora_settings_group" value="' . esc_attr( $group ) . '">';
	echo '<div class="nx-settings__head"><div><h2>' . esc_html( $def['title'] ) . '</h2>';
	if ( ! empty( $def['description'] ) ) {
		echo '<p class="nx-muted">' . wp_kses_post( $def['description'] ) . '</p>';
	}
	echo '</div><div class="nx-settings__subnav">';
	foreach ( $def['sections'] as $sid => $section ) {
		printf( '<a href="#nx-sec-%1$s">%2$s</a>', esc_attr( $sid ), esc_html( $section['title'] ) );
	}
	echo '</div></div>';
	foreach ( $def['sections'] as $sid => $section ) {
		echo '<section class="nx-card nx-section" id="nx-sec-' . esc_attr( $sid ) . '"><header class="nx-card__head"><h3>' . esc_html( $section['title'] ) . '</h3></header><div class="nx-card__body">';
		if ( ! empty( $section['description'] ) ) {
			echo '<p class="nx-section__desc">' . wp_kses_post( $section['description'] ) . '</p>';
		}
		foreach ( $section['fields'] as $key => $field ) {
			nexora_render_field( $key, $field, $values[ $key ] ?? nexora_field_default( $field ) );
		}
		echo '</div></section>';
	}
	echo '<div class="nx-savebar"><button type="submit" class="button button-primary button-hero">' . esc_html__( 'Save changes', 'nexora' ) . '</button> <button type="submit" name="nexora_reset" value="1" class="button-link-delete" data-confirm>' . esc_html__( 'Reset this tab to defaults', 'nexora' ) . '</button></div>';
	echo '</form></div>';
	nexora_admin_footer();
}

/**
 * AJAX product search for the "products" field.
 */
add_action(
	'wp_ajax_nexora_admin_product_search',
	static function () {
		nexora_admin_ajax_check();
		if ( ! function_exists( 'wc_get_products' ) ) {
			wp_send_json_success( array() );
		}
		$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$ids = array();
		$store = WC_Data_Store::load( 'product' );
		$ids   = $store->search_products( $q, '', true, false, 20 );
		$out   = array();
		foreach ( array_filter( array_unique( $ids ) ) as $id ) {
			$p = wc_get_product( $id );
			if ( $p ) {
				$out[] = array( 'id' => $id, 'text' => $p->get_formatted_name() );
			}
		}
		wp_send_json_success( $out );
	}
);

/**
 * AJAX: icon list.
 */
add_action(
	'wp_ajax_nexora_admin_icons',
	static function () {
		nexora_admin_ajax_check();
		wp_send_json_success( nexora_icon_names() );
	}
);
