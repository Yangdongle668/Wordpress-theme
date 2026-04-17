<?php
/**
 * Front page.
 *
 * Routing priority:
 *   1. Elementor Pro theme builder "single" location → Pro owns the page.
 *   2. If the Home page post has content (built with Elementor, Gutenberg
 *      or the classic editor) → render the_content(). This is what lets
 *      users pick up any Elementor template and freely design the home.
 *   3. Fall back to one of the three VoltCore homepage variants
 *      (classic / grid / story), driven by Customizer.
 *
 * @package VoltCore
 */

get_header();

$vc_rendered = false;

// 1. Elementor Pro theme builder
if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	$vc_rendered = true;
}

// 2. User-authored home content (Elementor / Gutenberg / classic)
if ( ! $vc_rendered && have_posts() ) {
	while ( have_posts() ) :
		the_post();
		$post_content = get_the_content();
		$has_real_content = false;

		if ( post_password_required() ) {
			$has_real_content = true;
		} elseif ( function_exists( '\Elementor\Plugin' ) || class_exists( '\Elementor\Plugin' ) ) {
			// Elementor page?
			if ( class_exists( '\Elementor\Plugin' ) ) {
				$doc = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
				if ( $doc && method_exists( $doc, 'is_built_with_elementor' ) && $doc->is_built_with_elementor() ) {
					$has_real_content = true;
				}
			}
		}

		if ( ! $has_real_content && trim( wp_strip_all_tags( $post_content ) ) !== '' ) {
			$has_real_content = true;
		}

		if ( $has_real_content ) {
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'front-page-entry' ); ?>>
				<div class="entry-content entry-content--front">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
			$vc_rendered = true;
		}
	endwhile;
	rewind_posts();
}

// 3. Fallback: Customizer variant
if ( ! $vc_rendered ) {
	$variant = get_theme_mod( 'voltcore_homepage_variant', 'classic' );
	switch ( $variant ) {
		case 'grid':
			get_template_part( 'template-parts/home', 'grid' );
			break;
		case 'story':
			get_template_part( 'template-parts/home', 'story' );
			break;
		case 'classic':
		default:
			get_template_part( 'template-parts/home', 'classic' );
			break;
	}
}

get_footer();
