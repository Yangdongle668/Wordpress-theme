<?php
/**
 * Page template.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}
if ( function_exists( 'voltcore_tmpl_render' ) && voltcore_tmpl_render( 'page' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	$thumb_id  = get_post_thumbnail_id();
	$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'voltcore-hero' ) : '';
	$thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
	if ( ! $thumb_alt ) {
		$thumb_alt = get_the_title();
	}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-entry' ); ?> itemscope itemtype="https://schema.org/WebPage">

	<header class="page-header <?php echo $thumb_url ? 'page-header--image' : 'page-header--light'; ?>" <?php if ( $thumb_url ) : ?>style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');"<?php endif; ?>>
		<?php if ( $thumb_url ) : ?>
			<img class="single-hero__seo-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" itemprop="image" width="2000" height="1200" loading="eager" fetchpriority="high">
		<?php endif; ?>
		<div class="page-header__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<h1 class="page-header__title" itemprop="name"><?php the_title(); ?></h1>
		</div>
	</header>

	<div class="container container--prose">
		<div class="entry-content" itemprop="text">
			<?php the_content(); ?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
</article>

<?php endwhile; get_footer();
