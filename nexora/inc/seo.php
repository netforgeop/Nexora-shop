<?php
/**
 * Light SEO layer: schema.org JSON-LD for site, breadcrumb, product, article.
 * Everything is disabled automatically when a full SEO plugin is active.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Detect SEO plugins that output their own schema.
 *
 * @return bool
 */
function nexora_seo_plugin_active() {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

function nexora_json_ld() {
	if ( nexora_seo_plugin_active() ) {
		return;
	}
	$graph = array();

	if ( is_front_page() ) {
		$graph[] = array(
			'@type'           => 'WebSite',
			'name'            => get_bloginfo( 'name' ),
			'url'             => home_url( '/' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' . ( class_exists( 'WooCommerce' ) ? '&post_type=product' : '' ) ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	$crumbs = nexora_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$list = array();
		foreach ( $crumbs as $i => $c ) {
			$item = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $c[0] ) );
			if ( $c[1] ) {
				$item['item'] = $c[1];
			}
			$list[] = $item;
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title(),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'author'        => array( '@type' => 'Person', 'name' => get_the_author() ),
			'image'         => get_the_post_thumbnail_url( null, 'nexora-post-wide' ) ?: null,
			'description'   => wp_strip_all_tags( get_the_excerpt() ),
			'mainEntityOfPage' => get_permalink(),
		);
	}

	if ( empty( $graph ) ) {
		return;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'nexora_json_ld', 30 );

/**
 * Open Graph basics (skipped when an SEO plugin is present).
 */
function nexora_open_graph() {
	if ( nexora_seo_plugin_active() ) {
		return;
	}
	$title = wp_get_document_title();
	$desc  = is_singular() ? wp_strip_all_tags( get_the_excerpt() ) : get_bloginfo( 'description' );
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
	$image = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : ( get_site_icon_url( 512 ) ?: '' );
	$type  = ( class_exists( 'WooCommerce' ) && is_product() ) ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );

	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( wp_trim_words( $desc, 40 ) ) . '">' . "\n";
		echo '<meta name="description" content="' . esc_attr( wp_trim_words( $desc, 30 ) ) . '">' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'nexora_open_graph', 4 );
