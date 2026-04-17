<?php
/**
 * Products archive — category listing page.
 *
 * Top hero + taxonomy pill filter + product grid.
 *
 * @package VoltCore
 */

get_header();

// Elementor Pro theme-builder archive location wins.
if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'archive' ) ) {
	get_footer();
	return;
}

// Built-in Theme Builder archive template wins.
if ( function_exists( 'voltcore_tmpl_render' ) && voltcore_tmpl_render( 'archive' ) ) {
	get_footer();
	return;
}

$is_tax       = is_tax( 'vc_product_cat' );
$current_term = $is_tax ? get_queried_object() : null;
$archive_title = $is_tax ? $current_term->name : __( 'Products', 'voltcore' );
$archive_desc  = $is_tax ? $current_term->description : __( 'Cells, packs, and full energy systems — engineered in-house.', 'voltcore' );

$all_terms = get_terms( array( 'taxonomy' => 'vc_product_cat', 'hide_empty' => true ) );
?>

<section class="products-hero">
	<div class="products-hero__inner">
		<?php voltcore_breadcrumbs(); ?>
		<p class="eyebrow"><?php esc_html_e( 'VoltCore', 'voltcore' ); ?></p>
		<h1 class="products-hero__title" data-fade><?php echo esc_html( $archive_title ); ?></h1>
		<?php if ( $archive_desc ) : ?>
			<p class="products-hero__desc" data-fade data-fade-delay="120"><?php echo esc_html( wp_strip_all_tags( $archive_desc ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( $all_terms && ! is_wp_error( $all_terms ) ) : ?>
<nav class="product-filter" aria-label="<?php esc_attr_e( 'Product categories', 'voltcore' ); ?>">
	<div class="product-filter__inner">
		<a class="product-filter__pill <?php echo ! $is_tax ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'vc_product' ) ); ?>"><?php esc_html_e( 'All', 'voltcore' ); ?></a>
		<?php foreach ( $all_terms as $term ) : ?>
			<a class="product-filter__pill <?php echo ( $is_tax && $current_term && $current_term->term_id === $term->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
		<?php endforeach; ?>
	</div>
</nav>
<?php endif; ?>

<section class="section section--light">
	<?php if ( have_posts() ) : ?>
		<div class="product-grid product-grid--archive">
			<?php $i = 0; while ( have_posts() ) : the_post(); $i++;
				$thumb_id  = get_post_thumbnail_id();
				$img       = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'voltcore-card' ) : '';
				$thumb_alt = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
				if ( ! $thumb_alt ) { $thumb_alt = get_the_title(); }
				$subtitle  = voltcore_product_meta( 'subtitle' );
				$price     = voltcore_product_meta( 'price' );
			?>
				<a class="product-card product-card--tall" href="<?php the_permalink(); ?>" data-fade data-fade-delay="<?php echo esc_attr( ( $i % 6 ) * 60 ); ?>">
					<div class="product-card__media" <?php if ( $img ) : ?>style="background-image:url('<?php echo esc_url( $img ); ?>');"<?php endif; ?> role="img" aria-label="<?php echo esc_attr( $thumb_alt ); ?>">
						<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" width="900" height="700" loading="lazy" decoding="async" class="screen-reader-text"><?php endif; ?>
					</div>
					<div class="product-card__body">
						<h2 class="product-card__title"><?php the_title(); ?></h2>
						<?php if ( $subtitle ) : ?><p class="product-card__desc"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
						<div class="product-card__meta">
							<?php if ( $price ) : ?><span class="product-card__price"><?php echo esc_html( $price ); ?></span><?php endif; ?>
							<span class="product-card__arrow" aria-hidden="true">→</span>
						</div>
					</div>
				</a>
			<?php endwhile; ?>
		</div>
		<?php voltcore_pagination(); ?>
	<?php else : ?>
		<p style="text-align:center;padding:40px 0;color:var(--vc-muted)"><?php esc_html_e( 'No products yet. Add some from Products → Add New.', 'voltcore' ); ?></p>
	<?php endif; ?>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer(); ?>
