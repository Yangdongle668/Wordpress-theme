<?php
/**
 * VoltCore Navbar — Elementor header widget.
 *
 * A tesla.com-accurate navigation bar with three behaviours:
 *   1. Transparent on hero → frosted-white on scroll.
 *   2. Hover mega-panel: hovering the primary menu opens a full-width
 *      white panel listing sub-products for that category.
 *   3. Right-side icon cluster: Shop, Account, Menu (full-viewport
 *      drawer on mobile AND desktop).
 *
 * Panels are configured via repeater so editors can tailor each top-level
 * item (Vehicles, Energy, Charging, Shop, …) with its own image + links.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Navbar extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-navbar'; }
	public function get_title() { return __( 'VoltCore Navbar', 'voltcore' ); }
	public function get_icon() { return 'eicon-nav-menu'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'header', 'navbar', 'menu', 'navigation', 'tesla' ); }

	protected function register_controls() {

		/* =====================  Content: logo  ===================== */
		$this->start_controls_section( 'section_logo', array( 'label' => __( 'Logo', 'voltcore' ) ) );

		$this->add_control( 'logo_source', array(
			'label'   => __( 'Logo source', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'site',
			'options' => array(
				'site'  => __( 'Site Identity', 'voltcore' ),
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
			'label'      => __( 'Logo height', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 10, 'max' => 80 ) ),
			'default'    => array( 'size' => 20, 'unit' => 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .vc-nav__logo img' => 'max-height: {{SIZE}}{{UNIT}}; width: auto;',
			),
		) );

		$this->end_controls_section();

		/* =====================  Content: primary items + mega-panels  ===================== */
		$this->start_controls_section( 'section_primary', array( 'label' => __( 'Primary items (with mega-panel)', 'voltcore' ) ) );

		$primary = new \Elementor\Repeater();

		$primary->add_control( 'label', array(
			'label'   => __( 'Label', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Vehicles',
		) );
		$primary->add_control( 'url', array(
			'label'   => __( 'URL', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$panel = new \Elementor\Repeater();
		$panel->add_control( 'pitem_image', array( 'label' => __( 'Card image', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => VOLTCORE_URI . '/assets/images/product-1.jpg' ) ) );
		$panel->add_control( 'pitem_title', array( 'label' => __( 'Title', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model S' ) );
		$panel->add_control( 'pitem_sub',   array( 'label' => __( 'Sub-label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$panel->add_control( 'pitem_cta1',  array( 'label' => __( 'Button 1 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$panel->add_control( 'pitem_cta1_url', array( 'label' => __( 'Button 1 URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$panel->add_control( 'pitem_cta2',  array( 'label' => __( 'Button 2 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Learn' ) );
		$panel->add_control( 'pitem_cta2_url', array( 'label' => __( 'Button 2 URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$primary->add_control( 'panel_items', array(
			'label'       => __( 'Mega-panel cards', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $panel->get_controls(),
			'title_field' => '{{{ pitem_title }}}',
			'default'     => array(),
		) );

		$primary->add_control( 'links_heading', array(
			'label' => __( 'Panel side-links (pipe-separated: Label|URL)', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'description' => __( 'One link per line. Appears under the image cards.', 'voltcore' ),
			'default' => "Inventory|#\nDemo Drive|#\nTrade-in|#",
		) );

		$this->add_control( 'primary_items', array(
			'label'       => __( 'Primary menu items', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $primary->get_controls(),
			'title_field' => '{{{ label }}}',
			'default'     => array(
				array( 'label' => 'Vehicles', 'url' => array( 'url' => '#vehicles' ) ),
				array( 'label' => 'Energy',   'url' => array( 'url' => '#energy'  ) ),
				array( 'label' => 'Charging', 'url' => array( 'url' => '#charging') ),
				array( 'label' => 'Discover', 'url' => array( 'url' => '#discover') ),
				array( 'label' => 'Shop',     'url' => array( 'url' => '#shop'    ) ),
			),
		) );

		$this->end_controls_section();

		/* =====================  Content: right-side icons  ===================== */
		$this->start_controls_section( 'section_right', array( 'label' => __( 'Right-side actions', 'voltcore' ) ) );

		$this->add_control( 'show_shop',    array( 'label' => __( 'Show Shop link', 'voltcore' ),    'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_account', array( 'label' => __( 'Show Account', 'voltcore' ),      'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_region',  array( 'label' => __( 'Show Region/Language', 'voltcore' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'account_url',  array( 'label' => __( 'Account URL', 'voltcore' ),       'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#account' ) ) );
		$this->add_control( 'shop_url',     array( 'label' => __( 'Shop URL', 'voltcore' ),          'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#shop' ) ) );

		$this->end_controls_section();

		/* =====================  Content: drawer side-links  ===================== */
		$this->start_controls_section( 'section_drawer', array( 'label' => __( 'Drawer (hamburger) side-links', 'voltcore' ) ) );

		$this->add_control( 'drawer_links', array(
			'label'       => __( 'Drawer sections (pipe-separated: Group|Label|URL)', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'description' => __( 'One link per line. Group name prefix groups links. Example: Existing Owners|Manage|/manage', 'voltcore' ),
			'default'     => "Existing Owners|Manage|/manage\nExisting Owners|Support|/support\nAbout|Careers|/careers\nAbout|About|/about\nAbout|Investors|/investors\nMore|Press|/press\nMore|Contact|/contact",
		) );

		$this->end_controls_section();

		/* =====================  Behaviour  ===================== */
		$this->start_controls_section( 'section_behaviour', array( 'label' => __( 'Behaviour', 'voltcore' ) ) );

		$this->add_control( 'scroll_mode', array(
			'label'   => __( 'Scroll behaviour', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'transparent',
			'options' => array(
				'transparent'        => __( 'Transparent → solid on scroll', 'voltcore' ),
				'solid'              => __( 'Always solid', 'voltcore' ),
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
			'label'     => __( 'Text (transparent)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .vc-nav:not(.is-solid)' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'color_text_solid', array(
			'label'     => __( 'Text (solid)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#171a20',
			'selectors' => array( '{{WRAPPER}} .vc-nav.is-solid' => 'color: {{VALUE}};' ),
		) );
		$this->add_control( 'color_bg_solid', array(
			'label'     => __( 'Background (solid)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(255,255,255,.95)',
			'selectors' => array( '{{WRAPPER}} .vc-nav.is-solid' => 'background: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$classes = array( 'vc-nav', 'vc-nav--mega' );
		$scroll  = $s['scroll_mode'] ?? 'transparent';
		if ( $scroll === 'solid' ) $classes[] = 'is-solid';
		$data_mode = esc_attr( $scroll );
		$primaries = is_array( $s['primary_items'] ?? null ) ? $s['primary_items'] : array();
		?>
		<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-vc-nav data-mode="<?php echo $data_mode; ?>" role="banner">
			<div class="vc-nav__inner">

				<div class="vc-nav__logo">
					<?php
					$home = esc_url( home_url( '/' ) );
					if ( $s['logo_source'] === 'image' && ! empty( $s['logo_image']['url'] ) ) {
						printf( '<a href="%s" aria-label="%s"><img src="%s" alt="%s"></a>',
							$home, esc_attr( get_bloginfo( 'name' ) ),
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
					<ul class="vc-nav__menu" data-vc-mega-menu>
						<?php foreach ( $primaries as $idx => $item ) :
							$label = $item['label'];
							$url   = $item['url']['url'] ?? '#';
							$has_panel = ! empty( $item['panel_items'] ) || ! empty( trim( (string) $item['links_heading'] ) );
							$panel_id  = 'vc-megapanel-' . $idx;
						?>
						<li class="vc-nav__item<?php echo $has_panel ? ' has-panel' : ''; ?>"
							<?php if ( $has_panel ) : ?>data-panel="<?php echo esc_attr( $panel_id ); ?>"<?php endif; ?>>
							<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
						</li>
						<?php endforeach; ?>
					</ul>
				</nav>

				<div class="vc-nav__right">
					<?php if ( $s['show_shop'] === 'yes' ) : ?>
						<a class="vc-nav__icon-link" href="<?php echo esc_url( $s['shop_url']['url'] ?? '#shop' ); ?>"><?php esc_html_e( 'Shop', 'voltcore' ); ?></a>
					<?php endif; ?>
					<?php if ( $s['show_account'] === 'yes' ) : ?>
						<a class="vc-nav__icon-link vc-nav__icon-link--account" href="<?php echo esc_url( $s['account_url']['url'] ?? '#account' ); ?>"
							aria-label="<?php esc_attr_e( 'Account', 'voltcore' ); ?>"
							data-vc-modal-open="account">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( $s['show_region'] === 'yes' ) : ?>
						<button type="button" class="vc-nav__icon-link vc-nav__icon-link--region" data-vc-modal-open="region" aria-label="<?php esc_attr_e( 'Region and language', 'voltcore' ); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg>
						</button>
					<?php endif; ?>
					<button class="vc-nav__menu-btn" aria-expanded="false" aria-controls="vc-nav-drawer" data-vc-nav-toggle>
						<span class="vc-nav__menu-btn-label"><?php esc_html_e( 'Menu', 'voltcore' ); ?></span>
					</button>
				</div>
			</div>

			<?php /* Mega panels (desktop hover/focus) */
			foreach ( $primaries as $idx => $item ) :
				$cards = is_array( $item['panel_items'] ?? null ) ? $item['panel_items'] : array();
				$raw_links = trim( (string) ( $item['links_heading'] ?? '' ) );
				$side_links = array();
				if ( $raw_links !== '' ) {
					foreach ( preg_split( '/\r?\n/', $raw_links ) as $ln ) {
						$parts = array_map( 'trim', explode( '|', $ln ) );
						if ( count( $parts ) >= 1 && $parts[0] !== '' ) {
							$side_links[] = array(
								'label' => $parts[0],
								'url'   => $parts[1] ?? '#',
							);
						}
					}
				}
				if ( empty( $cards ) && empty( $side_links ) ) continue;
				$panel_id = 'vc-megapanel-' . $idx;
			?>
			<div id="<?php echo esc_attr( $panel_id ); ?>" class="vc-nav__mega" data-mega-panel hidden>
				<div class="vc-nav__mega-inner">
					<?php if ( ! empty( $cards ) ) : ?>
						<ul class="vc-nav__mega-cards">
						<?php foreach ( $cards as $card ) : ?>
							<li class="vc-nav__mega-card">
								<?php if ( ! empty( $card['pitem_image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $card['pitem_image']['url'] ); ?>" alt="<?php echo esc_attr( $card['pitem_title'] ); ?>">
								<?php endif; ?>
								<div class="vc-nav__mega-title"><?php echo esc_html( $card['pitem_title'] ); ?></div>
								<?php if ( ! empty( $card['pitem_sub'] ) ) : ?>
									<div class="vc-nav__mega-sub"><?php echo esc_html( $card['pitem_sub'] ); ?></div>
								<?php endif; ?>
								<div class="vc-nav__mega-ctas">
									<?php if ( ! empty( $card['pitem_cta1'] ) ) : ?>
										<a href="<?php echo esc_url( $card['pitem_cta1_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $card['pitem_cta1'] ); ?></a>
									<?php endif; ?>
									<?php if ( ! empty( $card['pitem_cta2'] ) ) : ?>
										<a href="<?php echo esc_url( $card['pitem_cta2_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $card['pitem_cta2'] ); ?></a>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( ! empty( $side_links ) ) : ?>
						<ul class="vc-nav__mega-links">
						<?php foreach ( $side_links as $sl ) : ?>
							<li><a href="<?php echo esc_url( $sl['url'] ); ?>"><?php echo esc_html( $sl['label'] ); ?></a></li>
						<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</header>

		<?php /* Drawer: grouped side-links from textarea */
		$groups = array();
		$raw = trim( (string) ( $s['drawer_links'] ?? '' ) );
		if ( $raw !== '' ) {
			foreach ( preg_split( '/\r?\n/', $raw ) as $ln ) {
				$parts = array_map( 'trim', explode( '|', $ln ) );
				if ( count( $parts ) < 2 ) continue;
				$g = $parts[0]; $lab = $parts[1]; $url = $parts[2] ?? '#';
				$groups[ $g ][] = array( 'label' => $lab, 'url' => $url );
			}
		}
		?>
		<aside id="vc-nav-drawer" class="vc-nav__drawer" data-vc-nav-drawer hidden aria-label="<?php esc_attr_e( 'Site menu', 'voltcore' ); ?>">
			<div class="vc-nav__drawer-inner">
				<button class="vc-nav__drawer-close" data-vc-nav-toggle aria-label="<?php esc_attr_e( 'Close menu', 'voltcore' ); ?>">&times;</button>
				<div class="vc-nav__drawer-grid">
				<?php foreach ( $groups as $g => $links ) : ?>
					<div class="vc-nav__drawer-group">
						<h3 class="vc-nav__drawer-group-title"><?php echo esc_html( $g ); ?></h3>
						<ul>
						<?php foreach ( $links as $l ) : ?>
							<li><a href="<?php echo esc_url( $l['url'] ); ?>"><?php echo esc_html( $l['label'] ); ?></a></li>
						<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		</aside>
		<?php
	}
}
