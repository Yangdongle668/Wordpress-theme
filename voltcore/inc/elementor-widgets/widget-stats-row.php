<?php
/**
 * VoltCore Stats Row — up to 6 animated counters in a single widget.
 * Repeater-driven: editors add/remove stats inline.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Stats_Row extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-stats-row'; }
	public function get_title() { return __( 'VoltCore Stats Row', 'voltcore' ); }
	public function get_icon() { return 'eicon-number-field'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'value',  array( 'label' => __( 'Value',  'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '640' ) );
		$repeater->add_control( 'suffix', array( 'label' => __( 'Suffix', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$repeater->add_control( 'label',  array( 'label' => __( 'Label',  'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'km range' ) );

		$this->add_control( 'stats', array(
			'label'       => __( 'Stats', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'value' => '640',   'suffix' => '',  'label' => 'km range' ),
				array( 'value' => '15',    'suffix' => '',  'label' => 'min fast charge' ),
				array( 'value' => '1.2',   'suffix' => 'M', 'label' => 'packs built' ),
				array( 'value' => '99.98', 'suffix' => '%', 'label' => 'pack uptime' ),
			),
			'title_field' => '{{{ value }}}{{{ suffix }}} — {{{ label }}}',
		) );

		$this->add_control( 'scheme', array(
			'label'   => __( 'Color scheme', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'light',
			'options' => array( 'light' => __( 'Light', 'voltcore' ), 'dark' => __( 'Dark', 'voltcore' ) ),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '4',
			'options' => array( '2' => '2', '3' => '3', '4' => '4', '6' => '6' ),
			'selectors' => array(
				'{{WRAPPER}} .vc-stats-row__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => __( 'Style', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'value_color', array(
			'label'     => __( 'Value color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .stat__value' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array( 'name' => 'value_typo', 'selector' => '{{WRAPPER}} .stat__value', 'label' => __( 'Value typography', 'voltcore' ) )
		);
		$this->add_control( 'label_color', array(
			'label'     => __( 'Label color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .stat__label' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array( 'name' => 'label_typo', 'selector' => '{{WRAPPER}} .stat__label', 'label' => __( 'Label typography', 'voltcore' ) )
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$scheme = $s['scheme'];
		?>
		<div class="vc-stats-row stats stats--<?php echo esc_attr( $scheme ); ?>">
			<div class="vc-stats-row__grid stats__grid">
				<?php foreach ( $s['stats'] as $i => $st ) :
					$count = trim( (string) $st['value'] ) . (string) $st['suffix'];
				?>
					<div class="stat stat--<?php echo esc_attr( $scheme ); ?>" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<div class="stat__value" data-count="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?></div>
						<div class="stat__label"><?php echo esc_html( $st['label'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
