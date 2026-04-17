<?php
/**
 * Search results template.
 *
 * @package VoltCore
 */

get_header(); ?>

<section class="page-header page-header--light">
	<div class="page-header__inner">
		<h1 class="page-header__title">
			<?php printf( esc_html__( 'Search: %s', 'voltcore' ), '<em>' . esc_html( get_search_query() ) . '</em>' ); ?>
		</h1>
		<?php
		global $wp_query;
		printf(
			'<p class="page-header__kicker">%s</p>',
			esc_html( sprintf(
				_n( '%d result found.', '%d results found.', $wp_query->found_posts, 'voltcore' ),
				$wp_query->found_posts
			) )
		);
		?>
	</div>
</section>

<div class="container container--blog">
	<div class="blog-layout">
		<div class="blog-layout__main">
			<?php if ( have_posts() ) : ?>
				<div class="post-grid post-grid--blog">
				<?php $i = 0; while ( have_posts() ) : the_post(); $i++; ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?> data-fade data-fade-delay="<?php echo esc_attr( ( $i % 6 ) * 60 ); ?>">
						<a class="post-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'voltcore-card' ); ?>
							<?php else : ?>
								<span class="post-card__placeholder" aria-hidden="true"></span>
							<?php endif; ?>
						</a>
						<div class="post-card__body">
							<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26, '…' ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
				</div>
				<?php voltcore_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No results. Try a different query.', 'voltcore' ); ?></p>
				<?php get_search_form(); ?>
			<?php endif; ?>
		</div>
		<aside class="blog-layout__sidebar">
			<?php if ( is_active_sidebar( 'sidebar-blog' ) ) : ?>
				<?php dynamic_sidebar( 'sidebar-blog' ); ?>
			<?php endif; ?>
		</aside>
	</div>
</div>

<?php get_footer();
