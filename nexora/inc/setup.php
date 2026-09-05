<?php
/**
 * Theme setup: supports, menus, image sizes, content width.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

function nexora_setup() {
	load_theme_textdomain( 'nexora', NEXORA_DIR . 'languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/editor.css' ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'custom-background', array( 'default-color' => 'ffffff' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'post-formats', array() );

	// Editor colour palette follows the active preset.
	$preset = nexora_active_preset();
	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Primary', 'nexora' ), 'slug' => 'primary', 'color' => $preset['colors']['primary'] ),
			array( 'name' => __( 'Accent', 'nexora' ), 'slug' => 'accent', 'color' => $preset['colors']['accent'] ),
			array( 'name' => __( 'Text', 'nexora' ), 'slug' => 'text', 'color' => $preset['colors']['text'] ),
			array( 'name' => __( 'Muted', 'nexora' ), 'slug' => 'muted', 'color' => $preset['colors']['text_muted'] ),
			array( 'name' => __( 'Surface', 'nexora' ), 'slug' => 'surface', 'color' => $preset['colors']['surface'] ),
			array( 'name' => __( 'White', 'nexora' ), 'slug' => 'white', 'color' => '#ffffff' ),
		)
	);

	register_nav_menus(
		array(
			'primary'       => __( 'Primary navigation', 'nexora' ),
			'mobile'        => __( 'Mobile drawer menu (falls back to primary)', 'nexora' ),
			'topbar_start'  => __( 'Top bar (start)', 'nexora' ),
			'topbar_end'    => __( 'Top bar (end)', 'nexora' ),
			'footer_1'      => __( 'Footer column 1', 'nexora' ),
			'footer_2'      => __( 'Footer column 2', 'nexora' ),
			'footer_bottom' => __( 'Footer bottom links', 'nexora' ),
			'account'       => __( 'Account sidebar extras', 'nexora' ),
		)
	);

	// Image sizes match the template's intrinsic dimensions.
	add_image_size( 'nexora-hero', 1600, 680, true );
	add_image_size( 'nexora-hero-sm', 900, 383, true );
	add_image_size( 'nexora-banner', 1200, 600, true );
	add_image_size( 'nexora-tile', 800, 600, true );
	add_image_size( 'nexora-post', 800, 500, true );
	add_image_size( 'nexora-post-wide', 1600, 686, true );
	add_image_size( 'nexora-square', 640, 640, true );
	add_image_size( 'nexora-thumb', 160, 160, true );

	$GLOBALS['content_width'] = 1320;
}
add_action( 'after_setup_theme', 'nexora_setup' );

/**
 * Register sizes in the media picker.
 */
function nexora_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'nexora-banner' => __( 'Nexora banner (1200×600)', 'nexora' ),
			'nexora-post'   => __( 'Nexora post (800×500)', 'nexora' ),
			'nexora-square' => __( 'Nexora square (640)', 'nexora' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'nexora_image_size_names' );

/**
 * Excerpt length / more.
 */
add_filter( 'excerpt_length', static function () { return 24; }, 999 );
add_filter( 'excerpt_more', static function () { return '…'; } );

/**
 * Wrap the search form with theme markup.
 */
function nexora_search_form( $form ) {
	$id = uniqid( 'search-' );
	return '<form class="input-icon" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
		. nexora_icon( 'magnifier', 'sm' )
		. '<label class="visually-hidden" for="' . esc_attr( $id ) . '">' . esc_html__( 'Search', 'nexora' ) . '</label>'
		. '<input id="' . esc_attr( $id ) . '" class="form-control" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr__( 'Search…', 'nexora' ) . '">'
		. '</form>';
}
add_filter( 'get_search_form', 'nexora_search_form' );

/**
 * Add the "minimal" page template (auth-style pages without full header/footer).
 */
function nexora_page_templates( $templates ) {
	$templates['page-templates/minimal.php']    = __( 'Nexora: Minimal (no header/footer nav)', 'nexora' );
	$templates['page-templates/full-width.php'] = __( 'Nexora: Full width', 'nexora' );
	$templates['page-templates/contact.php']    = __( 'Nexora: Contact', 'nexora' );
	$templates['page-templates/faq.php']        = __( 'Nexora: FAQ', 'nexora' );
	$templates['page-templates/wishlist.php']   = __( 'Nexora: Wishlist', 'nexora' );
	$templates['page-templates/compare.php']    = __( 'Nexora: Compare', 'nexora' );
	return $templates;
}
add_filter( 'theme_page_templates', 'nexora_page_templates' );

/**
 * Comment form defaults with theme classes.
 */
function nexora_comment_form_defaults( $defaults ) {
	$req = get_option( 'require_name_email' ) ? ' <span class="req">*</span>' : '';
	$commenter = wp_get_current_commenter();

	$defaults['class_form']          = 'review-form';
	$defaults['class_submit']        = 'btn btn--primary';
	$defaults['title_reply_before']  = '<h3 class="review-form__title" id="reply-title">';
	$defaults['title_reply_after']   = '</h3>';
	$defaults['comment_field']       = '<div class="form-group"><label class="form-label" for="comment">' . esc_html__( 'Comment', 'nexora' ) . ' <span class="req">*</span></label><textarea id="comment" class="form-control" name="comment" rows="5" required></textarea></div>';
	$defaults['fields']['author']    = '<div class="form-cols"><div class="form-group"><label class="form-label" for="author">' . esc_html__( 'Name', 'nexora' ) . $req . '</label><input id="author" class="form-control" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '"' . ( $req ? ' required' : '' ) . '></div>';
	$defaults['fields']['email']     = '<div class="form-group"><label class="form-label" for="email">' . esc_html__( 'Email', 'nexora' ) . $req . '</label><input id="email" class="form-control" name="email" type="email" dir="ltr" value="' . esc_attr( $commenter['comment_author_email'] ) . '"' . ( $req ? ' required' : '' ) . '></div></div>';
	$defaults['fields']['url']       = '';
	if ( isset( $defaults['fields']['cookies'] ) ) {
		$consent = empty( $commenter['comment_author_email'] ) ? '' : ' checked';
		$defaults['fields']['cookies'] = '<div class="form-group"><label class="check"><input class="check__input" type="checkbox" name="wp-comment-cookies-consent" id="wp-comment-cookies-consent" value="yes"' . $consent . '><span class="check__box"></span><span class="check__label">' . esc_html__( 'Save my name and email for next time.', 'nexora' ) . '</span></label></div>';
	}
	return $defaults;
}
add_filter( 'comment_form_defaults', 'nexora_comment_form_defaults' );

/**
 * Register a wishlist / compare query var for the fallback URLs.
 */
add_filter(
	'query_vars',
	static function ( $vars ) {
		$vars[] = 'nexora_wishlist';
		$vars[] = 'nexora_compare';
		return $vars;
	}
);
