<?php
/**
 * VoltCore — Elementor widgets loader.
 *
 * Registers the full VoltCore widget set under the "VoltCore" category.
 * Each widget is a pure PHP class and ships with sensible defaults so
 * dropping it on a page looks polished immediately.
 *
 * Content widgets (Phase 0): Hero, Feature Card, Stat, Split, CTA,
 * Post Grid.
 *
 * Chrome widgets (Phase 1): Navbar, Footer, Stats Row, Icon Box,
 * Logo Cloud, Marquee.
 *
 * Page-specific composite widgets (Phase 2 — to come): Contact
 * Grid, Careers Hero, Press Kit, Press List, Legal Hero, Legal
 * TOC, Team Grid, Timeline, FAQ, Product Hero, Product Specs,
 * Breadcrumbs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function voltcore_register_elementor_widgets( $widgets_manager ) {
	$dir = VOLTCORE_DIR . '/inc/elementor-widgets/';

	$files = array(
		// Phase 0 (shipping)
		'widget-hero.php'         => 'VoltCore_Hero',
		'widget-feature-card.php' => 'VoltCore_Feature_Card',
		'widget-stat.php'         => 'VoltCore_Stat',
		'widget-split.php'        => 'VoltCore_Split',
		'widget-cta.php'          => 'VoltCore_CTA',
		'widget-post-grid.php'    => 'VoltCore_Post_Grid',

		// Phase 1 (chrome)
		'widget-navbar.php'        => 'VoltCore_Navbar',
		'widget-footer.php'        => 'VoltCore_Footer',
		'widget-stats-row.php'     => 'VoltCore_Stats_Row',
		'widget-icon-box.php'      => 'VoltCore_Icon_Box',
		'widget-logo-cloud.php'    => 'VoltCore_Logo_Cloud',
		'widget-marquee.php'       => 'VoltCore_Marquee',

		// Phase 2 (page-specific composite)
		'widget-contact-grid.php'  => 'VoltCore_Contact_Grid',
		'widget-careers-hero.php'  => 'VoltCore_Careers_Hero',
		'widget-press-kit.php'     => 'VoltCore_Press_Kit',
		'widget-press-list.php'    => 'VoltCore_Press_List',
		'widget-legal-hero.php'    => 'VoltCore_Legal_Hero',
		'widget-legal-toc.php'     => 'VoltCore_Legal_TOC',
		'widget-team-grid.php'     => 'VoltCore_Team_Grid',
		'widget-timeline.php'      => 'VoltCore_Timeline',
		'widget-faq.php'           => 'VoltCore_FAQ',
		'widget-product-hero.php'  => 'VoltCore_Product_Hero',
		'widget-product-specs.php' => 'VoltCore_Product_Specs',
		'widget-breadcrumbs.php'   => 'VoltCore_Breadcrumbs',
	);

	foreach ( $files as $file => $class ) {
		$path = $dir . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
			$fqcn = '\\' . $class;
			if ( class_exists( $fqcn ) ) {
				$widgets_manager->register( new $fqcn() );
			}
		}
	}
}
add_action( 'elementor/widgets/register', 'voltcore_register_elementor_widgets' );
