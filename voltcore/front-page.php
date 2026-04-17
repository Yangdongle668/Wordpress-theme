<?php
/**
 * Front page — delegates to the chosen homepage variant.
 *
 * @package VoltCore
 */

get_header();

$variant = get_theme_mod( 'voltcore_homepage_variant', 'classic' );

switch ( $variant ) {
	case 'grid':
		get_template_part( 'template-parts/home', 'grid' );
		break;
	case 'story':
		get_template_part( 'template-parts/home', 'story' );
		break;
	case 'classic':
	default:
		get_template_part( 'template-parts/home', 'classic' );
		break;
}

get_footer();
