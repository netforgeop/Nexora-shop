<?php
/**
 * Comments.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area" aria-labelledby="comments-title">
	<?php if ( have_comments() ) : ?>
		<h2 class="h4 comments-title" id="comments-title"><?php esc_html_e( 'Comments', 'nexora' ); ?> <span class="text-muted small">(<?php echo esc_html( nexora_num( get_comments_number() ) ); ?>)</span></h2>
		<div class="comment-list">
			<?php wp_list_comments( array( 'style' => 'div', 'callback' => 'nexora_comment', 'short_ping' => true ) ); ?>
		</div>
		<?php the_comments_navigation( array( 'prev_text' => nexora_icon( 'chevron-right', 'xs', 'icon--flip-ltr' ) . esc_html__( 'Older', 'nexora' ), 'next_text' => esc_html__( 'Newer', 'nexora' ) . nexora_icon( 'chevron-left', 'xs', 'icon--flip-ltr' ) ) ); ?>
	<?php else : ?>
		<h2 class="h4 comments-title" id="comments-title"><?php esc_html_e( 'Comments', 'nexora' ); ?></h2>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="text-muted small"><?php esc_html_e( 'Comments are closed.', 'nexora' ); ?></p>
	<?php endif; ?>

	<?php
	$nexora_commenter = wp_get_current_commenter();
	$nexora_req       = get_option( 'require_name_email' );
	$nexora_aria      = $nexora_req ? ' required' : '';
	comment_form(
		array(
			'class_form'           => 'review-form',
			'title_reply'          => __( 'Leave a comment', 'nexora' ),
			'title_reply_to'       => __( 'Reply to %s', 'nexora' ),
			'title_reply_before'   => '<h3 class="review-form__title" id="reply-title">',
			'title_reply_after'    => '</h3>',
			'cancel_reply_before'  => ' <span class="small">',
			'cancel_reply_after'   => '</span>',
			'class_submit'         => 'btn btn--primary',
			'submit_field'         => '<div class="form-actions">%1$s %2$s</div>',
			'label_submit'         => __( 'Post comment', 'nexora' ),
			'comment_notes_before' => '',
			'logged_in_as'         => '<p class="small text-muted logged-in-as">' . sprintf( /* translators: 1: user name, 2: logout url */ __( 'Logged in as %1$s. <a href="%2$s">Log out?</a>', 'nexora' ), esc_html( wp_get_current_user()->display_name ), esc_url( wp_logout_url( get_permalink() ) ) ) . '</p>',
			'fields'               => array(
				'author' => '<div class="form-row"><div class="form-group"><label class="form-label" for="author">' . esc_html__( 'Name', 'nexora' ) . ( $nexora_req ? ' <span class="req">*</span>' : '' ) . '</label><input id="author" class="form-control" name="author" type="text" value="' . esc_attr( $nexora_commenter['comment_author'] ) . '" minlength="2"' . $nexora_aria . '></div>',
				'email'  => '<div class="form-group"><label class="form-label" for="email">' . esc_html__( 'Email (not published)', 'nexora' ) . ( $nexora_req ? ' <span class="req">*</span>' : '' ) . '</label><input id="email" class="form-control" name="email" type="email" dir="ltr" value="' . esc_attr( $nexora_commenter['comment_author_email'] ) . '"' . $nexora_aria . '></div></div>',
				'cookies' => '<div class="form-check"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . ( empty( $nexora_commenter['comment_author_email'] ) ? '' : ' checked' ) . '><label for="wp-comment-cookies-consent">' . esc_html__( 'Save my name and email for next time.', 'nexora' ) . '</label></div>',
			),
			'comment_field'        => '<div class="form-group"><label class="form-label" for="comment">' . esc_html__( 'Comment', 'nexora' ) . ' <span class="req">*</span></label><textarea id="comment" class="form-control" name="comment" rows="5" required minlength="10"></textarea></div>',
		)
	);
	?>
</section>
