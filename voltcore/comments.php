<?php
/**
 * Comments template.
 *
 * @package VoltCore
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			$count = get_comments_number();
			printf(
				esc_html( _n( '%s Comment', '%s Comments', $count, 'voltcore' ) ),
				number_format_i18n( $count )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => __( '&larr; Older', 'voltcore' ),
				'next_text' => __( 'Newer &rarr;', 'voltcore' ),
			)
		);

	endif;

	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'voltcore' ); ?></p>
	<?php endif;

	comment_form(
		array(
			'class_submit' => 'btn btn--dark',
			'title_reply'  => esc_html__( 'Leave a reply', 'voltcore' ),
		)
	);
	?>
</section>
