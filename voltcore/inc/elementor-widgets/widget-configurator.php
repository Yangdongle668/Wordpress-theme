<?php
/**
 * VoltCore Design Studio (Configurator) — interactive vehicle builder.
 *
 * Reproduces tesla.com's configurator: a sticky stage on the left
 * cycles between paint colours, with options panels on the right for
 * Paint / Wheels / Interior / Autopilot. Live price totals update as
 * the user picks options. Final CTA carries the configuration through
 * the URL so checkout / lead-form pages can read it back.
 *
 * Editor controls expose every option so non-developers can ship a
 * full configurator without touching JSON.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Configurator extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-configurator'; }
	public function get_title() { return __( 'VoltCore Design Studio', 'voltcore' ); }
	public function get_icon() { return 'eicon-paint-brush'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'configurator', 'design', 'studio', 'paint', 'wheels' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_model', array( 'label' => __( 'Model', 'voltcore' ) ) );

		$this->add_control( 'model_name', array( 'label' => __( 'Model name', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model S' ) );
		$this->add_control( 'base_price', array( 'label' => __( 'Base price (USD)', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 79990 ) );
		$this->add_control( 'cta_label',  array( 'label' => __( 'CTA label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Continue to Order' ) );
		$this->add_control( 'cta_url',    array( 'label' => __( 'CTA URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#order' ) ) );

		$this->add_control( 'preset', array(
			'label'   => __( 'Layout preset', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'split',
			'options' => array(
				'split'    => __( 'Stage left + panel right (default)', 'voltcore' ),
				'mirror'   => __( 'Mirror — panel left + stage right', 'voltcore' ),
				'stacked'  => __( 'Stacked — stage on top', 'voltcore' ),
				'centered' => __( 'Centered hero — panel below', 'voltcore' ),
			),
		) );

		$this->end_controls_section();

		/* Paint */
		$this->start_controls_section( 'section_paint', array( 'label' => __( 'Paint', 'voltcore' ) ) );
		$paint = new \Elementor\Repeater();
		$paint->add_control( 'id',    array( 'label' => __( 'ID', 'voltcore' ),    'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'pearl' ) );
		$paint->add_control( 'name',  array( 'label' => __( 'Name', 'voltcore' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Pearl White' ) );
		$paint->add_control( 'color', array( 'label' => __( 'Swatch color', 'voltcore' ), 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#f4f4f4' ) );
		$paint->add_control( 'price', array( 'label' => __( '+ Price', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$paint->add_control( 'image', array( 'label' => __( 'Vehicle image', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => VOLTCORE_URI . '/assets/images/product-1.jpg' ) ) );

		$this->add_control( 'paints', array(
			'label'       => __( 'Paint options', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $paint->get_controls(),
			'title_field' => '{{{ name }}}',
			'default'     => array(
				array( 'id' => 'pearl', 'name' => 'Pearl White Multi-Coat', 'color' => '#f4f4f4', 'price' => 0,    'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-pearl.svg' ) ),
				array( 'id' => 'solid', 'name' => 'Solid Black',            'color' => '#0b0b0b', 'price' => 1500, 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-black.svg' ) ),
				array( 'id' => 'red',   'name' => 'Ultra Red',              'color' => '#a31621', 'price' => 2500, 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-red.svg' ) ),
				array( 'id' => 'blue',  'name' => 'Deep Blue Metallic',     'color' => '#1f3263', 'price' => 1500, 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-blue.svg' ) ),
				array( 'id' => 'gray',  'name' => 'Stealth Grey',           'color' => '#3b3d40', 'price' => 1000, 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-grey.svg' ) ),
			),
		) );
		$this->end_controls_section();

		/* Wheels */
		$this->start_controls_section( 'section_wheels', array( 'label' => __( 'Wheels', 'voltcore' ) ) );
		$wh = new \Elementor\Repeater();
		$wh->add_control( 'id', array( 'label' => __( 'ID', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'tempest' ) );
		$wh->add_control( 'name', array( 'label' => __( 'Name', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '19” Tempest Wheels' ) );
		$wh->add_control( 'sub',  array( 'label' => __( 'Sub-text', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Up to 405 mi range' ) );
		$wh->add_control( 'price', array( 'label' => __( '+ Price', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );

		$this->add_control( 'wheels', array(
			'label' => __( 'Wheel options', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::REPEATER,
			'fields' => $wh->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'id' => 'tempest', 'name' => '19” Tempest Wheels', 'sub' => 'Up to 405 mi range', 'price' => 0 ),
				array( 'id' => 'arachnid', 'name' => '21” Arachnid Wheels', 'sub' => 'Up to 359 mi range', 'price' => 4500 ),
			),
		) );
		$this->end_controls_section();

		/* Interior */
		$this->start_controls_section( 'section_interior', array( 'label' => __( 'Interior', 'voltcore' ) ) );
		$ir = new \Elementor\Repeater();
		$ir->add_control( 'id',   array( 'label' => __( 'ID', 'voltcore' ),    'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'allblack' ) );
		$ir->add_control( 'name', array( 'label' => __( 'Name', 'voltcore' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'All Black' ) );
		$ir->add_control( 'sub',  array( 'label' => __( 'Sub-text', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$ir->add_control( 'price', array( 'label' => __( '+ Price', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );

		$this->add_control( 'interiors', array(
			'label' => __( 'Interior options', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::REPEATER,
			'fields' => $ir->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'id' => 'allblack', 'name' => 'All Black', 'price' => 0 ),
				array( 'id' => 'cream',    'name' => 'Cream',     'price' => 2000 ),
				array( 'id' => 'whiteblack', 'name' => 'Black and White', 'price' => 2000 ),
			),
		) );
		$this->end_controls_section();

		/* Autopilot */
		$this->start_controls_section( 'section_ap', array( 'label' => __( 'Autopilot', 'voltcore' ) ) );
		$ap = new \Elementor\Repeater();
		$ap->add_control( 'id',   array( 'label' => __( 'ID', 'voltcore' ),    'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'basic' ) );
		$ap->add_control( 'name', array( 'label' => __( 'Name', 'voltcore' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Autopilot' ) );
		$ap->add_control( 'sub',  array( 'label' => __( 'Sub-text', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Included' ) );
		$ap->add_control( 'price', array( 'label' => __( '+ Price', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );

		$this->add_control( 'autopilots', array(
			'label' => __( 'Autopilot tiers', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::REPEATER,
			'fields' => $ap->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'id' => 'basic', 'name' => 'Autopilot', 'sub' => 'Included', 'price' => 0 ),
				array( 'id' => 'efsd',  'name' => 'Enhanced Autopilot', 'sub' => 'Auto Lane Change · Navigate on AP', 'price' => 6000 ),
				array( 'id' => 'fsd',   'name' => 'Full Self-Driving Capability', 'sub' => 'Traffic-aware cruise · Auto-park · Summon', 'price' => 12000 ),
			),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$paints   = is_array( $s['paints'] ?? null ) ? $s['paints'] : array();
		$wheels   = is_array( $s['wheels'] ?? null ) ? $s['wheels'] : array();
		$interiors = is_array( $s['interiors'] ?? null ) ? $s['interiors'] : array();
		$aps      = is_array( $s['autopilots'] ?? null ) ? $s['autopilots'] : array();

		$json = array(
			'basePrice'  => intval( $s['base_price'] ),
			'paints'     => array_map( function( $p ) { return array(
				'id'    => $p['id'],
				'name'  => $p['name'],
				'price' => intval( $p['price'] ),
				'image' => $p['image']['url'] ?? '',
			); }, $paints ),
			'wheels'     => array_map( function( $p ) { return array( 'id' => $p['id'], 'name' => $p['name'], 'price' => intval( $p['price'] ) ); }, $wheels ),
			'interiors'  => array_map( function( $p ) { return array( 'id' => $p['id'], 'name' => $p['name'], 'price' => intval( $p['price'] ) ); }, $interiors ),
			'autopilots' => array_map( function( $p ) { return array( 'id' => $p['id'], 'name' => $p['name'], 'price' => intval( $p['price'] ) ); }, $aps ),
		);
		?>
		<section class="vc-config vc-config--<?php echo esc_attr( $s['preset'] ?? 'split' ); ?>" data-vc-config>
			<div class="vc-config__stage">
				<div class="vc-config__stage-stack">
				<?php foreach ( $paints as $i => $p ) : if ( empty( $p['image']['url'] ) ) continue; ?>
					<img src="<?php echo esc_url( $p['image']['url'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>"
						data-paint="<?php echo esc_attr( $i ); ?>"
						class="<?php echo $i === 0 ? 'is-active' : ''; ?>">
				<?php endforeach; ?>
				</div>
			</div>
			<aside class="vc-config__panel">
				<h2 class="vc-config__model"><?php echo esc_html( $s['model_name'] ); ?></h2>
				<p class="vc-config__price">$<span data-config-price>0</span><small><?php esc_html_e( 'Estimated total · before incentives', 'voltcore' ); ?></small></p>

				<div class="vc-config__group">
					<h3 class="vc-config__group-title"><?php esc_html_e( 'Paint', 'voltcore' ); ?></h3>
					<div class="vc-config__paints">
					<?php foreach ( $paints as $i => $p ) : ?>
						<button class="vc-config__paint<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button"
							style="background:<?php echo esc_attr( $p['color'] ); ?>"
							aria-label="<?php echo esc_attr( $p['name'] ); ?>"></button>
					<?php endforeach; ?>
					</div>
				</div>

				<div class="vc-config__group" data-config-group="wheel">
					<h3 class="vc-config__group-title"><?php esc_html_e( 'Wheels', 'voltcore' ); ?></h3>
					<div class="vc-config__choice">
					<?php foreach ( $wheels as $i => $w ) : ?>
						<button class="vc-config__option<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button">
							<span><span class="vc-config__option-label"><?php echo esc_html( $w['name'] ); ?></span>
							<?php if ( ! empty( $w['sub'] ) ) : ?><span class="vc-config__option-sub"><?php echo esc_html( $w['sub'] ); ?></span><?php endif; ?></span>
							<span class="vc-config__option-price"><?php echo $w['price'] > 0 ? '+$' . number_format( $w['price'] ) : __( 'Included', 'voltcore' ); ?></span>
						</button>
					<?php endforeach; ?>
					</div>
				</div>

				<div class="vc-config__group" data-config-group="interior">
					<h3 class="vc-config__group-title"><?php esc_html_e( 'Interior', 'voltcore' ); ?></h3>
					<div class="vc-config__choice">
					<?php foreach ( $interiors as $i => $w ) : ?>
						<button class="vc-config__option<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button">
							<span><span class="vc-config__option-label"><?php echo esc_html( $w['name'] ); ?></span>
							<?php if ( ! empty( $w['sub'] ) ) : ?><span class="vc-config__option-sub"><?php echo esc_html( $w['sub'] ); ?></span><?php endif; ?></span>
							<span class="vc-config__option-price"><?php echo $w['price'] > 0 ? '+$' . number_format( $w['price'] ) : __( 'Included', 'voltcore' ); ?></span>
						</button>
					<?php endforeach; ?>
					</div>
				</div>

				<div class="vc-config__group" data-config-group="autopilot">
					<h3 class="vc-config__group-title"><?php esc_html_e( 'Autopilot', 'voltcore' ); ?></h3>
					<div class="vc-config__choice">
					<?php foreach ( $aps as $i => $w ) : ?>
						<button class="vc-config__option<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button">
							<span><span class="vc-config__option-label"><?php echo esc_html( $w['name'] ); ?></span>
							<?php if ( ! empty( $w['sub'] ) ) : ?><span class="vc-config__option-sub"><?php echo esc_html( $w['sub'] ); ?></span><?php endif; ?></span>
							<span class="vc-config__option-price"><?php echo $w['price'] > 0 ? '+$' . number_format( $w['price'] ) : __( 'Included', 'voltcore' ); ?></span>
						</button>
					<?php endforeach; ?>
					</div>
				</div>

				<a class="btn btn--dark vc-config__cta" data-config-cta data-base="<?php echo esc_url( $s['cta_url']['url'] ?? '#' ); ?>" href="<?php echo esc_url( $s['cta_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['cta_label'] ); ?></a>

				<script type="application/json"><?php echo wp_json_encode( $json ); ?></script>
			</aside>
		</section>
		<?php
	}
}
