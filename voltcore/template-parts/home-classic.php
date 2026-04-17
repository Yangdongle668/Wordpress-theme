<?php
/**
 * Homepage variant: Classic.
 * A vertical stack of full-bleed scroll-snap heroes, each with its own
 * background image, headline, subheadline and two buttons.
 *
 * @package VoltCore
 */
?>
<div class="home home--classic" data-home="classic">

	<?php
	$heroes = array( 'hero1', 'hero2', 'hero3' );
	$fallbacks = array( 'hero1' => 'hero-1.jpg', 'hero2' => 'hero-2.jpg', 'hero3' => 'hero-3.jpg' );
	$alts      = array( 'hero1' => __( 'Next-generation electric vehicle battery pack', 'voltcore' ), 'hero2' => __( 'Home energy storage unit on a modern wall', 'voltcore' ), 'hero3' => __( 'Utility-scale grid battery installation at dusk', 'voltcore' ) );

	foreach ( $heroes as $i => $h ) :
		$img      = voltcore_image( 'voltcore_' . $h . '_image', $fallbacks[ $h ] );
		$alt      = $alts[ $h ];
		$title    = voltcore_text( 'voltcore_' . $h . '_title', '' );
		$subtitle = voltcore_text( 'voltcore_' . $h . '_subtitle', '' );
		$btn1_l   = voltcore_text( 'voltcore_' . $h . '_btn1_label', '' );
		$btn1_u   = voltcore_text( 'voltcore_' . $h . '_btn1_url', '#' );
		$btn2_l   = voltcore_text( 'voltcore_' . $h . '_btn2_label', '' );
		$btn2_u   = voltcore_text( 'voltcore_' . $h . '_btn2_url', '#' );
		$HeadingTag = ( $i === 0 ) ? 'h1' : 'h2';
	?>
	<section class="hero" style="background-image:url('<?php echo esc_url( $img ); ?>');" data-snap aria-label="<?php echo esc_attr( $title ); ?>">
		<img class="single-hero__seo-img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="2000" height="1200" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" <?php echo $i === 0 ? 'fetchpriority="high"' : ''; ?>>
		<div class="hero__inner">
			<<?php echo $HeadingTag; ?> class="hero__title" data-fade><?php echo esc_html( $title ); ?></<?php echo $HeadingTag; ?>>
			<p class="hero__subtitle" data-fade data-fade-delay="120"><?php echo esc_html( $subtitle ); ?></p>
			<div class="hero__actions" data-fade data-fade-delay="220">
				<?php if ( $btn1_l ) : ?>
					<a class="btn btn--light" href="<?php echo esc_url( $btn1_u ); ?>"><?php echo esc_html( $btn1_l ); ?></a>
				<?php endif; ?>
				<?php if ( $btn2_l ) : ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( $btn2_u ); ?>"><?php echo esc_html( $btn2_l ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $i < count( $heroes ) - 1 ) : ?>
			<button class="hero__scroll-hint" data-scroll-hint aria-label="<?php esc_attr_e( 'Scroll to next section', 'voltcore' ); ?>">
				<span></span>
			</button>
		<?php endif; ?>
	</section>
	<?php endforeach; ?>

	<?php get_template_part( 'template-parts/section', 'latest-posts' ); ?>
	<?php get_template_part( 'template-parts/section', 'cta' ); ?>
</div>
