<?php
/**
 * VoltCore — first-run installer.
 *
 * Runs once on theme activation. Seeds the site so that:
 * - Permalinks are SEO-friendly (/%postname%/)
 * - Pages Home / Blog / About / Products / Contact exist
 * - 4 default categories exist
 * - 6 sample blog posts exist with featured images
 * - A primary menu is built and assigned
 * - Reading settings point at Home (static) and Blog (posts)
 * - Basic footer widgets are pre-populated
 *
 * Everything is idempotent — running it a second time won't duplicate content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * Activation hook
 * ============================================================ */

function voltcore_after_switch_theme() {
	// Guard against multiple runs.
	if ( get_option( 'voltcore_installed_v1' ) ) {
		return;
	}

	voltcore_install_permalinks();
	$pages  = voltcore_install_pages();
	$cats   = voltcore_install_categories();
	voltcore_install_posts( $cats );
	voltcore_install_reading( $pages );
	voltcore_install_menu( $pages );
	voltcore_install_footer_widgets();

	update_option( 'voltcore_installed_v1', time() );

	// Flush pretty permalinks.
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'voltcore_after_switch_theme' );

/* ============================================================
 * Permalinks
 * ============================================================ */

function voltcore_install_permalinks() {
	$current = get_option( 'permalink_structure' );
	if ( empty( $current ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
}

/* ============================================================
 * Pages
 * ============================================================ */

function voltcore_install_pages() {
	$pages = array(
		'home'     => array( 'title' => __( 'Home', 'voltcore' ), 'content' => "<!-- wp:paragraph --><p>Welcome to VoltCore.</p><!-- /wp:paragraph -->" ),
		'blog'     => array( 'title' => __( 'Blog', 'voltcore' ), 'content' => '' ),
		'about'    => array( 'title' => __( 'About', 'voltcore' ), 'content' => voltcore_seed_page_about() ),
		'products' => array( 'title' => __( 'Products', 'voltcore' ), 'content' => voltcore_seed_page_products() ),
		'contact'  => array( 'title' => __( 'Contact', 'voltcore' ), 'content' => voltcore_seed_page_contact() ),
	);

	$ids = array();
	foreach ( $pages as $slug => $def ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post( array(
			'post_title'   => $def['title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $def['content'],
		) );
	}
	return $ids;
}

function voltcore_seed_page_about() {
	return <<<HTML
<!-- wp:paragraph --><p>VoltCore builds high-density lithium-ion cells, automotive battery packs, and utility-scale energy storage systems. We own the full stack — from cell chemistry to battery management software — so we can iterate faster than the rest of the industry.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What we do</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li>Next-generation 4680 cylindrical cells with tabless architecture.</li><li>Automotive-grade packs for passenger and commercial EVs.</li><li>Grid-scale containerised storage from 1 MWh to 100 MWh.</li><li>Battery management software and telemetry at scale.</li></ul><!-- /wp:list -->
<!-- wp:heading --><h2>Our numbers</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>12 GWh of cells shipped. 400 million km of on-road driving data. 99.98% pack uptime in field deployments across 18 countries.</p><!-- /wp:paragraph -->
HTML;
}

function voltcore_seed_page_products() {
	return <<<HTML
<!-- wp:paragraph --><p>Three product lines — one stack. Cells, packs, and full energy systems, all engineered in-house.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Cell 4680</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>A next-generation cylindrical cell with a tabless current collector. 5× energy, 6× power, and 16% more range over the previous generation.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Pack P-500</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Automotive-grade pack built for heavy electric vehicles. 500 kWh usable, 350 kW peak discharge, liquid-cooled, cell-to-pack architecture.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Grid Node</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Modular commercial storage shipped in a 20 ft container. 3.9 MWh per unit, grid-forming inverter, plug-and-play deployment in under two hours.</p><!-- /wp:paragraph -->
HTML;
}

function voltcore_seed_page_contact() {
	return <<<HTML
<!-- wp:paragraph --><p>Looking to partner, specify VoltCore cells in your next program, or request a quote for a grid-scale installation? Reach us below.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li><strong>Sales</strong> — sales@example.com</li><li><strong>Engineering</strong> — engineering@example.com</li><li><strong>Press</strong> — press@example.com</li></ul><!-- /wp:list -->
<!-- wp:heading --><h2>Headquarters</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>1 Volt Way, Reno, NV 89506, United States.</p><!-- /wp:paragraph -->
HTML;
}

/* ============================================================
 * Categories
 * ============================================================ */

function voltcore_install_categories() {
	$cats = array(
		'technology'     => __( 'Technology', 'voltcore' ),
		'engineering'    => __( 'Engineering', 'voltcore' ),
		'sustainability' => __( 'Sustainability', 'voltcore' ),
		'product-news'   => __( 'Product News', 'voltcore' ),
	);

	$ids = array();
	foreach ( $cats as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term ) {
			$ids[ $slug ] = $term->term_id;
			continue;
		}
		$res = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $res ) ) {
			$ids[ $slug ] = $res['term_id'];
		}
	}
	return $ids;
}

/* ============================================================
 * Sample posts (content lives in voltcore_sample_posts())
 * ============================================================ */

