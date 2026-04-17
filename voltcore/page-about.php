<?php
/**
 * Template Name: VoltCore — About
 *
 * Design intent: a full-bleed, cinematic hero framing the company's
 * reason for existing (a short, big-type statement), then a body of
 * Gutenberg / Elementor content the site owner fully owns, and a
 * closing "join us" CTA strip linking to Careers.
 *
 * Chrome (hero + closing CTA) is fixed by the theme for visual
 * consistency across brands that pick this template.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	$thumb_id  = get_post_thumbnail_id();
	$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'voltcore-hero' ) : voltcore_image( '', 'story-1.jpg' );
	$thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : get_the_title();
	if ( ! $thumb_alt ) { $thumb_alt = get_the_title(); }
	$excerpt = get_the_excerpt();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-about' ); ?>>

	<header class="about-hero" style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');">
		<img class="single-hero__seo-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" width="2000" height="1200" loading="eager" fetchpriority="high">
		<div class="about-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php esc_html_e( 'About VoltCore', 'voltcore' ); ?></p>
			<h1 class="about-hero__title"><?php the_title(); ?></h1>
			<?php if ( $excerpt ) : ?>
				<p class="about-hero__sub"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<div class="about-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>

	<section class="about-cta" data-fade>
		<div class="about-cta__inner">
			<p class="eyebrow"><?php esc_html_e( 'Join us', 'voltcore' ); ?></p>
			<h2 class="about-cta__title"><?php esc_html_e( "Build the decade's most important machine.", 'voltcore' ); ?></h2>
			<div class="hero__actions">
				<a class="btn btn--light" href="<?php echo esc_url( home_url( '/careers/' ) ); ?>"><?php esc_html_e( 'See open roles', 'voltcore' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact us', 'voltcore' ); ?></a>
			</div>
		</div>
	</section>

</article>

<?php endwhile; get_footer(); ?>
