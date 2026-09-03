<?php
/**
 * Blog archive heading + search.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_title = $args['title'] ?? '';
$nexora_sub   = $args['sub'] ?? '';
?>
<div class="page-head__inner page-head__inner--blog">
	<div><h1 class="h3"><?php echo esc_html( $nexora_title ); ?></h1><?php if ( $nexora_sub ) : ?><p class="text-muted"><?php echo wp_kses_post( $nexora_sub ); ?></p><?php endif; ?></div>
	<form class="input-icon blog-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php nexora_the_icon( 'magnifier', 'sm' ); ?>
		<label class="visually-hidden" for="blog-q"><?php esc_html_e( 'Search articles', 'nexora' ); ?></label>
		<input id="blog-q" class="form-control" type="search" name="s" value="<?php echo esc_attr( is_search() ? get_search_query() : '' ); ?>" placeholder="<?php esc_attr_e( 'Search articles', 'nexora' ); ?>">
		<input type="hidden" name="post_type" value="post">
	</form>
</div>
