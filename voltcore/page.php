<?php
/**
 * Page template.
 *
 * @package VoltCore
 */

get_header();
while ( have_posts() ) : the_post();
	$thumb = get_the_post_thumbnail_url( get_the_ID(), 'voltcore-hero' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-entry' ); ?>>

	<header class="page-header <?php echo $thumb ? 'page-header--image' : 'page-header--light'; ?>" <?php if ( $thumb ) : ?>style="background-image:url('<?php echo esc_url( $thumb ); ?>');"<?php endif; ?>>
		<div class="page-header__inner" data-fade>
			<h1 class="page-header__title"><?php the_title(); ?></h1>
		</div>
	</header>

	<div class="container container--prose">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
</article>

<?php endwhile; get_footer();
