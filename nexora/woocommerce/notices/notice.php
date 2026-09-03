<?php
/**
 * Info notice.
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
	<div class="alert alert--info woocommerce-info" role="status" <?php echo wc_get_notice_data_attr( $notice ); ?>><?php nexora_the_icon( 'question-circle', 'sm' ); ?><span><?php echo wc_kses_notice( $notice['notice'] ); ?></span></div>
	<?php
endforeach;
