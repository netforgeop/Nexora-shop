<?php
/**
 * Category grid.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_cats = nexora_top_categories( (int) $nexora_home['categories_count'], array_map( 'absint', (array) $nexora_home['categories_ids'] ) );
if ( ! $nexora_cats ) {
	return;
}
?>
<section class="section section--flush-top" aria-labelledby="sec-categories">
	<div class="container">
		<?php nexora_section_head( array( 'title' => $nexora_home['categories_title'], 'sub' => $nexora_home['categories_sub'], 'id' => 'sec-categories', 'link' => nexora_shop_url() ) ); ?>
		<ul class="category-grid">
			<?php
			foreach ( $nexora_cats as $nexora_i => $nexora_cat ) :
				$nexora_img = (int) get_term_meta( $nexora_cat->term_id, 'thumbnail_id', true );
				?>
				<li data-reveal style="--reveal-delay:<?php echo (int) $nexora_i; ?>0ms">
					<a class="category-card" href="<?php echo esc_url( get_term_link( $nexora_cat ) ); ?>">
						<span class="category-card__media<?php echo $nexora_img ? '' : ' category-card__media--icon'; ?>">
							<?php
							if ( $nexora_img ) {
								echo wp_get_attachment_image( $nexora_img, 'nexora-square', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
							} else {
								nexora_the_icon( nexora_category_icon( $nexora_cat ), 'lg' );
							}
							?>
						</span>
						<span class="category-card__name"><?php echo esc_html( $nexora_cat->name ); ?></span>
						<span class="category-card__count"><?php echo esc_html( sprintf( /* translators: %s: count */ _n( '%s product', '%s products', $nexora_cat->count, 'nexora' ), nexora_num( $nexora_cat->count ) ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
