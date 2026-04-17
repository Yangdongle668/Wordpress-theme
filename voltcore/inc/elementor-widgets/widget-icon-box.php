<?php
/**
 * VoltCore Icon Box — icon/image + title + text + optional link.
 * Good for values grids, features, service lists.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Icon_Box extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-icon-box'; }
	public function get_title() { return __( 'VoltCore Icon Box', 'voltcore' ); }
	public function get_icon() { return 'eicon-icon-box'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::ICONS,
			'default' => array( 'value' => 'eicon-bolt', 'library' => 'eicons' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Energy density',
		) );

		$this->add_control( 'text', array(
			'label'   => __( 'Description', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We measure ourselves by watt-hours per kilogram — not by press releases.',
		) );

		$this->add_control( 'link', array(
			'label'   => __( 'Link', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '' ),
		) );

		$this->add_control( 'link_label', array(
			'label'     => __( 'Link label', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => 'Learn more',
			'condition' => array( 'link[url]!' => '' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array(
			'label' => __( 'Style', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'icon_color', array(
			'label'     => __( 'Icon color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'var(--vc-accent)',
			'selectors' => array( '{{WRAPPER}} .vc-icon-box__icon' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'icon_size', array(
			'label'      => __( 'Icon size', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 16, 'max' => 96 ) ),
			'default'    => array( 'size' => 32, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .vc-icon-box__icon, {{WRAPPER}} .vc-icon-box__icon svg' => 'font-size: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'title_color', array(
			'label'     => __( 'Title color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#171a20',
			'selectors' => array( '{{WRAPPER}} .vc-icon-box__title' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(),
			array( 'name' => 'title_typo', 'label' => __( 'Title typography', 'voltcore' ), 'selector' => '{{WRAPPER}} .vc-icon-box__title' )
		);
		$this->add_control( 'text_color', array(
			'label'     => __( 'Description color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#5c5e62',
			'selectors' => array( '{{WRAPPER}} .vc-icon-box__text' => 'color: {{VALUE}};' ),
		) );
		$this->add_responsive_control( 'align', array(
			'label'     => __( 'Alignment', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::CHOOSE,
			'default'   => 'left',
			'options'   => array(
				'left'   => array( 'title' => __( 'Left', 'voltcore' ),   'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => __( 'Center', 'voltcore' ), 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => __( 'Right', 'voltcore' ),  'icon' => 'eicon-text-align-right' ),
			),
			'selectors' => array( '{{WRAPPER}} .vc-icon-box' => 'text-align: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$has_link = ! empty( $s['link']['url'] );
		?>
		<div class="vc-icon-box" data-fade>
			<?php if ( ! empty( $s['icon']['value'] ) ) : ?>
				<div class="vc-icon-box__icon">
					<?php \Elementor\Icons_Manager::render_icon( $s['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $s['title'] ) : ?><h3 class="vc-icon-box__title"><?php echo esc_html( $s['title'] ); ?></h3><?php endif; ?>
			<?php if ( $s['text'] ) : ?><p class="vc-icon-box__text"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
			<?php if ( $has_link ) : ?>
				<a class="vc-icon-box__link" href="<?php echo esc_url( $s['link']['url'] ); ?>"><?php echo esc_html( $s['link_label'] ); ?> →</a>
			<?php endif; ?>
		</div>
		<?php
	}
}
