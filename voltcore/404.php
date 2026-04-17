<?php
/**
 * 404 template.
 *
 * @package VoltCore
 */

get_header(); ?>

<section class="error-404">
	<div class="error-404__inner" data-fade>
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'voltcore' ); ?></p>
		<h1 class="error-404__title"><?php esc_html_e( 'This page is off the grid.', 'voltcore' ); ?></h1>
		<p class="error-404__desc"><?php esc_html_e( "The URL you followed doesn't match anything on our site.", 'voltcore' ); ?></p>
		<div class="hero__actions">
			<a class="btn btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'voltcore' ); ?></a>
		</div>
		<div class="error-404__search">
			<?php get_search_form(); ?>
		</div>
	</div>
</section>

<?php get_footer();
