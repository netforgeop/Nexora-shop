<?php
/**
 * Template Name: FAQ
 * Template Post Type: page
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

get_header();
$nexora_items  = (array) nexora_option( 'pages', 'faq_items' );
$nexora_groups = array();
foreach ( $nexora_items as $nexora_it ) {
	if ( empty( $nexora_it['question'] ) ) {
		continue;
	}
	$nexora_groups[ $nexora_it['group'] ?: __( 'General', 'nexora' ) ][] = $nexora_it;
}
$nexora_icons = array( 'shipping' => 'truck', 'payment' => 'credit-card', 'returns' => 'undo', 'orders' => 'cart', 'account' => 'user' );
$nexora_contact = get_pages( array( 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/contact.php', 'number' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
$nexora_phone   = nexora_option( 'general', 'phone' );
nexora_breadcrumb();
?>
<section class="section section--sm page-faq">
	<div class="container container-narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div class="section-head section-head--center"><div><h1 class="section-head__title"><?php the_title(); ?></h1><p class="section-head__sub"><?php echo esc_html( nexora_option( 'pages', 'faq_intro' ) ); ?></p></div></div>
			<?php if ( '' !== trim( get_the_content() ) ) : ?><div class="prose"><?php the_content(); ?></div><?php endif; ?>
		<?php endwhile; ?>

		<?php
		$nexora_gi = 0;
		foreach ( $nexora_groups as $nexora_gname => $nexora_qs ) :
			$nexora_slug = sanitize_title( $nexora_gname );
			$nexora_ico  = 'question-circle';
			foreach ( $nexora_icons as $nexora_k => $nexora_v ) {
				if ( false !== stripos( $nexora_slug, $nexora_k ) || false !== mb_stripos( $nexora_gname, __( $nexora_k, 'nexora' ) ) ) { // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
					$nexora_ico = $nexora_v;
				}
			}
			?>
			<h2 class="h5 faq-group__title" id="<?php echo esc_attr( $nexora_slug ); ?>"><?php nexora_the_icon( $nexora_ico, 'sm' ); ?> <?php echo esc_html( $nexora_gname ); ?></h2>
			<div class="accordion" itemscope itemtype="https://schema.org/FAQPage">
				<?php foreach ( $nexora_qs as $nexora_qi => $nexora_q ) : ?>
					<details class="accordion__item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"<?php echo ( 0 === $nexora_gi && 0 === $nexora_qi ) ? ' open' : ''; ?>>
						<summary class="accordion__summary"><span itemprop="name"><?php echo esc_html( $nexora_q['question'] ); ?></span><?php nexora_the_icon( 'chevron-down', 'xs' ); ?></summary>
						<div class="accordion__body" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><div itemprop="text"><?php echo wp_kses_post( wpautop( $nexora_q['answer'] ) ); ?></div></div>
					</details>
				<?php endforeach; ?>
			</div>
			<?php
			$nexora_gi++;
		endforeach;
		if ( ! $nexora_groups ) {
			nexora_empty_state( array( 'icon' => 'question-circle', 'title' => __( 'No questions yet', 'nexora' ), 'text' => __( 'Add questions under Nexora → Pages → FAQ.', 'nexora' ) ) );
		}
		?>
		<div class="cta-band cta-band--rounded faq-cta"><div class="cta-band__inner"><div><div class="cta-band__title"><?php nexora_the_icon( 'headset' ); ?><?php esc_html_e( 'Still need help?', 'nexora' ); ?></div><div class="cta-band__text"><?php echo esc_html( nexora_option( 'general', 'hours' ) ); ?><?php if ( $nexora_phone ) : ?> · <span class="ltr"><?php echo esc_html( nexora_num( $nexora_phone ) ); ?></span><?php endif; ?></div></div><?php if ( $nexora_contact ) : ?><a class="btn btn--dark" href="<?php echo esc_url( get_permalink( $nexora_contact[0] ) ); ?>"><?php esc_html_e( 'Contact support', 'nexora' ); ?></a><?php endif; ?></div></div>
	</div>
</section>
<?php
get_footer();
