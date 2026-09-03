<?php
/**
 * Newsletter band.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_home = $args['home'];
$nexora_bg   = (int) nexora_option( 'pages', 'newsletter_bg' );
$nexora_url  = $nexora_bg ? wp_get_attachment_image_url( $nexora_bg, 'nexora-banner' ) : '';
?>
<section class="section section--flush-top" aria-labelledby="sec-newsletter">
	<div class="container">
		<div class="newsletter" data-reveal<?php echo $nexora_url ? ' style="--newsletter-bg:url(' . esc_url( $nexora_url ) . ')"' : ''; ?>>
			<div class="newsletter__inner">
				<div>
					<span class="newsletter__icon"><?php nexora_the_icon( 'envelope', 'lg' ); ?></span>
					<h2 class="newsletter__title" id="sec-newsletter"><?php echo esc_html( $nexora_home['newsletter_title'] ); ?></h2>
					<p class="newsletter__text"><?php echo esc_html( $nexora_home['newsletter_text'] ); ?></p>
				</div>
				<?php get_template_part( 'template-parts/components/newsletter-form', null, array( 'id' => 'home-newsletter-email', 'class' => 'newsletter__form', 'source' => 'home' ) ); ?>
			</div>
		</div>
	</div>
</section>
