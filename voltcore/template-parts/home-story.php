<?php
/**
 * Homepage variant: Story.
 * Narrative split-screen blocks with counters. Feels like the "About"
 * sections of high-end EV brands.
 *
 * @package VoltCore
 */

$hero_img   = voltcore_image( 'voltcore_hero1_image', 'hero-1.jpg' );
$hero_title = voltcore_text( 'voltcore_hero1_title', 'Model V' );
$hero_sub   = voltcore_text( 'voltcore_hero1_subtitle', 'The most energy-dense battery pack we have ever built.' );
?>
<div class="home home--story" data-home="story">

	<section class="hero hero--full" style="background-image:url('<?php echo esc_url( $hero_img ); ?>');">
		<div class="hero__inner hero__inner--center">
			<p class="eyebrow" data-fade><?php esc_html_e( 'Our Story', 'voltcore' ); ?></p>
			<h1 class="hero__title" data-fade data-fade-delay="100"><?php echo esc_html( $hero_title ); ?></h1>
			<p class="hero__subtitle" data-fade data-fade-delay="200"><?php echo esc_html( $hero_sub ); ?></p>
		</div>
	</section>

	<?php
	$fallbacks = array( 1 => 'story-1.jpg', 2 => 'story-2.jpg' );
	for ( $i = 1; $i <= 2; $i++ ) :
		$img   = voltcore_image( 'voltcore_story_' . $i . '_image', $fallbacks[ $i ] );
		$title = voltcore_text( 'voltcore_story_' . $i . '_title', '' );
		$desc  = voltcore_text( 'voltcore_story_' . $i . '_desc', '' );
		$flip  = ( $i % 2 === 0 ) ? ' split--flip' : '';
	?>
	<section class="split<?php echo esc_attr( $flip ); ?>">
		<div class="split__media" data-fade>
			<div class="split__image" style="background-image:url('<?php echo esc_url( $img ); ?>');"></div>
		</div>
		<div class="split__body" data-fade data-fade-delay="150">
			<span class="eyebrow">0<?php echo esc_html( $i ); ?> / 02</span>
			<h2 class="split__title"><?php echo esc_html( $title ); ?></h2>
			<p class="split__desc"><?php echo esc_html( $desc ); ?></p>
		</div>
	</section>
	<?php endfor; ?>

	<?php get_template_part( 'template-parts/section', 'stats' ); ?>
	<?php get_template_part( 'template-parts/section', 'latest-posts' ); ?>
	<?php get_template_part( 'template-parts/section', 'cta' ); ?>
</div>
