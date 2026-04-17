<?php
/**
 * VoltCore Customizer.
 *
 * Every visible string and image on the homepage variants is controlled from
 * here so site owners can replace content without touching templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register panels, sections and controls.
 */
function voltcore_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'voltcore_panel',
		array(
			'title'    => __( 'VoltCore Theme', 'voltcore' ),
			'priority' => 30,
		)
	);

	/* ---------------------------------------------------------------
	 * General / brand
	 * --------------------------------------------------------------- */
	$wp_customize->add_section(
		'voltcore_brand',
		array(
			'title' => __( 'Brand & Colors', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);

	$wp_customize->add_setting(
		'voltcore_accent_color',
		array(
			'default'           => '#cc0000',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'voltcore_accent_color',
			array(
				'label'   => __( 'Accent Color', 'voltcore' ),
				'section' => 'voltcore_brand',
			)
		)
	);

	$wp_customize->add_setting(
		'voltcore_homepage_variant',
		array(
			'default'           => 'classic',
			'sanitize_callback' => 'voltcore_sanitize_variant',
		)
	);
	$wp_customize->add_control(
		'voltcore_homepage_variant',
		array(
			'label'       => __( 'Homepage Style', 'voltcore' ),
			'description' => __( 'Pick which of the 3 homepage layouts the Front Page template renders.', 'voltcore' ),
			'section'     => 'voltcore_brand',
			'type'        => 'select',
			'choices'     => array(
				'classic' => __( 'Classic — full-bleed scroll-snap hero stack', 'voltcore' ),
				'grid'    => __( 'Grid — product showcase grid with parallax', 'voltcore' ),
				'story'   => __( 'Story — split-screen narrative with counters', 'voltcore' ),
			),
		)
	);

	/* ---------------------------------------------------------------
	 * Homepage sections (shared across all 3 variants — we reuse where possible)
	 * --------------------------------------------------------------- */
	$home_sections = array(
		'hero1' => array( 'label' => __( 'Hero 1', 'voltcore' ), 'fallback_image' => 'hero-1.jpg', 'title_default' => 'Model V', 'subtitle_default' => 'The most energy-dense battery pack we have ever built.', 'btn1_default' => 'Pre-order', 'btn2_default' => 'Learn more' ),
		'hero2' => array( 'label' => __( 'Hero 2', 'voltcore' ), 'fallback_image' => 'hero-2.jpg', 'title_default' => 'Powerwall X', 'subtitle_default' => 'Home energy storage, redesigned for 2026 and beyond.', 'btn1_default' => 'Order now', 'btn2_default' => 'Specs' ),
		'hero3' => array( 'label' => __( 'Hero 3', 'voltcore' ), 'fallback_image' => 'hero-3.jpg', 'title_default' => 'Megapack', 'subtitle_default' => 'Utility-scale storage. One rack. 3.9 MWh.', 'btn1_default' => 'Request quote', 'btn2_default' => 'Tech sheet' ),
	);

	foreach ( $home_sections as $key => $def ) {
		$section_id = 'voltcore_' . $key;
		$wp_customize->add_section(
			$section_id,
			array(
				'title' => $def['label'],
				'panel' => 'voltcore_panel',
			)
		);

		// Image.
		$wp_customize->add_setting(
			$section_id . '_image',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$section_id . '_image',
				array(
					'label'       => __( 'Background Image', 'voltcore' ),
					'description' => sprintf(
						/* translators: %s: fallback image filename */
						__( 'Leave empty to use the bundled placeholder (%s).', 'voltcore' ),
						$def['fallback_image']
					),
					'section'     => $section_id,
				)
			)
		);

		// Title.
		$wp_customize->add_setting(
			$section_id . '_title',
			array(
				'default'           => $def['title_default'],
				'sanitize_callback' => 'wp_kses_post',
			)
		);
		$wp_customize->add_control(
			$section_id . '_title',
			array(
				'label'   => __( 'Headline', 'voltcore' ),
				'section' => $section_id,
				'type'    => 'text',
			)
		);

		// Subtitle.
		$wp_customize->add_setting(
			$section_id . '_subtitle',
			array(
				'default'           => $def['subtitle_default'],
				'sanitize_callback' => 'wp_kses_post',
			)
		);
		$wp_customize->add_control(
			$section_id . '_subtitle',
			array(
				'label'   => __( 'Subheadline', 'voltcore' ),
				'section' => $section_id,
				'type'    => 'textarea',
			)
		);

		// Button 1.
		$wp_customize->add_setting(
			$section_id . '_btn1_label',
			array(
				'default'           => $def['btn1_default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$section_id . '_btn1_label',
			array(
				'label'   => __( 'Primary Button Label', 'voltcore' ),
				'section' => $section_id,
			)
		);

		$wp_customize->add_setting(
			$section_id . '_btn1_url',
			array(
				'default'           => '#',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$section_id . '_btn1_url',
			array(
				'label'   => __( 'Primary Button URL', 'voltcore' ),
				'section' => $section_id,
				'type'    => 'url',
			)
		);

		// Button 2.
		$wp_customize->add_setting(
			$section_id . '_btn2_label',
			array(
				'default'           => $def['btn2_default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$section_id . '_btn2_label',
			array(
				'label'   => __( 'Secondary Button Label', 'voltcore' ),
				'section' => $section_id,
			)
		);

		$wp_customize->add_setting(
			$section_id . '_btn2_url',
			array(
				'default'           => '#',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$section_id . '_btn2_url',
			array(
				'label'   => __( 'Secondary Button URL', 'voltcore' ),
				'section' => $section_id,
				'type'    => 'url',
			)
		);
	}

	/* ---------------------------------------------------------------
	 * Product cards (used by Grid homepage)
	 * --------------------------------------------------------------- */
	$wp_customize->add_section(
		'voltcore_products',
		array(
			'title' => __( 'Product Cards (Grid Homepage)', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);

	$product_defaults = array(
		1 => array( 'Cell 4680', 'Next-generation cylindrical cell with tabless design.', 'product-1.jpg' ),
		2 => array( 'Pack P-500', 'Automotive-grade pack for heavy electric vehicles.', 'product-2.jpg' ),
		3 => array( 'Grid Node', 'Modular commercial storage, ships in a 20 ft container.', 'product-3.jpg' ),
	);

	foreach ( $product_defaults as $i => $def ) {
		$prefix = 'voltcore_product_' . $i;

		$wp_customize->add_setting(
			$prefix . '_image',
			array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' )
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$prefix . '_image',
				array(
					'label'   => sprintf( __( 'Product %d Image', 'voltcore' ), $i ),
					'section' => 'voltcore_products',
				)
			)
		);

		$wp_customize->add_setting( $prefix . '_title', array( 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $prefix . '_title', array( 'label' => sprintf( __( 'Product %d Title', 'voltcore' ), $i ), 'section' => 'voltcore_products' ) );

		$wp_customize->add_setting( $prefix . '_desc', array( 'default' => $def[1], 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control( $prefix . '_desc', array( 'label' => sprintf( __( 'Product %d Description', 'voltcore' ), $i ), 'section' => 'voltcore_products', 'type' => 'textarea' ) );

		$wp_customize->add_setting( $prefix . '_url', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( $prefix . '_url', array( 'label' => sprintf( __( 'Product %d URL', 'voltcore' ), $i ), 'section' => 'voltcore_products', 'type' => 'url' ) );
	}

	/* ---------------------------------------------------------------
	 * Story sections (used by Story homepage)
	 * --------------------------------------------------------------- */
	$wp_customize->add_section(
		'voltcore_story',
		array(
			'title' => __( 'Story Blocks (Story Homepage)', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);

	$story_defaults = array(
		1 => array( 'Built in-house', 'We own the full stack — from cell chemistry to battery management software.', 'story-1.jpg' ),
		2 => array( 'Validated at scale', 'Over 12 GWh of cells shipped and 400 million km of on-road driving data.', 'story-2.jpg' ),
	);

	foreach ( $story_defaults as $i => $def ) {
		$prefix = 'voltcore_story_' . $i;

		$wp_customize->add_setting(
			$prefix . '_image',
			array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' )
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$prefix . '_image',
				array(
					'label'   => sprintf( __( 'Story %d Image', 'voltcore' ), $i ),
					'section' => 'voltcore_story',
				)
			)
		);

		$wp_customize->add_setting( $prefix . '_title', array( 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $prefix . '_title', array( 'label' => sprintf( __( 'Story %d Title', 'voltcore' ), $i ), 'section' => 'voltcore_story' ) );

		$wp_customize->add_setting( $prefix . '_desc', array( 'default' => $def[1], 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control( $prefix . '_desc', array( 'label' => sprintf( __( 'Story %d Body', 'voltcore' ), $i ), 'section' => 'voltcore_story', 'type' => 'textarea' ) );
	}

	/* ---------------------------------------------------------------
	 * Stats (counters on story + grid pages)
	 * --------------------------------------------------------------- */
	$wp_customize->add_section(
		'voltcore_stats',
		array(
			'title' => __( 'Stats Counters', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);

	$stats = array(
		1 => array( '640', 'km range' ),
		2 => array( '15',  'minute fast charge' ),
		3 => array( '1.2', 'million packs built' ),
		4 => array( '99.98', '% pack uptime' ),
	);
	foreach ( $stats as $i => $def ) {
		$prefix = 'voltcore_stat_' . $i;
		$wp_customize->add_setting( $prefix . '_value', array( 'default' => $def[0], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $prefix . '_value', array( 'label' => sprintf( __( 'Stat %d Value', 'voltcore' ), $i ), 'section' => 'voltcore_stats' ) );
		$wp_customize->add_setting( $prefix . '_label', array( 'default' => $def[1], 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $prefix . '_label', array( 'label' => sprintf( __( 'Stat %d Label', 'voltcore' ), $i ), 'section' => 'voltcore_stats' ) );
	}

	/* ---------------------------------------------------------------
	 * CTA strip + Footer
	 * --------------------------------------------------------------- */
	$wp_customize->add_section(
		'voltcore_cta',
		array(
			'title' => __( 'CTA Strip', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);

	$wp_customize->add_setting( 'voltcore_cta_title', array( 'default' => 'Power what comes next.', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'voltcore_cta_title', array( 'label' => __( 'CTA Title', 'voltcore' ), 'section' => 'voltcore_cta' ) );

	$wp_customize->add_setting( 'voltcore_cta_btn_label', array( 'default' => 'Contact Sales', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'voltcore_cta_btn_label', array( 'label' => __( 'Button Label', 'voltcore' ), 'section' => 'voltcore_cta' ) );

	$wp_customize->add_setting( 'voltcore_cta_btn_url', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'voltcore_cta_btn_url', array( 'label' => __( 'Button URL', 'voltcore' ), 'section' => 'voltcore_cta', 'type' => 'url' ) );

	$wp_customize->add_section(
		'voltcore_footer',
		array(
			'title' => __( 'Footer', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);
	$wp_customize->add_setting( 'voltcore_footer_copy', array( 'default' => '© ' . date( 'Y' ) . ' VoltCore. All rights reserved.', 'sanitize_callback' => 'wp_kses_post' ) );
	$wp_customize->add_control( 'voltcore_footer_copy', array( 'label' => __( 'Copyright Line', 'voltcore' ), 'section' => 'voltcore_footer', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'voltcore_customize_register' );

/**
 * Sanitize homepage variant.
 */
function voltcore_sanitize_variant( $value ) {
	$allowed = array( 'classic', 'grid', 'story' );
	return in_array( $value, $allowed, true ) ? $value : 'classic';
}
