<?php
/**
 * VoltCore CTA Elementor widget — a centred heading + button band,
 * light or dark.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_CTA extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-cta'; }
	public function get_title() { return __( 'VoltCore CTA Strip', 'voltcore' ); }
	public function get_icon() { return 'eicon-call-to-action'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Power what comes next.',
		) );

		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Contact Sales',
		) );

		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$this->add_control( 'scheme', array(
			'label'   => __( 'Color Scheme', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'dark',
			'options' => array(
				'dark'  => __( 'Dark (white button)', 'voltcore' ),
				'light' => __( 'Light (dark button)', 'voltcore' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$is_light = $s['scheme'] === 'light';
		?>
		<section class="cta-strip cta-strip--<?php echo $is_light ? 'light' : 'dark'; ?>" data-fade>
			<h2 class="cta-strip__title"><?php echo esc_html( $s['title'] ); ?></h2>
			<?php if ( ! empty( $s['btn_label'] ) ) : ?>
				<a class="btn <?php echo $is_light ? 'btn--dark' : 'btn--light'; ?>" href="<?php echo esc_url( $s['btn_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn_label'] ); ?></a>
			<?php endif; ?>
		</section>
		<style>
		.cta-strip--light { background: var(--vc-surface); color: var(--vc-ink); }
		.cta-strip--light .cta-strip__title { color: var(--vc-ink); }
		</style>
		<?php
	}
}
