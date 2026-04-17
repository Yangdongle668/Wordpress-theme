<?php
/**
 * Homepage variant: Grid.
 * Parallax hero + product grid + stat strip + latest posts + CTA.
 *
 * @package VoltCore
 */

$img      = voltcore_image( 'voltcore_hero1_image', 'hero-1.jpg' );
$title    = voltcore_text( 'voltcore_hero1_title', 'Model V' );
$subtitle = voltcore_text( 'voltcore_hero1_subtitle', 'The most energy-dense battery pack we have ever built.' );
$btn1_l   = voltcore_text( 'voltcore_hero1_btn1_label', 'Pre-order' );
$btn1_u   = voltcore_text( 'voltcore_hero1_btn1_url', '#' );
$btn2_l   = voltcore_text( 'voltcore_hero1_btn2_label', 'Learn more' );
$btn2_u   = voltcore_text( 'voltcore_hero1_btn2_url', '#' );
?>
<div class="home home--grid" data-home="grid">

	<section class="hero hero--short" data-parallax style="background-image:url('<?php echo esc_url( $img ); ?>');" aria-label="<?php echo esc_attr( $title ); ?>">
		<img class="single-hero__seo-img" src="<?php echo esc_url( $img ); ?>" alt="<?php esc_attr_e( 'Next-generation electric vehicle battery pack', 'voltcore' ); ?>" width="2000" height="1200" loading="eager" fetchpriority="high">
		<div class="hero__inner hero__inner--left">
			<h1 class="hero__title" data-fade><?php echo esc_html( $title ); ?></h1>
			<p class="hero__subtitle" data-fade data-fade-delay="120"><?php echo esc_html( $subtitle ); ?></p>
			<div class="hero__actions" data-fade data-fade-delay="220">
				<?php if ( $btn1_l ) : ?>
					<a class="btn btn--dark" href="<?php echo esc_url( $btn1_u ); ?>"><?php echo esc_html( $btn1_l ); ?></a>
				<?php endif; ?>
				<?php if ( $btn2_l ) : ?>
					<a class="btn btn--ghost-dark" href="<?php echo esc_url( $btn2_u ); ?>"><?php echo esc_html( $btn2_l ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section section--tight">
		<header class="section__head" data-fade>
			<h2 class="section__title"><?php esc_html_e( 'Our Products', 'voltcore' ); ?></h2>
			<p class="section__kicker"><?php esc_html_e( 'Cells, packs, and full energy systems — built in-house.', 'voltcore' ); ?></p>
		</header>

		<div class="product-grid">
			<?php
			$fallbacks = array( 1 => 'product-1.jpg', 2 => 'product-2.jpg', 3 => 'product-3.jpg' );
			for ( $i = 1; $i <= 3; $i++ ) :
				$img   = voltcore_image( 'voltcore_product_' . $i . '_image', $fallbacks[ $i ] );
				$title = voltcore_text( 'voltcore_product_' . $i . '_title', '' );
				$desc  = voltcore_text( 'voltcore_product_' . $i . '_desc', '' );
				$url   = voltcore_text( 'voltcore_product_' . $i . '_url', '#' );
			?>
			<a class="product-card" href="<?php echo esc_url( $url ); ?>" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
				<div class="product-card__media" style="background-image:url('<?php echo esc_url( $img ); ?>');" role="img" aria-label="<?php echo esc_attr( $title ); ?>">
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $title . ' — ' . wp_strip_all_tags( $desc ) ); ?>" width="1200" height="900" loading="lazy" decoding="async" class="screen-reader-text">
				</div>
				<div class="product-card__body">
					<h3 class="product-card__title"><?php echo esc_html( $title ); ?></h3>
					<p class="product-card__desc"><?php echo esc_html( $desc ); ?></p>
					<span class="product-card__arrow" aria-hidden="true">→</span>
				</div>
			</a>
			<?php endfor; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/section', 'stats' ); ?>
	<?php get_template_part( 'template-parts/section', 'latest-posts' ); ?>
	<?php get_template_part( 'template-parts/section', 'cta' ); ?>
</div>
