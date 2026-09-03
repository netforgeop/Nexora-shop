<?php
/**
 * Woo breadcrumb → theme breadcrumb.
 *
 * @package Nexora
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $breadcrumb ) && function_exists( 'nexora_breadcrumb' ) ) {
	nexora_breadcrumb();
}
