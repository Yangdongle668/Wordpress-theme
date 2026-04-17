<?php
/**
 * Template Name: VoltCore — Contact
 *
 * Design intent: route visitors to the right team before they scroll.
 * Four "department" cards under a light hero — each is a real mailto:
 * link so phones open the mail app on tap. Below that, the_content()
 * takes over for a longer story / form / map the site owner controls.
 *
 * Departments + emails are read from post meta so editors can customise
 * without touching the template.
 *
 * @package VoltCore
 */

get_header();

if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'single' ) ) {
	get_footer();
	return;
}

while ( have_posts() ) : the_post();
	$title = get_the_title();
	$sub   = get_the_excerpt();
	if ( ! $sub ) {
		$sub = __( 'Our teams are organised by what you need. Pick the most relevant one below.', 'voltcore' );
	}

	// Departments can be overridden via post meta; fall back to sensible defaults.
	$depts = array(
		array(
			'label' => get_post_meta( get_the_ID(), '_vc_contact_sales_label', true ) ?: __( 'Sales & quotes', 'voltcore' ),
			'email' => get_post_meta( get_the_ID(), '_vc_contact_sales_email', true ) ?: 'sales@example.com',
			'desc'  => get_post_meta( get_the_ID(), '_vc_contact_sales_desc',  true ) ?: __( 'Specifying VoltCore cells or packs in a program, or pricing a grid-scale project.', 'voltcore' ),
		),
		array(
			'label' => get_post_meta( get_the_ID(), '_vc_contact_eng_label', true )   ?: __( 'Engineering', 'voltcore' ),
			'email' => get_post_meta( get_the_ID(), '_vc_contact_eng_email', true )   ?: 'engineering@example.com',
			'desc'  => get_post_meta( get_the_ID(), '_vc_contact_eng_desc',  true )   ?: __( 'Integration, firmware, test-bench and field support for active customers.', 'voltcore' ),
		),
		array(
			'label' => get_post_meta( get_the_ID(), '_vc_contact_press_label', true ) ?: __( 'Press & media', 'voltcore' ),
			'email' => get_post_meta( get_the_ID(), '_vc_contact_press_email', true ) ?: 'press@example.com',
			'desc'  => get_post_meta( get_the_ID(), '_vc_contact_press_desc',  true ) ?: __( 'Interview requests, quotes, imagery and media kit access.', 'voltcore' ),
		),
		array(
			'label' => get_post_meta( get_the_ID(), '_vc_contact_careers_label', true ) ?: __( 'Careers', 'voltcore' ),
			'email' => get_post_meta( get_the_ID(), '_vc_contact_careers_email', true ) ?: 'careers@example.com',
			'desc'  => get_post_meta( get_the_ID(), '_vc_contact_careers_desc',  true ) ?: __( 'Applications, talent partnerships and recruiter enquiries.', 'voltcore' ),
		),
	);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-contact' ); ?>>

	<header class="contact-hero">
		<div class="contact-hero__inner" data-fade>
			<?php voltcore_breadcrumbs(); ?>
			<p class="eyebrow"><?php esc_html_e( 'Contact', 'voltcore' ); ?></p>
			<h1 class="contact-hero__title"><?php echo esc_html( $title ); ?></h1>
			<p class="contact-hero__sub"><?php echo esc_html( $sub ); ?></p>
		</div>
	</header>

	<section class="contact-cards">
		<div class="contact-cards__grid">
			<?php foreach ( $depts as $i => $d ) :
				$email = sanitize_email( $d['email'] );
			?>
				<a class="contact-card" href="mailto:<?php echo esc_attr( $email ); ?>" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
					<span class="contact-card__label"><?php echo esc_html( $d['label'] ); ?></span>
					<span class="contact-card__email"><?php echo esc_html( $email ); ?></span>
					<p class="contact-card__desc"><?php echo esc_html( $d['desc'] ); ?></p>
					<span class="contact-card__arrow" aria-hidden="true">→</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<div class="contact-body">
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>

</article>

<?php endwhile; get_footer(); ?>
