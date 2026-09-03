<?php
/**
 * Post card (grid).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'nexora-post', array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( NEXORA_URI . '/assets/img/placeholder.svg' ); ?>" width="800" height="500" alt="" loading="lazy">
		<?php endif; ?>
		<?php if ( nexora_option( 'blog', 'show_cats' ) ) { nexora_post_category_badge(); } ?>
	</a>
	<div class="post-card__body">
		<?php nexora_post_meta( null, true ); ?>
		<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<div class="post-card__footer">
			<?php if ( nexora_option( 'blog', 'show_author' ) ) : ?><span class="post-card__author"><?php nexora_the_icon( 'user', 'xs' ); ?><?php the_author(); ?></span><?php endif; ?>
			<a class="link--arrow small" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></a>
		</div>
	</div>
</article>
