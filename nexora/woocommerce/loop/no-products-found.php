<?php
/**
 * No products found.
 *
 * @package Nexora
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;
nexora_empty_state( array( 'icon' => 'magnifier', 'title' => __( 'No products found', 'nexora' ), 'text' => __( 'Try removing some filters or searching with a different term.', 'nexora' ), 'cta' => __( 'Clear filters', 'nexora' ), 'href' => nexora_shop_url() ) );
