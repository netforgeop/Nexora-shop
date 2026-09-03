<?php
/**
 * Gallery: Swiper main + thumbs, PhotoSwipe lightbox.
 *
 * @package Nexora
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
$nexora_ids = nexora_product_gallery_ids( $product );
?>
<div class="gallery" data-gallery>
	<div class="gallery__main swiper" data-gallery-main id="gallery-main">
		<div class="swiper-wrapper">
			<?php if ( ! $nexora_ids ) : ?>
				<div class="swiper-slide"><?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
			<?php
			foreach ( $nexora_ids as $nexora_i => $nexora_aid ) :
				$nexora_full = wp_get_attachment_image_src( $nexora_aid, 'full' );
				if ( ! $nexora_full ) {
					continue;
				}
				?>
				<div class="swiper-slide">
					<a href="<?php echo esc_url( $nexora_full[0] ); ?>" data-pswp-width="<?php echo (int) $nexora_full[1]; ?>" data-pswp-height="<?php echo (int) $nexora_full[2]; ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Click to zoom', 'nexora' ); ?>">
						<?php echo wp_get_attachment_image( $nexora_aid, 'woocommerce_single', false, array( 'alt' => trim( wp_strip_all_tags( get_post_meta( $nexora_aid, '_wp_attachment_image_alt', true ) ) ) ?: $product->get_name(), 0 === $nexora_i ? 'fetchpriority' : 'loading' => 0 === $nexora_i ? 'high' : 'lazy', 'decoding' => 'async', 'data-variation-image' => 0 === $nexora_i ? '1' : '' ) ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="gallery__badges"><?php echo nexora_product_badges( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="gallery__tools">
			<button type="button" class="icon-btn icon-btn--circle" data-action="wishlist" data-id="<?php echo (int) $product->get_id(); ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'nexora' ); ?>" aria-pressed="false"><?php nexora_the_icon( 'heart', 'sm' ); ?></button>
			<button type="button" class="icon-btn icon-btn--circle" data-action="compare" data-id="<?php echo (int) $product->get_id(); ?>" aria-label="<?php esc_attr_e( 'Add to compare', 'nexora' ); ?>" aria-pressed="false"><?php nexora_the_icon( 'compare', 'sm' ); ?></button>
			<?php if ( nexora_option( 'product', 'show_share' ) ) : ?><button type="button" class="icon-btn icon-btn--circle" data-action="share" data-share-url="<?php the_permalink(); ?>" data-share-title="<?php the_title_attribute(); ?>" aria-label="<?php esc_attr_e( 'Share', 'nexora' ); ?>"><?php nexora_the_icon( 'share2', 'sm' ); ?></button><?php endif; ?>
		</div>
		<?php if ( count( $nexora_ids ) > 0 ) : ?>
			<span class="gallery__zoom-hint"><?php nexora_the_icon( 'zoom-in', 'xs' ); ?> <?php esc_html_e( 'Click to zoom', 'nexora' ); ?></span>
		<?php endif; ?>
		<?php if ( count( $nexora_ids ) > 1 ) : ?>
			<button type="button" class="gallery__nav gallery__nav--prev" data-gallery-prev aria-label="<?php esc_attr_e( 'Previous image', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ); ?></button>
			<button type="button" class="gallery__nav gallery__nav--next" data-gallery-next aria-label="<?php esc_attr_e( 'Next image', 'nexora' ); ?>"><?php nexora_the_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ); ?></button>
		<?php endif; ?>
	</div>
	<?php if ( count( $nexora_ids ) > 1 ) : ?>
		<div class="gallery__thumbs swiper" data-gallery-thumbs aria-label="<?php esc_attr_e( 'Product images', 'nexora' ); ?>">
			<div class="swiper-wrapper">
				<?php foreach ( $nexora_ids as $nexora_i => $nexora_aid ) : ?>
					<div class="swiper-slide" role="button" tabindex="0" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: index */ __( 'Image %s', 'nexora' ), nexora_num( $nexora_i + 1 ) ) ); ?>"><?php echo wp_get_attachment_image( $nexora_aid, 'nexora-thumb', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
