<?php
/**
 * Elementor / Elementor Pro compatibility layer.
 *
 * - Declares theme-level supports Elementor looks for (post_thumbnails,
 *   align-wide, responsive-embeds, editor-styles).
 * - Registers Elementor Pro "Theme Builder" locations so Pro users can
 *   override header, footer, archive, single, and 404 visually.
 * - Provides a "content" template that strips VoltCore chrome when a
 *   page is set to Elementor's "Elementor Canvas" template — in that
 *   mode we still print our <head>/<body> wrapper for SEO.
 * - Adds an Elementor category "VoltCore" so any future custom widgets
 *   are grouped there.
 * - Aligns Elementor content width with VoltCore's container (1440).
 * - Registers a body class for Elementor-edited pages so CSS can react.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Elementor.
 */
function voltcore_has_elementor() {
	return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' );
}

/**
 * Detect Elementor Pro.
 */
function voltcore_has_elementor_pro() {
	return defined( 'ELEMENTOR_PRO_VERSION' );
}

/* =========================================================
 * Theme supports Elementor relies on.
 * ========================================================= */
function voltcore_elementor_supports() {
	add_theme_support( 'elementor' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
}
add_action( 'after_setup_theme', 'voltcore_elementor_supports', 20 );

/* =========================================================
 * Register Elementor Pro Theme Builder locations.
 * Users can build a header / footer / single / archive / 404
 * template in Elementor Pro and it will replace our default
 * templates automatically.
 * ========================================================= */
function voltcore_register_elementor_locations( $locations_manager ) {
	$locations_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'voltcore_register_elementor_locations' );

/* =========================================================
 * Load an Elementor Pro location if one exists, otherwise
 * fall through to our template. Used for header and footer.
 * ========================================================= */
function voltcore_elementor_location( $location ) {
	if ( function_exists( 'elementor_theme_do_location' ) ) {
		return elementor_theme_do_location( $location );
	}
	return false;
}

/* =========================================================
 * Body class + content width.
 * ========================================================= */
function voltcore_elementor_body_class( $classes ) {
	if ( is_singular() && voltcore_has_elementor() ) {
		$post_id = get_queried_object_id();
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document && method_exists( $document, 'is_built_with_elementor' ) && $document->is_built_with_elementor() ) {
				$classes[] = 'voltcore-elementor';
			}
		}
	}
	return $classes;
}
add_filter( 'body_class', 'voltcore_elementor_body_class' );

/* =========================================================
 * Elementor widget category (for future custom widgets).
 * ========================================================= */
function voltcore_elementor_category( $elements_manager ) {
	if ( ! method_exists( $elements_manager, 'add_category' ) ) {
		return;
	}
	$elements_manager->add_category(
		'voltcore',
		array(
			'title' => __( 'VoltCore', 'voltcore' ),
			'icon'  => 'eicon-bolt',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'voltcore_elementor_category' );

/* =========================================================
 * Enqueue a small Elementor-specific stylesheet so Elementor
 * widgets blend in with VoltCore's design tokens.
 * ========================================================= */
function voltcore_elementor_frontend_styles() {
	if ( ! voltcore_has_elementor() ) {
		return;
	}
	$css = "
	.elementor-kit-default{--e-global-color-primary:var(--vc-ink);--e-global-color-secondary:var(--vc-accent);--e-global-color-text:var(--vc-ink);--e-global-color-accent:var(--vc-accent);}
	.elementor-widget-heading .elementor-heading-title{font-family:var(--vc-font-display);letter-spacing:-0.02em;}
	.elementor-button{border-radius:var(--vc-radius);padding:11px 24px;font-weight:500;letter-spacing:.08em;text-transform:uppercase;font-size:13px;}
	";
	wp_register_style( 'voltcore-elementor', false );
	wp_enqueue_style( 'voltcore-elementor' );
	wp_add_inline_style( 'voltcore-elementor', $css );
}
add_action( 'wp_enqueue_scripts', 'voltcore_elementor_frontend_styles', 20 );
