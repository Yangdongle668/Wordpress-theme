<?php
/**
 * Archive template.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'archive' ) ) {
	get_footer();
	return;
}
if ( function_exists( 'voltcore_tmpl_render' ) && voltcore_tmpl_render( 'archive' ) ) {
	get_footer();
	return;
}
?>

<section class="page-header page-header--light">
	<div class="page-header__inner">
		<?php voltcore_breadcrumbs(); ?>
		<h1 class="page-header__title">
			<?php the_archive_title(); ?>
		</h1>
		<?php the_archive_description( '<p class="page-header__kicker">', '</p>' ); ?>
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
							<?php voltcore_entry_categories(); ?>
							<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26, '…' ) ); ?></p>
							<div class="post-card__meta"><?php voltcore_posted_on(); ?></div>
						</div>
					</article>
				<?php endwhile; ?>
				</div>
				<?php voltcore_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No posts match this archive.', 'voltcore' ); ?></p>
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
