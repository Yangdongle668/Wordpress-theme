<?php
/**
 * VoltCore Marquee — infinite horizontal scrolling text band.
 * Single-line CSS animation; pauses on hover. Useful for
 * "Shipping globally" / "Powering the grid" / "Cells, packs,
 * systems" brand statements.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Marquee extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-marquee'; }
	public function get_title() { return __( 'VoltCore Marquee', 'voltcore' ); }
	public function get_icon() { return 'eicon-slideshow'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'items', array(
			'label'       => __( 'Items', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'default'     => "Cells\nPacks\nSystems\nBMS\nBMS 2.0\nFirmware\nFactory",
			'description' => __( 'One phrase per line.', 'voltcore' ),
		) );

		$this->add_control( 'separator', array(
			'label'   => __( 'Separator', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/',
		) );

		$this->add_control( 'duration', array(
			'label'      => __( 'Speed (seconds per loop)', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::NUMBER,
			'default'    => 40,
			'min'        => 5, 'max' => 120,
			'selectors'  => array( '{{WRAPPER}} .vc-marquee__track' => 'animation-duration: {{VALUE}}s;' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => __( 'Style', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'bg_color', array(
			'label'     => __( 'Background', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#000000',
			'selectors' => array( '{{WRAPPER}} .vc-marquee' => 'background: {{VALUE}};' ),
		) );
		$this->add_control( 'text_color', array(
			'label'     => __( 'Text color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .vc-marquee' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'sep_color', array(
			'label'     => __( 'Separator color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#3457dc',
			'selectors' => array( '{{WRAPPER}} .vc-marquee__sep' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
			'name' => 'typo', 'selector' => '{{WRAPPER}} .vc-marquee__track', 'label' => __( 'Typography', 'voltcore' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = array_filter( array_map( 'trim', explode( "\n", $s['items'] ) ) );
		if ( ! $items ) return;
		$sep = $s['separator'] ?: '/';
		// Repeat twice for a seamless loop
		$rendered = '';
		foreach ( array( 1, 2 ) as $_pass ) {
			foreach ( $items as $item ) {
				$rendered .= '<span class="vc-marquee__item">' . esc_html( $item ) . '</span><span class="vc-marquee__sep" aria-hidden="true">' . esc_html( $sep ) . '</span>';
			}
		}
		?>
		<section class="vc-marquee" aria-label="<?php esc_attr_e( 'Marquee', 'voltcore' ); ?>">
			<div class="vc-marquee__track"><?php echo $rendered; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</section>
		<?php
	}
}
