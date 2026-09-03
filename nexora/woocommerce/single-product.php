<?php
/**
 * Single product wrapper.
 *
 * @package Nexora
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
nexora_breadcrumb();
while ( have_posts() ) :
	the_post();
	wc_get_template_part( 'content', 'single-product' );
endwhile;
get_footer( 'shop' );
