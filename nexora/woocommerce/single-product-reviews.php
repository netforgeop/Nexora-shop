<?php
/**
 * Reviews tab.
 *
 * @package Nexora
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}
$nexora_count = (int) $product->get_review_count();
$nexora_avg   = (float) $product->get_average_rating();
$nexora_dist  = nexora_rating_distribution( $product );
$nexora_can   = 'yes' !== get_option( 'woocommerce_review_rating_verification_required' ) || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() );
?>
<div id="reviews" class="woocommerce-Reviews">
	<div class="reviews-summary">
		<div class="reviews-summary__score">
			<span class="reviews-summary__value"><?php echo esc_html( nexora_num( number_format_i18n( $nexora_avg, 1 ) ) ); ?></span>
			<?php echo nexora_rating_html( $nexora_avg, null, array( 'size' => 'lg', 'show_value' => false, 'show_count' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="reviews-summary__count"><?php echo esc_html( sprintf( /* translators: %s: count */ _n( 'Based on %s review', 'Based on %s reviews', $nexora_count, 'nexora' ), nexora_num( $nexora_count ) ) ); ?></span>
		</div>
		<div class="rating-bars">
			<?php foreach ( $nexora_dist as $nexora_star => $nexora_pct ) : ?>
				<div class="rating-bar"><span class="rating-bar__label"><?php echo esc_html( nexora_num( $nexora_star ) ); ?> <svg class="rating__star" aria-hidden="true" focusable="false"><use href="#i-star"></use></svg></span><div class="rating-bar__track"><div class="rating-bar__fill" style="inline-size:<?php echo (int) $nexora_pct; ?>%"></div></div><span class="rating-bar__value"><?php echo esc_html( nexora_num( $nexora_pct ) ); ?>%</span></div>
			<?php endforeach; ?>
		</div>
		<div>
			<?php if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) : ?>
				<a class="btn btn--dark" href="<?php echo esc_url( wp_login_url( get_permalink() . '#tab-reviews' ) ); ?>"><?php nexora_the_icon( 'pencil', 'xs' ); ?><?php esc_html_e( 'Log in to review', 'nexora' ); ?></a>
			<?php elseif ( $nexora_can ) : ?>
				<button type="button" class="btn btn--dark" data-review-toggle aria-expanded="false" aria-controls="review_form_wrapper"><?php nexora_the_icon( 'pencil', 'xs' ); ?><?php esc_html_e( 'Write a review', 'nexora' ); ?></button>
			<?php else : ?>
				<p class="small text-muted"><?php esc_html_e( 'Only verified buyers can review this product.', 'nexora' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $nexora_can && ! ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) ) : ?>
		<div id="review_form_wrapper" data-review-form hidden>
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					'title_reply'         => have_comments() ? esc_html__( 'Write your review', 'nexora' ) : esc_html__( 'Be the first to review this product', 'nexora' ),
					/* translators: %s: product title */
					'title_reply_to'      => esc_html__( 'Reply to %s', 'nexora' ),
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Submit review', 'nexora' ),
					'logged_in_as'        => '',
					'fields'              => array(),
					'submit_field'        => '<div class="cluster">%1$s %2$s<button type="button" class="btn btn--ghost" data-review-cancel>' . esc_html__( 'Cancel', 'nexora' ) . '</button></div>',
				);
				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array( 'author' => array( 'label' => __( 'Name', 'nexora' ), 'type' => 'text', 'value' => $commenter['comment_author'], 'required' => $name_email_required, 'autocomplete' => 'name' ), 'email' => array( 'label' => __( 'Email', 'nexora' ), 'type' => 'email', 'value' => $commenter['comment_author_email'], 'required' => $name_email_required, 'autocomplete' => 'email' ) );
				$row                 = '<div class="form-cols">';
				foreach ( $fields as $key => $field ) {
					$row .= '<div class="form-group"><label class="form-label" for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . ( $field['required'] ? ' <span class="req">*</span>' : '' ) . '</label><input id="' . esc_attr( $key ) . '" class="form-control" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' . ( 'email' === $key ? ' dir="ltr"' : '' ) . ( $field['required'] ? ' required' : '' ) . '></div>';
				}
				$row .= '</div>';
				if ( ! is_user_logged_in() ) {
					$comment_form['fields']['author_email'] = $row;
				}
				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url && ! is_user_logged_in() && get_option( 'comment_registration' ) ) {
					/* translators: %s: login url */
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'nexora' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}
				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php endif; ?>

	<div id="comments" data-reviews-list>
		<?php if ( have_comments() ) : ?>
			<div class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'nexora_review', 'style' => 'div' ) ) ); ?>
			</div>
			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="pagination pagination--comments">';
				paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', array( 'prev_text' => nexora_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ), 'next_text' => nexora_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ), 'type' => 'list' ) ) );
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="text-muted"><?php esc_html_e( 'There are no reviews yet.', 'nexora' ); ?></p>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
</div>
