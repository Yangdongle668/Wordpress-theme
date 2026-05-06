<?php
/**
 * VoltCore Savings Calculator — Powerwall / Solar style readout.
 *
 * Inputs: monthly kWh usage, monthly bill, sun hours/day, system size.
 * Outputs: estimated annual savings, 25-year savings, CO2 offset,
 * payback period. All math runs in vanilla JS in the browser.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Savings_Calculator extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-savings-calc'; }
	public function get_title() { return __( 'VoltCore Savings Calculator', 'voltcore' ); }
	public function get_icon() { return 'eicon-cart-light'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'powerwall', 'solar', 'savings', 'calculator' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_calc', array( 'label' => __( 'Defaults & labels', 'voltcore' ) ) );

		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Estimate your savings' ) );

		$this->add_control( 'usage_default', array( 'label' => __( 'Default kWh/month', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 900 ) );
		$this->add_control( 'bill_default',  array( 'label' => __( 'Default monthly bill ($)', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 180 ) );
		$this->add_control( 'sun_default',   array( 'label' => __( 'Default sun hours/day', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 5 ) );
		$this->add_control( 'system_default', array( 'label' => __( 'Default system price ($)', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 12000 ) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="vc-calc" data-vc-calc>
			<div class="vc-calc__inputs">
				<?php if ( ! empty( $s['heading'] ) ) : ?><h2 style="margin-bottom:24px;"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
				<div class="row">
					<label><?php esc_html_e( 'Monthly usage (kWh)', 'voltcore' ); ?> <span data-calc-display-usage></span></label>
					<input type="range" min="200" max="3000" step="50" value="<?php echo esc_attr( $s['usage_default'] ); ?>" data-calc-usage>
				</div>
				<div class="row">
					<label><?php esc_html_e( 'Monthly bill ($)', 'voltcore' ); ?> <span data-calc-display-bill></span></label>
					<input type="range" min="40" max="600" step="5" value="<?php echo esc_attr( $s['bill_default'] ); ?>" data-calc-bill>
				</div>
				<div class="row">
					<label><?php esc_html_e( 'Sun hours / day', 'voltcore' ); ?> <span data-calc-display-sun></span></label>
					<input type="range" min="2" max="8" step="0.1" value="<?php echo esc_attr( $s['sun_default'] ); ?>" data-calc-sun>
				</div>
				<div class="row">
					<label><?php esc_html_e( 'System size price ($)', 'voltcore' ); ?></label>
					<input type="number" min="3000" step="100" value="<?php echo esc_attr( $s['system_default'] ); ?>" data-calc-system>
				</div>
			</div>
			<div class="vc-calc__readout">
				<h3><?php esc_html_e( 'Estimated annual savings', 'voltcore' ); ?></h3>
				<p class="vc-calc__output-num" data-calc-out-year>$0</p>
				<div class="vc-calc__row"><span><?php esc_html_e( '25-year savings', 'voltcore' ); ?></span><b data-calc-out-life>$0</b></div>
				<div class="vc-calc__row"><span><?php esc_html_e( 'CO₂ offset (25 yr)', 'voltcore' ); ?></span><b data-calc-out-co2>0 t</b></div>
				<div class="vc-calc__row"><span><?php esc_html_e( 'Payback period', 'voltcore' ); ?></span><b data-calc-out-payback>0 yr</b></div>
			</div>
		</section>
		<?php
	}
}
