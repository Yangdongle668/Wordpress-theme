<?php
/**
 * VoltCore theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VOLTCORE_VERSION', '3.0.0' );
define( 'VOLTCORE_DIR', get_template_directory() );
define( 'VOLTCORE_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function voltcore_setup() {
	load_theme_textdomain( 'voltcore', VOLTCORE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 40,
			'width'       => 160,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'voltcore' ),
			'footer'  => __( 'Footer Menu', 'voltcore' ),
		)
	);

	add_image_size( 'voltcore-hero', 2000, 1200, true );
	add_image_size( 'voltcore-card', 900, 700, true );
	add_image_size( 'voltcore-story', 1400, 1000, true );
}
add_action( 'after_setup_theme', 'voltcore_setup' );

/**
 * Content width.
 */
function voltcore_content_width() {
	$GLOBALS['content_width'] = 1440;
}
add_action( 'after_setup_theme', 'voltcore_content_width', 0 );

/**
 * Enqueue styles and scripts.
 */
function voltcore_assets() {
	wp_enqueue_style(
		'voltcore-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'voltcore-main',
		VOLTCORE_URI . '/assets/css/main.css',
		array(),
		VOLTCORE_VERSION
	);

	wp_add_inline_style( 'voltcore-main', voltcore_customizer_inline_css() );

	wp_enqueue_script(
		'voltcore-main',
		VOLTCORE_URI . '/assets/js/main.js',
		array(),
		VOLTCORE_VERSION,
		true
	);

	// Leaflet (registered, only enqueued by widgets that depend on it).
	wp_register_style(
		'voltcore-leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
		array(),
		'1.9.4'
	);
	wp_register_script(
		'voltcore-leaflet',
		'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
		array(),
		'1.9.4',
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'voltcore_assets' );

/**
 * Sidebars.
 */
function voltcore_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'voltcore' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Widgets here appear on the blog archive and single posts.', 'voltcore' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar(
			array(
				/* translators: %d: footer widget column number */
				'name'          => sprintf( __( 'Footer Column %d', 'voltcore' ), $i ),
				'id'            => 'footer-' . $i,
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
	}
}
add_action( 'widgets_init', 'voltcore_widgets' );

/**
 * Is the given post built with Elementor?
 *
 * When true, page templates should render ONLY the_content() and
 * skip hardcoded hero / CTA chrome — Elementor owns the whole canvas.
 */
function voltcore_is_elementor_built( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	if ( ! $post_id ) return false;
	if ( get_post_meta( $post_id, '_elementor_edit_mode', true ) !== 'builder' ) return false;
	$data = get_post_meta( $post_id, '_elementor_data', true );
	return ! empty( $data ) && $data !== '[]';
}

/**
 * Render an Elementor-built page: just the_content() inside a minimal
 * wrapper. Respects the page's Elementor page template when set.
 */
function voltcore_render_elementor_page() {
	$classes = implode( ' ', get_post_class( 'voltcore-elementor-page' ) );
	echo '<article id="post-' . esc_attr( get_the_ID() ) . '" class="' . esc_attr( $classes ) . '">';
	the_content();
	echo '</article>';
}

/**
 * Get a Customizer-managed image URL, falling back to a bundled placeholder.
 */
function voltcore_image( $setting, $fallback ) {
	$val = get_theme_mod( $setting );
	if ( $val ) {
		return esc_url( $val );
	}
	return esc_url( VOLTCORE_URI . '/assets/images/' . $fallback );
}

/**
 * Get a Customizer-managed text value with fallback.
 */
function voltcore_text( $setting, $default ) {
	$val = get_theme_mod( $setting, $default );
	return $val === '' ? $default : $val;
}

/**
 * Customizer: return accent color as CSS custom property overrides.
 */
function voltcore_customizer_inline_css() {
	$accent = get_theme_mod( 'voltcore_accent_color', '#cc0000' );
	$accent = sanitize_hex_color( $accent ) ?: '#cc0000';

	return ":root{--vc-accent:{$accent};}";
}

/**
 * Load includes.
 */
require VOLTCORE_DIR . '/inc/customizer.php';
require VOLTCORE_DIR . '/inc/template-tags.php';
require VOLTCORE_DIR . '/inc/seo.php';
require VOLTCORE_DIR . '/inc/products.php';
require VOLTCORE_DIR . '/inc/sample-posts.php';
require VOLTCORE_DIR . '/inc/install.php';
require VOLTCORE_DIR . '/inc/elementor.php';
require VOLTCORE_DIR . '/inc/elementor-importer.php';
require VOLTCORE_DIR . '/inc/admin.php';
require VOLTCORE_DIR . '/inc/theme-builder.php';

/**
 * Pingback header for single posts.
 */
function voltcore_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'voltcore_pingback_header' );

/**
 * Excerpt tweaks.
 */
function voltcore_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'voltcore_excerpt_more' );

function voltcore_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'voltcore_excerpt_length', 999 );
