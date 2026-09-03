<?php
/**
 * Category pills above the posts grid.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_cats = get_categories( array( 'hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC' ) );
if ( count( $nexora_cats ) < 2 ) {
	return;
}
$nexora_current = is_category() ? get_queried_object_id() : 0;
$nexora_blog    = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
?>
<nav class="tabs tabs--pills blog-cats" aria-label="<?php esc_attr_e( 'Post categories', 'nexora' ); ?>">
	<div class="tabs__list">
		<a class="tabs__tab" href="<?php echo esc_url( $nexora_blog ); ?>" <?php echo $nexora_current ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'All', 'nexora' ); ?></a>
		<?php foreach ( $nexora_cats as $nexora_c ) : ?>
			<a class="tabs__tab" href="<?php echo esc_url( get_category_link( $nexora_c ) ); ?>" <?php echo $nexora_current === $nexora_c->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $nexora_c->name ); ?> <span class="badge badge--soft"><?php echo esc_html( nexora_num( $nexora_c->count ) ); ?></span></a>
		<?php endforeach; ?>
	</div>
</nav>
