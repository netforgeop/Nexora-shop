<?php
/**
 * Horizontal featured post card.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card post-card--horizontal post-card--featured' ); ?> data-reveal>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'nexora-post-wide', array( 'alt' => '', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( NEXORA_URI . '/assets/img/placeholder.svg' ); ?>" width="1600" height="686" alt="">
		<?php endif; ?>
		<span class="badge badge--hot post-card__cat"><?php esc_html_e( 'Featured', 'nexora' ); ?></span>
	</a>
	<div class="post-card__body post-card__body--featured">
		<div class="post-card__meta">
			<?php nexora_post_category_badge( null, '' ); ?>
			<span><?php nexora_the_icon( 'calendar-full', 'xs' ); ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( nexora_num( get_the_date() ) ); ?></time></span>
			<span><?php nexora_the_icon( 'clock3', 'xs' ); ?><?php echo esc_html( sprintf( /* translators: %s: minutes */ __( '%s min read', 'nexora' ), nexora_num( nexora_reading_time() ) ) ); ?></span>
		</div>
		<h2 class="post-card__title post-card__title--lg"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></p>
		<div><a class="btn btn--dark" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a></div>
	</div>
</article>
