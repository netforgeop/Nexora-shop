<?php
/**
 * Blog sidebar: widgets area with sensible built-in fallbacks.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_area = $args['area'] ?? 'sidebar-blog';
?>
<aside class="with-sidebar__aside" aria-label="<?php esc_attr_e( 'Sidebar', 'nexora' ); ?>">
	<?php
	if ( is_active_sidebar( $nexora_area ) ) {
		dynamic_sidebar( $nexora_area );
	} elseif ( 'sidebar-blog' === $nexora_area ) {
		get_template_part( 'template-parts/blog/sidebar-default' );
	}
	?>
</aside>
