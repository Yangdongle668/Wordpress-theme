<?php
/**
 * VoltCore — Elementor template importer.
 *
 * Instead of hand-writing fragile JSON files, we build each page's
 * Elementor element tree as a PHP array using a small set of helper
 * functions, then json_encode → wp_slash → _elementor_data.
 *
 * Public entry points:
 *   voltcore_el_import_all( $force = false ) — run the full import
 *   voltcore_el_templates()                  — slug → factory function
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =====================================================================
 *  Node builders — kept small so factories stay readable.
 * ===================================================================== */

function vc_el_uid() {
	return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
}

/**
 * Low-level builders.
 */
function vc_el_widget( $type, array $settings = array() ) {
	return array(
		'id'         => vc_el_uid(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

function vc_el_column( array $widgets, $size = 100 ) {
	return array(
		'id'       => vc_el_uid(),
		'elType'   => 'column',
		'settings' => array( '_column_size' => $size, '_inline_size' => null ),
		'elements' => $widgets,
		'isInner'  => false,
	);
}

function vc_el_section( array $columns, array $section_settings = array() ) {
	return array(
		'id'       => vc_el_uid(),
		'elType'   => 'section',
		'settings' => $section_settings,
		'elements' => $columns,
		'isInner'  => false,
	);
}

/**
 * Shorthand: a section with one full-width column that contains one widget.
 * This is by far the most common pattern, and keeps the factories flat.
 */
function vc_el_solo( $widget_type, array $widget_settings = array(), array $section_settings = array() ) {
	$widget = vc_el_widget( $widget_type, $widget_settings );
	$column = vc_el_column( array( $widget ), 100 );
	return vc_el_section( array( $column ), $section_settings );
}

/**
 * Shorthand: a section with N equal-width columns, each containing one widget.
 * Pass an array of [type, settings] pairs.
 */
function vc_el_row( array $cells, array $section_settings = array() ) {
	$n = count( $cells );
	$size = $n > 0 ? intdiv( 100, $n ) : 100;
	$columns = array();
	foreach ( $cells as $cell ) {
		$w = vc_el_widget( $cell[0], $cell[1] ?? array() );
		$columns[] = vc_el_column( array( $w ), $size );
	}
	return vc_el_section( $columns, $section_settings );
}

/**
 * Sideload a bundled theme image and return { url, id } for Elementor.
 */
function vc_el_img( $filename, $alt = '' ) {
	static $cache = array();
	if ( isset( $cache[ $filename ] ) ) {
		return $cache[ $filename ];
	}
	$src = VOLTCORE_DIR . '/assets/images/' . $filename;
	if ( ! file_exists( $src ) ) {
		return array( 'url' => VOLTCORE_URI . '/assets/images/' . $filename, 'id' => 0 );
	}
	$attach_id = voltcore_attach_local_image( $src, 0, $alt );
	if ( ! $attach_id ) {
		return array( 'url' => VOLTCORE_URI . '/assets/images/' . $filename, 'id' => 0 );
	}
	$result = array( 'url' => wp_get_attachment_url( $attach_id ), 'id' => (int) $attach_id );
	$cache[ $filename ] = $result;
	return $result;
}

/**
 * URL control value — Elementor expects a specific shape.
 */
function vc_el_url( $url ) {
	return array( 'url' => $url, 'is_external' => '', 'nofollow' => '' );
}

/* =====================================================================
 *  Import entry point
 * ===================================================================== */

function voltcore_el_import_all( $force = false ) {
	if ( ! voltcore_has_elementor() ) {
		return array( 'error' => 'Elementor is not active.' );
	}

	$results = array( 'pages' => array(), 'library' => array() );
	$tpls    = voltcore_el_templates();

	foreach ( $tpls as $slug => $factory ) {
		if ( in_array( $slug, array( 'header', 'footer', '404' ), true ) ) continue;
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( ! $page ) { $results['pages'][ $slug ] = 'page-missing'; continue; }

		$existing = get_post_meta( $page->ID, '_elementor_data', true );
		if ( ! empty( $existing ) && ! $force ) {
			$results['pages'][ $slug ] = 'skipped-existing';
			continue;
		}
		$tree = call_user_func( $factory );
		voltcore_el_import_page( $page->ID, $tree );
		$results['pages'][ $slug ] = 'imported';
	}

	$tb = array(
		'header' => array( 'title' => 'VoltCore Header', 'type' => 'header' ),
		'footer' => array( 'title' => 'VoltCore Footer', 'type' => 'footer' ),
		'404'    => array( 'title' => 'VoltCore 404',    'type' => 'error-404' ),
	);
	foreach ( $tb as $slug => $def ) {
		if ( empty( $tpls[ $slug ] ) ) continue;
		$tree = call_user_func( $tpls[ $slug ] );
		$results['library'][ $slug ] = voltcore_el_import_theme_builder( $def['title'], $def['type'], $tree );
	}

	return $results;
}

function voltcore_el_import_page( $post_id, array $tree ) {
	$json = wp_json_encode( $tree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $post_id, '_elementor_data',          wp_slash( $json ) );
	update_post_meta( $post_id, '_elementor_edit_mode',     'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version',       defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_wp_page_template',        'elementor_header_footer' );

	if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		try {
			$css = new \Elementor\Core\Files\CSS\Post( $post_id );
			$css->update();
		} catch ( \Throwable $e ) {}
	}
}

function voltcore_el_import_theme_builder( $title, $type, array $tree ) {
	$existing = get_posts( array(
		'post_type'      => 'elementor_library',
		'title'          => $title,
		'posts_per_page' => 1,
	) );
	$post_id = $existing ? $existing[0]->ID : wp_insert_post( array(
		'post_title'  => $title,
		'post_status' => 'publish',
		'post_type'   => 'elementor_library',
	) );
	if ( ! $post_id || is_wp_error( $post_id ) ) return array( 'status' => 'error' );

	$json = wp_json_encode( $tree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	update_post_meta( $post_id, '_elementor_data',          wp_slash( $json ) );
	update_post_meta( $post_id, '_elementor_edit_mode',     'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $type );
	update_post_meta( $post_id, '_elementor_version',       defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );

	if ( voltcore_has_elementor_pro() ) {
		$cond = array();
		switch ( $type ) {
			case 'header':
			case 'footer':           $cond[] = 'include/general'; break;
			case 'error-404':        $cond[] = 'include/singular/not_found404'; break;
			case 'single-post':      $cond[] = 'include/singular/post'; break;
			case 'archive':          $cond[] = 'include/archive'; break;
			case 'single-vc_product':$cond[] = 'include/singular/post_type/vc_product'; break;
		}
		if ( $cond ) {
			update_post_meta( $post_id, '_elementor_conditions', $cond );
		}
	}
	return array( 'status' => 'ok', 'id' => $post_id );
}

function voltcore_el_templates() {
	return array(
		'home'    => 'voltcore_el_tpl_home',
		'about'   => 'voltcore_el_tpl_about',
		'contact' => 'voltcore_el_tpl_contact',
		'careers' => 'voltcore_el_tpl_careers',
		'press'   => 'voltcore_el_tpl_press',
		'privacy' => 'voltcore_el_tpl_privacy',
		'terms'   => 'voltcore_el_tpl_terms',
		'cookies' => 'voltcore_el_tpl_cookies',
		'header'  => 'voltcore_el_tpl_header',
		'footer'  => 'voltcore_el_tpl_footer',
		'404'     => 'voltcore_el_tpl_404',
	);
}

/* =====================================================================
 *  Factories — each returns an array of section nodes.
 * ===================================================================== */

function voltcore_el_tpl_home() {
	$heroes = array(
		array( 'hero-1.jpg', 'Model V',     'The most energy-dense battery pack we have ever built.',    'Pre-order',     '/products/',                 'Learn more', '/products/cell-4680/' ),
		array( 'hero-2.jpg', 'Powerwall X', 'Home energy storage, redesigned for 2026 and beyond.',      'Order now',     '/products/pack-p500/',       'Specs',      '/products/pack-p500/' ),
		array( 'hero-3.jpg', 'Megapack',    'Utility-scale storage. One rack. 3.9 MWh.',                 'Request quote', '/contact/',                  'Tech sheet', '/products/grid-node/' ),
	);
	$out = array();
	foreach ( $heroes as $i => $h ) {
		$out[] = vc_el_solo( 'voltcore-hero', array(
			'image'       => vc_el_img( $h[0], $h[1] ),
			'title'       => $h[1],
			'heading_tag' => $i === 0 ? 'h1' : 'h2',
			'subtitle'    => $h[2],
			'align'       => 'center',
			'height'      => 'full',
			'btn1_label'  => $h[3],
			'btn1_url'    => vc_el_url( $h[4] ),
			'btn2_label'  => $h[5],
			'btn2_url'    => vc_el_url( $h[6] ),
		) );
	}
	$out[] = vc_el_solo( 'voltcore-post-grid', array(
		'heading'        => 'Latest from the Journal',
		'posts_per_page' => 3,
		'columns'        => '3',
	) );
	$out[] = vc_el_solo( 'voltcore-cta', array(
		'title'     => 'Power what comes next.',
		'btn_label' => 'Contact Sales',
		'btn_url'   => vc_el_url( '/contact/' ),
		'scheme'    => 'dark',
	) );
	return $out;
}

function voltcore_el_tpl_about() {
	return array(
		vc_el_solo( 'voltcore-hero', array(
			'image'       => vc_el_img( 'story-1.jpg', 'Inside the VoltCore engineering lab' ),
			'eyebrow'     => 'About VoltCore',
			'title'       => 'Batteries are the bottleneck. We build around it.',
			'heading_tag' => 'h1',
			'subtitle'    => 'We engineer lithium-ion cells, automotive battery packs and grid-scale energy systems — end to end, in-house.',
			'align'       => 'center',
			'height'      => 'full',
			'btn1_label'  => 'Our products',
			'btn1_url'    => vc_el_url( '/products/' ),
			'btn2_label'  => 'Careers',
			'btn2_url'    => vc_el_url( '/careers/' ),
		) ),
		vc_el_row( array(
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-bolt',  'library' => 'eicons' ),
				'title' => 'Energy density',
				'text'  => 'We measure ourselves by watt-hours per kilogram — not by press releases.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-cogs',  'library' => 'eicons' ),
				'title' => 'Full-stack',
				'text'  => 'Cell chemistry, pack hardware, BMS firmware — one team, one roadmap.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-globe', 'library' => 'eicons' ),
				'title' => 'Shipped globally',
				'text'  => '12 GWh of cells across 18 countries and 400 million km of on-road data.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-lock',  'library' => 'eicons' ),
				'title' => 'Safety first',
				'text'  => '99.98% pack uptime and zero field-cascade events since 2021.',
			) ),
		) ),
		vc_el_solo( 'voltcore-stats-row', array(
			'stats' => array(
				array( 'value' => '12',    'suffix' => 'GWh', 'label' => 'cells shipped' ),
				array( 'value' => '400',   'suffix' => 'M',   'label' => 'km on-road data' ),
				array( 'value' => '1,200', 'suffix' => '',    'label' => 'team members' ),
				array( 'value' => '99.98', 'suffix' => '%',   'label' => 'pack uptime' ),
			),
			'scheme' => 'light', 'columns' => '4',
		) ),
		vc_el_solo( 'voltcore-timeline', array(
			'events' => array(
				array( 'year' => '2015', 'title' => 'VoltCore founded',   'body' => 'Three engineers, one cell, one building in Reno, Nevada.' ),
				array( 'year' => '2018', 'title' => 'First pack shipped', 'body' => 'P-100 pack ships to a commercial EV OEM.' ),
				array( 'year' => '2021', 'title' => 'Dry electrode line', 'body' => 'Line 3 hits commercial yield on dry-coated 4680 cells.' ),
				array( 'year' => '2024', 'title' => '12 GWh milestone',   'body' => '12 gigawatt-hours of cells shipped to 18 countries.' ),
			),
		) ),
		vc_el_solo( 'voltcore-team-grid', array(
			'team' => array(
				array( 'name' => 'Elena Rojas',   'role' => 'Chief Executive Officer' ),
				array( 'name' => 'Dr. Mark Chen', 'role' => 'Chief Technology Officer' ),
				array( 'name' => 'Priya Narayan', 'role' => 'VP Engineering' ),
				array( 'name' => 'David Kim',     'role' => 'VP Manufacturing' ),
			),
			'columns' => '4',
		) ),
		vc_el_solo( 'voltcore-cta', array(
			'title'     => "Build the decade's most important machine.",
			'btn_label' => 'See open roles',
			'btn_url'   => vc_el_url( '/careers/' ),
			'scheme'    => 'dark',
		) ),
	);
}

