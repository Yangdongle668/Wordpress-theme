<?php
/**
 * VoltCore — Elementor widgets loader.
 *
 * Registers a set of drag-and-drop widgets under the "VoltCore" category
 * so users can compose tesla.com-style sections inside the Elementor
 * editor. Each widget is a pure PHP class and ships with sensible
 * defaults so dropping one on a page looks polished immediately.
 *
 * Widgets:
 *   - VoltCore_Hero
 *   - VoltCore_Feature_Card
 *   - VoltCore_Stat
 *   - VoltCore_Split
 *   - VoltCore_CTA
 *   - VoltCore_Post_Grid
 *
 * All widgets reuse VoltCore CSS classes so they inherit the same
 * typography, buttons and animations as the rest of the theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register our widgets once Elementor is ready.
 */
function voltcore_register_elementor_widgets( $widgets_manager ) {
	$dir = VOLTCORE_DIR . '/inc/elementor-widgets/';

	require_once $dir . 'widget-hero.php';
	require_once $dir . 'widget-feature-card.php';
	require_once $dir . 'widget-stat.php';
	require_once $dir . 'widget-split.php';
	require_once $dir . 'widget-cta.php';
	require_once $dir . 'widget-post-grid.php';

	$widgets_manager->register( new \VoltCore_Hero() );
	$widgets_manager->register( new \VoltCore_Feature_Card() );
	$widgets_manager->register( new \VoltCore_Stat() );
	$widgets_manager->register( new \VoltCore_Split() );
	$widgets_manager->register( new \VoltCore_CTA() );
	$widgets_manager->register( new \VoltCore_Post_Grid() );
}
add_action( 'elementor/widgets/register', 'voltcore_register_elementor_widgets' );
