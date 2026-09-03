<?php
/**
 * Minimal footer.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer site-footer--minimal">
	<div class="footer-bottom footer-bottom--minimal">
		<div class="container">
			<div class="footer-bottom__inner">
				<p><?php nexora_copyright(); ?></p>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_bottom',
						'container'      => false,
						'items_wrap'     => '<ul class="footer-bottom__links">%3$s</ul>',
						'depth'          => 1,
						'fallback_cb'    => false,
						'walker'         => new Nexora_Walker_Flat(),
					)
				);
				?>
			</div>
		</div>
	</div>
</footer>