function voltcore_install_posts( $cat_ids ) {
	foreach ( voltcore_sample_posts() as $post_def ) {
		$existing = get_page_by_path( $post_def['slug'], OBJECT, 'post' );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_title'    => $post_def['title'],
			'post_name'     => $post_def['slug'],
			'post_content'  => $post_def['content'],
			'post_excerpt'  => $post_def['excerpt'],
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_date'     => $post_def['date'],
			'post_category' => array_filter( array_map(
				function ( $slug ) use ( $cat_ids ) {
					return isset( $cat_ids[ $slug ] ) ? $cat_ids[ $slug ] : 0;
				},
				$post_def['categories']
			) ),
			'tags_input'    => $post_def['tags'],
		) );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		// Sideload featured image from the bundled file.
		$src_file = VOLTCORE_DIR . '/assets/images/' . $post_def['image'];
		if ( file_exists( $src_file ) ) {
			$attach_id = voltcore_attach_local_image( $src_file, $post_id, $post_def['image_alt'] );
			if ( $attach_id ) {
				set_post_thumbnail( $post_id, $attach_id );
			}
		}
	}
}

/**
 * Copy a bundled image into uploads and attach it to a post.
 */
function voltcore_attach_local_image( $src_file, $parent_post_id, $alt ) {
	if ( ! function_exists( 'wp_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$filename = basename( $src_file );
	$upload   = wp_upload_dir();
	$target   = trailingslashit( $upload['path'] ) . $filename;

	if ( ! @copy( $src_file, $target ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( $filename, null );
	$attach_id = wp_insert_attachment(
		array(
			'guid'           => trailingslashit( $upload['url'] ) . $filename,
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_title( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$target,
		$parent_post_id
	);

	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$meta = wp_generate_attachment_metadata( $attach_id, $target );
	wp_update_attachment_metadata( $attach_id, $meta );

	if ( $alt ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
	}

	return $attach_id;
}

/* ============================================================
 * Reading settings
 * ============================================================ */

function voltcore_install_reading( $pages ) {
	if ( ! empty( $pages['home'] ) && ! empty( $pages['blog'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
		update_option( 'page_for_posts', $pages['blog'] );
	}
}

/* ============================================================
 * Primary menu
 * ============================================================ */

function voltcore_install_menu( $pages ) {
	$name = __( 'VoltCore Primary', 'voltcore' );
	$menu = wp_get_nav_menu_object( $name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
		$items = array(
			array( 'title' => __( 'Products', 'voltcore' ), 'object_id' => $pages['products'] ?? 0 ),
			array( 'title' => __( 'About',    'voltcore' ), 'object_id' => $pages['about'] ?? 0 ),
			array( 'title' => __( 'Blog',     'voltcore' ), 'object_id' => $pages['blog'] ?? 0 ),
			array( 'title' => __( 'Contact',  'voltcore' ), 'object_id' => $pages['contact'] ?? 0 ),
		);
		foreach ( $items as $it ) {
			if ( ! $it['object_id'] ) {
				continue;
			}
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $it['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $it['object_id'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
	} else {
		$menu_id = $menu->term_id;
	}

	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* ============================================================
 * Footer widgets
 * ============================================================ */

function voltcore_install_footer_widgets() {
	// Seed once, tracked by its own flag so we can re-run safely.
	if ( get_option( 'voltcore_footer_widgets_seeded' ) ) {
		return;
	}

	$sidebars = get_option( 'sidebars_widgets', array() );
	if ( ! is_array( $sidebars ) ) {
		$sidebars = array();
	}

	/*
	 * WordPress, on theme switch, runs retrieve_widgets() and may move
	 * widgets inherited from the previous theme (Archives, Categories,
	 * Recent Posts, etc.) into the first available sidebar of the new
	 * theme. That is the "giant Archives / Categories in footer column 1"
	 * bug. Clear footer columns up front so the seed starts clean. If the
	 * user wants those back, they can add them from Appearance → Widgets.
	 */
	for ( $col = 1; $col <= 4; $col++ ) {
		$sidebars[ 'footer-' . $col ] = array();
	}

	$widget_html = get_option( 'widget_custom_html', array() );
	if ( ! is_array( $widget_html ) ) {
		$widget_html = array();
	}
	$next = 0;
	foreach ( array_keys( $widget_html ) as $k ) {
		if ( is_numeric( $k ) && (int) $k > $next ) {
			$next = (int) $k;
		}
	}
	$next++;

	$blocks = array(
		1 => array(
			'title'   => __( 'VoltCore', 'voltcore' ),
			'content' => '<p style="color:#5c5e62;font-size:13px;line-height:1.6">High-density batteries and energy systems for the electric era. Engineered in Reno.</p>',
		),
		2 => array(
			'title'   => __( 'Products', 'voltcore' ),
			'content' => '<ul><li><a href="/products/#cell">Cell 4680</a></li><li><a href="/products/#pack">Pack P-500</a></li><li><a href="/products/#grid">Grid Node</a></li><li><a href="/products/">All products</a></li></ul>',
		),
		3 => array(
			'title'   => __( 'Company', 'voltcore' ),
			'content' => '<ul><li><a href="/about/">About</a></li><li><a href="/blog/">Journal</a></li><li><a href="/contact/">Contact</a></li><li><a href="#">Careers</a></li></ul>',
		),
		4 => array(
			'title'   => __( 'Legal', 'voltcore' ),
			'content' => '<ul><li><a href="#">Privacy</a></li><li><a href="#">Terms</a></li><li><a href="#">Cookies</a></li><li><a href="#">Press</a></li></ul>',
		),
	);

	foreach ( $blocks as $col => $b ) {
		$widget_html[ $next ]          = array( 'title' => $b['title'], 'content' => $b['content'] );
		$sidebars[ 'footer-' . $col ]  = array( 'custom_html-' . $next );
		$next++;
	}

	$widget_html['_multiwidget']     = 1;
	update_option( 'widget_custom_html', $widget_html );
	update_option( 'sidebars_widgets', $sidebars );
	update_option( 'voltcore_footer_widgets_seeded', 1 );
}
