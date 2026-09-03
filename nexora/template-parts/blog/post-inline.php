<?php
/**
 * Small inline post (sidebar).
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="post-inline">
	<a class="post-inline__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'nexora-thumb', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<img src="<?php echo esc_url( NEXORA_URI . '/assets/img/placeholder.svg' ); ?>" width="80" height="80" alt="" loading="lazy">
		<?php endif; ?>
	</a>
	<div>
		<h3 class="post-inline__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<span class="post-inline__date"><?php echo esc_html( nexora_num( get_the_date() ) ); ?></span>
	</div>
</article>
