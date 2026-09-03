<?php
/**
 * Proceed to checkout button.
 *
 * @package Nexora
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn--primary btn--lg btn--block wc-forward"><?php esc_html_e( 'Proceed to checkout', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a>
