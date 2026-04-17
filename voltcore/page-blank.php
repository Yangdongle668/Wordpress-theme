<?php
/**
 * Template Name: VoltCore — Blank Canvas
 *
 * A completely blank page — no header, no footer — like Elementor
 * Canvas but still running inside VoltCore so SEO helpers (JSON-LD,
 * OG tags) still fire. Perfect for landing pages and coming-soon.
 *
 * @package VoltCore
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'page-blank' ); ?>>
<?php wp_body_open(); ?>
<main id="main" class="site-main" role="main">
<?php
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
?>
</main>
<?php wp_footer(); ?>
</body>
</html>
