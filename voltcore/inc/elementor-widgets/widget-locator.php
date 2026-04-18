<?php
/**
 * VoltCore Locator — service / charger / showroom locator.
 *
 * Renders a split layout: left-side scrollable card list, right-side
 * Leaflet map (OpenStreetMap tiles, loaded from CDN). Clicking a card
 * pans / zooms the map and opens the marker popup.
 *
 * Leaflet is only enqueued when the widget is present on the page
 * (handled in assets/js/interactions.js).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Locator extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-locator'; }
	public function get_title() { return __( 'VoltCore Locator', 'voltcore' ); }
	public function get_icon() { return 'eicon-google-maps'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'locator', 'map', 'charger', 'showroom' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Find us' ) );
		$this->add_control( 'intro',   array( 'label' => 'Intro',   'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Showrooms, service centres and VoltCore Superchargers near you.' ) );
		$this->add_control( 'center_lat', array( 'label' => 'Center lat', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '39.52' ) );
		$this->add_control( 'center_lng', array( 'label' => 'Center lng', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '-119.81' ) );
		$this->add_control( 'zoom', array( 'label' => 'Initial zoom', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3 ) );

		$p = new \Elementor\Repeater();
		$p->add_control( 'type', array( 'label' => 'Type', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'showroom',
			'options' => array( 'showroom' => 'Showroom', 'service' => 'Service', 'charger' => 'Supercharger', 'delivery' => 'Delivery' ) ) );
		$p->add_control( 'name',   array( 'label' => 'Name',   'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Reno Showroom' ) );
		$p->add_control( 'addr',   array( 'label' => 'Address','type' => \Elementor\Controls_Manager::TEXT, 'default' => '123 Battery Rd, Reno, NV' ) );
		$p->add_control( 'phone',  array( 'label' => 'Phone',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => '+1 (775) 000-0000' ) );
		$p->add_control( 'hours',  array( 'label' => 'Hours',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Mon–Sun 9–7' ) );
		$p->add_control( 'lat',    array( 'label' => 'Lat',    'type' => \Elementor\Controls_Manager::TEXT, 'default' => '39.52' ) );
		$p->add_control( 'lng',    array( 'label' => 'Lng',    'type' => \Elementor\Controls_Manager::TEXT, 'default' => '-119.81' ) );

		$this->add_control( 'places', array(
			'label' => 'Places', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $p->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'type' => 'showroom', 'name' => 'Reno HQ Showroom',  'addr' => '123 Battery Rd, Reno, NV',   'phone' => '+1 (775) 000-0000', 'hours' => 'Mon–Sun 9–7', 'lat' => '39.52',  'lng' => '-119.81' ),
				array( 'type' => 'service',  'name' => 'Austin Service',    'addr' => '99 Range Ave, Austin, TX',    'phone' => '+1 (512) 000-0000', 'hours' => 'Mon–Fri 8–6', 'lat' => '30.27',  'lng' => '-97.74' ),
				array( 'type' => 'charger',  'name' => 'Berlin Supercharger','addr' => 'Unter den Linden 10, Berlin','phone' => '+49 30 0000',       'hours' => '24/7',        'lat' => '52.52',  'lng' => '13.40' ),
				array( 'type' => 'delivery', 'name' => 'Singapore Delivery','addr' => '1 Marina Blvd, Singapore',    'phone' => '+65 0000 0000',     'hours' => 'Mon–Sat 10–6','lat' => '1.28',   'lng' => '103.85' ),
			),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$places = array();
		foreach ( (array) ( $s['places'] ?? array() ) as $p ) {
			$places[] = array(
				'type'  => $p['type']  ?? 'showroom',
				'name'  => $p['name']  ?? '',
				'addr'  => $p['addr']  ?? '',
				'phone' => $p['phone'] ?? '',
				'hours' => $p['hours'] ?? '',
				'lat'   => (float) ( $p['lat'] ?? 0 ),
				'lng'   => (float) ( $p['lng'] ?? 0 ),
			);
		}
		$json = wp_json_encode( array(
			'center' => array( (float) $s['center_lat'], (float) $s['center_lng'] ),
			'zoom'   => (int) $s['zoom'],
			'places' => $places,
		) );
		?>
		<section class="vc-loc" data-vc-locator>
			<header class="vc-loc__head">
				<h2><?php echo esc_html( $s['heading'] ); ?></h2>
				<?php if ( ! empty( $s['intro'] ) ) : ?><p><?php echo esc_html( $s['intro'] ); ?></p><?php endif; ?>
			</header>
			<div class="vc-loc__filters">
				<button type="button" class="vc-loc__chip is-active" data-vc-loc-chip="" ><?php esc_html_e( 'All', 'voltcore' ); ?></button>
				<button type="button" class="vc-loc__chip" data-vc-loc-chip="showroom"><?php esc_html_e( 'Showrooms', 'voltcore' ); ?></button>
				<button type="button" class="vc-loc__chip" data-vc-loc-chip="service"><?php esc_html_e( 'Service', 'voltcore' ); ?></button>
				<button type="button" class="vc-loc__chip" data-vc-loc-chip="charger"><?php esc_html_e( 'Superchargers', 'voltcore' ); ?></button>
				<button type="button" class="vc-loc__chip" data-vc-loc-chip="delivery"><?php esc_html_e( 'Delivery', 'voltcore' ); ?></button>
			</div>
			<div class="vc-loc__split">
				<div class="vc-loc__list" data-vc-loc-list>
					<?php foreach ( $places as $i => $p ) : ?>
						<button type="button" class="vc-loc__item" data-vc-loc-item="<?php echo (int) $i; ?>" data-type="<?php echo esc_attr( $p['type'] ); ?>">
							<span class="vc-loc__badge vc-loc__badge--<?php echo esc_attr( $p['type'] ); ?>"><?php echo esc_html( ucfirst( $p['type'] ) ); ?></span>
							<strong><?php echo esc_html( $p['name'] ); ?></strong>
							<span><?php echo esc_html( $p['addr'] ); ?></span>
							<?php if ( $p['hours'] ) : ?><span><?php echo esc_html( $p['hours'] ); ?></span><?php endif; ?>
							<?php if ( $p['phone'] ) : ?><a href="tel:<?php echo esc_attr( str_replace( ' ', '', $p['phone'] ) ); ?>"><?php echo esc_html( $p['phone'] ); ?></a><?php endif; ?>
						</button>
					<?php endforeach; ?>
				</div>
				<div class="vc-loc__map" data-vc-loc-map data-state="<?php echo esc_attr( $json ); ?>">
					<div class="vc-loc__map-fallback">
						<?php esc_html_e( 'Map loading…', 'voltcore' ); ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
