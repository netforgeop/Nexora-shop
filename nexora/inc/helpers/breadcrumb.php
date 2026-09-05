<?php
/**
 * Breadcrumb trail (theme markup, WooCommerce-aware, JSON-LD emitted by inc/seo.php).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the crumb list: array of [label, url|null].
 *
 * @return array
 */
function nexora_breadcrumb_items() {
	$items = array( array( __( 'Home', 'nexora' ), home_url( '/' ) ) );

	if ( is_front_page() ) {
		return array();
	}

	$is_wc = class_exists( 'WooCommerce' );

	if ( $is_wc && ( is_shop() || is_product_taxonomy() || is_product() ) ) {
		$shop_id = wc_get_page_id( 'shop' );
		$items[] = array( $shop_id > 0 ? get_the_title( $shop_id ) : __( 'Shop', 'nexora' ), is_shop() ? null : wc_get_page_permalink( 'shop' ) );

		if ( is_product_taxonomy() ) {
			$term = get_queried_object();
			if ( $term && 'product_cat' === $term->taxonomy ) {
				$ancestors = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
				foreach ( $ancestors as $anc ) {
					$a       = get_term( $anc, 'product_cat' );
					$items[] = array( $a->name, get_term_link( $a ) );
				}
			}
			$items[] = array( $term->name, null );
		} elseif ( is_product() ) {
			$terms = wc_get_product_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
			if ( $terms ) {
				$main      = $terms[0];
				$ancestors = array_reverse( get_ancestors( $main->term_id, 'product_cat' ) );
				foreach ( $ancestors as $anc ) {
					$a       = get_term( $anc, 'product_cat' );
					$items[] = array( $a->name, get_term_link( $a ) );
				}
				$items[] = array( $main->name, get_term_link( $main ) );
			}
			$items[] = array( get_the_title(), null );
		}
		return $items;
	}

	if ( is_home() ) {
		$items[] = array( get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Blog', 'nexora' ), null );
	} elseif ( is_singular( 'post' ) ) {
		if ( get_option( 'page_for_posts' ) ) {
			$items[] = array( get_the_title( get_option( 'page_for_posts' ) ), get_permalink( get_option( 'page_for_posts' ) ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
		}
		$items[] = array( get_the_title(), null );
	} elseif ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
		foreach ( $ancestors as $anc ) {
			$items[] = array( get_the_title( $anc ), get_permalink( $anc ) );
		}
		$items[] = array( get_the_title(), null );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array( single_term_title( '', false ), null );
	} elseif ( is_search() ) {
		/* translators: %s: search query */
		$items[] = array( sprintf( __( 'Search: %s', 'nexora' ), get_search_query() ), null );
	} elseif ( is_author() ) {
		$items[] = array( get_the_author(), null );
	} elseif ( is_date() ) {
		$items[] = array( get_the_date( is_year() ? 'Y' : ( is_month() ? 'F Y' : '' ) ), null );
	} elseif ( is_404() ) {
		$items[] = array( __( 'Page not found', 'nexora' ), null );
	} elseif ( is_singular() ) {
		$items[] = array( get_the_title(), null );
	}

	return apply_filters( 'nexora_breadcrumb_items', $items );
}

/**
 * Print the breadcrumb.
 */
function nexora_breadcrumb() {
	static $printed = false;
	if ( $printed ) {
		return; // Header already rendered it — templates may call it safely.
	}
	$printed = true;
	$items   = nexora_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	$last = count( $items ) - 1;
	echo '<div class="container"><nav class="breadcrumb-wrap" aria-label="' . esc_attr__( 'Breadcrumb', 'nexora' ) . '"><ol class="breadcrumb">';
	foreach ( $items as $i => $item ) {
		$is_last = $i === $last;
		echo '<li class="breadcrumb__item"' . ( $is_last ? ' aria-current="page"' : '' ) . '>';
		if ( ! $is_last && $item[1] ) {
			echo '<a href="' . esc_url( $item[1] ) . '">' . ( 0 === $i ? nexora_icon( 'home', 'xs' ) : '' ) . '<span>' . esc_html( $item[0] ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="breadcrumb__sep linear-icon-chevron-left icon--flip-ltr" aria-hidden="true"></span>';
		} else {
			echo '<span class="truncate breadcrumb__current">' . esc_html( $item[0] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav></div>';
}
