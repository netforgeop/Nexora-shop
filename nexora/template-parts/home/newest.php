<?php
/**
 * Homepage: newest products.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/home/product-section', null, array( 'home' => $args['home'], 'key' => 'newest', 'class' => 'section' ) );