function voltcore_el_tpl_contact() {
	return array(
		vc_el_solo( 'voltcore-hero', array(
			'image'       => vc_el_img( 'about.jpg', 'VoltCore headquarters' ),
			'eyebrow'     => 'Contact',
			'title'       => 'How can we help?',
			'heading_tag' => 'h1',
			'subtitle'    => 'Our teams are organised by what you need. Pick the most relevant one below.',
			'align'       => 'center',
			'height'      => 'short',
		) ),
		vc_el_solo( 'voltcore-contact-grid', array( 'columns' => '2' ) ),
		vc_el_solo( 'voltcore-cta', array(
			'title'     => 'Prefer a quick call?',
			'btn_label' => '+1 (775) 000-0000',
			'btn_url'   => vc_el_url( 'tel:+17750000000' ),
			'scheme'    => 'light',
		) ),
	);
}

function voltcore_el_tpl_careers() {
	return array(
		vc_el_solo( 'voltcore-careers-hero', array(
			'image'   => vc_el_img( 'story-2.jpg', 'VoltCore production line' ),
			'eyebrow' => 'Careers',
			'title'   => 'Build the battery decade.',
			'sub'     => 'The bottleneck on electrifying the world is batteries. We build them. Come help.',
			'btn1_l'  => 'See open roles', 'btn1_u' => vc_el_url( '#open-roles' ),
			'btn2_l'  => 'About us',       'btn2_u' => vc_el_url( '/about/' ),
		) ),
		vc_el_solo( 'voltcore-stats-row', array(
			'stats' => array(
				array( 'value' => '1,200', 'suffix' => '', 'label' => 'team members' ),
				array( 'value' => '4',     'suffix' => '', 'label' => 'engineering centres' ),
				array( 'value' => '48',    'suffix' => '', 'label' => 'open roles' ),
			),
			'scheme' => 'light', 'columns' => '3',
		) ),
		vc_el_row( array(
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-map-pin', 'library' => 'eicons' ),
				'title' => 'Reno, Nevada', 'text' => 'Cell R&D, pilot line, headquarters.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-map-pin', 'library' => 'eicons' ),
				'title' => 'Austin, Texas', 'text' => 'Pack integration, BMS firmware, thermal.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-map-pin', 'library' => 'eicons' ),
				'title' => 'Berlin, Germany', 'text' => 'European service and automotive OEM programs.',
			) ),
			array( 'voltcore-icon-box', array(
				'icon'  => array( 'value' => 'eicon-map-pin', 'library' => 'eicons' ),
				'title' => 'Singapore', 'text' => 'Asia-Pacific sales, commissioning, supplier engineering.',
			) ),
		) ),
		vc_el_solo( 'voltcore-faq', array(
			'items' => array(
				array( 'q' => 'How do you hire?',      'a' => '<p>Recruiter call → technical screen → half-day on-site → offer within 10 business days. We do not ghost.</p>' ),
				array( 'q' => 'Do you sponsor visas?', 'a' => '<p>Yes, for roles that cannot be filled locally.</p>' ),
				array( 'q' => 'Remote work?',          'a' => '<p>Engineering is on-site (the factory is not in the cloud). Commercial roles are often hybrid.</p>' ),
			),
			'open_first' => 'yes',
		) ),
		vc_el_solo( 'voltcore-cta', array(
			'title'     => 'Not seeing your role?',
			'btn_label' => 'careers@example.com',
			'btn_url'   => vc_el_url( 'mailto:careers@example.com' ),
			'scheme'    => 'dark',
		) ),
	);
}

