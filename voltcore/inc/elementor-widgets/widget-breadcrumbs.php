<?php
/**
 * VoltCore Breadcrumbs — wraps voltcore_breadcrumbs() as a widget so
 * editors can drop it anywhere in a layout.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Breadcrumbs extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-breadcrumbs'; }
	public function get_title() { return __( 'VoltCore Breadcrumbs', 'voltcore' ); }
	public function get_icon() { return 'eicon-arrow-right'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'voltcore' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );

		$this->add_control( 'align', array(
			'label'   => __( 'Alignment', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::CHOOSE,
			'default' => 'left',
			'options' => array(
				'left'   => array( 'title' => 'Left',   'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ),
			),
			'selectors' => array(
				'{{WRAPPER}} .breadcrumbs__list' => 'justify-content: {{VALUE}};',
			),
		) );
		$this->add_control( 'color', array(
			'label'     => __( 'Text color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .breadcrumbs' => 'color: {{VALUE}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( function_exists( 'voltcore_breadcrumbs' ) ) {
			voltcore_breadcrumbs();
		}
	}
}
