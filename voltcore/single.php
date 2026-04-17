<?php
/**
 * Single post template.
 *
 * @package VoltCore
 */

get_header();
while ( have_posts() ) : the_post();
	$thumb = get_the_post_thumbnail_url( get_the_ID(), 'voltcore-hero' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>

	<header class="single-hero" <?php if ( $thumb ) : ?>style="background-image:url('<?php echo esc_url( $thumb ); ?>');"<?php endif; ?>>
		<div class="single-hero__inner" data-fade>
			<?php voltcore_entry_categories(); ?>
			<h1 class="single-hero__title"><?php the_title(); ?></h1>
			<div class="single-hero__meta"><?php voltcore_posted_on(); ?></div>
		</div>
	</header>

	<div class="container container--prose">
		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'voltcore' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<?php if ( has_tag() ) : ?>
			<div class="entry-tags">
				<?php the_tags( '<span class="entry-tags__label">' . esc_html__( 'Tagged:', 'voltcore' ) . '</span> ', ' ' ); ?>
			</div>
		<?php endif; ?>

		<nav class="post-nav" aria-label="<?php esc_attr_e( 'Post navigation', 'voltcore' ); ?>">
			<div class="post-nav__prev"><?php previous_post_link( '%link', '<span>←</span> %title' ); ?></div>
			<div class="post-nav__next"><?php next_post_link( '%link', '%title <span>→</span>' ); ?></div>
		</nav>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
</article>

<?php endwhile; get_footer();
