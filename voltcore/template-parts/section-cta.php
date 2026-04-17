<?php
/**
 * CTA strip at the bottom of every homepage variant.
 *
 * @package VoltCore
 */
$title = voltcore_text( 'voltcore_cta_title', 'Power what comes next.' );
$label = voltcore_text( 'voltcore_cta_btn_label', 'Contact Sales' );
$url   = voltcore_text( 'voltcore_cta_btn_url', '#' );
?>
<section class="cta-strip" data-fade>
	<h2 class="cta-strip__title"><?php echo esc_html( $title ); ?></h2>
	<a class="btn btn--light" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
</section>
