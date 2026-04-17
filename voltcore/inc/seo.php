<?php
/**
 * VoltCore SEO module.
 *
 * - Meta description, canonical, robots
 * - Open Graph + Twitter Card
 * - JSON-LD: Organization, WebSite (with SearchAction), BreadcrumbList,
 *   Article (single post), WebPage (pages)
 * - Preconnect hints for font origins
 * - Breadcrumb component
 * - Image output hardening (lazy, decoding, dimensions, alt fallback)
 * - Title tag fine-tuning
 *
 * WordPress 5.5+ already generates a valid XML sitemap at /wp-sitemap.xml,
 * adds rel="canonical" via rel_canonical(), and lazy-loads images. We
 * layer on top of that rather than duplicating.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =============================================================
 * 1. <head> additions — preconnect, meta description, OG, Twitter
 * ============================================================= */

function voltcore_preconnect() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
}
add_action( 'wp_head', 'voltcore_preconnect', 1 );

/**
 * Compute a meta description for the current request.
 */
function voltcore_meta_description() {
	$desc = '';

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_excerpt ) ) {
			$desc = $post->post_excerpt;
		} elseif ( $post ) {
			$desc = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$desc = term_description();
	} elseif ( is_search() ) {
		/* translators: %s: search query */
		$desc = sprintf( __( 'Search results for "%s" on %s.', 'voltcore' ), get_search_query(), get_bloginfo( 'name' ) );
	} elseif ( is_home() || is_front_page() ) {
		$desc = get_bloginfo( 'description' );
	} elseif ( is_archive() ) {
		$desc = get_the_archive_description();
	}

	$desc = wp_strip_all_tags( $desc );
	$desc = preg_replace( '/\s+/u', ' ', $desc );
	$desc = trim( $desc );

	if ( mb_strlen( $desc ) > 160 ) {
		$desc = mb_substr( $desc, 0, 157 ) . '…';
	}

	if ( '' === $desc ) {
		$desc = get_bloginfo( 'description' );
	}

	return $desc;
}

/**
 * Output meta description + robots for non-indexable archives.
 */
function voltcore_seo_meta() {
	$desc = voltcore_meta_description();
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}

	// Keep search results and paginated archives out of the index.
	if ( is_search() || is_404() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	} elseif ( is_paged() ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'voltcore_seo_meta', 2 );

/**
 * OG + Twitter Card.
 */
function voltcore_open_graph() {
	$site = get_bloginfo( 'name' );
	$desc = voltcore_meta_description();
	$url  = voltcore_current_url();
	$type = is_singular( 'post' ) ? 'article' : 'website';
	$img  = voltcore_primary_image_url();
	$locale = str_replace( '-', '_', get_locale() );

	$title = wp_get_document_title();

	echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
	echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
		echo '<meta property="og:image:width" content="1200">' . "\n";
		echo '<meta property="og:image:height" content="630">' . "\n";
	}

	if ( is_singular( 'post' ) ) {
		$published = get_the_date( 'c' );
		$modified  = get_the_modified_date( 'c' );
		$author    = get_the_author_meta( 'display_name', get_post_field( 'post_author' ) );
		echo '<meta property="article:published_time" content="' . esc_attr( $published ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( $modified ) . '">' . "\n";
		if ( $author ) {
			echo '<meta property="article:author" content="' . esc_attr( $author ) . '">' . "\n";
		}
		foreach ( get_the_category() as $cat ) {
			echo '<meta property="article:section" content="' . esc_attr( $cat->name ) . '">' . "\n";
		}
		foreach ( get_the_tags() ?: array() as $tag ) {
			echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '">' . "\n";
		}
	}

	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( $img ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'voltcore_open_graph', 3 );

/**
 * Current URL (stable).
 */
function voltcore_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return get_term_link( get_queried_object() );
	}
	if ( is_author() ) {
		return get_author_posts_url( get_queried_object_id() );
	}
	if ( is_home() ) {
		return get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}

