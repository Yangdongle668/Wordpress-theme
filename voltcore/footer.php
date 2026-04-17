<?php
/**
 * The site footer.
 *
 * @package VoltCore
 */
?>
</main><!-- #main -->

<?php
$vc_footer_rendered = false;
if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'footer' ) ) {
	$vc_footer_rendered = true;
}
if ( ! $vc_footer_rendered && function_exists( 'voltcore_tmpl_render' ) ) {
	$vc_footer_rendered = voltcore_tmpl_render( 'footer' );
}

if ( ! $vc_footer_rendered ) : ?>
<footer class="site-footer" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
	<div class="site-footer__widgets">
		<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
			<div class="site-footer__col">
				<?php
				if ( is_active_sidebar( 'footer-' . $i ) ) {
					dynamic_sidebar( 'footer-' . $i );
				} else {
					echo '<h4 class="widget-title">' . esc_html( sprintf( __( 'Column %d', 'voltcore' ), $i ) ) . '</h4>';
					echo '<p class="muted">' . esc_html__( 'Add widgets from Appearance → Widgets.', 'voltcore' ) . '</p>';
				}
				?>
			</div>
		<?php endfor; ?>
	</div>

	<div class="site-footer__bottom">
		<div class="site-footer__copy">
			<?php echo wp_kses_post( voltcore_text( 'voltcore_footer_copy', '© ' . date( 'Y' ) . ' VoltCore. All rights reserved.' ) ); ?>
		</div>
		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'footer-menu',
					'depth'          => 1,
				)
			);
		}
		?>
	</div>
</footer>
<?php endif; // end default footer ?>

<?php wp_footer(); ?>
</body>
</html>
