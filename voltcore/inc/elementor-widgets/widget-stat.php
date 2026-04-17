<?php
/**
 * VoltCore Stat Elementor widget — animated number + label.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Stat extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-stat'; }
	public function get_title() { return __( 'VoltCore Stat', 'voltcore' ); }
	public function get_icon() { return 'eicon-counter'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'value', array(
			'label'   => __( 'Value (supports decimals and suffix)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '640',
			'description' => __( 'Example: 640, 99.98, 12', 'voltcore' ),
		) );

		$this->add_control( 'suffix', array(
			'label'   => __( 'Suffix', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
			'description' => __( 'e.g. %, km, GWh', 'voltcore' ),
		) );

		$this->add_control( 'label', array(
			'label'   => __( 'Label', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'km range',
		) );

		$this->add_control( 'scheme', array(
			'label'   => __( 'Color Scheme', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'light',
			'options' => array(
				'light' => __( 'On light background', 'voltcore' ),
				'dark'  => __( 'On dark background', 'voltcore' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$count = trim( (string) $s['value'] ) . (string) $s['suffix'];
		?>
		<div class="stat stat--elementor stat--<?php echo esc_attr( $s['scheme'] ); ?>" data-fade>
			<div class="stat__value" data-count="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?></div>
			<div class="stat__label"><?php echo esc_html( $s['label'] ); ?></div>
		</div>
		<style>
		.stat--light .stat__value { color: var(--vc-ink); }
		.stat--light .stat__label { color: var(--vc-muted); }
		</style>
		<?php
	}
}