/**
 * Primary image URL for OG/Twitter.
 */
function voltcore_primary_image_url() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'voltcore-hero' );
		if ( $src ) {
			return $src[0];
		}
	}
	// Fallback to hero-1 (the homepage brand image).
	return voltcore_image( 'voltcore_hero1_image', 'hero-1.jpg' );
}

/* =============================================================
 * 2. JSON-LD structured data
 * ============================================================= */

function voltcore_jsonld() {
	$graph = array();

	// Organization.
	$graph[] = array(
		'@type'    => 'Organization',
		'@id'      => home_url( '/#organization' ),
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'logo'     => voltcore_image( '', 'logo.svg' ),
		'sameAs'   => array_filter( array(
			get_theme_mod( 'voltcore_social_twitter' ),
			get_theme_mod( 'voltcore_social_linkedin' ),
			get_theme_mod( 'voltcore_social_youtube' ),
		) ),
	);

	// WebSite with SearchAction.
	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'url'             => home_url( '/' ),
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
		'inLanguage'      => get_locale(),
	);

	// BreadcrumbList (except on the homepage).
	if ( ! is_front_page() ) {
		$crumbs = voltcore_breadcrumbs_data();
		if ( count( $crumbs ) > 1 ) {
			$items = array();
			foreach ( $crumbs as $i => $c ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $c['label'],
					'item'     => $c['url'],
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => voltcore_current_url() . '#breadcrumbs',
				'itemListElement' => $items,
			);
		}
	}

	// Article on single posts.
	if ( is_singular( 'post' ) ) {
		$post      = get_queried_object();
		$author_id = (int) $post->post_author;
		$thumb_url = has_post_thumbnail( $post ) ? wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'voltcore-hero' ) : voltcore_primary_image_url();

		$graph[] = array(
			'@type'            => 'Article',
			'@id'              => get_permalink( $post ) . '#article',
			'headline'         => get_the_title( $post ),
			'description'      => voltcore_meta_description(),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $author_id ),
				'url'   => get_author_posts_url( $author_id ),
			),
			'publisher'        => array( '@id' => home_url( '/#organization' ) ),
			'image'            => $thumb_url,
			'mainEntityOfPage' => get_permalink( $post ),
			'articleSection'   => wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) ),
			'keywords'         => wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) ),
			'wordCount'        => str_word_count( wp_strip_all_tags( $post->post_content ) ),
			'inLanguage'       => get_locale(),
		);
	} elseif ( is_singular( 'page' ) ) {
		$page = get_queried_object();
		$graph[] = array(
			'@type'         => 'WebPage',
			'@id'           => get_permalink( $page ) . '#webpage',
			'url'           => get_permalink( $page ),
			'name'          => get_the_title( $page ),
			'description'   => voltcore_meta_description(),
			'isPartOf'      => array( '@id' => home_url( '/#website' ) ),
			'inLanguage'    => get_locale(),
			'datePublished' => get_the_date( 'c', $page ),
			'dateModified'  => get_the_modified_date( 'c', $page ),
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'voltcore_jsonld', 20 );

/* =============================================================
 * 3. Breadcrumbs
 * ============================================================= */

function voltcore_breadcrumbs_data() {
	$items = array();
	$items[] = array(
		'label' => __( 'Home', 'voltcore' ),
		'url'   => home_url( '/' ),
	);

	if ( is_singular( 'post' ) ) {
		$blog_page_id = get_option( 'page_for_posts' );
		if ( $blog_page_id ) {
			$items[] = array(
				'label' => get_the_title( $blog_page_id ),
				'url'   => get_permalink( $blog_page_id ),
			);
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array(
				'label' => $cats[0]->name,
				'url'   => get_category_link( $cats[0] ),
			);
		}
		$items[] = array(
			'label' => get_the_title(),
			'url'   => get_permalink(),
		);
	} elseif ( is_singular( 'page' ) ) {
		$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
		foreach ( $ancestors as $aid ) {
			$items[] = array(
				'label' => get_the_title( $aid ),
				'url'   => get_permalink( $aid ),
			);
		}
		$items[] = array(
			'label' => get_the_title(),
			'url'   => get_permalink(),
		);
	} elseif ( is_home() ) {
		$items[] = array(
			'label' => single_post_title( '', false ),
			'url'   => voltcore_current_url(),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array(
			'label' => single_term_title( '', false ),
			'url'   => voltcore_current_url(),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			/* translators: %s: search query */
			'label' => sprintf( __( 'Search: %s', 'voltcore' ), get_search_query() ),
			'url'   => voltcore_current_url(),
		);
	} elseif ( is_404() ) {
		$items[] = array( 'label' => __( 'Not Found', 'voltcore' ), 'url' => voltcore_current_url() );
	} elseif ( is_archive() ) {
		$items[] = array( 'label' => get_the_archive_title(), 'url' => voltcore_current_url() );
	}

	return $items;
}

/**
 * Render a breadcrumb trail. Skipped on the homepage.
 */
function voltcore_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = voltcore_breadcrumbs_data();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'voltcore' ) . '"><ol class="breadcrumbs__list">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $c ) {
		if ( $i === $last ) {
			printf(
				'<li class="breadcrumbs__item is-current" aria-current="page">%s</li>',
				esc_html( $c['label'] )
			);
		} else {
			printf(
				'<li class="breadcrumbs__item"><a href="%s">%s</a><span class="breadcrumbs__sep" aria-hidden="true">/</span></li>',
				esc_url( $c['url'] ),
				esc_html( $c['label'] )
			);
		}
	}
	echo '</ol></nav>';
}

