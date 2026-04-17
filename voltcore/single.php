<?php
/**
 * Single post template.
 *
 * @package VoltCore
 */

get_header();

// Theme Builder override — custom single template wins.
if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}
if ( function_exists( 'voltcore_tmpl_render' ) && voltcore_tmpl_render( 'single' ) ) {
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

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?> itemscope itemtype="https://schema.org/Article">
	<meta itemprop="mainEntityOfPage" content="<?php echo esc_url( get_permalink() ); ?>">
	<meta itemprop="headline" content="<?php echo esc_attr( get_the_title() ); ?>">
	<meta itemprop="author" content="<?php echo esc_attr( get_the_author() ); ?>">

	<header class="single-hero" <?php if ( $thumb_url ) : ?>style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');"<?php endif; ?>>
		<?php if ( $thumb_url ) : ?>
			<img class="single-hero__seo-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" itemprop="image" width="2000" height="1200" loading="eager" fetchpriority="high">
		<?php endif; ?>
		<div class="single-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<?php voltcore_entry_categories(); ?>
			<h1 class="single-hero__title" itemprop="name"><?php the_title(); ?></h1>
			<div class="single-hero__meta">
				<time class="entry-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" itemprop="datePublished"><?php echo esc_html( get_the_date() ); ?></time>
				<meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
				<span class="sep">·</span>
				<span itemprop="author" itemscope itemtype="https://schema.org/Person">
					<span class="entry-author" itemprop="name"><?php echo esc_html( get_the_author() ); ?></span>
				</span>
				<span class="sep">·</span>
				<span class="entry-reading-time"><?php echo esc_html( voltcore_reading_time( get_the_content() ) ); ?></span>
			</div>
		</div>
	</header>

	<div class="container container--prose">
		<div class="entry-content" itemprop="articleBody">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'voltcore' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<?php if ( has_tag() ) : ?>
			<div class="entry-tags">
				<?php the_tags( '<span class="entry-tags__label">' . esc_html__( 'Tagged:', 'voltcore' ) . '</span> ', ' ' ); ?>
			</div>
		<?php endif; ?>

		<nav class="post-nav" role="navigation" aria-label="<?php esc_attr_e( 'Post navigation', 'voltcore' ); ?>">
			<div class="post-nav__prev"><?php previous_post_link( '%link', '<span>←</span> %title' ); ?></div>
			<div class="post-nav__next"><?php next_post_link( '%link', '%title <span>→</span>' ); ?></div>
		</nav>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
</article>

<?php endwhile; get_footer();
