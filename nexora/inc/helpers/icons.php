<?php
/**
 * Icon system: Linearicons font subset (UI glyphs) + SVG sprite (brands/payment).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon names available in the bundled Linearicons subset.
 *
 * @return string[]
 */
function nexora_icon_names() {
	static $names = null;
	if ( null === $names ) {
		$names = array(
			'alarm', 'alarm-ringing', 'apartment', 'apple', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'baby-bottle', 'bag', 'bag-dollar', 'bathtub', 'battery-full', 'bed', 'bicycle', 'bicycle2', 'book', 'bookmark', 'bow-tie', 'box', 'briefcase', 'brush', 'bubble', 'bubble-alert', 'bubble-question', 'bubbles', 'bullhorn', 'bus', 'cake', 'calendar-full', 'camera', 'car', 'car2', 'cart', 'cart-add', 'cart-empty', 'cart-full', 'cart-remove', 'cash-dollar', 'chart-bars', 'check', 'checkmark-circle', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'circle-minus', 'city', 'clock3', 'cloud-check', 'cog', 'coin-dollar', 'compare', 'credit-card', 'cross', 'cross-circle', 'crown', 'cube', 'database', 'desktop', 'diamond', 'dinner', 'download', 'drop', 'dumbbell', 'ellipsis', 'enter', 'envelope', 'exit', 'eye', 'eye-crossed', 'file-empty', 'film', 'flag', 'frame-expand', 'funnel', 'gamepad', 'gift', 'glasses', 'graduation-hat', 'grid', 'hammer', 'hanger', 'happy', 'headphones', 'headset', 'heart', 'heart-pulse', 'hearts', 'history', 'home', 'home2', 'home3', 'home4', 'hourglass', 'inbox', 'joystick', 'key', 'keyboard', 'lamp', 'laptop', 'layers', 'leaf', 'library', 'license', 'link', 'list', 'location', 'lock', 'magnifier', 'map-marker', 'medal-first', 'menu', 'minus', 'moon', 'mouse', 'music-note', 'mustache', 'paint-roller', 'palette', 'paper-plane', 'paw', 'pencil', 'pencil-ruler2', 'percent', 'picture', 'pie-chart', 'pizza', 'plane', 'plus', 'plus-circle', 'power-switch', 'printer', 'question-circle', 'redo', 'refresh', 'register', 'road', 'rocket', 'ruler', 'sad', 'scissors', 'share2', 'shield', 'shield-check', 'ship', 'shirt', 'shoe', 'shovel', 'smartphone', 'smile', 'socks', 'sort-amount-asc', 'sort-amount-desc', 'star', 'star-empty', 'star-half', 'store', 'sun', 'sync', 'tablet2', 'tag', 'tags', 'telephone', 'thumbs-up', 'tie', 'train', 'trash2', 'trophy', 'truck', 'tv', 'umbrella', 'undo', 'upload', 'user', 'user-lock', 'user-plus', 'users', 'wall', 'wallet', 'warning', 'watch', 'wheelchair', 'wrench', 'zoom-in', 'zoom-out',
		);
	}
	return $names;
}

/**
 * Whether an icon exists in the subset.
 *
 * @param string $name Icon name.
 * @return bool
 */
function nexora_icon_exists( $name ) {
	return in_array( $name, nexora_icon_names(), true );
}

/**
 * Font icon markup.
 *
 * @param string $name  Icon name.
 * @param string $size  xs|sm|''|lg|xl.
 * @param string $extra Extra classes.
 * @return string
 */
function nexora_icon( $name, $size = '', $extra = '' ) {
	$name = sanitize_html_class( $name );
	if ( ! nexora_icon_exists( $name ) ) {
		$name = 'tag';
	}
	$class = 'icon' . ( $size ? ' icon--' . sanitize_html_class( $size ) : '' ) . ' linear-icon-' . $name . ( $extra ? ' ' . esc_attr( $extra ) : '' );
	return '<span class="' . $class . '" aria-hidden="true"></span>';
}

/**
 * Echo a font icon.
 *
 * @param string $name  Icon name.
 * @param string $size  Size.
 * @param string $extra Extra classes.
 */
function nexora_the_icon( $name, $size = '', $extra = '' ) {
	echo nexora_icon( $name, $size, $extra ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from sanitised parts.
}

/**
 * Sprite <use> markup for brand/payment icons.
 *
 * @param string $id    Symbol id without the "i-" prefix.
 * @param string $class Extra classes.
 * @return string
 */
function nexora_svg( $id, $class = '' ) {
	$id = sanitize_html_class( $id );
	return '<svg class="svg-icon' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" aria-hidden="true" focusable="false"><use href="#i-' . $id . '"></use></svg>';
}

/**
 * Echo the sprite symbol sheet once per page (in the body).
 */
function nexora_svg_sprite() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	$file = NEXORA_DIR . 'assets/icons/sprite.svg';
	if ( is_readable( $file ) ) {
		// Bundled theme file — trusted; still strip anything unexpected.
		echo wp_kses( file_get_contents( $file ), array_merge( nexora_kses_svg(), array( 'svg' => array( 'xmlns' => true, 'class' => true, 'width' => true, 'height' => true, 'aria-hidden' => true, 'focusable' => true ) ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}

/**
 * Star rating markup (fractional fill).
 *
 * @param float    $rating 0-5.
 * @param int|null $count  Review count.
 * @param array    $args   size, show_value, show_count.
 * @return string
 */
function nexora_rating_html( $rating, $count = null, array $args = array() ) {
	$args   = wp_parse_args( $args, array( 'size' => '', 'show_value' => true, 'show_count' => true ) );
	$value  = max( 0, min( 5, (float) $rating ) );
	$pct    = round( ( $value / 5 ) * 1000 ) / 10;
	$star   = '<svg class="rating__star" aria-hidden="true" focusable="false"><use href="#i-star"></use></svg>';
	$stars  = str_repeat( $star, 5 );
	/* translators: %s: rating value */
	$label  = sprintf( __( 'Rated %s out of 5', 'nexora' ), nexora_num( number_format_i18n( $value, 1 ) ) );
	if ( null !== $count ) {
		/* translators: %s: number of reviews */
		$label .= ' – ' . sprintf( _n( '%s review', '%s reviews', (int) $count, 'nexora' ), nexora_num( $count ) );
	}
	$html  = '<div class="rating' . ( $args['size'] ? ' rating--' . esc_attr( $args['size'] ) : '' ) . '" role="img" aria-label="' . esc_attr( $label ) . '">';
	$html .= '<span class="rating__stars"><span class="rating__row rating__row--empty">' . $stars . '</span><span class="rating__row rating__row--fill" style="inline-size:' . esc_attr( $pct ) . '%">' . $stars . '</span></span>';
	if ( $args['show_value'] ) {
		$html .= '<span class="rating__value" aria-hidden="true">' . esc_html( nexora_num( number_format_i18n( $value, 1 ) ) ) . '</span>';
	}
	if ( $args['show_count'] && null !== $count ) {
		$html .= '<span class="rating__count" aria-hidden="true">(' . esc_html( nexora_num( $count ) ) . ')</span>';
	}
	return $html . '</div>';
}
