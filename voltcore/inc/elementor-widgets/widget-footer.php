<?php
/**
 * VoltCore Footer — Elementor footer widget.
 *
 * A complete tesla.com-style footer: up to 5 link columns (title +
 * list of links), a bottom bar with copyright and optional footer
 * menu. Repeater-driven so editors add/remove columns and links
 * directly in the Elementor panel.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Footer extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-footer'; }
	public function get_title() { return __( 'VoltCore Footer', 'voltcore' ); }
	public function get_icon() { return 'eicon-footer'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		/* =====================  Columns (repeater)  ===================== */
		$this->start_controls_section( 'section_columns', array( 'label' => __( 'Columns', 'voltcore' ) ) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'title', array(
			'label'   => __( 'Column title', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Column',
		) );
		$repeater->add_control( 'links', array(
			'label'       => __( 'Links', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'default'     => "Link|/about/\nLink|/contact/",
			'description' => __( 'One per line. Format: Label|URL', 'voltcore' ),
		) );

		$this->add_control( 'columns', array(
			'label'       => __( 'Columns', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'title' => 'VoltCore', 'links' => "High-density batteries for the electric era.|" ),
				array( 'title' => 'Products', 'links' => "Cell 4680|/products/cell-4680/\nPack P-500|/products/pack-p500/\nGrid Node|/products/grid-node/" ),
				array( 'title' => 'Company',  'links' => "About|/about/\nJournal|/blog/\nContact|/contact/\nCareers|/careers/" ),
				array( 'title' => 'Legal',    'links' => "Privacy|/privacy/\nTerms|/terms/\nCookies|/cookies/\nPress|/press/" ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();

		/* =====================  Bottom bar  ===================== */
		$this->start_controls_section( 'section_bottom', array( 'label' => __( 'Bottom bar', 'voltcore' ) ) );

		$this->add_control( 'copyright', array(
			'label'   => __( 'Copyright', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '© ' . date( 'Y' ) . ' VoltCore. All rights reserved.',
		) );

		$this->add_control( 'show_footer_menu', array(
			'label'   => __( 'Show Footer Menu', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();

		/* =====================  Style  ===================== */
		$this->start_controls_section( 'section_style_colors', array(
			'label' => __( 'Colors', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'bg_color', array(
			'label'     => __( 'Background', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#fafafa',
			'selectors' => array( '{{WRAPPER}} .vc-footer' => 'background: {{VALUE}};' ),
		) );
		$this->add_control( 'title_color', array(
			'label'     => __( 'Column title', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#171a20',
			'selectors' => array( '{{WRAPPER}} .vc-footer__col h4' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'link_color', array(
			'label'     => __( 'Link', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#5c5e62',
			'selectors' => array( '{{WRAPPER}} .vc-footer a' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'link_hover_color', array(
			'label'     => __( 'Link hover', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#171a20',
			'selectors' => array( '{{WRAPPER}} .vc-footer a:hover' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_typo', array(
			'label' => __( 'Typography', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'label'    => __( 'Column title', 'voltcore' ),
				'selector' => '{{WRAPPER}} .vc-footer__col h4',
			)
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typo',
				'label'    => __( 'Links', 'voltcore' ),
				'selector' => '{{WRAPPER}} .vc-footer a',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<footer class="vc-footer site-footer">
			<div class="vc-footer__widgets site-footer__widgets">
				<?php foreach ( $s['columns'] as $col ) :
					$links = array_filter( array_map( 'trim', explode( "\n", $col['links'] ) ) );
				?>
					<div class="vc-footer__col site-footer__col">
						<?php if ( ! empty( $col['title'] ) ) : ?>
							<h4 class="widget-title"><?php echo esc_html( $col['title'] ); ?></h4>
						<?php endif; ?>
						<?php if ( $links ) : ?>
							<ul>
								<?php foreach ( $links as $ln ) :
									$parts = array_map( 'trim', explode( '|', $ln ) );
									if ( count( $parts ) !== 2 ) {
										// Single-cell entry → show as plain paragraph
										echo '<li style="list-style:none"><span>' . esc_html( $parts[0] ) . '</span></li>';
										continue;
									}
									list( $label, $url ) = $parts;
									if ( empty( $url ) ) {
										echo '<li style="list-style:none"><span>' . esc_html( $label ) . '</span></li>';
									} else {
										printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
									}
								endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="vc-footer__bottom site-footer__bottom">
				<div class="site-footer__copy"><?php echo wp_kses_post( $s['copyright'] ); ?></div>
				<?php if ( $s['show_footer_menu'] === 'yes' && has_nav_menu( 'footer' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'depth'          => 1,
					) );
				} ?>
			</div>
		</footer>
		<?php
	}
}
