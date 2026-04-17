<?php
/**
 * Template Name: VoltCore — Press
 *
 * Design intent: journalists arrive needing two things quickly — the
 * media kit, and a way to talk to a human. So the top of the page is
 * a single, unambiguous download card beside the press contact, then
 * a tidy list of latest announcements, then the_content() for the
 * longer brand / spokesperson bio. Visual language is paper-quiet:
 * white background, thin dividers, monochrome type.
 *
 * Press releases are pulled from posts in the "press" category.
 * If none exist, the section is hidden automatically.
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
	$sub = get_the_excerpt();
	if ( ! $sub ) {
		$sub = __( 'Media kit, news, and who to talk to.', 'voltcore' );
	}

	$kit_url   = get_post_meta( get_the_ID(), '_vc_press_kit_url', true ) ?: '#';
	$kit_size  = get_post_meta( get_the_ID(), '_vc_press_kit_size', true ) ?: '42 MB';
	$press_email = get_post_meta( get_the_ID(), '_vc_press_email', true ) ?: 'press@example.com';

	$press_posts = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 5,
		'category_name'  => 'press',
		'ignore_sticky_posts' => true,
	) );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-press' ); ?>>

	<header class="press-hero">
		<div class="press-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php esc_html_e( 'Press & Media', 'voltcore' ); ?></p>
			<h1 class="press-hero__title"><?php the_title(); ?></h1>
			<p class="press-hero__sub"><?php echo esc_html( $sub ); ?></p>
		</div>
	</header>

	<section class="press-row">
		<div class="press-row__inner">
			<a class="press-kit" href="<?php echo esc_url( $kit_url ); ?>" <?php echo $kit_url === '#' ? '' : 'download'; ?> data-fade>
				<div class="press-kit__label"><?php esc_html_e( 'Media kit', 'voltcore' ); ?></div>
				<div class="press-kit__title"><?php esc_html_e( 'Logos, product photography, factory b-roll and brand guidelines.', 'voltcore' ); ?></div>
				<div class="press-kit__meta">
					<span class="press-kit__size">ZIP · <?php echo esc_html( $kit_size ); ?></span>
					<span class="press-kit__cta"><?php esc_html_e( 'Download →', 'voltcore' ); ?></span>
				</div>
			</a>

			<div class="press-contact" data-fade data-fade-delay="100">
				<div class="press-contact__label"><?php esc_html_e( 'Press inquiries', 'voltcore' ); ?></div>
				<a class="press-contact__email" href="mailto:<?php echo esc_attr( $press_email ); ?>"><?php echo esc_html( $press_email ); ?></a>
				<p class="press-contact__desc"><?php esc_html_e( 'Response within one business day. Please share deadline and outlet in your first message.', 'voltcore' ); ?></p>
			</div>
		</div>
	</section>

	<?php if ( $press_posts->have_posts() ) : ?>
	<section class="press-releases">
		<header class="section__head" data-fade>
			<h2 class="section__title"><?php esc_html_e( 'Latest announcements', 'voltcore' ); ?></h2>
			<a class="section__link" href="<?php echo esc_url( get_category_link( get_category_by_slug( 'press' ) ) ); ?>"><?php esc_html_e( 'All news →', 'voltcore' ); ?></a>
		</header>
		<ul class="press-list">
			<?php while ( $press_posts->have_posts() ) : $press_posts->the_post(); ?>
				<li class="press-list__item">
					<time class="press-list__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<a class="press-list__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<span class="press-list__arrow" aria-hidden="true">→</span>
				</li>
			<?php endwhile; wp_reset_postdata(); ?>
		</ul>
	</section>
	<?php endif; ?>

	<div class="press-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>

</article>

<?php endwhile; get_footer(); ?>
