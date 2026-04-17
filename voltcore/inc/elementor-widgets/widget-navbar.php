<?php
/**
 * VoltCore Navbar — Elementor header widget.
 *
 * A complete tesla.com-style navigation bar: logo (Site Identity or
 * image override), horizontal menu (pulled from a WP nav menu), CTA
 * button and mobile toggle. Full Style tab controls colors, typography,
 * spacing and behaviour (transparent / sticky-to-solid / solid).
 *
 * Designed to be dropped into an Elementor Pro Header template (or the
 * VoltCore Theme Builder "header" slot).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Navbar extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-navbar'; }
	public function get_title() { return __( 'VoltCore Navbar', 'voltcore' ); }
	public function get_icon() { return 'eicon-nav-menu'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'header', 'navbar', 'menu', 'navigation' ); }

	protected function register_controls() {

		/* =====================  Content  ===================== */
		$this->start_controls_section( 'section_logo', array( 'label' => __( 'Logo', 'voltcore' ) ) );

		$this->add_control( 'logo_source', array(
			'label'   => __( 'Logo source', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'site',
			'options' => array(
				'site'  => __( 'Site Identity (WP custom logo or site name)', 'voltcore' ),
				'image' => __( 'Custom image', 'voltcore' ),
				'text'  => __( 'Text', 'voltcore' ),
			),
		) );

		$this->add_control( 'logo_image', array(
			'label'     => __( 'Logo image', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::MEDIA,
			'default'   => array( 'url' => VOLTCORE_URI . '/assets/images/logo.svg' ),
			'condition' => array( 'logo_source' => 'image' ),
		) );

		$this->add_control( 'logo_text', array(
			'label'     => __( 'Logo text', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => 'VOLTCORE',
			'condition' => array( 'logo_source' => 'text' ),
		) );

		$this->add_responsive_control( 'logo_height', array(
			'label'      => __( 'Logo height (image)', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 10, 'max' => 80 ) ),
			'default'    => array( 'size' => 20, 'unit' => 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .vc-nav__logo img' => 'max-height: {{SIZE}}{{UNIT}}; width: auto;',
			),
		) );

		$this->end_controls_section();

		/* Menu */
		$this->start_controls_section( 'section_menu', array( 'label' => __( 'Menu', 'voltcore' ) ) );

		$menus = wp_get_nav_menus();
		$menu_choices = array( '' => __( '— Use Primary Menu location —', 'voltcore' ) );
		foreach ( $menus as $m ) {
			$menu_choices[ $m->slug ] = $m->name;
		}

		$this->add_control( 'menu_slug', array(
			'label'   => __( 'Menu', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => $menu_choices,
		) );

		$this->end_controls_section();

		/* CTA button */
		$this->start_controls_section( 'section_cta', array( 'label' => __( 'Right-side CTA', 'voltcore' ) ) );

		$this->add_control( 'cta_show', array(
			'label'   => __( 'Show CTA', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );
		$this->add_control( 'cta_label', array(
			'label'     => __( 'CTA label', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => 'Contact',
			'condition' => array( 'cta_show' => 'yes' ),
		) );
		$this->add_control( 'cta_url', array(
			'label'     => __( 'CTA URL', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::URL,
			'default'   => array( 'url' => '/contact/' ),
			'condition' => array( 'cta_show' => 'yes' ),
		) );

		$this->end_controls_section();

		/* Behaviour */
		$this->start_controls_section( 'section_behaviour', array( 'label' => __( 'Behaviour', 'voltcore' ) ) );

		$this->add_control( 'scroll_mode', array(
			'label'   => __( 'Scroll behaviour', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'transparent',
			'options' => array(
				'transparent' => __( 'Transparent → solid on scroll', 'voltcore' ),
				'solid'       => __( 'Always solid', 'voltcore' ),
				'transparent-always' => __( 'Always transparent', 'voltcore' ),
			),
		) );

		$this->add_control( 'height', array(
			'label'      => __( 'Header height', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 40, 'max' => 120 ) ),
			'default'    => array( 'size' => 56, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .vc-nav' => 'height: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();

		/* =====================  Style  ===================== */
		$this->start_controls_section( 'section_style_colors', array(
			'label' => __( 'Colors', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'color_text_transparent', array(
			'label'     => __( 'Text (transparent state)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .vc-nav:not(.is-solid)' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'color_text_solid', array(
			'label'     => __( 'Text (solid state)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#171a20',
			'selectors' => array( '{{WRAPPER}} .vc-nav.is-solid' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'color_bg_solid', array(
			'label'     => __( 'Background (solid state)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(255,255,255,.92)',
			'selectors' => array( '{{WRAPPER}} .vc-nav.is-solid' => 'background: {{VALUE}};' ),
		) );
		$this->add_control( 'color_hover_pill', array(
			'label'     => __( 'Menu item hover pill', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(255,255,255,.10)',
			'selectors' => array( '{{WRAPPER}} .vc-nav__menu a:hover' => 'background: {{VALUE}};' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_typo', array(
			'label' => __( 'Typography', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'menu_typo',
				'label'    => __( 'Menu typography', 'voltcore' ),
				'selector' => '{{WRAPPER}} .vc-nav__menu a',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'logo_typo',
				'label'    => __( 'Logo text typography', 'voltcore' ),
				'selector' => '{{WRAPPER}} .vc-nav__logo .site-title',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_style_spacing', array(
			'label' => __( 'Spacing', 'voltcore' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'padding', array(
			'label'      => __( 'Horizontal padding', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
			'default'    => array( 'size' => 24, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .vc-nav' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'menu_gap', array(
			'label'      => __( 'Menu item gap', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'default'    => array( 'size' => 4, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .vc-nav__menu' => 'gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$classes = array( 'vc-nav' );
		$scroll  = $s['scroll_mode'] ?? 'transparent';
		if ( $scroll === 'solid' ) $classes[] = 'is-solid';
		$data_mode = esc_attr( $scroll );
		?>
		<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-vc-nav data-mode="<?php echo $data_mode; ?>">
			<div class="vc-nav__inner">

				<div class="vc-nav__logo">
					<?php
					$home = esc_url( home_url( '/' ) );
					if ( $s['logo_source'] === 'image' && ! empty( $s['logo_image']['url'] ) ) {
						printf( '<a href="%s"><img src="%s" alt="%s"></a>',
							$home,
							esc_url( $s['logo_image']['url'] ),
							esc_attr( get_bloginfo( 'name' ) )
						);
					} elseif ( $s['logo_source'] === 'text' ) {
						printf( '<a class="site-title" href="%s">%s</a>', $home, esc_html( $s['logo_text'] ) );
					} else {
						if ( has_custom_logo() ) {
							the_custom_logo();
						} else {
							printf( '<a class="site-title" href="%s">%s</a>', $home, esc_html( get_bloginfo( 'name' ) ) );
						}
					}
					?>
				</div>

				<nav class="vc-nav__menu-wrap" aria-label="<?php esc_attr_e( 'Primary', 'voltcore' ); ?>">
					<?php
					$args = array(
						'container' => false,
						'menu_class' => 'vc-nav__menu',
						'depth' => 1,
						'fallback_cb' => '__return_empty_string',
					);
					if ( ! empty( $s['menu_slug'] ) ) {
						$args['menu'] = $s['menu_slug'];
					} else {
						$args['theme_location'] = 'primary';
					}
					wp_nav_menu( $args );
					?>
				</nav>

				<div class="vc-nav__right">
					<?php if ( $s['cta_show'] === 'yes' && ! empty( $s['cta_label'] ) ) : ?>
						<a class="vc-nav__cta" href="<?php echo esc_url( $s['cta_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['cta_label'] ); ?></a>
					<?php endif; ?>
					<button class="vc-nav__toggle" aria-expanded="false" aria-controls="vc-nav-drawer" data-vc-nav-toggle>
						<span></span><span></span><span></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'voltcore' ); ?></span>
					</button>
				</div>
			</div>
		</header>

		<aside id="vc-nav-drawer" class="vc-nav__drawer" data-vc-nav-drawer hidden>
			<?php
			$drawer_args = $args;
			$drawer_args['menu_class'] = 'vc-nav__drawer-menu';
			wp_nav_menu( $drawer_args );
			?>
		</aside>
		<?php
	}
}
