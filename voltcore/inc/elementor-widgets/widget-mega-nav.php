<?php
/**
 * VoltCore Mega Nav — tesla.com-style navbar with vehicle mega-menu,
 * account drawer trigger and a full right-side slide-in drawer.
 *
 * Each nav item can optionally open a mega-panel with up to 6 vehicle
 * thumbnails (image + title + starting price + CTA). When a panel is
 * open, the whole navbar switches to a solid-white state and a thin
 * panel slides down from the header. On mobile the same items feed
 * into a full-screen right drawer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Mega_Nav extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-mega-nav'; }
	public function get_title() { return __( 'VoltCore Mega Nav', 'voltcore' ); }
	public function get_icon() { return 'eicon-site-search'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'mega', 'nav', 'menu', 'tesla', 'header' ); }

	protected function register_controls() {

		$this->start_controls_section( 'logo', array( 'label' => __( 'Logo', 'voltcore' ) ) );
		$this->add_control( 'logo_source', array(
			'label'   => 'Logo source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'site',
			'options' => array( 'site' => 'Site identity', 'image' => 'Image', 'text' => 'Text' ),
		) );
		$this->add_control( 'logo_text', array( 'label' => 'Logo text', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'VOLTCORE', 'condition' => array( 'logo_source' => 'text' ) ) );
		$this->add_control( 'logo_image', array( 'label' => 'Logo image', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => array( 'logo_source' => 'image' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'nav', array( 'label' => __( 'Nav items', 'voltcore' ) ) );

		$vehicle = new \Elementor\Repeater();
		$vehicle->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$vehicle->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model V' ) );
		$vehicle->add_control( 'price', array( 'label' => 'Price/sub', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'From $39,990' ) );
		$vehicle->add_control( 'link1_l', array( 'label' => 'Link 1 label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$vehicle->add_control( 'link1_u', array( 'label' => 'Link 1 URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$vehicle->add_control( 'link2_l', array( 'label' => 'Link 2 label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Demo Drive' ) );
		$vehicle->add_control( 'link2_u', array( 'label' => 'Link 2 URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$item = new \Elementor\Repeater();
		$item->add_control( 'label', array( 'label' => 'Label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Vehicles' ) );
		$item->add_control( 'url', array( 'label' => 'URL (if no mega)', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$item->add_control( 'has_mega', array( 'label' => 'Show mega panel', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$item->add_control( 'vehicles', array(
			'label'       => 'Mega panel items',
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $vehicle->get_controls(),
			'title_field' => '{{{ title }}}',
			'condition'   => array( 'has_mega' => 'yes' ),
			'default'     => array(
				array( 'title' => 'Model V',   'price' => 'From $39,990', 'link1_l' => 'Custom Order', 'link2_l' => 'Demo Drive' ),
				array( 'title' => 'Powerwall', 'price' => 'From $6,500',  'link1_l' => 'Order',        'link2_l' => 'Learn More' ),
				array( 'title' => 'Megapack',  'price' => 'Request quote','link1_l' => 'Quote',        'link2_l' => 'Spec sheet' ),
			),
		) );
		$item->add_control( 'mega_links', array(
			'label'     => 'Mega panel side links (one per line: Label|URL)',
			'type'      => \Elementor\Controls_Manager::TEXTAREA,
			'default'   => "Existing Inventory|/inventory/\nUsed Inventory|/used/\nTrade-in|/trade-in/\nDemo Drive|/demo-drive/",
			'condition' => array( 'has_mega' => 'yes' ),
		) );

		$this->add_control( 'items', array(
			'label'       => 'Primary items',
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $item->get_controls(),
			'title_field' => '{{{ label }}}',
			'default'     => array(
				array( 'label' => 'Vehicles',  'has_mega' => 'yes', 'url' => array( 'url' => '/vehicles/' ) ),
				array( 'label' => 'Energy',    'has_mega' => 'yes', 'url' => array( 'url' => '/energy/' ) ),
				array( 'label' => 'Charging',  'has_mega' => 'no',  'url' => array( 'url' => '/charging/' ) ),
				array( 'label' => 'Discover',  'has_mega' => 'no',  'url' => array( 'url' => '/discover/' ) ),
				array( 'label' => 'Shop',      'has_mega' => 'no',  'url' => array( 'url' => '/shop/' ) ),
			),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'actions', array( 'label' => __( 'Right-side actions', 'voltcore' ) ) );
		$this->add_control( 'show_support', array( 'label' => 'Show Support', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'support_url',  array( 'label' => 'Support URL',  'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '/support/' ) ) );
		$this->add_control( 'show_shop',    array( 'label' => 'Show Shop',    'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'shop_url',     array( 'label' => 'Shop URL',     'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '/shop/' ) ) );
		$this->add_control( 'show_account', array( 'label' => 'Show Account', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'behaviour', array( 'label' => __( 'Behaviour', 'voltcore' ) ) );
		$this->add_control( 'scroll_mode', array(
			'label'   => 'Scroll mode', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'transparent',
			'options' => array( 'transparent' => 'Transparent → solid on scroll', 'solid' => 'Always solid', 'transparent-always' => 'Always transparent' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$scheme = $s['scroll_mode'];
		$classes = array( 'vc-nav', 'vc-mega' );
		if ( $scheme === 'solid' ) $classes[] = 'is-solid';
		?>
		<header class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-vc-nav data-mode="<?php echo esc_attr( $scheme ); ?>" data-vc-mega>
			<div class="vc-nav__inner">
				<div class="vc-nav__logo">
					<?php
					$home = esc_url( home_url( '/' ) );
					if ( $s['logo_source'] === 'image' && ! empty( $s['logo_image']['url'] ) ) {
						printf( '<a href="%s"><img src="%s" alt="%s" class="vc-mega__logo-img"></a>', $home, esc_url( $s['logo_image']['url'] ), esc_attr( get_bloginfo( 'name' ) ) );
					} elseif ( $s['logo_source'] === 'text' ) {
						printf( '<a class="site-title" href="%s">%s</a>', $home, esc_html( $s['logo_text'] ) );
					} else {
						has_custom_logo() ? the_custom_logo() : printf( '<a class="site-title" href="%s">%s</a>', $home, esc_html( get_bloginfo( 'name' ) ) );
					}
					?>
				</div>

				<nav class="vc-mega__primary" aria-label="<?php esc_attr_e( 'Primary', 'voltcore' ); ?>">
					<ul class="vc-mega__list">
						<?php foreach ( $s['items'] as $idx => $it ) :
							$has_mega = ( $it['has_mega'] ?? 'no' ) === 'yes' && ! empty( $it['vehicles'] );
							$url = $it['url']['url'] ?? '#';
							$target = 'vc-mega-panel-' . $idx;
						?>
							<li class="vc-mega__li">
								<?php if ( $has_mega ) : ?>
									<button type="button" class="vc-mega__trigger" data-vc-mega-trigger="<?php echo esc_attr( $target ); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr( $target ); ?>">
										<?php echo esc_html( $it['label'] ); ?>
									</button>
								<?php else : ?>
									<a class="vc-mega__link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $it['label'] ); ?></a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>

				<div class="vc-nav__right vc-mega__right">
					<?php if ( $s['show_shop'] === 'yes' ) : ?>
						<a class="vc-mega__action" href="<?php echo esc_url( $s['shop_url']['url'] ?? '/shop/' ); ?>"><?php esc_html_e( 'Shop', 'voltcore' ); ?></a>
					<?php endif; ?>
					<?php if ( $s['show_account'] === 'yes' ) : ?>
						<button type="button" class="vc-mega__action" data-vc-account-open aria-haspopup="dialog"><?php esc_html_e( 'Account', 'voltcore' ); ?></button>
					<?php endif; ?>
					<button class="vc-nav__toggle" aria-expanded="false" aria-controls="vc-mega-drawer" data-vc-nav-toggle>
						<span></span><span></span><span></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'voltcore' ); ?></span>
					</button>
				</div>
			</div>

			<?php foreach ( $s['items'] as $idx => $it ) :
				$has_mega = ( $it['has_mega'] ?? 'no' ) === 'yes' && ! empty( $it['vehicles'] );
				if ( ! $has_mega ) continue;
				$target = 'vc-mega-panel-' . $idx;
				$side_links = array_values( array_filter( array_map( 'trim', explode( "\n", (string) ( $it['mega_links'] ?? '' ) ) ) ) );
			?>
				<div class="vc-mega__panel" id="<?php echo esc_attr( $target ); ?>" data-vc-mega-panel="<?php echo esc_attr( $target ); ?>" hidden>
					<div class="vc-mega__panel-inner">
						<div class="vc-mega__vehicles">
							<?php foreach ( $it['vehicles'] as $v ) : $img = $v['image']['url'] ?? ''; ?>
								<div class="vc-mega__vehicle">
									<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $v['title'] ?? '' ); ?>" loading="lazy"><?php else : ?><span class="vc-mega__vehicle-ph"></span><?php endif; ?>
									<span class="vc-mega__vehicle-title"><?php echo esc_html( $v['title'] ?? '' ); ?></span>
									<?php if ( ! empty( $v['price'] ) ) : ?><span class="vc-mega__vehicle-price"><?php echo esc_html( $v['price'] ); ?></span><?php endif; ?>
									<div class="vc-mega__vehicle-actions">
										<?php if ( ! empty( $v['link1_l'] ) ) : ?><a href="<?php echo esc_url( $v['link1_u']['url'] ?? '#' ); ?>"><?php echo esc_html( $v['link1_l'] ); ?></a><?php endif; ?>
										<?php if ( ! empty( $v['link2_l'] ) ) : ?><a href="<?php echo esc_url( $v['link2_u']['url'] ?? '#' ); ?>"><?php echo esc_html( $v['link2_l'] ); ?></a><?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if ( $side_links ) : ?>
							<div class="vc-mega__side">
								<?php foreach ( $side_links as $line ) :
									$parts = array_map( 'trim', explode( '|', $line ) );
									$lbl = $parts[0] ?? ''; $u = $parts[1] ?? '#';
									if ( $lbl === '' ) continue;
								?>
									<a href="<?php echo esc_url( $u ); ?>"><?php echo esc_html( $lbl ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<span class="vc-mega__scrim" data-vc-mega-scrim></span>
		</header>

		<aside id="vc-mega-drawer" class="vc-nav__drawer vc-mega__drawer" data-vc-nav-drawer hidden>
			<ul>
				<?php foreach ( $s['items'] as $it ) :
					$url = $it['url']['url'] ?? '#'; ?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $it['label'] ); ?></a></li>
				<?php endforeach; ?>
				<?php if ( $s['show_support'] === 'yes' ) : ?>
					<li><a href="<?php echo esc_url( $s['support_url']['url'] ?? '/support/' ); ?>"><?php esc_html_e( 'Support', 'voltcore' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $s['show_shop'] === 'yes' ) : ?>
					<li><a href="<?php echo esc_url( $s['shop_url']['url'] ?? '/shop/' ); ?>"><?php esc_html_e( 'Shop', 'voltcore' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $s['show_account'] === 'yes' ) : ?>
					<li><button type="button" data-vc-account-open><?php esc_html_e( 'Account', 'voltcore' ); ?></button></li>
				<?php endif; ?>
			</ul>
		</aside>
		<?php
	}
}