function voltcore_el_tpl_press() {
	return array(
		vc_el_solo( 'voltcore-hero', array(
			'image'       => vc_el_img( 'about.jpg', 'VoltCore engineering lab' ),
			'eyebrow'     => 'Press & Media',
			'title'       => 'Press',
			'heading_tag' => 'h1',
			'subtitle'    => 'Media kit, news, and who to talk to at VoltCore.',
			'align'       => 'center',
			'height'      => 'short',
		) ),
		vc_el_solo( 'voltcore-press-kit', array(
			'kit_label' => 'Media kit',
			'kit_title' => 'Logos, product photography, factory b-roll and brand guidelines.',
			'kit_url'   => vc_el_url( '#' ),
			'kit_size'  => '42 MB',
			'kit_format'=> 'ZIP',
			'contact_label' => 'Press inquiries',
			'contact_email' => 'press@example.com',
			'contact_desc'  => 'Response within one business day.',
		) ),
		vc_el_solo( 'voltcore-press-list', array(
			'heading' => 'Latest announcements',
			'category' => 'press',
			'count' => 6,
		) ),
	);
}

function voltcore_el_tpl_legal( $title, $sections ) {
	$body = '';
	foreach ( $sections as $sec ) {
		$body .= '<h2>' . esc_html( $sec[0] ) . "</h2>\n" . $sec[1] . "\n";
	}
	$body = '<div data-legal-content>' . $body . '</div>';

	$legal_hero = vc_el_solo( 'voltcore-legal-hero', array(
		'eyebrow' => 'Legal',
		'title'   => $title,
		'updated' => date_i18n( get_option( 'date_format' ) ),
	) );

	$toc_col  = vc_el_column( array( vc_el_widget( 'voltcore-legal-toc', array( 'label' => 'On this page' ) ) ), 25 );
	$body_col = vc_el_column( array( vc_el_widget( 'text-editor', array( 'editor' => $body ) ) ), 75 );
	$two_col  = vc_el_section( array( $toc_col, $body_col ) );

	return array( $legal_hero, $two_col );
}

