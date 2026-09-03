<?php
/**
 * Document head + site header.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="color-scheme" content="light">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'nexora' ); ?></a>
<div class="page-progress" id="page-progress" aria-hidden="true"></div>
<?php
nexora_svg_sprite();

if ( nexora_is_minimal_layout() ) {
	get_template_part( 'template-parts/header/minimal' );
} else {
	get_template_part( 'template-parts/header/header' );
}
?>
<main id="main" tabindex="-1">
<?php
if ( ! is_front_page() && ! nexora_is_minimal_layout() ) {
	nexora_breadcrumb();
}
