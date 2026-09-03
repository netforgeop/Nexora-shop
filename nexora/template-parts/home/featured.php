<?php
/**
 * Homepage: featured products.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/home/product-section', null, array( 'home' => $args['home'], 'key' => 'featured', 'class' => 'section bg-surface' ) );