function voltcore_el_tpl_privacy() {
	return voltcore_el_tpl_legal( 'Privacy Policy', array(
		array( 'Information we collect',  '<p>We collect information you provide directly (name, email, company), information collected automatically (IP, browser, pages visited), and information from third-party service providers.</p>' ),
		array( 'How we use information',  '<p>To operate our services, respond to your requests, send updates you have opted into, comply with legal obligations, and protect against fraud.</p>' ),
		array( 'Sharing',                 '<p>With contracted service providers, professional advisers, in business transfers, and where required by law. We do not sell personal information.</p>' ),
		array( 'Your rights',             '<p>You may access, correct, delete, or port your information. Email <a href="mailto:privacy@example.com">privacy@example.com</a>.</p>' ),
		array( 'International transfers', '<p>We may transfer information to the United States under appropriate safeguards such as Standard Contractual Clauses.</p>' ),
		array( 'Retention',               '<p>We retain information for as long as needed to provide services, meet legal obligations, and resolve disputes.</p>' ),
		array( 'Children',                '<p>Not directed to children under 16.</p>' ),
		array( 'Changes',                 '<p>We may update this policy; the "last updated" date reflects the most recent revision.</p>' ),
		array( 'Contact',                 '<p><a href="mailto:privacy@example.com">privacy@example.com</a></p>' ),
	) );
}

