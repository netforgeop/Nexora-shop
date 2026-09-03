<?php
/**
 * Specifications tab.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

$nexora_specs = $args['specs'] ?? array();
?>
<div class="row"><div class="col-lg-9">
	<?php foreach ( $nexora_specs as $nexora_group ) : ?>
		<div class="spec-group">
			<h3 class="spec-group__title"><?php echo esc_html( $nexora_group['group'] ); ?></h3>
			<table class="spec-table"><tbody>
				<?php foreach ( $nexora_group['rows'] as $nexora_row ) : ?><tr><th scope="row"><?php echo esc_html( $nexora_row[0] ); ?></th><td><?php echo esc_html( $nexora_row[1] ); ?></td></tr><?php endforeach; ?>
			</tbody></table>
		</div>
	<?php endforeach; ?>
</div></div>
