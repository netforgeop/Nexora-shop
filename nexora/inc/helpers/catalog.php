<?php
/**
 * Catalogue helpers shared by header, footer, homepage and shop (WooCommerce-aware,
 * safe to call without WooCommerce — they return empty arrays).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Top-level product categories (ordered by count unless ids are given).
 *
 * @param int   $count Number.
 * @param int[] $ids   Specific ids (keeps order).
 * @return WP_Term[]
 */
function nexora_top_categories( $count = 8, array $ids = array() ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$key   = 'nexora_cats_' . md5( $count . '|' . implode( ',', $ids ) . '|' . get_locale() );
	$terms = get_transient( $key );
	if ( false === $terms ) {
		$args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'number'     => $count,
		);
		if ( $ids ) {
			$args['include']    = $ids;
			$args['orderby']    = 'include';
			$args['hide_empty'] = false;
		} else {
			$args['parent']  = 0;
			$args['orderby'] = 'count';
			$args['order']   = 'DESC';
			$args['exclude'] = array( (int) get_option( 'default_product_cat' ) );
		}
		$terms = get_terms( $args );
		$terms = is_wp_error( $terms ) ? array() : $terms;
		set_transient( $key, $terms, HOUR_IN_SECONDS );
	}
	return $terms;
}

/**
 * Flush category caches when terms change.
 */
function nexora_flush_cat_cache() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nexora_cats_%' OR option_name LIKE '_transient_timeout_nexora_cats_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
add_action( 'edited_product_cat', 'nexora_flush_cat_cache' );
add_action( 'create_product_cat', 'nexora_flush_cat_cache' );
add_action( 'delete_product_cat', 'nexora_flush_cat_cache' );
add_action( 'save_post_product', 'nexora_flush_cat_cache' );

/**
 * Icon for a category (term meta `nexora_icon`, else keyword guess, else "tag").
 *
 * @param WP_Term $term Term.
 * @return string
 */
function nexora_category_icon( $term ) {
	$icon = get_term_meta( $term->term_id, 'nexora_icon', true );
	if ( $icon && nexora_icon_exists( $icon ) ) {
		return $icon;
	}
	$map = array(
		'digital'  => 'smartphone', 'electronic' => 'smartphone', 'mobile' => 'smartphone', 'phone' => 'smartphone',
		'laptop'   => 'laptop', 'computer' => 'laptop', 'camera' => 'camera', 'audio' => 'headphones', 'headphone' => 'headphones',
		'fashion'  => 'shirt', 'cloth' => 'shirt', 'apparel' => 'shirt', 'shoe' => 'shirt', 'bag' => 'briefcase', 'watch' => 'watch',
		'home'     => 'lamp', 'kitchen' => 'dinner', 'furniture' => 'lamp', 'decor' => 'lamp',
		'beauty'   => 'leaf', 'cosmetic' => 'leaf', 'health' => 'heart-pulse', 'perfume' => 'leaf',
		'sport'    => 'dumbbell', 'fitness' => 'dumbbell', 'outdoor' => 'dumbbell',
		'book'     => 'book', 'toy' => 'baby-bottle', 'kid' => 'baby-bottle', 'tool' => 'hammer', 'car' => 'car', 'food' => 'dinner', 'pet' => 'paw',
		// Persian keywords.
		'دیجیتال'  => 'smartphone', 'موبایل' => 'smartphone', 'لپ' => 'laptop', 'پوشاک' => 'shirt', 'مد' => 'shirt', 'لباس' => 'shirt',
		'خانه'     => 'lamp', 'آشپزخانه' => 'dinner', 'زیبایی' => 'leaf', 'آرایش' => 'leaf', 'ورزش' => 'dumbbell', 'کتاب' => 'book',
		'اسباب'    => 'baby-bottle', 'ابزار' => 'hammer', 'ساعت' => 'watch', 'کیف' => 'briefcase',
	);
	$hay = mb_strtolower( $term->slug . ' ' . $term->name );
	foreach ( $map as $needle => $icon ) {
		if ( false !== mb_strpos( $hay, $needle ) && nexora_icon_exists( $icon ) ) {
			return $icon;
		}
	}
	return 'tag';
}

/**
 * Category image URL (WooCommerce thumbnail_id) with fallback.
 *
 * @param WP_Term $term Term.
 * @param string  $size Size.
 * @return string
 */
function nexora_category_image( $term, $size = 'nexora-square' ) {
	$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( $url ) {
			return $url;
		}
	}
	return function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( $size ) : NEXORA_URI . 'assets/img/placeholder.svg';
}

/**
 * Children of a category.
 *
 * @param WP_Term $term Term.
 * @param int     $limit Limit.
 * @return WP_Term[]
 */
function nexora_category_children( $term, $limit = 8 ) {
	$kids = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $term->term_id, 'hide_empty' => true, 'number' => $limit, 'orderby' => 'count', 'order' => 'DESC' ) );
	return is_wp_error( $kids ) ? array() : $kids;
}

/**
 * Brands (product_brand terms, or attribute terms) optionally limited to a category.
 *
 * @param int $limit Limit.
 * @param int $cat_id Category id (0 = all).
 * @return WP_Term[]
 */
