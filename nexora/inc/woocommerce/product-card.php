<?php
/**
 * Product card + carousels + grids + product queries for homepage sections.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render one product card (used by loop & AJAX).
 *
 * @param WC_Product|int $product Product.
 * @param array          $args    view (grid|list), flash (bool), priority (bool).
 */
function nexora_product_card( $product, array $args = array() ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product || ! $product->is_visible() ) {
		return;
	}
	$args = wp_parse_args( $args, array( 'view' => 'grid', 'flash' => false, 'priority' => false ) );
	$GLOBALS['product'] = $product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	set_query_var( 'nexora_card_args', $args );
	wc_get_template_part( 'content', 'product' );
}

/**
 * Colour swatches for a variable product (from the colour attribute).
 *
 * @param WC_Product $product Product.
 * @return array [ [name, hex] ]
 */
function nexora_product_swatches( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}
	$attr  = 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) );
	$terms = wc_get_product_terms( $product->get_id(), $attr, array( 'fields' => 'all' ) );
	$out   = array();
	foreach ( $terms as $term ) {
		$hex = get_term_meta( $term->term_id, 'nexora_color', true );
		if ( ! $hex ) {
			$hex = nexora_color_from_name( $term->slug );
		}
		$out[] = array( $term->name, $hex );
	}
	return $out;
}

/**
 * Best-effort colour from a slug (no hex stored yet).
 *
 * @param string $slug Slug.
 * @return string
 */
function nexora_color_from_name( $slug ) {
	$map = array(
		'black' => '#111111', 'white' => '#f4f4f4', 'silver' => '#c9c9c9', 'gray' => '#8a8a8a', 'grey' => '#8a8a8a', 'red' => '#d64545', 'blue' => '#3b6ea5', 'navy' => '#1f2a44', 'green' => '#2f5d47', 'yellow' => '#f6d014', 'gold' => '#d4af37', 'brown' => '#5a3a22', 'tan' => '#b5651d', 'beige' => '#d9c8a9', 'pink' => '#e39bb7', 'purple' => '#6f42c1', 'orange' => '#f28c28', 'mustard' => '#d7a21a', 'burgundy' => '#6e1c2a', 'camel' => '#c19a6b',
		'مشکی' => '#111111', 'سفید' => '#f4f4f4', 'نقره‌ای' => '#c9c9c9', 'قرمز' => '#d64545', 'آبی' => '#3b6ea5', 'سرمه‌ای' => '#1f2a44', 'سبز' => '#2f5d47', 'زرد' => '#f6d014', 'قهوه‌ای' => '#5a3a22', 'خردلی' => '#d7a21a', 'زرشکی' => '#6e1c2a',
	);
	$key = urldecode( $slug );
	return $map[ $key ] ?? '#cccccc';
}

/**
 * Query product IDs for a homepage section.
 *
 * @param string $source   featured|sale|newest|best|rating|category|manual.
 * @param int    $count    Number.
 * @param array  $extra    category (term id), products (ids).
 * @return int[]
 */
