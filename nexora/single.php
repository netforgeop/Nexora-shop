<?php
/**
 * Single post.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_sidebar = nexora_option( 'blog', 'sidebar', 'right' );

while ( have_posts() ) :
	the_post();
	$nexora_cats    = get_the_category();
	$nexora_content = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$nexora_content = str_replace( ']]>', ']]&gt;', $nexora_content );
	$nexora_toc     = array();
	if ( nexora_option( 'blog', 'toc' ) && preg_match_all( '/<h2([^>]*)>(.*?)<\/h2>/iu', $nexora_content, $nexora_m, PREG_SET_ORDER ) && count( $nexora_m ) >= 2 ) {
		foreach ( $nexora_m as $nexora_i => $nexora_h ) {
			$nexora_id = preg_match( '/id="([^"]+)"/', $nexora_h[1], $nexora_idm ) ? $nexora_idm[1] : 'section-' . ( $nexora_i + 1 );
			if ( empty( $nexora_idm[1] ) ) {
				$nexora_content = str_replace( $nexora_h[0], '<h2 id="' . esc_attr( $nexora_id ) . '"' . $nexora_h[1] . '>' . $nexora_h[2] . '</h2>', $nexora_content );
			}
			$nexora_toc[] = array( $nexora_id, wp_strip_all_tags( $nexora_h[2] ) );
			$nexora_idm   = array();
		}
	}
	nexora_breadcrumb();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'section section--sm single-post' ); ?>>
		<div class="container">
			<header class="article-head">
				<?php if ( $nexora_cats && nexora_option( 'blog', 'show_cats' ) ) : ?><a class="badge badge--discount badge--lg article-head__cat" href="<?php echo esc_url( get_category_link( $nexora_cats[0] ) ); ?>"><?php echo esc_html( $nexora_cats[0]->name ); ?></a><?php endif; ?>
				<h1 class="article-head__title"><?php the_title(); ?></h1>
				<div class="article-head__meta">
					<?php if ( nexora_option( 'blog', 'show_author' ) ) : ?><span><?php nexora_the_icon( 'user', 'xs' ); ?><?php echo esc_html( sprintf( /* translators: %s: author */ __( 'By %s', 'nexora' ), get_the_author() ) ); ?></span><?php endif; ?>
					<?php if ( nexora_option( 'blog', 'show_date' ) ) : ?><span><?php nexora_the_icon( 'calendar-full', 'xs' ); ?><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( nexora_num( get_the_date() ) ); ?></time></span><?php endif; ?>
					<?php if ( nexora_option( 'blog', 'show_readtime' ) ) : ?><span><?php nexora_the_icon( 'clock3', 'xs' ); ?><?php echo esc_html( sprintf( /* translators: %s: minutes */ __( '%s min read', 'nexora' ), nexora_num( nexora_reading_time() ) ) ); ?></span><?php endif; ?>
					<?php if ( comments_open() || get_comments_number() ) : ?><span><?php nexora_the_icon( 'bubble', 'xs' ); ?><?php echo esc_html( sprintf( /* translators: %s: count */ _n( '%s comment', '%s comments', get_comments_number(), 'nexora' ), nexora_num( get_comments_number() ) ) ); ?></span><?php endif; ?>
				</div>
			</header>
			<?php if ( has_post_thumbnail() ) : ?><figure class="article-hero"><?php the_post_thumbnail( 'nexora-post-wide', array( 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?></figure><?php endif; ?>

			<div class="<?php echo 'none' === $nexora_sidebar ? 'article-single-col' : 'with-sidebar' . ( 'left' === $nexora_sidebar ? ' with-sidebar--start' : ' with-sidebar--end' ); ?>">
				<div class="article-body">
					<?php if ( $nexora_toc ) : ?>
						<nav class="card-surface card-surface--pad article-toc" aria-label="<?php esc_attr_e( 'Table of contents', 'nexora' ); ?>">
							<h2 class="h6"><?php esc_html_e( 'Table of contents', 'nexora' ); ?></h2>
							<ol class="stack--sm article-toc__list">
								<?php foreach ( $nexora_toc as $nexora_t ) : ?><li><a class="link" href="#<?php echo esc_attr( $nexora_t[0] ); ?>"><?php echo esc_html( $nexora_t[1] ); ?></a></li><?php endforeach; ?>
							</ol>
						</nav>
					<?php endif; ?>
					<div class="prose"><?php echo $nexora_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php
					wp_link_pages( array( 'before' => '<nav class="pagination pagination--pages">', 'after' => '</nav>', 'link_before' => '<span class="pagination__link">', 'link_after' => '</span>' ) );
					$nexora_tags = get_the_tags();
					if ( $nexora_tags ) :
						?>
						<div class="article-tags"><?php nexora_the_icon( 'tags', 'sm' ); ?><?php foreach ( $nexora_tags as $nexora_t ) : ?><a class="chip" href="<?php echo esc_url( get_tag_link( $nexora_t ) ); ?>"><?php echo esc_html( $nexora_t->name ); ?></a><?php endforeach; ?></div>
					<?php endif; ?>
					<?php if ( nexora_option( 'blog', 'share' ) ) { get_template_part( 'template-parts/components/share', null, array( 'url' => get_permalink(), 'title' => get_the_title() ) ); } ?>
					<?php if ( nexora_option( 'blog', 'author_box' ) ) : ?>
						<div class="article-author">
							<?php nexora_avatar_initial( get_the_author(), 'avatar--lg avatar--brand' ); ?>
							<div>
								<div class="small text-muted"><?php esc_html_e( 'About the author', 'nexora' ); ?></div>
								<div class="article-author__name"><?php the_author(); ?></div>
								<?php if ( get_the_author_meta( 'description' ) ) : ?><p class="article-author__bio"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p><?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php
					if ( nexora_option( 'blog', 'prev_next' ) ) :
						$nexora_prev = get_previous_post();
						$nexora_next = get_next_post();
						if ( $nexora_prev || $nexora_next ) :
							?>
							<nav class="article-nav" aria-label="<?php esc_attr_e( 'Previous / next post', 'nexora' ); ?>">
								<?php if ( $nexora_prev ) : ?>
									<a class="article-nav__link article-nav__link--prev" href="<?php echo esc_url( get_permalink( $nexora_prev ) ); ?>"><span class="article-nav__label"><?php nexora_the_icon( 'arrow-right', 'xs', 'icon--flip-ltr' ); ?><?php esc_html_e( 'Previous post', 'nexora' ); ?></span><span class="article-nav__title clamp-2"><?php echo esc_html( get_the_title( $nexora_prev ) ); ?></span></a>
								<?php else : ?><span></span><?php endif; ?>
								<?php if ( $nexora_next ) : ?>
									<a class="article-nav__link article-nav__link--next" href="<?php echo esc_url( get_permalink( $nexora_next ) ); ?>"><span class="article-nav__label"><?php esc_html_e( 'Next post', 'nexora' ); ?><?php nexora_the_icon( 'arrow-left', 'xs', 'icon--flip-ltr' ); ?></span><span class="article-nav__title clamp-2"><?php echo esc_html( get_the_title( $nexora_next ) ); ?></span></a>
								<?php else : ?><span></span><?php endif; ?>
							</nav>
							<?php
						endif;
					endif;
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php if ( 'none' !== $nexora_sidebar ) { get_sidebar(); } ?>
			</div>
		</div>
	</article>
	<?php
	$nexora_rel_n = (int) nexora_option( 'blog', 'related_count', 3 );
	if ( $nexora_rel_n > 0 ) :
		$nexora_rel = new WP_Query(
			array(
				'posts_per_page'      => $nexora_rel_n,
				'post__not_in'        => array( get_the_ID() ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				'category__in'        => wp_list_pluck( $nexora_cats, 'term_id' ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $nexora_rel->have_posts() ) :
			?>
			<section class="section bg-surface related-posts" aria-labelledby="sec-related-posts">
				<div class="container">
					<?php nexora_section_head( array( 'title' => __( 'Related posts', 'nexora' ), 'id' => 'sec-related-posts', 'link' => get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ), 'link_text' => __( 'All posts', 'nexora' ), 'reveal' => false ) ); ?>
					<div class="row g-4">
						<?php
						while ( $nexora_rel->have_posts() ) :
							$nexora_rel->the_post();
							?>
							<div class="col-md-6 col-lg-4"><?php get_template_part( 'template-parts/blog/post-card' ); ?></div>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				</div>
			</section>
			<?php
		endif;
	endif;
endwhile;
get_footer();
