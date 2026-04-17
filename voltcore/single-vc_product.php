<?php
/**
 * Single product template.
 *
 * Structure:
 *   1. Full-bleed hero (title + subtitle + price + CTA) using the
 *      featured image as background.
 *   2. Free-form body — the_content() so Elementor / Gutenberg can
 *      own the rest of the page.
 *   3. Optional spec strip if `_vc_product_specs` is filled.
 *   4. Related products + CTA.
 *
 * Elementor Pro / VoltCore Theme Builder can still override the whole
 * thing via the `single` location.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}
if ( function_exists( 'voltcore_tmpl_render' ) && voltcore_tmpl_render( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	if ( voltcore_is_elementor_built() ) { voltcore_render_elementor_page(); continue; }
	$thumb_id  = get_post_thumbnail_id();
	$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'voltcore-hero' ) : '';
	$thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
	if ( ! $thumb_alt ) { $thumb_alt = get_the_title(); }

	$subtitle = voltcore_product_meta( 'subtitle' );
	$price    = voltcore_product_meta( 'price' );
	$specs    = voltcore_product_meta( 'specs' );
	$cta_l    = voltcore_product_meta( 'cta_label' );
	$cta_u    = voltcore_product_meta( 'cta_url' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-product' ); ?> itemscope itemtype="https://schema.org/Product">
	<meta itemprop="name" content="<?php echo esc_attr( get_the_title() ); ?>">
	<?php if ( $thumb_url ) : ?><meta itemprop="image" content="<?php echo esc_url( $thumb_url ); ?>"><?php endif; ?>
	<?php if ( $subtitle ) : ?><meta itemprop="description" content="<?php echo esc_attr( $subtitle ); ?>"><?php endif; ?>

	<header class="product-hero" <?php if ( $thumb_url ) : ?>style="background-image:url('<?php echo esc_url( $thumb_url ); ?>');"<?php endif; ?>>
		<?php if ( $thumb_url ) : ?>
			<img class="single-hero__seo-img" src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" width="2000" height="1200" loading="eager" fetchpriority="high">
		<?php endif; ?>
		<div class="product-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php
				$terms = get_the_terms( get_the_ID(), 'vc_product_cat' );
				if ( $terms && ! is_wp_error( $terms ) ) {
					echo esc_html( $terms[0]->name );
				} else {
					esc_html_e( 'Product', 'voltcore' );
				}
			?></p>
			<h1 class="product-hero__title" itemprop="name"><?php the_title(); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p class="product-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>

			<div class="product-hero__meta">
				<?php if ( $price ) : ?>
					<div class="product-hero__price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
						<span itemprop="price"><?php echo esc_html( $price ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( $cta_l ) : ?>
					<a class="btn btn--light" href="<?php echo esc_url( $cta_u ?: '#' ); ?>"><?php echo esc_html( $cta_l ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<?php if ( $specs ) : ?>
	<section class="product-specs" data-fade>
		<?php voltcore_render_specs( $specs ); ?>
	</section>
	<?php endif; ?>

	<div class="product-body">
		<div class="entry-content">
			<?php
			// Elementor / Gutenberg / classic editor owns everything below.
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'voltcore' ),
				'after'  => '</div>',
			) );
			?>
		</div>
	</div>

	<?php
	// Related products (same category, exclude current).
	$terms = get_the_terms( get_the_ID(), 'vc_product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$rel = new WP_Query( array(
			'post_type'           => 'vc_product',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'tax_query'           => array(
				array( 'taxonomy' => 'vc_product_cat', 'field' => 'term_id', 'terms' => wp_list_pluck( $terms, 'term_id' ) ),
			),
			'ignore_sticky_posts' => true,
		) );
		if ( $rel->have_posts() ) : ?>
			<section class="section section--light related-products">
				<header class="section__head" data-fade>
					<h2 class="section__title"><?php esc_html_e( 'Related products', 'voltcore' ); ?></h2>
					<a class="section__link" href="<?php echo esc_url( get_post_type_archive_link( 'vc_product' ) ); ?>"><?php esc_html_e( 'See all →', 'voltcore' ); ?></a>
				</header>
				<div class="product-grid">
					<?php $i = 0; while ( $rel->have_posts() ) : $rel->the_post(); $i++;
						$r_img = has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'voltcore-card' ) : '';
						$r_sub = voltcore_product_meta( 'subtitle' );
					?>
						<a class="product-card" href="<?php the_permalink(); ?>" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
							<div class="product-card__media" <?php if ( $r_img ) : ?>style="background-image:url('<?php echo esc_url( $r_img ); ?>');"<?php endif; ?> role="img" aria-label="<?php echo esc_attr( get_the_title() ); ?>"></div>
							<div class="product-card__body">
								<h3 class="product-card__title"><?php the_title(); ?></h3>
								<?php if ( $r_sub ) : ?><p class="product-card__desc"><?php echo esc_html( $r_sub ); ?></p><?php endif; ?>
								<span class="product-card__arrow" aria-hidden="true">→</span>
							</div>
						</a>
					<?php endwhile; ?>
				</div>
			</section>
		<?php endif;
		wp_reset_postdata();
	}
	?>

	<?php get_template_part( 'template-parts/section', 'cta' ); ?>
</article>

<?php endwhile; get_footer(); ?>
