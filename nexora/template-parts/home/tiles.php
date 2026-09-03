<?php
/**
 * Category tiles (large image cards).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_tiles = (array) $args['home']['tiles'];
if ( ! $nexora_tiles ) {
	// Auto: top 4 categories with thumbnails.
	foreach ( nexora_top_categories( 4 ) as $nexora_c ) {
		$nexora_tiles[] = array( 'category' => $nexora_c->term_id, 'image' => 0 );
	}
}
$nexora_out = array();
foreach ( $nexora_tiles as $nexora_t ) {
	$nexora_term = $nexora_t['category'] ? get_term( (int) $nexora_t['category'], 'product_cat' ) : null;
	if ( ! $nexora_term || is_wp_error( $nexora_term ) ) {
		continue;
	}
	$nexora_img = $nexora_t['image'] ? wp_get_attachment_image_url( (int) $nexora_t['image'], 'nexora-tile' ) : '';
	if ( ! $nexora_img ) {
		$nexora_img = nexora_category_image( $nexora_term, 'nexora-tile' );
	}
	$nexora_out[] = array( $nexora_term, $nexora_img );
}
if ( ! $nexora_out ) {
	return;
}
?>
<section class="section section--flush-top" aria-label="<?php esc_attr_e( 'Categories', 'nexora' ); ?>">
	<div class="container">
		<div class="promo-grid promo-grid--tiles">
			<?php foreach ( $nexora_out as $nexora_i => $nexora_tile ) : ?>
				<a class="category-tile" href="<?php echo esc_url( get_term_link( $nexora_tile[0] ) ); ?>" data-reveal style="--reveal-delay:<?php echo (int) $nexora_i * 80; ?>ms">
					<img src="<?php echo esc_url( $nexora_tile[1] ); ?>" width="800" height="600" alt="" loading="lazy" decoding="async">
					<span class="category-tile__body"><span class="category-tile__name"><?php echo esc_html( $nexora_tile[0]->name ); ?></span><span class="category-tile__count"><?php echo esc_html( sprintf( /* translators: %s: count */ _n( '%s product', '%s products', $nexora_tile[0]->count, 'nexora' ), nexora_num( $nexora_tile[0]->count ) ) ); ?></span><span class="category-tile__link"><?php esc_html_e( 'View all', 'nexora' ); ?> <?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
