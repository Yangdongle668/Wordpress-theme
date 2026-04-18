<?php
/**
 * VoltCore Product 360 — scroll/drag image-sequence viewer.
 *
 * Editor supplies N ordered frames (recommended 24-36). On the frontend,
 * the user can drag left/right or use the slider to rotate the product.
 * Auto-plays once on reveal (optional).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Product_360 extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-product-360'; }
	public function get_title() { return __( 'VoltCore Product 360', 'voltcore' ); }
	public function get_icon() { return 'eicon-360-rotate'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( '360', 'rotate', 'product', 'spin' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Every angle.' ) );
		$this->add_control( 'intro',   array( 'label' => 'Intro', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Drag to rotate. Or just look.' ) );
		$this->add_control( 'frames', array(
			'label' => 'Frames', 'type' => \Elementor\Controls_Manager::GALLERY,
			'default' => array(),
		) );
		$this->add_control( 'autoplay', array( 'label' => 'Auto-spin once on reveal', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$frames = array();
		foreach ( (array) ( $s['frames'] ?? array() ) as $f ) {
			if ( ! empty( $f['url'] ) ) $frames[] = esc_url( $f['url'] );
		}
		if ( count( $frames ) < 2 ) {
			// Fallback: at least show a placeholder
			$frames = array( VOLTCORE_URI . '/assets/images/hero-1.jpg' );
		}
		$json = wp_json_encode( $frames );
		?>
		<section class="vc-360" data-vc-product-360 data-autoplay="<?php echo esc_attr( $s['autoplay'] ); ?>">
			<div class="vc-360__head">
				<?php if ( ! empty( $s['eyebrow'] ) ) : ?><p class="vc-360__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $s['heading'] ) ) : ?><h2 class="vc-360__heading"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
				<?php if ( ! empty( $s['intro'] ) ) : ?><p class="vc-360__intro"><?php echo esc_html( $s['intro'] ); ?></p><?php endif; ?>
			</div>
			<div class="vc-360__stage" data-vc-360-stage data-frames="<?php echo esc_attr( $json ); ?>">
				<img class="vc-360__img" src="<?php echo esc_url( $frames[0] ); ?>" alt="" draggable="false">
				<span class="vc-360__hint"><?php esc_html_e( 'Drag to rotate', 'voltcore' ); ?></span>
			</div>
			<input class="vc-360__scrub" type="range" min="0" max="<?php echo max( 0, count( $frames ) - 1 ); ?>" step="1" value="0" data-vc-360-scrub aria-label="<?php esc_attr_e( 'Rotate', 'voltcore' ); ?>">
		</section>
		<?php
	}
}
