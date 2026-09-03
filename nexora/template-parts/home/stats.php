<?php
/**
 * Stats band.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_stats = array_values( array_filter( (array) $args['home']['stats'], static function ( $s ) { return '' !== $s['value']; } ) );
if ( ! $nexora_stats ) {
	return;
}
?>
<section class="stats-band" aria-label="<?php esc_attr_e( 'Store in numbers', 'nexora' ); ?>">
	<div class="container">
		<div class="stats-band__grid">
			<?php foreach ( $nexora_stats as $nexora_i => $nexora_s ) : ?>
				<div class="stat-big" data-reveal style="--reveal-delay:<?php echo (int) $nexora_i * 80; ?>ms"><span class="stat-big__value"><?php echo esc_html( nexora_num( $nexora_s['value'] ) ); ?></span><span class="stat-big__label"><?php echo esc_html( $nexora_s['label'] ); ?></span></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
