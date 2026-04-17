<?php
/**
 * Template Name: VoltCore — Full Width
 *
 * Header + footer, but no theme chrome on the content — the page
 * content goes edge-to-edge. Perfect for Elementor pages that
 * already design their own hero.
 *
 * @package VoltCore
 */

get_header();
while ( have_posts() ) : the_post();
	if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
		continue;
	}
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-fullwidth' ); ?>>
		<div class="entry-content entry-content--fullwidth">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;
get_footer();
