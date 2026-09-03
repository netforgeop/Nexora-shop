<?php
/**
 * Success notice.
 *
 * @package Nexora
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $notices ) {
	return;
}
foreach ( $notices as $notice ) :
	?>
	<div class="alert alert--success woocommerce-message" role="status" <?php echo wc_get_notice_data_attr( $notice ); ?>><?php nexora_the_icon( 'checkmark-circle', 'sm' ); ?><span><?php echo wc_kses_notice( $notice['notice'] ); ?></span></div>
	<?php
endforeach;