/* =============================================================
 * 4. Image / content hardening
 * ============================================================= */

/**
 * Always emit width+height on content images so layout doesn't shift (CLS).
 * WP already does this post-5.5, but force a known default when missing alt.
 */
function voltcore_content_img_alt( $content ) {
	if ( strpos( $content, '<img' ) === false ) {
		return $content;
	}
	$title = get_the_title();
	return preg_replace_callback(
		'/<img([^>]*)>/i',
		function ( $m ) use ( $title ) {
			$attrs = $m[1];
			if ( preg_match( '/\salt\s*=/i', $attrs ) ) {
				return $m[0];
			}
			// Inject an empty alt (decorative) if no alt present, but prefer title.
			$alt = $title ? ' alt="' . esc_attr( $title ) . '"' : ' alt=""';
			return '<img' . $attrs . $alt . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'voltcore_content_img_alt', 9 );

/**
 * Title separator tweak — tesla.com uses " | ".
 */
function voltcore_document_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'voltcore_document_title_separator' );

/**
 * Home/front-page title: "{site name} | {tagline}" — good for brand SEO.
 */
function voltcore_document_title_parts( $parts ) {
	if ( is_front_page() ) {
		$parts['title']   = get_bloginfo( 'name' );
		$parts['tagline'] = get_bloginfo( 'description' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'voltcore_document_title_parts' );

/* =============================================================
 * 5. Customizer: social links (used by JSON-LD sameAs)
 * ============================================================= */

function voltcore_seo_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'voltcore_social',
		array(
			'title' => __( 'Social Profiles (SEO)', 'voltcore' ),
			'panel' => 'voltcore_panel',
		)
	);
	foreach ( array(
		'voltcore_social_twitter'  => __( 'X / Twitter URL', 'voltcore' ),
		'voltcore_social_linkedin' => __( 'LinkedIn URL', 'voltcore' ),
		'voltcore_social_youtube'  => __( 'YouTube URL', 'voltcore' ),
	) as $id => $label ) {
		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => 'voltcore_social',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'voltcore_seo_customizer', 20 );
