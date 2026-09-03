<?php
/**
 * Template Name: Minimal (no header/footer chrome)
 * Template Post Type: page
 *
 * Used for login / register / lost password pages and landing pages that need a quiet layout.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-minimal' ); ?>>
		<div class="entry-content"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;
get_footer();
