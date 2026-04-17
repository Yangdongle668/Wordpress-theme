<?php
/**
 * Reusable template helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta line used on blog cards and single posts.
 */
function voltcore_posted_on() {
	$time = sprintf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	printf(
		/* translators: %1$s: post date, %2$s: author name */
		esc_html__( '%1$s · by %2$s', 'voltcore' ),
		$time, // phpcs:ignore WordPress.Security.EscapeOutput
		'<span class="entry-author">' . esc_html( get_the_author() ) . '</span>'
	);
}

/**
 * Categories line.
 */
function voltcore_entry_categories() {
	$cats = get_the_category_list( ' · ' );
	if ( $cats ) {
		echo '<div class="entry-categories">' . $cats . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Pagination in archives.
 */
function voltcore_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( '&larr; Previous', 'voltcore' ),
			'next_text' => __( 'Next &rarr;', 'voltcore' ),
			'screen_reader_text' => __( 'Posts navigation', 'voltcore' ),
		)
	);
}

/**
 * Compute a rough reading-time string for article content (English-friendly).
 */
function voltcore_reading_time( $content, $wpm = 220 ) {
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	$minutes = max( 1, (int) ceil( $words / $wpm ) );
	/* translators: %d: minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'voltcore' ), $minutes );
}

/**
 * Render a split of comma-separated custom-field values as a key spec block.
 * Used by product cards. Expects format: "Range|640 km,Charge|15 min,Cells|4680".
 */
function voltcore_render_specs( $raw ) {
	if ( ! $raw ) {
		return;
	}
	$rows = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	if ( ! $rows ) {
		return;
	}
	echo '<ul class="spec-list">';
	foreach ( $rows as $row ) {
		$parts = array_map( 'trim', explode( '|', $row ) );
		if ( count( $parts ) !== 2 ) {
			continue;
		}
		printf(
			'<li><span class="spec-value">%1$s</span><span class="spec-label">%2$s</span></li>',
			esc_html( $parts[1] ),
			esc_html( $parts[0] )
		);
	}
	echo '</ul>';
}