function voltcore_el_tpl_terms() {
	return voltcore_el_tpl_legal( 'Terms of Service', array(
		array( 'Use of services',          '<p>You may use our services only in compliance with these Terms and all applicable laws.</p>' ),
		array( 'Accounts',                 '<p>You are responsible for safeguarding your credentials and for activity under your account.</p>' ),
		array( 'Intellectual property',    '<p>All content on our services is owned by VoltCore or its licensors.</p>' ),
		array( 'Disclaimer of warranties', '<p>Services are provided "as is". We disclaim all implied warranties.</p>' ),
		array( 'Limitation of liability',  '<p>We will not be liable for indirect, incidental, or consequential damages.</p>' ),
		array( 'Indemnification',          '<p>You will defend and hold us harmless from third-party claims arising from your use of the services.</p>' ),
		array( 'Termination',              '<p>We may suspend or terminate access at any time for violation of these Terms.</p>' ),
		array( 'Governing law',            '<p>Laws of the State of Nevada, United States.</p>' ),
		array( 'Changes',                  '<p>We may update these Terms; continued use is acceptance.</p>' ),
		array( 'Contact',                  '<p><a href="mailto:legal@example.com">legal@example.com</a></p>' ),
	) );
}

function voltcore_el_tpl_cookies() {
	return voltcore_el_tpl_legal( 'Cookie Policy', array(
		array( 'What are cookies',   '<p>Small text files placed on your device to remember preferences, authentication and analytics identifiers.</p>' ),
		array( 'How we use cookies', '<p>Strictly necessary, preferences, analytics, and (with consent) marketing cookies.</p>' ),
		array( 'Third-party',        '<p>Some cookies are set by analytics providers and embedded content under their own policies.</p>' ),
		array( 'Managing cookies',   '<p>Control cookies through browser settings or via our consent banner.</p>' ),
		array( 'Do Not Track',       '<p>We respect Global Privacy Control signals.</p>' ),
		array( 'Contact',            '<p><a href="mailto:privacy@example.com">privacy@example.com</a></p>' ),
	) );
}

