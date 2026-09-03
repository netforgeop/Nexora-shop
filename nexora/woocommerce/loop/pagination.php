<?php
/**
 * Loop pagination.
 *
 * @package Nexora
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( $total <= 1 ) {
	return;
}
nexora_pagination( array( 'total' => $total, 'current' => $current, 'base' => $base, 'format' => $format ) );
