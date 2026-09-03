<?php
/**
 * Template Name: Full width
 * Template Post Type: page
 *
 * Content without the card wrapper or sidebar — for landing pages built with blocks.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-full' ); ?>>
		<?php if ( ! get_post_meta( get_the_ID(), '_nexora_hide_title', true ) ) : ?>
			<header class="section section--sm section--flush-bottom"><div class="container"><h1 class="h2"><?php the_title(); ?></h1></div></header>
		<?php endif; ?>
		<div class="entry-content"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;
get_footer();
