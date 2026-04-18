<?php
/**
 * VoltCore Sticky CTA — tesla.com-style bottom-pinned configurator bar.
 *
 * Reveals after the user scrolls past a threshold; pins to the bottom
 * of the viewport with product name, current price and up to two CTAs.
 * Dismissible. Respects prefers-reduced-motion.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Sticky_CTA extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-sticky-cta'; }
	public function get_title() { return __( 'VoltCore Sticky CTA Bar', 'voltcore' ); }
	public function get_icon() { return 'eicon-nav-menu'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'sticky', 'bar', 'cta', 'footer' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model V' ) );
		$this->add_control( 'label', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'From $39,990' ) );
		$this->add_control( 'sub', array( 'label' => 'Sub', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'After est. incentives' ) );
		$this->add_control( 'btn1_label', array( 'label' => 'Button 1', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$this->add_control( 'btn1_url', array( 'label' => 'Button 1 URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'btn2_label', array( 'label' => 'Button 2', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Demo Drive' ) );
		$this->add_control( 'btn2_url', array( 'label' => 'Button 2 URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'threshold', array(
			'label' => 'Reveal after scroll (px)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 600,
		) );
		$this->add_control( 'dismissible', array( 'label' => 'Dismissible', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="vc-sticky-cta" data-vc-sticky-cta data-threshold="<?php echo (int) $s['threshold']; ?>" hidden>
			<div class="vc-sticky-cta__inner">
				<div class="vc-sticky-cta__copy">
					<?php if ( ! empty( $s['eyebrow'] ) ) : ?><span class="vc-sticky-cta__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
					<strong class="vc-sticky-cta__label"><?php echo esc_html( $s['label'] ); ?></strong>
					<?php if ( ! empty( $s['sub'] ) ) : ?><span class="vc-sticky-cta__sub"><?php echo esc_html( $s['sub'] ); ?></span><?php endif; ?>
				</div>
				<div class="vc-sticky-cta__actions">
					<?php if ( ! empty( $s['btn1_label'] ) ) : ?>
						<a class="btn btn--dark" href="<?php echo esc_url( $s['btn1_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn1_label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['btn2_label'] ) ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $s['btn2_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn2_label'] ); ?></a>
					<?php endif; ?>
				</div>
				<?php if ( $s['dismissible'] === 'yes' ) : ?>
					<button type="button" class="vc-sticky-cta__close" aria-label="<?php esc_attr_e( 'Dismiss', 'voltcore' ); ?>" data-vc-sticky-cta-close>×</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
