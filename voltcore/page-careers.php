<?php
/**
 * Template Name: VoltCore — Careers
 *
 * Design intent: recruiting pages on tesla.com lead with mission, not
 * job listings. So we open with a full-bleed hero, a 3-number metrics
 * strip (team size, locations, open roles — editable via post meta),
 * and then hand off to the_content() where the editor composes values
 * and open-position copy with Gutenberg or Elementor. A sticky jump-
 * strip is intentionally omitted; we want users to scroll and absorb
 * the story before they filter.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	if ( voltcore_is_elementor_built() ) { voltcore_render_elementor_page(); continue; }
	$thumb_id  = get_post_thumbnail_id();
	$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'voltcore-hero' ) : voltcore_image( '', 'story-2.jpg' );
	$thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : get_the_title();
	if ( ! $thumb_alt ) { $thumb_alt = get_the_title(); }

	$sub = get_the_excerpt();
	if ( ! $sub ) {
		$sub = __( 'The bottleneck on electrifying the world is batteries. We build them.', 'voltcore' );
	}

	$stats = array(
		array(
			'value' => get_post_meta( get_the_ID(), '_vc_careers_stat1_value', true ) ?: '1,200',
			'label' => get_post_meta( get_the_ID(), '_vc_careers_stat1_label', true ) ?: __( 'team members', 'voltcore' ),
		),
		array(
			'value' => get_post_meta( get_the_ID(), '_vc_careers_stat2_value', true ) ?: '4',
			'label' => get_post_meta( get_the_ID(), '_vc_careers_stat2_label', true ) ?: __( 'engineering centres', 'voltcore' ),
		),
		array(
			'value' => get_post_meta( get_the_ID(), '_vc_careers_stat3_value', true ) ?: '48',
			'label' => get_post_meta( get_the_ID(), '_vc_careers_stat3_label', true ) ?: __( 'open roles', 'voltcore' ),
		),
	);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-careers' ); ?>>

	<header class="careers-hero" style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');">
		<img class="single-hero__seo-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" width="2000" height="1200" loading="eager" fetchpriority="high">
		<div class="careers-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php esc_html_e( 'Careers', 'voltcore' ); ?></p>
			<h1 class="careers-hero__title"><?php the_title(); ?></h1>
			<p class="careers-hero__sub"><?php echo esc_html( $sub ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--light" href="#open-roles"><?php esc_html_e( 'See open roles', 'voltcore' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About us', 'voltcore' ); ?></a>
			</div>
		</div>
	</header>

	<section class="careers-stats">
		<div class="careers-stats__grid">
			<?php foreach ( $stats as $i => $s ) : ?>
				<div class="stat stat--light" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
					<div class="stat__value" data-count="<?php echo esc_attr( $s['value'] ); ?>"><?php echo esc_html( $s['value'] ); ?></div>
					<div class="stat__label"><?php echo esc_html( $s['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<div class="careers-body" id="open-roles">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>

	<?php get_template_part( 'template-parts/section', 'cta' ); ?>

</article>

<?php endwhile; get_footer(); ?>
