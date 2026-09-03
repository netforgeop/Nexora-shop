<?php
/**
 * My Account wrapper (sidebar + panel).
 *
 * @package Nexora
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="section section--sm account-page" data-account-page>
	<div class="container">
		<div class="with-sidebar">
			<aside class="with-sidebar__aside"><?php do_action( 'woocommerce_account_navigation' ); ?></aside>
			<div class="woocommerce-MyAccount-content account-content">
				<?php woocommerce_output_all_notices(); ?>
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>
		</div>
	</div>
</section>
