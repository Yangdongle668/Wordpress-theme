<?php
/**
 * VoltCore Energy Calculator — ROI estimator for home solar + battery.
 *
 * Inputs: monthly bill, grid rate (¢/kWh), roof size (m²), state.
 * Outputs: estimated annual savings, payback years, 20-year savings,
 * CO₂ offset (tonnes). Pure client-side; values recompute live.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Energy_Calc extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-energy-calc'; }
	public function get_title() { return __( 'VoltCore Energy Calc', 'voltcore' ); }
	public function get_icon() { return 'eicon-tools'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'energy', 'solar', 'ROI', 'calculator' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Estimate your savings' ) );
		$this->add_control( 'currency', array( 'label' => 'Currency', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '$' ) );
		$this->add_control( 'install_cost', array( 'label' => 'System install cost', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 18500 ) );
		$this->add_control( 'co2_per_kwh', array( 'label' => 'CO₂ kg per kWh grid', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0.42 ) );
		$this->add_control( 'disclaimer', array( 'label' => 'Disclaimer', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Illustrative only; real output depends on roof orientation, shading and local tariffs.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$c = $s['currency'] ?: '$';
		?>
		<section class="vc-erg" data-vc-energy-calc
			data-currency="<?php echo esc_attr( $c ); ?>"
			data-install="<?php echo (int) $s['install_cost']; ?>"
			data-co2="<?php echo esc_attr( $s['co2_per_kwh'] ); ?>">
			<h2 class="vc-erg__heading"><?php echo esc_html( $s['heading'] ); ?></h2>
			<div class="vc-erg__grid">
				<div class="vc-erg__inputs">
					<label>
						<span class="vc-erg__label"><?php esc_html_e( 'Monthly electricity bill', 'voltcore' ); ?> <output data-vc-erg-out="bill"><?php echo esc_html( $c . '180' ); ?></output></span>
						<input type="range" min="50" max="800" step="5" value="180" data-vc-erg="bill">
					</label>
					<label>
						<span class="vc-erg__label"><?php esc_html_e( 'Grid rate', 'voltcore' ); ?> <output data-vc-erg-out="rate">22¢/kWh</output></span>
						<input type="range" min="8" max="45" step="1" value="22" data-vc-erg="rate">
					</label>
					<label>
						<span class="vc-erg__label"><?php esc_html_e( 'Roof size', 'voltcore' ); ?> <output data-vc-erg-out="roof">40 m²</output></span>
						<input type="range" min="10" max="120" step="1" value="40" data-vc-erg="roof">
					</label>
					<label>
						<span class="vc-erg__label"><?php esc_html_e( 'Sun hours/day', 'voltcore' ); ?> <output data-vc-erg-out="sun">4.5</output></span>
						<input type="range" min="2" max="8" step="0.1" value="4.5" data-vc-erg="sun">
					</label>
				</div>
				<div class="vc-erg__results">
					<div class="vc-erg__kpi">
						<span><?php esc_html_e( 'Est. annual savings', 'voltcore' ); ?></span>
						<strong data-vc-erg-out="annual">—</strong>
					</div>
					<div class="vc-erg__kpi">
						<span><?php esc_html_e( 'Simple payback', 'voltcore' ); ?></span>
						<strong data-vc-erg-out="payback">—</strong>
					</div>
					<div class="vc-erg__kpi">
						<span><?php esc_html_e( '20-year savings', 'voltcore' ); ?></span>
						<strong data-vc-erg-out="twenty">—</strong>
					</div>
					<div class="vc-erg__kpi">
						<span><?php esc_html_e( 'CO₂ avoided (20 yr)', 'voltcore' ); ?></span>
						<strong data-vc-erg-out="co2">—</strong>
					</div>
					<?php if ( ! empty( $s['disclaimer'] ) ) : ?><p class="vc-erg__disclaim"><?php echo esc_html( $s['disclaimer'] ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
