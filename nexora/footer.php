<?php
/**
 * Site footer + drawers, mobile bar, toasts.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<?php
if ( nexora_is_minimal_layout() ) {
	get_template_part( 'template-parts/footer/minimal' );
} else {
	get_template_part( 'template-parts/footer/footer' );
	get_template_part( 'template-parts/footer/drawers' );
	get_template_part( 'template-parts/footer/mobile-bar' );
}
?>
<div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="false"></div>
<button type="button" class="back-to-top" id="back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-up', 'sm' ); ?></button>
<?php wp_footer(); ?>
</body>
</html>
