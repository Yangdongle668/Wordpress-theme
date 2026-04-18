<?php
/**
 * VoltCore Configurator — vehicle/product configurator.
 *
 * A client-side option picker: trims, paint swatches, wheels, interior,
 * add-ons. Each option has a delta price that updates the running total.
 * Selections persist to URL query and localStorage. Final "Save
 * Configuration" button serializes the state into a POST-able summary.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Configurator extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-configurator'; }
	public function get_title() { return __( 'VoltCore Configurator', 'voltcore' ); }
	public function get_icon() { return 'eicon-slider-push'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'configurator', 'builder', 'options', 'pricing' ); }

	protected function register_controls() {

		$this->start_controls_section( 'content', array( 'label' => __( 'Vehicle', 'voltcore' ) ) );
		$this->add_control( 'vehicle_name', array( 'label' => 'Vehicle name', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model V' ) );
		$this->add_control( 'base_price', array( 'label' => 'Base price (USD)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 39990 ) );
		$this->add_control( 'currency', array( 'label' => 'Currency symbol', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '$' ) );
		$this->add_control( 'image', array( 'label' => 'Hero image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$this->end_controls_section();

		// Trim
		$this->start_controls_section( 'trims', array( 'label' => __( 'Trims', 'voltcore' ) ) );
		$trim = new \Elementor\Repeater();
		$trim->add_control( 'name',  array( 'label' => 'Name',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Long Range' ) );
		$trim->add_control( 'delta', array( 'label' => 'Delta $', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$trim->add_control( 'range_mi', array( 'label' => 'Range (mi)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 340 ) );
		$trim->add_control( 'zero_sixty', array( 'label' => '0-60 s', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '4.2' ) );
		$trim->add_control( 'top_speed', array( 'label' => 'Top mph', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 130 ) );
		$this->add_control( 'trims_list', array(
			'label' => 'Trims', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $trim->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'Long Range',  'delta' => 0,     'range_mi' => 340, 'zero_sixty' => '4.2', 'top_speed' => 130 ),
				array( 'name' => 'Performance', 'delta' => 10000, 'range_mi' => 310, 'zero_sixty' => '2.9', 'top_speed' => 162 ),
			),
		) );
		$this->end_controls_section();

		// Paint
		$this->start_controls_section( 'paint', array( 'label' => __( 'Paint', 'voltcore' ) ) );
		$paint = new \Elementor\Repeater();
		$paint->add_control( 'name',  array( 'label' => 'Name',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Pearl White' ) );
		$paint->add_control( 'color', array( 'label' => 'Swatch color', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#ffffff' ) );
		$paint->add_control( 'delta', array( 'label' => 'Delta $', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$this->add_control( 'paints_list', array(
			'label' => 'Paints', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $paint->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'Pearl White', 'color' => '#f2f2f2', 'delta' => 0 ),
				array( 'name' => 'Solid Black', 'color' => '#111111', 'delta' => 1500 ),
				array( 'name' => 'Midnight Silver', 'color' => '#4a4e53', 'delta' => 1500 ),
				array( 'name' => 'Deep Blue', 'color' => '#1f3a6b', 'delta' => 2000 ),
				array( 'name' => 'Red Multi-Coat', 'color' => '#b3121a', 'delta' => 2500 ),
			),
		) );
		$this->end_controls_section();

		// Wheels
		$this->start_controls_section( 'wheels', array( 'label' => __( 'Wheels', 'voltcore' ) ) );
		$wheel = new \Elementor\Repeater();
		$wheel->add_control( 'name',  array( 'label' => 'Name',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => '19" Aero' ) );
		$wheel->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$wheel->add_control( 'range_adj', array( 'label' => 'Range adjust (mi)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$wheel->add_control( 'delta', array( 'label' => 'Delta $', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$this->add_control( 'wheels_list', array(
			'label' => 'Wheels', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $wheel->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => '19" Aero',   'delta' => 0,    'range_adj' => 0 ),
				array( 'name' => '20" Turbine','delta' => 2500, 'range_adj' => -15 ),
			),
		) );
		$this->end_controls_section();

		// Interior
		$this->start_controls_section( 'interior', array( 'label' => __( 'Interior', 'voltcore' ) ) );
		$int = new \Elementor\Repeater();
		$int->add_control( 'name',  array( 'label' => 'Name',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'All Black' ) );
		$int->add_control( 'color', array( 'label' => 'Swatch', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#151515' ) );
		$int->add_control( 'delta', array( 'label' => 'Delta $', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0 ) );
		$this->add_control( 'interiors_list', array(
			'label' => 'Interiors', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $int->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'All Black', 'color' => '#0c0c0c', 'delta' => 0 ),
				array( 'name' => 'Cream',     'color' => '#e9dfce', 'delta' => 1000 ),
			),
		) );
		$this->end_controls_section();

		// Autopilot / Add-ons
		$this->start_controls_section( 'addons', array( 'label' => __( 'Add-ons', 'voltcore' ) ) );
		$add = new \Elementor\Repeater();
		$add->add_control( 'name',  array( 'label' => 'Name',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Enhanced Autopilot' ) );
		$add->add_control( 'desc',  array( 'label' => 'Description', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Navigate, lane change, Autopark and Smart Summon.' ) );
		$add->add_control( 'delta', array( 'label' => 'Delta $', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6000 ) );
		$this->add_control( 'addons_list', array(
			'label' => 'Add-ons', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $add->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'Enhanced Autopilot',    'delta' => 6000, 'desc' => 'Navigate, lane change, Autopark, Smart Summon.' ),
				array( 'name' => 'Full Self-Driving',     'delta' => 12000, 'desc' => 'Traffic-aware autosteer on city and highway.' ),
				array( 'name' => 'Premium Connectivity',  'delta' => 99,    'desc' => 'Live traffic visualisation and streaming.' ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'cta', array( 'label' => __( 'Footer / CTA', 'voltcore' ) ) );
		$this->add_control( 'order_label', array( 'label' => 'Order label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Continue to order' ) );
		$this->add_control( 'order_url', array( 'label' => 'Order URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'disclaimer', array( 'label' => 'Disclaimer', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Prices are estimates before tax, title, registration and destination fees.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$currency = $s['currency'] ?: '$';
		$img = $s['image']['url'] ?? '';
		?>
		<section class="vc-cfg" data-vc-cfg data-base="<?php echo (int) $s['base_price']; ?>" data-currency="<?php echo esc_attr( $currency ); ?>">
			<div class="vc-cfg__visual">
				<?php if ( $img ) : ?>
					<img class="vc-cfg__img" src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $s['vehicle_name'] ); ?>">
				<?php else : ?>
					<div class="vc-cfg__img vc-cfg__img--placeholder"></div>
				<?php endif; ?>
				<div class="vc-cfg__visual-stats">
					<div><strong data-vc-cfg-range>—</strong><span>mi range</span></div>
					<div><strong data-vc-cfg-zero>—</strong><span>0-60 s</span></div>
					<div><strong data-vc-cfg-top>—</strong><span>top mph</span></div>
				</div>
			</div>

			<div class="vc-cfg__panel">
				<header class="vc-cfg__head">
					<span class="vc-cfg__eyebrow">Custom Order</span>
					<h2 class="vc-cfg__title"><?php echo esc_html( $s['vehicle_name'] ); ?></h2>
					<p class="vc-cfg__price"><span data-vc-cfg-price><?php echo esc_html( $currency . number_format_i18n( $s['base_price'] ) ); ?></span></p>
				</header>

				<div class="vc-cfg__group" data-vc-cfg-group="trim">
					<h3>Trim</h3>
					<div class="vc-cfg__opts vc-cfg__opts--stack">
						<?php foreach ( (array) $s['trims_list'] as $i => $t ) : ?>
							<button type="button" class="vc-cfg__opt vc-cfg__opt--row<?php echo $i === 0 ? ' is-active' : ''; ?>"
								data-vc-cfg-option="trim"
								data-delta="<?php echo (int) $t['delta']; ?>"
								data-range="<?php echo (int) $t['range_mi']; ?>"
								data-zero="<?php echo esc_attr( $t['zero_sixty'] ); ?>"
								data-top="<?php echo (int) $t['top_speed']; ?>"
								data-label="<?php echo esc_attr( $t['name'] ); ?>">
								<strong><?php echo esc_html( $t['name'] ); ?></strong>
								<span><?php echo (int) $t['range_mi']; ?> mi · 0-60 <?php echo esc_html( $t['zero_sixty'] ); ?> s</span>
								<span class="vc-cfg__opt-price"><?php echo $t['delta'] > 0 ? '+' . $currency . number_format_i18n( $t['delta'] ) : 'Included'; ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="vc-cfg__group" data-vc-cfg-group="paint">
					<h3>Paint</h3>
					<div class="vc-cfg__swatches">
						<?php foreach ( (array) $s['paints_list'] as $i => $p ) : ?>
							<button type="button" class="vc-cfg__swatch<?php echo $i === 0 ? ' is-active' : ''; ?>"
								style="--sw: <?php echo esc_attr( $p['color'] ); ?>;"
								data-vc-cfg-option="paint"
								data-delta="<?php echo (int) $p['delta']; ?>"
								data-label="<?php echo esc_attr( $p['name'] ); ?>"
								aria-label="<?php echo esc_attr( $p['name'] . ' +' . $currency . $p['delta'] ); ?>">
								<span class="vc-cfg__swatch-dot"></span>
							</button>
						<?php endforeach; ?>
					</div>
					<p class="vc-cfg__selected">Selected: <strong data-vc-cfg-label="paint">—</strong></p>
				</div>

				<div class="vc-cfg__group" data-vc-cfg-group="wheel">
					<h3>Wheels</h3>
					<div class="vc-cfg__opts">
						<?php foreach ( (array) $s['wheels_list'] as $i => $w ) : ?>
							<button type="button" class="vc-cfg__opt<?php echo $i === 0 ? ' is-active' : ''; ?>"
								data-vc-cfg-option="wheel"
								data-delta="<?php echo (int) $w['delta']; ?>"
								data-range-adj="<?php echo (int) $w['range_adj']; ?>"
								data-label="<?php echo esc_attr( $w['name'] ); ?>">
								<?php if ( ! empty( $w['image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $w['image']['url'] ); ?>" alt="<?php echo esc_attr( $w['name'] ); ?>">
								<?php else : ?>
									<span class="vc-cfg__opt-ph"></span>
								<?php endif; ?>
								<strong><?php echo esc_html( $w['name'] ); ?></strong>
								<span class="vc-cfg__opt-price"><?php echo $w['delta'] > 0 ? '+' . $currency . number_format_i18n( $w['delta'] ) : 'Included'; ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="vc-cfg__group" data-vc-cfg-group="interior">
					<h3>Interior</h3>
					<div class="vc-cfg__swatches">
						<?php foreach ( (array) $s['interiors_list'] as $i => $it ) : ?>
							<button type="button" class="vc-cfg__swatch vc-cfg__swatch--wide<?php echo $i === 0 ? ' is-active' : ''; ?>"
								style="--sw: <?php echo esc_attr( $it['color'] ); ?>;"
								data-vc-cfg-option="interior"
								data-delta="<?php echo (int) $it['delta']; ?>"
								data-label="<?php echo esc_attr( $it['name'] ); ?>">
								<span class="vc-cfg__swatch-dot"></span>
								<span class="vc-cfg__swatch-label"><?php echo esc_html( $it['name'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( ! empty( $s['addons_list'] ) ) : ?>
					<div class="vc-cfg__group" data-vc-cfg-group="addons">
						<h3>Add-ons</h3>
						<div class="vc-cfg__addons">
							<?php foreach ( (array) $s['addons_list'] as $i => $a ) : ?>
								<label class="vc-cfg__addon">
									<input type="checkbox"
										data-vc-cfg-option="addon"
										data-delta="<?php echo (int) $a['delta']; ?>"
										data-label="<?php echo esc_attr( $a['name'] ); ?>">
									<span class="vc-cfg__addon-body">
										<strong><?php echo esc_html( $a['name'] ); ?></strong>
										<span><?php echo esc_html( $a['desc'] ); ?></span>
									</span>
									<span class="vc-cfg__addon-price">+<?php echo esc_html( $currency . number_format_i18n( $a['delta'] ) ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<footer class="vc-cfg__foot">
					<a class="btn btn--dark vc-cfg__order" href="<?php echo esc_url( $s['order_url']['url'] ?? '#' ); ?>" data-vc-cfg-order>
						<?php echo esc_html( $s['order_label'] ); ?>
					</a>
					<?php if ( ! empty( $s['disclaimer'] ) ) : ?>
						<p class="vc-cfg__disclaimer"><?php echo esc_html( $s['disclaimer'] ); ?></p>
					<?php endif; ?>
				</footer>
			</div>
		</section>
		<?php
	}
}