function voltcore_el_tpl_header() {
	return array(
		vc_el_solo( 'voltcore-navbar', array(
			'logo_source' => 'site',
			'scroll_mode' => 'transparent',
		) ),
	);
}

function voltcore_el_tpl_footer() {
	return array(
		vc_el_solo( 'voltcore-footer', array(
			'copyright' => '© ' . date( 'Y' ) . ' VoltCore. All rights reserved.',
			'columns'   => array(
				array( 'title' => 'VoltCore', 'links' => "High-density batteries for the electric era.|" ),
				array( 'title' => 'Products', 'links' => "Cell 4680|/products/cell-4680/\nPack P-500|/products/pack-p500/\nGrid Node|/products/grid-node/\nAll products|/products/" ),
				array( 'title' => 'Company',  'links' => "About|/about/\nJournal|/blog/\nContact|/contact/\nCareers|/careers/" ),
				array( 'title' => 'Legal',    'links' => "Privacy|/privacy/\nTerms|/terms/\nCookies|/cookies/\nPress|/press/" ),
			),
		) ),
	);
}

function voltcore_el_tpl_404() {
	return array(
		vc_el_solo( 'voltcore-hero', array(
			'image'       => vc_el_img( 'hero-3.jpg', '404 — page off the grid' ),
			'eyebrow'     => 'Error 404',
			'title'       => 'This page is off the grid.',
			'heading_tag' => 'h1',
			'subtitle'    => "The URL you followed doesn't match anything on our site.",
			'align'       => 'center',
			'height'      => 'full',
			'btn1_label'  => 'Back to home', 'btn1_url' => vc_el_url( '/' ),
			'btn2_label'  => 'All products', 'btn2_url' => vc_el_url( '/products/' ),
		) ),
	);
}
