<?php
/**
 * Widget areas.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_widgets_init() {
	$areas = array(
		'sidebar-blog'   => array( __( 'Blog sidebar', 'nexora' ), __( 'Shown next to the posts archive and single posts.', 'nexora' ) ),
		'sidebar-shop'   => array( __( 'Shop sidebar', 'nexora' ), __( 'Shown next to the product archive, below the theme filters. WooCommerce filter widgets work here.', 'nexora' ) ),
		'sidebar-page'   => array( __( 'Page sidebar', 'nexora' ), __( 'Optional sidebar for pages using the default template.', 'nexora' ) ),
		'footer-extra'   => array( __( 'Footer extra column', 'nexora' ), __( 'Replaces the newsletter column when it contains widgets.', 'nexora' ) ),
		'product-after'  => array( __( 'Product page: after buy box', 'nexora' ), __( 'Small trust widgets, banners, etc.', 'nexora' ) ),
	);
	foreach ( $areas as $id => $meta ) {
		register_sidebar(
			array(
				'id'            => $id,
				'name'          => $meta[0],
				'description'   => $meta[1],
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget__title">',
				'after_title'   => '</h2>',
			)
		);
	}
}
add_action( 'widgets_init', 'nexora_widgets_init' );
