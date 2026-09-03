<?php
/**
 * Testimonials: real WooCommerce reviews or manual entries.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home  = $args['home'];
$nexora_items = array();
if ( 'woocommerce' === $nexora_home['reviews_source'] && class_exists( 'WooCommerce' ) ) {
	$nexora_items = get_transient( 'nexora_home_reviews' );
	if ( ! is_array( $nexora_items ) ) {
		$nexora_items = array();
		$nexora_cmts  = get_comments( array( 'post_type' => 'product', 'status' => 'approve', 'type' => 'review', 'number' => (int) $nexora_home['reviews_count'] * 2, 'meta_key' => 'rating', 'meta_value' => 4, 'meta_compare' => '>=', 'orderby' => 'comment_date', 'order' => 'DESC' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		foreach ( $nexora_cmts as $nexora_c ) {
			if ( mb_strlen( $nexora_c->comment_content ) < 20 ) {
				continue;
			}
			$nexora_items[] = array(
				'name'     => $nexora_c->comment_author,
				'role'     => wc_review_is_from_verified_owner( $nexora_c->comment_ID ) ? __( 'Verified buyer', 'nexora' ) : __( 'Customer', 'nexora' ),
				'rating'   => (int) get_comment_meta( $nexora_c->comment_ID, 'rating', true ),
				'text'     => $nexora_c->comment_content,
				'product'  => (int) $nexora_c->comment_post_ID,
				'verified' => wc_review_is_from_verified_owner( $nexora_c->comment_ID ),
			);
			if ( count( $nexora_items ) >= (int) $nexora_home['reviews_count'] ) {
				break;
			}
		}
		set_transient( 'nexora_home_reviews', $nexora_items, HOUR_IN_SECONDS );
	}
}
if ( ! $nexora_items ) {
	foreach ( (array) $nexora_home['reviews_manual'] as $nexora_m ) {
		if ( $nexora_m['text'] ) {
			$nexora_items[] = array( 'name' => $nexora_m['name'], 'role' => $nexora_m['role'], 'rating' => (int) $nexora_m['rating'], 'text' => $nexora_m['text'], 'product' => 0, 'verified' => false );
		}
	}
}
if ( ! $nexora_items ) {
	return;
}
?>
<section class="section bg-surface" aria-labelledby="sec-reviews">
	<div class="container">
		<?php nexora_section_head( array( 'title' => $nexora_home['reviews_title'], 'sub' => $nexora_home['reviews_sub'], 'id' => 'sec-reviews', 'carousel' => 'reviews' ) ); ?>
		<div class="swiper" data-swiper="reviews" data-carousel-id="reviews" data-reveal>
			<div class="swiper-wrapper">
				<?php foreach ( $nexora_items as $nexora_r ) : ?>
					<div class="swiper-slide">
						<article class="review-card">
							<?php echo nexora_rating_html( $nexora_r['rating'], null, array( 'show_value' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<p class="review-card__text"><?php echo esc_html( $nexora_r['text'] ); ?></p>
							<div class="review-card__author">
								<?php nexora_avatar_initial( $nexora_r['name'] ); ?>
								<div><div class="review-card__name"><?php echo esc_html( $nexora_r['name'] ); ?></div><div class="review-card__meta"><?php echo esc_html( $nexora_r['role'] ); ?></div></div>
							</div>
							<?php if ( $nexora_r['product'] && ( $nexora_rp = wc_get_product( $nexora_r['product'] ) ) ) : // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments ?>
								<a class="review-card__product" href="<?php echo esc_url( $nexora_rp->get_permalink() ); ?>"><?php echo $nexora_rp->get_image( 'nexora-thumb', array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="truncate"><?php echo esc_html( $nexora_rp->get_name() ); ?></span><?php if ( $nexora_r['verified'] ) : ?><span class="review-card__verified"><?php nexora_the_icon( 'checkmark-circle', 'xs' ); ?><?php esc_html_e( 'Verified purchase', 'nexora' ); ?></span><?php endif; ?></a>
							<?php endif; ?>
						</article>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