function nexora_query_products( $source, $count, array $extra = array() ) {
	$key   = 'nexora_q_' . md5( wp_json_encode( array( $source, $count, $extra ) ) );
	$cache = get_transient( $key );
	if ( is_array( $cache ) ) {
		return $cache;
	}
	$args = array(
		'status'     => 'publish',
		'limit'      => (int) $count,
		'return'     => 'ids',
		'visibility' => 'catalog',
	);
	switch ( $source ) {
		case 'featured':
			$args['featured'] = true;
			break;
		case 'sale':
			$args['include'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
			$args['orderby'] = 'popularity';
			break;
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'best':
			$args['orderby'] = 'popularity';
			break;
		case 'rating':
			$args['orderby'] = 'rating';
			break;
		case 'category':
			if ( ! empty( $extra['category'] ) ) {
				$term = get_term( (int) $extra['category'], 'product_cat' );
				if ( $term && ! is_wp_error( $term ) ) {
					$args['category'] = array( $term->slug );
				}
			}
			break;
		case 'manual':
			if ( ! empty( $extra['products'] ) ) {
				$args['include'] = array_map( 'absint', (array) $extra['products'] );
				$args['orderby'] = 'include';
			} else {
				return array();
			}
			break;
	}
	$ids = wc_get_products( $args );
	// Fall back so an empty "featured" list still shows something on a fresh store.
	if ( empty( $ids ) && in_array( $source, array( 'featured', 'sale', 'rating' ), true ) ) {
		$ids = wc_get_products( array( 'status' => 'publish', 'limit' => (int) $count, 'return' => 'ids', 'orderby' => 'date' ) );
	}
	set_transient( $key, $ids, 10 * MINUTE_IN_SECONDS );
	return $ids;
}

/**
 * Flush product query caches whenever a product changes.
 */
function nexora_flush_product_cache() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nexora_q_%' OR option_name LIKE '_transient_timeout_nexora_q_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
add_action( 'save_post_product', 'nexora_flush_product_cache' );
add_action( 'woocommerce_product_set_stock', 'nexora_flush_product_cache' );
add_action( 'nexora_options_saved', 'nexora_flush_product_cache' );

/**
 * Carousel of product cards.
 *
 * @param int[]  $ids  Product ids.
 * @param string $id   Carousel id (binds nav buttons).
 * @param array  $args flash, columns.
 */
function nexora_product_carousel( array $ids, $id, array $args = array() ) {
	if ( empty( $ids ) ) {
		return;
	}
	$args = wp_parse_args( $args, array( 'flash' => false, 'columns' => 4 ) );
	echo '<div class="product-carousel" data-reveal><div class="swiper" data-swiper="products" data-carousel-id="' . esc_attr( $id ) . '" data-slides-xl="' . (int) $args['columns'] . '" data-slides-xxl="' . (int) $args['columns'] . '"><div class="swiper-wrapper">';
	foreach ( $ids as $pid ) {
		echo '<div class="swiper-slide">';
		nexora_product_card( $pid, array( 'flash' => $args['flash'] ) );
		echo '</div>';
	}
	echo '</div></div></div>';
}

/**
 * Grid of product cards.
 *
 * @param int[] $ids  Product ids.
 * @param array $args columns, flash.
 */
function nexora_product_grid( array $ids, array $args = array() ) {
	if ( empty( $ids ) ) {
		return;
	}
	$args = wp_parse_args( $args, array( 'columns' => 4, 'flash' => false ) );
	echo '<div class="product-grid product-grid--' . (int) $args['columns'] . '" data-reveal>';
	foreach ( $ids as $pid ) {
		nexora_product_card( $pid, array( 'flash' => $args['flash'] ) );
	}
	echo '</div>';
}

/**
 * Compact ranked list item ("collections").
 *
 * @param WC_Product $product Product.
 * @param int        $rank    Rank number.
 */
function nexora_product_mini( $product, $rank = 0 ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
	if ( ! $product ) {
		return;
	}
	echo '<article class="product-mini' . ( $rank ? ' product-mini--ranked' : '' ) . '">';
	if ( $rank ) {
		echo '<span class="product-mini__rank" aria-hidden="true">' . esc_html( nexora_num( $rank ) ) . '</span>';
	}
	echo '<a class="product-mini__media" href="' . esc_url( $product->get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . $product->get_image( 'nexora-thumb', array( 'loading' => 'lazy' ) ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div><h3 class="product-mini__title"><a href="' . esc_url( $product->get_permalink() ) . '">' . esc_html( $product->get_name() ) . '</a></h3>';
	if ( nexora_option( 'shop', 'card_rating' ) ) {
		echo nexora_rating_html( $product->get_average_rating(), null, array( 'show_count' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<div class="price price--sm"><span class="price__current">' . wp_kses_post( $product->get_price_html() ) . '</span></div></div></article>';
}

/**
 * Product price block in theme markup.
 *
 * @param WC_Product $product Product.
 * @param string     $size    ''|sm|lg.
 * @param bool       $discount Show % badge.
 * @return string
 */
function nexora_price_html( $product, $size = '', $discount = false ) {
	$html = '<div class="price' . ( $size ? ' price--' . esc_attr( $size ) : '' ) . '">';
	if ( $product->is_on_sale() && ! $product->is_type( 'variable' ) && ! $product->is_type( 'grouped' ) ) {
		$html .= '<span class="price__current">' . wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ) . '</span>';
		$html .= '<s class="price__old"><span class="visually-hidden">' . esc_html__( 'Regular price', 'nexora' ) . ' </span>' . wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ) ) . '</s>';
		if ( $discount ) {
			$pct = nexora_discount_percent( $product );
			if ( $pct ) {
				$html .= '<span class="price__discount">' . esc_html( nexora_num( $pct ) ) . '%</span>';
			}
		}
	} else {
		$html .= '<span class="price__current">' . wp_kses_post( $product->get_price_html() ) . '</span>';
	}
	return $html . '</div>';
}

/**
 * Badges for a product.
 *
 * @param WC_Product $product Product.
 * @param int        $max     Max badges.
 * @return string
 */
function nexora_product_badges( $product, $max = 2 ) {
	if ( ! nexora_option( 'shop', 'card_badges' ) ) {
		return '';
	}
	$out = array();
	$pct = nexora_discount_percent( $product );
	if ( $pct ) {
		/* translators: %s: percent */
		$out[] = nexora_badge( sprintf( __( '%s%% off', 'nexora' ), nexora_num( $pct ) ), 'discount' );
	} elseif ( $product->is_on_sale() ) {
		$out[] = nexora_badge( __( 'Sale', 'nexora' ), 'discount' );
	}
	if ( ! $product->is_in_stock() ) {
		$out[] = nexora_badge( __( 'Out of stock', 'nexora' ), 'out' );
	} elseif ( nexora_product_is_new( $product ) ) {
		$out[] = nexora_badge( __( 'New', 'nexora' ), 'new' );
	} elseif ( $product->is_featured() ) {
		$out[] = nexora_badge( __( 'Best seller', 'nexora' ), 'hot' );
	}
	return implode( '', array_slice( $out, 0, $max ) );
}

/**
 * Colour attribute term meta (hex) — adds a colour picker to the attribute term screen.
 */
function nexora_color_term_fields() {
	$attr = 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_color_attr', 'color' ) );
	if ( ! taxonomy_exists( $attr ) ) {
		return;
	}
	add_action(
		$attr . '_add_form_fields',
		static function () {
			echo '<div class="form-field"><label for="nexora_color">' . esc_html__( 'Swatch colour', 'nexora' ) . '</label><input type="text" id="nexora_color" name="nexora_color" value="" class="nexora-color-field" data-default-color="#cccccc"><p class="description">' . esc_html__( 'Hex colour used for filter and product-card swatches.', 'nexora' ) . '</p></div>';
		}
	);
	add_action(
		$attr . '_edit_form_fields',
		static function ( $term ) {
			$val = get_term_meta( $term->term_id, 'nexora_color', true );
			echo '<tr class="form-field"><th scope="row"><label for="nexora_color">' . esc_html__( 'Swatch colour', 'nexora' ) . '</label></th><td><input type="text" id="nexora_color" name="nexora_color" value="' . esc_attr( $val ) . '" class="nexora-color-field"></td></tr>';
		}
	);
	$save = static function ( $term_id ) {
		if ( isset( $_POST['nexora_color'] ) && current_user_can( 'manage_product_terms' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WP term forms carry their own nonce, verified by core before this action.
			$hex = sanitize_hex_color( wp_unslash( $_POST['nexora_color'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( $hex ) {
				update_term_meta( $term_id, 'nexora_color', $hex );
			} else {
				delete_term_meta( $term_id, 'nexora_color' );
			}
		}
	};
	add_action( 'created_' . $attr, $save );
	add_action( 'edited_' . $attr, $save );
	add_action(
		'admin_enqueue_scripts',
		static function ( $hook ) use ( $attr ) {
			if ( in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) && isset( $_GET['taxonomy'] ) && $attr === $_GET['taxonomy'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".nexora-color-field").wpColorPicker();});' );
			}
		}
	);
}
add_action( 'init', 'nexora_color_term_fields', 20 );
