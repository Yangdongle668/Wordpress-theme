<?php
/**
 * Template Name: VoltCore — Legal (Privacy / Terms / Cookies)
 *
 * Design intent: legal pages are typography-first, not graphic. The
 * template is deliberately quiet — narrow reading column, oversized
 * title, last-updated pill, and an "on this page" index that JS auto-
 * generates from the document's h2 headings (so editors don't have to
 * maintain it by hand). Elementor / Gutenberg still owns the body.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	if ( voltcore_is_elementor_built() ) { voltcore_render_elementor_page(); continue; }
	$updated = get_the_modified_date( get_option( 'date_format' ) );
	$eyebrow = get_post_meta( get_the_ID(), '_vc_legal_eyebrow', true ) ?: __( 'Legal', 'voltcore' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-legal' ); ?> itemscope itemtype="https://schema.org/WebPage">

	<header class="legal-hero">
		<div class="legal-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="legal-hero__title" itemprop="name"><?php the_title(); ?></h1>
			<div class="legal-hero__meta">
				<span class="legal-hero__pill"><?php
					/* translators: %s: last updated date */
					printf( esc_html__( 'Last updated %s', 'voltcore' ), esc_html( $updated ) );
				?></span>
			</div>
		</div>
	</header>

	<div class="legal-body">
		<aside class="legal-toc" aria-label="<?php esc_attr_e( 'On this page', 'voltcore' ); ?>">
			<div class="legal-toc__label"><?php esc_html_e( 'On this page', 'voltcore' ); ?></div>
			<ol class="legal-toc__list" data-legal-toc></ol>
		</aside>

		<div class="legal-content">
			<div class="entry-content" data-legal-content itemprop="text">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

</article>

<?php endwhile; get_footer(); ?>
