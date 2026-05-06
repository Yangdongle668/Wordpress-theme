<?php
/**
 * VoltCore Supercharger Map — interactive locations map.
 *
 * Renders a Leaflet (OpenStreetMap) map with custom pin icons
 * positioned for each location. Sidebar with search, type filters
 * (V2/V3 chips), and clickable list. Locations are configured via
 * repeater so editors can populate any service network.
 *
 * Falls back gracefully to a static list if Leaflet fails to load.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Supercharger_Map extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-supercharger-map'; }
	public function get_title() { return __( 'VoltCore Supercharger Map', 'voltcore' ); }
	public function get_icon() { return 'eicon-google-maps'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'supercharger', 'map', 'leaflet', 'locations' ); }

	public function get_style_depends() { return array( 'voltcore-leaflet' ); }
	public function get_script_depends() { return array( 'voltcore-leaflet' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_stations', array( 'label' => __( 'Stations', 'voltcore' ) ) );

		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Find a Supercharger' ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'name',    array( 'label' => __( 'Name', 'voltcore' ),    'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Palo Alto' ) );
		$rep->add_control( 'address', array( 'label' => __( 'Address', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '3500 Deer Creek Rd, Palo Alto CA' ) );
		$rep->add_control( 'lat',     array( 'label' => __( 'Latitude', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '37.395' ) );
		$rep->add_control( 'lng',     array( 'label' => __( 'Longitude', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '-122.150' ) );
		$rep->add_control( 'stalls',  array( 'label' => __( 'Stalls', 'voltcore' ),    'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 12 ) );
		$rep->add_control( 'type',    array( 'label' => __( 'Type', 'voltcore' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'v3', 'options' => array( 'v2' => 'V2 (150 kW)', 'v3' => 'V3 (250 kW)' ) ) );

		$this->add_control( 'stations', array(
			'label'       => __( 'Stations', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'Palo Alto', 'address' => '3500 Deer Creek Rd, Palo Alto CA', 'lat' => '37.395', 'lng' => '-122.150', 'stalls' => 12, 'type' => 'v3' ),
				array( 'name' => 'Fremont',   'address' => '45500 Fremont Blvd, Fremont CA',   'lat' => '37.493', 'lng' => '-121.946', 'stalls' => 24, 'type' => 'v3' ),
				array( 'name' => 'San Francisco', 'address' => '650 Mission St, San Francisco CA', 'lat' => '37.787', 'lng' => '-122.401', 'stalls' => 8, 'type' => 'v2' ),
				array( 'name' => 'Los Angeles',   'address' => '5000 W Olympic Blvd, Los Angeles CA', 'lat' => '34.034', 'lng' => '-118.345', 'stalls' => 16, 'type' => 'v3' ),
				array( 'name' => 'Las Vegas',     'address' => '3645 Las Vegas Blvd, Las Vegas NV', 'lat' => '36.114', 'lng' => '-115.173', 'stalls' => 10, 'type' => 'v3' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$stations = is_array( $s['stations'] ?? null ) ? $s['stations'] : array();

		$json = array_map( function( $st, $i ) {
			return array(
				'id'      => 's' . $i,
				'name'    => $st['name'],
				'address' => $st['address'],
				'lat'     => floatval( $st['lat'] ),
				'lng'     => floatval( $st['lng'] ),
				'stalls'  => intval( $st['stalls'] ),
				'type'    => $st['type'],
			);
		}, $stations, array_keys( $stations ) );
		?>
		<?php if ( ! empty( $s['heading'] ) ) : ?><h2 style="text-align:center;margin:40px 0 24px;"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
		<section class="vc-map" data-vc-map>
			<div class="vc-map__container">
				<div class="vc-map__canvas" id="vc-map-<?php echo esc_attr( $this->get_id() ); ?>"></div>
				<aside class="vc-map__sidebar">
					<input type="search" class="vc-map__search" data-map-search placeholder="<?php esc_attr_e( 'Search city, address…', 'voltcore' ); ?>">
					<div class="vc-map__filters">
						<button class="vc-map__chip is-active" data-filter="all"><?php esc_html_e( 'All', 'voltcore' ); ?></button>
						<button class="vc-map__chip" data-filter="v3"><?php esc_html_e( 'V3 250 kW', 'voltcore' ); ?></button>
						<button class="vc-map__chip" data-filter="v2"><?php esc_html_e( 'V2 150 kW', 'voltcore' ); ?></button>
					</div>
					<ul class="vc-map__list" data-map-list></ul>
				</aside>
			</div>
			<script type="application/json"><?php echo wp_json_encode( $json ); ?></script>
		</section>
		<?php
	}
}
