<?php
/**
 * Latest posts grid used on all homepages.
 *
 * @package VoltCore
 */

$q = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
	)
);

if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section section--light latest-posts">
	<header class="section__head" data-fade>
		<h2 class="section__title"><?php esc_html_e( 'Latest from the Blog', 'voltcore' ); ?></h2>
		<a class="section__link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'All articles →', 'voltcore' ); ?></a>
	</header>

	<div class="post-grid">
		<?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); $i++; ?>
			<article class="post-card" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
				<a class="post-card__media" href="<?php the_permalink(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'voltcore-card' ); ?>
					<?php else : ?>
						<span class="post-card__placeholder" aria-hidden="true"></span>
					<?php endif; ?>
				</a>
				<div class="post-card__body">
					<?php voltcore_entry_categories(); ?>
					<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
					<div class="post-card__meta"><?php voltcore_posted_on(); ?></div>
				</div>
			</article>
		<?php endwhile; wp_reset_postdata(); ?>
	</div>
</section>