function nexora_brands( $limit = 12, $cat_id = 0 ) {
	$tax = taxonomy_exists( 'product_brand' ) ? 'product_brand' : 'pa_' . sanitize_key( nexora_option( 'shop', 'filter_brand_attr', 'brand' ) );
	if ( ! taxonomy_exists( $tax ) ) {
		return array();
	}
	$args = array( 'taxonomy' => $tax, 'hide_empty' => true, 'number' => $limit, 'orderby' => 'count', 'order' => 'DESC' );
	if ( $cat_id ) {
		$ids = get_posts( array( 'post_type' => 'product', 'posts_per_page' => 200, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'terms' => $cat_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! $ids ) {
			return array();
		}
		$args['object_ids'] = $ids;
	}
	$terms = get_terms( $args );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * URL for a brand term.
 *
 * @param WP_Term $term Term.
 * @return string
 */
function nexora_brand_url( $term ) {
	if ( 'product_brand' === $term->taxonomy ) {
		$url = get_term_link( $term );
		return is_wp_error( $url ) ? '' : $url;
	}
	return nexora_shop_url( array( 'brand' => $term->slug ) );
}

/**
 * Brand logo (product_brand thumbnail_id / term meta `nexora_logo`).
 *
 * @param WP_Term $term Term.
 * @return string URL or ''.
 */
function nexora_brand_logo( $term ) {
	$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) ?: (int) get_term_meta( $term->term_id, 'nexora_logo', true );
	return $id ? (string) wp_get_attachment_image_url( $id, 'medium' ) : '';
}

/**
 * Fallback primary menu when no menu is assigned (keeps the header usable).
 */
function nexora_primary_fallback() {
	$items = array( array( __( 'Home', 'nexora' ), home_url( '/' ), is_front_page() ) );
	if ( class_exists( 'WooCommerce' ) ) {
		$items[] = array( __( 'Shop', 'nexora' ), nexora_shop_url(), is_shop() );
		$items[] = array( __( 'Offers', 'nexora' ), nexora_shop_url( array( 'on_sale' => 1 ) ), false, 'hot' );
	}
	if ( get_option( 'page_for_posts' ) ) {
		$items[] = array( __( 'Blog', 'nexora' ), get_permalink( get_option( 'page_for_posts' ) ), is_home() );
	}
	foreach ( array( 'page_contact' => __( 'Contact', 'nexora' ), 'page_faq' => __( 'FAQ', 'nexora' ) ) as $k => $label ) {
		if ( nexora_get_state( $k ) ) {
			$items[] = array( $label, get_permalink( nexora_get_state( $k ) ), is_page( nexora_get_state( $k ) ) );
		}
	}
	echo '<ul class="nav">';
	foreach ( $items as $item ) {
		printf(
			'<li class="nav__item%1$s"><a class="nav__link%2$s" href="%3$s"%4$s>%5$s%6$s</a></li>',
			$item[2] ? ' is-active' : '',
			! empty( $item[3] ) ? ' nav__link--hot' : '',
			esc_url( $item[1] ),
			$item[2] ? ' aria-current="page"' : '',
			! empty( $item[3] ) ? nexora_icon( 'alarm-ringing', 'xs' ) : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item[0] )
		);
	}
	if ( current_user_can( 'edit_theme_options' ) ) {
		printf( '<li class="nav__item"><a class="nav__link" href="%1$s" style="opacity:.6">%2$s</a></li>', esc_url( admin_url( 'nav-menus.php' ) ), esc_html__( 'Assign a menu', 'nexora' ) );
	}
	echo '</ul>';
}

/**
 * Category icon term-meta field (Products → Categories).
 */
add_action(
	'product_cat_edit_form_fields',
	static function ( $term ) {
		$icon = get_term_meta( $term->term_id, 'nexora_icon', true );
		echo '<tr class="form-field"><th scope="row"><label for="nexora_icon">' . esc_html__( 'Nexora icon', 'nexora' ) . '</label></th><td>';
		wp_nonce_field( 'nexora_cat_icon', '_nexora_cat_icon' );
		echo '<input type="text" id="nexora_icon" name="nexora_icon" value="' . esc_attr( $icon ) . '" list="nexora-icon-list" class="regular-text" placeholder="smartphone">';
		echo '<datalist id="nexora-icon-list">';
		foreach ( nexora_icon_names() as $n ) {
			echo '<option value="' . esc_attr( $n ) . '"></option>';
		}
		echo '</datalist><p class="description">' . esc_html__( 'Linearicons name used in the category menu and homepage. Leave empty to auto-detect.', 'nexora' ) . '</p></td></tr>';
	}
);
add_action(
	'edited_product_cat',
	static function ( $term_id ) {
		if ( ! isset( $_POST['_nexora_cat_icon'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_nexora_cat_icon'] ), 'nexora_cat_icon' ) || ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		$icon = isset( $_POST['nexora_icon'] ) ? sanitize_key( $_POST['nexora_icon'] ) : '';
		if ( $icon && nexora_icon_exists( $icon ) ) {
			update_term_meta( $term_id, 'nexora_icon', $icon );
		} else {
			delete_term_meta( $term_id, 'nexora_icon' );
		}
	}
);
