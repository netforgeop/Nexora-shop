<?php
/**
 * Minimal header (checkout, auth pages, "Minimal" page template).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="site-header site-header--minimal" data-header data-no-sticky>
	<div class="header-main">
		<div class="container">
			<div class="header-main__inner" style="grid-template-columns: auto 1fr auto;">
				<?php nexora_brand(); ?>
				<span></span>
				<div class="header-actions">
					<?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>
						<span class="header-action header-action--secure"><?php nexora_the_icon( 'lock', 'sm' ); ?><span class="header-action__text"><span class="header-action__value"><?php esc_html_e( 'Secure checkout', 'nexora' ); ?></span></span></span>
					<?php endif; ?>
					<a class="header-action" href="<?php echo esc_url( nexora_shop_url() ); ?>"><?php nexora_the_icon( 'arrow-left', 'sm', 'icon--flip-ltr' ); ?><span class="header-action__text"><span class="header-action__value"><?php esc_html_e( 'Continue shopping', 'nexora' ); ?></span></span></a>
				</div>
			</div>
		</div>
	</div>
</header>
